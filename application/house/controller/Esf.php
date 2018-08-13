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
use think\Log;

class Esf extends HouseBase
{
    private $house_esf = "house_esf";

    public function index()
    {
        global $_W;

        $type = trim(input('param.type', ' ', 'htmlspecialchars'));
        $data = trim(input('param.data', '', 'htmlspecialchars'));

        $order = 'update_at desc';
        if ($type) {//增加查询选择条件，否则默认
            if ($data) {
                switch ($type) {
                    case "huxing"://户型筛选
                        $where['shi'] = $data;
                        break;
                    case "area"://区域筛选
                        $where['area_id'] = $data;
                        break;
                    case "jiage"://低到高0、高到低1
                        if ($data == 0) {
                            $order = "price desc";
                        } elseif ($data == 1) {
                            $order = "price asc";
                        }
                        break;
                    case "zhuangxiu"://1|毛胚\r\n2|简装\r\n3|精装\r\n4|豪装
                        $where['zhuangxiu'] = $data;
                        break;
                    case "tese"://tags->1|满五年\r\n2|满两年\r\n3|
                        //不满两年\r\n4|满五唯一\r\n5|随时看房\r\n6|
                        //学区房\r\n7|新房源\r\n8|大产权\r\n9|小产权
                        $where['tags'] = array('LIKE', '%' . $data . '%');
                        break;
                    case "leixing"://yongtu->1|商铺\r\n2|住宅\r\n3|商住两用\r\n4|厂房\r\n5|酒店公寓
                        $where['yongtu'] = $data;
                        break;
                }
            }
        }
        $model = set_model('house_esf');
        $this->view->lists = $model->where($where)->order($order)->select()->toArray();

        //设置筛选数据
        $this->view->area_data = set_model('area')->field('id,area_name')->select()->toArray();


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