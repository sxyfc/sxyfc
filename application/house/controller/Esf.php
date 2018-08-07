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

class Esf extends HouseBase
{
    private $house_esf = "house_esf";

    public function index()
    {
        global $_W;
        $this->view->areas = ContentTag::model_tree('area', '', 'area_name');


        $this->view->loupan_type_options = ContentTag::load_options("house_esf", 'loupan_type');
        //$this->view->price_options = ContentTag::load_options("house_esf", 'price_qujian');
        $this->view->tags_options = ContentTag::load_options("house_esf", 'tags');
        $this->view->zhuangxiu_options = ContentTag::load_options("house_esf", 'zhuangxiu');

        return $this->view->fetch();
    }

    public function detail($id)
    {
        global $_W;
        $content_model_id = $this->house_esf;
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

        //设置可见权限：支付查看信息
        $user_role_id = $this->user['user_role_id'];
        if ($user_role_id == 2 || $user_role_id == 4 || $user_role_id == 5) {
            $show_power = false;
        } else if ($user_role_id == 1 || $user_role_id == 3 || $user_role_id == 22 || $user_role_id == 33) {
            $show_power = true;
        } else {
            $show_power = false;
        }
        $this->assign("show_power", $show_power);

        //设置支付查看交易结果
        $pay_result = false;
        $this->assign("pay_result", $pay_result);
        return $this->view->fetch();
    }

    /**
     * 二手房一键导入
     * @param $id
     * @return void
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function autoAdd($id)
    {
        global $_W;
        $user_id = $this->user['id'];
        $esf_id = $id;

        $model_info = set_model('user_esf');
        $base_info['user_id'] = $user_id;
        $base_info['esf_id'] = $esf_id;

        $res = $model_info->add_content($base_info);
        if ($res['code'] == 1) {
            return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''");
        } else {
            return $this->zbn_msg($res['msg'], 2);
        }
    }
}