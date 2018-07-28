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
namespace app\wechat_follow\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserMenu;
use app\common\util\Tree2;
use app\wechat\util\MhcmsWechatAccountBase;
use app\wechat\util\WechatUtility;
use think\Db;

class AdminFollow extends AdminBase
{

    public $wechat_follow = "wechat_follow";
    public function index(){
        global $_W , $_GPC;

        $this->content_model_id = $this->wechat_follow;
        $model = set_model($this->content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $id = (int)$_W['site']['id'];
        $where = ['site_id' => $id];
        $detail = $model->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
            }

            $data['site_id'] =$id;
            if ($detail) {
                $res = $model_info->edit_content($data, $where);
            } else {
                $res = $model_info->add_content($data);
            }
            if($res['code'] == 1){
                $this->zbn_msg($res['msg']);
            }else{
                $this->zbn_msg("操作失败");
            }

        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

}