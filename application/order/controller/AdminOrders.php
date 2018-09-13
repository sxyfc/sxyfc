<?php
namespace app\order\controller;

use app\common\controller\AdminBase;
use app\common\model\Modules;
use app\common\model\NodeFields;
use app\common\model\NodeTypes;
use app\common\model\Sites;
use app\order\model\Orders;
use app\order\model\OrdersLogs;
use app\common\model\Models;
use app\common\datas\csv;
use app\admin\controller\Fund;
use think\Db;

class AdminOrders extends AdminBase
{

    public function index()
    {
        $this->view->filter_info = Models::gen_admin_filter("orders", $this->menu_id);
        $product_dict = ['1'=>'充值', '2'=>'租房', '3'=>'售房'];//1充值；2租房；3售房
        $where = [];

        $buyer_name = trim(input('param.buyer_name', ' ', 'htmlspecialchars'));
        $trade_sn = trim(input('param.trade_sn', ' ', 'htmlspecialchars'));
        $status = input('param.status');
        if ($status) {
            $where['status'] = $status;
        }

        if ($buyer_name) {
            $buyers = set_model('users')->where(['nickname' => ['LIKE', '%'.$buyer_name.'%']])->whereor(['user_name' => ['LIKE', '%'.$buyer_name.'%', 'OR']])->select()->column('id');
            if (count($buyers) < 1) {
                $where['buyer_user_id'] = '';
            } else if (count($buyers) == 1) {
                $where['buyer_user_id'] = $buyers[0];
            } else {
                $where['buyer_user_id'] = ['IN', $buyers];
            }
        }

        if($trade_sn){
            $where['trade_sn'] = $trade_sn;
        }

        $this->view->lists = set_model('orders')->where($where)->order('id desc')->paginate();
        $this->view->assign('buyer_name', $buyer_name);
        $this->view->assign('trade_sn', $trade_sn);
        $this->view->assign('product_dict', $product_dict);

        $this->view->field_list = set_model('orders')->model_info->get_admin_column_fields();
        return $this->view->fetch();
    }

    public function refund_index()
    {
        $this->view->filter_info = Models::gen_admin_filter("orders", $this->menu_id);
        $product_dict = ['1'=>'充值', '2'=>'租房', '3'=>'售房'];//1充值；2租房；3售房
        $where = [];

        $buyer_name = trim(input('param.buyer_name', ' ', 'htmlspecialchars'));
        $trade_sn = trim(input('param.trade_sn', ' ', 'htmlspecialchars'));
        $status = input('param.status');
        if ($status) {
            $where['status'] = $status;
        }

        if ($buyer_name) {
            $buyers = set_model('users')->where(['nickname' => ['LIKE', '%'.$buyer_name.'%']])->whereor(['user_name' => ['LIKE', '%'.$buyer_name.'%', 'OR']])->select()->column('id');
            if (count($buyers) < 1) {
                $where['buyer_user_id'] = '';
            } else if (count($buyers) == 1) {
                $where['buyer_user_id'] = $buyers[0];
            } else {
                $where['buyer_user_id'] = ['IN', $buyers];
            }
        }

        if($trade_sn){
            $where['trade_sn'] = $trade_sn;
        }
        $where['status'] = '退款中';

        $this->view->lists = set_model('orders')->where($where)->order('id desc')->paginate();
        $this->view->assign('buyer_name', $buyer_name);
        $this->view->assign('trade_sn', $trade_sn);
        $this->view->assign('product_dict', $product_dict);

        $this->view->field_list = set_model('orders')->model_info->get_admin_column_fields();
        return $this->view->fetch();
    }

    public function export()
    {
        $csv = new csv();
        $id = input('param.id');
        $list = [[1,2,3,4],[11,22,33,44]];
        $csv_title = array('订单号', '创建时间', '收银员', '金额');
        $csv->put_csv($list, $csv_title);
    }

    //申请退款
    public function refund($order_id)
    {
        global $_W;

        if ($this->isPost()) {
            $data = input('param.data/a');
            $orders = Orders::get(['id' => $order_id]);

            if (!$orders) {
                $this->zbn_msg('查询不到订单');
            }
            if ($orders['buyer_user_id'] !== $this->user['id']) {
                $this->zbn_msg('没有权限');
            }
            if ($orders['status'] == '退款中') {
                $this->zbn_msg('正在退款，等待管理员审核');
            }
            if ($orders['status'] == '待支付') {
                $this->zbn_msg('订单未支付，无法申请退款');
            }
            if ($orders['status'] == '已关闭' ||(time() - strtotime($orders['create_time'])) > 24 * 60 * 60) {
                $this->zbn_msg('订单已关闭或者超过24小时，无法申请退款');
            }
            $order_data = array();
            $order_data['status'] = '退款中';
            $order_data['refund_desc'] = $data['description'];
            $order_data['refund_time'] = date('Y-m-d H:i:s', time());
            set_model('orders')->where(['id'=>$order_id])->update($order_data);
            Orders::log_add($order_id, $orders['buyer_user_id'], '[申请退款]'.$data['description']);
            $this->zbn_msg("操作成功", 1, 'true', 1000, "''", "'reload_parent_page()'");
        } else {
            return $this->view->fetch();
        }
    }
    //审核退款
    public function audit_refund($order_id)
    {
        global $_W;

        if ($this->isPost()) {
            try {
                $data = input('param.data/a');
                $orders = Orders::get(['id' => $order_id]);
                $audit_res = $data['pass'] == 1 ? '同意' : '不同意';

                if (!$orders) {
                    $this->zbn_msg('查询不到订单');
                }
                if (!$this->super_power) {
                    $this->zbn_msg('没有权限');
                }
                if ($orders['status'] !== '退款中') {
                    $this->zbn_msg('订单尚未申请退款');
                }
                if ((time() - strtotime($orders['refund_time'])) > 24 * 60 * 60) {
                    $order_data = array();
                    $order_data['status'] = '已完成';
                    $order_data['audit_desc'] = '订单审核超时';
                    $order_data['audit_time'] = date('Y-m-d H:i:s', time());
                    $order_data['audit_user_id'] = $this->user['id'];
                    set_model('orders')->where(['id'=>$order_id])->update($order_data);
                    Orders::log_add($order_id, $this->user['id'], '[审核退款]'.$data['description']);
                    $this->zbn_msg('订单申请退款已经超过24小时');
                }
                $order_data = array();
                $order_data['status'] = $data['pass'] == 1 ? '已关闭' : '已完成';
                $order_data['audit_desc'] = $data['description'];
                $order_data['audit_time'] = date('Y-m-d H:i:s', time());
                $order_data['audit_user_id'] = $this->user['id'];
                set_model('orders')->where(['id'=>$order_id])->update($order_data);
                Orders::log_add($order_id, $this->user['id'], '[审核退款:'.$audit_res.']'.$data['description']);
                if ($data['pass'] == 1) {
                    $this->refund_order($orders);
                }
                $res = array('result'=>0, 'reason'=> '操作成功');
            } catch (Exception $e) {
                $res = $this->ajaxJson($e);
            }
            if ($res['result'] == 0) {
                $this->zbn_msg($res['reason'], 1, 'true', 1000, "''", "'reload_parent_page()'");
            } else {
                $this->zbn_msg($res['reason']);
            }
        } else {
            $this->view->orders = Orders::get(['id' => $order_id]);
            return $this->view->fetch();
        }
    }

    public function refund_order($orders)
    {
        $deposit_data = [];
        $deposit_data['user_id'] = $orders['buyer_user_id'];
        $deposit_data['amount'] = $orders['amount'];
        $deposit_data['unit_type'] = 1;
        $deposit_data['pay_type'] = 2;
        $deposit_data['note'] = '系统退款,管理员：'.$this->user['id'];

        $fund = new Fund();
        $fund->chg($deposit_data);

        if ($orders['source_type'] != 1) {
            $this->refund_distribution($orders['id']);
        }
    }

    public function refund_distribution($order_id)
    {
        $data = set_model('distribution_orders')->where(['order_id'=>$order_id])->select()->toArray();

        $fund = new Fund();
        foreach ($data as $v) {
            $deposit_data = [];
            $deposit_data['user_id'] = $v['user_id'];
            $deposit_data['amount'] = $v['total_fee'];
            $deposit_data['unit_type'] = 2;
            $deposit_data['pay_type'] = 1;
            $deposit_data['note'] = '房宝退款,管理员：'.$this->user['id'];

            $fund->chg($deposit_data);
        }
    }

    public function view_logs($order_id){

        $order_log = new OrdersLogs();
        $order_logs = $order_log->where(['order_id'=>$order_id])->select();

        $this->view->lists = $order_logs;
        return $this->view->fetch();
    }

    public function log_add($order_id){
        $order = Orders::get(['order_id'=>$order_id]);

        if($this->isPost()){

            if($order){
                $insert = input('param.data/a');
                $insert['order_id'] = $order_id;
                $insert['create_time'] = NOW;
                $insert['user_id'] = $this->admin_id;
                OrdersLogs::create($insert);
                $this->zbn_msg("操作完成");
            }else{
                $this->zbn_msg("操作失败");
            }
        }else{
            return $this->view->fetch();
        }


    }

    public function close($order_id){
        $order = Orders::get(['order_id' =>$order_id ]);
        $cancle_data = json_decode( input('param.data') , true) ;
        $cancle_data['order_id'] = $order_id;
        $cancle_data['create_time'] = date("Y-m-d H:i:s");
        $cancle_data['user_id'] = $this->admin_id;
        $cancle_data['description'] .= "管理员后台操作";
        if($this->isPost()){
            if($order['status'] != "已完成"){
                $res = $order->cancle($order->seller_user_id);
                if($res){
                    OrdersLogs::create($cancle_data);
                    $ret = [
                        'code' => 0 ,
                        'msg' => '订单取消成功'
                    ];
                }else{
                    $ret = [
                        'code' => 0 ,
                        'msg' => '订单取消失败'
                    ];
                }
                return $ret;
            }else{
                $ret = [
                    'code' => 0 ,
                    'msg' => '订单取消失败 ， 因为订单已经完成'
                ];
                return $ret;
            }
        }else{
            return $this->view->fetch();
        }
    }
}