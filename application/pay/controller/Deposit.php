<?php

namespace app\pay\controller;

use app\common\controller\ModuleBase;
use app\common\controller\ModuleUserBase;
use app\common\payment\micropay\utils\WxPayConfig;
use app\order\model\Orders;
use app\wechat\util\MhcmsWechatEngine;
use think\Cookie;

class Deposit extends ModuleUserBase
{

    /**
     * @return string
     * @throws \think\Exception
     */
    public function do_deposit()
    {
        global $_W, $_GPC;


        if (!$this->user['is_mobile_verify']) {
            $url = url('member/info/set_mobile').'?forward='.urlencode('/pay/deposit/do_deposit');
            $this->message("请先绑定手机号!", 1, $url);
        }

        //检查权限
        $user_role_id = $this->user['user_role_id'];
        if ($user_role_id == 4 || $user_role_id == 2) {
            $this->error('权限不足，请先申请为经纪人！', "/member/info/verify");
            return;
        }

        if ($this->isPost(true)) {
            $data = $_GPC['data'];

            if ($data['note']) {
                $data['note'] = "用户备注: " . $data['note'];
            }

            if (!$data['amount'] || $data['amount'] <= 0) {
                $this->zbn_msg("对不起，金额错误！");
            }
            if ($data['amount'] < $_W['site']['config']['trade']['minimum_charge']) {
                $this->zbn_msg("对不起，最低充值金额为".$_W['site']['config']['trade']['minimum_charge']."！");
            }
            $order_insert = [];
            $order_insert['id'] = $order_insert['trade_sn'] = create_sn();
            $order_insert['out_trade_no'] = $order_insert['id'] . "@" . time();
            $order_insert['buyer_user_id'] = $this->user['id'];
            $order_insert['user_id'] = $this->user['id'];
            //
            $order_insert['gateway'] = $gateway = $data['gateway'];
            //计算费用
            $order_insert['amount'] = $data['amount'];
            $order_insert['total_fee'] = $data['amount'] * $_W['site']['config']['trade']['rmb_balance_ratio'] * (1 - $_W['site']['config']['trade']['fund_rate']/100);
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
            $order_insert['source_type'] = 1;//充值

            $order_insert = set_model('orders')->setDefaultValueByFields($order_insert, array('unit_type', ''));
            $order = Orders::create($order_insert);
            if ($order) {
                Orders::log_add($order['id'], $order_insert['buyer_user_id'], '创建订单');
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
            $openid = Cookie::get("openid");
            if (is_weixin() && empty($openid) && $_W['account']) {
                if (empty($_W['uuid'])) {
                    $_W['uuid'] = Cookie::get("uuid");
                    if(!$_W['uuid']){
                        $_W['uuid'] =  mhcms_uuid();
                        Cookie::set("uuid" , $_W['uuid']);
                    }
                }
                $wechat = MhcmsWechatEngine::create($_W['account']);
                $openid = $wechat->getOpenid();
                Cookie::set("openid" , $openid);
            }
            return $this->view->fetch();
        }

    }
}