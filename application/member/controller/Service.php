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
namespace app\member\controller;

use app\common\controller\Base;
use app\common\model\UserMenu;
use app\core\util\MhcmsMenu;
use think\Db;

class Service extends Base
{



    /**
     * 异步加载
     * @param string $module
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function load_menu($module = "")
    {
        $menu = new MhcmsMenu();
        return $menu->get_member_menu($module);
    }


}