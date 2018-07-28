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

class UserMenu extends Common
{
    //
    /**
     * 获取指定节点下的所有子节点(不含快捷收藏的菜单)
     * @param int $pid 父ID
     * @param int $status 状态码 不等于1则调取所有状态
     * @param string $cache_tag 缓存标签名
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    public static function getAllChild($pid = 0,    $level = 0)
    {
        $map = [];
        // 非开发模式，只显示可以显示的菜单
        $map['debug'] = 0;
        $map['user_menu_parentid'] = $pid;
        $menus = self::where($map)->order('user_menu_listorder,id') ->select()->toArray();
        $level++;
        foreach ($menus as $key => &$menu) {
            $menu['childs'] = '';
            if (self::where('user_menu_parentid = ' . $menu['id'])->find()) {
                $menu['childs'] = self::getAllChild($menu['id'],  $level);
            }
        }
        return $menus;
    }
}
