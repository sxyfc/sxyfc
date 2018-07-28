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
namespace app\sms\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;

class AdminProvider extends AdminBase
{


    public $sms_provider = "sms_provider";

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //自定义筛选条件
        $where = [];
        //获取模型信息
        $content_model_id = $this->sms_provider;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //data list 如果不是超级管理员 并且数据是区分站群的
        if (!$this->super_power && Models::field_exits('site_id', $content_model_id)) {
            $where['site_id'] = $this->site['id'];
        }

        //分配到当前模块
        if (Models::field_exits('module', $content_model_id)) {
            $where['module'] = ROUTE_M;
        }
        $lists = $model->where($where)->order("id desc")->paginate();
        //列表数据
        $this->view->lists = $lists;
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();
        //model_info
        $this->view->model_info = $model_info;
        $this->view->content_model_id = $content_model_id;
        //+--------------------------------以下为系统--------------------------
        //模板替换变量
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    /**
     * @param $provider_id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function config($provider_id)
    {
        global $_W;
        $where = [];
        //获取模型信息
        $model = set_model("sms_site_config");
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $provider = set_model($this->sms_provider)->where(['id' => $provider_id])->find();
        $where = ['site_id' => $_W['site']['id'], 'provider_id' => $provider['id']];
        $detail = $model->where($where)->find();
        if ($this->isPost()) {
            $data = input('data/a');
            $data['config'] = json_encode($data['config']);
            if ($detail) {
                set_model('sms_site_config')->where($where)->update($data);
            } else {
                $_data['provider_id'] = $provider_id;
                $_data['site_id'] = $_W['site']['id'];
                set_model('sms_site_config')->insert($_data);
            }
            $this->zbn_msg("ok");
        } else {
            $detail['config'] = json_decode($detail['config'], true);
            $this->view->detail = $detail;
            $this->view->field_list = $model_info->get_admin_publish_fields($detail);
            return $this->view->fetch('providers/' . strtolower($provider['sign']));
        }
    }

    /**
     * @param $provider_id
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function set_default($provider_id)
    {
        global $_W;
        $content_model_id = "sms_site_config";
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $where = ['site_id' => $_W['site']['id'], 'status' => 99];
        $detail = $model->where($where)->find();
        if ($detail) {
            $update = [];
            $update['status'] = 1;
            $model->where($where)->update($update);
        }
        $update = [];
        $update['status'] = 99;
        $where = ['site_id' => $_W['site']['id'], 'provider_id' => $provider_id];
        $model->where($where)->update($update);
        return [
            'code' => 1,
            'msg' => "操作完成"
        ];
    }
}