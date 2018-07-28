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

class AdminPosition extends AdminBase
{

    public $position = "position";

    public function index()
    {
        global $_W;
        $content_model_id = $this->position;
        $model = set_model($content_model_id);
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public function add()
    {
        $model = set_model($this->position);
        $model_info = $model->model_info;

        if ($this->isPost() && $model_info) {
            $base_info = input('post.data/a');//get the base info

            if (Models::field_exits('site_id', $model_info['id'])) {
                $base_info['site_id'] = $this->site['id'];
            }
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->list = $model_info->get_admin_publish_fields([], true);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function edit($id)
    {
        global $_W;
        $id = (int)$id;
        $model = set_model($this->position);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $where = ['id' => $id , 'site_id' => $_W['site']['id']];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        $this->check_admin_auth($detail);
        if ($this->isPost() && $model_info) {
            $data = input('param.data/a');
            // todo  process data input
            Db::name($model_info['table_name'])->where($where)->update($data);
            $this->zbn_msg("ok");
        } else {
            //todo auth
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        set_model($this->position)->where(['id' => $id])->delete();
        set_model($this->position_data)->where(['position_id' => $id])->delete();
        return ['code' => 0, 'msg' => '删除成功'];
    }
}