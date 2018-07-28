
<script>
    require(['jquery', 'layui'], function () {

        function sso_logout() {
            layui.use('layer', function () {
                var $ = layui.$;

                console.log('get user info' + m_c_d);
                var top_menu_url = "//" + m_c_d + "/sso/passport/sso_logout";

                $.get(
                    top_menu_url, {
                        "site_id": site_id
                    }, function (data) {
                        console.log(data);
                        //alert(data.top_menu_url);
                        window.location.href = "{$forward}";
                    }, 'jsonp'
                );
            });

        }

        layui.use(['layer'], function () {
            var $ = layui.$;
            sso_logout();
        });
    });


</script>