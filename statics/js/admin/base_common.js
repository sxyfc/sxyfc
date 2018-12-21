var loading_layer;         //Loading 的索引
var mhcms_frame_work = {}; //鸣鹤CMS核心框架

mhcms_frame_work.mhcms_frame = function (obj) {
    layui.use(['layer', 'form'], function () {
        loading('open');
        var $ = layui.$;
        var form = layui.form;
        $.get(obj.attr('href'), function (data) { //获取网址内容 把内容放进data
            loading('close'); //关闭加载层
            if (data.code == 0) {
                layer.msg(data.msg);
            } else {
                layer.open({
                    type: 1,
                    fix: true, //固定位置
                    title: obj.attr('title'),
                    shadeClose: true, //点击背景关闭
                    shade: 0.4,  //透明度
                    content: data,
                    area: [obj.attr('width'), obj.attr('height')],
                    success: function (layero, index) {
                        form.render();
                    }
                });
            }
        }, 'json');
    });
};
//Load a iframe
mhcms_frame_work.mhcms_iframe = function (obj) {
    var maxmin =true;
    if(obj.data('full')=='false'){

        maxmin =false;

    }

    layui.use(['layer'], function () {

        var index = layer.open({
            type: 2,
            fix: true, //固定位置
            title: obj.attr('title'),
            shadeClose: true, //点击背景关闭
            shade: 0.4,  //透明度
            content: obj.attr('href'),
            area: [obj.data('width'), '600px'],
            maxmin: true
        });

        if(maxmin){
            layer.full(index);
        }

    });
};
//load a url , expecting a json data with status
mhcms_frame_work.mhcms_load = function (obj) {
    loading('open');
    layui.use(['layer'], function () {
        $.get(obj.attr('href'), function (data) {
            loading('close');
            layer.msg(data.msg, {icon: data.code, time: 500}, function () {
                eval(data.callback);
            });

        }, 'json');
    });
};
//confirm url , expecting a json data with status
mhcms_frame_work.mhcms_confirm = function (obj) {
    var url = obj.attr('href');
    layui.use(['layer'], function () {
        var $ = layui.$;
        layer.confirm('您确定吗?', {
            icon: 3,
            title: '提示'
        }, function (index) {
            loading('open');
            $.get(url, function (data) {
                loading('close');
                layer.close(index);
                layer.msg(data.msg, {icon: data.code}, function () {
                    if(data.javascript){
                        eval(data.javascript);
                    }
                    if(data.url){
                        goToUrl(data.url);
                    }
                });

            }, 'json');
        });
    });
};
//confirm url , expecting a json data with status
mhcms_frame_work.mhcms_confirm_frame = function (obj) {
    var url = obj.attr('href');
    layui.use(['layer'], function () {
        layer.confirm('您确定吗?', {
            icon: 3,
            title: '提示'
        }, function (index) {
            mhcms_frame_work.mhcms_frame(obj);
        });
    });
};
mhcms_frame_work.mhcms_new_tab = function(obj){
    var url = obj.attr('href');
    window.open(url , '_blank');
};
//input 编辑
mhcms_frame_work.mhcms_input_blur = function (obj) {
    layui.use(['layer'], function () {
        var url = single_url;
        loading('open');
        $.get(url, {
            'field': obj.attr('field'),
            'field_value': obj.val(),
            'pk': obj.attr('pk'),
            'pk_value': obj.attr('pk_value'),
            'model': obj.attr('model'),
        }, function (data) {
            loading('close');
            layer.msg(data.msg, {icon: data.code});
        }, 'json');
    });
};
//元素编辑
mhcms_frame_work.mhcms_element_blur = function (obj) {
    layui.use(['layer'], function () {
        var url = single_url;
        loading('open');
        var value = obj.html();
        $.get(url, {
            'field': obj.attr('field'),
            'field_value': value,
            'pk': obj.attr('pk'),
            'pk_value': obj.attr('pk_value'),
            'model': obj.attr('model'),
        }, function (data) {
            loading('close');
            layer.msg(data.msg, {icon: data.code});
        }, 'json');
    });
};
//全部选中
mhcms_frame_work.mhcms_check_all = function (obj) {
    layui.use(['layer'], function () {
        var $ = layui.$;
        var child = obj.data('rel');
        $(".child_" + child).prop('checked', obj.prop("checked"));
    });
};
//弹框选择
mhcms_frame_work.mhcms_form_picker = function (obj) {

    layui.use(['layer'], function () {
        layer.open({
            content: 'test'
            , btn: ['确定', '关闭']
            , yes: function (index, layero) {
                //按钮【按钮一】的回调
                layer.close(index);
            }
            , btn2: function (index, layero) {
                //按钮【按钮二】的回调

                //return false 开启该代码可禁止点击该按钮关闭
            },
            success: function (layero, index) {

                console.log(layero, index);
            }
        });
    });
};

//icon selector
mhcms_frame_work.mhcms_icon_form_picker = function (obj) {
    var service = obj.data('service');
    layui.use(['layer'], function () {
        layer.open({
            content: 'test'
            , btn: ['确定', '关闭']
            , yes: function (index, layero) {
                //按钮【按钮一】的回调
                layer.close(index);
            }
            , btn2: function (index, layero) {
                //按钮【按钮二】的回调

                //return false 开启该代码可禁止点击该按钮关闭
            },
            success: function (layero, index) {
                $.get(service, {}, function (data) {
                    console.log(data);
                });
                console.log(layero, index);
            }
        });
    });
};

mhcms_frame_work.mhcms_normal = function (obj) {
    this.mhcms_iframe(obj);
};
mhcms_frame_work.mhcms_element_linkage_select = function (obj , init) {

    function clear_linkage(linkage_group){
        //alert('.sub_linkage_select' + "." + linkage_group);
        $('.sub_linkage_select' + "." + linkage_group).html("");
    }


    //api service
    var service = obj.data('service');
    //current value
    var id_key_val  = obj.val();
    //连动分组
    var linkage_group = obj.data('linkage_group');
    if(id_key_val=="0"){
        return clear_linkage(linkage_group)
    }

    var model_id = obj.data('current_model_id');
    var target_field = obj.data('target_field');
    var from_field = obj.data('from_field');

    if(!target_field || !from_field){
        return false;
    }
    loading('open');
    $.get(service , {
        model_id : model_id ,
        target_field : target_field ,
        from_field : from_field ,
        id_key_val : id_key_val
    } , function (data) {
        loading('close');
        var rows = data.data;var $str = "";
        $.each(rows , function (index) {
            var select = " ";
            if(rows[index].id == $("#" + target_field).data('default_value')){
                 select = "selected";
            }
             $str += "<option " + select +" value='" + rows[index].id + "'>" + rows[index].name + "</option>";
        })


        if(init==1){
            var target = $("#" + target_field).val();
            if(target==0 || target==""){
                $("#" + target_field).html($str);
            }
        }else{
            $("#" + target_field).html($str);
        }

    }, 'json');
};

mhcms_frame_work.reload_page = function () {
    layui.use(['layer'] , function () {
        var $ = layui.$;
        $('#larry_tab_content').children('.layui-show').children('iframe')[0].contentWindow.location.reload(true);
    });
}




layui.use(['layer'], function () {
    var $ = layui.$, layer = layui.layer;
    $(document).on("click", "a[mini]", function (e) {
        e.preventDefault();//阻止默认动作
        eval("mhcms_frame_work.mhcms_" + $(this).attr('mini') + "($(this))");
    });

    $(document).on("blur", "input[mini='blur']", function (e) {
        e.preventDefault();//阻止默认动作
        eval("mhcms_frame_work.mhcms_input_" + $(this).attr('mini') + "($(this))");
    });

    $(document).on("blur", "[mini='element_blur']", function (e) {
        e.preventDefault();//阻止默认动作
        eval("mhcms_frame_work.mhcms_element_" + $(this).attr('mini') + "($(this))");
    });

    $(document).on("change", "[mini='linkage_select']", function (e) {
        e.preventDefault();//阻止默认动作
        eval("mhcms_frame_work.mhcms_element_" + $(this).attr('mini') + "($(this))");
    });

    $(document).on("click", "input[mini='chain']", function (e) {
//    e.preventDefault();
        var chain = $(this).data('chain');
        console.log($(this).prop("checked"));
        $(".chain").prop("checked", $(this).prop("checked"));
    });

});


function loading(type) {
    layui.use(['layer'], function () {
        var $ = layui.$, layer = layui.layer;
        if (type == "open") {
            loading_layer = layer.load(1, {
                shade: [0.5, '#ccc'] //0.1透明度的白色背景shade: 0
                , shadeClose: true
            });
        } else {
            layer.close(loading_layer);
        }
    });
}

/*Form RES*/
function show_message($msg, $icon, $autoclose, $time, $url, $callback) {
    layui.use(['layer'], function () {
        layer.msg($msg, {
            icon: $icon, shade: [0.8, '#393D49'], shadeClose: true,
            time: $time,
            zIndex:99999999
        }, function () {
            if ($autoclose) {
                layer.closeAll();
            }
            if ($url) {
                goToUrl($url);
            } else {
                eval($callback);
            }
        });
    });
}

function goToUrl($url) {
    window.location.href = $url;
}

function reset_code($target) {
    $target = $target ? $target : "#code";
    $($target).attr("src", $($target).attr("src") + "&" + Math.random());
}

function reload_page() {
    window.location.reload();
}

function reload_parent_page() {
    parent.window.location.reload();
}


function sso_admin_login($url, $auth_str) {
    loading('open');
    $.get($url, {
        'auth_str': $auth_str
    }, function (data) {
        console.log(data);
        loading('close');
        parent.window.location.href = data.url;
        //layer.msg(data.msg,{ icon : data.code },function(){});
    }, 'jsonp');
}



function remove_parent(obj , selector) {
    $(obj).parents(selector).remove();
}