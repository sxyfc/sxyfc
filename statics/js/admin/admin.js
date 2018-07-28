layui.use(['layer'] , function () {
    var $ = layui.$;
    setInterval(function () {
        $.get('admin/service/check_session' , {} , function (data) {
            console.log(data);
        });
    } , 500000);
});
