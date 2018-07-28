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
use app\sms\model\Notice;
use think\Db;

class AdminLoupanAsk extends AdminBase
{

    private $house_loupan_ask = "house_loupan_ask";

    public function index($loupan_id)
    {
        global $_W;
        $content_model_id = $this->house_loupan_ask;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['loupan_id'] = $loupan_id;
        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->mapping['loupan_id'] = $loupan_id;

        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public function add($loupan_id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_loupan_ask);
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
            $base_info['loupan_id'] = $loupan_id;

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $base = [];
            $base['loupan_id'] = $loupan_id;
            $this->view->field_list = $model_info->get_admin_publish_fields($base);
            return $this->view->fetch();
        }
    }

    public function edit($id){
        global $_W, $_GPC;
        $model = set_model($this->house_loupan_ask);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        $sendTpl = false;
        if($detail['status']!=99){
            $sendTpl = true;
        }
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

                if($sendTpl){
                    Notice::send("楼盘提问通知" , "wxmsg" , $detail['openid'] , ['title' => '您好 管理员回答了您的问题！' . $detail['title'] ,'loupan_id' => $detail['loupan_id'] ]);
                }

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

    public function delete($id){
        global $_W, $_GPC;
        $model = set_model($this->house_loupan_ask);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $model_info->delete_item($id , 'house_loupan_ask');

        return [
            'code' => 1 , 'msg' => 'ok'
        ];
    }

}