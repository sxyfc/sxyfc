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
use app\core\util\ContentTag;
use app\core\util\MhcmsMenu;

class EmployeeUser extends HouseUserBase {
    public function index(){

        $this->view->seo = $this->seo($this->mapping);

        $menu = new MhcmsMenu();
        $this->view->menus = $menu->get_member_module_menu("house");
        return $this->view->fetch();
    }
}