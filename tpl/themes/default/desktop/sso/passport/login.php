
<link href="/statics/components/semantic/semantic.min.css" rel="stylesheet">
<p style="line-height: 100px;font-size: 20px;text-align: center">
    用账号密码登录
</p>
<div class="ui    container" id="new_better_form" >
    <form action="" method="post" class="ui tall stacked red form " id="form" style="margin-top: 10px;max-width: 500px;margin: auto">


        <div class=" field">
            <input type="number" id="user_name" name="data[user_name]" pattern="[0-9]*" placeholder="请输入手机号">
        </div>
        <div class=" field">
            <input type="password" id="password" name="data[password]"  placeholder="登录密码">
        </div>
        <div class="weui-cell weui-cell_vcode" style="display: none">
            <div class="weui-cell__hd">
                <label class="weui-label">验证码</label>
            </div>
            <div class="weui-cell__bd">
                <input class="weui-input" type="tel" placeholder="请输手机验证码">
            </div>
            <div class="weui-cell__ft">
                <button class="weui-vcode-btn">获取验证码</button>
            </div>
        </div>
        <div class=" field">

            <div class="ui grid">
                <div class="six wide column">
                    <div class=" col-xs-6">
                        <div class="ui submit button login" >立刻登录</div>
                    </div>
                </div>
                <div class="ten wide column">
                    <div style="float: right"  class="red ui animated fade button submit register" tabindex="0">
                        <div class="visible content">创建一个账号</div>
                        <div class="hidden content">使用当前信息创建</div>
                    </div>
                </div>


            </div>

            <div class="ui grid">
                {if $_W['site_wechat']}
                <div class="sixteen  wide column is-clearfix"  >
                    {if is_weixin()}

                    <a  href="{:url('sso/passport/wx_login')}" class="green ui  button " id="showTooltips">微信登录</a>

                    {else}
                    <a  href="{:url('sso/passport/wx_subscribe')}" class="green ui  button " id="showTooltips">微信登录</a>
                    {/if}
                </div>
                {/if}
            </div>
        </div>

        <!--hidden fields 隐藏域-->

        <input type="hidden" value="{$forward}" name="forward">
        <input type="hidden" value="{$site_id}" name="site_id">
        <div class="ui error message"></div>
    </form>
</div>
<script>


    require(['semantic'] , function (semantic) {
        $.fn.api.settings.successTest = function(response) {
            return response.code === 1;
        }

        $('#form')
            .form({
                fields: {
                    user_name: {
                        identifier: 'user_name',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : '请输入您的手机号码'
                            }
                        ]
                    },
                    password: {
                        identifier: 'password',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : '请输入您的密码'
                            },
                            {
                                type   : 'minLength[6]',
                                prompt : '密码长度最少为{ruleValue} 位数'
                            }
                        ]
                    }
                },
                onSuccess: function(e) {
                    //阻止表单的提交
                    e.preventDefault();
                }
            })
        ;

        /**
         * need to login when redirect to the other site
         * @param $domain
         * @param $url
         */
        function sso_login($domain , $url) {
            $('.form .login')
                .api({
                    url: "{:url('sso/passport/login')}",
                    method: "post",
                    on: 'now',
                    serializeForm: true,
                    onFailure: function (response) {
                        // request failed, or valid response but response.success = false
                        console.log(response);
                        show_message(response.msg );
                    },
                    onSuccess: function (response) {
                        // valid response and response.success = true
                        //todo login to current site

                        show_message(response.msg, response.code, false, 1000, response.url);

                    },
                    // modify data PER element in callback
                    beforeSend: function (settings) {
                        return $('.ui.form').form("is valid")
                    }
                })
            ;
        }
        $('.form .login')
            .api({
                url: "{:url('sso/passport/login')}",
                method: "post",
                serializeForm: true,
                onFailure: function (response) {
                    // request failed, or valid response but response.success = false
                    console.log(response);
                    show_message(response.msg );
                },
                onSuccess: function (response) {
                    // valid response and response.success = true
                    //todo login to current site
                    show_message(response.msg, response.code, false, 1000, response.url);
                },
                    // modify data PER element in callback
                    beforeSend: function (settings) {
                        return $('.ui.form').form("is valid")
                    }
                })
        ;

        $('.form .register')
            .api({
                url: "{:url('sso/passport/register')}",
                method: "post",
                serializeForm: true,
                // modify data PER element in callback
                beforeSend: function (settings) {
                    return $('.ui.form').form("is valid")
                },
                onFailure: function (response) {
                    // request failed, or valid response but response.success = false
                    show_message(response.msg );
                },
                onSuccess: function (response) {
                    // valid response and response.success = true
                    show_message(response.msg ,response.code , false ,1000 , response.url);
                },

            })
        ;
    });

</script>