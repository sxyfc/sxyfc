<?php

namespace app\common\util\forms;

use think\Cache;

class input extends Form
{


    /** this is text mode output for field
     * @return string
     * @internal param string $value
     */
    public function text($field , $base)
    {
        return Forms::text($field);
    }
    public function color($field , $base)
    {
        return Forms::text($field , 'color');
    }


    public function number($field , $base){

        return Forms::text($field , 'number');
    }
    /** this is text mode output for field
     * @return string
     * @internal param string $value
     */
    public function hidden($field , $base)
    {
        return Forms::text($field);
    }

    /**
     * @return string
     */
    public function textarea($field, $base)
    {
        return Forms::textarea($field);
    }


    public function location($field , $base){
        $str = Forms::text($field);


        if(!defined("IN_MHCMS_ADMIN")){
            $str .= <<<EOF

<script>
    map.plugin('AMap.Geolocation', function() {
        geolocation = new AMap.Geolocation({
            enableHighAccuracy: true,//是否使用高精度定位，默认:true
            timeout: 10000,          //超过10秒后停止定位，默认：无穷大
           // buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
            zoomToAccuracy: true,      //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
            buttonPosition:'RB'
        });
        map.addControl(geolocation);
        geolocation.getCurrentPosition();
        AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息
        AMap.event.addListener(geolocation, 'error', onError);      //返回定位出错信息
    });
    //解析定位结果
    function onComplete(data) {
        lnglatXY = [ data.position.getLng(), data.position.getLat()]; //已知点坐标
        regeocoder(lnglatXY);
    }
    //解析定位错误信息
    function onError(data) {
        $('#$field->field_name').val('定位失败');
    }

    function regeocoder(lnglatXY) {  //逆地理编码
        var geocoder = new AMap.Geocoder({
            radius: 1000,
            extensions: "all"
        });
        geocoder.getAddress(lnglatXY, function(status, result) {
            if (status === 'complete' && result.info === 'OK') {
                geocoder_CallBack(result);
            }
        });
    }
    function geocoder_CallBack(data) {
        var address = data.regeocode.formattedAddress; //返回地址描述
        $("#$field->field_name").val(address);
    }
</script>
EOF;
        }
        return $str;
    }
}