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

use app\common\controller\ModuleBase;
use app\common\model\Models;
use app\core\util\ContentTag;

class Loupan extends HouseBase
{
    private $house_loupan = "house_loupan";

    public function index()
    {
        $this->view->areas = ContentTag::model_tree('area', '', 'area_name');


        $this->view->loupan_type_options = ContentTag::load_options("house_loupan", 'loupan_type');
        $this->view->price_options = ContentTag::load_options("house_loupan", 'price_qujian');
        $this->view->tags_options = ContentTag::load_options("house_loupan", 'tags');
        $this->view->zhuangxiu_options = ContentTag::load_options("house_loupan", 'zhuangxiu');

        $this->view->view = $this->view;
        return $this->view->fetch();
    }

    public function map_info($id){
        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;
        return $this->view->fetch();
    }

    public function map_esf_list($id){
        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;

        $this->view->esf_count = set_model('house_esf')->where(['loupan_id' =>$id ])->count();
        return $this->view->fetch();
    }

    public function map_rent_list($id){
        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;

        $this->view->rent_count = set_model('house_rent')->where(['loupan_id' =>$id ])->count();
        return $this->view->fetch();
    }
    public function detail($id)
    {
        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;
        $this->view->page_title = $detail['loupan_name'];

        $huxing_where = [];
        $huxing_where['loupan_id'] = $id;
        $huxings = Models::list_item($huxing_where, "house_loupan_huxing");

        //$huxings = set_model("house_loupan_huxing")->where($huxing_where)->select();

        $this->view->huxings = $huxings;

        // load ask list
        $ask_where = [];
        $ask_where['site_id'] = $_W['site']['id'];
        $ask_where['loupan_id'] = $id;
        $ask_where['status'] = 99;
        $asks = set_model('house_loupan_ask')->where($ask_where)->limit(10)->select();
        $this->view->asks = $asks;
        // near by loupans
        $loupan_where = [];
        $loupan_where['site_id'] = $_W['site']['id'];
        $loupan_where['id'] = ['NEQ' , $id];
        $loupan_where['area_id'] = $detail['old_data']['area_id'];
        $nearby_loupans = Models::list_item($loupan_where, "house_loupan");
        $this->view->nearby_loupans = $nearby_loupans;


        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        $template = "";
        if($detail['old_data']['template']){
            $template = "loupan_" . $detail['old_data']['template'];
        }

        $this->view->user_verify = set_model("users_verify")->where(['user_id'=>$this->user['id']])->find();

        $this->view->template = $template;
        $this->view->view = $this->view;
        return $this->view->fetch($template);
    }

    public function poster($loupan_id)
    {
        global $_W;
        if (!$this->user) {
            $this->error("请先登录", url('house/user/index'));
        }
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($loupan_id, $content_model_id);

        $this->view->poster = $poster = set_model('poster')->where(['id' => $detail['old_data']['poster']])->find();

        if (!$poster) {
            $this->error("该楼盘尚未设置海报");
        }


        $this->view->detail = $detail;
        $this->view->page_title = "正在制作" . $detail['loupan_name'] . "的海报";

        $huxing_where = [];
        $huxing_where['loupan_id'] = $loupan_id;
        $huxings = Models::list_item($huxing_where, "house_loupan_huxing");

        //$huxings = set_model("house_loupan_huxing")->where($huxing_where)->select();

        $poster = set_model('poster')->where(['id' => $detail['old_data']['poster']])->find();
        $this->view->huxings = $huxings;
        return $this->view->fetch();
    }

    public function map($loupan_id)
    {

        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($loupan_id, $content_model_id);
        $this->view->detail = $detail;
        $this->view->lnglat = explode("," , $detail['baidu_map']);
        if(!$detail['baidu_map']){
            $this->message("对不起，请在编辑楼盘  先设置坐标信息！");
        }


        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        $this->view->baidu_map = true;
        return $this->view->fetch();
    }

    public function esf($loupan_id){

        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($loupan_id, $content_model_id);

        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        return $this->view->fetch();
    }
    public function rent($loupan_id){
        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($loupan_id, $content_model_id);
        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        return $this->view->fetch();
    }

    public function vr_link($id){
        global $_W;
        $content_model_id = $this->house_loupan;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;
        return $this->view->fetch();
    }
}