<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserRoles;
use app\common\model\Users;
use think\Db;
use think\Log;

class InfoSearch extends AdminBase
{
    /*
     * 房管列表
     */
    public function area_manage_list()
    {
        $where['user_role_id'] = 22;
        $user_name = trim(input('param.user_name', ' ', 'htmlspecialchars'));
        if ($user_name) {
            $where['user_name'] = array('LIKE', '%' . $user_name . '%');
        }

        $list = Users::where($where)->order('id desc')->paginate(config('list_rows'));
        $pages = $list->render();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = IpToArea($val['create_ip']);
            $val['last_ip_area'] = IpToArea($val['last_login_ip']);
        }

        $this->view->assign('page', $pages);
        $this->view->assign('list', $list);
        $this->view->assign('user_name', $user_name);

        $this->view->field_list = set_model('users')->model_info->get_admin_column_fields();
        return $this->view->fetch();
    }

    /*
     * 房管下辖房源管家
     */
    public function house_manage()
    {
        $user_name = trim(input('param.user_name'));
        if ($user_name) {
            $where['user_name'] = array('LIKE', '%' . $user_name . '%');
        }

        $area_manage_id = trim(input('param.area_manage_id'));
        $where['parent_id'] = $area_manage_id;

        $list = Users::where($where)->order('id desc')->paginate(config('list_rows'));
        $pages = $list->render();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = IpToArea($val['create_ip']);
            $val['last_ip_area'] = IpToArea($val['last_login_ip']);
        }

        $this->view->assign('page', $pages);
        $this->view->assign('list', $list);

        $this->view->field_list = set_model('users')->model_info->get_admin_column_fields();
        return $this->view->fetch();
    }

    public function house_manage_list()
    {
        $where['user_role_id'] = 23;
        $user_name = trim(input('param.user_name', ' ', 'htmlspecialchars'));
        if ($user_name) {
            $where['user_name'] = array('LIKE', '%' . $user_name . '%');
        }

        $list = Users::where($where)->order('id desc')->paginate(config('list_rows'));
        $pages = $list->render();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = IpToArea($val['create_ip']);
            $val['last_ip_area'] = IpToArea($val['last_login_ip']);
        }

        $this->view->assign('page', $pages);
        $this->view->assign('list', $list);
        $this->view->assign('user_name', $user_name);

        $this->view->field_list = set_model('users')->model_info->get_admin_column_fields();
        return $this->view->fetch();
    }
}
