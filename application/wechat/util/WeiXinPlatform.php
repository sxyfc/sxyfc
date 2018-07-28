<?php

namespace app\wechat\util;

use think\Cache;

class WeiXinPlatform extends OpenPlatform
{
    public $appid;
    public $appsecret;
    public $encodingaeskey;
    public $token;
    public $refreshtoken;
    public $account;
    public $unic_cacke_key;

    public function __construct($account = array())
    {
        parent::__construct($account);
        if (isset($this->account['app_id']) && $this->account['app_id'] == 'wx570bc396a51b8ff8') {
            $this->open_platform_test_case();
        }
        $this->account['account_appid'] = $this->account['app_id'];
        $this->account['key'] = $this->appid;
        $this->unic_cacke_key = $this->account['type'] . "_" . $this->account['id'];
    }

    /**
     * 测试验证
     */
    public function open_platform_test_case()
    {
        global $_GPC;
        $post = file_get_contents('php://input');
        WechatUtility::logging('platform-test-message', $post);
        $encode_message = $this->xmlExtract($post);
        $message = aes_decode($encode_message['encrypt'], $this->encodingaeskey);
        $message = $this->parse($message);
        $response = array(
            'ToUserName' => $message['from'],
            'FromUserName' => $message['to'],
            'CreateTime' => SYS_TIME,
            'MsgId' => SYS_TIME,
            'MsgType' => 'text',
        );
        if ($message['content'] == 'TESTCOMPONENT_MSG_TYPE_TEXT') {
            $response['Content'] = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
        }
        if ($message['msgtype'] == 'event') {
            $response['Content'] = $message['event'] . 'from_callback';
        }
        if (strexists($message['content'], 'QUERY_AUTH_CODE')) {
            list($sufixx, $authcode) = explode(':', $message['content']);
            $auth_info = $this->getAuthInfo($authcode);
            WechatUtility::logging('platform-test-send-message', var_export($auth_info, true));
            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $auth_info['authorization_info']['authorizer_access_token'];
            $data = array(
                'touser' => $message['from'],
                'msgtype' => 'text',
                'text' => array('content' => $authcode . '_from_api'),
            );
            $response = ihttp_request($url, urldecode(json_encode($data)));
            exit('');
        }
        $xml = array(
            'Nonce' => $_GPC['nonce'],
            'TimeStamp' => $_GPC['timestamp'],
            'Encrypt' => aes_encode(array2xml($response), $this->encodingaeskey, $this->appid),
        );
        $signature = array($xml['Encrypt'], $this->token, $_GPC['timestamp'], $_GPC['nonce']);
        sort($signature, SORT_STRING);
        $signature = implode($signature);
        $xml['MsgSignature'] = sha1($signature);
        exit(array2xml($xml));
    }


    public function getOauthCodeUrl($callback, $state = '')
    {
        return sprintf(ACCOUNT_PLATFORM_API_OAUTH_CODE, $this->account['account_appid'], $this->appid, $callback, $state);
    }

    public function getOauthUserInfoUrl($callback, $state = '')
    {
        return sprintf(ACCOUNT_PLATFORM_API_OAUTH_USERINFO, $this->account['account_appid'], $callback, $state, $this->appid);
    }

    public function oauth_user_info_login($code = ''){
        global $_W, $_GPC;
        if (!empty($_GPC['code'])) {
            $code = $_GPC['code'];
        }
        if (empty($code)) {
            $forward = $this->getOauthUserInfoUrl(urlencode($_W['current_url']) , $_W['uuid']);
            header('Location: ' . $forward);
            exit;
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->account['key']}&secret={$this->account['app_secret']}&code={$code}&grant_type=authorization_code";
        $response = $this->request($url);
        $user_info = $this->getOauthUserInfo($response['access_token'] , $response['openid']);
        return $user_info;
    }


    public function pc_login(){
        global $_W, $_GPC;
        $state  = md5(uniqid(rand(), TRUE));
        $_SESSION["wx_state"]    =   $state; //存到SESSION
        $callback = urlencode($_W['current_url']);
        $wxurl = "https://open.weixin.qq.com/connect/qrconnect?appid=".$this->account['account_appid']."&redirect_uri={$callback}&response_type=code&scope=snsapi_login&state={$state}#wechat_redirect";
        header("Location: $wxurl");
    }
    /**
     * 授权登录地址
     * @param string $code
     * @return array|mixed
     */
    public function oauth_user_login($code = '')
    {
        global $_W, $_GPC;
        if (!empty($_GPC['code'])) {
            $code = $_GPC['code'];
        }
        if (empty($code)) {
            $forward = $this->getOauthUserInfoUrl($_W['current_url']);
            header('Location: ' . $forward);
            exit;
        }
        $component_accesstoken = $this->getComponentAccesstoken();
        if (is_error($component_accesstoken)) {
            return $component_accesstoken;
        }

        $apiurl = sprintf(ACCOUNT_PLATFORM_API_OAUTH_INFO . $component_accesstoken, $this->account['account_appid'], $this->appid, $code);
        $response = $this->request($apiurl);
        if (is_error($response)) {
            return $response;
        }
        Cache::set('account:oauth:refreshtoken:' . $this->account['account_appid'] , $response['refresh_token']);
        $user = $this->getOauthUserInfo($response['access_token'] , $response['openid']);
        if(!isset($user['openid']) || empty($user['openid'])){
            test($user);
        }
        return $user;
    }


    public function getOauthInfo($code = '')
    {
        global $_W, $_GPC;
        if (!empty($_GPC['code'])) {
            $code = $_GPC['code'];
        }
        if (empty($code)) {
            $forward = $this->getOauthCodeUrl(urlencode($_W['current_url']));
            header('Location: ' . $forward);
            exit;
        }

        $component_accesstoken = $this->getComponentAccesstoken();
        if (is_error($component_accesstoken)) {
            return $component_accesstoken;
        }

        $apiurl = sprintf(ACCOUNT_PLATFORM_API_OAUTH_INFO . $component_accesstoken, $this->account['account_appid'], $this->appid, $code);
        $response = $this->request($apiurl);
        if (is_error($response)) {
            return $response;
        }
        Cache::set('account:oauth:refreshtoken:' . $this->account['account_appid'] , $response['refresh_token']);
        return $response;
    }

    public function getJsApiTicket()
    {
        $cachekey = "jsticket:{$this->account['type']}{$this->account['id']}";
        $js_ticket = Cache::get($cachekey);
        if (empty($js_ticket) || empty($js_ticket['value']) || $js_ticket['expire'] < SYS_TIME) {
            $access_token = $this->getAccessToken();
            if (is_error($access_token)) {
                return $access_token;
            }
            $apiurl = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
            $response = $this->request($apiurl);
            $js_ticket = array(
                'value' => $response['ticket'],
                'expire' => SYS_TIME + $response['expires_in'] - 200,
            );
            Cache::set($cachekey, $js_ticket);
        }
        $this->account['jsapi_ticket'] = $js_ticket;
        return $js_ticket['value'];
    }

    public function getJssdkConfig($url = '')
    {
        global $_W;
        $jsapiTicket = $this->getJsApiTicket();
        if (is_error($jsapiTicket)) {
            $jsapiTicket = $jsapiTicket['message'];
        }
        $nonceStr = random(16);
        $timestamp = SYS_TIME;
        $url = empty($url) ? $_W['siteurl'] : $url;
        $string1 = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string1);
        $config = array(
            "appId" => $this->account['account_appid'],
            "nonceStr" => $nonceStr,
            "timestamp" => "$timestamp",
            "signature" => $signature,
        );
        if ($_W['develop']) {
            $config['url'] = $url;
            $config['string1'] = $string1;
            $config['name'] = $this->account['account_name'];
        }
        return $config;
    }


}