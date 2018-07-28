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
namespace app\home\controller;

use app\common\controller\App;
use app\common\controller\Base;
use app\common\controller\ModuleBase;
use think\Controller;
use think\Cookie;

class Share extends ModuleBase
{
    //share personal link
    public function s($id)
    {
        $id = (int)$id;
        $share = set_model("share")->where(['id' => $id])->find();
        if ($share) {
            $refer = $share['user_id'];
            Cookie::set('refer', (int)$refer);
        }
        if (!$share['url']) {
            $url = url('/home');
        } else {
            $url = $share['url'];
        }
        header('location:' . $url);
        die();
    }

    //share product
    public function sp()
    {

    }
}