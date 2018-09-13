
<!--/**-->
<!-- * Created by PhpStorm.-->
<!-- * User: RoryHe-->
<!-- * Date: 2018/8/7-->
<!-- * Time: 下午3:27-->
<!-- */-->

<!--mhcms.net  content start {php} global $_W; {/php}-->
<div class="weui-panel__ft has-text-centered" id="show_img"><a class=" button is-light is-mobile-loading is-loading"><img src="/statics/images/logo.png" class="loading_icon"></a></div>
<div id="app_mhcms">
    <div class="weui-flex filter has-text-centered" id="filter_list">
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
                    $("#show_img").hide();
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