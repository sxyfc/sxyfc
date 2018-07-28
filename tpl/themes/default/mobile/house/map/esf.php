
<style>
    .app_container{
        padding-bottom: 0;
    }
    #top_nav{
        position: fixed;
        z-index: 9999;
        width: 100%;
        color: #fff;
        line-height: 35px;
        background-color: rgb(255,255,255,0.9);
    }
    .column.active{
        color: #fff;
        background: #0bb20c;
    }
    a.column{
        color: #fff;

        background: #9b9b9b;
    }
    .weui-navbar__item{
        padding: 0;
        font-size: 12px;
    }
</style>

<?php
$baidu_map_apikey = $_W['site']['config']['map']['bd_key'];
$config = [
    "zoom" => 15
];
$map_str = \app\common\util\map\BaiduMap::render_static("baidu_map",   "" , '100%' , '100vh' );
?>
<div id="app_mhcms">

    <div id="top_nav" class="weui-navbar ">
        <a href="/house" class="weui-navbar__item ">返回首页</a>
        <a class="weui-navbar__item" href="{:url('house/map/index')}">新房</a>
        <a class="weui-navbar__item weui-bar__item_on">二手房</a>
        <a class="weui-navbar__item" href="{:url('house/map/rent')}">租房</a>
    </div>

    <div id="container" style="width: 100%;height: 100vh">
        {$map_str}


        <div>

            <div style="padding: 0 15px;margin-bottom: 15px;position: fixed;z-index: 9999;bottom: 0;width: 100%;">
                <div class="field has-addons">

                    <div class="control is-expanded">
                        <input class="input" id="keyword" type="text" v-model="keyword" placeholder="输入小区名字或任何地址来定位">
                    </div>
                    <div class="control">
                        <a class="button is-info" onclick="do_local_search()">
                            Search
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>


    function ComplexCustomOverlay(point, text  ,data){
        this._point = point;
        this._text = text;
        this._data = data;
    }
    ComplexCustomOverlay.prototype = new BMap.Overlay();
    ComplexCustomOverlay.prototype.initialize = function(map){
        this._map = map;var that = this;

        var div = this._div = document.createElement("div");
        div.style.position = "absolute";
        div.style.zIndex = BMap.Overlay.getZIndex(this._point.lat);
        div.style.backgroundColor = "#EE5D5B";
        div.style.border = "1px solid #BC3B3A";
        div.style.color = "white";
        div.style.height = "24px";
        div.style.padding = "2px";
        div.style.lineHeight = "18px";
        div.style.whiteSpace = "nowrap";
        div.style.MozUserSelect = "none";
        div.style.fontSize = "12px"
        var span = this._span = document.createElement("span");
        div.appendChild(span);
        span.appendChild(document.createTextNode(this._text));

        var arrow = this._arrow = document.createElement("div");
        arrow.style.background = "url(http://map.baidu.com/fwmap/upload/r/map/fwmap/static/house/images/label.png) no-repeat";
        arrow.style.position = "absolute";
        arrow.style.width = "11px";
        arrow.style.height = "10px";
        arrow.style.top = "22px";
        arrow.style.left = "10px";
        arrow.style.overflow = "hidden";
        div.appendChild(arrow);
        map.getPanes().labelPane.appendChild(div);

        return div;
    }
    ComplexCustomOverlay.prototype.draw = function(){
        var map = this._map;
        var pixel = map.pointToOverlayPixel(this._point);
        this._div.style.left = pixel.x - parseInt(this._arrow.style.left) + "px";
        this._div.style.top  = pixel.y - 30 + "px";
    }
    ComplexCustomOverlay.prototype.addEventListener = function(event,fun){
        this._div['on'+event] = fun;
    }
    var $model_id = "{$model_id}";
    map.setZoom(14);
    map.addEventListener("moveend", function(){
        var center = map.getCenter();
        var zoom = map.getZoom();
        console.log(zoom + "地图中心点变更为：" + center.lng + ", " + center.lat);

        map.clearOverlays();
        console.log(map.getBounds()	);
        //do_search(center.lng , center.lat , $model_id);
        do_search_screen(map.getBounds() , $model_id);
    });


    function do_local_search() {
        var local = new BMap.LocalSearch(map, {
            renderOptions:{map: map}
        });

        require(['jquery'], function ($) {
            local.search($("#keyword").val());
        });

    }


    function do_search_screen(Bounds , $model_id) {
        var ne = Bounds.getNorthEast() // 东北点
        var sw = Bounds.getSouthWest() // 西南点

        var $url = "{:url('house/service/screen_search')}";
        require(['jquery'], function ($) {
            $.get($url, {
                site_id: "{$_W.site_id}",
                query: {
                    lng1: sw.lng,
                    lat1: sw.lat,
                    lng2: ne.lng,
                    lat2: ne.lat,
                    model_id : $model_id
                }
            }, function (data) {
                //remove all overlays
                if(data.data.length > 0){
                    $.each(data.data , function (index ,  item) {
                        console.log(item , index);
                        var myCompOverlay = new ComplexCustomOverlay(new BMap.Point(item.lng,item.lat), item.loupan_name,item);
                        map.addOverlay(myCompOverlay);
                        myCompOverlay.addEventListener('touchstart',function(){
                            mhcms_frame_work.open_frame(myCompOverlay._data.loupan_name + "二手房列表","/house/loupan/map_esf_list?id="+myCompOverlay._data.id , '100%' , '70vh' , 'b');
                        });
                    });
                    console.log(data);
                }
                //
            }, 'json')

        });
    }
    function do_search(lng, lat , $model_id) {
        var $url = "{:url('house/service/lbs_query')}";
        require(['jquery'], function ($) {
            $.get($url, {
                site_id: "{$_W.site_id}",
                query: {
                    lng: lng,
                    lat: lat,
                    model_id : $model_id
                }
            }, function (data) {
                //remove all overlays


                //
                $.each(data.data , function (index ,  item) {
                    console.log(item , index);
                    var myCompOverlay = new ComplexCustomOverlay(new BMap.Point(item.lng,item.lat), item.loupan_name,"");
                    map.addOverlay(myCompOverlay);

                });
                console.log(data);

            }, 'json')

        });
    }

    require(['jquery']  ,function ($) {
        $(function () {
            $("#house_footer").hide('slow');
            var center = map.getCenter();

            //todo search database
            // add layers
            do_search_screen(map.getBounds()  , $model_id);
            //
        })
    });
</script>
