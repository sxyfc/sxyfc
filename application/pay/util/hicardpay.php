<?php
namespace app\pay\util;

use app\common\model\Users;
use app\orders\model\Orders;

class hicardpay
{

    public function __construct()
    {
        $this->config = load_config("huiyunpay");
        $this->app_id = $this->config['app_id']['value'];
        $this->app_secret = $this->config['app_secret']['value'];

    }

    /**
     * @param \app\orders\model\Orders $order
     * @return array
     */
    public function pay(\app\orders\model\Orders $order)
    {
        // 网页-无卡支付$payType = "016";
        $shaghu = Users::get($order['operator_id']);

        //
        if ($shaghu['creator_id'] && $shaghu['user_role_id'] == 12) {
            $shaghu = Users::get($shaghu['creator_id']);
        }

        if ($shaghu) {
            $shaghu->get_user_external();
            if (empty($shaghu->external['content'])) {
                die("商户还没有通过审核 。无法使用该功能");
            }
        } else {
            die();
        }
        // POST请求参数：按签名顺序，有序存放
        // alipay 009
        //wechat 011
        $order["payType"] = is_weixin() ? "014" : "009";
        $data["version"] = "V001";
        $data["organNo"] = $this->app_id;
        $data["hicardMerchNo"] = $shaghu->external['content'];
        if (!isset($order["payType"])) {
            $data["payType"] = "009";
        } else {
            $data["payType"] = $order["payType"];
        }
        $data["bizType"] = "01";
        $data["merchOrderNo"] = $order["order_id"];
        $data["showPage"] = 1;
        $data["amount"] = $order["amount"] * 100;
        $data["frontEndUrl"] = url("pay/api/call_back" ,['ext'=>"hicardpay"] , true , true);
        $data["backEndUrl"] = url("pay/api/call_back" ,['ext'=>"hicardpay"] , true , true);

        $data['sign'] = $this->sign($data);

        $data["openId"] = $order["openid"];
        $data["certsNo"] = '';
        $data["remark"] = $data["amount"];
        $data["reserved"] = "";
        $data["isT0"] = "1";
        $data["html"] = "0";

        $data["goodsName"] = urlencode($order['note']);

        $url = "http://www.hicardpay.com:8080/hicardpay/order/create";
        $resp = ihttp_post($url, json_encode($data));


        return $resp;
    }

    public function scan_pay(\app\orders\model\Orders $order)
    {
        // 网页-无卡支付$payType = "016";
        $shaghu = Users::get($order['operator_id']);

        if ($shaghu['creator_id'] && $shaghu['user_role_id'] == 12) {
            $shaghu = Users::get($shaghu['creator_id']);
        }

        if ($shaghu) {
            $shaghu->get_user_external();
            if (empty($shaghu->external['content'])) {
                die("商户还没有通过审核 。无法使用该功能");
            }
        } else {
            die();
        }
        // POST请求参数：按签名顺序，有序存放
        // alipay 009
        //wechat 011
        $data["version"] = "V001";
        $data["organNo"] = $this->app_id;
        $data["hicardMerchNo"] = $shaghu->external['content'];
        $data["merchOrderNo"] = $order["order_id"];
        $data["goodsName"] = "";
        $data["amount"] = $order["amount"] * 100;
        $data["authCode"] = $order["authCode"];
        $data["payType"] = $order["payType"];
        $data['sign'] = $this->sign($data);

        $data["isT0"] = "1";

        $url = "http://www.hicardpay.com:8080/hicardpay/order/unScan";
        $resp = ihttp_post($url, json_encode($data));


        return $resp;
    }

    private function sign($data, $linkstr = "&")
    {
        $new_params = [];
        foreach ($data as $k => $v) {
            $new_params[] = $k . "=" . $v;
        }
        $str_sign = join("&", $new_params) . $linkstr . $this->app_secret;
        return md5($str_sign);
    }

    private function sign2($data, $paykey = "")
    {
        $new_params = [];
        foreach ($data as $k => $v) {
            $new_params[] = $k . "=" . $v;
        }
        $str_sign = join("&", $new_params) . "&" . $this->app_secret;
        $sign = strtolower(md5($str_sign));
        //file_put_contents($data['merchOrderNo'] ."sign.txt" , $str_sign );
        return $sign;
    }

    public function call_back()
    {
        $data = input('get.');

        $data = input('post.');
        foreach ($data as $k => $v) {
            if (empty($v) && strpos($k, "version") !== false) {
                $data = json_decode($k, true);
            }
        }

        $_data['version'] = $data['version'];
        $_data['hicardMerchNo'] = $data['hicardMerchNo'];
        $_data['payType'] = $data['payType'];
        $_data['merchOrderNo'] = $data['merchOrderNo'];
        $_data['hicardOrderNo'] = $data['hicardOrderNo'];
        $_data['amount'] = $data['amount'];
        $_data['createTime'] = str_replace("_", " ", $data['createTime']);
        $_data['payTime'] = str_replace("_", " ", $data['payTime']);
        $_data['respCode'] = $data['respCode'];


        $order = Orders::get($_data['merchOrderNo']);
        if ($order && $data['sign'] == self::sign2($_data)) {
            if ($data['respCode'] == 00) {
                $order->status = 1;
                $order->payment_name = input('param.ext');
                $order->third_order_id = $data['hicardOrderNo'];
                $order->pay_time = $data['payTime'];
                $order->pay_type = $data['payType'] == "011" ? "微信" : "支付宝";
                $order->save();
                echo "SECCESS";
            }
        } else {
            echo "error";
        }

    }

    public function view($order)
    {

        $data["version"] = "V001";
        $shaghu = Users::get($order['operator_id']);
        if ($shaghu['creator_id']) {
            $shaghu = Users::get($shaghu['creator_id']);
            $shaghu->get_user_external();
        }

        $data["hicardMerchNo"] = $shaghu->external['content'];
        $data["merchOrderNo"] = $order["order_id"];

        $data["hicardOrderNo"] = $order['third_order_id'];

        $data["createTime"] = $order['create_time'];
        $data["sign"] = $this->sign2($data);
        $url = "http://www.hicardpay.com:8080/hicardpay/order/query";
        $resp = ihttp_post($url, json_encode($data));
        return $resp;

    }
}