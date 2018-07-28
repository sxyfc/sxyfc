<?php

namespace app\pay\util;

use app\common\util\forms\input;
use app\order\model\Orders;
use think\Cache;

class phpbank
{

    const UserId = "6905";
    const Key = "1a4c31d7543a9145d998a0af6249f237";
    const Api_url = "http://wx.yzch.net/Pay.aspx";
    public  $call_back_url;
    public  $ext;

    public  function pay(Orders $order)
    {
        $userid = self::UserId;//用户ID（www.yzch.net获取）
        $orderid = $order["id"];//用户订单号（必须唯一）
        $money = $order["total_fee"];//订单金额
        $bankid = input('param.bank_id');//银行ID（见文档）
        $keyvalue = self::Key;//用户key（www.yzch.net获取）
        $reutrn_url = $this->call_back_url;//用户接收返回URL连接
        $ext = $this->ext;
        $submiturl = self::Api_url;
        $sign = "userid=" . $userid . "&orderid=" . $orderid . "&bankid=" . $bankid . "&keyvalue=" . $keyvalue;
        $sign2 = "money=" . $money . "&userid=" . $userid . "&orderid=" . $orderid . "&bankid=" . $bankid . "&keyvalue=" . $keyvalue;
        $sign = md5($sign);//签名数据 32位小写的组合加密验证串
        $sign2 = md5($sign2);//签名数据2 32位小写的组合加密验证串
        $url = $submiturl . "?userid=" . $userid . "&orderid=" . $orderid . "&money=" . $money . "&url=" . $reutrn_url . "&bankid=" . $bankid . "&sign=" . $sign . "&ext=" . $ext . "&sign2=" . $sign2;
        header('Location:' . $url);
    }


    public static function call_back()
    {

        ini_set('date.timezone', 'Asia/Shanghai');
        //echo md5('admin'); //请确认为 21232f297a57a5a743894a0e4a801fc3 echo '</br>';
        $keyvalue = self::Key;// "a051d608b85e9aab6a899fb2f0b9a663";//;用户中心获取
        $returncode = $_GET["returncode"];

        $userid = $_GET["userid"]; //order.UserId.ToString();

        $orderid = $_GET["orderid"];//order.UserOrderNo;
        $order = \app\pay\model\Orders::get($orderid);
        $money = $_GET["money"];//order.OrderMoney.ToString();
        if ($order['total_fee'] != $money) {
            \think\Cache::set("error_set" . $orderid , $money);
            die();
        }
        $sign = $_GET["sign"];
        $sign2 = $_GET["sign2"];
        if (!isset($sign2) && empty($sign2)) {
            echo 'param error';
            exit;
        }
        $ext = $_GET["ext"];//order.Ext;



        //echo $localsign; echo '</br>';
        $localsign = "returncode=" . $returncode . "&userid=" . $userid . "&orderid=" . $orderid . "&keyvalue=" . $keyvalue;
        //echo $localsign;  echo '</br>';
        $localsign2 = "money=" . $money . "&returncode=" . $returncode . "&userid=" . $userid . "&orderid=" . $orderid . "&keyvalue=" . $keyvalue;

        $localsign = md5($localsign);
        $localsign2 = md5($localsign2);

        //echo $sign; echo '</br>'; echo $localsign;echo '</br>';

        if ($sign != $localsign) {
            echo 'sign error';
            exit;            //加密错误
        }
        //注意这个带金额的加密 判断 一定要加上，否则非常危险 ！！
        if ($sign2 != $localsign2) {
            echo 'sign2 error';
            exit;            //加密错误
        }


        switch ($returncode) {
            case "1"://成功
                $order->complete();

                //成功逻辑处理，现阶段只发送成功的单据
                echo 'ok';
                break;
            default:
                //失败
                break;
        }


    }

    private static  function format()
    {
        $args = func_get_args();
        if (count($args) == 0) {
            return;
        }
        if (count($args) == 1) {
            return $args[0];
        }
        $str = array_shift($args);
        $str = preg_replace_callback('/\\{(0|[1-9]\\d*)\\}/', create_function('$match', '$args = ' . var_export($args, true) . '; return isset($args[$match[1]]) ? $args[$match[1]] : $match[0];'), $str);
        return $str;
    }
}