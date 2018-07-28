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
namespace app\wechat_follow\mhcms_classes;

use app\wechat\util\WechatProcessor;
use app\wechat\util\WechatUtility;
use think\Log;

class WechatFollowProcessor extends WechatProcessor
{
    public function respond()
    {
        global $_W;
        $config = set_model('wechat_follow')->where(['site_id' =>  $_W['site']['id']])->find();


        if($this->message['type'] =="trace"){

            if($config['trace_rule_id']){
                $keyword = set_model('sites_wechat_keyword')->where(['id' =>$config['trace_rule_id'] ])->find();
                $par = $_W['engine']->make_rule($keyword , $this->message);
                $_W['engine']->do_response([$par]);
            }
        }


        if($this->message['type'] =="subscribe"){

            if($config['follow_rule_id']){
                $keyword = set_model('sites_wechat_keyword')->where(['id' =>$config['follow_rule_id'] ])->find();
                $par = $_W['engine']->make_rule($keyword , $this->message);
                $_W['engine']->do_response([$par]);
            }


            //return $this->respText("您好，欢迎关注本公众号" . $_W['fans']['nickname']);
        }
    }

}