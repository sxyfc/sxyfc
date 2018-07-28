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
namespace app\common\util\map;
class TecentMap
{

    public static function render_static($container  , $default = '' , $width = 0 , $height = 0){
        global $_W;

        $ret_str = "";
        if(!defined('TECENT_MAP')){
            $ret_str = $script = '<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp"></script>';
        }

        $default = $default ? $default : $_W['site']['config']['map']['site_coordinate'];
        $value = explode(',' ,$default);
        $map_id = $container;

        $style = '';
        $style .= ';width:' .( $width ? $width : '100%');
        $style .= ';height:' .($height ? $height : '500px');
        $ret_str .= '<div style="'.$style.'" id="'. $map_id.'"></div>';
        $ret_str .= <<<EOF
        <script>
                var center = new qq.maps.LatLng({$value[1]},{$value[0]});
                var map = new qq.maps.Map(document.getElementById('container'),{
                    center: center,
                    zoom: 13
                });
                
        </script>

EOF;



        return $ret_str;
    }
}