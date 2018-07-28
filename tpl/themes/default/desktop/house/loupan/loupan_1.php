{block name="content_header"}
{include file="public/top_nav" /}
{/block}
<!--mhcms.net  content start {php} global $_W; {/php}-->
<style>
    .loupan-nav {
        width: 1200px;
        height: 50px;
        line-height: 50px;
        background: #f3f3f3;
        margin-bottom: 10px;
    }

    .inf_left1 {
        line-height: 50px;
        font-size: 14px;
        clear: both;
        height: 50px;
    }


    #loupan_detail h1 {
        font-size: 14px;
        color: #666;
        height: 42px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    #loupan_detail h1 strong{
        font-size: 30px;
        color: #333;
        margin-right: 15px;
        float: left;
        display: inline;
        line-height: 42px;
    }
    .information_li {
        line-height: 32px;
        font-size: 14px;
        clear: both;
        height: 32px;
        margin: 5px 0;
    }

    .information_li .inf_left .prib {
        font-size: 24px;
        line-height: 30px;
        color: #ff3333;
        font-weight: bold;
        min-width: 78px;
        width: 78px;
        overflow: hidden;
    }
    .information_li .inf_right a {position: relative;
        height: 22px;
        border: solid 1px #fff;
        font-size: 12px;
        line-height: 22px;
        color: #999;
        text-decoration: none;
        padding: 0px 6px 0 4px;
        margin-top: 5px;
        overflow: hidden;
    }

    .fnzoushi01 {
        width: 406px;
        height: auto;
        border: solid 1px #ebebeb;
        background-color: #fff;
        position: absolute;
        top: 30px;
        right: 38px;
        padding: 10px;
        z-index: 1;
    }

    .line_dj {
        height: 37px;
        background: #fff1f1;
        color: #f77a7a;
        font-size: 14px;
        line-height: 37px;
        margin: 13px 0 18px;
        padding-left: 18px;
        position: relative;
    }
    .btn_dj {
        background: #f54f4f;
        text-align: center;
        width: 87px;
        position: absolute;
        right: 0;
        top: 0;
        color: #fff;
        font-size: 15px;
        cursor: pointer;
    }
    .xfdh{    background-color: #ffffff;
        color: #333;
        clear: both;
        padding: 5px 0 15px;
        background-color: #ffffff;
        color: #333;
        clear: both;
        padding: 5px 0 15px;
        border-bottom: solid 1px #ccc;
    }
    .xfdh span.slcdh{font-size: 18px;font-weight: bold}
    .xfdh span{font-size: 24px;font-weight: bold;    vertical-align: middle;}
    .xfdh span.f14 {
        font-size: 14px;
        font-weight: normal;
        line-height: 19px;
    }

    .bigtit {
        background-color: #f8f8f8;
        height: 62px;
        line-height: 62px;
        font-size: 20px;
        color: #000;
        padding-left: 30px;
        border-bottom: 1px solid #e4e4e4;
        font-weight: bold;
    }
</style>
<div class=" mhcms-container" id="loupan_detail">
    <div id="app_mhcms">
        <div class="columns is-mobile is-marginless">
            <nav class="breadcrumb column" aria-label="breadcrumbs" style="    line-height: 35px;">
                <ul>
                    <li><a href="/house">首页</a></li>
                    <li><a href="/house/loupan">新房</a></li>
                    <li class="is-active"><a href="#" aria-current="page">楼盘详情</a></li>
                </ul>
            </nav>


            <div class="column is-narrow "><a class="button is-radiusless is-danger">我是房企,免费入住</a></div>
        </div>


        <!--loupan nav-->
        <nav class="navbar loupan-nav is-transparent">
        <div class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item is-active">
                    楼盘首页
                </a>


                <a class="navbar-item" href="#content_detail">
                    楼盘详情
                </a>



                <a class="navbar-item">
                    小区二手房
                </a>



                <a class="navbar-item">
                    小区租房
                </a>



                <a class="navbar-item">
                    楼盘周边
                </a>



                <a class="navbar-item">
                    装修效果图
                </a>
                {if $detail['vr_link']}
                <a href="{:url('house/loupan/vr_link' , ['id'=>$detail['id']])}"  class="navbar-item"> 全景看房</a>
                {/if}

            </div>

        </div>
        </nav>


        <div class="columns is-mobile">

            <div class="column is-half">
                <div>
                    <div class="swiper-container " id="detail_ad">
                        <div  class="swiper-wrapper">
                            {foreach $detail.thumb as $image}
                            <div class="swiper-slide swiper-slide-prev">
                                <img src="{$image.url}" class="image ui" style="max-height:350px;margin:auto">
                            </div>
                            {/foreach}
                        </div>
                        <!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                    </div>

                </div>

            </div>


            <div class="column is-half">
                <div class=" mhcms-panel" id="intro">
                    <div class="ui  mhcms-panel-header">

                        <div class="inf_left1">
                        <h1><strong>{$detail['loupan_name']}</strong></h1>
                        </div>


                        <div class="information_li mb5 columns">
                            <div class="inf_left fl column is-narrow">

                                <strong style="font-weight: bold;font-size: 14px;">均价：</strong>&nbsp; <span class="prib cn_ff">{$detail['price']} </span>元/m²

                            </div>

                            <div class="inf_right column pr" style="margin-left:4px;">
                                <a style="margin-top:6px;" class="pra01 fl " href="javascript:void(0);">
                                    <i class="lpt_icon1 fl" onmouseover="$('.fnzoushi01').show();" onmouseout="$('.fnzoushi01').hide();"></i><span onmouseover="$('.fnzoushi01').show();" onmouseout="$('.fnzoushi01').hide();">价格说明</span>
                                    <!--价格说明 sta-->
                                    <div class="fnzoushi01" style="display: none;">
                                        <div class="sh"></div>
                                        <p style="word-break: break-all;">预计均价{$detail['price']}元/平方米</p>												<p>报价时间：{:date('Y-m-d')} 价格有效期：14天</p>
                                    </div>

                                    <!--房价说明 end-->
                                </a>
                                <a id="xfdsxq_B04_03" style="margin-top:6px;" class="pra fl " href="#fjzs">
                                    <i class="lpt_icon fl"></i>查看价格走势
                                </a>
                                <a id="xfdsxq_B04_04" class="com fl" href="#computer_fangdai" style="margin-top:6px;">
                                    <i class="lpt_icon fl"></i>贷款计算器
                                </a>
                                <!-- 降价通知我 -->
                                <a id="xfdsxq_B04_05" class="down_pri fl" style="margin-top:6px;padding-right:0;" href="javascript:void(0);" onclick="detail_bodyright.cutPriceNotice(1);"> <i class="lpt_icon fl"></i>降价通知我 </a>
                                <!-- 降价通知我 -->
                            </div>
                        </div>

                        <div class="line_dj">
                            <p>一键咨询更多打折优惠、特价房源</p>
                            <p class="btn_dj" id="xfdsxq_B27_01_01">询底价</p>
                        </div>

                        <div class="xfdh">
                            <div class="advice_left">
                                <p id="phone400">
                                    <span class="slcdh">咨询电话：</span> <span id="shadow_tel">{$detail['sell_phone']}</span>

                                </p>
                            </div>
                        </div>

                        <div class="bk10"></div>

                        <div class="mtags tags" style="padding: 10px 0">
                            <?php
                            $tags = explode("," , $detail['tags']);
                            ?>
                            {foreach $tags as $k=>$tag}
                            <span class="mtag tag_{$k}">{$tag}</span>
                            {/foreach}
                        </div>


                        <div class="information_li ">
                            <div class="inf_left columns" id="xfdsxq_B04_13">
                                <strong class="column is-narrow">主力户型：</strong>
                                <div class="fl zlhx column" >
                                    {foreach $huxings as $huxing}
                                    <a  target="_blank">{$huxing.title}</a>
                                    {/foreach}
                                </div>
                            </div>
                        </div>

                        <div class="information_li ">
                            <div class="inf_left columns" id="xfdsxq_B04_13">
                                <strong class="column is-narrow">项目地址：</strong>
                                <div class="fl zlhx column" >
                                    <a data="89__0"></a>
                                    <a  target="_blank" >{$detail.address}</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



        <div class="columns is-mobile">
            <div class="column">


                <div class="ui top attached mhcms-panel">
                <div class="ui  mhcms-panel-header bigtit">  <h2>楼盘动态</h2>

                    <span class="loupan_price"><a style="float: right" class="pull-right"> </a></span>
                    <?php
                    $items = \app\core\util\ContentTag::category_item(['model_id' => 'house_news' , 'where' => [
                        'loupan_id' => $detail['id']
                    ]]);
                    ?>
                </div>
                <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">
                    <ul class="ui mhcms-list unstackable items">
                        {foreach $items as $item}
                        <li class=" ui item grid" style="padding: 10px;">
                            <a href="{:url('house/content/detail' , ['id'=>$item['id'] , 'cate_id' => $item['old_data']['cate_id'] ])}">{$item.title}</a>
                        </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
            </div>
        </div>


        <div class="columns is-mobile">
            <div class="column">
                <div class="ui top attached mhcms-panel">
                    <div class="ui  mhcms-panel-header bigtit">  <h2>主力户型</h2></div>

                    <div class="ui columns mhcms-panel-body" style="margin-bottom: 10px;padding: 15px;">
                        {foreach $huxings as $huxing}
                        <div class="column is-3">
                            <div class=" img-wrap mhcms-list-pic-item swiper-slide swiper-slide-prev">
                                <img class=" image  ui" src="{$huxing.thumb.0.url}">
                                <span class="mhcms-img-cover">{$huxing.title}</span>
                            </div></div>
                        {/foreach}
                    </div>

                </div>
            </div>
        </div>

        <div class="columns is-mobile" id="content_detail">
            <div class="column">
                <div class="ui top attached mhcms-panel">
                    <div class="ui  mhcms-panel-header bigtit">  <h2>楼盘详情</h2></div>

                    <div class="ui columns mhcms-panel-body" style="margin-bottom: 10px;padding: 15px;">
                        {$detail['content']['contents'][0]}
                    </div>

                </div>
            </div>
        </div>

        <div class="columns is-mobile">
            <div class="column">
                <div class="ui top attached mhcms-panel">
                    <div class="ui  mhcms-panel-header bigtit">  <h2>附近楼盘</h2></div>

                    <div class="ui columns mhcms-panel-body" style="margin-bottom: 10px;padding: 15px;">
                        <?php
                        $i = 0;
                        ?>
                        {foreach $nearby_loupans as $_item}

                        <?php
                        $i++;
                        //$_item = \app\common\model\Models::get_item($item['id'] , "house_loupan");
                        $url  = $_item['thumb']['0']['url'];
                        ?>
                        <a  href="{:url('house/loupan/detail' , ['id'=>$_item['id'] ])}" class="column is-3 {if $i%2 !== 0} even {else} odd {/if}" style="padding: 5px">

                            {if $url}<div  class=" image tiny ui" > <img src="{$url}"  ></div>{/if}
                            <div class="content">
                                <div style="line-height: 1.9em;font-size: 14px;" class="header mhcms-item-header">{$_item.loupan_name}</div>
                                <div class="meta" style="font-size: 10px;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;">
                                    <div style="color: #0D9BF2;line-height: 1.5em">{$_item.area_id}   <span class="has-text-danger is-pulled-right" style="font-size: 14px">均价{$_item['price']} 元/平方</span></div>
                                </div>
                            </div>

                        </a>

                        {/foreach}
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
    require(['jquery' , 'swiper4' , 'mhcms' ] , function ($ , Swiper , mhcms) {
        $(document).ready(function () {
            var swiper = new Swiper('#detail_ad', { });


            mhcms.init_seo("{$seo.seo_key}" , {:json_encode($seo)});
        });
    });
</script>