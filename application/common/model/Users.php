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
namespace app\common\model;

use app\core\util\MhcmsDistribution;
use app\wechat\util\WechatUtility;
use think\Cookie;
use think\Loader;
use think\Request;
use think\Session;

class Users extends Common
{
    protected $hidden = ['pass', 'user_crypt'];

    public static function create_user($data)
    {
        global $_W;
        $user = new Users();
        $user_data = $data;
        $user_data['site_id'] = $data['site_id'] ? $data['site_id'] : $_W['site']['id'];
        $test = $user->where(['user_name' => $user_data['user_name']])->find();
        if ($test) {
            return $test;
        }
        $user_data['site_id'] = $data['site_id'] ? $data['site_id'] : $_W['site']['id'];
        $user_data['user_crypt'] = random(6);
        $user_data['pass'] = crypt_pass($data['password'], $user_data['user_crypt']);
        //todo if user need to check
        $user_data['user_status'] = $user_data['status'] = 99;
        $user_data['user_role_id'] = 4;
        $user_data['created'] = date("Y-m-d H:i:s");

        if ($res = $user->allowField(true)->validate(true)->save($user_data)) {
            if( $_W['refer']){
                MhcmsDistribution::make_down_line($user['id'], $_W['refer']);
            }
            return $user;
        } else {
            return false;
        }
    }

    public static function create_weixin_connect_user($fans){
        //unionid come first
        if($fans['unionid']){
            $where['wechat_unionid'] = $fans['unionid'];
            $user = self::where($where)->find();
            if($user){
                return $user;
            }
        }


        //openid come second
        $where['user_name'] = $fans['openid'];
        $user = self::where($where)->find();
        if($user){
            return $user;
        }

        //create user
        $data = [];
        $data['user_name'] = $fans['openid'];
        return self::create_connect_user($data);
    }
    /**
     * 根据信息创建用户
     * @param $data
     * @return bool|false|int |Users
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function create_connect_user($data)
    {
        global $_W;
        $user = new Users();
        $user_data['user_name'] = $data['user_name'];
        $user_data['site_id'] = isset($data['site_id']) && $data['site_id'] ? $data['site_id'] : $_W['site']['id'];
        $test = $user->where(['user_name' => $user_data['user_name']])->find();
        if ($test) {
            return true;
        }
        $user_data['site_id'] = $data['site_id'] ? $data['site_id'] : $_W['site']['id'];
        $user_data['user_crypt'] = random(6);
        $user_data['pass'] = "NOTSET";
        //if user need to check
        $user_data['user_status'] = $user_data['status'] = 99;
        $user_data['user_role_id'] = 4;
        if($data['wechat_unionid']){
            $user_data['wechat_unionid'] = $data['wechat_unionid'];
        }
        $user_data['created'] = date("Y-m-d H:i:s");
        WechatUtility::logging('-reginfo-' , $user_data);
        if ($res = $user->allowField(true)->validate(true)->save($user_data)) {
            if(isset($_W['refer']) && $_W['refer']){
                MhcmsDistribution::make_down_line($user['id'], $_W['refer']);
            }
            return $user;
        } else {
            return false;
        }
    }

    public function getUserRoleIdTextAttr()
    {
        $roles = new UserRoles();
        $user_roles = $roles->fetchAll('user_role_id');
        return zlang($user_roles[$this->user_role_id]['user_role_name']);
    }

    public function getUserStatusTextAttr()
    {
        //$user_roles[$value];
        $status = [-1 => '删除', 0 => '禁用', 99 => '正常', 2 => '待审核'];
        return zlang($status[$this->status]);
    }

    /**
     * 获取用户分组绑定的模型的附加数据
     */
    public function get_external()
    {
        $this->role = UserRoles::get($this->user_role_id);
        if ($this->role->model_id) {
            $map = [
                'user_id' => $this->id,
            ];
            $this->external = set_model($this->role->model_id)->where($map)->find();
            return $this->external;
        }
        return false;
    }

    public function delete_user()
    {
        $role = UserRoles::get($this->user_role_id);
        if ($role) {
            $external = $this->get_user_external();
            $this->delete();
            // 移除属下
            $where['creator_id'] = $this->user_id;
            $data['creator_id'] = 0;
            $this->where($where)->update($data);
            //移除下线
            $where['parent_id'] = $this->user_id;
            $data['parent_id'] = 0;
            $this->where($where)->update($data);
            //删除扩展信息 external
            if ($role['node_type_id'] && $role['node_type_id'] != 9999) {
                $node = new Node();
                $node->delete_node($external);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function get_user_external()
    {
        $this->role = UserRoles::get($this->user_role_id);
        if ($this->role->node_type_id && $this->role->node_type_id != 9999) {
            $map = [
                'user_id' => $this->user_id,
                'node_type_id' => $this->role->node_type_id
            ];
        }
        $node = Node::where($map)->find();
        $this->external = $node->get_node($node->node_id);
        return $this->external;
    }

    /**
     * 用户登录
     * @param int $is_admin
     * @return string
     */
    public function log_user_in($is_admin = 0)
    {
        Session::set('start_time', time());
        if ($is_admin) {
            Session::set('admin_id', $this->id);
            Cookie::set("admin_id", $this->id);
            $sso_str = crypt_auth_str($this);
            Session::set('auth_admin_info', $sso_str);
            Cookie::set('auth_admin_info', $sso_str);
        } else {
            Session::set('user_id', $this->id);
            Cookie::set("user_id", $this->id);
            $sso_str = crypt_auth_str($this);
            Session::set('auth_info', $sso_str);
            Cookie::set('auth_info', $sso_str);
        }

        //更新用户登录时间
        $this->last_login = date("Y-m-d H:i:s", SYS_TIME);
        $this->last_login_ip = Request::instance()->ip();
        $this->save();
        return $sso_str;
    }
}