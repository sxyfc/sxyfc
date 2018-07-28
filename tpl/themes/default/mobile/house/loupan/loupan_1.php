<div id="app_mhcms">
<iframe :class="{'is-hidden' : !org_status}" style="position: fixed; width: 100%;height: 75vh;top:0;left:0;z-index: 1" src="{$detail['vr_link']}"></iframe>
<div class="loupan_wrapper" :class="{org_status : org_status}" @touchmove="touchMove($event)">


    <div class="ui top attached mhcms-panel" id="intro">
        <div class="ui  mhcms-panel-header"><h2>{$detail['loupan_name']}


                {if $detail['vr_link']}
                <a href="{:url('house/loupan/vr_link' , ['id'=>$detail['id']])}"  class="weui-btn weui-btn_mini weui-btn_warn is-pulled-right  label"><i class="icon street view"></i> 全景看房</a>
                {/if}
            </h2>

            <div class="ui column mhcms-panel-body" style="    margin-bottom: 10px;padding-top: 0;">

                <div class=" cells columns is-mobile is-marginless">

                    <div class="column">
                        <div class="text">均价</div>
                        <div class="em">{$detail['price']} 元/平</div>
                    </div>

                    <div class="column">
                        <div class="text">类型</div>
                        <div class="em">{$detail['loupan_type']}</div>
                    </div>

                    <div class="column">
                        <div class="text">地区</div>
                        <div class="em">{$detail['area_id']} </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="weui-flex loupan_nav has-text-centered">
        <div class="weui-flex__item"><a href="#intro" class="placeholder"><i class="icon1"></i><p>详情</p></a></div>
        <div class="weui-flex__item"><a href="#comment" class="placeholder"><i class="icon2"></i><p>问答</p></a></div>
        <div class="weui-flex__item"><a href="{:url('house/loupan/map',['loupan_id'=>$detail['id']])}" class="placeholder"><i class="icon6"></i><p>位置</p></a></div>
        <div class="weui-flex__item"><a href="#huxing" class="placeholder"><i class="icon4"></i><p>户型 </p></a></div>

        <div class="weui-flex__item"><a href="#content" class="placeholder"><i class="icon5"></i><p>介绍</p></a></div>
    </div>
    <div class="ui top attached mhcms-panel" id="intro">

        <div class="ui column mhcms-panel-body" style="    margin-bottom: 10px;padding-top: 0;">





            <div class="columns is-mobile is-marginless is-multiline">

                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">开发商 :</div>
                        <div class="column">{$detail['developer']}</div>
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">电话 :</div>
                        <div class="column">{$detail['sell_phone']}</div>
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">开盘日期 :</div>
                        <div class="column">{$detail['kp_date']}</div>
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">特色 :</div>
                        <div class="column">{$detail['tags']}</div>
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">容积率 :</div>
                        <div class="column">{$detail['rongjilv']}</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">绿化率 :</div>
                        <div class="column">{$detail['lvhualv']}</div>
                    </div>
                </div>


                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">车位数量 :</div>
                        <div class="column">{$detail['car_parks']}</div>
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">占地面积 :</div>
                        <div class="column">{$detail['area_size']} 平方米</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">特色 :</div>
                        <div class="column">{$detail['tags']}</div>
                    </div>
                </div>


                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">地区 :</div>
                        <div class="column">{$detail['area_id']}</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">物业费 :</div>
                        <div class="column">{$detail['property_manage_fee']} 元/平方</div>
                    </div>
                </div>

                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">装修类型 :</div>
                        <div class="column">{$detail['zhuangxiu']}</div>
                    </div>
                </div>

                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">物业公司 :</div>
                        <div class="column">{$detail['property_company']}</div>
                    </div>
                </div>


                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">地址 :</div>
                        <div class="column">{$detail['address']}</div>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <div class="ui top attached mhcms-panel" id="huxing">
        <div class="ui  mhcms-panel-header ">
            <div class="columns is-mobile is-marginless has-text-centered ">
                <h2 :class="{active : huxing_tab}" class="column " @click="huxing_tab=true">主力户型 </h2>

                <h2 :class="{active : !huxing_tab}" class="column" @click="huxing_tab=false">楼盘图片 </h2>
            </div>
        </div>
        <div class="ui column mhcms-panel-body" style="margin-bottom: 10px;padding: 10px">
            <div :class="{'is-hidden' : !huxing_tab}">
                <div class="swiper-container swiper-container-horizontal new-better-swiper-container" id="loupan_huxing">
                    <div  class="swiper-wrapper">
                        {foreach $huxings as $huxing}
                        <div class="  mhcms-list-pic-item swiper-slide swiper-slide-prev" style="height: 100px">
                            <img class=" image  ui" src="{$huxing.thumb.0.url}">
                            <span class="mhcms-img-cover">{$huxing.title}</span>
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
            <div :class="{'is-hidden' : huxing_tab}">


                <div  class="swiper-container swiper-container-horizontal new-better-swiper-container" id="detail_ad">
                    <div  class="swiper-wrapper">

                        {foreach $detail.thumb as $image}
                        <div class="swiper-slide swiper-slide-prev">
                            <img src="{$image.url}" class="image ui">
                        </div>
                        {/foreach}
                    </div><!-- Add Pagination -->
                    <div class="swiper-pagination"></div>

                </div></div>
        </div>
    </div>
    <div class="ui top attached mhcms-panel" id="content">
        <div class="ui  mhcms-panel-header"> <h2>{$detail['loupan_name']} * 项目介绍 </h2>

            <span class="loupan_price"><a style="float: right" class="pull-right"> </a></span>
        </div>
        <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">
            <ul class="ui mhcms-list unstackable items">
                <li class=" ui item grid" v-bind:style="{ height: show_more_content ? 'auto':' 300px'}" style="padding: 10px;overflow: hidden">
                    {$detail['content']['contents'][0]}
                </li>
                <div class="weui-panel__ft" @click="show_more_content = !show_more_content">
                    <a href="javascript:void(0);" class="weui-cell weui-cell_access weui-cell_link has-text-centered">
                        <div class="weui-cell__bd">查看更多 <i class="icon angle down"></i></div>
                    </a>
                </div>
            </ul>
        </div>
    </div>



    {if $detail['old_data']['fanyong_term']}

    <div class="ui top attached mhcms-panel">
        <div class="ui  mhcms-panel-header"> <h2>{$detail['loupan_name']} * 佣金政策 </h2>
        </div>
        <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">
            {if $user_verify.company_verify==99}
            {$detail['fanyong_term']['contents'][0]}

            {else}
            <div class="has-text-centered">

                您尚未通过公司资质认证，无法查看该内容！
                <br />
                <a class="button is-success" href="{:url('/member/info/verify' , ['type'=>'company'])}">点此立刻认证</a>
            </div>

            {/if}
        </div>
    </div>
    {/if}

    <div class="ui top attached mhcms-panel">
        <div class="ui  mhcms-panel-header">  <h2>楼盘动态</h2>

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
    <div id="comment">

        <div v-if="show_ask_form" class="weui-cells weui-cells_form">
            <div class="weui-cell">
                <div class="weui-cell__bd">
                    <textarea class="weui-textarea" v-model="ask_title" placeholder="请输入您的问题 " rows="3"></textarea>
                    <div class="weui-textarea-counter"><span>{{ask_title.length}}</span>/80</div>
                </div>
            </div>
        </div>
    </div>
    <div class="ui top attached mhcms-panel" id="">
        <div class="ui  mhcms-panel-header"> <h2>{$detail['loupan_name']} * 在线咨询

                <span v-if="!show_ask_form" class="has-text-success is-pulled-right" @click="show_ask_form = !show_ask_form"><i class="icon edit"></i>我要提问</span>

                <span style="padding:0 10px" v-if="show_ask_form" class="weui-btn weui-btn_mini weui-btn_primary is-pulled-right" @click="post_ask">立刻提问</span>
            </h2>
        </div>
        <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">

            <ul class="ui mhcms-list unstackable items" style="
    margin: 0 20px;">
                {foreach $asks as $ask}
                <li class=" ui item grid" style="border-bottom: 1px solid #f8f8f8;padding: 20px 0 18px;">
                    <p class="has-text-black" style="font-size: 14px;">问： {$ask.title}</p>
                    <p class="has-text-danger" style="font-size: 16px;">答：{$ask.content}</p>
                </li>
                {/foreach}
            </ul>
        </div>
    </div>

    <div class="ui top attached mhcms-panel" id="">
        <div class="ui  mhcms-panel-header"> <h2> 附近楼盘</h2>
        </div>
        <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">
            <div class="columns is-multiline is-mobile">
                <?php
                $i = 0;
                ?>
                {foreach $nearby_loupans as $_item}

                <?php
                $i++;
                //$_item = \app\common\model\Models::get_item($item['id'] , "house_loupan");
                $url  = $_item['thumb']['0']['url'];
                ?>
                <a  href="{:url('house/loupan/detail' , ['id'=>$_item['id'] ])}" class="column is-half {if $i%2 !== 0} even {else} odd {/if}" style="padding: 5px">

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
<mhui_toast @hide_toast="loading_toast.show=false" :show="loading_toast.show" :type="'loadingToast'"></mhui_toast>

<mhui_toast @hide_toast="message_toast.show=false" :show="message_toast.show" :icon="message_toast.icon" :type="'toast'" :text="message_toast.toast_text"></mhui_toast>

<div class="mhcms-navbar weui-navbar">
    <a class="weui-navbar__item item column layui-bg-blue" href="/house" data-mha>首页</a>
    <a class="weui-navbar__item item column layui-bg-blue" href="#comment">在线提问</a>
    {if $agent}
    <a class="weui-navbar__item item column layui-bg-blue" href="{:url('house/loupan/poster' , ['loupan_id'=>$detail.id])}">推广赚佣金</a>
    {/if}
    <a class="weui-navbar__item item column layui-bg-green" href="{:url('house/appointment/create_loupan' , ['loupan_id'=>$detail.id])}">预约看房</a>
</div>
</div>
<link rel="stylesheet" href="/statics/components/semantic/components/icon.min.css" />


<script>
    require([ 'Vue' , 'axios','vue!mhcms_ui' ] , function (Vue , axios) {
        Vue.prototype.$http = axios;
        new Vue({
            el: "#app_mhcms",
            data: {
                show_more_content : false,
                loading_toast : {
                    show : false
                },
                message_toast : {
                    show : false
                    ,icon : "" ,
                    toast_text : ''
                },
                show_ask_form : false ,
                ask_title : "",
                org_status : true,
                zero_count : 0 ,
                huxing_tab : true
            },
            methods : {
                touchMove : function($e){
                    if(this.scrollTop() === 0){
                        if(this.zero_count === 1){
                            this.org_status = true;
                        }
                        this.zero_count++;
                    }else{
                        this.org_status = false;
                        this.zero_count = 0;
                    }
                },
                post_ask : function () {
                    var that = this;
                    if(this.ask_title.length < 6){
                        that.message_toast.icon = "weui-icon-warn weui-icon_msg";
                        that.message_toast.toast_text = "对不起 提问不得少于六个字！";
                        that.loading_toast.show = false;
                        that.message_toast.show = true;
                        return;
                    }
                    this.loading_toast.show = true;
                    let api_url = api_host + 'house/service/post_ask';
                    this.$http.get(api_url, {
                        params: {
                            site_id: 1, query: {
                                title : that.ask_title ,
                                openid : "{$_W['openid']}" ,
                                loupan_id : "{$detail['id']}"
                            }
                        }
                    }).then(function (ret) {
                        ret = ret.data
                        console.log(ret);
                        if(ret.code !==1){
                            that.message_toast.icon = "weui-icon-warn weui-icon_msg";
                            that.message_toast.toast_text = ret.msg;
                            that.loading_toast.show = false;
                            that.message_toast.show = true;
                        }else{
                            that.message_toast.icon = "weui-icon-success weui-icon_msg";
                            that.message_toast.toast_text = "感谢您的关注 我们会尽快处理 您会将会到一个微信消息提示！";
                            that.loading_toast.show = false;
                            that.message_toast.show = true;
                            that.show_ask_form= false;
                        }
                    }, function (error) {
                        // failure
                        console.log(error);
                    });

                },

                //获取页面顶部被卷起来的高度
                scrollTop() {
                    return Math.max(
                        //chrome
                        document.body.scrollTop,
                        //firefox/IE
                        document.documentElement.scrollTop);
                },
                //获取页面文档的总高度
                documentHeight() {
                    //现代浏览器（IE9+和其他浏览器）和IE8的document.body.scrollHeight和document.documentElement.scrollHeight都可以
                    return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
                },
                //获取页面浏览器视口的高度
                windowHeight() {
                    //document.compatMode有两个取值。BackCompat：标准兼容模式关闭。CSS1Compat：标准兼容模式开启。
                    return (document.compatMode == "CSS1Compat") ?
                        document.documentElement.clientHeight :
                        document.body.clientHeight;
                }
            }
            ,
            created : function(){
                var that = this;

                require(['jquery' , 'swiper4' ] , function ($ , Swiper) {
                    $(document).ready(function () {
                        var swiper1 = new Swiper('#loupan_huxing', {
                            slidesPerView : 3,spaceBetween : 20,
                        });
                        var swiper2 = new Swiper('#detail_ad', {
                            slidesPerView : 3
                            ,spaceBetween : 20
                            ,observer:true
                            ,observeParents:true,
                        });
                    });
                });
            }
        });
    });

    require([ 'mhcms'], function (mhcms) {
        //todo set title bar
        // {if is_weixin()}


        mhcms.init_wechat_share( {:json_encode($seo)}  , '{$_W.current_url}');
        // {/if}

        mhcms.init_seo("{$seo.seo_key}" , {:json_encode($seo)});
    });
</script>


<style>
    .loupan_wrapper{transition: all 1000ms;
        z-index: 2;overflow: hidden;background: #f5f6fa;position: relative;
    }
    .org_status{
        position: relative;
        z-index: 2;
        margin: 71vh 10px 0 ;
        overflow: hidden;
        border-top-left-radius: 25px;
        border-top-right-radius: 25px;
    }

    .topFocus a {
        display: block;
        -webkit-tap-highlight-color: transparent;
    }
    .column{
        color: #0C0C0C;
        font-size: 1.4rem;
        position: relative;
        line-height: 2.3rem;
    }
    .column.is-narrow{
        color: #999;
    }
    .column.is-full,.column.is-half{
        padding: 0;

    }
    #house_footer{
        display: none;
    }
    .column .em{
        color: orangered;
        font-size: 15px;
    }

    .column .text{
        font-size: 12px;
    }
    .loading_icon {
        width: 50rpx;
        height: 50rpx;
        border-radius: 50%;
        margin-top: 6rpx;
        margin-left: 5rpx;
        opacity: 0.5;
    }
    .button.is-loading {
        font-size: 33px;
        padding: 0;
        line-height: 45px;
    }
    .weui-mask, .weui-mask_transparent {
        background: #000;
        opacity: 0.6;
    }
    .column.active{
        border-bottom: solid 2px #0D9BF2!important;
    }
</style>