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

class AdminPages extends AdminBase
{
    public $page = 'page';

    public function index($module)
    {
        global $_W, $_GPC;
        //自定义筛选条件
        $where = [];
        $model = set_model($this->page);

        $model_info = $model->model_info;//获取模型信息
        $where['site_id'] = $_W['site']['id'];
        $where['module'] = $module;
        //分配到当前模块
        $lists = $model->where($where)->order("id desc")->paginate();
        //列表数据
        $this->view->lists = $lists;
        //fields
        $this->view->field_list = $model_info->get_admin_publish_fields();
        //model_info
        $this->view->model_info = $model_info;
        //+--------------------------------以下为系统--------------------------
        //模板替换变量
        $this->mapping['module'] = $module;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public function add()
    {
        global $_GPC;
        //后去模型信息
        $model = set_model($this->page);
        $model_info = $model->model_info;
        //手动处理类型的模型
        if ($this->isPost() && $model_info) {

            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            //自动提取缩略图
            if (!isset($base_info['thumb']) && !empty($base_info['content'])) {
                $content = stripslashes($base_info['content']);
                $auto_thumb_no = 1 - 1;
                if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
                    $thumb_url = str_replace("&quot;", "", $matches[3][$auto_thumb_no]);
                }
                if (isset($thumb_url)) {
                    $file = Db::name('file')->where(['url' => $thumb_url])->find();//File::get(['url'=>$base_info['thumb']]);
                    $base_info['thumb'][] = $file['file_id'];
                }
            }
            //自动截取简介
            if (!isset($base_info['description']) && !empty($base_info['content'])) {
                $content = stripslashes($base_info['content']);
                $introcude_length = intval(255);
                $base_info['description'] = str_cut(str_replace(array("\r\n", "\t"), '', strip_tags($content)), $introcude_length);
            }
            //分配到当前模块
            if (Models::field_exits('module', $this->page)) {
                $base_info['module'] = ROUTE_M;
            }
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {

            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields();
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function edit($id)
    {
        global $_GPC;
        $id = (int)$id;
        $model = set_model($this->page);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
            }
            // todo  process data input

            $model_info->edit_content($data, $where);
            $this->zbn_msg("ok");
        } else {

            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

}