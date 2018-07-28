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

class AdminLoupanProduct extends AdminBase
{

    private $house_loupan_product = "house_loupan_product";
    private $house_loupan = "house_loupan";
    public function index($loupan_id)
    {
        global $_W , $_GPC;
        $this->view->filter_info = Models::gen_admin_filter($this->house_loupan_product, $this->menu_id);
        //todo load loupan
        $model = set_model($this->house_loupan);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['id'] = $loupan_id;
        $detail = $model->where($where)->find();
        //build_id
        if($detail && $_GPC['build_id']){
            $this->view->detail = $detail;
            //todo load all loupan huxings
            $this->view->huxings =set_model('house_loupan_huxing')->where(['loupan_id' =>$loupan_id ])->select();
            $where = [];
            $where['loupan_id'] = $loupan_id;

            $where['id'] = (int)$_GPC['build_id'];
            $buildings = set_model("house_loupan_building")->where($where)->select()->toArray();

            foreach($buildings as &$building){
                $units = [];
                for($u = 1;$u <= $building['unit_count'];$u++){
                    $unit = [];$floors = [];$t = 0;
                    for($f = 1;$f<= $building['floor_count']; $f++){
                        $floor_info = [];
                        $floor_info['floor'] = $f;
                        $floor_name = $f;
                        if($building['floor_zero'] && $f<10){
                            $floor_name = "0" . $floor_name;
                        }

                        $floor_info['floor_name'] = $floor_name;
                        $suites = [];
                        for($j = 1; $j<=$building['suite_per_floor'];$j++){
                            $suite = [];
                            $suite['id'] = $j;
                            $room_name = $j;

                            switch($building['suite_name_type']){
                                case "1" :
                                    if($building['suite_zero'] && $j<10){
                                        $room_name = "0" . $room_name;
                                    }
                                    $suite['room_name'] = $floor_name . $room_name;
                                    break;
                                case "2" :
                                    if($building['suite_zero'] && $j<10){
                                        $room_name = "0" . $room_name;
                                    }
                                    $suite['room_name'] = $u . $floor_name . $room_name;
                                    break;
                                case "3" :
                                    $t ++;
                                    $room_name = $t;
                                    if($building['suite_zero'] && $t<10){
                                        $room_name = "0" . $t;
                                    }
                                    $suite['room_name'] = $u . $room_name;
                                    break;

                            }
                            //todo load product
                            $product_where = [];
                            $product_where['loupan_id'] = $loupan_id;
                            $product_where['build_id'] = $building['id'];
                            $product_where['unit_id'] = $u;
                            $product_where['floor_id'] = $f;
                            $product_where['suite_id'] = $j;
                            $product = set_model($this->house_loupan_product)->where($product_where)->find();

                            if($product){
                                $suite['product'] = $product;
                            }

                            $floor_info['suites'][$j] = $suite;
                        }
                        $floors[$f] = $floor_info;
                    }
                    krsort($floors);
                    $unit['floors'] = $floors;
                    $units[$u] = $unit;
                }
            }
            $building['units'] = $units;
            $this->view->buildings = $buildings;
        }
        $this->view->loupan_id = $loupan_id;
        return $this->view->fetch();
    }

    public function index_back($loupan_id = "")
    {
        global $_W;
        $this->view->filter_info = Models::gen_admin_filter($this->house_loupan_product, $this->menu_id);
        $content_model_id = $this->house_loupan_product;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $where = $this->view->filter_info['where'];
        $where['site_id'] = $_W['site']['id'];
        if ($loupan_id) {
            $where['loupan_id'] = $loupan_id;
        }

        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->mapping['loupan_id'] = $loupan_id;
        $this->view->mapping = $this->mapping;

        return $this->view->fetch();
    }

    public function add($loupan_id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_loupan_product);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['loupan_id'] = $loupan_id;

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields([]);
            return $this->view->fetch();
        }
    }


    public function edit($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_loupan_product);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }


            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, []);
            $detail['data'] = mhcms_json_decode($detail['data']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_loupan_product);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        if ($detail) {
            $model->where($where)->delete();
        }

        return [
            'code' => 1,
            'msg' => "ok"
        ];
    }
}