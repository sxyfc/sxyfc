
{if $wait}
<?php
global $_W;
$detail = \app\common\model\Models::get_item_by( ['user_id' => $_W['user']['id']] , 'users_verify');

?>
<div class="mhcms-panel" style="padding-top: 15px" v-lazy-container="{ selector: 'img' }" id="app_mhcms">
    <div class="mhcms-panel-body">
        <ul class="   user-btns has-text-centered">

            <li class="columns  is-mobile">
                <span class="attrName column is-narrow">姓名：</span>
                <span class="attrValue column is-narrow"><a> {$detail.personal_name}</a></span>
            </li>

            <li class="columns  is-mobile">
                <span class="attrName column is-narrow">身份证：</span>
                <span class="attrValue column is-narrow"><a> {$detail.personal_passport} </a></span>
            </li>
            <li class="columns  is-mobile">
                <span class="attrName column is-narrow">个人照片：</span>
                <span class="attrValue column is-narrow"><a><img class="image is-64x64" :data-src="'{$detail.personal_passport_pic.0.url}'">  </a></span>
            </li>
            {if $type=='company'}

            <li class="columns  is-mobile">
                <span class="attrName column is-narrow">营业执照：</span>
                <span class="attrValue column is-narrow"><a><img class="image is-64x64" :data-src="'{$detail.company_passport.0.url}'">  </a></span>
            </li>
            {/if}
        </ul>
    </div>
</div>
{/if}


{if $wait==99 || $wait==1}
{if $wait==99}
<article class="message is-success">
    <div class="message-header">
        <p>Success</p>
        <button class="delete" aria-label="delete"></button>
    </div>
    <div class="message-body">
        您已经通过了该认证
    </div>
</article>

{/if}

{if $wait==1}
<article class="message is-info">
    <div class="message-header">
        <p>Info</p>
        <button class="delete" aria-label="delete"></button>
    </div>
    <div class="message-body">
        您的资料审核中
    </div>
</article>

{/if}


{else}

<div class="layui-container" style="margin-top: 50px">
    <div class="layui-row">
        <fieldset class="layui-elem-field site-demo-button" style="padding: 15px">
            <legend> 实名认证 </legend>
            <div class="protocol">
                <p>
                </p><div class="well">
                </div>

                <p></p>
                <form class="layui-form form-inline" target="mhcms" method="post"
                      action="{:\think\\Request::instance()->url()}">
                    <br>
                    {volist name="list" id="item"}
                    <div class="layui-form-item">
                        <label class="layui-form-label">{$item['slug']}</label>
                        <div class="layui-input-block">
                            {$item['form_str']}
                        </div>
                    </div>
                    {/volist}

                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit lay-filter="*">立即提交</button>
                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                        </div>
                    </div>

                </form>

            </div>
        </fieldset>

    </div>
</div>
<script>
    layui.use(['form'] , function () {
        var form = layui.form;
        form.render();
    });
</script>

{/if}

<script>

    require([ 'Vue' , 'VueLazyload' ] , function (Vue , VueLazyload) {
        Vue.use(VueLazyload)
        new Vue({
            el : "#app_mhcms",
            data : {
                show_esf : true
            },
            methods :{

            }
        });
    });
</script>

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