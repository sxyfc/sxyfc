<?php
// +----------------------------------------------------------------------
// | 鸣鹤CMS [ New Better  ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://www.mhcms.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( 您必须获取授权才能进行使用 )
// +----------------------------------------------------------------------
// | Author: new better <1620298436@qq.com>
// +----------------------------------------------------------------------

namespace app\common\model;

use anerg\helper\Http;

class Draw extends Common
{
    //
    public function getStatusTextAttr($value,$data)
    {
        $value = $this->status;
        $data = [0=>'等待处理',1=>'已经完成'];
        return $data[$value];
    }

    //企业打款
    public function send()
    {
        global $_W;
        //todo find the user's site and the user's openid
        $user = Users::get($this->user_id);
        $fans = $_W['wechat_fans_model']->where(['user_id' => $user['id']])->find();

        if (!$fans) {
            return false;
        }
        test($fans);

        $options = [
            'cert_path' => APP_PATH . ".." . DS . "upload_file" . DS . 'micropay' . DS . 'apiclient_cert.pem',
            'key_path' => APP_PATH . ".." . DS . "upload_file" . DS . 'micropay' . DS . 'apiclient_key.pem',
            'ca_path' => APP_PATH . ".." . DS . "upload_file" . DS . 'micropay' . DS . 'rootca.pem',
        ];
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";


        $data = [
            'mch_appid' => load_config('smallapp', 'app_id'),
            'mchid' => load_config('smallapp', 'mchid'),
            'device_info' => '',
            'amount' => $this->amount * 100,
            'nonce_str' => random(32),
            'partner_trade_no' => $this->id,
            'openid' => $fans['weixin_id'],
            'check_name' => 'FORCE_CHECK',
            're_user_name' => $this->name,
            'desc' => '用户提现',
            'spbill_create_ip' => '182.254.222.45',

        ];

        $data['sign'] = $this->MakeSign($data);
        $xml = $this->ToXml($data);
        $res = Http::postRawSsl($url, $xml, $options);
        return $this->FromXml($res);
    }

    public function send_redbag()
    {
        $user = Users::get($this->user_id);
        if (!$user->weixin_id) {
            return false;
        }
        $options = [
            'cert_path' => APP_PATH . ".." . DS . "upload_file" . DS . 'micropay' . DS . 'apiclient_cert.pem',
            'key_path' => APP_PATH . ".." . DS . "upload_file" . DS . 'micropay' . DS . 'apiclient_key.pem',
            'ca_path' => APP_PATH . ".." . DS . "upload_file" . DS . 'micropay' . DS . 'rootca.pem',
        ];
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $data = [
            'nonce_str' => random(32),
            'mch_billno' => $this->id,

            'mch_id' => load_config('weixin', 'mchid'),
            'wxappid' => load_config('weixin', 'app_id'),

            'send_name' => load_config('redbag', 'send_name'),
            're_openid' => $user->weixin_id,

            'total_amount' => $this->amount * 100,
            'total_num' => 1,
            'wishing' => load_config('redbag', 'wishing'),

            'client_ip' => '182.254.222.45',
            'act_name' => load_config('redbag', 'act_name'),
            'remark' => load_config('redbag', 'remark'),

            'scene_id' => 'PRODUCT_4',

        ];

        $data['sign'] = $this->MakeSign($data);
        $xml = $this->ToXml($data);
        $res = Http::postRawSsl($url, $xml, $options);
        test($res);
        return $this->FromXml($res);
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
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
    public function ToUrlParams($values)
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
    public function MakeSign($values)
    {
        //签名步骤一：按字典序排序参数
        ksort($values);
        $string = $this->ToUrlParams($values);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . load_config('weixin', 'key');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml($values)
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
