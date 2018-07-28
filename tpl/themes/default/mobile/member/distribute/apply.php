
<div class="mhcms-panel">

    <div class="mhcms-panel-header">申请合伙人</div>
    <div class="mhcms-panel-body">
        <form class="layui-form form-inline" target="mhcms" method="post"
              action="{:\think\\Request::instance()->url()}" style="padding-right: 10px">
            <br>
            {foreach $fields as $k=>$field}
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
                    <button class="layui-btn" lay-submit lay-filter="*">立即申请</button>
                </div>
            </div>

        </form>
    </div>
</div>
{block name="footer"}
<div class="weui-tabbar" style="    z-index: 99999;position: fixed;box-shadow: 0px 7px 14px 5px #c0bfc4;">
    <a href="/" class="weui-tabbar__item " >
        <span style="display: inline-block;position: relative;">
            <i class="weui-tabbar__icon"><i class="iconfont icon-shouye"></i></i>
        </span>
        <div class="weui-tabbar__label">返回首页</div>
    </a>
</div>
{/block}