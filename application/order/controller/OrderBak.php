<?php

namespace app\order\controller;

use app\common\controller\ModuleBase;
use app\common\controller\ModuleUserBase;
use app\common\model\Node;
use app\common\model\NodeSetting;
use app\common\model\Product;
use app\common\model\SitesWechat;
use app\common\model\Users;
use app\common\model\WeixinMsgtpl;
use app\pay\payment\micropay\JsApiPay;
use app\pay\payment\micropay\NativePay;
use app\pay\payment\micropay\utils\WxPayApi;
use app\pay\payment\micropay\utils\WxPayConfig;
use app\pay\payment\micropay\utils\WxPayDataBase;
use app\pay\payment\micropay\utils\WxPayOrderQuery;
use app\pay\payment\micropay\utils\WxPayUnifiedOrder;
use app\common\util\wechat\wechat;
use app\order\model\Orders;
use app\order\model\OrdersLogs;
use app\order\model\OrdersProduct;
use think\Request;

class OrderBak extends ModuleBase {


    public function create_product_order(){
        $order = [];

        $order_data = json_decode( input('param.data') , true);
        $product= Product::where(['id'=>$order_data['product_id']])->find();
        if(!$product){
            return [
                'code' => 404 ,
                'msg' => '商品不存在'
            ];
        }
        $product_data = $this->node->get_node($product['node_id']);
        $product_setting =  NodeSetting::get($product_data['node_id']);

        //cal fee
        if($order_data['delivery']!="当面交易"){
            $express_fee = $product_setting['express_fee'];

        }else{
            $express_fee = 0;
        }


        $order['total_fee'] =$product['price'] + $express_fee;
        $order['express_fee'] =$express_fee;


        $order['delivery'] = $order_data['delivery'];

        //common info

        $order['address'] = $order_data['address'];
        $order['receiver'] = $order_data['name'];
        $order['mobile'] = $order_data['mobile'];

        $order['order_id'] = $order['trade_sn'] = create_sn();
        $order['buyer_user_id'] = $this->user['id'];
        $order['seller_user_id'] = $product_data['user_id'];
        $order['payment_type'] = $gateway = $order_data['payment'];
        $order['create_time'] = date("Y-m-d H:i:s");
        $order['create_ip'] = $this->request->ip();
        $order['note'] = $order_data['desc'];
        $order['site_id'] = $this->site['id'];


        if($order['seller_user_id'] ==  $order['buyer_user_id']){
            return [
                'code' => 2 ,
                'msg' => '你不可以买自己的东西' . $product_data['user_id']
            ];
        }


        //create order
        $order = Orders::create($order);
        //order product

        $order_product = [];
        $order_product['order_id'] = $order['order_id'] ;
        $order_product['price'] = $product['price'] ;
        $order_product['product_id'] = $product['id'] ;
        $order_product['amount'] = 1 ;
        $o_d = OrdersProduct::create($order_product);
        //

        $seller = Users::get(['user_id'=>$order['seller_user_id']]);

        if($o_d && $order){
            $weixin_msg =  WeixinMsgtpl::get(['tpl_name' => '新订单通知']);

            $this->mapping['create_time'] = $order['create_time'];
            $this->mapping['buyer_nickname'] = $seller['nickname'];
            $this->mapping['order_id'] = $order['order_id'];
            $this->mapping['product_name'] = $product_data['title'];
            $this->mapping['price'] = $order['total_fee'];
            $this->mapping['trade_method'] = $order['delivery'];

            $weixin_msg->send($seller['weixin_id'], $this->mapping);
            $ret = [
                'code' => 0 ,
                'msg' => '订单提交成功'
            ] ;
            $ret['url'] = url('order/index/view').'?order_id=' . $order['order_id'];
        }else{
            $ret = [
                'code' => 2 ,
                'msg' => '订单创建失败'
            ] ;
        }

        return $ret;
    }


    public function view($order_id){

        new WxPayDataBase();

        $order_product = OrdersProduct::get(['order_id'=>$order_id]);
        $product = Product::get(['id'=>$order_product['product_id']]);
        $product_data = $this->node->get_node($product['node_id']);


        $order = Orders::get(['order_id'=>$order_id]);
        if(!$order){
            $this->error("对不起订单不存在！" , "/");
        }
        if($order->buyer_user_id !=$this->user['id']){
            $order->pay_user_id = $this->user['id'];
            $order->save();
        }

        //todo 查询订单是否存在


        $order['trade_sn'] = create_sn();
        $order->save();

        $tools = new JsApiPay();
        $payment = "micropay";

        // 1.OPenid
        $site_wechat = SitesWechat::get(['site_id'=>$this->site['site_id']]);
        $wechat = new wechat($site_wechat);
        $base_info = $wechat->get_base_info();
        $openId =$base_info['openid'];

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($product_data['title'] . "。");
        $input->SetOut_trade_no($order['trade_sn']);
        $input->SetTotal_fee($order['total_fee'] * 100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetSubMchid($order['sub_mch_id']);
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($order['note'] . ".");
        $input->SetNotify_url(\think\Url::build("pay/api/call_back", ['gateway' => 'micropay'], "", true));

        //pay/api/call_back/gateway/micropay.html
        $input->SetTrade_type("JSAPI");
        //
        $input->SetSubOpenid($openId);
        //$input->SetOpenid($openId);
        // 过滤post数组中的非数据表字段数据
        $input->SetAttach($order_id);//原样返还
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        //$editAddress = $tools->GetEditAddressParameters();
        //$this->view->editAddress = $editAddress;
        $this->view->jsApiParameters = $jsApiParameters;
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




        $_W['WxPayConfig'] = new WxPayConfig();

        //设置支付模式
        //todo mobile mode
        //JSAPI，NATIVE，APP
        if(!is_weixin()){
            $_W['pay_mode'] = "WX_NATIVE";
            $tools = new NativePay();
            $Trade_type = "NATIVE";
        }else{
            $_W['pay_mode'] = "WX_GZH";
            $tools = new JsApiPay();
            $Trade_type = "JSAPI";
        }


        $test = $this->Queryorder('' ,$order['out_trade_no']);
        if($test['trade_state'] == 'SUCCESS'){
            $this->error("您好，订单已经支付完成了，无需重复支付！" , "/");
        }
        //订单已经关闭
        if($test['trade_state'] == "CLOSED"){
            $order->parent_id = 0;
            if($this->user['id']){
                $order->buyer_user_id = $this->user['id'];
            }
            $order->out_trade_no = $order['id'] . "@" . time();
            $order->save();
        }


        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody( $order['year'] . " 年 ". $order['month']. $order['note']);
        $input->SetOut_trade_no($order['out_trade_no']);
        $input->SetTotal_fee($order['total_fee'] * 100);
        $input->SetTime_start(date("YmdHis"));
        if(isset($order['sub_mch_id'])){
            $input->SetSubMchid($order['sub_mch_id']);
        }
        //$input->SetProduct_id("1");
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

    public function create($operator_id)
    {
        $gateway = input('param.ext');
        if (!$operator_id || !$gateway) {
            Die("系统出错 ， 未知的商家");
        }
        $data['amount'] = input('param.amount', 0, 'trim');
        $data['operator_id'] = input('param.operator_id');
        $data['is_online'] = input('param.is_online', 0, "intval");
        $data['note'] = input('param.remark');
        $data['payment_name'] = $gateway;
        $data['create_time'] = date('Y-m-d H:i:s');
        $tools = new JsApiPay();
        $data['openid'] = $tools->GetOpenid();

        $order = Orders::create($data);
        $gateway = "\\app\\pay\\util\\$gateway";
        $gateway = new $gateway();

        $res = $gateway->pay($order);
        $res = json_decode( $res['content'] , true);

        $this->view->res = $res;
        return $this->view->fetch();
    }

    public function scan_create($operator_id)
    {
        $gateway = input('param.ext');
        if (!$operator_id || !$gateway) {
            Die("系统出错 ， 未知的商家");
        }
        $data['amount'] = input('param.amount', 0, 'trim');
        $data['operator_id'] = input('param.operator_id');
        $data['is_online'] = input('param.is_online', 0, "intval");
        $data['note'] = input('param.remark' , "" , "htmlspecialchars");

        $data['create_time'] = date('Y-m-d H:i:s');
        $data['payment_name'] = $gateway;
        $order = Orders::create($data);

        $order['authCode'] = input('param.authCode');
        $order['payType'] = input('param.paytype');
        $gateway = "\\app\\pay\\util\\$gateway";
        $gateway = new $gateway();
        $res = $gateway->scan_pay($order);
        echo $res['content'];
    }

    public function scan_create_xcx()
    {
        $gateway = input('param.ext');

        $data = input('param.data/a');
        $operator_id = $data['operator_id'];

        if (!$operator_id || !$gateway) {
            Die("系统出错 ， 未知的operator_id");
        }
        if($operator_id!=$this->user->user_id){
            Die("系统出错 ， 未知的user_id");
        }
        if(!is_numeric($data['amount'])){
            Die("系统出错 ， 未知的amount");
        }
        $order['operator_id'] = $operator_id;
        $order['amount'] = $data['amount'];

        $order['is_online'] = 0;
        $order['note'] = "";

        $order['create_time'] = date('Y-m-d H:i:s');
        $order['payment_name'] = $gateway;

        $order = Orders::create($order);

        $order['authCode'] = $data['authCode'];
        $order['payType'] = $data['paytype'];
        $gateway = "\\app\\pay\\util\\$gateway";
        $gateway = new $gateway();
        $res = $gateway->scan_pay($order);
        echo $res['content'];

    }


    /**
     * 取消订单
     * @param $order_id
     * @throws \think\exception\DbException
     */
    public function cancle($order_id){
        $order = Orders::get(['order_id'=>$order_id]);
        $order->cancle();
    }


    /**
     * 订单改价格
     * @param $order_id
     * @return array|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function change($order_id){
        if($this->isPost()){

            $data = json_decode( input("param.data") , true) ;
            if(!is_numeric($data['price'])){
                return [
                    'code' => 2 ,
                    'msg' => "必须位数字才可以"
                ];
            }else{

                $order = Orders::get(['order_id'=>$order_id]);
                $order->total_fee = $data['price'];
                $order->trade_sn = create_sn();


                if($order->save()){
                    $order->close();
                    $log = [
                        'order_id' => $order_id,
                        'description' => "卖家修改价格为" . $data['price'] ,
                        'create_time' => date("Y-m-d H:i:s"),
                        'user_id' => $this->user['id'],
                    ];
                    OrdersLogs::create($log);

                    $weixin_msg =  WeixinMsgtpl::get(['tpl_name' => '订单改价通知']);
                    $buyer = Users::get(['user_id'=>$order->buyer_user_id]);
                    //$seller
                    $node = new Node();
                    $order_product = OrdersProduct::get(['order_id'=>$order->order_id]);
                    $product = Product::get(['id'=>$order_product['product_id']]);
                    $product_data = $node->get_node($product['node_id']);

                    $mapping = [];
                    $mapping['order_id'] = $order->order_id;
                    $mapping['price'] = $order->total_fee;
                    $mapping['product_name'] = $product_data['title'];
                    $mapping['create_time'] = $order->create_time;
                    $mapping['order_status'] = $order->status;
                    $weixin_msg->send($buyer['weixin_id'] , $mapping);

                    return [
                        'code' => 0 ,
                        'msg' => "改价成功"
                    ];
                }

                return [
                    'code' => 2 ,
                    'msg' => "改价失败，未知错误！"
                ];

            }
        }else{
            return $this->view->fetch();
        }

    }
}