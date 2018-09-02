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
use think\Db;

class AdminXiaoqu extends AdminBase
{
    private $house_xiaoqu = "house_xiaoqu";

    public function index()
    {
        global $_W;
        $model = set_model($this->house_xiaoqu);
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];

        $xiaoqu_name = trim(input('param.xiaoqu_name', ' ', 'htmlspecialchars'));
        if ($xiaoqu_name) {
            $where['xiaoqu_name'] = array('LIKE', '%' . $xiaoqu_name . '%');
            $this->view->assign('xiaoqu_name', $xiaoqu_name);
        }

        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $this->house_xiaoqu;
        $this->view->mapping = $this->mapping;
        $positions = set_model('position')->where('site_id = ' . $_W['site']['id'])->select();
        $this->view->positions = $positions;
        return $this->view->fetch();
    }

    public function add()
    {
        global $_W, $_GPC;
        $model = set_model($this->house_xiaoqu);
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                $base_info = $_GPC;
            } else {
                $base_info = input('post.data/a');
            }

            $xiaoqu_name = $base_info['xiaoqu_name'];
            $area_id = $base_info['area_id'];
            if ($resule = Db::table('mhcms_house_xiaoqu')->where(['xiaoqu_name' => $xiaoqu_name, 'area_id' => $area_id])->find()) {
                return $this->zbn_msg('小区已存在', 2, 'true', 1000, "''", "'reload_parent_page()'");
            }

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_parent_page()'");
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
        $model = set_model($this->house_xiaoqu);
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
                $base_info = input('post.data/a');
            }

            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_parent_page()'");
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
        $model = set_model($this->house_xiaoqu);
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        if ($detail) {
            $model->where($where)->delete();
        }

        return ['code' => 1, 'msg' => '删除完成'];
    }
}