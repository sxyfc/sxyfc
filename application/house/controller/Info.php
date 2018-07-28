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

class Info extends HouseBase
{
    private $house_info = "house_info";

    public function index()
    {
        global $_W;
        $content_model_id = $this->house_info;
        $filter_info = Models::gen_user_filter($content_model_id, null);

        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = $filter_info['where'];
        $where['site_id'] = $_W['site']['id'];

        $simple_page = is_mobile() ? true : false;

        $this->view->lists = $lists = $model->where($where)->order("id desc")->paginate(null, $simple_page, ['query' => $filter_info['query']]);

        $this->view->filter = $filter_info;
        $this->view->content_model_id = $content_model_id;
        return $this->view->fetch();
    }


    public function detail($id)
    {
        global $_W;
        $content_model_id = $this->house_info;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;
        $this->view->page_title = $detail['title'];

        Hits::hit($id ,$this->house_info);
        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        return $this->view->fetch();
    }
}