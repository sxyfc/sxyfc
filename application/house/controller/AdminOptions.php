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

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\util\Tree2;
use think\Db;

class AdminOptions extends AdminBase
{

    public function index(){
        //load all models in modules

        $this->view->models = Models::all(['module' => ROUTE_M]);

        return $this->view->fetch();
    }

}