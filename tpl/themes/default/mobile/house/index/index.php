<?php
if (ROUTE_C == "index" && ROUTE_A == "index" && module_exist('sites')) {
    $target = nb_url(['r' => 'o2o.index.change']);
} else {
    $target = url(ROUTE_M . "/index/index");
}

$logo = $_W['share_img'] = $_W['global_config']['data']['logo'] ? render_file_id($_W['global_config']['data']['logo']) : "/statics/images/logo.png";

$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
$is_ipad = (strpos($agent, 'ipad')) ? true : false;
?>

<div class="mh_header columns is-mobile is-marginless">
    {if module_exist('sites')}
    <a data-mha class="back column is-narrow is-paddingless" href="/sites/site/change?m=house">
        <span style="padding: 0px 0px 0 15px">{$_W['site']['site_name']}</span>
        <i class="angle down iconfont icon-down"></i>
    </a>
    {else}
    <a class="back column is-narrow is-paddingless" href="/house">
        <div style="padding: 0px 0px 0 15px"><img class="image is-rounded is-32x32" src="{$logo}"
                                                  style="    margin-top: 7px;border-radius: 50%">
        </div>
    </a>
    {/if}
    <span class="column is-paddingless" id="ipageTitle" style="font-size: 15px">{$_W['module_config']['system_name']|default=$_W['global_config']['system_name']}</span>

    <span class=" column is-paddingless is-narrow">
        <div class="search_box">

            <a href="/house/search" class="type list iconfont icon-search" id="nav_ico">搜索</a>


        </div>


    </span>

    <span class=" column is-paddingless is-narrow">
            <i class="type list iconfont icon-fenlei" id="nav_ico"></i>

    </span>
</div>

<div id="app_mhcms" v-lazy-container="{ selector: 'img' }">
    <?php
    $ads = render_ad("手机版房产首页");
    ?>
    {if $ads['has_ads']}
    {if $is_ipad}
    <div class="slider_wraper" style="background: #fff; height:382px">
        <div class="blender" style="height:382px">
            {else}
            <div class="slider_wraper" style="background: #fff; ">
                <div class="blender">
                    {/if}

                    <div class="swiper-container swiper-container-horizontal new-better-swiper-container" id="index_ad"
                         style="overflow: hidden; max-height: 382px;">
                        <div class="swiper-wrapper">
                            {foreach $ads as $ad}
                            <?php
                            if ($ad['image'][0]) {
                                $url = $ad['image'][0]['url'];
                            }
                            ?>
                            <div class="swiper-slide  swiper-slide-prev">
                                <div><a href='{$ad[' link']}' ><img :data-src="'{$url}'" class='ui swiper-lazy image' style='width: 100%;max-width: 100%; display: block;'/></a>
                                </div>
                            </div>
                            {/foreach}
                        </div><!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
            {/if}


            <div class="ui mhcms-panel index-views">
                <div class="ui  mhcms-panel-body">
                    <?php
                    $views = set_model('access_logs')->count() + $_W['module_config']['visits'];
                    $views_esf = set_model('house_esf')->count();
                    $views_rent = set_model('house_rent')->count() + $_W['module_config']['infos'];
                    ?>

                    <!--            网站访问统计-->
                    <!--            <span class="is-pulled-right has-text-danger"> <i class="iconfont icon-iconfontdongtai Rotation" style="display: inline-block"></i> 浏览量：{$views} 房源数量：{$views_esf +$views_rent }</span>-->
                </div>
            </div>

            {if $_W.module_config.show_rec_news}
            <div class="is-clearfix bk10"></div>

            <div class="ui mhcms-panel">
                <div class="ui  mhcms-panel-body">
                    <div class="columns is-multiline    is-mobile">
                        <div class="column is-narrow" style="    width: 94px;font-size: 12px;"><i
                                    class="bullhorn icon"></i>新闻头条
                        </div>
                        <?php
                        $items = \app\core\util\ContentTag::category_item(['cate_id' => 0, "model_id" => 'house_news'], null, true, 5, "id desc", ['is_rec' => 1]);
                        ?>
                        <div class="column" style="height: 33px;overflow: hidden">
                            <div class="swiper-container swiper-container-horizontal new-better-swiper-container"
                                 id="index_news" style="">
                                <div class="swiper-wrapper">
                                    {foreach $items as $item}
                                    <a data-mha
                                       href="{:url('house/content/detail' , ['id'=>$item['id'] , 'cate_id'=>$item['old_data']['cate_id']])}"
                                       class="swiper-slide  swiper-slide-prev">
                                        {$item.title}
                                    </a>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            {/if}

            <div class="is-clearfix bk10"></div>

            <?php
            $size = $_W['site']['config']['nave_size'] ? $_W['site']['config']['nave_size'] : 5;
            foreach ($cate_tree as $_cate_tree) {
                if (strpos($_cate_tree['position'], ',1,') === false) {
                    continue;
                } else {
                    $cate_tree_to_chunks[] = $_cate_tree;
                }
            }
            $cate_tree_to_chunks = array_chunk($cate_tree_to_chunks, 10);
            ?>
            <div class="ui index_cate">
                <div class="bk10"></div>
                <div class="ui " style="    padding-top: 5px;margin-bottom: 0;">
                    <div class="swiper-container swiper-container-horizontal new-better-swiper-container"
                         id="index_cate_nav" style="    padding-bottom: 34px;">
                        <div class="swiper-wrapper">
                            {foreach $cate_tree_to_chunks as $_cate_tree}
                            <div class="swiper-slide  swiper-slide-prev ">
                                <div class=" columns is-multiline    is-mobile" style="    margin: 0;">
                                    {foreach $_cate_tree as $nav}
                                    <?php
                                    $_icon = render_file_id($nav['image']);
                                    ?>
                                    <div class="is-one-fifth  column" style="width: 50%;">
                                        <a {$nav.property} class="nav_item" href="{$nav.url}">
                                            {if $nav['image']}
                                            <i style="width: 70px;height: 70px;">
                                                <img class="lazy-img" :data-src="'{$_icon}'"/>
                                            </i>

                                            {else}
                                            <i class="{$nav.icon}"
                                               style="{if $nav.ft_color} color: {$nav.ft_color}; {/if}{if $nav.bg_color}  background: {$nav.bg_color}; {/if}"></i>
                                            {/if}
                                            {$nav.cate_name}
                                        </a>
                                    </div>
                                    {/foreach}
                                </div>
                            </div>
                            {/foreach}
                        </div>
                        <!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
                <div class="is-clearfix"></div>
            </div>


            <?php
            $ads = render_ad("手机版房产首页横排广告");
            ?>
            {if $ads['has_ads']}
            <div class="slider_wraper" style="  background: #fff;">
                <div class="ui  mhcms-panel-body">

                    <div class="swiper-container swiper-container-horizontal new-better-swiper-container"
                         id="index_ad_h" style="overflow: hidden; max-height: 200px;">
                        <div class="swiper-wrapper">
                            {foreach $ads as $ad}
                            <?php
                            if ($ad['image'][0]) {
                                $url = $ad['image'][0]['url'];
                            }
                            ?>
                            <div class="swiper-slide  swiper-slide-prev">
                                <div><a href='{$ad[' link']}' ><img :data-src='"{$url}"'
                                                                    class='ui swiper-lazy image'
                                                                    style='width: 50%;margin: 0 auto;'/></a></div>
                            </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
            {/if}


            <?php
            $ad_videos = render_ad("手机版房产首页视频");;
            ?>

            {if $ad_videos['has_ads']}

            <div class="bk10"></div>
            <div class="video_container" id="ad_video"
                 style="display:block;margin: 0 auto;width: 100%;height: 0px;padding-bottom: 55.2%;overflow: hidden;">

                <div class="pause needsclick"></div>
                <img :data-src="'{$ad_videos.0.image.0.url}'" class="poster" style="min-height:22.7rem;">

                <video id="player" src="{$ad_videos.0.media.0.url}" x5-video-player-type="h5"
                       x5-video-player-fullscreen="true" playsinline="true" webkit-playsinline="true"
                       poster="{$ad_videos.0.image.0.url}" style="width: 100%;height: 100%">
                    <span>您的手机版本，网页版暂未能支持！</span>
                </video>

                <canvas></canvas>
                <div class="video-controls">
                    <button class="video-icon"></button>
                    <div class="video-seek clearfix">

                        <div class="video-seek__container">
                            <div class="video-progress-bar"></div>
                        </div>
                        <div class="video-time">
                            <span class="video-time__current"> 0 </span> <span
                                    class="video-time__plus"> / </span>
                            <span class="video-time__duration"> 0 </span></div>
                    </div>
                    <button class="video-icon full-status normal"></button>
                </div>

            </div>
            {/if}


            <?php

            $rec_loupan = \app\core\util\ContentTag::position_data("推荐楼盘");

            ?>
            {if(count($rec_loupan['items']))}
            <div class="is-clearfix bk10"></div>

            <!--    <div class="ui mhcms-panel">-->
            <!---->
            <!--        <div class="ui  mhcms-panel-header">-->
            <!--            推荐楼盘-->
            <!---->
            <!--            <a class="is-pulled-right" href="{:url('house/loupan/index')}">-->
            <!--                <small class="has-text-grey-light">查看更多楼盘 <i class="icon angle right"></i></small>-->
            <!--            </a>-->
            <!--        </div>-->
            <!--        <div class="ui  mhcms-panel-body">-->
            <!---->
            <!--            <div class="columns is-multiline is-mobile">-->
            <!--                --><?php
            //
            //                $i = 0;
            //                ?>
            <!--                {foreach $rec_loupan['items'] as $item}-->
            <!--                --><?php
            //                $i++;
            //                $_item = \app\common\model\Models::get_item($item['id'], "house_loupan");
            //                $url = $_item['thumb']['0']['url'];
            //                $_W['module_config']['default_thumb'] = '/upload_file/default.jpg';
            //                ?>
            <!--                <a href="{:url('house/loupan/detail' , ['id'=>$item['id'] ])}"-->
            <!--                   class="column is-half {if $i%2 !== 0} even {else} odd {/if}"-->
            <!--                   style="padding:10px 20px">-->
            <!---->
            <!--                    <div class=" image tiny ui"><img src="{$url|default=$_W['module_config']['default_thumb']}">-->
            <!--                    </div>-->
            <!---->
            <!--                    <div class="content">-->
            <!--                        <div style="line-height: 1.9em;font-size: 14px;"-->
            <!--                             class="header mhcms-item-header">{$item.loupan_name}-->
            <!--                        </div>-->
            <!--                        <div class="meta" style="font-size: 10px;-->
            <!--    white-space: nowrap;-->
            <!--    text-overflow: ellipsis;-->
            <!--    overflow: hidden;">-->
            <!--                            <div style="color: #0D9BF2;line-height: 1.5em">{$item.area_id} <span class="has-text-danger is-pulled-right" style="font-size: 14px">均价{$item['price']} 元/平方</span></div>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!---->
            <!--                </a>-->
            <!--                {/foreach}-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            {/if}
            <div class="bk10"></div>

            <!--    {if !$_W['module_config']['close_esf'] || !$_W['module_config']['close_rent']}-->
            {if !true}
            <div class="ui mhcms-panel" id="rent_esf">
                <div class="ui  mhcms-panel-header weui-flex has-text-centered">
                    <div class="weui-navbar__item" @click="show_esf=true" v-bind:class="{ active:show_esf==true }">
                        <span>最新二手房源</span></div>
                    <div class="weui-navbar__item" @click="show_esf=false" v-bind:class="{ active:show_esf==false }">
                        <span>最新出租房源</span></div>
                </div>

                <div class="ui  mhcms-panel-body">
                    <div class="ui mhcms-list unstackable items" v-show="show_esf">
                        <?php
                        $items = \app\core\util\ContentTag::model_data('house_esf', [], 'is_top desc , update_at desc');
                        ?>
                        {foreach $items as $item}
                        <?php
                        $url = $item['thumb']['0']['url'] ? $item['thumb']['0']['url'] : $_W['module_config']['default_thumb'];
                        // check top
                        if (SYS_TIME > strtotime($item['top_expire'])) {
                            set_model('house_esf')->where(['id' => $item['id']])->update(['is_top' => 0]);
                        }
                        ?>
                        <a href="{:url('house/esf/detail' , ['id'=>$item['id']])}"
                           class="ui item mhcms-list-item ">
                            {if $url}
                            <div class=" image tiny ui"><img src="{$url}"></div>
                            {/if}
                            <div class="content">
                                <div class="header" style="    font-size: 14px;"> {if
                                    $item.old_data.is_top==1}
                                    <em class="top">顶</em>
                                    {/if} {$item.title}
                                </div>
                                <div class="extra">
                                    {$item.size}平方 / {$item.direction}

                                </div>
                                <div class="extra">
                                    <small class="has-text-gray">{:format_date($item.update_at)}</small>
                                    <small class="has-text-gray">
                                        浏览 <?php echo($item['hits']['base'] + $item['hits']['views']); ?></small>
                                    {if $item.price}
                                    <span class="mhcms-list-price is-pulled-right"> ￥<strong>{$item.price}</strong><small>万</small></span>
                                    {else}
                                    <span class="mhcms-list-price is-pulled-right"> 价格面议</span>
                                    {/if}
                                </div>
                            </div>
                        </a>
                        {/foreach}
                    </div>
                    <div class="ui mhcms-list unstackable items" v-show="!show_esf">
                        <?php
                        $items = \app\core\util\ContentTag::model_data('house_rent', [], 'is_top desc , update_at desc');
                        ?>
                        {foreach $items as $item}
                        <?php
                        $url = $item['thumb']['0']['url'] ? $item['thumb']['0']['url'] : $_W['module_config']['default_thumb'];
                        // check top
                        if (SYS_TIME > strtotime($item['top_expire'])) {
                            set_model('house_rent')->where(['id' => $item['id']])->update(['is_top' => 0]);
                        }
                        ?>
                        <a href="{:url('house/rent/detail' , ['id'=>$item['id']])}"
                           class="ui item mhcms-list-item ">
                            {if $url}
                            <div class=" image tiny ui"><img src="{$url}"></div>
                            {/if}
                            <div class="content">
                                <div class="header" style="    font-size: 14px;"> {if
                                    $item.old_data.is_top==1}
                                    <em class="top">顶</em>
                                    {/if} {$item.title}
                                </div>
                                <div class="extra">
                                    {$item.size}平方 / {$item.direction}

                                </div>
                                <div class="extra">
                                    <small class="has-text-gray">{:format_date($item.update_at)}</small>
                                    <small class="has-text-gray">
                                        浏览 <?php echo($item['hits']['base'] + $item['hits']['views']); ?></small>

                                    {if $item.price}
                                    <span class="mhcms-list-price is-pulled-right"> ￥{$item.price}元/月</span>
                                    {else}

                                    <span class="mhcms-list-price is-pulled-right"> 价格面议</span>
                                    {/if}
                                </div>
                            </div>
                        </a>
                        {/foreach}
                    </div>
                </div>
            </div>
            {/if}

            {if $_W.module_config.show_agent}
            <div class="bk10"></div>
            <!--    <div class="ui mhcms-panel">-->
            <!--        <div class="ui  mhcms-panel-header" >-->
            <!--            最新入驻经纪人-->
            <!--        </div>-->
            <!--        <div class="ui  mhcms-panel-body">-->
            <!--            <div class="swiper-container swiper-container-horizontal new-better-swiper-container" id="index_agent" >-->
            <!--                <div  class="swiper-wrapper">-->
            <!--                    --><?php
            //                    $items = \app\core\util\ContentTag::model_data('house_agent');
            //                    ?>
            <!--                    {foreach $items as $item}-->
            <!--                    --><?php
            //
            //                    $url = $_item['avatar']['0']['url'];
            //                    $api_imag = url('attachment/image/view_thumb' , ['file_id'=>$item['avatar']['0']['file_id']]);
            //                    ?>
            <!--                    <div class="swiper-slide  swiper-slide-prev agent_slider">-->
            <!--                        <a href="{:url('house/agent/detail' , 'user_id='.$item['user_id'])}" class=" " style="padding: 5px">-->
            <!---->
            <!--                            <img :data-src="'{$api_imag}'">-->
            <!--                        </a>-->
            <!--                        <span>{$item.person_name}</span>-->
            <!--                    </div>-->
            <!--                    {/foreach}-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            {/if}


            {if $_W.module_config.show_news}
            <div class="bk10"></div>

            <div class="ui mhcms-panel">
                <div class="ui  mhcms-panel-header">
                    最新资讯
                </div>
                <div class="ui  mhcms-panel-body">
                    <div class="ui mhcms-list unstackable items">
                        <?php
                        $items = \app\core\util\ContentTag::category_item(['cate_id' => 0, "model_id" => 'house_news']);
                        ?>
                        {foreach $items as $item}
                        <?php
                        $url = $item['thumb']['0']['url'];
                        ?>
                        <a class="ui item mhcms-list-item " data-mha style="    font-size: 14px;"
                           href="{:url('house/content/detail' , ['id'=>$item['id'] , 'cate_id'=>$item['old_data']['cate_id']])}">
                            <div data-mha class=" image tiny ui"><img
                                        src="{$url|default=$_W['module_config']['default_thumb']}">
                            </div>
                            <div class="content">
                                <div class="header">{$item.title}</div>
                                <div class="extra">
                                    <small class="has-text-gray">{:format_date($item.create_at)}</small>
                                    <span class="fly-list-nums"> <i class="talk outline icon"
                                                                    title="浏览"></i> {$item.hits.views} </span>
                                </div>
                            </div>
                        </a>
                        {/foreach}
                    </div>
                </div>

            </div>

            {/if}
        </div>

        <script>


    require(['Vue', 'VueLazyload'], function (Vue, VueLazyload) {
        Vue.use(VueLazyload)
        new Vue({
            el: "#app_mhcms",
            data: {
                show_esf: true
            },
            methods: {}
        });
    });

    require(['jquery', 'swiper4', 'mhcms'], function ($, Swiper, mhcms) {
        $(document).ready(function () {
            var swiper = new Swiper('#index_ad', {
                autoplay: true
            });

            new Swiper('#index_ad_h', {
                autoplay: true,
                slidesPerView: 1
            });

            var swiper = new Swiper('#index_agent', {
                autoplay: true,
                slidesPerView: 4,
                disableOnInteraction: false
            });
            var swiper = new Swiper('#index_news', {
                direction: 'vertical',
                autoplay: true
            });
            var swiper = new Swiper('#index_cate_nav', {
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                }
            });
        });
    });

    alert("平台均为真实房源，虚假房源退还10倍房宝");

</script>


        <style>
            .index_cate {
                z-index: 999;
                position: relative;
            }

            #rent_esf .weui-flex__item {

                color: #ccc;
            }

            #rent_esf .active {
                border-bottom: 1px solid #94c2fe;
                color: #015eb6;
            }

            .index-views {
                line-height: 45px;
                padding: 0 20px;
            }
        </style>


        {block name="footer"}
        {include file="public/footer" /}
        {/block}