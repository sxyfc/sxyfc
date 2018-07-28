<?php

namespace app\order\controller;

use app\common\controller\ModuleBase;
use app\common\model\SitesWechat;
use app\pay\payment\micropay\JsApiPay;
use app\pay\payment\micropay\NativePay;
use app\pay\payment\micropay\utils\WxPayApi;
use app\pay\payment\micropay\utils\WxPayConfig;
use app\pay\payment\micropay\utils\WxPayDataBase;
use app\pay\payment\micropay\utils\WxPayOrderQuery;
use app\pay\payment\micropay\utils\WxPayUnifiedOrder;
use app\common\util\wechat\wechat;
use app\order\model\Orders;
use app\order\model\OrdersProduct;
use think\Request;

class Index extends ModuleBase {


    public function view($id){
        global $_W;
        $_W['pay_mode'] = "WX_GZH";
        $_W['WxPayConfig'] = new WxPayConfig();

        $order_id = (int)$id;

        $order = Orders::get(['id'=>$order_id]);

        $this->view->order = $order;
        return $this->view->fetch();
    }

    public function view_scan($id){

        global $_W;
        new WxPayDataBase();

        $order = Orders::get(['id'=>$id]);

        if(!$order){
            $this->error("对不起订单不存在！" , "/");
        }

        if($order['status']!='待支付'){
            $this->error("您好，订单已经支付完成了，无需重复支付！" , "/");
        }

        $this->view->to_url = url("member/index/main");




        //设置支付模式
        //todo mobile mode
        //JSAPI，NATIVE，APP

        $_W['WxPayConfig'] = WxPayConfig::get_config();
        if(!is_weixin()){
            $_W['pay_mode'] = "WX_NATIVE";
            $tools = new NativePay();
            $Trade_type = "NATIVE";
        }else{
            $_W['pay_mode'] = "WX_GZH";
            $tools = new JsApiPay();
            $Trade_type = "JSAPI";
        }


        $test = $this->Queryorder('' ,$order['trade_sn']);
        if($test['trade_state'] == 'SUCCESS'){
            $this->error("您好，订单已经支付完成了，无需重复支付！" , "/");
        }
        //订单已经关闭
        if($test['trade_state'] == "CLOSED"){
            $order->parent_id = 0;
            if($this->user['id']){
                $order->buyer_user_id = $this->user['id'];
            }
            $order->trade_sn = create_sn();
            $order->save();
        }


        //②、统一下单
        $input = new WxPayUnifiedOrder();

        $input->SetAppid($_W['WxPayConfig']['app_id']);
        $input->SetMch_id($_W['WxPayConfig']['mchid']);
        $input->SetBody($order['note'].'');
        $input->SetOut_trade_no($order['trade_sn']);
        $input->SetTotal_fee($order['total_fee'] * 100);
        $input->SetTime_start(date("YmdHis"));
        if(isset($order['sub_mch_id'])){
            $input->SetSubMchid($order['sub_mch_id']);
        }
        $input->SetProduct_id("1");
        $input->SetTime_expire(date("YmdHis", time() + 600));
        //$input->SetSpbill_create_ip("123.206.135.82");
        $input->SetGoods_tag($order['note'] . ".");
        $input->SetNotify_url(\think\Url::build("pay/api/call_back", ['gateway' => $order['gateway']], "", true));

        //pay/api/call_back/gateway/micropay.html
        $input->SetTrade_type($Trade_type);
        // 过滤post数组中的非数据表字段数据
        $input->SetAttach($id);//原样返还
        $result = $tools->GetPayUrl($input);
        if($result['return_code'] !="SUCCESS" || $result['result_code'] !="SUCCESS"){
            $order->trade_sn = create_sn();
            $order->save();
            $this->error("对不起，请求支付出错！" . $result['return_msg']);
        }
        if(Request::instance()->isAjax()){
            return $result;
        }else{

            $this->view->result = $result;
            $this->view->order = $order;
            return $this->view->fetch();
        }

    }

    public function Queryorder($transaction_id = "" , $out_trade_no = '')
    {
        $input = new WxPayOrderQuery();
        if($transaction_id){
            $input->SetTransaction_id($transaction_id);
        }
        if($out_trade_no){
            $input->SetOut_trade_no($out_trade_no);
        }
        $result = WxPayApi::orderQuery($input);

        return $result;
    }

}