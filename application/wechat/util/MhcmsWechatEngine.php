<?php

namespace app\wechat\util;

use app\common\model\Users;
use app\mhcms_professional\wechat\WeiXinPlatform;
use think\Cache;
use think\Log;

class MhcmsWechatEngine
{

    /*** @var $account WeiXinAccount|WeiXinPlatform|null */
    private $account = null;
    public $keyword = array();
    public $message = array();

    public function __construct($account)
    {
        $this->sites_wechat = $account;
        $this->account = self::create($account);
    }

    /**
     * @param $account
     * @return WeiXinAccount|WeiXinPlatform| WechatApp | WechatAppPlatform
     */
    public static function create($account)
    {
        static $wechats;
        if (!empty($account) && isset($account['type'])) {
            if (isset($wechats[$account['app_id']])) {
                return $wechats[$account['app_id']];
            } else {
                //公众号手动
                if ($account['type'] == 1) {
                    $wechats[$account['app_id']] = new WeiXinAccount($account);
                }
                //授权接入
                if ($account['type'] == 3) {
                    $wechats[$account['app_id']] = new WeiXinPlatform($account);
                }
                //    small app request
                //小程序手动
                if ($account['type'] == 4) {
                    $wechats[$account['app_id']] = new WechatApp($account);
                }
                //小程序授权
                if ($account['type'] == 5) {
                    $wechats[$account['app_id']] = new WechatAppPlatform($account);
                }
            }
        }
        return $wechats[$account['app_id']];
    }

    /**
     * 加密信息
     */
    public function encrypt()
    {
        global $_W;
        if (empty($this->account)) {
            exit('Miss Account.');
        }
        $SYS_TIME = SYS_TIME;
        $nonce = random(5);
        $token = $_W['account']['token'];
        $signkey = array($token, SYS_TIME, $nonce);
        sort($signkey, SORT_STRING);
        $signString = implode($signkey);
        $signString = sha1($signString);

        $_GET['SYS_TIME'] = $SYS_TIME;
        $_GET['nonce'] = $nonce;
        $_GET['signature'] = $signString;
        $postStr = file_get_contents('php://input');
        if (!empty($_W['account']['encodingaeskey']) && strlen($_W['account']['encodingaeskey']) == 43 && !empty($_W['account']['app_id']) && $_W['setting']['development'] != 1) {
            $data = $this->account->encryptMsg($postStr);
            $array = array('encrypt_type' => 'aes', 'SYS_TIME' => $SYS_TIME, 'nonce' => $nonce, 'signature' => $signString, 'msg_signature' => $data[0], 'msg' => $data[1]);
        } else {
            $data = array('', '');
            $array = array('encrypt_type' => '', 'SYS_TIME' => $SYS_TIME, 'nonce' => $nonce, 'signature' => $signString, 'msg_signature' => $data[0], 'msg' => $data[1]);
        }
        exit(json_encode($array));
    }


    public function decrypt()
    {
        global $_W;
        if (empty($this->account)) {
            exit('Miss Account.');
        }
        $postStr = file_get_contents('php://input');
        if (!empty($_W['account']['encodingaeskey']) && strlen($_W['account']['encodingaeskey']) == 43 && !empty($_W['account']['app_id']) && $_W['setting']['development'] != 1) {
            $resp = $this->account->local_decryptMsg($postStr);
        } else {
            $resp = $postStr;
        }
        exit($resp);
    }


    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function start()
    {
        global $_W;
        if (empty($this->account)) {
            exit('Miss Account.');
        }
        if (!$this->account->checkSign()) {
            WechatUtility::logging('85 checkSign', "Check Sign Fail");
            exit('Check Sign Fail.');
        }
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $postStr = file_get_contents('php://input');

            if (!empty($_GET['encrypt_type']) && $_GET['encrypt_type'] == 'aes') {
                $postStr = $this->account->decryptMsg($postStr);
            }

            $message = $this->account->parse($postStr);
            $this->message = $message;

            $this->check_site_status();
            if (empty($message)) {
                WechatUtility::logging('Fatal ', 'Decode Failed :');
                exit('Request Failed');
            }
            $_W['openid'] = $message['from'];
            //todo find fans
            $_W['wechat_fans_model'] = set_model("sites_wechat_fans");

            $_W['fans'] = $_W['wechat_fans_model']->where(['openid' => $_W['openid']])->find();
            $fans = $this->account->fansQueryInfo($_W['openid']);


            //处理消息
            $this->booking($message);

            /**
             * 根据消息类型解析消息处理器
             */
            $pars = $this->analyze($message);


            if (!$_W['fans']) {
                WechatUtility::process_create_fans($fans);
            }

            WechatUtility::process_update_fans($_W['fans'], $fans);
            if (empty($pars)) {
                // perform default reply  load default rule
                $where = [];
                $where['default'] = 1;
                $where['sites_wechat_id'] = $_W['account']['id'];
                $keyword = set_model("sites_wechat_keyword")->where($where)->find();
                if ($keyword) {
                    $pars[] = $this->make_rule($keyword, $message);
                }
            }
            $this->do_response($pars);
        }
    }

    public function do_response($pars)
    {
        global $_GPC, $_W;
        $response = array();
        $ok_to_send = 0;
        foreach ($pars as $par) {
            if (!$par) {
                continue;
            }
            $response = $this->process($par);
            if ($this->isValidResponse($response)) {
                $ok_to_send = 1;
                break;
            } else {
                Log::write($response);
                WechatUtility::logging("This Par Is Not a ValidResponse : ", $response);
            }

        }

        if ($ok_to_send) {
            $resp = $this->account->response($response);

            if (isset($_GPC['encrypt_type']) && $_GPC['encrypt_type'] == 'aes') {
                $resp = $this->account->encryptMsg($resp);
                $resp = $this->account->xmlDetract($resp);
            }
            ob_start();
            echo $resp;
            ob_start();
            ob_end_clean();
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            //todo when the resp is sent we should do the  clean up and after actions
            //todo :1 add keyword logs
            if ($par['rule']['gap_minute'] > 0) {

                $where_log = [];
                $where_log['site_id'] = $_W['site']['id'];
                $where_log['keyword_id'] = $par['rule']['id'];
                $where_log['openid'] = $par['message']['from'];

                $test = set_model("sites_wechat_keyword_log")->where($where_log)->find();

                if ($test) {
                    $test['total']++;
                    $test['last_send'] = SYS_TIME;
                    set_model("sites_wechat_keyword_log")->where($where_log)->update($test);
                } else {
                    $where_log['total'] = 1;
                    $where_log['last_send'] = SYS_TIME;
                    set_model("sites_wechat_keyword_log")->insert($where_log);
                }
            }

            exit();
        } else {
            Log::write("No Content Sent");
        }
    }


    private function isValidResponse($response)
    {
        if (is_array($response)) {
            if ($response['MsgType'] == 'text' && !empty($response['Content'])) {
                return true;
            }
            if ($response['MsgType'] == 'news' && !empty($response['items'])) {
                return true;
            }
            if (in_array($response['MsgType'], array('text', 'news', 'image')) && !empty($response['Image']['MediaId'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * 公众号统计
     * @param $message
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function booking($message)
    {
        global $_W;
        /*** 订阅 取消订阅事件 */
        if ($message['event'] == 'unsubscribe' || $message['event'] == 'subscribe') {
            //todo 统计公众号数据
            $todaystat = set_model('sites_wechat_stat')->where(array('date' => date('Ymd'), 'site_id' => $_W['site']['id']))->find();
            if (empty($todaystat)) {
                $updatestat = array(
                    'new' => 0,
                    'site_id' => $_W['site']['id'],
                    'sites_wechat_id' => $_W['account']['id'],
                    'cancel' => 0,
                    'cumulate' => 0,
                    'date' => date('Ymd'),
                );
                set_model('sites_wechat_stat')->insert($updatestat);
            }

            if ($message['event'] == 'unsubscribe') {
                $updatestat = array(
                    'cancel' => $todaystat['cancel'] + 1,
                );
                $updatestat['cumulate'] = 0;
                set_model('sites_wechat_stat')->where(array('id' => $todaystat['id']))->update($updatestat);
            } elseif ($message['event'] == 'subscribe') {
                $updatestat = array(
                    'new' => $todaystat['new'] + 1,
                    'cumulate' => 0,
                );
                set_model('sites_wechat_stat')->where(array('id' => $todaystat['id']))->update($updatestat);
            }

        }

    }

    private function analyze_qr(&$message)
    {
        global $_W;
        if (strpos($message['scene'], "###") !== false) {
            //from share
            $data = explode("###", $message['scene']);
            $uuid = $data[0];
            $_W['refer'] = $data[1];
        }else{
            //from self
            $uuid = Cache::get("mhcms_wechat_subscribe_login:" . $message['scene']);
        }
        if ($uuid) {
            //todo test if user exist

            $user = Users::get( $_W['fans']['user_id']);

            $_W['login_scan']  = true;
            $_W['uuid']  = $uuid;
            if($user){
                Cache::set("mhcms_wechat_subscribe_login:" . $uuid, $_W['fans']['user_id']);
            }
            return false;
        }else{
            $_W['login_scan']  = false;
            //todo other san actions
        }
    }


    public function analyze_subscribe(&$message, $order = 0)
    {
        //todo redirect to wechat follow
        $keyword = [
            'module' => 'wechat_follow',
        ];
        $pars[] = $this->make_rule($keyword, $message);
        return $pars;
    }

    public function analyze_unsubscribe(&$message, $order = 0)
    {
        //todo redirect to wechat follow
        $keyword = [
            'module' => 'wechat_follow',
            'rule' => "-1",
            'priority' => 0,
            'keyword' => "取消订阅公众号",
            'reply_type' => ""
        ];
        $pars[] = $this->make_rule($keyword, $message);
        return $pars;
    }


    public function analyze_trace(&$message, $order = 0)
    {

        //todo redirect to wechat follow
        $keyword = [
            'module' => 'wechat_follow',
        ];
        $pars[] = $this->make_rule($keyword, $message);
        return $pars;
    }

    public function analyze_text(&$message, $order = 0)
    {
        global $_W;
        if (!isset($message['content'])) {
            return [];
        }
        //todo 分析菜单
        $cachekey = 'mhcms:' . $this->sites_wechat['id'] . ':keyword:' . md5($message['content']);
        $keyword_cache = Cache::get($cachekey);
        /** 非开发模式 读取缓存*/
        if (!empty($keyword_cache) && $keyword_cache['expire'] > SYS_TIME && !$_W['develop']) {
            //    return $keyword_cache['data'];
        }
        $condition = <<<EOF
            `sites_wechat_id` IN ( {$this->sites_wechat['id']} )
            AND 
            (
                ( `keyword_type` = 1 AND `keyword` = "{$message['content']}" )
                or
                ( `keyword_type` = 2 AND instr("{$message['content']}", `keyword`) )
                or
                ( `keyword_type` = 3 AND "{$message['content']}" REGEXP `keyword` )
                or
                ( `keyword_type` = 4 )
            )
            AND `status`= 99
EOF;

        $order = intval($order);
        if (intval($order) > 0) {
            $condition .= " AND `listorder` > $order";
        }
        $keywords = set_model("sites_wechat_keyword")->where($condition)->select();

        if (empty($keywords)) {
            return [];
        }

        $pars = array();

        foreach ($keywords as $keyword) {
            $rule = $this->make_rule($keyword, $message);
            if ($rule) {
                $pars[] = $rule;
            }

        }

        $cache = array(
            'data' => $pars,
            'expire' => SYS_TIME + 5 * 60,
        );
        Cache::set($cachekey, $cache);
        return $pars;
    }

    public static function make_rule($keyword, $message)
    {
        global $_W;
        if (isset($keyword['id']) && $keyword['id'] > 0 && $keyword['gap_minute'] > 0) {
            $where = [];
            $where['openid'] = $message['from'];
            $where['keyword_id'] = $keyword['id'];
            $where['site_id'] = $_W['site']['id'];
            $where['last_send'] = [">", time() - $keyword['gap_minute'] * 60];
            $test = set_model('sites_wechat_keyword_log')->where($where)->find();
            if ($test) {
                return false;
            }
        }
        return array(
            'message' => $message,
            'module' => $keyword['module'],
            'rule' => $keyword,
            'priority' => isset($keyword['listorder']) ? $keyword['listorder'] : 0
        );
    }

    /**
     * 分析消息
     * @param $message
     * @return array|mixed
     */
    private function analyze(&$message)
    {
        $params = array();

        $function_name = 'analyze_' . strtolower($message['type']);
        if (method_exists($this, $function_name)) {
            $temp = call_user_func_array(array($this, $function_name), array(&$message));
            if (!empty($temp) && is_array($temp)) {
                $params += $temp;
            }
        } else {
            WechatUtility::logging('info', "$function_name Not Exist Perform handler");
            $params += $this->handler($message['type']);
        }
        return $params;
    }


    /**
     * 默认消息处理
     * @param $type
     * @return array
     */
    private function handler($type)
    {
        return array();
    }


    /**
     * 处理关键字回复
     * @param $param
     * @return bool
     * @throws \think\exception\DbException
     */
    private function process($param)
    {
        $param['module'] = $param['module'] ? $param['module'] : 'wechat';
        if (!module_exist($param['module'])) {
            WechatUtility::logging("module not exist :" . $param['module'], $param['module']);
            return false;
        } else {
            WechatUtility::logging("redirect to module :" . $param['module'], $param['module']);
        }

        $processor = WechatUtility::create_module_processor($param['module']);
        $processor->message = $param['message'];
        $processor->rule = $param['rule'];
        $response = $processor->respond();
        if (empty($response)) {
            return false;
        }

        return $response;
    }


    public function check_site_status()
    {
        //expired
        $expired = false;
        $closed = false;
        if ($expired || $closed) {
            $this->died("对不起 您的账号已过期");
        }

    }

    public function died($content = '')
    {
        global $_W, $engine;
        if (empty($content)) {
            exit('');
        }
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'text';
        $response['Content'] = htmlspecialchars_decode($content);
        $response['CreateTime'] = SYS_TIME;
        $response['FuncFlag'] = 0;
        $xml = array2xml($response);
        if (!empty($_GET['encrypt_type']) && $_GET['encrypt_type'] == 'aes') {
            $resp = $this->account->encryptMsg($xml);
            $resp = $this->account->xmlDetract($resp);
        } else {
            $resp = $xml;
        }
        exit($resp);
    }
}

