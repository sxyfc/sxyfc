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
namespace app\home\controller;

use app\common\controller\App;
use app\common\controller\Base;
use app\common\controller\ModuleBase;
use think\Controller;

class Index extends ModuleBase {
    public function index(){
        global $_W;

        if($_W['site']['config']['default_app']){
            $default_app = $_W['site']['config']['default_app'];
        }else{
            $default_app = $_W['global_config']['default_app'];
        }
        if($default_app == "sites" && $this->site['site_domain'] == "www"){
        //    $default_app = 0;
        }

        if(!$default_app){
            $default_app = $_W['root']['default_app'];
        }

        if($default_app && $default_app!=="home"){
            $url = url('/' . $default_app);
            header("location:$url");die();
        }else{

            $where = [];
            $where['is_o2o'] = 1;
            $this->view->modules = set_model("modules")->where($where)->select();
            return $this->view->fetch();
        }

    }
}