<?php

namespace app\wechat\util;

use app\common\util\pkcs7\PKCS7Encoder;
use app\common\util\pkcs7\Prpcrypt;
use SimpleXMLElement;
use think\Cache;
use think\Exception;
use think\Log;

define('ACCOUNT_PLATFORM_API_ACCESSTOKEN', 'https://api.weixin.qq.com/cgi-bin/component/api_component_token');
define('ACCOUNT_PLATFORM_API_PREAUTHCODE', 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=');
define('ACCOUNT_PLATFORM_API_LOGIN', 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=%s&pre_auth_code=%s&redirect_uri=%s&auth_type=3');
define('ACCOUNT_PLATFORM_API_QUERY_AUTH_INFO', 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=');
define('ACCOUNT_PLATFORM_API_ACCOUNT_INFO', 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=');
define('ACCOUNT_PLATFORM_API_REFRESH_AUTH_ACCESSTOKEN', 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=');
define('ACCOUNT_PLATFORM_API_OAUTH_CODE', 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&component_appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect');
define('ACCOUNT_PLATFORM_API_OAUTH_USERINFO', 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=%s&component_appid=%s#wechat_redirect');
define('ACCOUNT_PLATFORM_API_OAUTH_INFO', 'https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=%s&component_appid=%s&code=%s&grant_type=authorization_code&component_access_token=');

abstract class MhcmsWechatAccountBase
{

    /**
     * @var $account array 账号配置信息
     */
    public $account;

    abstract public function __construct($account);


    /**
     * 账号签名验证
     * @return bool
     */
    public function checkSign()
    {
        $token = $this->account['token'];
        $signkey = array($token, $_GET['timestamp'], $_GET['nonce']);
        sort($signkey, SORT_STRING);
        $signString = implode($signkey);
        $signString = sha1($signString);
        return $signString == $_GET['signature'];
    }

    public function decryptMsg($postData)
    {
        $packet = $this->xmlExtract($postData);
        if (is_error($packet)) {
            return error(-1, $packet['message']);
        }
        $istrue = $this->checkSignature($packet['encrypt']);
        if (!$istrue) {
            return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40001 \n,错误描述为: " . $this->encryptErrorCode('40001'));
        }
        $encodingaeskey = $this->account['encodingaeskey'];
        $appid = $this->account['app_id'];
        $this->key = base64_decode($encodingaeskey . '=');
        try {
            $iv = substr($this->key, 0, 16);
            $decrypted = openssl_decrypt($packet['encrypt'],'AES-256-CBC',substr($this->key, 0, 32),OPENSSL_ZERO_PADDING,$iv);
        } catch (Exception $e) {
            return array(-40002, null);
        }
        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder();
            $result = $pkc_encoder->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
            if (!$appid)
                $appid = $from_appid;
            //如果传入的appid是空的，则认为是订阅号，使用数据中提取出来的appid
        } catch (Exception $e) {
            //print $e;
            WechatUtility::logging("" , "");
            return array(-1, null);
        }
        if ($from_appid != $appid)
            return array(-1, null);
        //不注释上边两行，避免传入appid是错误的情况
        return $xml_content;
    }
    /**
     * from xml process parse
     * @param $message
     * @return array
     */
    public static function xmlExtract($message)
    {
        $packet = array();
        if (!empty($message)) {
            $obj = isimplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($obj instanceof SimpleXMLElement) {
                $packet['encrypt'] = strval($obj->Encrypt);
                $packet['to'] = strval($obj->ToUserName);
            }
        }
        if (!empty($packet['encrypt'])) {
            return $packet;
        } else {
            return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40002 \n,错误描述为: " . self::encryptErrorCode('40002'));
        }
    }

    public function encryptMsg($text)
    {
        $encodingaeskey = $this->account['encodingaeskey'];
        $appid = $this->account['key'];
        $this->key = base64_decode($encodingaeskey . '=');
        $text = Prpcrypt::getRandomStr() . pack("N", strlen($text)) . $text . $appid;


        $iv = substr($this->key, 0, 16);
        $pkc_encoder = new PKCS7Encoder();
        $text = $pkc_encoder->encode($text);
        $encrypted = openssl_encrypt($text,'AES-256-CBC',substr($this->key, 0, 32),OPENSSL_ZERO_PADDING,$iv);
        $signature = $this->buildSignature($encrypted);

        return array($signature, $encrypted);
    }

    public function fetchAccountInfo()
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function queryAvailableMessages()
    {
        return array();
    }


    public function queryAvailablePackets()
    {
        return array();
    }

    /**
     * fans group
     * @return bool
     */
    public function isTagSupported()
    {
        if ((!empty($this->account['key']) &&
                !empty($this->account['app_secret']) || $this->account['type'] == 3) &&
            (intval($this->account['level']) > 2)) {
            return true;
        }
        return false;

    }

    public function getOauthUserInfo($accesstoken, $openid)
    {
        $apiurl = "https://api.weixin.qq.com/sns/userinfo?access_token={$accesstoken}&openid={$openid}&lang=zh_CN";
        $response = $this->request($apiurl);
        return $response;
    }

    /**
     * 获取用户信息
     * @param $uniid
     * @param bool $isOpen
     * @return array|mixed
     */
    public function fansQueryInfo($uniid, $isOpen = true)
    {
        if ($isOpen) {
            $openid = $uniid;
        } else {
            exit('error');
        }
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$openid}&lang=zh_CN";
        $response = ihttp_get($url);
        if (is_error($response)) {
            return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
        }
        preg_match('/city":"(.*)","province":"(.*)","country":"(.*)"/U', $response['content'], $reg_arr);
        $city = htmlentities(bin2hex($reg_arr[1]));
        $province = htmlentities(bin2hex($reg_arr[2]));
        $country = htmlentities(bin2hex($reg_arr[3]));
        $response['content'] = str_replace('"city":"' . $reg_arr[1] . '","province":"' . $reg_arr[2] . '","country":"' . $reg_arr[3] . '"', '"city":"' . $city . '","province":"' . $province . '","country":"' . $country . '"', $response['content']);
        $result = @json_decode($response['content'], true);
        $result['city'] = hex2bin(html_entity_decode($result['city']));
        $result['province'] = hex2bin(html_entity_decode($result['province']));
        $result['country'] = hex2bin(html_entity_decode($result['country']));
        if (empty($result)) {
            return error(-1, "接口调用失败, 元数据: {$response['meta']}");
        } elseif (!empty($result['errcode'])) {
            return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->errorCode($result['errcode'])}");
        }
        return $result;
    }

    /**
     * 创建账号消息验证签名
     * @param $encrypt_msg
     * @return string
     */
    public function buildSignature($encrypt_msg)
    {
        global $_GPC;
        $token = $this->account['token'];
        $array = array($encrypt_msg, $token, $_GPC['timestamp'], $_GPC['nonce']);
        sort($array, SORT_STRING);
        $str = implode($array);
        $str = sha1($str);
        return $str;
    }


    /**
     * 消息签名验证
     * @param $encrypt_msg
     * @return bool
     */
    public function checkSignature($encrypt_msg)
    {
        $str = $this->buildSignature($encrypt_msg);
        return $str == $_GET['msg_signature'];
    }


    /**
     * to xml process parse
     * @param $data
     * @return array
     */
    function xmlDetract($data)
    {
        $xml['Encrypt'] = $data[1];
        $xml['MsgSignature'] = $data[0];
        $xml['TimeStamp'] = $_GET['timestamp'];
        $xml['Nonce'] = $_GET['nonce'];
        return array2xml($xml);
    }

    public function parse($message)
    {
        global $_W;
        if (!empty($message)) {
            $message = xml2array($message);
            $packet = iarray_change_key_case($message, CASE_LOWER);
            $packet['from'] = $message['FromUserName'];
            $packet['to'] = $message['ToUserName'];
            $packet['time'] = $message['CreateTime'];
            $packet['type'] = $message['MsgType'];
            $packet['event'] = isset($message['Event']) ? $message['Event'] : "";
            switch ($packet['type']) {
                case 'text':
                    $packet['redirection'] = false;
                    unset($packet['source']);
                    break;
                case 'image':
                    $packet['url'] = $message['PicUrl'];
                    break;
                case 'video':
                case 'shortvideo':
                    $packet['thumb'] = $message['ThumbMediaId'];
                    break;
            }

            switch ($packet['event']) {
                case 'subscribe':
                    $packet['type'] = 'subscribe';
                case 'SCAN':
                    if ($packet['event'] == 'SCAN') {
                        $packet['type'] = 'qr';
                    }
                    if (!empty($packet['eventkey'])) {
                        $packet['scene'] = str_replace('qrscene_', '', $packet['eventkey']);
                        if (strexists($packet['scene'], '\u')) {
                            $packet['scene'] = '"' . str_replace('\\u', '\u', $packet['scene']) . '"';
                            $packet['scene'] = json_decode($packet['scene']);
                        }

                    }
                    break;
                case 'unsubscribe':
                    $packet['type'] = 'unsubscribe';
                    break;
                case 'LOCATION':
                    $packet['type'] = 'trace';
                    $packet['location_x'] = $message['Latitude'];
                    $packet['location_y'] = $message['Longitude'];
                    break;
                case 'pic_photo_or_album':
                case 'pic_weixin':
                case 'pic_sysphoto':
                    $packet['sendpicsinfo']['piclist'] = array();
                    $packet['sendpicsinfo']['count'] = $message['SendPicsInfo']['Count'];
                    if (!empty($message['SendPicsInfo']['PicList'])) {
                        foreach ($message['SendPicsInfo']['PicList']['item'] as $item) {
                            if (empty($item)) {
                                continue;
                            }
                            $packet['sendpicsinfo']['piclist'][] = is_array($item) ? $item['PicMd5Sum'] : $item;
                        }
                    }
                    break;
                case 'card_pass_check':
                case 'card_not_pass_check':
                case 'user_get_card':
                case 'user_del_card':
                case 'user_consume_card':
                case 'poi_check_notify':
                    $packet['type'] = 'coupon';
                    break;
            }
        }
        return $packet;
    }


    public function response($packet)
    {
        if (is_error($packet)) {
            return '';
        }
        if (!is_array($packet)) {
            return $packet;
        }
        if (empty($packet['CreateTime'])) {
            $packet['CreateTime'] = SYS_TIME;
        }
        if (empty($packet['MsgType'])) {
            $packet['MsgType'] = 'text';
        }
        if (empty($packet['FuncFlag'])) {
            $packet['FuncFlag'] = 0;
        } else {
            $packet['FuncFlag'] = 1;
        }
        return array2xml($packet);
    }


    public function isPushSupported()
    {
        return false;
    }


    public function push($uniid, $packet)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function isBroadcastSupported()
    {
        return false;
    }


    public function broadcast($packet, $targets = array())
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function isMenuSupported()
    {
        return false;
    }

    abstract function getAccessToken();

    /**
     * 发送开放平台请求
     * @param $url
     * @param array $post
     * @return array|mixed
     */
    protected function request($url, $post = array())
    {
        $response = ihttp_request($url, json_encode($post));
        $response = json_decode($response['content'], true);

        if (empty($response) || !empty($response['errcode'])) {
            return error($response['errcode'], self::error_code($response['errcode'], $response['errmsg']));
        }
        return $response;
    }


    /**
     *
     * @param string $type
     * @param int $offset
     * @param int $count
     * @return array|mixed
     */
    public function batchGetMaterial($type = 'news', $offset = 0, $count = 20)
    {
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $token;
        $data = array(
            'type' => $type,
            'offset' => intval($offset),
            'count' => $count,
        );
        $response = $this->request($url, $data);
        return $response;
    }


    /**
     * 创建上传菜单
     * @param $menu
     * @return array|void
     */
    public function menuCreate($menu)
    {
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}";
        if (!empty($menu['matchrule'])) {
            $url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token={$token}";
        }
        $data = urldecode(json_encode($menu));
        $response = ihttp_post($url, $data);

        if (is_error($response)) {
            return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
        }
        $result = @mhcms_json_decode($response['content']);
        if (!empty($result['errcode'])) {
            return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：" . self::error_code($result['errcode']));
        }
        $ret['code'] = 1;
        $ret['msg'] = $result['errmsg'];
        return $ret;
    }

    /**
     * 获取模板消息列表
     */

    public function getAllPrivateTemplate(){
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $api_url = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=$token";
        $resp = $this->request($api_url);
        return $resp;
    }

    public function menuDelete()
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function menuModify($menu)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function menuQuery()
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function queryFansActions()
    {
        return array();
    }


    public function fansGroupAll()
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function fansGroupCreate($group)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function fansGroupModify($group)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function fansMoveGroup($uniid, $group)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function fansQueryGroup($uniid)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    /**
     * 拉去粉丝列表
     * @param string $startopenid
     * @return array
     */
    public function fansAll($startopenid = '')
    {
        global $_GPC;
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $token;
        if (!empty($_GPC['next_openid'])) {
            $startopenid = $_GPC['next_openid'];
        }
        if (!empty($startopenid)) {
            $url .= '&next_openid=' . $startopenid;
        }
        $response = ihttp_get($url);
        if (is_error($response)) {
            return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
        }
        $result = @json_decode($response['content'], true);
        if (empty($result)) {
            return error(-1, "接口调用失败, 元数据: {$response['meta']}");
        } elseif (!empty($result['errcode'])) {
            return error(-1, "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：" . self::error_code($result['errcode']));
        }
        $return = array();
        $return['total'] = $result['total'];
        $return['fans'] = $result['data']['openid'];
        $return['next'] = $result['next_openid'];
        return $return;
    }

    /**
     * 粉丝信息获取
     * @param $data
     * @return array
     */
    public function fansBatchQueryInfo($data)
    {
        if (empty($data)) {
            return error(-1, '粉丝openid错误');
        }
        foreach ($data as $da) {
            $post[] = array(
                'openid' => trim($da),
                'lang' => 'zh-CN'
            );
        }
        $data = array();
        $data['user_list'] = $post;
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token={$token}";
        $response = ihttp_post($url, json_encode($data));
        if (is_error($response)) {
            return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
        }
        $result = @json_decode($response['content'], true);
        if (empty($result)) {
            return error(-1, "接口调用失败, 元数据: {$response['meta']}");
        } elseif (!empty($result['errcode'])) {
            return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情： " . self::error_code($result['errcode']));
        }
        return $result['user_info_list'];
    }


    public function queryTraceActions()
    {
        return array();
    }


    public function traceCurrent($uniid)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function traceHistory($uniid, $time)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function queryBarCodeActions()
    {
        return array();
    }


    public function barCodeCreateDisposable($barcode)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }


    public function barCodeCreateFixed($barcode)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }

    public function downloadMedia($media)
    {
        trigger_error('not supported.', E_USER_WARNING);
    }

    /**
     * 加密错误函数
     * @param $code
     * @return mixed|string
     */
    public static function encryptErrorCode($code)
    {
        $errors = array(
            '40001' => '签名验证错误',
            '40002' => 'xml解析失败',
            '40003' => 'sha加密生成签名失败',
            '40004' => 'encodingAesKey 非法',
            '40005' => 'appid 校验错误',
            '40006' => 'aes 加密失败',
            '40007' => 'aes 解密失败',
            '40008' => '解密后得到的buffer非法',
            '40009' => 'base64加密失败',
            '40010' => 'base64解密失败',
            '40011' => '生成xml失败',
        );
        if ($errors[$code]) {
            return $errors[$code];
        } else {
            return '未知错误';
        }
    }

    /**
     * 错误信息和代码
     * @param $code
     * @param string $errmsg
     * @return mixed|string
     */
    public static function error_code($code, $errmsg = '未知错误')
    {
        global $_W;
        $errors = array(
            '-1' => '系统繁忙',
            '0' => '请求成功',
            '40001' => '获取access_token时AppSecret错误，或者access_token无效',
            '40002' => '不合法的凭证类型',
            '40003' => '不合法的OpenID',
            '40004' => '不合法的媒体文件类型',
            '40005' => '不合法的文件类型',
            '40006' => '不合法的文件大小',
            '40007' => '不合法的媒体文件id',
            '40008' => '不合法的消息类型',
            '40009' => '不合法的图片文件大小',
            '40010' => '不合法的语音文件大小',
            '40011' => '不合法的视频文件大小',
            '40012' => '不合法的缩略图文件大小',
            '40013' => '不合法的APPID',
            '40014' => '不合法的access_token',
            '40015' => '不合法的菜单类型',
            '40016' => '不合法的按钮个数',
            '40017' => '不合法的按钮个数',
            '40018' => '不合法的按钮名字长度',
            '40019' => '不合法的按钮KEY长度',
            '40020' => '不合法的按钮URL长度',
            '40021' => '不合法的菜单版本号',
            '40022' => '不合法的子菜单级数',
            '40023' => '不合法的子菜单按钮个数',
            '40024' => '不合法的子菜单按钮类型',
            '40025' => '不合法的子菜单按钮名字长度',
            '40026' => '不合法的子菜单按钮KEY长度',
            '40027' => '不合法的子菜单按钮URL长度',
            '40028' => '不合法的自定义菜单使用用户',
            '40029' => '不合法的oauth_code',
            '40030' => '不合法的refresh_token',
            '40031' => '不合法的openid列表',
            '40032' => '不合法的openid列表长度',
            '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
            '40035' => '不合法的参数',
            '40038' => '不合法的请求格式',
            '40039' => '不合法的URL长度',
            '40050' => '不合法的分组id',
            '40051' => '分组名字不合法',
            '40155' => '请勿添加其他公众号的主页链接',
            '41001' => '缺少access_token参数',
            '41002' => '缺少appid参数',
            '41003' => '缺少refresh_token参数',
            '41004' => '缺少secret参数',
            '41005' => '缺少多媒体文件数据',
            '41006' => '缺少media_id参数',
            '41007' => '缺少子菜单数据',
            '41008' => '缺少oauth code',
            '41009' => '缺少openid',
            '42001' => 'access_token超时',
            '42002' => 'refresh_token超时',
            '42003' => 'oauth_code超时',
            '43001' => '需要GET请求',
            '43002' => '需要POST请求',
            '43003' => '需要HTTPS请求',
            '43004' => '需要接收者关注',
            '43005' => '需要好友关系',
            '44001' => '多媒体文件为空',
            '44002' => 'POST的数据包为空',
            '44003' => '图文消息内容为空',
            '44004' => '文本消息内容为空',
            '45001' => '多媒体文件大小超过限制',
            '45002' => '消息内容超过限制',
            '45003' => '标题字段超过限制',
            '45004' => '描述字段超过限制',
            '45005' => '链接字段超过限制',
            '45006' => '图片链接字段超过限制',
            '45007' => '语音播放时间超过限制',
            '45008' => '图文消息超过限制',
            '45009' => '接口调用超过限制',
            '45010' => '创建菜单个数超过限制',
            '45015' => '回复时间超过限制',
            '45016' => '系统分组，不允许修改',
            '45017' => '分组名字过长',
            '45018' => '分组数量超过上限',
            '45056' => '创建的标签数过多，请注意不能超过100个',
            '45057' => '该标签下粉丝数超过10w，不允许直接删除',
            '45058' => '不能修改0/1/2这三个系统默认保留的标签',
            '45059' => '有粉丝身上的标签数已经超过限制',
            '45065' => '24小时内不可给该组人群发该素材',
            '45157' => '标签名非法，请注意不能和其他标签重名',
            '45158' => '标签名长度超过30个字节',
            '45159' => '非法的标签',
            '46001' => '不存在媒体数据',
            '46002' => '不存在的菜单版本',
            '46003' => '不存在的菜单数据',
            '46004' => '不存在的用户',
            '47001' => '解析JSON/XML内容错误',
            '48001' => 'api功能未授权',
            '48003' => '请在微信平台开启群发功能',
            '50001' => '用户未授权该api',
            '40070' => '基本信息baseinfo中填写的库存信息SKU不合法。',
            '41011' => '必填字段不完整或不合法，参考相应接口。',
            '40056' => '无效code，请确认code长度在20个字符以内，且处于非异常状态（转赠、删除）。',
            '43009' => '无自定义SN权限，请参考开发者必读中的流程开通权限。',
            '43010' => '无储值权限,请参考开发者必读中的流程开通权限。',
            '43011' => '无积分权限,请参考开发者必读中的流程开通权限。',
            '40078' => '无效卡券，未通过审核，已被置为失效。',
            '40079' => '基本信息base_info中填写的date_info不合法或核销卡券未到生效时间。',
            '45021' => '文本字段超过长度限制，请参考相应字段说明。',
            '40080' => '卡券扩展信息cardext不合法。',
            '40097' => '基本信息base_info中填写的参数不合法。',
            '49004' => '签名错误。',
            '43012' => '无自定义cell跳转外链权限，请参考开发者必读中的申请流程开通权限。',
            '40099' => '该code已被核销。',
            '61005' => '缺少接入平台关键数据，等待微信开放平台推送数据，请十分钟后再试或是检查“授权事件接收URL”是否写错（index.php?c=account&amp;a=auth&amp;do=ticket地址中的&amp;符号容易被替换成&amp;amp;）',
            '61023' => '请重新授权接入该公众号',
        );
        $code = strval($code);
        //todo
        if ($code == '40001' || $code == '42001') {
            $cachekey = "accesstoken:{$_W['account']['type']}_{$_W['account']['id']}";
            Cache::rm($cachekey);
            return '微信公众平台授权异常, 系统已修复这个错误, 请刷新页面重试.';
        }
        if ($errors[$code]) {
            return $errors[$code];
        } else {
            return $errmsg;
        }
    }

}

