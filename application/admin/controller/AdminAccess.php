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
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserRoles;
use app\common\model\Users;
use think\Db;

class AdminAccess extends AdminBase
{

    public $access_logs = "access_logs";

    public function index($user_id = 0 )
    {
        global $_W , $_GPC;
        $content_model_id = $this->access_logs;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        if($user_id){
            $where['user_id'] = $user_id;
        }
        $ip = $_GPC['ip'];
        if($ip){
            $where['ip'] = $ip;
        }

        $this->view->lists = $model->where($where)->order("update_time desc")->paginate(null, false , ['query' => $where]);
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }
}