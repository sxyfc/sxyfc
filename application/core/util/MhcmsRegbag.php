<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace app\core\util;

use anerg\helper\Http;
use app\common\model\Users;
use app\common\util\Money;
use app\pay\payment\micropay\utils\WxPayConfig;
use think\Db;
use think\Exception;
use think\Request;

class MhcmsRegbag
{


    //创建红包
    public static function create($data)
    {
        global $_W;
        //获取平均红包每个红包的个数
        $min_amount = max($_W['site']['config']['redbag']['min'], 0.01);
        $data['pass'] = trim($data['pass']);
        if ($min_amount > $data['money'] / $data['money']) {
            $ret['code'] = 0;
            $ret['msg'] = "对不起，平均每个红包最低金额不能低于$min_amount ，请增加红包金额或减少红包数量";
            return $ret;
        }

        if ($data['is_pass'] && empty($data['pass'])) {
            $ret['code'] = 0;
            $ret['msg'] = "对不起，开启口令红包必须填写口令";
            return $ret;
        }

        $insert = $data;

        //spend
        $res_spend = Money::spend($_W['user'], $data['money'], 0, '发布红包信息');

        if (!$res_spend) {
            $ret['code'] = 0;
            $ret['msg'] = "对不起，塞红包失败,可能是！";
            return $ret;
        }
        $insert['module'] = ROUTE_M;
        $insert['site_id'] = $_W['site']['id'];
        $insert['user_id'] = $_W['user']['id'];
        $id = set_model("redbag")->insert($insert, false, true);
        if ($id) {

            //todo create baglogs
            if ($data['is_average'] == 1) {
                //创建灯蛾红包
                $insert_log = [];
                $average = $data['money'] / $data['parts'];
                $insert_log['amount'] = sprintf("%.2f", substr(sprintf("%.3f", $average), 0, -2));
                $insert_log['user_id'] = 0;
                $insert_log['redbag_id'] = $id;
                $insert_log['site_id'] = $_W['site']['id'];


                for ($i = 1; $i <= $data['parts']; $i++) {

                    $log_res = set_model('redbag_logs')->insert($insert_log);
                    if (!$log_res) {
                        $ret['code'] = 0;
                        $ret['msg'] = "对不起，系统错误 红包记录创建失败！";
                        return $ret;
                    }
                }


            } else {

                $total_money = $data['money'];
                $left = $total_money;
                //创建随机红包
                //$_W['site']['config']['redbao']['min']
                for ($i = $data['parts']; $i > 0; $i--) {
                    $insert_log = [];
                    $insert_log['user_id'] = 0;
                    $insert_log['redbag_id'] = $id;
                    $insert_log['site_id'] = $_W['site']['id'];
                    if ($i > 1) {
                        //$min_amount = max(0.01 , $_W['site']['config']['redbao']['min']);
                        $amount = rand(1, $left * 100 / $i * 2) / 100;

                        if ($amount <= 0) {
                            $ret['code'] = 0;
                            $ret['msg'] = "对不起，系统错误 红包记录创建失败！";
                            return $ret;
                        }
                        $insert_log['amount'] = sprintf("%.2f", substr(sprintf("%.3f", $amount), 0, -1));
                    } else {
                        if ($left) {
                            $insert_log['amount'] = sprintf("%.2f", substr(sprintf("%.3f", $left), 0, -1));
                        }
                    }

                    if ($insert_log['amount'] > 0) {
                        $log_res = set_model('redbag_logs')->insert($insert_log);
                        $left = $left - $insert_log['amount'];
                        if (!$log_res) {
                            $ret['code'] = 0;
                            $ret['msg'] = "对不起，系统错误 红包记录创建失败！";
                            return $ret;
                        }
                    }


                }
            }
            // rand ( 0.01, m/n * 2 )


            $insert['id'] = $id;
            $ret['code'] = 1;
            $ret['redbag'] = $insert;
            return $ret;
        } else {
            $ret['code'] = 0;
            $ret['msg'] = "对不起，系统错误 红包创建失败！";
            return $ret;
        }

    }

    //抢红包
    public static function qhb($red_bag, $user)
    {
        global $_W;
        //check if the user has already got one
        $test_where = [];
        $test_where['redbag_id'] = $red_bag['id'];
        $test_where['user_id'] = $user['id'];
        $test_where['site_id'] = $_W['site']['id'];
        $test = set_model('redbag_logs')->where($test_where)->find();

        if (!$test) {
            Db::startTrans();
            //todo select all red_logs not got
            $test_where['user_id'] = 0;
            $red_bag_logs = set_model('redbag_logs')->where($test_where)->lock(true)->select()->toArray();

            //shuffle
            shuffle($red_bag_logs);
            $red_bag_log = $red_bag_logs[0];

            $red_bag_log['user_id'] = $user['id'];
            $red_bag_log['got_at'] = date("Y-m-d H:i:s" , SYS_TIME);
            $red_bag_log['ip'] = Request::instance()->ip();



            try{
                //first give the current user
                $res = set_model('redbag_logs')->where(['id' =>$red_bag_log['id'] ])->update($red_bag_log);
                //if the redbag is out  update the content model status
                if(count($red_bag_logs) == 1){
                    set_model($red_bag['model_id'])->where(['id' => $red_bag['item_id']])->update(['redbag_status' =>0 ]);
                }

                $res = Money::deposit($_W['user'] ,$red_bag_log['amount'] , 4 , '抢红包收入');
                if(!$res){
                    throw new Exception("存款错误");
                }
            }catch (Exception $e){
                Db::rollback();
                $ret['code'] = 0;
                $ret['msg'] =  $e->getMessage();
                return $ret;
            }
            Db::commit();
            $ret['code'] = 1;
            $ret['data'] = $red_bag_log;
            $ret['msg'] = "领取成功！";
            return $ret;
        } else {
            $ret['code'] = 0;
            $ret['msg'] = "您已经领过红包了！";
            return $ret;
        }
    }

    //提现发红包
    public static function send_redbag(Users $user , $red_bag_config )
    {
        global $_W;
        $fans = $_W['wechat_fans_model']->where(['user_id' => $user['id']])->find();

        if (!$fans) {
            return array('result_code'=>'FALSE','err_code_des'=>'找不到提现人的微信信息');
        }

        //load pay config
        $config = $_W['wx_pay_config'] = WxPayConfig::get_config();


        $options = [
            'cert_path' =>$config['SSLCERT_PATH'] ,
            'key_path' => $config['SSLKEY_PATH'] ,
            //'ca_path' => APP_PATH . ".." . DS . "upload_file" . DS . 'micropay' . DS . 'rootca.pem',
        ];
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $data = [
            'nonce_str' => random(32),
            'mch_billno' => $red_bag_config['withdraw_id'],

            'mch_id' => $config['mchid'],
            'wxappid' => $config["site_wechat"]['app_id'],

            'send_name' => $red_bag_config['send_name'],
            're_openid' => $fans['openid'],

            'total_amount' => floor($red_bag_config['amount'] * 100),
            'total_num' => 1,
            'wishing' => $red_bag_config['wishing'],

            // 'client_ip' => '182.254.222.45',

            'act_name' =>  $red_bag_config['act_name'] ,
            'scene_id' => 'PRODUCT_4',

        ];

        $data['sign'] = self::MakeSign($data);
        $xml = self::ToXml($data);
        $res = Http::postRawSsl($url, $xml, $options);
        $res = self::FromXml($res);
        return $res;
    }

    public static function FromXml($xml)
    {
        if (!$xml) {
            die();
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }


    /**
     * 格式化参数格式化成url参数
     */
    public static function ToUrlParams($values)
    {
        $buff = "";
        foreach ($values as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public static function MakeSign($values)
    {
        global $_W;
        //签名步骤一：按字典序排序参数
        ksort($values);
        $string = self::ToUrlParams($values);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $_W['wx_pay_config']['api_key'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 输出xml字符
     **/
    public static function ToXml($values)
    {
        if (!is_array($values)
            || count($values) <= 0
        ) {
            // throw new WxPayException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}