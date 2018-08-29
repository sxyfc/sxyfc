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

class Rent extends HouseBase
{
    private $house_esf = "house_rent";

    public function index()
    {
        global $_W;
        $content_model_id = $this->house_esf;
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
        $content_model_id = $this->house_esf;
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


        //设置可见权限：支付查看信息
        $show_power = true;
        $this->assign("show_power", $show_power);


        $user_id = $this->user_id;
        $rent_id = $id;

        //设置支付查看交易结果
        if ($result = Db::table('mhcms_house_rent_order')->where(['user_id' => $user_id, 'rent_id' => $rent_id])->find()) {
            $pay_result = true;
        } else {
            $pay_result = false;
        }
        $agent = Db::table('mhcms_house_rent')->where(['id' => $id])->find();
        $mobile = $agent['mobile'];
        $this->assign("mobile", $mobile);
        $this->assign("pay_result", $pay_result);
        return $this->view->fetch();
    }

    /**
     * 一键导入
     * @param $id
     * @return void
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function autoAdd($id)
    {
        global $_W;
        $user_id = $this->user['id'];
        $rent_id = $id;

        $model_info = set_model('user_rent');
        $base_info['user_id'] = $user_id;
        $base_info['rent_id'] = $rent_id;

        $res = $model_info->add_content($base_info);
        if ($res['code'] == 1) {
            return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''");
        } else {
            return $this->zbn_msg($res['msg'], 2);
        }
    }

    /**
     * 支付查看
     * @param $id
     */
    public function payInfo($id)
    {
        global $_W;
    }
}