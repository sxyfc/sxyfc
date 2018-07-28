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
use app\common\controller\ModuleUserBase;
use app\common\model\Models;
use app\common\util\Tree2;
use think\Db;

class Appointment extends ModuleUserBase{
    private $house_appointment = "house_appointment";

    public function self_create($item_id = 0 , $model_id = 0){
        global $_W, $_GPC;global $_W, $_GPC;
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
                $base_info = input('post.data/a');//get the base info
            }
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'] . " 感谢您的预约， 稍后将有工作人员与您联系，祝您生活愉快！", 1, 'true', 1000, "'/house/user'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_user_publish_fields([ ] , ['loupan_id']);
            $this->view->page_title = "看房团报名";
            return $this->view->fetch();
        }
    }
    public function kft_create($kft_id){
        global $_W, $_GPC;
        $content_model_id = $this->house_appointment;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $this->view->kft = Models::get_item($kft_id , 'house_kft');
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['kft_id'] = $kft_id;
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'] . " 感谢您的预约， 稍后将有工作人员与您联系，祝您生活愉快！", 1, 'true', 1000, "'/house/user'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_user_publish_fields(['kft_id' => (int) $kft_id] , ['loupan_id']);
            $this->view->page_title = "看房团报名";
            return $this->view->fetch();
        }
    }
    public function create_loupan($loupan_id){
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
                $base_info = input('post.data/a');//get the base info
            }

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'] . " 感谢您的预约， 稍后将有工作人员与您联系，祝您生活愉快！", 1, 'true', 1000, "'/house/user'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_user_publish_fields(['loupan_id' => (int) $loupan_id]);
            $this->view->page_title = "预约看房";
            return $this->view->fetch();
        }
    }


}