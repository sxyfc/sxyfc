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

class AdminOptions extends AdminBase
{
    private $option = "option";


    public function index_options(){
        //load all models in modules

        $this->view->models = Models::all(['module' => ["IN" , ['core' , 'system']]]);

        return $this->view->fetch();
    }




    public function index($field_name, $model_id)
    {
        global $_W;
        $model = set_model($this->option);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $bind_model = set_model($model_id);
        $where['field_name'] = $field_name;
        $where['model_id'] = $bind_model->model_info['id'];

        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->pages = $this->view->lists->render();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $this->option;
        $this->mapping['model_id'] = $model_id;
        $this->mapping['field_name'] = $field_name;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public function add($field_name, $model_id)
    {
        global $_W, $_GPC;

        $target_model = set_model($model_id);
        $model = set_model($this->option);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //todo dynamic bind module
        $bind_model = set_model($model_id);
        $model_info->module = $bind_model->model_info['module'];
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['model_id'] = $target_model->model_info['id'];
            $base_info['field_name'] = $field_name;
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields([]);
            return $this->view->fetch();
        }

    }


    public function edit($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->option);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

        //todo dynamic bind module
        $bind_model = set_model($detail['model_id']);
        $model_info->module = $bind_model->model_info['module'];

        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }


            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, []);
            $detail['data'] = mhcms_json_decode($detail['data']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->option);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

        if ($detail) {
            $model->where($where)->delete();
        }

        return ['code' => 1, 'msg' => 'ok'];
    }
}