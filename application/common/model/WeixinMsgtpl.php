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

use anerg\Alidayu\SmsGateWay;
use app\common\util\wechat\wechat;
use think\Db;
use think\Log;

class WeixinMsgtpl extends Common
{
    /**
     * 发送模板消息消息
     * @param $open_id :target
     * @param $params  :tpl params
     * @param int $site_id :site_id
     * @return bool
     */
    public function send($open_id  , $params , $site_id = 0){

        $site_id =(int) $site_id ? $site_id : $GLOBALS['site_id'];

        $site = Sites::get($site_id);
        $site_wechat = SitesWechat::get(['site_id'=>$site_id]);
        $wechat = new wechat($site_wechat);

        $token = $wechat->getAccessToken();
        $send_api = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token";

        $where = [];
        $where['site_id'] = $site_id;
        $where['msgtpl_id'] = $this->id;
        $tpl_config = WeixinMsgconfig::get($where);

        $msg_data = $tpl_config['data'];

        foreach($msg_data as $k=>$v){
            $v['value'] = parseParam($v['value'] , $params);
            $msg_data[$k] = $v;
        }
        if($tpl_config){
            $message['template_id'] = $tpl_config["wxtpl_id"];
            $message['url'] = nb_url($tpl_config["tp_url"] , $site['site_domain'] .  $GLOBALS['root_domain']) ;
            $message['data'] = $msg_data;
            $message['touser'] = $open_id;
            $rea = ihttp_post($send_api, json_encode($message));
            /*
             *Log::write("！！！！！！！！！！！！！！！！===========================");
            Log::write($rea);
            Log::write("！！！！！！！！！！！！！！！！===========================");
              */
            $rea = json_decode($rea['content'] , true);
            if ($rea['errcode'] == 0) {
                return true;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }


    public function send_sms($mobile , $params , $site_id = 0){
        $site_id =(int) $site_id ? $site_id : $GLOBALS['site_id'];
        if(!is_phone($mobile)){
            return [
                'code' => 1,
                'msg' => "您的手机号码不正确"
            ];
        }


        $AliSMS = new SmsGateWay();
        //send方法三个参数分别为：手机号，变量参数，模板编号
        $where = [];
        $where['site_id'] = $site_id;
        $where['msgtpl_id'] = $this->id;
        $tpl_config = SmsConfig::get($where);
        if(!$tpl_config || empty($tpl_config['tpl_id'])){
            return false;
        }
        $tpl_id = $tpl_config['tpl_id'];
        $msg_data = $tpl_config['data'];

        foreach($msg_data as $k=>$v){
            $v['value'] = parseParam($v['value'] , $params);
            $msg_data[$k] = $v;
        }
        $params_tosend = [];
        foreach($msg_data as $k=>$v){
            $params_tosend[$k] = $v['value'];
        }

        $resp = $AliSMS->send($mobile, $params_tosend , $tpl_id);

        $report = [];
        $report['create_time'] = date("Y-m-d H:i:s");
        $report['target'] = $mobile;
        $report['content'] = $msg_data;
        $report['user_id'] = 0;
        $report['tpl_id'] = $tpl_id;
        $report['log'] = $resp;

        SmsReport::create($report);
        if($resp){
            return[
                'code' => 0,
                'msg' => '发送成功'
            ];
        }else{
            return [
                'code' => 0,
                'msg' => '发送失败'
            ] ;
        }
    }
}
