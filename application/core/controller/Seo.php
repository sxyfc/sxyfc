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
namespace app\core\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\util\forms\input;
use think\Db;

class Seo extends AdminBase
{
    public function index($module = "")
    {
        global $_W;
        $this->view->filter_info = Models::gen_admin_filter("seo", $this->menu_id);

        $where = [];
        $keyword = input('param.keyword');
        if ($keyword) {
            $where = [];
            $where['seo_key'] = ['like', "%$keyword%"];
        }
        $where['site_id'] = $_W['site']['id'];
        if (!$module) {
            $list = Db::name("seo")->where($where)->select();
            $pages = "";
        } else {
            $where['module'] = $module;
            $list = Db::name("seo")->where($where)->paginate();
            $pages = $list->render();
        }

        $this->view->pages = $pages;
        $this->view->list = $list;
        $this->view->keyword = $keyword;
        return $this->view->fetch();
    }

    public function delete($id){
        global $_W;
        $id = (int) $id;
        $where['site_id'] = $_W['site']['id'];
        $where['id'] = $id;
        Db::name("seo")->where($where)->delete();


        $ret['code'] = 1;
        $ret['msg'] = "删除完成";

        return $ret;
    }
}