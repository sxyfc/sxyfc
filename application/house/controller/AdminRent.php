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

class AdminRent extends AdminBase
{
    private $house_rent = "house_rent";

    public function index()
    {
        global $_W, $_GPC;

        $content_model_id = $this->house_rent;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        if (!$this->super_power) {
            $where['user_id'] = $this->user['id'];
        }

        $area_id = getArea($_GPC);
        if (substr($area_id, 2, 4) == '0000') {
            $where['area_id'][] = array('egt', substr($area_id, 0, 2).'0000');
            $where['area_id'][] = array('elt', substr($area_id, 0, 2).'9999');
        } else if (substr($area_id, 4, 2) == '00') {
            $where['area_id'][] = array('egt', substr($area_id, 0, 4).'00');
            $where['area_id'][] = array('elt', substr($area_id, 0, 4).'99');
        } else if ($area_id != '' && $area_id != '0') {
            $where['area_id'] = $area_id;
        }

        $mobile = trim(input('param.mobile', '', 'htmlspecialchars'));
        if ($mobile) {
            $where['mobile'] = array('LIKE', '%' . $mobile . '%');
            $this->view->assign('mobile', $mobile);
        }

        if (!$this->super_power){
            $ids = array();
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                $ids = $this->map_city_childs($this->user['id']);
                array_push($ids, $this->user['id']);
                $where['online'] = 1;
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $ids = $this->map_county_childs($this->user['id']);
                array_push($ids, $this->user['id']);
                $where['online'] = 1;
            } elseif ($users['user_role_id'] == 25) {
                // CEO（区域经理）
                $ids = $this->map_area_childs($this->user['id']);
                array_push($ids, $this->user['id']);
                $where['online'] = 1;
            } elseif ($users['user_role_id'] == 26) {
                // 省级代理
                $ids = $this->map_province_childs($this->user['id']);
                array_push($ids, $this->user['id']);
                $where['online'] = 1;
            }else{
                array_push($ids, $this->user['id']);
            }

            $where['user_id'] = array('IN', $ids);
        }

        $loupan_name = trim(input('param.loupan_name'));
        if ($loupan_name) {
            $ids = array();
            $where_loupan['xiaoqu_name'] = array('LIKE', '%' . $loupan_name . '%');
            if ($loupan_name_info = Db::name('house_xiaoqu')->where($where_loupan)->field('id')->select()->toArray()) {
                foreach ($loupan_name_info as $key => $value) {
                    $ids[$key] = $value['id'];
                }

                $where['xiaoqu_id'] = array('IN', $ids);
                $this->view->lists = $model->where($where)->order("id desc")->paginate();
            } else {
                $this->view->lists = '';
            }

            $this->view->assign('loupan_name', $loupan_name);
        } else {
            $this->view->lists = $model->where($where)->order("id desc")->paginate();
        }

        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        $this->view->area_id = $area_id;
        return $this->view->fetch();
    }

    public function add()
    {
        global $_W, $_GPC;
        $model = set_model($this->house_rent);

        // 新增租房、二手房源，同县城、小区、联系方式、地址 不可重复添加

        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
                if (!isset($base_info['top_expire']) || $base_info['top_expire'] == '' || empty($base_info['top_expire'])) $base_info['top_expire'] = gmdate("Y-m-d H:i:s");
                $where['mobile'] = $base_info['mobile'];
                $where['address'] = $base_info['address'];
                $where['area_id'] = getArea($_GPC['area']);
                $where['site_id'] = $_W['site']['id'];
                $where['title'] = $base_info['title'];
                $find_data = $model->where($where)->find();
//                Log::error("where==" . json_encode($where)."====".$model_info);
                if ($find_data) {
                    return $this->zbn_msg("不可添加重复房源", 2);
                }

                if (!is_int(intval($base_info['mobile']))) {
                    return $this->zbn_msg("手机号必须为数字", 2);
                }
            }

            $base_info['area_id'] = getArea($_GPC['area']);
            $base_info['user_id'] = $this->user['id'];
            $base_info['status'] = 0;
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_parent_page()'");
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
        $model = set_model($this->house_rent);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];

        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
                if (!isset($base_info['top_expire']) || $base_info['top_expire'] == '' || empty($base_info['top_expire'])) $base_info['top_expire'] = gmdate("Y-m-d H:i:s");
            }

            $base_info['area_id'] = getArea($_GPC['area']);
            if (!$this->super_power){
                unset($base_info['status']);
            }

            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_parent_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $detail = $model->where($where)->find();
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, []);
            $detail['data'] = mhcms_json_decode($detail['data']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_rent);
        $model_info = $model->model_info;
        // $where['id'] = $id;
        // $where['site_id'] = $_W['site']['id'];
        // $detail = $model->where($where)->find();

        // if ($detail) {
        //     $model_info::delete_item($id, $this->house_rent);
        // }
        $model->where(['id' => $id])->update(['online'=>0, 'status'=>0]);

        $ret['code'] = 1;
        $ret['msg'] = "ok";
        $ret['javascript'] = "reload_page()";
        return $ret;
    }


    public function online($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_rent);

        $model->where(['id' => $id])->update(['online'=>1]);
        $ret['code'] = 1;
        $ret['msg'] = "ok";
        $ret['javascript'] = "reload_page()";
        return $ret;
    }

    public function check($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_rent);
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        if ($detail) {
            if ($detail['status'] == 99) {
                $data['status'] = 0;
                $model->where(['id' => $id])->update($data);
                $ret['code'] = 0;
                $ret['msg'] = "取消通过";
                $ret['javascript'] = "reload_page()";
                return $ret;
                // return $this->zbn_msg('审核取消', 1, 'true', 1000, "''", "window.location.reload()");
            } else {
                $data['status'] = 99;
                $model->where(['id' => $id])->update($data);
                $ret['code'] = 1;
                $ret['msg'] = "审核通过";
                $ret['javascript'] = "reload_page()";
                return $ret;
                // return $this->zbn_msg('审核通过', 1, 'true', 1000, "''", "window.location.reload()");
            }
        }
    }
}