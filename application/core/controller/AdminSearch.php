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
use think\Db;

class AdminSearch extends AdminBase
{

    private $models = "models";

    public function index()
    {
        /**
         * 过滤字段
         */
        $ret = Models::gen_admin_filter("models", $this->menu_id);
        $this->view->filter_info = $ret;


        //自定义筛选条件
        $where = $ret['where'];
        $where['is_index'] = 1;
        //获取模型信息
        $model = set_model($this->models);
        $model_info = $model->model_info;
        $this->view->content_model_id = $this->models;
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();
        //model_info
        $this->view->model_info = $model_info;
        //+--------------------------------以下为系统--------------------------
        //模板替换变量
        $this->view->mapping = $this->mapping;
        $this->view->lists = Models::order('table_name asc')->where($where)->select();
        return $this->view->fetch();
    }

    public function re_index($model_id)
    {
        /**
         *     $_index_where['model_id'] = $model_id;
        //todo delete all index
        $res = Db::view('mhcms_index')->where($_index_where)
        ->view('mhcms_index_data', '*', 'mhcms_index_data.id=mhcms_index.id')
        ->select();

         */
        if (Models::field_exits('status', $model_id)) {
            $where['status'] = 99;
        }
        $this->view->limit = 50;


        $total_count = set_model($model_id)->where($where)->count();
        $total_pages = round($total_count / 50);
        $this->view->total_pages = $total_pages;
        $this->view->total_count = $total_count;
        $this->view->model_id = $model_id;
        return $this->view->fetch();
    }
}