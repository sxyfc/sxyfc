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
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserRoles;
use app\common\model\Users;
use think\Db;
use think\Log;

class Admin extends AdminBase
{

    public $admin = "admin";

    public function index()
    {
        //自定义筛选条件
        $where = [];
        //获取模型信息
        $model = set_model($this->admin);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        //data list 如果不是超级管理员 并且数据是区分站群的
        if (!$this->super_power && Models::field_exits('site_id', $this->admin)) {
            $where['site_id'] = $this->site['id'];
        }

        $lists = $model->where($where)->order("id desc")->paginate();
        //列表数据
        $this->view->lists = $lists;
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();
        //model_info
        $this->view->model_info = $model_info;
        //+--------------------------------以下为系统--------------------------
        //模板替换变量
        $this->view->mapping = $this->mapping;
//        //设置筛选数据
//        $area_data = set_model('area')->order(['parent_id' => 'asc'])->field('id,area_name,parent_id')->select()->toArray();
//        $area_province = array();
//        foreach ($area_data as $area_item) {
//            if ($area_item['parent_id'] == 0) {
//                array_push($area_province, $area_item);//省
//                $key = array_search($area_item, $area_data);
//                array_splice($area_data, $key, 1);
//            }
//        }
//        $this->assign('area_data', json_encode($area_data));
//        $this->assign('area_province', json_encode($area_province));
        return $this->view->fetch();
    }

    public function create()
    {
        global $_GPC;
        //后去模型信息
        $model = set_model($this->admin);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        if (!$this->super_power) {
            return $this->zbn_msg('无权操作！', 2);
        }

        //手动处理类型的模型
        if ($this->isPost() && $model_info) {
            $area = 0;
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
                if ($_POST['area_province'] != null) {
                    if ($_POST['area_province'] != null) $area = $_POST['area_province'];
                    if ($_POST['area_city'] != null) $area = $_POST['area_city'];
                    if ($_POST['area_area'] != null) $area = $_POST['area_area'];
                }
            }

            // 查询用户信息
            if (!$user_info = Db::name('users')->where(['id' => $base_info['user_id']])->find()) {
                return $this->zbn_msg('用户不存在', 2);
            }
            $base_info['site_id'] = $user_info['site_id'];
            $base_info['user_name'] = $user_info['user_name'];

//            // 修改用户is_admin状态
//            if (!$result = Db::name('users')->where(['id' => $base_info['user_id']])->update(['is_admin' => 1])) {
//                return $this->zbn_msg('网络出错，请稍后再试！', 2);
//            }

            /** @var Models $model_info */
            $res = $model_info->add_content($base_info);
//            Log::error($area);
            if ($area == 0) return $this->zbn_msg("必须选择代理地区", 2);

            if ($res['code'] == 1) {
                $address_info['area_id'] = $area;
                $address_info['user_id'] = $base_info['user_id'];
                $address_info['role_id'] = $base_info['role_id'];
                Db::name('role_address')->insertGetId($address_info);
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            //设置筛选数据
            $area_data = set_model('area')->order(['parent_id' => 'asc'])->field('id,area_name,parent_id')->select()->toArray();
            $area_province = array();
            foreach ($area_data as $area_item) {
                if ($area_item['parent_id'] == 0) {
                    array_push($area_province, $area_item);//省
                    $key = array_search($area_item, $area_data);
                    array_splice($area_data, $key, 1);
                }
            }
            //todo auth

            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields();
            $this->view->model_info = $model_info;
            $this->assign('area_data', json_encode($area_data));
            $this->assign('area_province', json_encode($area_province));
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {

        global $_GPC;
        $id = (int)$id;
//        $area = 0;
        $model = set_model($this->admin);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
//                if ($_POST['area_province'] != null) {
//                    if ($_POST['area_province'] != null) $area = $_POST['area_province'];
//                    if ($_POST['area_city'] != null) $area = $_POST['area_city'];
//                    if ($_POST['area_area'] != null) $area = $_POST['area_area'];
//                }
            }
//            $role_address = set_model('role_address');
//            $address_info['area_id'] = $area;
//            $address_info['user_id'] = $data['user_id'];
//            $address_info['role_id'] = $data['role_id'];
//            $info_res = $role_address->add_content($address_info);
//            if ($info_res['code'] == 1) {
//                return $this->zbn_msg($info_res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
//            } else {
//                return $this->zbn_msg($info_res['msg'], 2);
//            }
            // todo  process data input
            Db::name($model_info['table_name'])->where($where)->update($data);
            $this->zbn_msg("ok");
        } else {
            //设置筛选数据
            $area_data = set_model('area')->order(['parent_id' => 'asc'])->field('id,area_name,parent_id')->select()->toArray();
            $area_province = array();
            foreach ($area_data as $area_item) {
                if ($area_item['parent_id'] == 0) {
                    array_push($area_province, $area_item);//省
                    $key = array_search($area_item, $area_data);
                    array_splice($area_data, $key, 1);
                }
            }
            $this->assign('area_data', json_encode($area_data));
            $this->assign('area_province', json_encode($area_province));
            //todo auth
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {

        $id = (int)$id;
        $model = set_model($this->admin);
        $model->where(['id' => $id])->delete();
        $data['code'] = 1;
        $data['msg'] = '操作成功！';
        return $data;
    }


    public function destroy()
    {
        $admin_ids = input("param.id/a");
        foreach ($admin_ids as $admin_id) {
            if ($admin_id == 1) {
                continue;
            }
            $tmp_admin = Users::get($admin_id);
            $this->check_admin_auth($tmp_admin);
            $tmp_admin->delete();
        }
        $data['code'] = 1;
        $data['msg'] = '操作成功！';
        return $data;
    }
}
