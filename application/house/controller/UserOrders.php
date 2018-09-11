<?php
// +----------------------------------------------------------------------
// | 鸣鹤CMS [ New Better  ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://www.mhcms.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( 您必须获取授权才能进行使用 )
// +----------------------------------------------------------------------
// | Author: new better <1620298436@qq.com>
// +----------------------------------------------------------------------
namespace app\house\controller;


use app\common\model\Hits;
use app\common\util\forms\input;
use app\common\controller\AdminBase;
use app\common\model\Draw;
use app\common\model\PaymentLogs;
use app\common\model\Users;
use app\common\model\UserRoles;
use app\common\model\DistributionOrders;
use app\common\util\forms\Forms;
use app\common\util\Money;
use app\common\util\Point;
use app\order\model\Orders;
use think\Db;
use think\Exception;
use think\Log;

class UserOrders extends HouseUserBase
{
    private $house_esf = "house_esf";
    private $house_appointment = "house_appointment";

    public function _initialize()
    {
        parent::_initialize();
        $this->view->no_check_area = 1;
    }

    public function index($status = 0)
    {

        $where = [];

        $user_id = $this->user_id;
        $where['user_id'] = $user_id;

        $this->view->lists = set_model('orders')->where($where)->order('create_time desc')->paginate();

        return $this->view->fetch();
    }

    public function create()
    {

        if (!$this->agent) {
            $this->error("对不起，您还不是经济人，先去申请一下吧");
        }

        $appoint_model = set_model($this->house_appointment);
        $model_info = $appoint_model->model_info;

        if ($this->isPost(true)) {

            $base_info = input('param.data/a');
            $res = $model_info->add_content($base_info);
            $base_info['user_id'] = 0; // 清空uid
            $base_info['agent_id'] = $this->agent['id'];
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'] . " 感谢您信息，我们将尽快处理，祝您生活愉快！", 1, 'true', 1000, "'/house/user'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_user_publish_fields();
            return $this->view->fetch();
        }


    }

    /**
     * 消费房宝查看
     * @param $id 房源id
     * @param $type 类型  1-租房 2-二手房
     */
    public function pay_for_see()
    {
        $id = trim(input('param.id'));
        $type = trim(input('param.type'));
        /**
         * 1.检查房宝余额
         * 2.余额不足，提示余额不足
         * 3.余额充足，则消费对应余额
         * 4.写入对应查看权限记录
         */
        $model_name = "";
        $base_info['user_id'] = $this->user_id;
        if ($type == 1) {//租房
            $model_name = 'house_rent_order';
            $model = set_model($model_name);
            $models = set_model('house_rent');
            $base_info['rent_id'] = $id;
            $source_type = 2;
            $url = url('house/rent/detail', ['id'=>$id]);
        } else if ($type == 2) {//二手房
            $model_name = "house_esf_order";
            $model = set_model($model_name);
            $models = set_model('house_esf');
            $base_info['esf_id'] = $id;
            $source_type = 3;
            $url = url('house/esf/detail', ['id'=>$id]);
        }

        //检查权限
        $user_role_id = $this->user['user_role_id'];
        $user_role_ids = array(1, 3, 22, 23, 24);
        if (!in_array($user_role_id,$user_role_ids)) {
            $this->zbn_msg('权限不足，请先申请为经纪人！', 2, 'true', '1000', "'".$url."'", "''");
        }

        //检查房宝余额
        $balance = $this->user['balance'];
        $fb_value = config("pay.fangbao_ratio");

        $left_value = $balance - $fb_value;
        if ($balance <= 0.00 || $left_value < 0.00) {
            $this->zbn_msg('余额不足，请先去充值！', 2, 'true', '1000', "'".$url."'", "''");
        }

        $info = $models->where(['id' => $id])->field('user_id')->find();
        $data['seller_user_id'] = $info['user_id'];
        $data['amount'] = $fb_value;
        $data['source_type'] = $source_type;
        $data['source_id'] = $id;
        $data['note'] = '支付查看消费';

        // $result_json = $this->create_pay_order($data);
        $result_json = $this->fangbao_pay($data);

        //房宝消费订单号获取，生产真正的消费记录
        if ($result_json['result'] == 0) {
            $fb_order_id = $result_json['data']['order_id'];
        } else {
            $this->zbn_msg($result_json['reason'], 1, 'true', 1500, "'".$url."'", "");
            return false;
        }

        $base_info['order_id'] = $fb_order_id;
        if ($type == 1) {
            $res = Db::table('mhcms_house_rent_order')->insert($base_info);
        } elseif ($type == 2) {
            $res = Db::table('mhcms_house_esf_order')->insert($base_info);
        }

        if (!$res) {
            // $this->zbn_msg('网络故障，请稍后再试！', 2, '', 'history.back()');
            $this->zbn_msg('网络故障，请稍后再试！', 1, 'true', 1500, "'".$url."'", "");
            return false;
        } else {
            $this->zbn_msg('操作成功！', 1, 'true', 1000, "'".$url."'", "");
            echo "<script>history.back();</script>";
        }
    }

    /**
     * 房宝退款申请
     * @param $order_id 消费权限订单id
     */
    public function refund_fangbao($id, $type)
    {
        $where['user_id'] = $this->user_id;
        if ($type == 1) {//租房
            $model_name = 'house_rent_order';
            $model = set_model($model_name);
            $where['rent_id'] = $id;
            $m = $model->where($where)->find();
        } else if ($type == 2) {//二手房
            $model_name = "house_esf_order";
            $model = set_model($model_name);
            $where['esf_id'] = $id;
            $m = $model->where($where)->find();
        }
        $order_id = $m['order_id'];
        if (isset($order_id)) {

        } else {
            return false;
        }

    }

    //申请退款
    public function refund($order_id)
    {
        global $_W;

        if ($this->isPost()) {
            $data = input('param.data/a');
            $orders = Orders::get(['id' => $order_id]);

            if (!$orders) {
                $this->zbn_msg('查询不到订单', 1, 'true', 1000, "'".url('house/user_orders/index')."'", "");
            }
            if ($orders['buyer_user_id'] !== $this->user['id']) {
                $this->zbn_msg('没有权限', 1, 'true', 1000, "'".url('house/user_orders/index')."'", "");
            }
            if ($orders['status'] == '退款中') {
                $this->zbn_msg('正在退款，等待管理员审核', 1, 'true', 1000, "'".url('house/user_orders/index')."'", "");
            }
            if ($orders['status'] == '待支付') {
                $this->zbn_msg('订单未支付，无法申请退款');
            }
            if ($orders['status'] == '已关闭' ||(time() - strtotime($orders['pay_time'])) > 24 * 60 * 60) {
                $this->zbn_msg('订单已关闭或者超过24小时，无法申请退款', 1, 'true', 1000, "'".url('house/user_orders/index')."'", "");
            }
            $order_data = array();
            $order_data['status'] = '退款中';
            $order_data['refund_desc'] = $data['description'];
            $order_data['refund_time'] = date('Y-m-d H:i:s', time());
            set_model('orders')->where(['id'=>$order_id])->update($order_data);
            Orders::log_add($order_id, $orders['buyer_user_id'], '[申请退款]'.$data['description']);
            $this->zbn_msg("操作成功", 1, 'true', 1000, "'".url('house/user_orders/index')."'", "");
        } else {
            return $this->view->fetch();
        }
    }

    /**
     * 房宝消费
     * @param  [type] $data [description]
     * @return [type]       [description]
     * 
        $data['seller_user_id'] = '2108';
        $data['amount'] = 10;
        $data['source_type'] = 2;
        $data['source_id'] = 100;
        $data['note'] = 'test';
     */
    public function fangbao_pay($data)
    {
        $result = config('WEB_SUCCESS_RT');
        try {
            $result['data']['order_id'] = $this->create_pay_order($data);
        } catch (Exception $e) {
            $result = $e;
        }
        return $this->ajaxJson($result);
    }
    //创建房宝支付查看订单
    public function create_pay_order($data)
    {
        global $_W;
        if ($data['note']) {
            $data['note'] = "用户备注: " . $data['note'];
        }

        if (!$data['amount'] || $data['amount'] <= 0) {
            throw new Exception("对不起，金额错误！", 1);
        }
        $seller_user = Users::get(['id' => $data['seller_user_id']]);
        if (!$seller_user) {
            //找不到人的都是大佬的
            // throw new Exception("卖家不存在", 1);
            $data['seller_user_id'] = 1;
            $seller_user = Users::get(['id' => $data['seller_user_id']]);
        }
        if (!$data['source_type'] || !$data['source_id']) {
            throw new Exception("来源信息为空", 1);
        }
        $order_model = set_model('orders');
        $where = [];
        $where['source_type'] = $data['source_type'];
        $where['source_id'] = $data['source_id'];
        $where['buyer_user_id'] = $this->user['id'];
        $where['status'] = '已支付';
        $order = $order_model->where($where)->find();
        if ($order) {
            throw new Exception("请勿重复支付", 1);
        }

        $order_insert = [];
        $order_insert['id'] = $order_insert['trade_sn'] = create_sn();
        $order_insert['out_trade_no'] = $order_insert['id'] . "@" . time();
        $order_insert['buyer_user_id'] = $this->user['id'];
        $order_insert['user_id'] = $this->user['id'];
        $order_insert['seller_user_id'] = $data['seller_user_id'];
        //
        $order_insert['gateway'] = $gateway = 'balance';
        //计算费用
        $order_insert['amount'] = $data['amount'];
        $order_insert['total_fee'] = $data['amount'];
        $order_insert['express_fee'] = 0;
        $order_insert['delivery'] = "";
        //common info
        $order_insert['address'] = "";
        $order_insert['receiver'] = "";
        $order_insert['mobile'] = empty($data['mobile']) ? '' : $data['mobile'];
        $order_insert['create_time'] = date("Y-m-d H:i:s");
        $order_insert['create_ip'] = $this->request->ip();
        $order_insert['note'] = "房宝消费. " . $data['note'];
        $order_insert['site_id'] = $_W['site']['id'];
        $order_insert['month'] = date("m");
        $order_insert['year'] = date('Y');
        $order_insert['status'] = '已支付';
        //支付模式公众号模式
        $order_insert['pay_mode'] = 'WX_GZH';
        $order_insert['unit_type'] = 1;
        $order_insert['is_online'] = 1;
        $order_insert['source_type'] = $data['source_type'];
        $order_insert['source_id'] = $data['source_id'];

        $order_insert = $order_model->setDefaultValueByFields($order_insert);

        $spend_data = [];
        $spend_data['user_id'] = $order_insert['user_id'];
        $spend_data['amount'] = $order_insert['total_fee'];
        $spend_data['unit_type'] = 1;
        $spend_data['pay_type'] = 1;
        $spend_data['note'] = $order_insert['note'];

        try {
            $this->chg($spend_data);
            $order = Orders::create($order_insert);
            Orders::log_add($order['id'], $order_insert['buyer_user_id'], '创建订单');
            $this->create_distribution_orders($order_insert['seller_user_id'], $order['id'], $order_insert['total_fee']);
        } catch (Exception $e) {
            throw $e;
        }

        if ($order) {
            return $order['id'];
        } else {
            throw new Exception("订单创建失败！", 1);
        }
    }
    public function create_distribution_orders($seller_user_id, $order_id, $amount)
    {
        $seller_user = Users::get($seller_user_id);
        $user_id = $seller_user['parent_id'];
        $rest = 1;
        while (!empty($user_id)) {
            $user = Users::get($user_id);
            if (!in_array($user['user_role_id'], [1,3,22,23])) {
                break;
            }
            $user_role = UserRoles::get(['id'=>$user['user_role_id']]);
            
            $this->create_distribution_order($user, $order_id, $amount, $user_role['distribution_rate'] / 100);
            $rest = $rest - $user_role['distribution_rate'] / 100;
            $user_id = $user['parent_id'];
        }
        $this->create_distribution_order($seller_user, $order_id, $amount, $rest);
        return true;
    }
    public function create_distribution_order(Users $user, $order_id, $amount, $rest)
    {
        global $_W;
        $distribution_order_insert = array();
        $distribution_order_insert['order_id'] = $order_id;
        $distribution_order_insert['user_id'] = $user['id'];
        $distribution_order_insert['amount'] = $amount;
        $distribution_order_insert['total_fee'] = $amount * (float)$_W['site']['config']['trade']['balance_point_ratio'] * $rest;
        $distribution_order_insert['create_time'] = date('Y-m-d H:i:s', time());
        $distribution_order_insert['note'] = '';

        if (DistributionOrders::create($distribution_order_insert)) {
            if (!Point::deposit($user, $distribution_order_insert['total_fee'], 3, '订单分润')) {
                Log::write("分润失败！订单号：".$order_id.",user_id:".$distribution_order_insert['user_id'].',金额：'.$distribution_order_insert['total_fee']);
            }
        } else {
            Log::write("分润订单创建失败！订单号：".$order_id.",user_id:".$distribution_order_insert['user_id'].',金额：'.$distribution_order_insert['total_fee']);
        }
    }

    public function chg($data)
    {
        switch ($data['pay_type']) {
            case 1:
                $data['operate'] = 1;//1 减少;2 增加
                break;
            default:
                $data['operate'] = 2;
                break;
        }
        $user = Users::get(['id' => $data['user_id']]);
        if (!$user) {
            throw new Exception("用户不存在", 1);
        }

        if ($data['unit_type'] == 1) {
            //1 消费;2 充值
            if ($data['operate'] == 1) {
                if (!Money::spend($user, $data['amount'], $data['pay_type'], $data['note'])) {
                    throw new Exception("余额不足", 1);
                }
            } else if ($data['operate'] == 2) {
                Money::deposit($user, $data['amount'], $data['pay_type'], $data['note']);
            }
        }  elseif ($data['unit_type'] == 2) {
            //1 消费;2 充值
            if ($data['operate'] == 1) {
                if (!Point::spend($user, $data['amount'], $data['pay_type'], $data['note'])) {
                    throw new Exception("余额不足", 1);
                }
            } else if ($data['operate'] == 2) {
                Point::deposit($user, $data['amount'], $data['pay_type'], $data['note']);
            }
        }
        return true;
    }


}