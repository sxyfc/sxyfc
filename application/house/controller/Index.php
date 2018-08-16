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
namespace app\house\controller;

use app\common\controller\HomeBase;
use app\common\controller\ModuleBase;
use app\core\util\ContentTag;
use app\sso\controller\Passport;

class Index extends HouseBase {

    public function index(){
        if (is_weixin() && !$this->user) {
            $passport = new Passport();
            $passport->wx_register();
        }
        $this->view->view = $this->view;
        return $this->view->fetch();
    }

    public function entry_publish(){

        $this->view->view = $this->view;
        return $this->view->fetch();
    }
}