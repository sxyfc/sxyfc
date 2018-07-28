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

