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

use app\common\controller\HomeBase;
use app\common\model\Hits;
use app\common\model\Models;
use app\core\util\ContentTag;
use think\Db;

class Kft extends HouseBase
{
    private $house_kft = "house_kft";
    public function index(){
        global $_W;
        $content_model_id = $this->house_kft;
        $filter_info = Models::gen_user_filter($content_model_id, null);

        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = $filter_info['where'];
        $where['site_id'] = $_W['site']['id'];

        $simple_page = is_mobile() ? true : false;

        $this->view->lists = $lists = $model->where($where)->order("id desc")->paginate(null, $simple_page, ['query' => $filter_info['query']]);

        $this->view->page_title = "看房团活动列表";
        $this->view->filter = $filter_info;
        $this->view->content_model_id = $content_model_id;

        return $this->view->fetch();
    }

    public function detail($id){
        global $_W;
        $content_model_id = $this->house_kft;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['site_id'] = $_W['site']['id'];

        $where['id'] = $id; $this->view->detail = $model->where($where)->find();
        $this->view->content_model_id = $content_model_id;
        return $this->view->fetch();
    }
}