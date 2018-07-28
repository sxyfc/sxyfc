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
        return $this->view->fetch();
    }
}