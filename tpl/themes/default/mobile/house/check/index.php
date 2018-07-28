<div id="app_mhcms">
    <div class="weui-navbar" style="position: static">
        <a class="weui-navbar__item{if $controller=='esf'} weui_bar__item_on has-text-danger{/if}" data-mha href="/house/check/index?model_id=esf">
            二手房
        </a>
        <a class="weui-navbar__item{if $controller=='rent'} weui_bar__item_on has-text-danger{/if}" data-mha href="/house/check/index?model_id=rent">
            出租房
        </a>
        <a class="weui-navbar__item{if $controller=='loupan'} weui_bar__item_on has-text-danger{/if}" data-mha href="/house/check/index?model_id=loupan">
            楼盘
        </a>
    </div>


    <div class="infinite weui-panel"  >


        <div class="mhcms-lists weui-panel__bd"  id="index_content">
{if count($lists) > 0}


    {foreach $lists as $item}
            <?php

            $_item = \app\common\model\Models::get_item($item['id'] ,$model_info['id'] )
            ?>

            <div class="mhcms-panel">
                <div class="mhcms-panel-header"> <a class="weui- -box__title" style="margin-bottom: 10px;line-height: 45px" data-mha="" href="/house/{$controller}/detail?id={$item['id']}" > {$item[$model_info['name_key']]}</a></div>

                <div class="mhcms-panel-body" style="    align-self: flex-start;padding:0 10px 10px">



                    <a data-title="您确定通过审核嘛？" data-href="/house/check/pass" mini="confirm" data-model_id="{$model_info.id}" data-id="{$item.id}" class="button is-success is-danger"> 通过审核
                    </a>

                    <a data-title="您确定删除信息嘛？" data-href="/house/check/delete" mini="confirm" data-model_id="{$model_info.id}" data-id="{$item.id}" class="button is-success is-warning"> 删除信息
                    </a>

                    <a onclick="parent.show_message('暂未开放')" data-model_id="{$model_info.id}" data-id="{$item.id}" class="button is-success is-link"> 退稿 </a>

                </div>
            </div>


    {/foreach}
{else}

            <div class="weui-msg">
                <div class="weui-msg__icon-area"><i class="weui-icon-info weui-icon_msg"></i></div>
                <div class="weui-msg__text-area">
                    <p class="weui-msg__desc">暂无信息待审核</p>
                </div>
            </div>

{/if}

        </div>

    </div>

</div>