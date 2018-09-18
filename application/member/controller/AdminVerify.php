<?php

namespace app\member\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\Users;
use think\Db;

class AdminVerify extends AdminBase
{
    public function index()
    {
        global $_W, $_GPC;
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();
        //model_info
        $this->view->model_info = $model_info;
//        $where = [];
        //data list
//        $where['site_id'] = $this->site['id'];
        $where2['personal_verify'] = 1;
        $where1['company_verify'] = 1;
        $this->view->mapping = $this->mapping;
        $lists = $model->whereOr($where1)->whereOr($where2)->order("create_at desc")->paginate();
        $this->view->lists = $lists;
        return $this->view->fetch();
    }

    public function edit($user_id)
    {
        global $_GPC;
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $id = (int)$user_id;
        $where = [$model_info['id_key'] => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post . data / a');//get the base info
            }
            // todo  process data input
            $model_info->edit_content($data, $where);
            $this->zbn_msg("ok");
        } else {
            //todo auth
            $this->view->list = $model_info->get_user_publish_fields($detail);;
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function pass($user_id, $type)
    {
        global $_GPC, $_W;
        $user = Users::get($user_id);
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $id = (int)$user_id;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = [$model_info['id_key'] => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        $update = [];
        if ($detail) {
            switch ($type) {
                case "personal":
                    $update['personal_verify'] = 99;
                    break;
                case "company":
                    $update['company_verify'] = 99;
                    break;
            }
            $res = $model_info->edit_content($update, $where);
            if ($res['code'] == 1) {
                $userInfo = Db::name('users')->where(['id' => $user_id])->find();

                if (!$result = Db::name('admin')->where(['user_id' => $user_id])->find()) {
                    $admin_info['site_id'] = $_W['site']['id'];
                    switch ($type) {
                        case "personal":
                            $admin_info['role_id'] = 24;
                            break;
                        case "company":
                            $admin_info['role_id'] = 23;
                            break;
                    }
                    $admin_info['user_id'] = $user_id;
                    $admin_info['user_name'] = $userInfo['user_name'];
                    set_model('admin')->insert($admin_info);
                }

                Db::name('users')->where(['id' => $user_id])->update(['parent_id' => $this->user['id']]);
                $user->is_verify = 1;
                if ($user['user_role_id'] == 2 || $user['user_role_id'] == 4)
                    $user->user_role_id = 24;
                $user->save();
                $area_id = set_model('role_address')->where(['user_id' => $this->user['id']])->field('area_id')->find();
                if (!isset($area_id)) $area_id = 26;
                $address_info['area_id'] = $area_id;
                $address_info['user_id'] = $user_id;
                switch ($type) {
                    case "personal":
                        $admin_info['role_id'] = 24;
                        break;
                    case "company":
                        $admin_info['role_id'] = 23;
                        break;
                }
                if (!Db::name('role_address')->where(['user_id' => $user_id])->find()) {
                    Db::name('role_address')->insertGetId($address_info);
                } else {
                    Db::name('role_address')->where(['user_id' => $user_id])->update($address_info);
                }
            }
            return $res;
            $this->zbn_msg("审核成功", 1);
        } else {
            $this->zbn_msg("对不起，用不户存在", 2);
        }
    }

    public function cancle($user_id)
    {
        global $_GPC;
        $user = Users::get($user_id);
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $id = (int)$user_id;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = [$model_info['id_key'] => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        $update = [];
        if ($detail) {
            $update['personal_verify'] = 0;
            $update['company_verify'] = 0;
            $res = $model_info->edit_content($update, $where);
            if ($res['code'] == 1) {
                $user->is_verify = 0;
                $user->save();
                $this->zbn_msg("操作成功", 1);
            } else {
                $this->zbn_msg("操作失败，" . $res['msg'], 1);
            }
        } else {
            $this->zbn_msg("对不起，用不户存在", 2);
        }
    }
}