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
namespace app\common\controller;
use app\common\model\Linkage;
use app\common\model\UserMenu;
use think\Cookie;
class HomeBase extends ModuleBase {
    /**
     *
     */
    public $theme;

    public function map_fenzhan($map_old = []){
        $map = [];
        $map['site_id'] = $GLOBALS['site_id'];
        return array_merge($map,$map_old);
    }
}