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
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\UserMenu;
use think\Db;

class Index extends AdminBase
{
    /**加载后台主页
     * @return mixed
     */
    public function index()
    {

        return $this->view->fetch();
    }

    public function main()
    {
        $where = [];
        //$where['is_app'] = 1;
        //$where['is_o2o'] = 1;
        //allow modules

        $modules = ['house', 'info', 'gov_task', 'zhaopin'];
        $where['module'] = ['IN', $modules];
        // $this->view->modules = set_model("modules")->where($where)->select();
        $this->view->modules = [];
        $this->view->view = $this->view;

        return $this->view->fetch();
    }

    public function help()
    {
        return $this->view->fetch();
    }

}