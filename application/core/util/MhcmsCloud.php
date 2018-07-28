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

use think\Cache;
use think\cache\Driver;
if (!defined('API_URL')) {
    define("API_URL", "http://cloud.bao8.org/");
}
/**
 * 文件类型缓存类
 * @author    liu21st <liu21st@gmail.com>
 */
class MhcmsCloud
{
    public static function get_mini_apps()
    {

        $res = Cache::get("mini_app_list");
        if (!$res['miniapp_lists'] || time() - $res['last_update'] > 0) {
            $url = API_URL . 'product/service/get_mini_apps';
            $licence = config('licence');
            if(!module_exist("sites")){
                $licence['domain'] = $_SERVER['HTTP_HOST'];
            }
            $res = ihttp_request($url, $licence);
            $res = json_decode($res['content'], 1);
            $res['last_update'] = SYS_TIME;
            Cache::set("mini_app_list", $res);
        }

        return $res;
    }


    public static function get_mini_app($small_app){
        $res = Cache::get("mini_app_list");
        $url = API_URL . 'product/service/get_mini_app';
        $licence = config('licence');
        $licence['app_info'] = $small_app;
        $res = ihttp_request($url, $licence);


        $res = json_decode($res['content'], 1);

        return $res;
    }
    public static function get_all_mini_apps()
    {

        $all_mini_apps = Cache::get("get_all_mini_apps");

        if (time() - $all_mini_apps['last_update'] > 600) {
            $url = API_URL . 'product/service/get_all_mini_apps';
            $licence = config('licence');
            $licence['domain'] = $_SERVER['HTTP_HOST'];
            $all_mini_apps = ihttp_request($url, $licence);
            //test($res['content']);
            $all_mini_apps = json_decode($all_mini_apps['content'], 1);
            $all_mini_apps['last_update'] = SYS_TIME;
            Cache::set("get_all_mini_apps", $all_mini_apps);
        }

        return $all_mini_apps;
    }


}