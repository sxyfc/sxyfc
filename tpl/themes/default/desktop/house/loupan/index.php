{block name="content_header"}
{include file="public/top_nav" /}
{/block}

<?php

$where = [];
$where['site_id'] = $_W['site']['id'];
$lists = set_model("house_loupan")->where($where)->order('id desc')->paginate(9);

?>
<div class=" mhcms-container">
<div id="app_mhcms">
    <div class="columns">
    </div>
        <div class="row columns is-multiline is-marginless">

            {foreach $lists as $item}
            <?php

            $_item= \app\common\model\Models::get_item($item['id'] , 'house_loupan');
            $is_developer = false;
            ?>
            <a class="column is-3" href="{:url('house/loupan/detail' , ['id'=>$item.id])}">
                <div class="card large">
                    <div class="card-image">
                        <figure class="image">
                            <img src="{$_item.thumb.0.url}" alt="Image" style="height: 180px">
                        </figure>
                    </div>
                    <div class="card-content" style="padding: 10px">
                        <div class="media">
                            {if $is_developer}
                            <div class="media-left" >
                                <figure class="image is-96x96">
                                    <img src="https://i.imgsafe.org/a4/a4bb9acc5e.jpeg" alt="Image">
                                </figure>
                            </div>
                            {/if}
                            <div class="media-content">
                                <p class="title is-4 no-padding">{$item.loupan_name}</p>
                                <p><span class="title is-6">{$_item.area_id|default='-'}</span>
                                    <span class=" is-6 is-pulled-right has-text-danger">{$_item.price|default='-'}元/平方</span>
                                </p>
                            </div>
                        </div>
                        <div class="content is-clipped"  style="    white-space: nowrap;">
                            {$_item.address}
                            <div class="background-icon"><span class="icon-twitter"></span></div>
                        </div>
                    </div>
                </div>
            </a>
            {/foreach}
        </div>



</div>
    {$lists->render()}
</div>
