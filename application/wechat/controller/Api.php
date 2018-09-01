<?php
namespace app\wechat\controller;

use app\common\controller\ApiUserBase;
use app\order\model\Orders;
use app\pay\payment\micropay\JsApiPay;
use app\pay\payment\micropay\utils\WxPayApi;
use app\pay\payment\micropay\utils\WxPayConfig;
use app\pay\payment\micropay\utils\WxPayDataBase;
use app\pay\payment\micropay\utils\WxPayUnifiedOrder;

use app\common\util\Money;
use app\common\model\Users;
use think\Cookie;


/**
 * @property int node_type_id
 */
class Api extends ApiUserBase
{
    public function get_pay_params(){

        global $_W, $_GPC;
        $query = $_GPC['query'];
        if(!is_array($query)){
            $query = mhcms_json_decode($query);
        }
        $order = Orders::get(['id'=>$query['order_id']]);
        $site_id = $_GPC['site_id'] ? $_GPC['site_id'] : $_W['site']['id'];
        new WxPayDataBase();
        if(!$order){
            $this->error("对不起订单不存在！" , "/");
        }
        if($order->buyer_user_id !=$this->user['id']){
            $order->pay_user_id = $this->user['id'];
        }
        //todo 查询订单是否存在

        $_W['pay_mode'] = "WX_GZH";
        $_W['WxPayConfig'] =   WxPayConfig::get_config();
        $order->save();

        $tools = new JsApiPay();
        if (empty($_W['openid'])) {
            $openid = Cookie::get("openid");
            if (empty($openid)) {
                $ret['code'] = -1;
                $ret['data'] = array('err_code_des'=>'请先绑定微信');
                echo json_encode($ret);
                exit();
            } else {
                $_W['openid'] = $openid;
            }
        }
        $openId = $_W['openid'];
        // echo json_encode(array('openid'=>$openId));die;

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetAppid($_W['WxPayConfig']['app_id']);
        $input->SetBody($_W['site']['config']['trade']['balance_text'].'充值');
        $input->SetMch_id($_W['WxPayConfig']['mchid']);
        $input->SetOut_trade_no($order['trade_sn']);
        $input->SetTotal_fee($order['amount'] * 100);
        $input->SetTime_start(date("YmdHis"));
        //$input->SetSubMchid($order['sub_mch_id']);
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($_W['site']['config']['trade']['balance_text'].'充值');
        $input->SetNotify_url(url("pay/api/call_back", ['gateway' => 'micropay'], "", true));

        //pay/api/call_back/gateway/micropay.html
        $input->SetTrade_type("JSAPI");
        //
        //$input->SetSubOpenid($openId);
        $input->SetOpenid($openId);
        // 过滤post数组中的非数据表字段数据
        $input->SetAttach($order['id']);//原样返还
        $_order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($_order);
        //获取共享收货地址js函数参数
        //$editAddress = $tools->GetEditAddressParameters();
        //$this->view->editAddress = $editAddress;

        $ret_data = mhcms_json_decode($jsApiParameters) ;
        if(is_array($ret_data)){
            $ret['code'] = 1;
            $ret['data'] =$ret_data;
        }else{
            $ret['code'] = 0;
            $ret['data'] =$jsApiParameters;
        }
        if ($ret['data']['err_code_des'] == '该订单已支付') {
            $this->do_deposit($query['order_id']);
            // $this->error($ret['data']['err_code_des'], "/member/wallet");
        }

        echo json_encode($ret);exit();
    }

    public function query_order($id) {
        global $_W, $_GPC;
        $order = Orders::get(['id'=>$id]);
        $site_id = $_GPC['site_id'] ? $_GPC['site_id'] : $_W['site']['id'];
        new WxPayDataBase();
        if(!$order){
            $this->error("对不起订单不存在！" , "/");
        }
        if($order->buyer_user_id !=$this->user['id']){
            $order->pay_user_id = $this->user['id'];
        }
        //todo 查询订单是否存在

        $_W['pay_mode'] = "WX_GZH";
        $_W['WxPayConfig'] =   WxPayConfig::get_config();
        $order->save();

        $tools = new JsApiPay();
        $openId = $_W['openid'];

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetAppid($_W['WxPayConfig']['app_id']);
        $input->SetBody($_W['site']['config']['trade']['balance_text'].'充值');
        $input->SetMch_id($_W['WxPayConfig']['mchid']);
        $input->SetOut_trade_no($order['trade_sn']);
        $input->SetTotal_fee($order['amount'] * 100);
        $input->SetTime_start(date("YmdHis"));
        //$input->SetSubMchid($order['sub_mch_id']);
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($_W['site']['config']['trade']['balance_text'].'充值');
        $input->SetNotify_url(url("pay/api/call_back", ['gateway' => 'micropay'], "", true));

        //pay/api/call_back/gateway/micropay.html
        $input->SetTrade_type("JSAPI");
        //
        //$input->SetSubOpenid($openId);
        $input->SetOpenid($openId);
        // 过滤post数组中的非数据表字段数据
        $input->SetAttach($order['id']);//原样返还
        $_order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($_order);
        //获取共享收货地址js函数参数
        //$editAddress = $tools->GetEditAddressParameters();
        //$this->view->editAddress = $editAddress;

        if(is_array($jsApiParameters)){
            $ret = $jsApiParameters;
        }else{
            $ret = mhcms_json_decode($jsApiParameters);
        }
        return $ret;
    }

    public function call_back() {
        global $_W, $_GPC;
        $id = $_GPC['id'];
        $test = $this->query_order($id);
        if($test['err_code_des'] == '该订单已支付' ){
            $this->do_deposit($id);
        }
    }

    private function do_deposit($id) {
        $where = array('id'=>$id);
        $result = set_model('orders')->where($where)->find();
        if ($result['status'] != '已支付') {
            $data = [];
            $result['status'] = $data['status'] = '已支付';
            $data['pay_time'] = date('Y-m-d H:i:s', time());
            $res = set_model('orders')->where($where)->update($data);
            $buyer = Users::get(['id' => $result['buyer_user_id']]);
            if($buyer){
                Money::deposit($buyer, $result['total_fee'], 2 , $result['note'], ['order_id' => $result['id']]);
            }
        }
    }
}