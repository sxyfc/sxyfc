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
namespace app\attachment\controller;

use app\common\controller\AdminBase;
use app\common\controller\Base;
use app\common\model\File;

class AdminAttch extends AdminBase
{

    public function index(){
        return $this->view->fetch();
    }
}