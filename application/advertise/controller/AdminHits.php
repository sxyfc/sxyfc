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
namespace app\advertise\controller;

use app\advertise\model\Advertise;
use app\common\controller\AdminBase;
use app\common\model\Models;
use think\Db;

class AdminHits extends AdminBase
{
    private $hits = "hits";
    public function index(){

        $where = [];
        $model = set_model($this->hits);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        //data list 如果不是超级管理员 并且数据是区分站群的
        if (!$this->super_power && Models::field_exits('site_id', $this->hits)) {
            $where['site_id'] = $this->site['id'];
        }
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();// assign('field_list', $new_field_list);
        //model_info
        $this->view->assign('model_info', $model_info);
        //data list
        $lists = Db::name($model_info['table_name'])->where($where)->order('id desc')->paginate();
        $this->view->lists = $lists;
        return $this->view->fetch();
    }
    public function edit($id)
    {
        $id = (int)$id;
        $model = set_model($this->hits);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();

        $this->check_admin_auth($detail);
        if ($this->isPost() && $model_info) {
            $data = input('param.data/a');
            // todo  process data input
            Db::name($model_info['table_name'])->where($where)->update($data);
            $this->zbn_msg("ok");
        } else {
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            //assign('list', $new_field_list);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }
}