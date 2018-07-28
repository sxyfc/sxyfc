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

class Content extends HouseBase
{
    private $content_model_id;

    /**
     * @param $cate_id
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cate($cate_id)
    {
        global $_GPC, $_W;
        $cate_id = (int) $cate_id;
        $this->view->cate_id = (int) $cate_id;
        $this->view->cate = $cate = $this->cates[$cate_id] ;

        $this->content_model_id = $cate['model_id'];
        if (!isset($cate)) {
            $this->message("对不起 ， 您访问的栏目不存在", 2);
        }

        //TODO 是否有子栏目
        if (isset($this->have_children_cates[$cate_id]) && $this->have_children_cates[$cate_id]) {
            $is_parent = 1;
            $template = $cate['parent_tpl'];
        }

        else {
            $is_parent = 0;
            $template = $cate['list_tpl'];
        }

        //单网页 直接渲染
        if ($cate['cate_type'] == 2) {
            $page = set_model('page')->where(['cate_id' => $cate_id, 'site_id' => $_W['site']['id']])->find();
            $this->view->page_data = $page;
            $this->view->detail = $page;
            if (!$page) {
                $this->error("对不起，单页内容还没有添加！");
            }
            $page['template'] = $page['template'] ? $page['template'] : "item_article";
            if (!$page['template']) {
                $this->error("对不起，单页必须制定模板！");
            }
            return $this->view->fetch($page['template']);
        }

        if ($cate['cate_type'] == 1) {
            $top_parent_id = $cate['parent_id'] ? $cate['parent_id'] : $cate_id;
            $sub_categorys = Db::name("house_cate")->where(['parent_id' => $top_parent_id])->order('listorder desc')->select()->toArray();
            $this->view->sub_categorys = $sub_categorys;
            $cate_ids[] = $cate_id;
            //如果
            /**
             *
            if ($sub_categorys && $top_parent_id == $cate_id) {
            $tpl = $cate['parent_tpl'];
            foreach ($sub_categorys as $sub_category) {
            if ($sub_category['in_parent_list'] == 1) {
            $cate_ids[] = $sub_category['id'];
            }
            }
            } else {
            $tpl = $cate['list_tpl'];
            }
             */
            //自定义筛选条件

            $where = [];
            //获取模型信息
            $model = set_model($this->content_model_id);
            /** @var Models $model_info */
            $model_info = $model->model_info;

            $filter = Models::gen_user_filter($this->content_model_id , 0);


            $where = $filter['where'];
            //data list 如果不是超级管理员 并且数据是区分站群的
            if (Models::field_exits('site_id', $this->content_model_id)) {
                $where['site_id'] = $this->site['id'];
            }

            $where['cate_id'] = ["IN", $cate_ids];


            $lists = $model->where($where)->order(" listorder desc ,id desc")->paginate(10 , true);
            //列表数据
            $this->view->lists = $lists;
            $this->view->pages =$lists->render();
            //fields
            $this->view->field_list = $model_info->get_admin_column_fields();
            //model_info
            $this->view->model_info = $model_info;
            //+--------------------------------以下为系统--------------------------
            $this->mapping = array_merge($this->mapping, $cate);
            //模板替换变量
            $this->view->mapping = $this->mapping;
            $this->view->content_model_id = $this->content_model_id;
            $this->view->seo = $this->seo($this->mapping);

            return $this->view->fetch($template);
        }

    }


    /**
     * @param $id
     * @param $cate_id
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function detail($id, $cate_id)
    {
        global $_GPC, $_W;

        $cate_id = (int) $cate_id;
        $this->view->cate_id = (int) $cate_id;
        if(!isset($this->cates[$cate_id])){
            $this->message("对不起，内容不存在！");
        }
        $this->view->cate = $cate = $this->cates[$cate_id] ;
        $this->content_model_id = $cate['model_id'];
        $detail = Models::get_item($id, $this->content_model_id);
        if(!$detail){
            $this->message("对不起，内容不存在！");
        }

        Hits::hit($id , $this->content_model_id ,0);

        $this->view->detail = $detail;
//获取模型信息
        $model = set_model($this->content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        //$search_keys = explode(",", $model_info['search_keys']);
        /**
         *
        $new_field_list = is_array($model_info['setting']['fields']) ? $model_info['setting']['fields'] : [];

        foreach ($new_field_list as $k => $field) {
        if (empty($field['node_field_mode']) || $field['disabled'] == 1) {
        unset($new_field_list[$k]);
        continue;
        }
        //筛选条件
        if ($field['node_field_is_filter'] && $_GPC[$k]) {
        $where[$k] = ['EQ', $_GPC[$k]];
        }
        if (in_array($k, $search_keys) && $_GPC['q']) {
        $where[$k] = ['like', "%{$_GPC['q']}%"];
        }
        $field['form_str'] = $this->form_factory->config_model_form($field);
        $new_field_list[$k] = $field;
        }
         */
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields([] , false);
        $this->mapping = array_merge($this->mapping, $this->view->detail);
        //模板替换变量
        $this->view->mapping = $this->mapping;
        $this->view->seo = $this->seo($this->mapping);

        $this->view->template = $cate['item_tpl'];
        $this->view->view = $this->view;
        return $this->view->fetch($cate['item_tpl']);
    }

}