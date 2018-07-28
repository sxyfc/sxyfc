<?php
namespace app\member\controller;

use app\common\controller\HomeBase;
use app\common\model\UserRoles;
use app\common\model\Users;
use think\Cookie;
use think\Session;

class Passport extends HomeBase
{
    public function _initialize()
    {
        parent::_initialize();
        $this->view->config([
            'view_path'     => APP_PATH . '../tpl/' . MODULE_NAME ."/",
        ]);
    }

    /**
     * @return mixed
     */
    public function register()
    {
        if ($this->isPost()) {
            $foreword_url = "";
            $code = 2;
            $user_data['password'] = input('param.password') == input('param.password1') ? input('param.password') : $this->zbn_msg("两次密码必须一样", 2);;
            $user = new Users();
            $user_data['user_name'] = input('param.email');
            $user_data['site_id'] = $GLOBALS['site_id'];
            $user_data['root_id'] = $GLOBALS['root_id'];
            $user_data['user_crypt'] = random(6);
            $user_data['pass'] = crypt_pass($user_data['password'], $user_data['user_crypt']);
            $validate = \think\Loader::validate('Users');
            $result = $validate->check($user_data);
            if (!$result) {
                $msg = $validate->getError();
            } else {
                if ($res = $user->allowField(true)->validate(true)->save($user_data)) {
                    $code = 1;
                    $msg = "注册成功";
                    $foreword_url = "/member";
                } else {
                    $code = 2;
                    $msg = $res;
                    $foreword_url = "";
                }
            }
            $this->zbn_msg($msg, $code, true, 2000, $foreword_url);
        } else {
            return $this->view->fetch();
        }
    }

    public function login()
    {
        if ($this->isPost()) {
            $data = input('param.data/a');
            $code = input('param.code');
            if (!captcha_check($code)) {
                //    $this->zbn_msg("verify code, error", 2, '', 1000, "", "\"reset_code('#code')\"");
            }
            $where = ['user_name' => $data['email'], 'is_admin' => 0];
            $current_user = Users::get($where);


            if ($current_user) {
                //获取角色信息
                $current_user_role = UserRoles::get($current_user['user_role_id']);
                if ((int)$current_user_role['user_role_status'] == 0) {
                    $this->zbn_msg("sorry I can't  Log you in now ， Because The Group You are In is Disabled By The System! ", 2, "false", 4000);
                }
            }

            if ($current_user && $current_user['pass'] == crypt_pass($data['password'], $current_user['user_crypt'])) {
                $this->log_user_in($current_user);
                $this->zbn_msg("success", 'true', 1, 1000, "", 'top.reload_page()');
            } else {
                $this->zbn_msg("account verify failed!", 2);
            }
        } else {
            return $this->view->fetch();
        }
    }

    /**
     * @param $user
     */
    private function log_user_in(Users $user)
    {
        $sso_str = crypt_auth_str($user);
        Session::set('auth_info', $sso_str);
        Session::set('user_id', $user->user_id);
        Session::set('user_role_id', $user->user_role_id);
        Session::set('user_name', $user->user_name);
        Cookie::set('user_id', $user->user_id);
        //更新用户登录时间
        $user->last_login = date("Y-m-d H:i:s", SYS_TIME);
        $user->last_login_ip = $this->request->ip(0, true);
        $user->save();
    }

    public function logout()
    {

        Session::set('auth_info', null);
        Session::set('user_id', null);
        Session::set('user_role_id', null);
        Session::set('user_name', null);
        Cookie::set('user_id', null);
        $this->success("退出成功", "/member/passport/login");
    }

}