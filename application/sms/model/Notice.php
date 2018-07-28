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
namespace app\sms\model;

use app\common\model\Common;
use app\common\model\Sites;
use app\common\model\SitesWechat;
use app\common\util\PHPMailer\PHPMailer;
use app\common\util\wechat\wechat;
use app\wechat\util\MhcmsWechatEngine;
use app\wechat\util\WechatUtility;
use think\Db;
use think\exception\DbException;
use think\Log;
use think\Request;

class Notice extends Common
{
    const WX_MSG = "wxmsg";

    public static function send($notice_name, $msg_type, $target, $params, $tpl_config = [])
    {
        $notice = self::get(['tpl_name' => $notice_name]);
        if ($notice) {

            switch ($msg_type) {
                case "wxmsg":
                    return $notice->send_wxmsg($target, $params, $tpl_config);
                    break;
                case "email" :
                    break;
            }
        }
    }

    /**
     * 发送模板消息消息
     * @param $open_id :target
     * @param $params :tpl params
     * @param array $outer_tpl_config
     * @return bool
     * @throws DbException
     */
    public function send_wxmsg($open_id, $params, $outer_tpl_config = [])
    {
        global $_W;
        $params['siteroot'] = $_W['siteroot'];
        $wechat_api = MhcmsWechatEngine::create($_W['account']);

        if (!$wechat_api) {
            return;
        }
        $where = [];

        $where['site_id'] = ["IN", [$_W['root']['site_id'], $_W['site']['id']]];


        $where['notice_id'] = $this->id;

        //如果没有提供模板信息 获取配置的模板信息
        $tpl_config = WeixinMsgconfig::get($where);
        $wxtpl_id = $tpl_config['wxtpl_id'];

        if (!$outer_tpl_config) {
            $tpl_config = WeixinMsgconfig::get($where);
            $msg_data = $tpl_config['data'];
            $message['url'] = parseParam($tpl_config["tp_url"], $params);
        } else {
            $msg_data = $outer_tpl_config;
            $message['url'] = parseParam($outer_tpl_config["tp_url"], $params);
        }

        foreach ($msg_data as $k => $v) {
            if ($k != "miniprogram") {
                $v['value'] = parseParam($v['value'], $params);
            } else {

                $mini = $v;
                unset($v[$k]);
                unset($msg_data["miniprogram"]);

                foreach ($mini as $kk => $vv) {
                    $mini[$kk] = parseParam($vv, $params);
                }
                continue;

            }

            $msg_data[$k] = $v;
        }
        if (empty($message['url'])) {
            unset($message['url']);
        }


        $rea = $wechat_api->sendTplNotice($open_id, $wxtpl_id, $msg_data, $message['url'], $mini);

        WechatUtility::logging("send tpl msg" ,$rea );

        if ($rea['code'] == 0) {
            return true;
        } else {
            return $rea;
        }
    }

    /**
     * @param $mobile
     * @param $params
     * @param int $site_id
     * @return array
     * @throws DbException
     */
    public function send_sms_old($mobile, $params, $site_id = 0)
    {
        $site_id = (int)$site_id ? $site_id : $GLOBALS['site_id'];
        if (!is_phone($mobile)) {
            return [
                'code' => 1,
                'msg' => "您的手机号码不正确"
            ];
        }


        $AliSMS = new SmsGateWay();
        //send方法三个参数分别为：手机号，变量参数，模板编号
        $where = [];
        $where['site_id'] = $site_id;
        $where['notice_id'] = $this->id;
        $tpl_config = SmsConfig::get($where);

        if (!$tpl_config || empty($tpl_config['tpl_id'])) {
            return false;
        }
        $tpl_id = $tpl_config['tpl_id'];
        $msg_data = $tpl_config['data'];

        foreach ($msg_data as $k => $v) {
            $v['value'] = parseParam($v['value'], $params);
            $msg_data[$k] = $v;
        }
        $params_tosend = [];
        foreach ($msg_data as $k => $v) {
            $params_tosend[$k] = $v['value'];
        }
        $resp = $AliSMS->send($mobile, $params_tosend, $tpl_id);

        $report = [];
        $report['create_time'] = date("Y-m-d H:i:s");
        $report['target'] = $mobile;
        $report['content'] = $msg_data;
        $report['user_id'] = 0;
        $report['tpl_id'] = $tpl_id;
        //$report['log'] = $resp;
        var_dump($resp);
        SmsReport::create($report);
        if ($resp) {
            return [
                'code' => 0,
                'msg' => '发送成功'
            ];
        } else {
            return [
                'code' => 0,
                'msg' => '发送失败'
            ];
        }
    }

    /**
     * @param $mobile
     * @param $params
     * @return array
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function send_sms($mobile, $params, $site_id = 0)
    {
        global $_W;
        //todo get latest unused item and cal send seconds
        $report = SmsReport::where(['ip' => Request::instance()->ip(), 'status' => 0])->order('id desc')->find();
        if ($report) {
            $sec = strtotime(date("Y-m-d H:i:s")) - strtotime($report['create_at']);
            $gap = 60;
            if ($sec < $gap) {
                return [
                    'code' => 3,
                    'msg' => "还要等" . ($gap - $sec) . " 秒才可以再次发送！"
                ];
            } else {
                // clear expired
                $report = SmsReport::where(['ip' => Request::instance()->ip(), 'status' => 0, 'create_at' => ['LT', date('Y-m-d', strtotime('-7 days'))]])->order('id desc')->delete();
            }
        }

        //create provider obj
        if (!is_phone($mobile)) {
            return [
                'code' => 3,
                'msg' => "您的手机号码不正确"
            ];
        }

        /**
         *
         */
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['notice_id'] = $this->id;
        try {
            $tpl_config = set_model('sms_config')->where($where)->find();
            if (!$tpl_config || empty($tpl_config['tpl_id'])) {
                return false;
            }
        } catch (DbException $e) {
            Log::write("SMS DbException");
            return false;
        }

        $tpl_id = $tpl_config['tpl_id'];
        $msg_data = json_decode($tpl_config['data'], 1);
        foreach ($msg_data as $k => $v) {
            $v['value'] = parseParam($v['value'], $params);
            $msg_data[$k] = $v;
        }

        $params_tosend = [];
        foreach ($msg_data as $k => $v) {
            $params_tosend[$k] = $v['value'];
        }


        //todo get the provide
        $sms_site_config = set_model('sms_site_config')->where(['status' => 99])->find();
        $provider = set_model('sms_provider')->where(['id' => $sms_site_config['provider_id']])->find();
        $class = "\app\sms\providers\\" . $provider['sign'];
        $provider_controller = new $class($sms_site_config);
        $resp = $provider_controller->send($mobile, $params_tosend, $tpl_id);


        if ($resp['code'] == 1) {
            $report = [];
            $report['create_at'] = date("Y-m-d H:i:s");
            $report['type'] = "sms";
            $report['target'] = $mobile;
            $report['content'] = $msg_data;
            $report['sender_uid'] = 0;
            $report['receiver_uid'] = 0;
            $report['tpl_id'] = $tpl_id;
            $report['notice_id'] = $this->id;
            $report['log'] = $resp['log'] . "-";
            $report['site_id'] = $_W['site']['id'];
            $report['ip'] = Request::instance()->ip();
            $report['provider_id'] = $provider['id'];
            SmsReport::create($report);

            return [
                'code' => 1,
                'msg' => '发送成功',
                'resp' => $resp
            ];
        } else {
            return [
                'code' => 3,
                'msg' => '发送失败',
                'resp' => $resp
            ];
        }
    }

    /**
     * @param $toemail
     * @param int $site_id
     * @param array $cfg
     * @return array
     * @throws DbException
     * @throws \app\common\util\PHPMailer\phpmailerException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function send_email($toemail, $sender_uid = 0, $target_uid = 0, $site_id = 0, $cfg = array())
    {
        global $_W;
        $where = [];
        $where['site_id'] = $site_id ? $site_id : $_W['site']['id'];
        $where['notice_id'] = $this->id;
        $tpl_config = Db::name('email_config')->where($where)->find();

        if (!$tpl_config) {
            $res = [
                'code' => 0,
                'msg' => '发送失败，因为模板还没有配置！'
            ];
            return $res;
        }

        $resp = sendmail($toemail, $this->tpl_name, $tpl_config['content']);
        if ($resp['code'] == 1) {
            $report = [];
            $report['create_at'] = date("Y-m-d H:i:s");
            $report['type'] = "email";
            $report['target'] = $toemail;
            $report['content'] = $tpl_config['content'];
            $report['sender_uid'] = $sender_uid;
            $report['receiver_uid'] = $target_uid;
            $report['tpl_id'] = $tpl_config['id'];
            $report['notice_id'] = $this->id;
            $report['log'] = $resp['log'] ? $resp['log'] : "";
            $report['site_id'] = $_W['site']['id'];
            $report['ip'] = Request::instance()->ip();
            $report['provider_id'] = "mhcms";
            SmsReport::create($report);
            return [
                'code' => 1,
                'msg' => '发送成功',
                'data' => $resp
            ];
        } else {
            return $resp;
        }

    }
}
