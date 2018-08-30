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
use app\common\model\Users;
use app\common\util\forms\date;
use app\common\util\forms\select;
use app\common\util\Tree2;
use think\Db;
use think\Log;

class AdminEsf extends AdminBase
{
    private $house_esf = "house_esf";

    public function index()
    {
        global $_W;
        $this->view->filter_info = Models::gen_admin_filter($this->house_esf, $this->menu_id);
        $where = $this->view->filter_info['where'];

        $user_name = trim(input('param.user_name', ' ', 'htmlspecialchars'));
        $update_time = trim(input('param.update_time', '', 'htmlspecialchars'));
        $create_time = trim(input('param.create_time', '', 'htmlspecialchars'));
        $esf_name = trim(input('param.esf_name', '', 'htmlspecialchars'));
        $mobile = trim(input('param.mobile', '', 'htmlspecialchars'));


        if ($mobile) {
            $where['mobile'] = array('LIKE', '%' . $mobile . '%');
            $this->view->assign('mobile', $mobile);
        }

        if ($update_time) {
            $where['update_at'] = array('LIKE', '%' . $update_time . '%');
        }

        if ($create_time) {
            $where['create_at'] = array('LIKE', '%' . $create_time . '%');
        }

        if ($esf_name) {
            $where['title'] = array('LIKE', '%' . $esf_name . '%');
        }

        if ($user_name) {
            $where_user['user_name'] = $user_name;
            $user_model = Users::get($where_user);
            $where['user_id'] = $user_model['id'];
        }

        if (!$this->super_power) {
            $where['user_id'] = $this->user['id'];
        }

        $content_model_id = $this->house_esf;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['site_id'] = $_W['site']['id'];

        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public function add($loupan_id = 0)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_esf);

        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
                $where['mobile'] = $base_info['mobile'];
                $where['address'] = $base_info['address'];
                $where['area_id'] = $base_info['area_id'];
                $where['site_id'] = $_W['site']['id'];
                $where['title'] = $base_info['title'];
                $find_data = set_model($this->house_esf);
                $find_data = $find_data->where($where)->find();
//                Log::error("where==" . json_encode($where)."====".$model_info);
                if ($find_data) {
                    return $this->zbn_msg("不可添加重复房源", 2);
                }
            }
            if (!isset($base_info['top_expire']) || $base_info['top_expire'] == '' || empty($base_info['top_expire'])) $base_info['top_expire'] = gmdate("Y-m-d H:i:s");

            $base_info['loupan_id'] = $loupan_id;
            $base_info['user_id'] = $this->user['id'];
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'close_page()'");
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
        $model = set_model($this->house_esf);
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

            if (!isset($base_info['top_expire']) || $base_info['top_expire'] == '' || empty($base_info['top_expire'])) $base_info['top_expire'] = gmdate("Y-m-d H:i:s");
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
        $model = set_model($this->house_esf);
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

        if ($detail) {
            $model_info::delete_item($id, $this->house_esf);
        }

//        return ['code' => 1, 'msg' => 'ok'];
        return $this->zbn_msg('ok', 1, 'true', 1000, "''", "window.location.reload()");
    }

    public function record($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_esf);
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
                if (!isset($base_info['top_expire']) || $base_info['top_expire'] == '' || empty($base_info['top_expire'])) $base_info['top_expire'] = gmdate("Y-m-d H:i:s");
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