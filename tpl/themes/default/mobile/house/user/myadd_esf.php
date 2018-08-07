
<!--/**-->
<!-- * Created by PhpStorm.-->
<!-- * User: RoryHe-->
<!-- * Date: 2018/8/7-->
<!-- * Time: 下午3:27-->
<!-- */-->

<!--mhcms.net  content start {php} global $_W; {/php}-->
<div id="app_mhcms">
    <div class="weui-flex filter has-text-centered" id="filter_list">
        <div class="weui-flex__item" onclick="toggle_filter('area')">
            <div class="placeholder" id="area" data-val="请选择">地区 <i class="iconfont icon-dropdown"></i></div>
        </div>
        <div class="weui-flex__item" onclick="toggle_filter('loupan_type')">
            <div class="placeholder">类型 <i class="iconfont icon-dropdown"></i></div>
        </div>
        <div class="weui-flex__item" onclick="toggle_filter('price')" >
            <div class="placeholder">价格 <i class="iconfont icon-dropdown"></i></div>
        </div>
        <div class="weui-flex__item"  onclick="toggle_filter('tags')" >
            <div class="placeholder">特色 <i class="iconfont icon-dropdown"></i></div>
        </div>


        <div class="weui-flex__item"  onclick="toggle_filter('zhuangxiu')" >
            <div class="placeholder">装修 <i class="iconfont icon-dropdown"></i></div>
        </div>


    </div>
    <div style="margin-top: -10px;">
        <div class="is-marginless columns filter_panel is-mobile mhcms_simple_tab" id="filter_area" style="display: none">
            <div class="column is-marginless is-paddingless " style="background-color: rgb(255, 255, 255);">
                <div  style="height: 50vh;">
                    <ul class="menu-list tab-header weui-cells weui-cells_checkbox is-marginless">
                        <li class="change_options" data-field_name="area_id"  data-value="0"><a>不限</a></li>
                        {foreach $areas as $area}
                        <li data-target="{$area.id}" data-cate_id="top_cate.id"><a>{$area.area_name}</a></li>
                        {/foreach}
                    </ul>
                </div>
            </div>
            <div class="column is-marginless is-paddingless" style="height: 50vh;background: #fff">
                {foreach $areas as $area}
                <ul  class="group_{$area.id} area menu-list tab-body weui-cells weui-cells_checkbox is-marginless" style="display: none">
                    <li class="change_options" data-field_name="area_id"  data-value="{$area.id}"><a>不限</a></li>
                    {if $area.children}
                    {foreach $area.children as $_sub}
                    <li class="change_options" data-field_name="area_id" data-value="{$_sub.id}"><a class="">{$_sub.area_name}</a></li>
                    {/foreach}
                    {/if}
                </ul>
                {/foreach}
            </div>
            <div class="is-clearfix"></div>
            <div class="modal-background" style="z-index: -1;top:50px; height: 100%; position: fixed; opacity: 0.5;"></div>

        </div>


        <div class="is-marginless columns filter_panel is-mobile " id="filter_loupan_type" style="display: none">

            <div class="column is-marginless is-paddingless" style="height: 50vh;background: #fff">
                <ul  class="group_0 area menu-list tab-body weui-cells weui-cells_checkbox is-marginless"  >
                    <li class="change_options" data-field_name="loupan_type"  data-value="0"><a>不限</a></li>

                    {foreach $loupan_type_options  as $loupan_type}
                    <li class="change_options" data-field_name="loupan_type" data-value="{$loupan_type.id}"><a class="">{$loupan_type.name}</a></li>
                    {/foreach}
                </ul>
            </div>
            <div class="is-clearfix"></div>
            <div class="modal-background" style="z-index: -1;top:50px; height: 100%; position: fixed; opacity: 0.5;"></div>

        </div>

        <div class="is-marginless columns filter_panel is-mobile " id="filter_price" style="display: none">

            <div class="column is-marginless is-paddingless" style="height: 50vh;background: #fff">
                <ul  class="group_0 area menu-list tab-body weui-cells weui-cells_checkbox is-marginless"  >
                    <li class="change_options" data-field_name="price_qujian"  data-value="0"><a>不限</a></li>

                    {foreach $price_options  as $price}
                    <li class="change_options" data-field_name="price_qujian" data-value="{$price.id}"><a class="">{$price.name}</a></li>
                    {/foreach}
                </ul>
            </div>
            <div class="is-clearfix"></div>
            <div class="modal-background" style="z-index: -1;top:50px; height: 100%; position: fixed; opacity: 0.5;"></div>

        </div>

        <div class="is-marginless columns filter_panel is-mobile " id="filter_tags" style="display: none">

            <div class="column is-marginless is-paddingless" style="height: 50vh;background: #fff">
                <ul  class="group_0 area menu-list tab-body weui-cells weui-cells_checkbox is-marginless"  >
                    <li class="change_options" data-field_name="tags"  data-value="0"><a>不限</a></li>

                    {foreach $tags_options  as $tag}
                    <li class="change_options" data-field_name="tags" data-value="{$tag.id}"><a class="">{$tag.name}</a></li>
                    {/foreach}
                </ul>
            </div>
            <div class="is-clearfix"></div>
            <div class="modal-background" style="z-index: -1;top:50px; height: 100%; position: fixed; opacity: 0.5;"></div>

        </div>
        <div class="is-marginless columns filter_panel is-mobile " id="filter_zhuangxiu" style="display: none">

            <div class="column is-marginless is-paddingless" style="height: 50vh;background: #fff">
                <ul  class="group_0 area menu-list tab-body weui-cells weui-cells_checkbox is-marginless"  >
                    <li class="change_options" data-field_name="zhuangxiu"  data-value="0"><a>不限</a></li>

                    {foreach $zhuangxiu_options  as $zhuangxiu}
                    <li class="change_options" data-field_name="zhuangxiu" data-value="{$zhuangxiu.id}"><a class="">{$zhuangxiu.name}</a></li>
                    {/foreach}
                </ul>
            </div>
            <div class="is-clearfix"></div>
            <div class="modal-background" style="z-index: -1;top:50px; height: 100%; position: fixed; opacity: 0.5;"></div>

        </div>

    </div>

    <div class="infinite weui-panel"  >


        <div class="mhcms-lists weui-panel__bd"  id="index_content"></div>

    </div>

    <div class="weui-panel__ft has-text-centered" style="display: none">
        <a class=" button is-light is-mobile-loading is-loading">
            <img src="/statics/images/logo.png" class="loading_icon">
        </a>
    </div>

    <div class="weui-loadmore weui-loadmore_line" id="no_data" style="display: none">
        <span class="weui-loadmore__tips">暂无数据</span>
    </div>

    <input type="hidden" id="page" value="1" >

    <div style="height: 45px"></div>
</div>

<script>



    function toggle_filter(filter_name){
        require(['jquery' , 'mhcms' , 'weui'  ] , function ($ , mhcms , weui ) {
            if($("#filter_" + filter_name).is(":visible")){
                var $show = false;
            }else{
                var $show = true;
            }

            $(".filter_panel").hide();



            if($show){
                $("#filter_" + filter_name).show();
            }else{
                $("#filter_" + filter_name).hide();
            }


        });
    }

    require(['jquery' , 'mhcms' , 'weui'  ] , function ($ , mhcms , weui ) {

        var pager = $("#page");
        var $options = {
            has_more : true,
            site_id : "{$_W.site.id}" ,
            page : parseInt( pager.val()) ,
            query : {
            },
            _f : 'html'
        };
        var action = "{:url('house/service/list_esf_resource')}";

        var loader =new mhcms.list_loader($options, action);
        loader.options.container = "#index_content";

        function do_load(init){
            loader.options.is_loading = true;
            loader.load_item_list(init , function (data) {
                if(init===1){
                    $("#index_content").html("");
                }

                if(data.html!==""){
                    pager.val(parseInt( pager.val()) + 1);
                    $("#index_content").append(data.html);
                }else{
                    loader.options.has_more = false;
                    $("#no_data").show();
                }
                loader.options.is_loading = false;
                $("#load_more").hide();
            });
        }

        function change_options(field_name , value) {
            pager.val(1)
            loader.options.query[field_name] = value
            loader.options.page = 1;
            loader.options.has_more = true;
            do_load(1);
        }


        $(".change_options").each(function () {
            $(this).click(function () {
                var field_name = $(this).data('field_name');
                var value = $(this).data('value');
                change_options(field_name , value);
                $('.filter_panel').hide();
            });
        });

        if($("#index_content").html()===""){
            do_load(1);
        }

        $(document.body).infinite().on("infinite", function() {
            if($('.infinite').length === 0){
                $(document.body).destroyInfinite();
            }

            if(loader.options.is_loading || !loader.options.has_more) return;
            $("#load_more").show();
            loader.options.page =  parseInt(pager.val());
            do_load(0);
        });
    });

    //
    require([ 'mhcms'], function (mhcms) {
        mhcms.mhcms_simple_tab('.mhcms_simple_tab');

        //todo set title bar
        // {if is_weixin()}


        mhcms.init_wechat_share( {:json_encode($seo)}  , '{$_W.current_url}');
        // {/if}

        mhcms.init_seo("{$seo.seo_key}" , {:json_encode($seo)});
    });
</script>