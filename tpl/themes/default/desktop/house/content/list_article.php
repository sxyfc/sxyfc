
<div class="ui top attached mhcms-panel">
    <div class="ui  mhcms-panel-header"><i class="{$cate['icon']}"></i>{$cate['cate_name']}</div>
    <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">

        <ul class="ui mhcms-list unstackable items">
            {foreach $lists as $item}
            <?php
            $_item = \app\common\model\Models::get_item($item['id'] , $content_model_id);
            $url  = $_item['thumb']['0']['url'];
            ?>
            <div class="ui item mhcms-list-item ">
                {if $url}<a  class=" image tiny ui"> <img src="{$url}"  > </a>{/if}
                <div class="content">
                    <a class="header" href="{:url('house/content/detail' , ['id'=>$item['id'] , 'cate_id'=>$item['cate_id']])}">{$item.title}</a>

                    <div class="extra">
                        <span>{$item.create_at}</span> <span class="fly-list-nums"> <i class="talk outline icon" title="浏览"></i> {$_item.hits.views} </span>
                    </div>
                </div>

            </div>
            {/foreach}
        </ul>
    </div>


</div>