<?php
namespace app\wechat\util;

use SimpleXMLElement;
use think\Cache;
use think\Log;


class WechatAppPlatform extends OpenPlatform {
    public $appid;
    public $appsecret;
    public $encodingaeskey;
    public $token;
    public $refreshtoken;
    public $account;

    /**
     * WeiXinPlatform constructor.
     * @param array $account
     */
    function __construct($account = array()) {
        parent::__construct($account);
        if (isset($this->account['app_id']) && $this->account['app_id'] == 'wx570bc396a51b8ff8') {
        //    $this->open_platform_test_case();
        }
        $this->account['account_appid'] = $this->account['app_id'];
        $this->account['key'] = $this->appid;
    }


    public function get_openid($code){


        $api_url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->app_id}&secret={$this->app_secret}&js_code=$code&grant_type=authorization_code";
        $res = $this->request($api_url);
    }



}