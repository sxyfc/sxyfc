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

use app\common\controller\AdminBase;
use app\common\model\Modules;
use app\common\model\UserMenu;
use think\Db;
use think\Log;
use think\Session;

class Service extends AdminBase
{

    /**
     * 异步加载
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function load_menu()
    {
        $menuList = $this->getAdminMenus();
        $menus = self::getAllChild(0, 0, $menuList);
        $new_menus= [];
        foreach($menus as $k=>$menu){

            if(!$menus[$k]['children']){
                unset($menus[$k]);

            }else{
                $new_menus[] = $menu;
            }
        }
        return $new_menus;
    }

    /**
     * load sys modules
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_sys_modules($module = "")
    {
        $sys_modules = ['core', 'admin', 'attachment', 'common', 'system', 'sms', 'update', 'member' , 'mhcms_professional'];
        $in_modules = $sys_modules;

        //todo : only retrive the site opened modules' menus

        //$this->site->get_site_modules();

        if (!$module) {
            $module_model = set_model('modules');
            $modules = $module_model->select();

            foreach ($modules as $module) {
                if ($module['status']) {
                    $in_modules[] = $module['module'];
                }
            }
        } else {
            $in_modules[] = $module;
        }

        return $in_modules;
    }

    /**
     * 获取所有子节点
     * @param int $pid
     * @param int $level
     * @param array $menus_all
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAllChild($pid = 0, $level = 0, $menus_all = [])
    {
        global $_W, $_GPC;
        $menus = [];
        foreach ($menus_all as $menu) {
            if ($menu['user_menu_parentid'] == $pid) {
                $menu['title'] = $menu['user_menu_name'];
                $menus[] = $menu;
            }
        }
        $level++;
        foreach ($menus as $key => &$menu) {
            // fix alias params bug
            $alias = [];
            if ($menu['alias'] > 0) {
                $alias = UserMenu::get(['id' => $menu['alias']]);
            }

            $user_menu_params = $menu['user_menu_params'] ? $menu['user_menu_params'] : $alias['user_menu_params'];

            $user_menu_params = self::str_to_url_params($user_menu_params);
            $user_menu_params['user_menu_id'] =  $menu['id'];
            // 多语言
            $menu['title'] = zlang($menu['user_menu_name']);
            $menu['icon'] = $menu['user_menu_icon'];
            $menu['children'] = '';

            $menu['url'] =url( $menu['user_menu_module']."/" . $menu['user_menu_controller'] . "/" .$menu['user_menu_action'] , $user_menu_params);

            //$menu['url'] = nb_url(['r' => $menu['user_menu_module'] . "." . $menu['user_menu_controller'] . "." . $menu['user_menu_action'], $menu['user_menu_params'], 'user_menu_id' => $menu['id']], "", $user_menu_params);
            if (Db::name('user_menu')->where('user_menu_parentid = ' . $menu['id'])->find()) {
                $menu['children'] = self::getAllChild($menu['id'], $level, $menus_all);
            }
        }
        return $menus;
    }

    public static function str_to_url_params($str){
        $new_params = [];
        $params = explode("&" , $str);
        foreach($params as $param){
            $param = explode("=" , $param);

            $new_params[$param[0]] = $param[1];
        }
        return $new_params;
    }


    /**
     * 管理员菜单那
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAdminMenus()
    {
        global $_W;
        $in_modules = $this->get_sys_modules();
        $where = ['is_admin' => 1];
        $where['id'] = ["NOT IN" , '0,' .$_W['site']['config']['hide_menus']];
        if (!$this->super_power) {
            $where['user_menu.module'] = ['IN', $in_modules];

            // 先获取个人菜单，不存在的话获取角色菜单
            $menuList = Db::view('user_menu', '*')
                ->view('user_menu_allot', 'user_id,user_menu_id', "user_menu_allot.user_id=" . $this->user['id'] . ' and user_menu.id=user_menu_allot.user_menu_id and user_menu.is_admin = 1  and user_menu.user_menu_display = 1 ')
                ->where($where)->select();
            $result = $menuList->toArray();

            if(empty($result)){
                $menuList = Db::view('user_menu', '*')
                    ->view('user_menu_access', 'user_role_id,user_menu_id', "user_menu_access.user_role_id=" . $this->admin_info['role_id'] . ' and user_menu.id=user_menu_access.user_menu_id and user_menu.is_admin = 1  and user_menu.user_menu_display = 1  ')
                    ->where($where)->select();
            }
        } else {
            //超级管理员分组

            $where['module'] = ['IN', $in_modules];
            if ($this->current_admin['id'] == 1) {
                $where['user_menu_display'] = 1;
                $menuList = UserMenu::where($where)->order('user_menu_listorder', 'asc')->select();
            } else {
                //超级管理员其他成员只展示分配的菜单 暂不支持
                $menuList = [];
            }
        }

        foreach ($menuList as $k => $menu) {
            $menu['parent_id'] = $menu['user_menu_parentid'];
            $menu['name'] = $menu['user_menu_name'];
            $menuList[$k] = $menu;
        }
        return $menuList;
    }

    /**
     * 管理员菜单那
     * @param string $module
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_module_admin_menus($module = "")
    {
        if ($module) {
            $in_modules[] = $module;
            $in_modules[] = "system";
            $module = Modules::get(['module'=>$module]);
            if($module['parent_modules']){
                $in_modules = array_merge($in_modules , array_filter(explode("," , $module['parent_modules']))) ;
            }
        } else {
            $in_modules = $this->get_sys_modules();
        }

        if (!$this->super_power) {
            $where['user_menu.module'] = ['IN', $in_modules];

            // 先获取个人菜单，不存在的话获取角色菜单
            $menuList = Db::view('user_menu', '*')
                ->view('user_menu_allot', 'user_id,user_menu_id', "user_menu_allot.user_id=" . $this->user['id'] . ' and user_menu.id=user_menu_allot.user_menu_id and user_menu.is_admin = 1  and user_menu.user_menu_display = 1 ')
                ->where($where)->select();
            $result = $menuList->toArray();

            if(empty($result)){
                $menuList = Db::view('user_menu', '*')
                    ->view('user_menu_access', 'user_role_id,user_menu_id', "user_menu_access.user_role_id=" . $this->admin_info['role_id'] . ' and user_menu.id=user_menu_access.user_menu_id and user_menu.is_admin = 1  and user_menu.user_menu_display = 1  ')
                    ->where($where)->select();
            }
        } else {
            //超级管理员分组

            $where = ['is_admin' => 1];
            $where['module'] = ['IN', $in_modules];
            if ($this->current_admin['id'] == 1) {
                $menuList = UserMenu::where($where)->order('user_menu_listorder', 'asc')->select();
            } else {
                //超级管理员其他成员只展示分配的菜单 暂不支持
                $menuList = [];
            }
        }

        foreach ($menuList as $k => $menu) {
            $menu['parent_id'] = $menu['user_menu_parentid'];
            $menu['name'] = $menu['user_menu_name'];
            $menuList[$k] = $menu;
        }
        return $menuList;
    }

    /**
     * 员菜单那
     * @param string $module
     * @param bool $show_hide
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_module_user_menus($module, $show_hide = false)
    {
        global $_W;
        //
        if (!$show_hide) {
            $where['user_menu_display'] = 1;
        }
        $in_modules = $this->get_sys_modules($module);

        if (!$_W['super_power']) {
            $where['user_menu.module'] = ['IN', $in_modules];
            $where['user_menu.is_admin'] = ['IN', 0];

            // 先获取个人菜单，不存在的话获取角色菜单
            $menuList = Db::view('user_menu', '*')
                ->view('user_menu_allot', 'user_id,user_menu_id', "user_menu_allot.user_id=" . $this->user['id'] . ' and user_menu.id=user_menu_allot.user_menu_id and user_menu.is_admin = 1  and user_menu.user_menu_display = 1 ')
                ->where($where)->select();
            $result = $menuList->toArray();

            if(empty($result)){
                $menuList = Db::view('user_menu', '*')
                    ->view('user_menu_access', 'user_role_id,user_menu_id', "user_menu_access.user_role_id=" . $this->admin_info['role_id'] . ' and user_menu.id=user_menu_access.user_menu_id and user_menu.is_admin = 1  and user_menu.user_menu_display = 1  ')
                    ->where($where)->select();
            }
        } else {
            //超级管理员分组
            $where = ['is_admin' => 0];
            $where['module'] = ['IN', $in_modules];
            $menuList = UserMenu::where($where)->order('user_menu_listorder desc')->select();
        }

        foreach ($menuList as $k => $menu) {
            $menu['parent_id'] = $menu['user_menu_parentid'];
            $menu['name'] = $menu['user_menu_name'];
            $menuList[$k] = $menu;
        }
        return $menuList;
    }

    public function check_session(){
        $start = Session::get('session_start_time');
        return $start;
    }
}