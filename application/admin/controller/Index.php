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
        global $_W;

        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"><span style="font-size:30px">'.$_W['site']['config']['system_name'].'</span></p></div></script>';
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