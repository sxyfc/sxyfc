<?php
namespace app\sso\controller;

use app\common\controller\SsoBase;
use app\common\model\Roots;
use app\common\model\Sites;
use app\common\model\UserMenu;
use think\Cookie;
use think\Db;

class Menu extends SsoBase
{


    public function load_member_menu($site_id)
    {
        //
        $site_id = (int)$site_id;

        //TODO:顶部菜单
        $this->site = Sites::get($site_id);
        $this->view->site = $this->site;
        $this->root = Roots::get($this->site['root_id']);
        $this->view->root = $this->root;
        $node_types = $this->site->get_node_types($this->site);
        $this->view->node_types = $node_types;
        $data = $this->view->fetch();
        return jsonp($data);
    }


    public function load_menu($site_id)
    {
        //
        $site_id = (int)$site_id;

        /**
         * top menu loader
         */
        $this->site = Sites::get($site_id);
        $this->view->site = $this->site;
        $this->root = Roots::get($this->site['root_id']);
        $this->view->root = $this->root;

        $node_types = $this->site->get_node_types($this->site);

        $this->view->node_types = $node_types;

        /**
         * Users menu , only load menus for the current roles
         */


        $globals_menus = [];
        if($this->config['system']['config_data']['load_core_user_menu']['value'] = 1){
            /**
             * load global menus
             */
            $map = [
                'is_admin' => 0,
                'root_id' => 0,
            ];
            $globals_menus =  UserMenu::all($map)->toArray();
        }

        /**
         * special menus for current user
         */
        $menuList = Db::view('user_menu','*')
            ->view('user_menu_allot','user_id,user_menu_id','user_menu.id=user_menu_allot.user_menu_id')
            ->where('user_id','=',$this->user['id'])
            ->select()->toArray();
        if(empty($menuList)){
            $menuList = Db::view('user_menu','*')
                ->view('user_menu_access','user_role_id,user_menu_id','user_menu.id=user_menu_access.user_menu_id')
                ->where('user_role_id','=',$this->user['user_role_id'])
                ->select()->toArray();
        }
        
        $user_menus = array_merge($globals_menus , $menuList);
        $this->view->user_menus = $user_menus;
        $data = $this->view->fetch();
        return jsonp($data);
    }
}