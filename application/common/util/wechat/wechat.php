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
namespace app\common\util\wechat;
use think\Cache;

/**
 * 微信授权相关接口
 *
 * @link http://www.phpddt.com
 */

class wechat
{
    /*
     * 初始化
     */
    public function __construct($site_wechat)
    {
        $this->app_id = $site_wechat['app_id'];
        $this->app_secret = $site_wechat['app_secret'];
        $this->redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
        $this->redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].\think\Request::instance()->url());
        $this->site_id = $site_wechat['site_id'];
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * getAccessToken
     * @return mixed
     */
    public function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $name = "access_token_" . $this->site_id;
        $data = Cache::get($name);
        if ($data['expire_time'] < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->app_id&secret=$this->app_secret";
            $res = json_decode($this->httpGet($url  ));
            $access_token = $res->access_token;
            if ($access_token) {
                $data['expire_time'] = time() + 7000;
                $data['access_token'] = $access_token;
                Cache::set($name , $data);
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }

    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例

    private function getJsApiTicket() {
        $name = "jsapi_ticket_" . $this->site_id;
        $data = Cache::get($name);
        if ($data['expire_time'] < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url ));
            $ticket = $res->ticket;
            if ($ticket) {
                $data['expire_time']  = time() + 7000;
                $data['jsapi_ticket']  = $ticket;
                Cache::set($name , $data);
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }
        return $ticket;
    }

    /**
     * 获取微信授权链接
     *
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     */

    public function get_authorize_url($state)
    {
        $redirect_uri = $this->redirect_uri;

        //https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }

    public function get_openid_url($state)
    {
        $redirect_uri = $this->redirect_uri;

        //https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
    }


    /**
     * 获取授权token
     *
     * @param string $code 通过get_authorize_url获取到的code
     */
    public function get_access_token($code)
    {
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
        $token_data = $this->http($token_url , 'get');


        if ($token_data[0] == 200) {
            return json_decode($token_data[1], TRUE);
        }

        return FALSE;
    }


    //get tocken and openid


    //just get openid

    public function get_base_info()
    {
        $code = input('param.code');
        $state = random();
        if (!$code) {
            $url = $this->get_openid_url($state);
            header("location:$url");
            exit;
        }

        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
        $token_data = $this->http($token_url , 'get');
        if ($token_data[0] == 200) {
            return json_decode($token_data[1], TRUE);
        }
        return FALSE;
    }

    /**
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function getUserInfo($open_id)
    {
        $access_token = $this->getAccessToken();
        if ($access_token && $open_id) {
            $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $info_data = $this->http($info_url , 'get');
            test($info_data);
            if ($info_data[0] == 200) {
                return json_decode($info_data[1], TRUE);
            }
        }
        return FALSE;
    }
    /**
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function get_user_info($access_token, $open_id)
    {
        if ($access_token && $open_id) {
            $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $info_data = $this->http($info_url , 'get');

            if ($info_data[0] == 200) {
                return json_decode($info_data[1], TRUE);
            }
        }
        return FALSE;
    }

    //关注
    public function get_fans_info($open_id)
    {
        $access_token = $this->getAccessToken();
        if ($access_token && $open_id) {
            $info_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            //$info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $info_data = $this->http($info_url , 'get');
            if ($info_data[0] == 200) {
                return json_decode($info_data[1], TRUE);
            }
        }
        return FALSE;
    }


    public function http($url, $method, $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($http_code, $response);
    }

    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->app_id,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
}