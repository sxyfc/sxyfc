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

class AdminIcon extends AdminBase
{

    /**
     * @return string
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = set_model("icon");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $filter = Models::gen_admin_filter("icon", $this->menu_id);
        $where = $filter['where'];

        $this->view->filter_info = $filter;
        $this->view->lists = set_model("icon")->where($where)->order('id desc')->select();

        $this->view->field_list = $model_info->get_admin_column_fields();
        return $this->view->fetch();
    }
}