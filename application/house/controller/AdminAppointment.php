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
use app\common\util\Money;
use app\common\util\Point;
use app\common\util\Tree2;
use app\core\util\MhcmsDistribution;
use think\Db;
use think\Exception;
use think\Log;


class AdminAppointment extends AdminBase
{

    private $house_appointment = "house_appointment";

    /**
     * @return string
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function index()
    {
        global $_W;
        $content_model_id = $this->house_appointment;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];

        if (!$this->super_power){
            $ids = array();
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                $ids = $this->map_city_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $ids = $this->map_county_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            } elseif ($users['user_role_id'] == 25) {
                // CEO（区域经理）
                $ids = $this->map_area_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            } elseif ($users['user_role_id'] == 26) {
                // 省级代理
                $ids = $this->map_province_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            }else{
                array_push($ids, $this->user['id']);
            }

            $where['user_id'] = array('IN', $ids);
        }

        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }


    public function add()
    {
        global $_W, $_GPC;
        $content_model_id = $this->house_appointment;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');
            }

            $base_info['user_id'] = $this->user['id'];
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
        $model = set_model($this->house_appointment);
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

            $base_info['user_id'] = $this->user['id'];
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

    /**
     * 匹配二手房源
     * @param $id
     */
    public function match_esf($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_appointment);
        $where['id'] = $id;
        $detail = $model->where($where)->find();
        //小区、面积、楼层、装修
        $content_model_id = 'house_esf';
        $esf_model = set_model($content_model_id);
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['xiaoqu_id'] = $detail['xiaoqu_id'];
        $where['size'] = $detail['size'];
        $where['floor'] = $detail['floor'];
        $where['zhuangxiu'] = $detail['zhuangxiu'];

        $model_info = $esf_model->model_info;
        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        return $this->view ->fetch();
    }


    public function delete($id)
    {

        global $_W, $_GPC;
        $need_check_distribute_order = false;
        $model = set_model($this->house_appointment);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        if ($detail) {
            $detail = $model->where($where)->delete();
        }
        $this->zbn_msg("删除成功！");
    }
}