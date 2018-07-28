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
namespace app\core\controller;

use app\common\controller\AdminBase;
use app\common\model\Roots;
use app\common\model\Sites;
use app\common\model\Models;
use app\common\util\Tree2;
use think\Config;
use think\Db;
use think\Session;


class System extends AdminBase{
    public function icon(){
        $this->view->icons = set_model("icon")->select();
        return $this->view->fetch();
    }
}