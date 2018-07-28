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
namespace app\sms\controller;

use app\common\controller\UserBase;
use app\common\model\Users;
use app\sms\model\Notice;

class Api extends UserBase
{


    public function send_code($mobile)
    {
        //todo send sms_code
        $where = [];
        $where['user_name'] = $mobile;
        $user = Users::get($where);
        if ($user && $user['id'] != $this->user['id']) {
            return [
                'code' => 2,
                'msg' => '该手机号吗已经绑定了其他账号了 !' . $user['id'] . "_".$this->user['id']
            ];
        }

        if($user['id'] == $this->user_id && $this->user['is_mobile_verify']){
            return [
                'code' => 2,
                'msg' => '您的手机已经通过认证了'
            ];
        }

        $notice = Notice::get(['tpl_name' => '验证码发送']);
        //Notice::send_sms();
        $code = mt_rand(1000, 9999);
        $resp = $notice->send_sms($mobile, ['yzm' => $code]);

        return $resp;
    }
}