<?php

namespace app\common\exception;

use app\common\controller\Base;
use app\sms\model\Notice;
use app\wechat\util\MhcmsWechatEngine;
use app\wechat\util\WechatUtility;
use think\Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\Request;

class Http extends Handle
{
    public function render(\Exception $exception)
    {
        global $_W, $_GPC;


        if (!isset($_W['develop']) || !$_W['develop']) {
            die();
        }

        $where['ip'] = Request::instance()->ip();
        $where['is_bad'] = 1 ;
        $bad_ip = set_model("bad_ip")->where($where)->find();
        if($bad_ip){
            die();
        }

        $where = [];
        $where['ip'] = Request::instance()->ip();
        $bad_ip = set_model("bad_ip")->where($where)->find();
        if($bad_ip){
            set_model("bad_ip")->where($where)->setInc('error_count');
        }else{
            set_model("bad_ip")->insert($where);
        }

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        }
        //todo send tpl msg
        $request = Request::instance();
        $_sw = $_W['account'] = set_model("sites_wechat")->find();
        $api = MhcmsWechatEngine::create($_sw);

        $agent = $request->header('user-agent');
        $skip_agents = ['Sogou','bot','Bot',];
        $skip = false;
        foreach($skip_agents as $skip_agent){
            if(strpos($agent , $skip_agent) !==false){
                $skip = true;
                break;
            }
        }

        if (!$skip && $_sw && $api) {

            $this->root =$_W['root'] = Base::get_root();
            $_W['global_config'] = config('mhcms_' . $this->root['id'] . '.mhcms_config');
            $_W['tpl_config'] = '{"first":{"value":"{header}","color":"#000000"},"remark":{"value":"{footer}","color":"#000000"},"keyword1":{"value":"{keyword1}","color":"#000000"},"keyword2":{"value":"{keyword2}","color":"#000000"},"keyword3":{"value":"{keyword3}","color":"#000000"},"miniprogram":{"appid":"","pagepath":""}}';

            $tpl_config = mhcms_json_decode($_W['tpl_config']);
            $params['header'] = "网站出错了!，" .  get_url();
            $params['keyword1'] = "网站出错了!，" .  $exception->getMessage();
            $params['footer'] =  Request::instance()->ip() .  HTTP_REFERER . $request->header('user-agent');
            $tpl_config['tp_url'] = get_url();
            $res = Notice::send('系统通知', 'wxmsg', $_W['global_config']['secure']['admin_openid'], $params, $tpl_config);

            $admin = check_admin();
            if(!$admin){
               redirect("http://www.".$this->root['root_domain'] , '' , 404);
            }
        }

        WechatUtility::logging("------SITE ERROR-------" , get_url()
            . " # " . $exception->getMessage()
            . " # " . Request::instance()->ip()
            . " # " . HTTP_REFERER
            . " # " .$request->header('user-agent')
            . " # ". " # ");


        //todo count bad ip error count

        if (!isset($_W['develop']) || !$_W['develop']) {
            return parent::render($exception);
        } else {
            return parent::render($exception);
        }
    }
}