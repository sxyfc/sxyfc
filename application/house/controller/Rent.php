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
        $this->view->field_list = $model_info->get_admin_publish_fields($detail, []);
        $this->view->detail = $detail;
        $this->view->page_title = $detail['title'];
        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);

        Hits::hit($id, $this->house_esf);
        if ($this->user_id) {
            Hits::log($id, $this->house_esf, $this->user_id);
        }
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

        if ($agent['user_id']) {
            $user_info = Db::table('mhcms_users')->where(['id' => $agent['user_id']])->find();
            $mobile = $user_info['mobile'];
        } else {
            $mobile = '';
        }

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
    public function autoAdd()
    {
        $rent_id = trim(input('param.id'));
        $user_id = $this->user['id'];
        $base_info['user_id'] = $where['user_id'] = $user_id;
        $base_info['rent_id'] = $where['rent_id'] = $rent_id;

        $url = '/house/rent/detail/id/' . $rent_id;

        if ($result = Db::table('mhcms_user_rent')->where($where)->find()) {
            echo "<script> alert('请勿重复导入！'); </script>";
            echo "<meta http-equiv='Refresh' content='0;URL=$url'>";
            exit();
        }

        $res = Db::table('mhcms_user_rent')->insert($base_info);
        if ($res) {
            echo "<script> alert('导入成功！'); </script>";
            echo "<meta http-equiv='Refresh' content='0;URL=$url'>";
            exit();
        } else {
            echo "<script> alert('导入失败，请稍后再试！'); </script>";
            echo "<meta http-equiv='Refresh' content='0;URL=$url'>";
            exit();
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