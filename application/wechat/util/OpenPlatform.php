<?php

namespace app\wechat\util;

use SimpleXMLElement;
use think\Cache;

class OpenPlatform extends MhcmsWechatAccountBase
{
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
    function __construct($account = array())
    {
        $setting = setting_load('wechat_platform');
        $this->appid = $setting['app_id'];
        $this->appsecret = $setting['app_secret'];
        $this->token = $setting['token'];
        $this->encodingaeskey = $setting['encodingaeskey'];
        $this->account = $account;
    }


    /**
     * global platform pre auth code
     * @return array|mixed
     */
    function getPreauthCode()
    {
        $preauthcode = Cache::get('account:preauthcode');

        if (true || empty($preauthcode) || empty($preauthcode['value']) || $preauthcode['expire'] < SYS_TIME) {
            $component_accesstoken = $this->getComponentAccesstoken();
            if (is_error($component_accesstoken) || !$component_accesstoken) {
                return $component_accesstoken;
            }
            $data = array(
                'component_appid' => $this->appid
            );
            $response = $this->request(ACCOUNT_PLATFORM_API_PREAUTHCODE . $component_accesstoken, $data);
            if (is_error($response)) {
                return $response;
            }
            $preauthcode = array(
                'value' => $response['pre_auth_code'],
                'expire' => SYS_TIME + intval($response['expires_in']),
            );
            Cache::set('account:preauthcode', $preauthcode);
        }
        return $preauthcode['value'];
    }


    /**
     * 公众号获取 接入地址
     * @param $site_id
     * @param string $module
     * @return string
     */
    public function getAuthLoginUrl($site_id , $module = "wechat")
    {
        $setting = setting_load('wechat_platform');
        $preauthcode = $this->getPreauthCode();
        if (is_error($preauthcode) || !$preauthcode) {
            $authurl = "javascript:alert('{$preauthcode['message']}');";
        } else {
            //$authurl = sprintf(ACCOUNT_PLATFORM_API_LOGIN, $this->appid, $preauthcode, urlencode($GLOBALS['_W']['siteroot'] . 'index.php?c=account&a=auth&do=forward'));
            $authurl = sprintf(ACCOUNT_PLATFORM_API_LOGIN, $this->appid, $preauthcode, urlencode($setting['redirect_uri'] . $module . '/index/auto_auth/do/success?site_id=' . $site_id));
        }
        return $authurl;
    }


    /**
     * 获取账号信息
     * @param string $appid
     * @return array|mixed
     */
    public function getAccountInfo($appid = '')
    {
        $component_accesstoken = $this->getComponentAccesstoken();
        if (is_error($component_accesstoken)) {
            return $component_accesstoken;
        }
        $appid = !empty($appid) ? $appid : $this->account['account_appid'];
        $post = array(
            'component_appid' => $this->appid,
            'authorizer_appid' => $appid,
        );
        $response = $this->request(ACCOUNT_PLATFORM_API_ACCOUNT_INFO . $component_accesstoken, $post);
        if (is_error($response)) {
            return $response;
        }
        return $response;
    }
    /**
     * get Component Access token
     * @return array|mixed
     */
    function getComponentAccesstoken()
    {
        $access_token = Cache::get('account:component:assess_token');

        if (empty($access_token) || empty($access_token['value']) || $access_token['expire'] < SYS_TIME) {
            $ticket = Cache::get('account:ticket');
            if (empty($ticket)) {
                return error(0, 'Error , ticket has not been  received!Please Wait at most 10 minutes;');
            }
            $data = array(
                'component_appid' => $this->appid,
                'component_appsecret' => $this->appsecret,
                'component_verify_ticket' => $ticket,
            );
            $response = $this->request(ACCOUNT_PLATFORM_API_ACCESSTOKEN, $data);

            if (is_error($response)) {
                $errormsg = self::error_code($response['errno'], $response['message']);
                return error($response['errno'], $errormsg);
            }
            $access_token = array(
                'value' => $response['component_access_token'],
                'expire' => SYS_TIME + intval($response['expires_in']),
            );
            Cache::set('account:component:assess_token', $access_token);
        }
        return $access_token['value'];
    }

    /**
     * 授权信息
     * @param $code
     * @return array|mixed
     */
    public function getAuthInfo($code)
    {
        $component_accesstoken = $this->getComponentAccesstoken();
        if (is_error($component_accesstoken)) {
            return $component_accesstoken;
        }
        $post = array(
            'component_appid' => $this->appid,
            'authorization_code' => $code,
        );
        $response = $this->request(ACCOUNT_PLATFORM_API_QUERY_AUTH_INFO . $component_accesstoken, $post);
        if (is_error($response)) {
            return $response;
        }
        return $response;
    }
    //=========================================以下为代==============================================













    /**
     *
     * @return mixed
     */
    private function getAuthRefreshToken()
    {
        $auth_refresh_token = Cache::get('account:auth:refreshtoken:' . $this->account['type'] . "_". $this->account['id']);
        if (empty($auth_refresh_token)) {
            $auth_refresh_token = $this->account['auth_refresh_token'];
            Cache::set('account:auth:refreshtoken:' . $this->account['type'] . "_". $this->account['id'], $auth_refresh_token);
        }
        return $auth_refresh_token;
    }
    /**
     * 开放平台后去token
     * @return array|mixed
     */
    public function getAccessToken()
    {
        $cachename = 'account:auth:accesstoken:' . $this->account['account_appid'];
        $auth_accesstoken = Cache::get($cachename);

        if (empty($auth_accesstoken) || empty($auth_accesstoken['value']) || $auth_accesstoken['expire'] < SYS_TIME) {
            $component_accesstoken = $this->getComponentAccesstoken();

            if (is_error($component_accesstoken)) {
                return $component_accesstoken;
            }

            $this->refreshtoken = $this->getAuthRefreshToken();

            $data = array(
                'component_appid' => $this->appid,
                'authorizer_appid' => $this->account['account_appid'],
                'authorizer_refresh_token' => $this->refreshtoken,
            );
            $response = $this->request(ACCOUNT_PLATFORM_API_REFRESH_AUTH_ACCESSTOKEN . $component_accesstoken, $data);

            if (is_error($response)) {
                return $response;
            }
            $auth_accesstoken = array(
                'value' => $response['authorizer_access_token'],
                'expire' => SYS_TIME + intval($response['expires_in']),
            );
            Cache::set($cachename, $auth_accesstoken);
        }
        return $auth_accesstoken['value'];
    }



}