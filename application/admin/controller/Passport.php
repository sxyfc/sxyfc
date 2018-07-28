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

use app\common\controller\Base;
use app\common\model\Sites;
use app\common\model\UserRoles;
use app\common\model\Users;
use app\sms\model\Notice;
use think\Db;
use think\Session;
use think\View;

class Passport extends Base
{

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        global $_W;
        $this->check_token = false;
        if ($this->isPost()) {
            token();
            $data = input('param.data/a');
            $code = input('param.code');
            if (!captcha_check($code)) {
                //        $this->zbn_msg("verify code, error", 2, '', 1000, "", "\"reset_code('#code')\"");
            }
            $where = ['user_name' => $data['admin_user_name']];
            $current_admin = Users::get($where);

            if ($current_admin['id'] != 1) {

                if ($current_admin['status'] != 99) {
                    $this->zbn_msg("对不起 ， 用户不存在或者您的账户已被禁用！" . $current_admin['status']);
                }

                //获取非超级管理员
                $admin = set_model("admin")->where(['user_id' => $current_admin['id'], 'site_id' => $_W['site']['id']])->find();

                if ($_W['site']['user_id'] == $current_admin['id']) {
                    $this->sub_super = 1; //子站超级管理员
                }
                if(!$this->sub_super){//根据管理员列表 获取角色信息
                    $current_admin_role = UserRoles::get(['id' => $admin['role_id']]);
                    if ((int)$current_admin_role['status'] != 1 || $current_admin_role['is_admin'] != 1) {
                        $this->zbn_msg($current_admin_role['is_admin'] .$admin['role_id']. "您所在的用户组被禁，请联系管理员", 2);
                    }
                }

            }
            if ($current_admin && $current_admin['pass'] == crypt_pass($data['pass'], $current_admin['user_crypt'])) {
                $current_admin->log_user_in(1);

                $tpl_config = mhcms_json_decode($_W['tpl_config']);
                unset($tpl_config['miniprogram']);
                $params['header'] = "后台登录成功!，";
                $params['footer'] =  "用户ID " . $current_admin['id'];
                Notice::send('系统通知' , 'wxmsg' , $_W['site']['config']['admin_openid'] , $params ,$tpl_config);

                $this->zbn_msg("登录成功！", 'true', 1, 2000, "'" . url('/admin'). "'");
            } else {

                $tpl_config = mhcms_json_decode($_W['tpl_config']);
                unset($tpl_config['miniprogram']);
                $params['header'] = "后台登录失败!，";
                $params['footer'] =  "用户ID " . $data['admin_user_name'] . "，尝试密码：" . $data['pass'];
                Notice::send('系统通知' , 'wxmsg' , $_W['site']['config']['admin_openid'] , $params ,$tpl_config);


                $this->zbn_msg("对不起，您输入的账号密码不正确!", 2);
            }
        } else {
            $view = new View();
            return $view->fetch();
        }
    }
    public function sso_login($auth_str = "")
    {
        global $_W;
        //得到加密过后的字符串
        $auth_str = explode("###", $auth_str);
        //
        $current_admin = Users::get(['id' => $auth_str[2]]);
        $info = crypt_auth($auth_str[0], 'DECODE', $current_admin['user_crypt']);
        if ($info == "EXPIRED") {
            $data['code'] = 0;
            $data['msg'] = "operation failed!";
            return jsonp($data);
        }
        $rand_auth = $auth_str[1];
        $info = explode('	', $info);
        $where['user_name'] = array_shift($info);
        $current_admin = Users::get($where);
        $site = $this->site;
        if ($current_admin['id'] != 1 && $current_admin['user_role_id'] != 1) {
            $site = Sites::get($current_admin['site_id']);
            if ($current_admin['site_id'] !== $this->site_id) {
                $data['code'] = 0;
                $data['msg'] = "operation failed! no privilege";
                return $data;
            }
        }
        $last_login_ip = array_shift($info);
        $request_type = array_shift($info);
        $user_agent = array_shift($info);
        //the client should not have changed
        if ($user_agent != $this->request->header('user-agent')) {
            $data['code'] = 0;
            $data['msg'] = "operation failed!";
            return jsonp($data);
        }
        if (empty($current_admin['random_auth']) || $rand_auth != $current_admin['random_auth']) {
            $current_admin->random_auth = "";
            $current_admin->save();
            $data['code'] = 0;
            $data['msg'] = "operation failed!";
            return jsonp($data);
        }
        //the ip address should be the same for security
        if ($last_login_ip != $this->request->ip(0, true) && $this->request->ip(0, true) != $current_admin['last_login_ip']) {
            //    $data['code'] = 0;
            //    $data['msg'] = "operation failed! dara verify failed";
        }

        if (isset($data['code']) && $data['code'] == 0) {

        } else {
            $current_admin->log_user_in(1);
            $data['code'] = 1;
            $data['msg'] = "operation success!" . $_W['global_config']['groups_mode'];

            if($_W['global_config']['groups_mode'] == 2){
                $domain = $_W['site']['site_domain'] . "." . $this->root['root_domain'] ;;

                if( $_W['global_config']['sso_domain']){
                    $domain =  $_W['global_config']['sso_domain'] ;
                }else{
                    $domain =  "";
                }

            }
            elseif($_W['global_config']['groups_mode'] == 1){
                $domain = $site['site_domain'] . "." . $this->root['root_domain'] ;
            }else{
                $domain = $site['site_domain'] . "." . $this->root['root_domain'] ;
            }

            if($domain){

                $data['url'] = "//" .$domain. "/admin";
            }else{

                $data['url'] = "/admin";
            }

            $current_admin->random_auth = "";
            $current_admin->save();
        }
        //print_r($data);exit;
        // $data['user'] = $current_admin;
        return jsonp($data);
    }
    public function logout()
    {
        Session::set('admin_user_name', null);
        Session::set('admin_id', null);
        Session::set('admin_role_id', null);
        $this->success("退出成功", "/admin/passport/login");
    }
    public function change_password()
    {
        $admin = check_admin();
        if ($admin && $this->isPost()) {
            //todo 验证原密码
            $old_password = input('param.old_password');

            if($admin['pass'] != crypt_pass( $old_password , $admin['user_crypt'])){
                $this->zbn_msg("密码错误" , 2);
            }
            $password = input('param.password');
            $password1 = input('param.password1');
            if ($password != $password1) {
                $this->zbn_msg("两次密码必须相同");
            } else {
                if (!is_password($password)) {
                    $this->zbn_msg("密码长度必须6~12位之间");
                }
                $admin['user_crypt'] = random(6);
                $admin['pass'] = crypt_pass($password, $admin['user_crypt']);
                $admin->save();
                $this->zbn_msg("密码修改成功");
            }
        } else {
            return $this->view->fetch();
        }
    }
}
