<?php

namespace app\wechat\util;


use app\common\model\Users;
use think\Cache;
use think\Loader;
use think\Log;

class WechatUtility
{

    public static function create_wechat($wechat_id)
    {
        return;
        $_prefix = config('database.prefix');
        $sql = "CREATE TABLE IF NOT EXISTS `{$_prefix}sites_wechat_fans_{$wechat_id}` (
                `id` varchar(255) NOT NULL COMMENT 'openid',
                `user_id` int(10) unsigned NOT NULL COMMENT '会员id',
                `avatar` varchar(255) NOT NULL COMMENT '微信头像',
                `nickname` varchar(255) NOT NULL,
                `subscribe` tinyint(1) unsigned NOT NULL COMMENT '是否订阅',
                `province` varchar(20) NOT NULL,
                `city` varchar(20) NOT NULL,
                `country` varchar(20) NOT NULL,
                `gender` tinyint(1) unsigned NOT NULL,
                `follow_time` datetime NOT NULL COMMENT '首次访问事件',
                `group_ids` varchar(255) NOT NULL COMMENT '用户分组',
                `last_active` INT(10) UNSIGNED NOT NULL ,
                `last_reply` INT(10) UNSIGNED NOT NULL , 
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        sql_execute($sql);
    }

    public static function create_wechat_smallapp($smallapp_id)
    {
        $_prefix = config('database.prefix');
        $sql = "CREATE TABLE `{$_prefix}sites_smallapp_fans_{$smallapp_id}` (
                `id` varchar(255) NOT NULL COMMENT 'openid',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员id',
  `avatar` varchar(255) NOT NULL COMMENT '微信头像',
  `nickname` varchar(255) NOT NULL,
  `subscribe` tinyint(1) unsigned NOT NULL COMMENT '是否订阅',
  `province` varchar(20) NOT NULL,
  `city` varchar(20) NOT NULL,
  `country` varchar(20) NOT NULL,
  `gender` tinyint(1) unsigned NOT NULL,
  `follow_time` datetime NOT NULL COMMENT '首次访问事件',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        sql_execute($sql);
    }

    /**
     * @param $keyword
     * @param $module
     * @param int $keyword_type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function create_keyword($keyword, $module, $old_keyword = "", $keyword_type = 1)
    {
        global $_W;
        $model = set_model('sites_wechat_keyword');
        $where = [];
        $where['keyword'] = $keyword;
        $where['sites_wechat_id'] = $_W['account']['id'];
        // test($where);
        $keyword_found = $model->where($where)->find();

        if ($keyword_found) {
            $code = 0;
            $msg = "该关键字已经存在";
        } else {
            $code = 1;
            $insert = [];
            $insert['keyword'] = $keyword;
            $insert['module'] = $module;
            $insert['action'] = 1;
            $insert['keyword_type'] = $keyword_type;
            $insert['sites_wechat_id'] = $_W['account']['id'];
            $model->insert($insert);
            if ($old_keyword) {
                $where = [];
                $where['keyword'] = $old_keyword;
                $where['sites_wechat_id'] = $_W['account']['id'];
                $model->where($where)->delete();
            }
        }
        return ['code' => $code, 'msg' => $msg];
    }

    /**
     * @param $module
     * @return WechatProcessor
     */
    public static function create_module_processor($module)
    {
        $module_class = Loader::parseName($module, 1);
        $classname = "\\app\\{$module}\\mhcms_classes\\{$module_class}Processor";
        if (!class_exists($classname)) {
            trigger_error($classname . ' Definition File Not Found ', E_USER_WARNING);
        }
        return new $classname();
    }


    /**
     * @param $fan  from db
     * @param $fans from wechat
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function process_update_fans($fan, $fans)
    {

        global $_W;
        $insert = [];
        if ($fan['user_id']) {
            $user = Users::get($fan['user_id']);
            if (!$user) {
                $fan['user_id'] = 0;
            }
        }
        if (!$fan['user_id']) {
            if ($fans['unionid']) {
                $where['wechat_unionid'] = $fans['unionid'];
                $user = Users::where($where)->find();
            }

            if (!isset($user) || !$user) {
                $where['user_name'] = $fans['openid'];
                $user = Users::where($where)->find();
            }

            if (!isset($user) || !$user) {
                if ($fans['unionid']) {
                    $data['wechat_unionid'] =   $fans['unionid'];
                }
                $data['user_name'] = $fans['openid'];
                $user = Users::create_connect_user($data);
            }

            $insert['user_id'] = $user['id'];
            if ($_W['login_scan']) {
                Cache::set("mhcms_wechat_subscribe_login:" . $_W['uuid'], $user['id']);
            }

        }
        $insert['subscribe'] = $fans['subscribe'];
        $insert['openid'] = $fans['openid'];
        $insert['last_active'] = time();
        $insert['site_id'] = $_W['site']['id'];

        $_W['wechat_fans_model']->where(['openid' => $fans['openid']])->update($insert);
        $_W['fans'] = array_merge($fan, $insert);
    }


    public static function process_create_fans($fans)
    {

        if ($fans['openid']) {
            global $_W;
            $insert = [];

            $insert['subscribe'] = $fans['subscribe'];
            $insert['openid'] = $fans['openid'];
            $insert['gender'] = $fans['sex'];
            $insert['province'] = $fans['province'];
            $insert['country'] = $fans['country'];
            $insert['avatar'] = $fans['headimgurl'];
            $insert['city'] = $fans['city'];
            $insert['site_id'] = $_W['site']['id'];
            $insert['follow_time'] = date("Y-m-d H:i:s", $fans['subscribe_time']);
            $insert['last_active'] = time();
            if (count($fans['tagid_list'])) {
                $fan['group_ids'] = "," . join(',', $fans['tagid_list']) . ",";;
            }
            $_W['wechat_fans_model']->insert($insert);
            $_W['fans'] = $insert;
        }
    }


    public static function logging($level = 'info', $message = '')
    {
        $filename = SYS_PATH . 'logs/wechat_' . date('Ymd') . '.log';
        mkdirs(dirname($filename));
        $content = date('Y-m-d H:i:s') . " {$level} :\n------------\n";
        if (is_string($message) && !in_array($message, array('post', 'get'))) {
            $content .= "String:\n{$message}\n";
        }
        if(is_array($message)) {
            $content .= "Array:\n";
            foreach($message as $key => $value) {
                $content .= sprintf("%s : %s ;\n", $key, $value);
            }
        }
        if ($message === 'get') {
            $content .= "GET:\n";
            foreach ($_GET as $key => $value) {
                $content .= sprintf("%s : %s ;\n", $key, $value);
            }
        }
        if ($message === 'post') {
            $content .= "POST:\n";
            foreach ($_POST as $key => $value) {
                $content .= sprintf("%s : %s ;\n", $key, $value);
            }
        }
        $content .= "\n";

        $fp = fopen($filename, 'a+');
        fwrite($fp, $content);
        fclose($fp);
    }


}