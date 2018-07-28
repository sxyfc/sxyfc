<?php
namespace app\sso\controller;

use app\common\controller\SsoBase;
use app\common\model\Roots;
use app\common\model\Sites;
use app\common\model\UserMenu;
use think\Cookie;

class Index extends SsoBase
{
    public function index(){
        header("location:".url("passport/login"));exit;
    }
}