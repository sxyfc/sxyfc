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
class BaiduMap
{
    public static function baidu_map($field_name, $default_value = '', $width = '100%', $height = '100%')
    {
        $ret_str = "";
        $setting = [];
        if (!defined('BAIDU_MAP')) {
            $setting['baidu_map_apikey'] = $setting['baidu_map_apikey'] ? $setting['baidu_map_apikey'] : "MKqiLv4hVg6G9gU6tIzbR9OBASGBt4zW";
            $ret_str = $script = '<script type="text/javascript" src="//api.map.baidu.com/api?v=2.0&ak=' . $setting['baidu_map_apikey'] . '"></script>';
        }
        $value = $default_value;
        $errortips = " error toi";//$this->fields[$field]['errortips'];
        $tips = $value ? "编辑标志" : "添加标志";
        $map_id = $field_name . '_baidu_map';
        $style = '';
        $style .= ';width:' . $width;
        $style .= ';height:' . $height;
        $ret_str .= '<div style="' . $style . '" id="' . $map_id . '"></div>';
        $ret_str .= ' <input type="hidden" name="data[' . $field_name . ']" value="' . $value . '" id="' . $field_name . '" >';
        $ret_str .= "
       
	";
        return $ret_str;
    }

    public static function render($field_name , $default = '' , $form_group = 'data' , $width = 0 , $height = 0){
        global $_W;

        if($form_group){
            $_form_name = $form_group."[$field_name]";
        }else{
            $_form_name = $field_name;
        }
        $ret_str = "";
        if(!defined('BAIDU_MAP')){
            $baidu_map_apikey = $_W['site']['config']['map']['bd_key'] ? $_W['site']['config']['map']['bd_key'] : $_W['global_config']['map']['bd_key'];
            $ret_str = $script = '<script type="text/javascript" src="//api.map.baidu.com/api?v=2.0&ak='.$baidu_map_apikey.'"></script>';
        }

        $tips =  $default ? "编辑标志" : "添加标志";
        $default = $default ? $default : $_W['site']['config']['map']['site_coordinate'];

        if(!$default){
            $default = "116.404, 39.915";
        }
        $value = explode(',' ,$default);
        $map_id = $field_name.'_baidu_map';

        $style = '';
        $style .= ';width:' .( $width ? $width : '100%');
        $style .= ';height:' .($height ? $height : '500px');
        $ret_str .= '<div style="'.$style.'" id="'. $map_id.'"></div>';

        $ret_str .=" <input type=\"hidden\" name=\"$_form_name\" value=\"$default\" id='$field_name' >";

        $ret_str .="
        <script>
        // 百度地图API功能
    var mk;
    var loadCount = 0;
    var map_input_id = '{$field_name}';
	var map = new BMap.Map(\"$map_id\");
	map.addControl(new BMap.MapTypeControl());   //添加地图类型控件
	var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// 左上角，添加比例尺
	var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
	var top_right_navigation = new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type: BMAP_NAVIGATION_CONTROL_SMALL});
    map.addControl(top_left_control);        
    map.addControl(top_left_navigation);     
    map.addControl(top_right_navigation); 
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
    
	";

        if($default){
            $ret_str  .="var point = new BMap.Point({$value[0]},{$value[1]});
    mk = new BMap.Marker(point, {
            offset : new BMap.Size(10,15)
        });
    
    map.addOverlay(mk);
    map.setCenter(point );
     
	map.centerAndZoom(point,15);
    ";
        }else{
            $ret_str  .="	var geolocation = new BMap.Geolocation();
geolocation.getCurrentPosition(function(r){
if(this.getStatus() == BMAP_STATUS_SUCCESS){
    mk = new BMap.Marker(r.point, {
            offset : new BMap.Size(10,15)
        });
    
    map.addOverlay(mk);
    map.panTo(r.point);
		}
		else {
			alert('自动定位失败'+this.getStatus());
		}        
	},{enableHighAccuracy: true})
	";
        }

        $ret_str  .="
	//单击获取点击的经纬度
	map.addEventListener(\"click\",function(e){
        mk.setPosition(e.point);
	    $('#'+map_input_id).val(e.point.lng + \",\" + e.point.lat);
	});
	map.addEventListener(\"tilesloaded\",function(){  
        if(loadCount == 1){  
            map.setCenter(point);  
        }         
        loadCount = loadCount + 1;  
     });  
	</script>
	
	
	";

        return $ret_str;
    }



    public static function render_static($container  , $default = '' , $width = 0 , $height = 0){
        global $_W;

        $ret_str = "";
        if(!defined('BAIDU_MAP')){
            $baidu_map_apikey = $_W['site']['config']['map']['bd_key'] ?   $_W['site']['config']['map']['bd_key']: 'MKqiLv4hVg6G9gU6tIzbR9OBASGBt4zW';
            $ret_str = $script = '<script type="text/javascript" src="//api.map.baidu.com/api?v=2.0&ak='.$baidu_map_apikey.'"></script>';
        }

        $tips =  $default ? "编辑标志" : "添加标志";
        $default = $default ? $default : $_W['site']['config']['map']['site_coordinate'];

        $value = explode(',' ,$default);
        $map_id = $container;

        $style = '';
        $style .= ';width:' .( $width ? $width : '100%');
        $style .= ';height:' .($height ? $height : '500px');
        $ret_str .= '<div style="'.$style.'" id="'. $map_id.'"></div>';

        $ret_str .="
        <script>
        // 百度地图API功能
    var mk;
    var loadCount = 0; 
	var map = new BMap.Map(\"$map_id\" , {enableMapClick:false});
	map.addControl(new BMap.MapTypeControl());   //添加地图类型控件
	var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// 左上角，添加比例尺
	var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
	var top_right_navigation = new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type: BMAP_NAVIGATION_CONTROL_SMALL});
    map.addControl(top_left_control);        
    map.addControl(top_left_navigation);     
    map.addControl(top_right_navigation); 
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
    
	";

        if($default){
            $ret_str  .="var point = new BMap.Point({$value[0]},{$value[1]});
    mk = new BMap.Marker(point, {
            offset : new BMap.Size(10,15)
        });
    
    //map.addOverlay(mk);
    map.setCenter(point );
     
	map.centerAndZoom(point,15);
    ";
        }else{
            $ret_str  .="
            var geolocation = new BMap.Geolocation();
        geolocation.getCurrentPosition(function(r){
            if(this.getStatus() == BMAP_STATUS_SUCCESS){
                mk = new BMap.Marker(r.point, {
                        offset : new BMap.Size(10,15)
                    }); 
                map.addOverlay(mk);
                map.panTo(r.point);
            }
            else {
                alert('自动定位失败'+this.getStatus());
            }        
        },{enableHighAccuracy: true});
	";
        }
        $ret_str  .="   </script> ";

        return $ret_str;
    }
}