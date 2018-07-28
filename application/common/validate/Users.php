<?php
namespace app\common\validate;

use think\Validate;

class Users extends Validate
{
    protected $rule = [
        'user_name' => 'require|checkName:',
        // 'user_mobile'  =>  'require|checkMobile:',
        'pass' => 'require|min:6'
    ];


    // 自定义验证规则
    protected function checkName($value, $rule, $data)
    {
        $res = \app\common\model\Users::get(['user_name' => $value]);
        if ($res) {
            return "用户名已经被注册！";
        } else {
            return true;
        }
    }

    // 自定义验证规则
    protected function checkMobile($value, $rule, $data)
    {

        $res = \app\common\model\Users::get(['mobile' => $value]);
        if ($res) {
            return "该会员名称已经被注册！";
        } else {
            return true;
        }
    }
}