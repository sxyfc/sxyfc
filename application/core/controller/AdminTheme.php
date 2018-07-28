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
use app\core\util\MhcmsTheme;
use think\Db;

class AdminTheme extends AdminBase
{
    public function index()
    {
        global $_W;
        MhcmsTheme::collect_theme();
        $model = set_model("theme");
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $where = [];

        $this->view->lists = $model->where($where)->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        return $this->view->fetch();
    }

    public function edit($id){
        global $_W, $_GPC;
        $model = set_model("theme");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
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
}