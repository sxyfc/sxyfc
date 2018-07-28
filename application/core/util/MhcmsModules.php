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

use app\common\model\Modules;

class MhcmsModules
{
    /**
     * @param $module_name
     * @return bool | Modules
     * @throws \think\exception\DbException
     */
    public static function module_exist($module_name)
    {
        static $modules;
        if (!isset($modules[$module_name])) {
            $modules[$module_name] = Modules::get(['module_sign' => $module_name]);
        }
        if ($modules[$module_name]['status'] == 1) {
            return $modules[$module_name];
        } else {
            return false;
        }
    }

    /**
     * @param $module_name
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function get_module_setting($module_name)
    {
        static $module_configs;
        if ($module = self::module_exist($module_name)) {
            if (!isset($module_configs[$module_name])) {
                $module_config = $module->get_site_module_config();
                $module_configs[$module_name] = mhcms_json_decode($module_config['setting']);
            }
            return $module_configs[$module_name];
        } else {
         // die("NO MODULE SETTING" . $module_name);
        }
    }

    /**
     * @param $module_name
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function get_module_global_setting($module_name)
    {
        static $module_configs;
        if ($module = self::module_exist($module_name)) {
            if (!isset($module_configs[$module_name])) {
                $module_global_config = config("module_" . ROUTE_M . "_config");
                $module_configs[$module_name] = $module_global_config['mhcms_config'];;
            }
            return $module_configs[$module_name];
        } else {
        //    die("NO MODULE SETTING");
        }
    }

    /**
     * @param $module_name
     * @return string
     * @throws \think\exception\DbException
     */
    public static function get_module_theme($module_name)
    {
        if ($config = self::get_module_setting($module_name)) {
            if (isset($config['theme']) && !empty($config['theme'])) {
                return $config['theme'];
            }
        }
        //global module
        $module_global_config = self::get_module_global_setting($module_name);
        if (isset($module_global_config['theme']) && !empty($module_global_config['theme'])) {
            return $module_global_config['theme'];
        }
        return self::get_site_theme();
    }


    /**
     * @param $module_name
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function get_device_tpl($module_name)
    {
        global $_W;
        $module_site_config = self::get_module_setting($module_name);
        if (isset($module_site_config['auto_fit_tpl']) && $module_site_config['auto_fit_tpl'] == 1) {
            $_W['DEVICE_TYPE_TPL'] =  $module_site_config['fixed_device'];
        }
    }

    public static function get_site_theme()
    {
        global $_W;
        //site config
        $site_theme = $_W['site']['config']['theme'] ? $_W['site']['config']['theme'] : 'default';
        return $site_theme;
    }
}