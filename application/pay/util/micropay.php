<?php
namespace app\pay\util;

use app\common\model\Sites;
use app\common\model\SitesWechat;
use app\common\model\Users;
use app\pay\payment\micropay\JsApiPay;
use app\pay\payment\micropay\utils\WxPayApi;
use app\pay\payment\micropay\utils\WxPayConfig;
use app\pay\payment\micropay\utils\WxPayDataBase;
use app\pay\payment\micropay\utils\WxPayNotify;
use app\pay\payment\micropay\utils\WxPayOrderQuery;
use app\pay\payment\micropay\utils\WxPayUnifiedOrder;
use app\common\util\Money;
use app\common\util\wechat\wechat;
use app\order\model\Orders;
use think\Log;

class micropay extends WxPayNotify
{
    private $data , $ext , $users_connect_model_id = 191;

    //查询订单

    /**
     * @param $transaction_id
     * @return bool
     * @throws \app\pay\payment\micropay\utils\WxPayException
     */
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS"
        ) {

            Log::write($result ,'log' , true);
            return true;
        }
        return false;
    }

    //重写回调处理函数

    /**
     * @param array $data
     * @param string $msg
     * @return bool
     * @throws \app\pay\payment\micropay\utils\WxPayException
     * @throws \think\exception\DbException
     */
    public function NotifyProcess($data, &$msg)
    {
        global $_W;
        //获取订单信息
        $this->data = $data;

        $notfiyOutput = array();
        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            Log::write($msg ,'log' , true);
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            Log::write($msg ,'log' , true);
            return false;
        }

        if ($this->order['status'] == "待支付") {
            $buyer = Users::get(['id' => $this->order['buyer_user_id']]); // deposit
            $this->order->status = '已支付';
            if($this->order->save()){
                if($this->order['buyer_user_id']){
                    Money::deposit($buyer, $this->order['total_fee'], 2 , $this->order['note'], ['order_id' => $this->order['id']]);
                }

            }
        }

        return true;

    }

    //pay an order

    /**
     * @return mixed
     * @throws \app\pay\payment\micropay\utils\WxPayException
     * @throws \think\exception\DbException
     */
    public function callback()
    {
        global $_W , $_GPC;
        $xml = $postData = file_get_contents('php://input');
        $data = $this->FromXml($xml);

        Log::write($data ,'log' , true);

        $_W['rest_data'] = $data;
        if ($data["out_trade_no"]) {
            $where["id"] = $data["attach"];
            $this->order = Orders::get($where);

            if($this->order ['pay_mode'] =="WX_GZH" ){
                $_W['WxPayConfig'] =   WxPayConfig::get_config();
            }
            if($this->order['pay_mode'] =="WX_XCX" ){
                $_W['WxPayConfig'] =   WxPayConfig::get_mini_config($_W['rest_data']['appid']);
            }


            $_W['site'] = Sites::get(['id'=> $this->order['site_id']]);
            $_W['pay_mode'] = $this->order['pay_mode'];
            //预处理完成
            $this->Handle(false, $data, $msg);
        }else{
            Log::write("No out_trade_no");
        }



    }


    public function set_ext($ext){
        $this->ext = $ext;
    }


    /**
     * @param Orders $order
     * @param $client
     * @param $call_back_url
     * @return bool|string
     */
    public function pay($order , $client , $call_back_url , $openId = 0){
        new WxPayDataBase();

        $model = set_model($this->users_connect_model_id);

        if(!$openId){
            if($client == "WX_GZH"){
                $user = $model->where(['user_id'=>$order['user_id'] , 'connect_id' => 1 ])->find();
                $openId= $user['weixin_id'];
            }

            if($client == "WX_XCX"){
                $site_wechat = SitesWechat::get(['site_id'=>$order['site_id']]);
                $user = $model->where(['user_id'=>$order['user_id'] , 'connect_id' => 2 ])->find();
                $openId= $user['openid'];
            }
        }


        if(!$openId){
            return false;
        }
        $order['openid'] = $openId;
        $order['trade_sn'] = create_sn();
        $order->save();
        $tools = new JsApiPay();
        // 1.OPenid
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($order['note']);
        $input->SetOut_trade_no($order['trade_sn']);
        $input->SetTotal_fee($order['total_fee'] * 100);
        $input->SetTime_start(date("YmdHis"));
        //$input->SetSubMchid($order['sub_mch_id']);
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($order['note'] . ".");
        $input->SetNotify_url($call_back_url);
        //$input->SetNotify_url();

        //pay/api/call_back/gateway/micropay.html
        $input->SetTrade_type("JSAPI");
        //
        //$input->SetSubOpenid($openId);
        $input->SetOpenid($openId);
        // 过滤post数组中的非数据表字段数据
        $input->SetAttach($order['order_id']);//原样返还
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        //$editAddress = $tools->GetEditAddressParameters();
        //$this->view->editAddress = $editAddress;
        return $jsApiParameters;
    }

}