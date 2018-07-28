{block name="content_header"}
{include file="public/top_nav" /}
{/block}

<div class=" mhcms-container">
<div class="weui-panel">
    <div class="weui-panel__hd">请选择发布信息分类</div>
    <div class="weui-panel__bd">
        <div class="weui-media-box weui-media-box_small-appmsg">
            <div class="weui-cells">

                {if !$_W['module_config']['close_esf']}

                <a class="weui-cell weui-cell_access" href="/house/user_content/publish_esf">
                    <div class="weui-cell__hd"><i class="iconfont icon-fabu1"></i></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>发布二手房</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>
                {/if}


                <a class="weui-cell weui-cell_access" href="/house/user_content/publish_rent">
                    <div class="weui-cell__hd"><i class="iconfont icon-fabu1"></i></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>发布出租</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>


                <a class="weui-cell weui-cell_access" href="/house/user_content/publish_info">
                    <div class="weui-cell__hd"><i class="iconfont icon-fabu1"></i></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>发布求租/求购</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>


                <a class="weui-cell weui-cell_access" href="/house/user_content/publish_loupan">
                    <div class="weui-cell__hd"><i class="iconfont icon-fabu1"></i></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>录入楼盘</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>


            </div>
            {if !$_W['module_config']['close_agent']}
            <div class="weui-panel__hd">温馨提示 您可以通过经纪人申请来提高免费发布次数</div>
            <div class="weui-cells">
                <a class="weui-cell weui-cell_access" href="/house/agent/apply">
                    <div class="weui-cell__hd"><i class="iconfont icon-fabu1"></i></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p>立刻升级经纪人VIP特权</p>
                    </div>
                    <span class="weui-cell__ft"></span>
                </a>
            </div>
            {/if}

        </div>
    </div>

</div>


</div>