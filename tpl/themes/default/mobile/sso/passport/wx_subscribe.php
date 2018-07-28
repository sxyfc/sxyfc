
<div class="mhcms-container" style=" ">
    <div class="ui    container" id="new_better_form" >

        <div class="">


            <img src="{$code}" class="logo" style="margin: auto;">

        </div>
        <div class="text">
            <div class="weui-flex">
                <div class="weui-flex__item">
                    <hr class="light">
                </div>
                <div>
                    <h1>{$_W['site']['config']['system_name']}</h1>
                </div>
                <div class="weui-flex__item">
                    <hr class="light">
                </div>
            </div>
            <p>使用微信扫一扫功能进行登录。</p>
        </div>
    </div>

</div>
<div class="vegas-overlay" ></div>
<script>

    var mhcms_wx_login = "{$mhcms_wx_login_api}";
    var uuid = "{$uuid}";

    check_login();


    function check_login() {
        $.post(mhcms_wx_login, {:json_encode($post_data)}, function (data) {

            if (data.status == 'wait') {
                setTimeout(function () {
                    check_login();
                }, 2000)
            }

            if (data.status == 'expired') {
                console.log("expired");
            }

            if (data.status == "success") {
                console.log("登录成功");
                window.location.href="{$forward}";
            }
        });
    }

</script>