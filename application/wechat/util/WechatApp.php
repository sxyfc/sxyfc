<?php

namespace app\wechat\util;

use SimpleXMLElement;
use think\Cache;
use think\Log;


class WechatApp extends MhcmsWechatAccountBase
{

    public function __construct($account = array())
    {
        $this->account = $account;
        if ($this->account['app_id'] == "wxd101a85aa106f53e") {

        }
    }
    public function get_openid($code)
    {
        global $_W, $_GPC;
        $code =$code ? $code : $_GPC['code'];
        $this->app_id = $this->account['app_id'];
        $this->app_secret = $this->account['app_secret'];
        $api_url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->app_id}&secret={$this->app_secret}&js_code=$code&grant_type=authorization_code";
        WechatUtility::logging($api_url);
        $res = $this->request($api_url);
        return $res;
    }

    public function getOauthInfo($code = '')
    {
        global $_W, $_GPC;
        if (!empty($_GPC['code'])) {
            $code = $_GPC['code'];
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->account['app_id']}&secret={$this->account['secret']}&js_code={$code}&grant_type=authorization_code";
        return $response = $this->request($url);
    }


    public function checkSign()
    {
        $token = $this->account['token'];
        $signkey = array($token, $_GET['timestamp'], $_GET['nonce']);
        sort($signkey, SORT_STRING);
        $signString = implode($signkey);
        $signString = sha1($signString);
        return $signString == $_GET['signature'];
    }


    public function pkcs7Encode($encrypt_data, $iv)
    {
        $key = base64_decode($_SESSION['session_key']);
        $result = aes_pkcs7_decode($encrypt_data, $key, $iv);
        if (is_error($result)) {
            return error(1, '解密失败');
        }
        $result = json_decode($result, true);
        if (empty($result)) {
            return error(1, '解密失败');
        }
        if ($result['watermark']['appid'] != $this->account['app_id']) {
            return error(1, '解密失败');
        }
        unset($result['watermark']);
        return $result;
    }

    public function checkIntoManage()
    {
        global $_GPC;
        if (empty($this->account) || (!empty($this->uniaccount['account']) && $this->uniaccount['account'] != 4 && !defined('IN_MODULE')) || empty($_GPC['version_id'])) {
            return false;
        }
        return true;
    }

    public function getAccessToken()
    {
        $cachekey = "accesstoken:{$this->account['app_id']}";
        $cache = Cache::get($cachekey);
        if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > TIMESTAMP) {
            $this->account['access_token'] = $cache;
            return $cache['token'];
        }

        if (empty($this->account['app_id']) || empty($this->account['secret'])) {
            return error('-1', '未填写小程序的 appid 或 appsecret！');
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->account['app_id']}&secret={$this->account['secret']}";
        $response = $this->request($url);

        $record = array();
        $record['token'] = $response['access_token'];
        $record['expire'] = SYS_TIME + $response['expires_in'] - 200;

        $this->account['access_token'] = $record;
        Cache::set($cachekey, $record);
        return $record['token'];
    }

    public function getJssdkConfig($url = '')
    {
        return array();
    }

    public function getCodeWithPath($path)
    {

    }

    public function getCodeUnlimit($scene, $width = '430', $option = array())
    {
        if (!preg_match('/[0-9a-zA-Z\!\#\$\&\'\(\)\*\+\,\/\:\;\=\?\@\-\.\_\~]{1,32}/', $scene)) {
            return error(1, '场景值不合法');
        }
        $access_token = $this->getAccessToken();
        if (is_error($access_token)) {
            return $access_token;
        }
        $data = array(
            'scene' => $scene,
            'width' => intval($width),
        );
        if (!empty($data['auto_color'])) {
            $data['auto_color'] = intval($data['auto_color']);
        }
        if (!empty($option['line_color'])) {
            $data['line_color'] = array(
                'r' => $option['line_color']['r'],
                'g' => $option['line_color']['g'],
                'b' => $option['line_color']['b'],
            );
            $data['auto_color'] = false;
        }
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $response = $this->request($url, json_encode($data));
        if (is_error($response)) {
            return $response;
        }
        return $response['content'];
    }

    public function getQrcode()
    {

    }

    public function result($errno, $message = '', $data = '')
    {
        exit(json_encode(array(
            'errno' => $errno,
            'message' => $message,
            'data' => $data,
        )));
    }

    public function getDailyVisitTrend()
    {
        global $_W;
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token={$token}";
        $data = array(
            'begin_date' => date('Y-m-d', strtotime('-1 days')),
            'end_date' => date('Y-m-d', strtotime('-1 days'))
        );

        $response = $this->request($url, json_encode($data));
        if (is_error($response)) {
            return $response;
        }
        return $response['list'][0];
    }
}