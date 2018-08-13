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
namespace app\sms\providers;

use app\sms\providers\Aliyun\SignatureHelper;
use Flc\Alidayu\App;
use Flc\Alidayu\Client;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;

class Aliyun extends Providers
{

    private $config;

    /**
     * Alidayu constructor.
     * @param $sms_site_config
     */
    public function __construct($sms_site_config)
    {
        global $_W;
        //todo init config
        $this->config = json_decode($sms_site_config['config'], 1);
    }


    function send($target, $params, $tpl_id)
    {
        global $_W;
        $sms_params = array();

        // *** 需用户填写部分 ***
        $accessKeyId = config('alidayu.app_key');
        $accessKeySecret = config('alidayu.app_secret');
        $sms_params["PhoneNumbers"] = $target;
        $sms_params["SignName"] = config('alidayu.signature');

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $sms_params["TemplateCode"] = "$tpl_id";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $sms_params['TemplateParam'] = $params;

        // fixme 可选: 设置发送短信流水号
        $sms_params['OutId'] = "";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$sms_params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($sms_params["TemplateParam"]) && is_array($sms_params["TemplateParam"])) {
            $sms_params["TemplateParam"] = json_encode($sms_params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $resp_sms = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($sms_params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        if ($resp_sms->Message == "OK" && $resp_sms->Code == "OK") {
            $resp['code'] = 1;
            $resp['msg'] = "短信发送成功";
        } else {
            $resp['code'] = 0;
            $resp['msg'] = "短信发送失败" .  $resp_sms->Message;
            $resp['data'] = $resp_sms;
        }

        return $resp;
    }
}