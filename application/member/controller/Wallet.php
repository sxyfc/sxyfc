<?php
namespace app\member\controller;

use app\common\controller\UserBase;
use app\common\model\Node;
use app\common\model\NodeTypes;
use app\common\model\UserRoles;
use app\common\model\Users;

class Wallet extends UserBase
{
    public function index()
    {

        $show_log = true;
        $users = db('users')->where(['id' => $this->user_id])->find();

        if (!$menu_access_result = db('user_menu_access')->where(['user_role_id' => $users['user_role_id'], 'user_menu_id' => 7032])->find()) {
            if (!$menu_allot_result = db('user_menu_allot')->where(['user_id' => $this->user_id, 'user_menu_id' => 7032])->find()) {
                $show_log = false;
            }
        }

        $this->view->assign('show_log', $show_log);
        return $this->view->fetch();
    }
}