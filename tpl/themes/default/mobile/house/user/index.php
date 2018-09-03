<div class="slider_wraper" style="background: rgb(255, 255, 255);height: 150px">
    <div class="blender">
        <div id="index_ad">

            <div class="column  mhcms-member-top">
                <div class="mhcms-member-info  segment">

                    <div class="avatar-wrapper">
                        {if $user.avatar}
                        <img src="{$user.avatar}"
                             class="ui mhcms-avatar image mhcms-small circular ">
                        {else}

                        <img src="/statics/images/logo.png"
                             class="ui mhcms-avatar image mhcms-small circular ">
                        {/if}
                        <i class="ui is-hidden bottom  right attached label circular {if $user.sex=='男'}man{else}woman{/if} icon"></i>
                    </div>

                    <div class="member-nickname has-text-centered">
                        <p>{$user.nickname|default=$user.user_name}</p>
                    </div>
                    <div class="is-clearfix  "></div>

                    <div class="columns is-mobile menu-box  has-text-centered">

                        <div class="column">

                            <a href="/member/wallet/index.html">
                                <i class="iconfont icon-qianbao"></i>
                                <span>钱包</span>
                            </a>
                        </div>

                        <div class="column">
                            <a>
                                <i class="iconfont icon-shoucang"></i>
                                <span>收藏夹</span>
                            </a>
                        </div>


                        <div class="column">
                            <a>
                                <i class="iconfont icon-jifen3"></i>
                                <span>金币: {$user.point}</span>
                            </a>
                        </div>

                        <div class="column">
                            <a>
                                <i class="iconfont icon-daikuan"></i>
                                <span>房宝: {$user.balance}</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<div class="mhcms-panel" style="padding-top: 15px">
    <div class="mhcms-panel-body">
        <div class="columns is-mobile user-btns has-text-centered">
            <div class=" column">
                <a data-mha href="/house/user_content/publish_info">
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-svgmoban06 "></i>
                        <p>我要买房</p>
                    </div>
                </a>

            </div>

            <div class="  column">
                <a data-mha href="/house/user_content/publish_info" data-model_id="resume">
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-chuzu "></i>
                        <p>我要租房</p>
                    </div>
                </a>

            </div>


            <!--            <div class="  column">-->
            <!--                <a href="/house/user/kanfang_log">-->
            <!--                    <div class="weui-cell__bd weui-cell_primary">-->
            <!--                        <i class="iconfont icon-yuyueline"></i>-->
            <!--                        <p>看房记录</p>-->
            <!--                    </div>-->
            <!--                </a>-->
            <!--            </div>-->

            <div class="  column ">
                <a data-mha href="/house/user/myadd_esf">
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-zhaoshangjiameng"></i>
                        <p>收藏房源</p>
                    </div>
                </a>
            </div>

            <div class="  column">
                <a data-mha href="/house/user/myadd_rent">
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="icon-kefu iconfont"></i>
                        <p>收藏租房</p>
                    </div>
                </a>
            </div>


        </div>

        <div class="columns is-mobile user-btns has-text-centered weui-cells">

            <div class="  column">
                <a href="/house/user_content/publish_weituo">
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="icon-woyaomaifang iconfont "></i>
                        <p>我要卖房</p>
                    </div>
                </a>
            </div>

            <div class="  column">
                <a href="/house/user_content/publish_weituo">
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont iconfont icon-woyaochuzu "></i>
                        <p>我要出租</p>
                    </div>
                </a>
            </div>


            <div class="  column  ">
                <a data-mha href="/member/info/verify">
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-renzheng"></i>
                        <p>实名认证</p>
                    </div>
                </a>

            </div>


            <!--                <div class="  column">-->
            <!--                    <a onclick="parent.show_message('暂未开放')">-->
            <!--                        <div class="weui-cell__bd weui-cell_primary">-->
            <!--                            <i class="iconfont icon-ic_pets_px"  ></i>-->
            <!--                            <p>我的足迹</p>-->
            <!--                        </div>-->
            <!--                    </a>-->
            <!--                </div>-->

            <div class="  column">
                <a>
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-qi"></i>
                        <p>投诉管理</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>


<div class="mhcms-panel" style="padding-top: 15px;display: none">
    <div class="weui-panel__hd">我的收藏</div>
    <div class="mhcms-panel-body">
        <div class="columns is-mobile user-btns has-text-centered">

            <div class="  column is-one-fifth">
                <a>
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-qi"></i>
                        <p>二手房</p>
                    </div>
                </a>
            </div>

            <div class="  column is-one-fifth">
                <a>
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-qi"></i>
                        <p>出租房</p>
                    </div>
                </a>
            </div>

            <div class="  column is-one-fifth">
                <a>
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-qi"></i>
                        <p>经纪人</p>
                    </div>
                </a>
            </div>


            <div class="  column is-one-fifth">
                <a>
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-qi"></i>
                        <p>楼盘</p>
                    </div>
                </a>
            </div>


            <div class="  column">
                <a>
                    <div class="weui-cell__bd weui-cell_primary">
                        <i class="iconfont icon-qi"></i>
                        <p>门店</p>
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>

<div class="weui-panel" style="">
    <div class="weui-panel__bd">
        <div class="weui-media-box weui-media-box_small-appmsg">
            <div class="weui-cells">


                <!--                <a class="weui-cell weui-cell_access" href="/member/info/verify">-->
                <!--                    <div class="weui-cell__hd"><i class="iconfont icon-renzheng"-->
                <!--                                                  style="width:20px;margin-right:5px;display:block"></i>-->
                <!--                    </div>-->
                <!--                    <div class="weui-cell__bd weui-cell_primary">-->
                <!--                        <p>申请个人认证</p>-->
                <!--                    </div>-->
                <!--                    <span class="weui-cell__ft"></span>-->
                <!--                </a>-->

                <a class="weui-cell weui-cell_access" href="/member/info/verify?type=company">
                    <div class="weui-cell__hd"><i class=" iconfont icon-renzhengpeizhi"
                                                  style="width:20px;margin-right:5px;display:block"></i>
                    </div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>申请公司认证</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>
            </div>
        </div>
    </div>
</div>

{if !$_W['module_config']['close_esf'] || !$_W['module_config']['close_rent']}
<div class="weui-panel">
    <div class="weui-panel__hd">信息管理</div>
    <div class="weui-panel__bd">
        <div class="weui-media-box weui-media-box_small-appmsg">
            <div class="weui-cells">


                {if !$_W['module_config']['close_esf']}
<!--                <a class="weui-cell weui-cell_access" href="/house/user_content/my_esf_lists">-->
<!--                    <div class="weui-cell__hd"><i class="iconfont icon-xiaoqu"-->
<!--                                                  style="width:20px;margin-right:5px;display:block"></i>-->
<!--                    </div>-->
<!--                    <div class="weui-cell__bd weui-cell_primary">-->
<!--                        <p>二手房管理</p>-->
<!--                    </div>-->
<!--                    <span class="weui-cell__ft"></span>-->
<!--                </a>-->
                {/if}


                {if !$_W['module_config']['close_rent']}
<!--                <a class="weui-cell weui-cell_access" href="/house/user_content/my_rent_lists">-->
<!--                    <div class="weui-cell__hd"><i class="iconfont icon-woyaochuzu"-->
<!--                                                  style="width:20px;margin-right:5px;display:block"></i>-->
<!--                    </div>-->
<!--                    <div class="weui-cell__bd weui-cell_primary">-->
<!--                        <p>出租管理</p>-->
<!--                    </div>-->
<!--                    <span class="weui-cell__ft"></span>-->
<!--                </a>-->
                {/if}


<!--                <a class="weui-cell weui-cell_access" href="/house/user_content/my_info_lists">-->
<!--                    <div class="weui-cell__hd"><i class="iconfont icon-zufang"-->
<!--                                                  style="width:20px;margin-right:5px;display:block"></i>-->
<!--                    </div>-->
<!--                    <div class="weui-cell__bd weui-cell_primary">-->
<!--                        <p>求租求购</p>-->
<!--                    </div>-->
<!--                    <span class="weui-cell__ft"></span>-->
<!--                </a>-->

                <a class="weui-cell weui-cell_access" href="/house/user_orders/index">
                    <div class="weui-cell__hd"><i class="iconfont icon-zufang"
                                                  style="width:20px;margin-right:5px;display:block"></i>
                    </div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>订单记录</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>
            </div>
        </div>
    </div>
</div>
{/if}


{if !$_W['module_config']['close_agent'] && $agent.status==99 && $agent.type==1}
<div class="weui-panel">
    <div class="weui-panel__bd">
        <div class="weui-media-box weui-media-box_small-appmsg">
            <div class="weui-cells">


                <a class="weui-cell weui-cell_access" href="{:url('house/agent_user/index')}">
                    <div class="weui-cell__hd"><img
                                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC4AAAAuCAMAAABgZ9sFAAAAVFBMVEXx8fHMzMzr6+vn5+fv7+/t7e3d3d2+vr7W1tbHx8eysrKdnZ3p6enk5OTR0dG7u7u3t7ejo6PY2Njh4eHf39/T09PExMSvr6+goKCqqqqnp6e4uLgcLY/OAAAAnklEQVRIx+3RSRLDIAxE0QYhAbGZPNu5/z0zrXHiqiz5W72FqhqtVuuXAl3iOV7iPV/iSsAqZa9BS7YOmMXnNNX4TWGxRMn3R6SxRNgy0bzXOW8EBO8SAClsPdB3psqlvG+Lw7ONXg/pTld52BjgSSkA3PV2OOemjIDcZQWgVvONw60q7sIpR38EnHPSMDQ4MjDjLPozhAkGrVbr/z0ANjAF4AcbXmYAAAAASUVORK5CYII="
                                alt="" style="width:20px;margin-right:5px;display:block"></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>经纪合伙人面板</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>

            </div>
        </div>
    </div>
</div>
{/if}


{if $agent.status==99 && $agent.type==2}
<div class="weui-panel">
    <div class="weui-panel__bd">
        <div class="weui-media-box weui-media-box_small-appmsg">
            <div class="weui-cells">

                <a class="weui-cell weui-cell_access" href="/house/employee_user/index">
                    <div class="weui-cell__hd"><img
                                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC4AAAAuCAMAAABgZ9sFAAAAVFBMVEXx8fHMzMzr6+vn5+fv7+/t7e3d3d2+vr7W1tbHx8eysrKdnZ3p6enk5OTR0dG7u7u3t7ejo6PY2Njh4eHf39/T09PExMSvr6+goKCqqqqnp6e4uLgcLY/OAAAAnklEQVRIx+3RSRLDIAxE0QYhAbGZPNu5/z0zrXHiqiz5W72FqhqtVuuXAl3iOV7iPV/iSsAqZa9BS7YOmMXnNNX4TWGxRMn3R6SxRNgy0bzXOW8EBO8SAClsPdB3psqlvG+Lw7ONXg/pTld52BjgSSkA3PV2OOemjIDcZQWgVvONw60q7sIpR38EnHPSMDQ4MjDjLPozhAkGrVbr/z0ANjAF4AcbXmYAAAAASUVORK5CYII="
                                alt="" style="width:20px;margin-right:5px;display:block"></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>进入内部经纪人控制台</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>

            </div>
        </div>
    </div>
</div>
{/if}


<!--<div class="weui-panel">-->
<!--    <div class="weui-panel__hd">我的分销面板</div>-->
<!--    <div class="weui-panel__bd">-->
<!--        <div class="weui-media-box weui-media-box_small-appmsg">-->
<!--            <div class="weui-cells">-->
<!--                <a class="weui-cell weui-cell_access" href="/member/distribute/orders/module/house">-->
<!--                    <div class="weui-cell__hd"><i class="iconfont icon-wodedingdan"-->
<!--                                                  style="width:20px;margin-right:5px;display:block"></i>-->
<!--                    </div>-->
<!--                    <div class="weui-cell__bd weui-cell_primary">-->
<!--                        <p>分销订单</p>-->
<!--                    </div>-->
<!--                    <span class="weui-cell__ft"></span>-->
<!--                </a>-->
<!---->
<!---->
<!--                <a class="weui-cell weui-cell_access" href="/member/distribute/link">-->
<!--                    <div class="weui-cell__hd"><i class="iconfont icon-shangjiaruzhu"-->
<!--                                                  style="width:20px;margin-right:5px;display:block"></i>-->
<!--                    </div>-->
<!--                    <div class="weui-cell__bd weui-cell_primary">-->
<!--                        <p>推广链接</p>-->
<!--                    </div>-->
<!--                    <span class="weui-cell__ft"></span>-->
<!--                </a>-->
<!---->
<!--                <a class="weui-cell weui-cell_access" href="/member/distribute/downline">-->
<!--                    <div class="weui-cell__hd"><i class="iconfont icon-mianshi"-->
<!--                                                  style="width:20px;margin-right:5px;display:block"></i>-->
<!--                    </div>-->
<!--                    <div class="weui-cell__bd weui-cell_primary">-->
<!--                        <p>我的下级</p>-->
<!--                    </div>-->
<!--                    <span class="weui-cell__ft"></span>-->
<!--                </a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->


<div class="weui-cells">

    <a class="weui-cell weui-cell_access" href="{:url('sso/passport/logout')}">
        <div class="weui-cell__hd"><i class="iconfont icon-back"
                                      style="width:20px;margin-right:5px;display:block"></i></div>
        <div class="weui-cell__bd weui-cell_primary">
            <p>注销登录</p>
        </div>
        <span class="weui-cell__ft"></span>
    </a>
</div>

<script>
    require(['jquery', 'mhcms'], function ($, mhcms) {


        $(document).ready(function () {

            //todo set title bar
            // {if is_weixin()}

            mhcms.init_wechat_share({
        :
            json_encode($seo)
        }  ,
            '{$_W.current_url}'
        )
            ;
            // {/if}

            mhcms.init_seo("{$seo.seo_key}", {
        :
            json_encode($seo)
        })
            ;
        })
    });
</script>