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
namespace app\core\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use think\Db;

class AdminGuestbook extends AdminBase
{

    public function index($module)
    {
        global $_W;
        $model = set_model("guestbook");
        /** @var Models $model_info */
        $model_info = $model->model_info;


        $where = [];
        $where['module'] = $module;
        $where['site_id'] = $_W['site']['id'];

        $this->view->lists = $model->where($where)->order("create_at desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        return $this->view->fetch();
    }
}