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

class Map extends HouseBase
{
    private $house_kft = "house_kft";

    public function loupan(){
        header("location:/house/map");
    }
    public function index()
    {


        global $_W, $_GPC;

        $allow_models = ['esf', 'loupan', 'rent'];
        if (in_array($_GPC['model_id'], $allow_models)) {

            $this->view->model_id = $_GPC['model_id'];
        } else {

            $this->view->model_id = "loupan";
        }
        return $this->view->fetch();
    }
    public function esf(){

        global $_W, $_GPC;

        $allow_models = ['esf', 'loupan', 'rent'];
        if (in_array($_GPC['model_id'], $allow_models)) {

            $this->view->model_id = $_GPC['model_id'];
        } else {

            $this->view->model_id = "loupan";
        }
        return $this->view->fetch();

    }
    public function rent(){

        global $_W, $_GPC;

        $allow_models = ['esf', 'loupan', 'rent'];
        if (in_array($_GPC['model_id'], $allow_models)) {

            $this->view->model_id = $_GPC['model_id'];
        } else {

            $this->view->model_id = "loupan";
        }
        return $this->view->fetch();

    }

}