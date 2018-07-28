<?php
namespace app\order\model;
use app\common\model\Common;
use app\common\model\Users;
use app\common\model\WeixinMsgtpl;
use app\pay\payment\micropay\utils\WxPayCloseOrder;
use app\pay\payment\micropay\utils\WxPayApi;
use app\pay\payment\micropay\utils\WxPayConfig;
use app\pay\payment\micropay\utils\WxPayDataBase;
use app\pay\payment\micropay\utils\WxPayRefund;
use app\common\util\Money;
use think\Log;

/**
 * Class Orders
 * @package app\order\model
 *
 * total amount of un finished orders ,is the amount of fee that is freezd for the current buyer
 */
class Orders extends Common {



    /**
     * 订单确认收货
     * @param $user_id
     * @return bool
     * @throws \think\exception\DbException
     */

    public function complete($user_id){
        if($this->buyer_user_id ==$user_id){
            $this->status = "已完成";
            $seller= Users::get($this->seller_user_id);
            if($this->save()){
                $res = Money::deposit($seller ,$this->total_fee, 2 , "订单交易成功 " ,['order_id' => $this->order_id ]);

                //todo send msg
                $weixin_msg =  WeixinMsgtpl::get(['tpl_name' => '订单确认收货通知']);
                //$buyer = Users::get(['user_id'=>$this->buyer_user_id]);
                //$seller


                $node = new Node();
                $order_product = OrdersProduct::get(['order_id'=>$this->order_id]);
                $product = Product::get(['id'=>$order_product['product_id']]);
                $product_data = $node->get_node($product['node_id']);


                $mapping = [];
                $mapping['price'] = $this->total_fee;
                $mapping['product_name'] = $product_data['title'];
                $mapping['create_time'] = $this->create_time;
                $mapping['deliver_time'] = date("Y-m-d H:i:s");
                $mapping['complete_time'] = date("Y-m-d H:i:s");

                $weixin_msg->send($seller['weixin_id'] , $mapping);

                return $res;
            }
        }
        return false;
    }


    /**
     * @param $user_id
     * @return bool
     */
    public function deny($user_id){
        if($this->seller_user_id == $user_id){
            if($this->status == "退款中"){
                $this->status = "客服处理";
                if($this->save()){
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * 付款完成
     * @throws \think\exception\DbException
     */
    public  function pay(){
        $buyer = Users::get($this->buyer_user_id);
        if($buyer){
            $res = Money::spend($buyer ,$this->total_fee , 0 ,  "缴纳党费", ['order_id' => $this->order_id]);
        }else{
            $res = true;
        }

        if ($res) {
            //第一 消费买家资金
            $this->status = "已支付";
            $this->pay_time =  date("Y-m-d H:i:s" , time());
            $res = $this->isUpdate(true)->save();
            // 批量支付子帐单
            $where = [];
            $where['parent_id'] = $this->order_id;
            $update_data = [];
            $update_data['status'] = "已支付";
            $update_data['pay_time'] =date( "Y-m-d H:i:s");
            Orders::update($update_data , $where);

            if($res){
                $notice =  Notice::get(['tpl_name' => '缴费成功通知']);

                if($buyer){
                    $node = Node::get(['node_id' => $this->node_id]);
                    $mapping = [];
                    $mapping['price'] = $this->total_fee;
                    $mapping['product_name'] = $node['title'];
                    $notice->send_wxmsg($buyer['weixin_id'] , $mapping);
                }

                if($this->node_id){
                    $where['node_id'] = $this->node_id;
                    $node = Node::get(['node_id' => $this->node_id]);
                    //todo send sms
                    $params = [];
                    if(is_phone($node['contact'])){
                        $params['name'] = $node['title'];
                        $params['n'] = $this->month ;;
                        $params['money'] =$this->total_fee ;
                        $notice->send_sms($node['contact'] , $params );
                    }
                }
            }
        }
    }

    /**
     *取消订单
     * @throws \think\exception\DbException
     */
    public  function cancle($user_id , $amount = 0){
        //
        if($this->buyer_user_id == $user_id){
            //buyer
            $this->status = "退款中";

            //send msg
            $weixin_msg =  WeixinMsgtpl::get(['tpl_name' => '订单申请取消通知']);
            $seller = Users::get(['user_id'=>$this->seller_user_id]);
            //$seller
            $node = new Node();
            $order_product = OrdersProduct::get(['order_id'=>$this->order_id]);
            $product = Product::get(['id'=>$order_product['product_id']]);
            $product_data = $node->get_node($product['node_id']);


            $mapping = [];
            $mapping['price'] = $this->total_fee;
            $mapping['product_name'] = $product_data['title'];
            $mapping['order_id'] = $this->order_id;
            $weixin_msg->send($seller['weixin_id'] , $mapping);


            $this->save();
            return true;
        }elseif($this->seller_user_id == $user_id){
            //seller
            $refund = 0;
            if($this->status="待发货" || $this->status="已发货" || $this->status="退款中"){
                $this->status = "已退款";
                $refund = 1;
            }elseif($this->status="待付款"){
                $this->status = "已关闭";
            }else{
                return false;
            }
            //是否退款
            if($refund){
                $buyer = Users::get($this->buyer_user_id);
                if(!$amount){
                    $amount = $this->total_fee;
                }
                //todo :直接退回至用户微信余额
                if($this->payment_type == "micropay"){
                    new WxPayDataBase();
                    $wxpay_config = new WxPayConfig();
                    if(isset($this->order_id) && $this->order_id != ""){
                        $out_trade_no = $this->trade_sn;
                        $total_fee = $this->total_fee * 100;
                        $refund_fee = $this->total_fee * 100;
                        $input = new WxPayRefund();
                        $input->SetOut_trade_no($out_trade_no);
                        $input->SetTotal_fee($total_fee);
                        $input->SetRefund_fee($refund_fee);
                        $input->SetOut_refund_no($this->order_id);
                        $input->SetOp_user_id($wxpay_config->MCHID);
                        $res = WxPayApi::refund($input);
                        if($res['result_code'] == "FAIL"){
                            return false;
                        }
                    }
                }else{
                    //存入余额
                    Money::deposit($buyer , $amount , 2 , "订单退款 " ,['is_online' => 1]);
                }
            }


            //send msg
            $weixin_msg =  WeixinMsgtpl::get(['tpl_name' => '退款通知']);
            $buyer = Users::get(['user_id'=>$this->buyer_user_id]);
            //$seller
            $node = new Node();
            $order_product = OrdersProduct::get(['order_id'=>$this->order_id]);
            $product = Product::get(['id'=>$order_product['product_id']]);
            $product_data = $node->get_node($product['node_id']);


            $mapping = [];
            $mapping['price'] = $this->total_fee;
            $mapping['product_name'] = $product_data['title'];
            $mapping['log'] = $product_data['title'];
            $mapping['order_id'] = $this->order_id;
            $weixin_msg->send($buyer['weixin_id'] , $mapping);

            $this->save();
            return true;
        }else{
            //没有权限
            return false;
        }
    }

    /**
     *发货订单
     */
    public  function deliver(){
        if ($this->status == '待发货' || $this->status == '退款中') {
            $this->status = "已发货";

            //send msg
            $buyer = Users::get($this->buyer_user_id);

            $weixin_msg =  WeixinMsgtpl::get(['tpl_name' => '订单发货通知']);

            $seller = Users::get(['user_id'=>$this->buyer_user_id]);

            //$seller
            $node = new Node();
            $order_product = OrdersProduct::get(['order_id'=>$this->order_id]);
            $product = Product::get(['id'=>$order_product['product_id']]);
            $product_data = $node->get_node($product['node_id']);


            $mapping = [];
            $mapping['price'] = $this->total_fee;
            $mapping['product_name'] = $product_data['title'];
            $order_express = OrdersExpress::get(['order_id'=>$this->order_id]);

            $mapping['express_name'] = $order_express['express_name'];
            $mapping['express_no'] = $order_express['express_no'];

            $mapping['buyer_nickname'] = $this->receiver;
            $mapping['mobile'] = $this->mobile;
            $mapping['address'] = $this->address;

            $weixin_msg->send($buyer['weixin_id'] , $mapping);


            $res = $this->save();
        }
    }

}