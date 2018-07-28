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
use app\common\model\Hits;
use app\common\model\Models;
use app\core\util\ContentTag;
use think\Db;

class Search extends HouseBase
{
    public function index(){

        return $this->view->fetch();
    }

}