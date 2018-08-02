<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace app\core\util;

use app\common\model\UserMenu;
use think\Db;

/**
 * 文件类型缓存类
 * @author    liu21st <liu21st@gmail.com>
 */
class MhcmsMenu
{

    public static $system_modules = ['member'];

    /**
     * @param $module
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_member_menu($module)
    {
        $menuList = $this->get_module_user_menus($module);
        return self::getAllChild(0, 0, $menuList);
    }
    /**
     * @param $module
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_member_module_menu($module)
    {
        $menuList = $this->get_module_user_menus($module , true , false);
        return self::getAllChild(0, 0, $menuList);
    }
    /**
     * load sys modules
     * @param $module
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_sys_modules($module)
    {
        $sys_modules = ['core', 'admin', 'attachment', 'common', 'system', 'sms', 'update', 'member'];
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
    private static function getAllChild($pid = 0, $level = 0, $menus_all = [])
    {
        $menus = [];
        foreach ($menus_all as $menu) {

            if ($menu['user_menu_parentid'] == $pid) {
                $menu['title'] = $menu['user_menu_name'];
                $menus[] = $menu;
            }
        }
        $level++;
        foreach ($menus as $key => &$menu) {
            // 多语言
            $menu['title'] = zlang($menu['user_menu_name']);
            $menu['icon'] = $menu['user_menu_icon'];
            $menu['children'] = '';
            $menu['url'] = nb_url(['r' => $menu['user_menu_module'] . "." . $menu['user_menu_controller'] . "." . $menu['user_menu_action'], $menu['user_menu_params'], 'user_menu_id' => $menu['id']]);
            if (Db::name('user_menu')->where('user_menu_parentid = ' . $menu['id'])->find()) {
                $menu['children'] = self::getAllChild($menu['id'], $level, $menus_all);
            }
        }
        return $menus;
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
    public function get_module_user_menus($module, $show_hide = false , $load_system_modules = true)
    {
        global $_W;
        //
        if ($show_hide) {

        } else {
            $where['user_menu_display'] = 1;
        }

        if($load_system_modules){
            $in_modules = $this->get_sys_modules($module);
        }else{
            $in_modules = [$module];
        }


        if (!isset($_W['super_power'])) {
            $where['user_menu.module'] = ['IN', $in_modules];
            $where['user_menu.is_admin'] = ['IN', 0];
            $menuList = Db::view('user_menu','*')
                ->view('user_menu_allot','user_id,user_menu_id','user_menu.id=user_menu_allot.user_menu_id')
                ->where('user_id','=',$_W['id'])->select();

            $result = $menuList->toArray();
            if(empty($result)){
                $menuList = Db::view('user_menu', '*')
                    ->view('user_menu_access', 'user_role_id,user_menu_id', "user_menu_access.user_role_id=" . $_W['user_role_id'] . ' and user_menu.id=user_menu_access.user_menu_id  ')
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
}