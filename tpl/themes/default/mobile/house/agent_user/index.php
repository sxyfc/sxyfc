<div class="slider_wraper" style="background: rgb(255, 255, 255);height: 150px">
    <div class="blender">
        <div id="index_ad">

            <div class="column  mhcms-member-top">
                <div class="mhcms-member-info  segment">

                    <div class="avatar-wrapper">
                        {if $user.avatar}
                        <img src="{$user.avatar}" class="ui mhcms-avatar image mhcms-small circular ">
                        {else}

                        <img src="/statics/images/logo.png" class="ui mhcms-avatar image mhcms-small circular ">
                        {/if}
                        <i class="ui is-hidden bottom  right attached label circular {if $user.sex=='男'}man{else}woman{/if} icon"></i>
                    </div>

                    <div class="member-nickname has-text-centered">
                        <p>{$user.nickname|default=$user.user_name}</p>
                    </div>
                    <div class="is-clearfix  "></div>

                    <div class="columns is-mobile menu-box  has-text-centered">

                        <div class="column">

                            <a  href="/member/wallet/index.html">
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
                                <span>{$_W['site']['config']['trade']['point_text']}: {$user.point}</span>
                            </a>
                        </div>

                        <div class="column">
                            <a>
                                <i class="iconfont icon-daikuan"></i>
                                <span>{$_W['site']['config']['trade']['balance_text']}: {$user.balance}</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
{if !$_W['module_config']['close_agent']}
<div class="weui-panel">
    <div class="weui-panel__hd">经纪合伙人面板</div>
    <div class="weui-panel__bd">
        <div class="weui-media-box weui-media-box_small-appmsg">
            <div class="weui-cells">


                {if $agent.status==99 && $agent.type==1}
                <a class="weui-cell weui-cell_access" href="{:url('house/user_orders/create')}">
                    <div class="weui-cell__hd"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC4AAAAuCAMAAABgZ9sFAAAAVFBMVEXx8fHMzMzr6+vn5+fv7+/t7e3d3d2+vr7W1tbHx8eysrKdnZ3p6enk5OTR0dG7u7u3t7ejo6PY2Njh4eHf39/T09PExMSvr6+goKCqqqqnp6e4uLgcLY/OAAAAnklEQVRIx+3RSRLDIAxE0QYhAbGZPNu5/z0zrXHiqiz5W72FqhqtVuuXAl3iOV7iPV/iSsAqZa9BS7YOmMXnNNX4TWGxRMn3R6SxRNgy0bzXOW8EBO8SAClsPdB3psqlvG+Lw7ONXg/pTld52BjgSSkA3PV2OOemjIDcZQWgVvONw60q7sIpR38EnHPSMDQ4MjDjLPozhAkGrVbr/z0ANjAF4AcbXmYAAAAASUVORK5CYII=" alt="" style="width:20px;margin-right:5px;display:block"></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>推荐客户</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>

                <a class="weui-cell weui-cell_access" href="/house/user_orders/index/status/0.html">
                    <div class="weui-cell__hd"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC4AAAAuCAMAAABgZ9sFAAAAVFBMVEXx8fHMzMzr6+vn5+fv7+/t7e3d3d2+vr7W1tbHx8eysrKdnZ3p6enk5OTR0dG7u7u3t7ejo6PY2Njh4eHf39/T09PExMSvr6+goKCqqqqnp6e4uLgcLY/OAAAAnklEQVRIx+3RSRLDIAxE0QYhAbGZPNu5/z0zrXHiqiz5W72FqhqtVuuXAl3iOV7iPV/iSsAqZa9BS7YOmMXnNNX4TWGxRMn3R6SxRNgy0bzXOW8EBO8SAClsPdB3psqlvG+Lw7ONXg/pTld52BjgSSkA3PV2OOemjIDcZQWgVvONw60q7sIpR38EnHPSMDQ4MjDjLPozhAkGrVbr/z0ANjAF4AcbXmYAAAAASUVORK5CYII=" alt="" style="width:20px;margin-right:5px;display:block"></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>订单管理</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>

                {else}
                <a data-mha class="weui-cell weui-cell_access"  href="{:url('house/agent/apply')}">
                    <div class="weui-cell__hd"><i class="iconfont icon-jingjiren"   style="width:20px;margin-right:5px;display:block"></i></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>您还不是经纪合伙人 点击立刻申请</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>
                {/if}
            </div>
        </div>
    </div>
</div>
{/if}