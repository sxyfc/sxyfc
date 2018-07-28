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

class MhcmsTheme
{
    public static function collect_theme()
    {

        $themes = get_themes_list();
        $model = set_model("theme");
        foreach ($themes as $theme) {
            $insert = [];
            $insert['theme_dir'] = $theme;
            $test = $model->where($insert)->find();
            if (!$test) {
                $insert['theme_name'] = $theme;
                $model->insert($insert);
            }
        }
    }

    public static function get_theme_tpls($module, $theme)
    {

        $ret['desktop'] = $ret['mobile'] = 0;

        $mobile_path = SYS_PATH . 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR
            . 'mobile'  . DIRECTORY_SEPARATOR. $module . DIRECTORY_SEPARATOR  ;


        if (is_dir($mobile_path)) {
            $ret['mobile'] = get_sub_file_names($mobile_path . "content" . DIRECTORY_SEPARATOR);
        }

        $desktop_path = SYS_PATH . 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'desktop'  . DIRECTORY_SEPARATOR. $module . DIRECTORY_SEPARATOR  ;
        if (is_dir($desktop_path)) {
            $ret['desktop'] = get_sub_file_names($desktop_path. "content" . DIRECTORY_SEPARATOR);
        }

        return $ret;

    }

    public static function get_module_themes_list($module){
        $themes = set_model("theme")->select();

        $ret['desktop'] = $ret['mobile'] = 0;

        $new_themes = [];
        foreach ($themes as $theme) {
            $ret = self::get_theme_tpls($module , $theme['theme_dir']);
            if(is_array($ret['desktop']) || is_array($ret['mobile'])){
                $new_themes[]  = $theme;
            }
        }
        return $new_themes;
    }
}