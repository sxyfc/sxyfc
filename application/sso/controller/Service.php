<?php

namespace app\sso\controller;

use app\common\controller\ApiBase;
use app\common\model\UserRoles;
use app\common\model\Users;

class Service extends ApiBase
{

    public function login()
    {

        global $_W, $_GPC;
        $query = $_GPC['query'];
        $code = $query['code'];
        $small_app = $this->small_app;


        $where = ['user_name' => $query['user_name'], 'is_admin' => 0];
        $current_user = Users::get($where);

        if ($current_user) {
            //获取角色信息
            $current_user_role = UserRoles::get($current_user['user_role_id']);
            if ((int)$current_user_role['status'] == 0) {
                $this->zbn_msg("对不起，您所在的用户组已经被管理员禁用！ ", 2, "false", 4000);
            }
        }
        if ($current_user && $current_user['pass'] == crypt_pass($query['password'], $current_user['user_crypt'])) {
            $res_content['auth_str'] = $current_user->log_user_in();
            $res_content['user_id'] = $current_user['id'];
            $res_content['user'] = $current_user;
            $ret['data'] = $res_content;
            $ret['code'] = 1;
        } else {
            $ret['code'] = 2;
            // user has been deleted
            $ret['msg'] = "对不起 账户验证失败";
        }

        echo json_encode($ret);die();


    }
}