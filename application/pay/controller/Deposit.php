<?php

namespace app\pay\controller;

use app\common\controller\ModuleBase;
use app\common\controller\ModuleUserBase;
use app\common\payment\micropay\utils\WxPayConfig;
use app\order\model\Orders;

class Deposit extends ModuleUserBase
{

    /**
     * @return string
     * @throws \think\Exception
     */
    public function do_deposit()
    {
        global $_W, $_GPC;


        if ($this->isPost(true)) {
            $data = $_GPC['data'];
            if ($data['note']) {
                $data['note'] = "用户备注: " . $data['note'];
            }

            if (!$data['amount'] || $data['amount'] <= 0) {
                $this->zbn_msg("对不起，金额错误！");
            }
            $order_insert = [];
            $order_insert['id'] = $order_insert['trade_sn'] = create_sn();
            $order_insert['out_trade_no'] = $order_insert['id'] . "@" . time();
            $order_insert['buyer_user_id'] = $this->user['id'];
            $order_insert['user_id'] = $this->user['id'];
            //
            $order_insert['gateway'] = $gateway = $data['gateway'];
            //计算费用
            $order_insert['total_fee'] = $data['amount'] * config('pay.recharge_ratio');
            $order_insert['express_fee'] = 0;
            $order_insert['delivery'] = "";
            //common info
            $order_insert['address'] = "";
            $order_insert['receiver'] = "";
            $order_insert['mobile'] = $data['mobile'];
            $order_insert['seller_user_id'] = 0;
            $order_insert['create_time'] = date("Y-m-d H:i:s");
            $order_insert['create_ip'] = $this->request->ip();
            $order_insert['note'] = "余额充值. " . $data['note'];
            $order_insert['site_id'] = $_W['site']['id'];
            $order_insert['month'] = date("m");
            $order_insert['year'] = date('Y');
            $order_insert['status'] = '待支付';
            //支付模式公众号模式
            $order_insert['pay_mode'] = 'WX_GZH';
            $order_insert['unit_type'] = 1;
            $order_insert['is_online'] = 1;

            $order_insert = set_model('orders')->setDefaultValueByFields($order_insert, array('unit_type', ''));
            $order = Orders::create($order_insert);
            if ($order) {
                //todo goto pay page

                //create gateway instance

                /**
                 *
                 * $gateway_processor = "\app\pay\payment\\$gateway" ;
                 * $payment = new $gateway_processor;
                 * $payment->pay();
                 */
                //do instance pay
                if (!is_weixin()) {
                    //todo goto scan pay
                    $pay_url = url('order/index/view_scan', ['id' => $order['id']]);
                    $this->zbn_msg("订单创建完成，请稍后！", "1", '', '2000', "'$pay_url'");
                    die();
                }else{
                    $pay_url = url('order/index/view', ['id' => $order['id']]);
                    $this->zbn_msg("订单创建完成，请稍后！", "1", '', '2000', "'$pay_url'");
                    die();
                }
            }

        } else {

            return $this->view->fetch();
        }

    }
}