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
namespace app\smallapp\controller;

use app\common\controller\AdminBase;

use app\common\model\Models;
use app\wechat\util\WechatUtility;
use think\Cache;
use think\Config;
if (!defined('API_URL')) {
    define("API_URL", "http://cloud.bao8.org/");
}
class AdminShop extends AdminBase
{
    public function bought(){
        Config::load(CONF_PATH . 'licence.php');
        $res = self::get_mini_apps();

        $app_lists = $res['miniapp_lists'];
        if ($res['code'] != 1) {
            $this->error($res['msg'], null, ['auto' => false]);
        }
        if (count($app_lists) == 0) {
            $app_lists = [];
        }
        $this->view->miniapp_lists = $app_lists;
        return $this->view->fetch();
    }

    public static function get_mini_apps()
    {

        $res = Cache::get("mini_app_list");


        if(time() - $res['last_update'] > 600){
            $url = API_URL . 'product/service/get_mini_apps';
            $licence = config('licence');
            if(!module_exist('sites')){
                $licence['domain'] = $_SERVER['HTTP_HOST'];
            }
            $res = ihttp_request($url, $licence);
            //test($res['content']);
            $res =  json_decode($res['content'], 1);
            $res['last_update'] = SYS_TIME;
            Cache::set("mini_app_list" , $res);
        }

        return $res;
    }


    public static function get_all_mini_apps()
    {

        $all_mini_apps = Cache::get("get_all_mini_apps");

        if(time() - $all_mini_apps['last_update'] > 600){
            $url = API_URL . 'product/service/get_all_mini_apps';
            $licence = config('licence');
            if(!module_exist('sites')){
                $licence['domain'] = $_SERVER['HTTP_HOST'];
            }

            $all_mini_apps = ihttp_request($url, $licence);
            //test($res['content']);
            $all_mini_apps =  json_decode($all_mini_apps['content'], 1);
            $all_mini_apps['last_update'] = SYS_TIME;
            Cache::set("get_all_mini_apps" , $all_mini_apps);
        }

        return $all_mini_apps;
    }


    public function index(){
        Config::load(CONF_PATH . 'licence.php');
        $res = self::get_all_mini_apps();

        $bought_mini_app_list = Cache::get("mini_app_list");
        $bought_mini_app_list = $bought_mini_app_list['miniapp_lists'];


        $app_lists = $res['miniapp_lists'];
        if ($res['code'] != 1) {
            $this->error($res['msg'], null, ['auto' => false]);
        }
        if (count($app_lists) == 0) {
            $app_lists = [];
        }
        $this->view->miniapp_lists = $app_lists;
        $this->view->bought_mini_app_list = $bought_mini_app_list;
        return $this->view->fetch();
    }
}