{block name="content_header"}
{include file="public/top_nav" /}
{/block}


<div class=" mhcms-container">
    <div id="app_mhcms" style="margin-top: 15px">
        <div class="columns is-mobile">
            <div class="column is-narrow">
                {include file="public/user_left_nav" /}
            </div>

            <div class="column is-9">
                <div class="mhcms-panel">
                    <div class="mhcms-panel-header">我要买房 - 在线委托</div>
                    <div class="mhcms-panel-body" style="padding: 25px">
                        <form class="layui-form form-inline" target="mhcms" method="post" action="{$_W.current_url}">

                            {foreach $fields as $field}
                            <div class="layui-form-item {$field.node_field_mode}_tr" style="">

                                <label class="layui-form-label"><strong style="font-size: 16px"> {$field['slug']}</strong></label>
                                <div class="layui-input-block node_field_tips">
                                    {$field['form_str']}
                                </div>
                            </div>
                            {/foreach}
                            <div class="layui-form-item">
                                <div class="layui-input-block needsclick">
                                    {:token()}
                                    <input class="layui-btn needsclick" type="submit" lay-submit lay-filter="*" value="立即提交">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>