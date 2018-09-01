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
        $select['leixing'] = array('类型', '商铺', '住宅', '商住两用', '厂房', '酒店公寓');
        $select['jiage'] = array('价格', '价格从低到高', '价格从高到低');
        $select['tese'] = array('特色', '满五年', '满两年', '不满两年', '满五唯一', '随时看房', '学区房', '新房源', '大产权', '小产权');
        $select['zhuangxiu'] = array('装修', '毛胚', '简装', '精装', '豪装');
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
        if ($this->user_id) {
            Hits::log($id, $this->house_esf, $this->user_id);
        }
        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        $this->view->user_verify = set_model("users_verify")->where(['user_id' => $detail['user_id']])->find();

        //设置可见权限：支付查看信息
        $show_power = true;
        //设置支付查看交易结果
        $user_id = $this->user_id;
        $esf_id = $id;

        if ($result = Db::table('mhcms_house_esf_order')->where(['user_id' => $user_id, 'esf_id' => $esf_id])->find()) {
            $pay_result = true;
        } else {
            $pay_result = false;
        }
        $agent = Db::table('mhcms_house_esf')->where(['id' => $id])->find();
        $mobile = $agent['mobile'];
        $this->assign("mobile", $mobile);
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
    public function autoAdd()
    {
        $esf_id = trim(input('param.id'));
        $user_id = $this->user['id'];
        $base_info['user_id'] = $where['user_id'] = $user_id;
        $base_info['esf_id'] = $where['esf_id'] = $esf_id;

        $url = '/house/esf/detail/id/' . $esf_id;

        if ($result = Db::table('mhcms_user_esf')->where($where)->find()) {
            echo "<script> alert('请勿重复导入！'); </script>";
            echo "<meta http-equiv='Refresh' content='0;URL=$url'>";
            exit();
        }

        $res = Db::table('mhcms_user_esf')->insert($base_info);
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
}