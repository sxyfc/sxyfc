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

use app\common\model\Models;
use app\core\util\ContentTag;
use app\core\util\MhcmsMenu;

class User extends HouseUserBase
{
    public function index()
    {

        $this->view->seo = $this->seo($this->mapping);

        $menu = new MhcmsMenu();
        $this->view->menus = $menu->get_member_module_menu("house");
        return $this->view->fetch();
    }

    /**
     * 看房记录
     */
    public function kanfang_log()
    {
        global $_W, $_GPC;
        if (!$this->user['is_mobile_verify']) {
            $url = url('member/info/set_mobile', ['forward' => '/house/user/index']);
            $this->message("您好,您必须完善手机号码才能查看看房记录!", 1, $url);
        }

        //find appointment
        $mobile = is_phone($this->user['mobile']) ? $this->user['mobile'] : $this->user['user_name'];
        $where['mobile'] = $mobile;
        $where['site_id'] = $_W['site']['id'];
        $appointment = set_model('house_appointment')->where($where)->find();

        $log_where = [];
        $log_where['log_type'] = 1;
        $log_where['appointment_id'] = $appointment['id'];
        $this->view->logs = set_model('house_appointment_log')->where($log_where)->select();

        return $this->view->fetch();
    }


    /**
     * 一键导入的二手房
     */
    public function myadd_esf()
    {
        $user_esf = "user_esf";
        $user_id = $this->user['id'];
        $where['user_id'] = $user_id;
        $esf_list_model = set_model($user_esf)->where($where)->select();


        global $_W;
        //渲染筛选条件-地区
        $this->view->areas = ContentTag::model_tree_tow('area', '', 'area_name', 'id', $user_esf);

        $this->view->loupan_type_options = ContentTag::load_options_two("house_esf", 'loupan_type', $esf_list_model, "erf_id");
        $this->view->tags_options = ContentTag::load_options_two("house_esf", 'tags', $esf_list_model, "erf_id");
        $this->view->zhuangxiu_options = ContentTag::load_options_two("house_esf", 'zhuangxiu', $esf_list_model, "erf_id");

        return $this->view->fetch();
    }

    public function detail_esf($id)
    {
        $house_esf = "house_esf";
        global $_W;
        $content_model_id = $house_esf;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);        //		$detail['mobile'] = '*********';//		$is_phone = Db::table('buy_phone')->where('user_id' , )->find();
        $this->view->detail = $detail;
        $this->view->page_title = $detail['title'];
        Hits::hit($id, $this->house_esf);
        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        $this->view->user_verify = set_model("users_verify")->where(['user_id' => $detail['user_id']])->find();

        return $this->view->fetch();
    }

    /**
     * 一键导入的租房
     */
    public function myadd_rent()
    {
        $user_rent = "user_rent";

        global $_W;
        $content_model_id = "house_rent";
        $filter_info = Models::gen_user_filter($content_model_id, null);

        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = $filter_info['where'];
        $where['site_id'] = $_W['site']['id'];

        $simple_page = is_mobile() ? true : false;

        $user_esf = "user_rent";
        $user_id = $this->user['id'];
        $where_rent['user_id'] = $user_id;
        $esf_list_model = set_model($user_esf)->where($where_rent)->select()->toArray();
        if (!empty($esf_list_model)){
            $rent_ids = array_column($esf_list_model, 'esf_id');
            $where['id'] = ['IN', $rent_ids];
        }else{
            $where['id'] = 0;
        }

        $this->view->lists = $lists = $model->where($where)->order("id desc")->paginate(null, $simple_page, ['query' => $filter_info['query']]);

        $this->view->filter = $filter_info;
        $this->view->content_model_id = $content_model_id;

        return $this->view->fetch();

    }

    public function detail_rent($id)
    {
        $house_rent = "house_rent";
        global $_W;
        $content_model_id = $house_rent;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;
        $this->view->page_title = $detail['title'];
        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);

        Hits::hit($id, $this->house_esf);
        $this->view->user_verify = set_model("users_verify")->where(['user_id' => $detail['user_id']])->find();

        return $this->view->fetch();
    }

}
