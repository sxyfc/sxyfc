<div id="app_mhcms">
    <div class="mhcms-panel">
        <div class="mhcms-panel-body">
            {if !is_phone($user.user_name) ||  !$user.is_mobile_verify || $change}

                    <form class="layui-form" action="" method="post" style="padding:10px 0">
                        <div class="weui-cells__title">
                            验证手机号码以后才可以发布
                        </div>
                        <div class="weui-cells weui-cells_form">
                            <div class="weui-cell weui-cell_vcode">
                                <div class="weui-cell__hd">
                                    <label class="weui-label">手机号</label>
                                </div>
                                <div class="weui-cell__bd">
                                    <input class="weui-input"  id="mobile" type="tel"  required lay-verify="phone" name="mobile" value="{$user.mobile}" placeholder="请输入手机号">
                                </div>
                                <div class="weui-cell__ft">
                                    <div class="weui-vcode-btn"  onclick="send_verify_code()">获取验证码</div>
                                </div>
                            </div>


                            <div class="weui-cell ">
                                <div class="weui-cell__hd"><label class="weui-label">验证码</label></div>
                                <div class="weui-cell__bd">
                                    <input class="weui-input" type="number" pattern="[0-9]*" placeholder="请输入验证码"  lay-verify="required" required name="code">
                                </div>
                            </div>
                        </div>




                        <div class="weui-btn-area">
                            <a class="weui-btn weui-btn_primary" href="javascript:" lay-submit lay-filter="formSubmit" id="showTooltips">确定</a>
                        </div>
                    </form>

                    <script>
                        var mobile_sender = {
                            count : 60 ,

                            recount : function () {
                                var that = this;
                                layui.use(['layer', 'form'] , function () {
                                    var $ = layui.$, layer = layui.layer, form = layui.form;


                                    if (that.count > 0) {
                                        setTimeout(function () {
                                            that.count--;
                                            that.recount()
                                        }, 1000);
                                        $("#send_btn").html(that.count);
                                    } else {
                                        that.count = 60;
                                        $("#send_btn").html("获取验证码");
                                    }
                                });
                            } ,
                            send_sms : function (mobile) {
                                var that = this;
                                //初始化类型类型
                                layui.use(['layer', 'form'] , function () {
                                    var url = "{:url('sms/api/send_code')}";
                                    $.get( url, { "mobile": mobile }, function (data) {
                                        if(data.code === 1){
                                            layer.msg("已经发送！");
                                            that.recount();
                                        }else{
                                            layer.msg("对不起 操作失败！" + data.msg);
                                        }
                                    } , 'json' );
                                });
                            }
                        };


                        function send_verify_code() {
                            var mobile_number = $("#mobile").val();
                            mobile_sender.send_sms(mobile_number);
                        }

                        require(['layui'] , function (layui) {
                            layui.use(['layer', 'form'] , function () {
                                var $ = layui.$ ,layer = layui.layer ,form = layui.form;
                                //监听提交
                                form.on('submit(formSubmit)', function(data){
                                    console.log(data);
                                    var api_url = "{:\think\\Request::instance()->url()}";
                                    //TODO 跳转到浏览订单页面
                                    var form_data = JSON.stringify(data.field);
                                    $.post(api_url , {
                                        'data' :  form_data
                                    } , function (ret) {
                                        layer.msg(ret.msg);
                                        if(ret.code===1){
                                            setTimeout(function () {
                                                goToUrl(ret.url);
                                            } , 2000);
                                        }
                                    });
                                    return false;
                                });
                            });

                        });

                    </script>
            {else}


            <div class="weui-msg">
                <div class="weui-msg__icon-area"><i class="weui-icon-success weui-icon_msg"></i></div>
                <div class="weui-msg__text-area">
                    <h2 class="weui-msg__title">操作成功</h2>
                    <p class="weui-msg__desc">您的手机号码已经通过认证</p>
                </div>
                <div class="weui-msg__opr-area">
                    <p class="weui-btn-area">
                        <a href="javascript:;" class="weui-btn weui-btn_primary" onclick="history.back()">返回</a>
                        <a href="/" class="weui-btn weui-btn_default" onclick="history.back()">返回首页</a>
                    </p>
                </div>
            </div>

            {/if}
        </div>
    </div>
</div>

