
var Bao8_org = function () {
    console.log("Bao8_org FrameWork init");
    console.log("此产品由鸣鹤CMS核心强力驱动");
    console.log("QQ : 1620298436");
};

Bao8_org.prototype.tooltip = function () {
    require(['bootstrap'], function () {
        $(".form-control").each(function () {
            $(this).tooltip({placement: "left"});
        });
    })
};

Bao8_org.prototype.closeItem = function () {
    $(document).on("click", "button.close", function (e) {
        e.preventDefault();
        $(this).parents(".item-close").remove();
    });
};

Bao8_org.prototype.reload_page = function () {
    parent.window.location.reload();
};

Bao8_org.prototype.goToUrl = function ($url) {
    window.location.href = $url;
};

Bao8_org.prototype.reset_code = function ($target) {
    $target = $target ? $target : "#code";
    $($target).attr("src", $($target).attr("src") + "?" + Math.random());
};
Bao8_org.prototype.render_content = function ($container, $nav_target) {
    $($container).attr("data-spy", "scroll");
    $($container).attr("data-target", $nav_target);
    $($container).css("position", 'relative');
    $($container).scrollspy({target: $nav_target});
}

Bao8_org.prototype.render_nav_helper = function ($target) {
    $($target).click(function () {
        $($($target).attr('data-target')).toggle(100);
    });
};

var mindHer = new Bao8_org();

var loading_layer; //Loading 的索引

function loading(type) {
    //layer.msg('Loading...',{icon: 3,time: 0});
    if (type == "open") {
        loading_layer = layer.load(1, {
            shade: [0.5, '#ccc'] //0.1透明度的白色背景shade: 0
            , shadeClose: true
        });
    } else {
        layer.close(loading_layer);
    }
}

var mhcms_frame_work = {}; //鸣鹤CMS核心框架

require(['jquery','layui'], function ($ , layui) {



    mhcms_frame_work.open_frame = function(title , href , width  , height , offset){
        offset = offset || "auto";
        layui.use(['layer', 'form'], function () {
            loading('open');
            var $ = layui.$;
            var form = layui.form;
            $.get(href , function (data) { //获取网址内容 把内容放进data
                loading('close'); //关闭加载层
                if (data.code === 0) {
                    layer.msg(data.msg);
                } else {
                    layer.open({
                        type: 1,
                        offset : offset ,
                        shadeClose: true, //点击背景关闭
                        fix: true, //固定位置
                        title: title,
                        shade: 0.4,  //透明度
                        content: data,
                        area: [width , height],
                        success: function (layero, index) {
                            form.render();
                        }
                    });
                }
            }, 'json');
        });
    }




    mhcms_frame_work.goToUrl = function ($url) {
        window.location.href = $url;
    };

    mhcms_frame_work.mhcms_frame = function (obj) {
        var title =  obj.attr('title'), width = obj.attr('width'),height = obj.attr('height'),href = obj.attr('href');
        this.open_frame(title ,href,width, height);
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
        var url = obj.attr('href') ||  obj.data('href');
        var title = obj.data('title') || '您确定吗?';
        var $query = obj.data();

            layui.use(['layer'], function () {
            var $ = layui.$;
            layer.confirm(title, {
                icon: 3,
                title: '提示'
            }, function (index) {
                loading('open');
                $.get(url, {
                    site_id : site_id,
                    query : $query
                } ,function (data) {
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

});

/*Form RES*/
function show_message($msg, $icon, $autoclose, $time, $url, $callback) {
    layer.msg($msg, {
        icon: $icon, shade: [0.8, '#393D49'], shadeClose: true,
        time: $time,zIndex : 99999999
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

}

function goToUrl($url) {
    window.location.href = $url;
}

function reset_code($target) {
    $target = $target ? $target : "#code";
    $($target).attr("src", $($target).attr("src") + "?" + Math.random());
}

function reload_page() {
    window.location.reload();
}

function reload_parent_page() {
    parent.window.location.reload();
}


//site process
if (check_area === 1) {
    require(['jquery'] , function ($) {
        //$('<div id="geo_allmap" class=" is-hidden"></div>').appendTo('body');
        //var map = new BMap.Map("geo_allmap");
        var geolocation = new BMap.Geolocation();
        geolocation.getCurrentPosition(function (r) {
            if (this.getStatus() == BMAP_STATUS_SUCCESS) {
                var geoc = new BMap.Geocoder();
                var pt = r.point;
                geoc.getLocation(pt, function (rs) {
                    console.log(rs);
                    var addComp = rs.addressComponents;
                    //alert(addComp.province + ", " + addComp.city + ", " + addComp.district + ", " + addComp.street + ", " + addComp.streetNumber);
                    //alert(JSON.stringify(rs));
                    $.post("/sites/service/create_site" , {
                        site_id : site_id ,
                        query : {
                            province : addComp.province ,
                            city: addComp.city ,
                            district: addComp.district ,
                            lng: r.point.lng ,
                            lat: r.point.lat ,
                        }
                    } , function (data) {
                        console.log(data);

                    } , 'json');
                });
            }
        }, {enableHighAccuracy: true})
    });
}


function set_cookie($name, $value, call_back) {
    require(['semantic'], function (semantic) {
        $("<br />")
            .api({
                action: "set cookie",
                data: {
                    name: $name,
                    value: $value,
                },
                on: 'now',
                method: "get",
                onSuccess: function (response) {
                    //    reload_page();
                },
                onComplete: function (response) {
                    if (typeof callback === "function") {
                        callback(response);
                    }
                }
            })
        ;
    });
}

function get_user_info() {
    layui.use('layer', function () {
        var $ = layui.$;

        console.log('get user info' + m_c_d);
        var top_menu_url = "//" + m_c_d + "/sso/passport/get_login_info";
        $.get(
            top_menu_url, {
                "site_id": site_id
            }, function (data) {
                sso_login(data);
            }, 'jsonp'
        );
    });

}


function sso_login($data) {
    layui.use('layer', function () {
        var $ = layui.$;
        console.log($data);
        var sso_login_url = "/sso/passport/sso_login";
        $.post(
            sso_login_url, {
                "auth_str": $data.random_auth , "user_id" : $data.user_id
            }, function (data) {
                console.log(data);
            }, 'json'
        );
    });
}


function play_music(obj) {
    require(['jquery' , 'jquery.jplayer'] , function ($, jplayer) {
        var track = $(obj).data("href");
        play(track);
    });
}

function play(track) {
    require(['jquery' , 'jquery.jplayer'] , function ($, jplayer) {
        if($('.jquery_jplayer').length <= 0){
            $("<div id=\"jquery_jplayer\" class=\"jquery_jplayer\" style=\"display: none\"></div>").appendTo('body');
        }

        var	my_jPlayer = $("#jquery_jplayer");

// Instance jPlayer
        my_jPlayer.jPlayer({
            ready: function () {

            },
            timeupdate: function(event) {
                console.log(parseInt(event.jPlayer.status.currentPercentAbsolute, 10) + "%");
            },
            play: function(event) {
                // my_playState.text(opt_text_playing);
            },
            pause: function(event) {
                //  my_playState.text(opt_text_selected);
            },
            ended: function(event) {
                // my_playState.text(opt_text_selected);
            },
            swfPath: "../../dist/jplayer",
            cssSelectorAncestor: "#jp_container",
            supplied: "mp3",
            wmode: "window"
        });

        my_jPlayer.jPlayer("setMedia", {
            mp3: track
        });
        my_jPlayer.jPlayer("play");
        first_track = false;

    });
}


function remove_parent(obj , selector) {
    $(obj).parents(selector).remove();
}