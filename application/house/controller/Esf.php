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
        $select = array();
        $select['leixing'] = array('类型','商铺', '住宅', '商住两用', '厂房', '酒店公寓');
        $select['jiage'] = array('价格','价格从低到高', '价格从高到低');
        $select['tese'] = array('特色','满五年', '满两年', '不满两年', '满五唯一', '随时看房', '学区房', '新房源', '大产权', '小产权');
        $select['zhuangxiu'] = array('装修','毛胚', '简装', '精装', '豪装');
        $select['huxing'] = array('0室', '1室', '2室', '3室', '4室', '5室');

        $area = $_GET['area'];
        $leixing = $_GET['leixing'];
        $zhuangxiu = $_GET['zhuangxiu'];
        $jiage = $_GET['jiage'];
        $tese = $_GET['tese'];
        $huxing = $_GET['huxing'];

        if ($area != null) {
            $where['mhcms_house_esf.area_id'] = $area;
            $this->assign('area', $area);
        }

        if (!empty($leixing)) {
            $where['mhcms_house_esf.yongtu'] = $leixing;
            $this->assign('leixing', $leixing);
        }
        if (!empty($zhuangxiu)) {
            $where['mhcms_house_esf.zhuangxiu'] = $zhuangxiu;
            $this->assign('zhuangxiu', $zhuangxiu);
        }
        if (!empty($tese)) {
            $tags = $tese;
            $where['mhcms_house_esf.tags'] = array('LIKE', '%' . $tags . '%');
            $this->assign('tese', $tese);
        }
        if (!empty($jiage)) {
            if ($jiage == 1) {
                $order = "mhcms_house_esf.price asc,mhcms_house_esf.update_at desc";
            } elseif ($jiage == 2) {
                $order = "mhcms_house_esf.price desc,mhcms_house_esf.update_at desc";
            }

            $this->assign('jiage', $jiage);
        } else {
            $order = "mhcms_house_esf.update_at desc";
        }
        if (!empty($huxing)) {
            $where['mhcms_house_esf.shi'] = $huxing;
            $this->assign('huxing', $huxing);
        }

//        case "huxing"://户型筛选
//        case "jiage"://低到高0、高到低1
//        case "zhuangxiu"://1|毛胚\r\n2|简装\r\n3|精装\r\n4|豪装
//        case "tese"://tags->1|满五年\r\n2|满两年\r\n3|
//            //不满两年\r\n4|满五唯一\r\n5|随时看房\r\n6|
//            //学区房\r\n7|新房源\r\n8|大产权\r\n9|小产权
//        case "leixing"://yongtu->1|商铺\r\n2|住宅\r\n3|商住两用\r\n4|厂房\r\n5|酒店公寓


        $model = set_model('house_esf');
        if ($huxing != null || $tese != null || $zhuangxiu != null || $leixing != null || $area != null) {
            $this->view->lists = $model->join('mhcms_file', 'mhcms_file.file_id=mhcms_house_esf.thumb')->where($where)->order($order)->paginate();
        } else {
            $this->view->lists = $model->join('mhcms_file', 'mhcms_file.file_id=mhcms_house_esf.thumb')->where($where)->order($order)->paginate();
        }

        //设置筛选数据
        $area_data = set_model('area')->field('id,area_name')->select()->toArray();
        $this->assign('area_data', $area_data);
        $this->assign('select', $select);


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
        } else if ($user_role_id == 1 || $user_role_id == 3 || $user_role_id == 22 || $user_role_id == 23) {
            $show_power = true;
        } else {
            $show_power = false;
        }

        //设置支付查看交易结果
        $pay_result = false;
        //查询对应表，通过esf_id和user_id
        $this->assign("pay_result", $pay_result);
        $this->assign("show_power", $show_power);
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