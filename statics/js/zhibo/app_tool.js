;(function ($) {
    $.fn.extend({
        "minTipsBox": function (options) {
            options = $.extend({
                    tipsContent: "",
                    //提示内容
                    tipsTime: 1 //停留时间 , 1 等于 1秒
                },
                options);
            var $minTipsBox = ".min_tips_box";
            var $tipsContent = ".min_tips_box .tips_content";
            var $tipsTime = parseFloat(options.tipsTime) * 1000;
            //弹出框html代码
            var $minTipsBoxHtml = '<div class="min_tips_box">' + '<b class="bg"></b>' + '<span class="tips_content"></span>' + '</div>';
            //判断是否有提示框
            if ($($minTipsBox).length > 0) {
                $($minTipsBox).show();
                resetBox();
                setTimeout(function () {
                        $($minTipsBox).hide();
                    },
                    $tipsTime);
            } else {
                $($minTipsBoxHtml).appendTo("body");
                resetBox();
                setTimeout(function () {
                        $($minTipsBox).hide();
                    },
                    $tipsTime);
            }

            //重置提示框属性
            function resetBox() {
                $($tipsContent).html(options.tipsContent);
                var tipsBoxLeft = $($tipsContent).width() / 2 + 10;
                $($tipsContent).css("margin-left", "-" + tipsBoxLeft + "px");
            }
        }
    });
})(jQuery);


var mhcms_ajax = {
    _ajax: function (ajaxInfo) {
        var i = mhcms_ajax._querystring('i');
        var j = mhcms_ajax._querystring('j');
        $.ajax({
            type: "POST",
            dataType: "JSON",
            cache: false,
            url: ajaxInfo.url,
            data: ajaxInfo.fromData,
            success: function (data) {
                ajaxInfo.success(data);
            }
        });
    },
    _querystring: function (name) {
        var result = location.search.match(new RegExp("[\?\&]" + name + "=([^\&]+)", "i"));
        if (result == null || result.length < 1) {
            return "";
        }
        return result[1];
    }
};
