/**
 * MHCMS 上传模块
 * Autor: New Better
 * site: www.mhcms.net
 * Date :2017-10-05
 */
layui.define(['element', 'upload', 'layer', 'jquery'], function (exports) {
    var $ = layui.$,
        device = layui.device(),
        layer = layui.layer,
        upload = layui.upload,
        element = layui.element;

    var new_better_upload = {

        init_single_video_upload: function (field_name, ext) {
            // 执行 实例
            var uploadInst = upload.render({
                elem: '.layui_single_upload_' + field_name //绑定元素
                , url: upload_url  //上传接口
                , accept: 'video'
                , exts : ext
                , before: function (obj) {
                    //todo check md5
                    obj.preview(function (index, file, result) {
                        $('#' + field_name).append('<div id="' + field_name + '_' + index + '_img" class="layui-upload-img  weui-uploader__file video_item"><div src=\"' + result + '\" alt=\"' + file.name + '\" class=\"layui-upload-img>'+ file.name+'</div>"></div>')
                    });

                }
                , done: function (res, index, upload) {
                    //上传完毕回调
                    var item = this.item; // 当前元素
                    var $real_form_name = item.attr('name');
                    $("#" + field_name + '_' + index + '_img').append('<input type="hidden" value="' + res.file_id + '" name="' + $real_form_name + '">');
                    console.log(index);
                }
                , error: function () {
                    //请求异常回调
                }
            });
        },

        init_single_upload: function (field_name, ext ,accept) {
            //执行实例
            accept = accept ||  'images';
            ext  = ext || "jpg|png|gif|bmp|jpeg";
            var uploadInst = upload.render({
                elem: '.layui_single_upload_' + field_name //绑定元素
                , url: upload_url  //上传接口
                , accept: accept
                , exts : ext
                , before: function (obj) {
                    layer.msg("上传中，请稍后");
                    //todo check md5
                    if(accept === "images"){
                        obj.preview(function (index, file, result) {
                            $('#' + field_name).html('<div id="' + field_name + '_' + index + '_file" class="layui-upload-img  weui-uploader__file"><img src=\"' + result + '\" alt=\"' + file.name + '\" class=\"layui-upload-img\"></div>')
                        });
                    }else{

                        obj.preview(function (index, file, result) {
                            var type = file.type.split("/")[0];
                            $('#' + field_name).html('<div id="' + field_name + '_' + index + '_file" class="layui-upload-img weui-uploader__file mhcms-upload-file-'+ type + '">' + file.name  + '</div>');
                        });
                    }
                }
                , done: function (res, index, upload) {
                    //上传完毕回调
                    layer.msg("上传成功");
                    var item = this.item; // 当前元素
                    var $real_form_name = item.attr('name');
                    $("#" + field_name + '_' + index + '_file').append('<input type="hidden" value="' + res.file_id + '" name="' + $real_form_name + '">');
                }
                , error: function (e) {
                    //请求异常回调
                    console.log(e);
                }
            });
        },

        init_mutil_wx_upload: function (field_name) {
            //alert(field_name);
            var wx_choose = {
                elem: $('.layui_mutil_upload_' + field_name ),
                sum: 0,
                choose: function () {

                    var that = this;
                    require(['wx', 'mhcms'], function (wx , mhcms) {
                            mhcms.get_sign(location.href , function (jssdk_obj) {
                                wx.config(jssdk_obj);


                                wx.ready(function () {
                                    wx.chooseImage({
                                        count: 5, // 默认9
                                        sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                                        sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                                        success: function (res) {
                                            that.localIds = res.localIds;
                                            that.upload(0);
                                        }
                                    });
                                });
                            });


                    });

                }
                ,
                upload: function (i) {
                    var that = this;
                    var length = that.localIds.length; //本次要上传所有图片的数量

                    require(['wx', 'mhcms'], function (wx , mhcms) {

                        mhcms.get_sign(location.href , function (jssdk_obj) {
                            wx.config(jssdk_obj);

                            wx.ready(function () {
                                wx.uploadImage({
                                    localId: that.localIds[i], //图片在本地的id
                                    isShowProgressTips: 1,
                                    success: function (res) {
                                        var download_api = "/wechat/service/download_media";
                                        // download media
                                        layui.use(['layer'], function () {
                                            var $ = layui.$;
                                            $.get(
                                                download_api, {
                                                    "media_id": res.serverId
                                                }, function (data) {
                                                    // add preview image to the list
                                                    var $real_form_name = that.elem.data('name');
                                                    $('#' + field_name).append('<div id="' + field_name + '_' + i + '_img" class="layui-upload-img  weui-uploader__file"><img src=\"' + data.url + '\" alt=\"' + '\" class=\"layui-upload-img\"><input type="hidden" value="' + data.data.file_id + '" name="' + $real_form_name + '"></div>');
                                                } , 'json'
                                            );
                                        });

                                        i++;
                                        that.sum++;

                                        if (i < length) {
                                            that.upload(i);
                                        }

                                    },
                                    fail: function (res) {
                                        alert(JSON.stringify(res));
                                    }
                                });
                            });
                        });

                    });

                }
            };

            layui.use(['layer'] , function () {
                var $ = layui.$;
                $('.layui_mutil_upload_' + field_name).on('click', function () {
                    wx_choose.choose();
                });
            });
        },

        init_mutil_upload: function (field_name, ext) {
            //执行实例
            var uploadInst = upload.render({
                elem: '.layui_mutil_upload_' + field_name //绑定元素
                , url: upload_url  //上传接口
                , accept: 'image/*'
                , before: function (obj) {
                    layer.msg("上传中，请稍后");
                    //todo check md5
                    obj.preview(function (index, file, result) {
                        $('#' + field_name).append('<div id="' + field_name + '_' + index + '_img" class="layui-upload-img  weui-uploader__file"><img src=\"' + result + '\" alt=\"' + file.name + '\" class=\"layui-upload-img\"></div>')
                    });

                }
                , done: function (res, index, upload) {
                    //上传完毕回调
                    var item = this.item; // 当前元素
                    layer.msg("上传成功！");
                    var $real_form_name = item.data('name');
                    $("#" + field_name + '_' + index + '_img').append('<input type="hidden" value="' + res.file_id + '" name="' + $real_form_name + '">');
                    console.log(index);
                }
                , error: function () {
                    //请求异常回调
                }
            });
        }
    };
    exports('new_better_upload', new_better_upload);
});