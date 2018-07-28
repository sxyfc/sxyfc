
{block name="content_header"}
{include file="public/top_nav" /}{/block}


<div class="weui-cells__title">录入楼盘</div>
<div class="ui top attached mhcms-panel">
    <form class="layui-form form-inline" target="mhcms" method="post"
          action="{:\think\\Request::instance()->url()}" style="padding-right: 10px">
        <br>
        {foreach $field_list as $k=>$field}
        <div class="layui-form-item" id="row_{$k}">
            <label class="layui-form-label">{$field['slug']}</label>
            <div class="layui-input-block">
                {$field['form_str']}
            </div>
        </div>
        {/foreach}
        <div class="layui-form-item">
            <div class="layui-input-block">
                {:token()}
                <button class="layui-btn" lay-submit lay-filter="*">立即发布</button>
            </div>
        </div>
    </form>
    <div class="bk10"></div>
    <div class="bk10"></div>
</div>
<style>
    .layui-form-item {
        margin-bottom: 0;
        padding: 9px 0;
        clear: both;
        border-top: solid 1px #f1f1f1;
    }
    span.point{
        color: #0bb20c;
    }
    span.balance{
        color: #d01919;
    }
</style>
<script>


    require(['layui'] , function (layui) {
        layui.use(['layer', 'form'], function () {
            var form = layui.form;
            var $ = layui.$;
            form.render();
        });
    });
</script>