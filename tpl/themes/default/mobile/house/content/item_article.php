
<div class="wechat_article top attached mhcms-panel">
    <h1 class="rich_media_title" >{$detail['title']}</h1>


    <div class="mhcms-detail-info">
        <a class="account_name" {if $_W['site_wechat']['followurl']} href="{$_W['site_wechat']['followurl']}" {/if}>{$_W['site_wechat']['account_name']}</a>


        <span class="datetime">{:explode(" " ,$detail['create_at'])[0]}</span>

    </div>

    <div class="detail-body photos"  >
        {if is_array($detail.content)}
        {$detail.content.contents.0}

        {else}
        {$detail.content}
        {/if}
    </div>
    <div class="mhcms-detail-info" style="margin-top: 15px">
        <a {if $detail.from}href="{$detail.from}"{/if}>阅读原文</a>


        <span class="fly-list-nums">
                    阅读 {$detail.hits.views}
        </span>
    </div>
</div>

<style>
    .wechat_article{
        padding: 20px 24px 15px;
    }
    .wechat_article .rich_media_title {
        font-size: 26px;
        line-height: 1.4;
        margin-bottom: 14px;
    }
    .mhcms-detail-info{
        margin-bottom: 20px;
        line-height: 20px; font-size: 14px;
    }
    .mhcms-detail-info a{
     color:    #607fa6   ;    margin-right: 10px;
    }
    .mhcms-detail-info .datetime{
        color: #888;
    }

    .mhcms-detail-info span{
        margin-right: 15px;color: #888;

    }
</style>
