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

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\util\Tree2;
use think\Db;

class AdminService extends AdminBase
{
    public function get_product($product_id = 0, $unit_id = 0, $build_id = 0, $floor_id = 0, $suite_id = 0)
    {
        global $_W, $_GPC;
        $loupan_id = (int)$_GPC['loupan_id'];
        //todo check loupan

        $loupan_where = [];
        $loupan_where['site_id'] = $_W['site']['id'];
        $loupan_where['id'] = $loupan_id;
        $loupan = set_model("house_loupan")->where($loupan_where)->find();

        if (!$loupan) {
            $ret['code'] = 3;
            $ret['msg'] = "对不起 无权操作";
            return $ret;
        }


        $where = [];
        $where['site_id'] = $_W['site']['id'];
        if ($product_id) {
            $where['id'] = (int)$product_id;
        } else {
            $where['build_id'] = (int)$build_id;
            $where['unit_id'] = (int)$unit_id;
            $where['floor_id'] = (int)$floor_id;
            $where['suite_id'] = (int)$suite_id;
        }
        $detail = set_model("house_loupan_product")->where($where)->find();

        if (!$detail) {

            $insert = $where;
            $insert['loupan_id'] = $loupan_id;
            $insert['status'] = 1;
            $insert['room_name'] = trim($_GPC['product_name']);

            $ret = set_model("house_loupan_product")->model_info->add_content($insert);
            if ($ret['code'] == 1) {
                return $ret;
            } else {
                $ret['code'] = 3;
                $ret['msg'] = "对不起 系统错误" . $ret['msg'];
                return $ret;
            }
        } else {
            $ret['code'] = 1;
            $ret['item'] = $detail;
            return $ret;
        }

    }

    public function update_product($id)
    {
        global $_W, $_GPC;
        $where = [];
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];

        return set_model("house_loupan_product")->model_info->edit_content( $_GPC , $where);

    }
}