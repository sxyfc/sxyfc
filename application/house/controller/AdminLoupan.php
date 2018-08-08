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

class AdminLoupan extends AdminBase
{
    private $house_loupan = "house_loupan";

    public function index()
    {
        global $_W;
        $model = set_model($this->house_loupan);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        if (!$this->sub_super && !$this->super_power) {
            $where['user_id'] = $this->admin_info['user_id'];
        }

        $loupan_name = trim(input('param.loupan_name', ' ', 'htmlspecialchars'));
        if ($loupan_name) {
            $where['loupan_name'] = array('LIKE', '%' . $loupan_name . '%');
            $this->view->assign('loupan_name', $loupan_name);
        }

        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $this->house_loupan;
        $this->view->mapping = $this->mapping;
        $positions = set_model('position')->where('site_id = ' . $_W['site']['id'])->select();
        $this->view->positions = $positions;
        return $this->view->fetch();
    }

    public function add()
    {
        global $_W, $_GPC;
        $model = set_model($this->house_loupan);
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
            $base_info['sites_wechat_id'] = $_W['account']['id'];
            $data['first'] = array("value" => $_GPC ['tp_first'],
                "color" => $_GPC ['firstcolor'],
            );
            $data['remark'] = array("value" => $_GPC ['tp_remark'],
                "color" => $_GPC ['remarkcolor'],
            );
            for ($i = 0; $i < count($_GPC['keyword']); $i++) {
                if ($_GPC['keyword'][$i]) {
                    $data[$_GPC['keyword'][$i]] = array(
                        "value" => $_GPC['value'][$i],
                        "color" => $_GPC['color'][$i],
                    );
                }
            }
            $base_info['data'] = json_encode($data);

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
        $model = set_model($this->house_loupan);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        if (!$this->sub_super && !$this->super_power) {
            $where['user_id'] = $this->admin_info['user_id'];
        }
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

    public function delete($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_loupan);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        if (!$this->sub_super && !$this->super_power) {
            $where['user_id'] = $this->admin_info['user_id'];
        }
        $detail = $model->where($where)->find();
        if ($detail) {
            $model->where($where)->delete();
            //
            //delete huxing
            set_model("house_loupan_ask")->where(['loupan_id' => $id])->delete();
            //delete ask

            set_model("house_loupan_huxing")->where(['loupan_id' => $id])->delete();
            set_model("house_loupan_building")->where(['loupan_id' => $id])->delete();
            set_model("house_loupan_product")->where(['loupan_id' => $id])->delete();

        }

        return ['code'=>1 , 'msg' => '删除完成'];
    }
}