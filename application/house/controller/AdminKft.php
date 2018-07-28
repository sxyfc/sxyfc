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

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\util\Tree2;
use think\Db;


class AdminKft extends AdminBase
{
    private $house_kft = "house_kft";

    /**
     * @return string
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function index()
    {
        global $_W;
        $content_model_id = $this->house_kft;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
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
        global $_W, $_GPC;
        $content_model_id = $this->house_kft;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
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

    public function edit($id){
        global $_W, $_GPC;
        $model = set_model($this->house_kft);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

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
}