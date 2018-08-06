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
use think\Db;

class AdminOrders extends AdminBase
{

    public function index()
    {
        $this->view->filter_info = Models::gen_admin_filter("orders", $this->menu_id);
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

        $this->view->field_list = set_model('orders')->model_info->get_admin_column_fields();
        return $this->view->fetch();
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