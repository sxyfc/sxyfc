var page = 2;
var page_loading = true;

$(function () {
    //todo : load chat history

    mhcms_ajax._ajax({
        url : history_service ,
        control: 'get_history',
        fromData: {"liveid": loginInfo.liveid, "page": 1, "time": loginInfo.nowtime},
        success: function (data) {
            var str = '';
            $.each(data, function (index, value) {
                var start = new Date(value.createtime * 1000);
                var times = start.getFullYear() + '-' + (start.getMonth() + 1) + '-' + start.getDate() + ' ' + (start.getHours() < 10 ? '0' + start.getHours() : start.getHours()) + ':' + (start.getMinutes() < 10 ? '0' + start.getMinutes() : start.getMinutes()) + ':' + (start.getSeconds() < 10 ? '0' + start.getSeconds() : start.getSeconds());
                var adminstr = '';
                if (roomadmin.indexOf('"' + value.user.replace('mhcms_live_user_', '') + '"') > 0) {
                    adminstr = '<span class="sec"> (管理员) </span>';
                }
                if (value.customtype == 'red') {
                    str += '<li class="redbao"><div class="chat_left"><div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name" username="' + value.nickname + '">' + value.nickname + adminstr + '</div><div class="char_message red_message"><div class="red-red" value="' + value.id + '"><img src="http://v.weiya.tv/Public/Home/image/hongbao.png" height="45px"><div class="red-cent"><p class="red-title">' + value.content.message + '</p><p>领取红包</p></div></div><div class="send"></div><div class="red_name">' + value.nickname + '的直播间红包</div></div></div></li>';
                } else if (value.customtype == 'reward' || value.customtype == 'getred' || value.customtype == 'gift' || value.customtype == 'getred') {
                    str += '<p class="red-tishi">' + value.content + '</p>';
                } else {
                    if (value.customtype == 'face') {

                        value.content = content2emo(value.content);
                    }
                    str += '<li id="old_msg_' + value.id + '"><div class="chat_left">';
                    if (value.user == loginInfo.identifier) {
                        str += '<div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + adminstr + '</div><div class="char_message">' + value.content + '<div class="send"></div><i class="iconfont close">&#xe629;</i></div></div></li>';
                    } else {

                        if (roomadmin.indexOf('"' + value.user.replace('mhcms_live_user_', '') + '"') > 0) {
                            str += '<div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + '<span class="sec"> (管理员) </span></div><div class="char_message">' + value.content + '<div class="send"></div></div></div></li>';
                        } else {
                            if (loginInfo.isadmin == 1) {
                                str += '<div class="head_portrait clickhead" user="' + value.user + '" username="' + value.nickname + '"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + '</div><div class="char_message">' + value.content + '<div class="send"></div><i class="iconfont close">&#xe629;</i></div></div></li>';
                            } else {
                                str += '<div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + '</div><div class="char_message">' + value.content + '<div class="send"></div></div></div></li>';
                            }
                        }
                    }
                }
            });
            $('#msghistory').prepend(str);
            $('.chat_title').scrollTop($('.chat_title').get(0).scrollHeight - $('.chat_title').height());
        }
    });

    //用户是否禁言
    if (app_user.is_jingyan == 1) {
        $('.footer_cover').show();
        $('#send_msg_text').attr('readonly', 'true');
        $('#send_msg_text').attr('placeholder', '您已被禁言');
    }

    //全员禁言
    if (loginInfo.allshutup == "1") {
        $(".bannerdbtn").children('em').removeClass('btn-movel').addClass('btn-mover').parent().css({backgroundColor: '#31ac82'});
        if (isadmin == 0) {
            $('.footer_cover').show();
        }
        $('#send_msg_text').attr('readonly', 'true');
        $('#send_msg_text').attr('placeholder', '全员禁言中');
    } else {
        $(".bannerdbtn").children('em').removeClass('btn-mover').addClass('btn-movel').parent().css({backgroundColor: '#ccc'});
    }

    $(".bannerdbtn").on('click', function (event) {
        if ($(this).children('em').hasClass('btn-mover')) {
            $(this).children('em').removeClass('btn-mover').addClass('btn-movel').parent().css({
                backgroundColor: '#ccc'
            });
        } else {
            $(this).children('em').removeClass('btn-movel').addClass('btn-mover').parent().css({
                backgroundColor: '#31ac82'
            });
        }
    });


    //todo :scroll up to fetch history
    /**
    $('.chat_title').on('scroll', function (event) {
        var $this = $(this), viewH = $(this).height(), contentH = $(this).get(0).scrollHeight, scrollTop = $(this).scrollTop(); //滚动高度
        var h = contentH - viewH;
        if (scrollTop == 0 && page_loading == true) {
            page_loading = false;
            mhcms_ajax._ajax({
                control: 'get_history',
                fromData: {"liveid": loginInfo.liveid, "page": page, "time": loginInfo.nowtime},
                success: function (data) {
                    var str = '';
                    $.each(data, function (index, value) {
                        var start = new Date(value.createtime * 1000);
                        var times = start.getFullYear() + '-' + (start.getMonth() + 1) + '-' + start.getDate() + ' ' + (start.getHours() < 10 ? '0' + start.getHours() : start.getHours()) + ':' + (start.getMinutes() < 10 ? '0' + start.getMinutes() : start.getMinutes()) + ':' + (start.getSeconds() < 10 ? '0' + start.getSeconds() : start.getSeconds());
                        var adminstr = '';
                        if (roomadmin.indexOf('"' + value.user.replace('mhcms_live_user_', '') + '"') > 0) {
                            adminstr = '<span class="sec"> (管理员) </span>';
                        }
                        if (value.customtype == 'red') {
                            str += '<li class="redbao"><div class="chat_left"><div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name" username="' + value.nickname + '">' + value.nickname + adminstr + '</div><div class="char_message red_message"><div class="red-red" value="' + value.id + '"><img src="http://v.weiya.tv/Public/Home/image/hongbao.png" height="45px"><div class="red-cent"><p class="red-title">' + value.content.message + '</p><p>领取红包</p></div></div><div class="send"></div><div class="red_name">' + value.nickname + '的直播间红包</div></div></div></li>';
                        } else if (value.customtype == 'reward' || value.customtype == 'getred' || value.customtype == 'gift' || value.customtype == 'getred') {
                            str += '<p class="red-tishi">' + value.content + '</p>';
                        } else {
                            if (value.customtype == 'face') {
                                value.content = content2emo(value.content);
                            }
                            str += '<li id="old_msg_' + value.id + '"><div class="chat_left">';
                            if (value.user == loginInfo.identifier) {
                                str += '<div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + adminstr + '</div><div class="char_message">' + value.content + '<div class="send"></div><i class="iconfont close">&#xe629;</i></div></div></li>';
                            } else {
                                if (roomadmin.indexOf('"' + value.user.replace('mhcms_live_user_', '') + '"') > 0) {
                                    str += '<div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + '<span class="sec"> (管理员) </span></div><div class="char_message">' + value.content + '<div class="send"></div></div></div></li>';
                                } else {
                                    if (loginInfo.isadmin == 1) {
                                        str += '<div class="head_portrait clickhead" user="' + value.user + '" username="' + value.nickname + '"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + '</div><div class="char_message">' + value.content + '<div class="send"></div><i class="iconfont close">&#xe629;</i></div></div></li>';
                                    } else {
                                        str += '<div class="head_portrait"><img src="' + value.headurl + '"></div><i class="iconfont" value="' + value.user + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + value.nickname + '</div><div class="char_message">' + value.content + '<div class="send"></div></div></div></li>';
                                    }
                                }
                            }
                        }
                    });
                    $('#msghistory').prepend(str);
                    $('.chat_title').scrollTop(($this.get(0).scrollHeight - $this.height()) - h);
                    if (data.length > 0) {
                        page_loading = true;
                        page++;
                    }
                }
            });
        }
    });
     */


    $('body').on('click', '.char_message>img:not(.smallface_img)', function (event) {
        event.preventDefault();
        var imgArray = [];
        var thisimg = $(this).attr('src');
        $('.char_message>img:not(.smallface_img)').each(function (index, el) {
            var itemSrc = $(this).attr('src');
            imgArray.push(itemSrc);
        });
        wx.previewImage({
            current: thisimg,
            urls: imgArray
        });
    });
    //聊天框
    $(document).on('click', '.chat_title', function (event) {
        event.preventDefault();
        $(".allgift").slideUp('fast');
        $(".browlist").slideUp('fast');
        $(".showfunction").slideUp('fast');
        hideflag = true;
    }).on('click', '.btnicont', function (event) {

        //表情
        event.preventDefault();
        $('.grap').hide();
        if (hideflag) {
            $(".browlist").slideDown('fast');
            hideflag = false;
        } else {
            if ($('.showfunction').css('display') == 'block' || $('.allgift').css('display') == 'block') {
                $(".allgift").slideUp('fast');
                $(".showfunction").slideUp('fast');
                $(".browlist").slideDown('fast');
                hideflag = false;
            } else {
                $(".browlist").slideUp('fast');
                hideflag = true;
            }
        }
        //加号
    }).on('click', '.showfun', function (event) {
        event.preventDefault();
        $('.grap').hide();
        if (hideflag) {
            $('.showfunction').slideDown('fast');
            hideflag = false;
        } else {
            if ($('.allgift').css('display') == 'block' || $('.browlist').css('display') == 'block') {
                $(".allgift").slideUp('fast');
                $(".browlist").slideUp('fast');
                $('.showfunction').slideDown('fast');
                hideflag = false;
            } else {
                $('.showfunction').slideUp('fast');
                hideflag = true;
            }
        }
        //礼物
    }).on('click', '.gift', function (event) {
        event.preventDefault();
        $('.grap').show().css("z-index", "5");
        $('.gift-list li').each(function (index, el) {
            if ($(this).hasClass('select')) {
                var summoney = $('.countKuang').val() * parseInt($(this).find('.moneynum').html());
                $('.money').html('￥' + summoney);
            } else {
                $('.countKuang').val('1');
            }
        });
        if (hideflag) {
            $(".allgift").slideDown('fast');
            hideflag = false;
        } else {
            if ($('.browlist').css('display') == 'block' || $('.showfunction').css('display') == 'block') {
                $(".browlist").slideUp('fast');
                $('.showfunction').slideUp('fast');
                $(".allgift").slideDown('fast');
                hideflag = false;
            } else {
                $(".allgift").slideUp('fast');
                $('.grap').hide();
                hideflag = true;
            }
        }
        //获得焦点
    }).on('focus', '.text-input', function (event) {
        event.preventDefault();
        $('.cont-right').hide();
        if ($(this).val().length >= 1) {
            $("#mune").empty().removeClass(' showfun').addClass('btn-sub').html('发送');
        }
        ;
        $(".allgift").slideUp('fast');
        $(".browlist").slideUp('fast');
        $(".showfunction").slideUp('fast');
        $(".grap").slideUp('fast');
        $(".focus-on").slideUp('fast');


        var h = $(document).height()-$(window).height();
        $(document).scrollTop(h);


        $(this).scrollIntoViewIfNeeded();
        document.activeElement.scrollIntoViewIfNeeded();
        //document.activeElement.scrollIntoView();
        hideflag = true;
        //发送消息按钮
    }).on('click', '.btn-sub', function (event) {
        event.preventDefault();
        if ($('.text-input').val().length == 0 || $('.text-input').val().match(/^\s+$/g)) {
            $(document).minTipsBox({
                tipsContent: '请输入内容',
                tipsTime: 1
            });
            $('.text-input').val('');
            return;
        } else {
            if (loginInfo.allshutup == "1") {
                $(document).minTipsBox({
                    tipsContent: '全员禁言中',
                    tipsTime: 1
                });
                return;
            }
            if (app_user.is_blacklist == "1") {
                $(document).minTipsBox({
                    tipsContent: '你已经被拉入黑名单',
                    tipsTime: 1
                });
                return;
            }
            if (app_user.is_jingyan == "1") {
                $(document).minTipsBox({
                    tipsContent: '你已被管理员禁言',
                    tipsTime: 1
                });
                return;
            }
            onSendMsg();
        }
        //失去焦点
    }).on('blur', '.text-input', function (event) {
        event.preventDefault();
        $('.cont-right').show();
        if ($('.text-input').val() == '') {
            $('.btn-sub').one('click',
                function (e) {
                    e.preventDefault();
                    if ($.trim($('.text-input').val()) === '') {
                        $(document).minTipsBox({
                            tipsContent: '请输入内容',
                            tipsTime: 1
                        });
                        return false;
                    }
                });
            $("#mune").empty().removeClass('btn-sub').addClass(' showfun').html();
        }
        //礼物列表
    }).on('click', '.gift-list li', function (event) {
        event.preventDefault();
        var $this = $(this);
        var item = $this.index();
        $this.addClass("select").siblings().removeClass("select");
        var onemoney = parseInt($this.find('.moneynum').html());
        $('.countKuang').val(1);
        $(".zhuang").slideDown('fast');
        $('.money').html(onemoney + '个' + bi_name);
        //加
    }).on('click', '.open_red', function () {
        var temp_pd = window.localStorage.getItem("SendRedBag");
        if (temp_pd) {
            temp_pd = JSON.parse(temp_pd);
            temp_pd.bagType = temp_pd.bagType == 1 ? 2 : 1;
            $("#bagAmount").val(parseInt(temp_pd.nums));
            $("#bagMessage").val(temp_pd.message);
            if (temp_pd.bagType == 1) {
                $(".redtype .typemsg").html(zbRedBag.randomText);
                $("#changeRedType").html(zbRedBag.fixedBtn);
                $(".totalmoney").html('总金额<i class="iconfont">&#xe631;</i>');
                $("#bagMoney").val(temp_pd.total_fee);
                var bi_num = (temp_pd.total_fee * bi_lv).toFixed(2);
                bag_money_show.text('共需: ' + bi_num + " 个" + bi_name);
                calc();
            } else if (temp_pd.bagType == 2) {
                $(".redtype .typemsg").html(zbRedBag.fixedText);
                $("#changeRedType").html(zbRedBag.randomBtn);
                $(".totalmoney").html('单个金额');
                $("#bagMoney").val(temp_pd.one_fee);//
                var bi_num = (temp_pd.total_fee * bi_lv).toFixed(2);
                bag_money_show.text('共需: ' + bi_num + " 个" + bi_name);
                calc();
            }
        }
        $(".sendredbagwin").show();
    }).on('click', '.open_shang', function () {
        personal.uid = founder.uid;
        personal.nickname = founder.nickname;
        personal.headurl = founder.headurl;
        $('.redbagBox .live_redbag').find('.live_headpic').children('img').attr('src', personal.headurl);
        $('.redbagBox .live_redbag').find('.live_towho').eq(0).html(personal.nickname);
        //主播红包
        $(".grap").hide();
        $(".redbagBox").show();

        $(".zhuang").slideUp('fast');
        $(".allgift").slideUp('fast');
        hideflag = true;
    }).on('click', '.jia', function (event) {
        var num = $('.countKuang').val();
        $('.countKuang').val(++num);
        $('.gift-list li').each(function (index, el) {
            if ($(this).hasClass('select')) {
                var thismoney = parseInt($(this).find('.moneynum').html());
                $('.money').html(thismoney * num + '个' + bi_name);
            }
        });
        //减
    }).on('click', '.jian', function (event) {
        var num = $('.countKuang').val();
        --num;
        if (num < 2) {
            num = 1;
            $('.countKuang').val(num);
        } else {
            $('.countKuang').val(num);
        }
        $('.gift-list li').each(function (index, el) {
            if ($(this).hasClass('select')) {
                var thismoney = parseInt($(this).find('.moneynum').html());
                $('.money').html(thismoney * num + '个' + bi_name);
            }
        });
        //更多
    }).on('click', '.functions', function (event) {
        event.preventDefault();
        $(".showfunction").slideUp('fast');
        $(".grap").toggle().css("z-index", "5");
        $(".function-all").slideDown('fast');
        //红包个数
    }).on("input", "#bagAmount", function () {
        calc();
        //总金额
    }).on("input", "#bagMoney", function () {
        calc();
        //留言
    }).on("input", "#bagMessage", function () {
        var _val = $(this).val();
        if (_val.length > 25) {
            $(this).val(_val.substring(0, 25));
        }
        calc();
        //群红包类型
    }).on("click", "#changeRedType", function () {
        pd.bagType = pd.bagType == 1 ? 2 : 1;
        if (pd.bagType == 1) {
            $(".redtype .typemsg").html(zbRedBag.randomText);
            $("#changeRedType").html(zbRedBag.fixedBtn);
            // $(".totalmoney i").show();
            $(".totalmoney").html('总金额<i class="iconfont">&#xe631;</i>');
            calc();
        } else if (pd.bagType == 2) {
            $(".redtype .typemsg").html(zbRedBag.fixedText);
            $("#changeRedType").html(zbRedBag.randomBtn);
            // $(".totalmoney i").hide();
            $(".totalmoney").html('单个金额');
            calc();
        }
        //群红包遮罩
    }).on("click", ".redbagmask", function () {
        $(".sendredbagwin").hide();
        $('.grap').hide();
        //群红包取消
    }).on("click", ".btn-cancel", function () {
        $(".sendredbagwin").hide();
        $('.grap').hide();
        //群红包确认支付
    }).on("click", "#btnSendRedBag", function () {
        calc();
        if (!red_ispost) {
            red_ispost = true;
            pd.bagMessage = $("#bagMessage").val() || $("#bagMessage").attr("placeholder");
            var qunredjson = {
                liveid: liveid || 0,
                total_fee: pd.bagMoney,
                one_fee: $("#bagMoney").val() || 1,
                user: app_user.userid,
                rtype: pd.bagType,
                //红包类型
                nums: pd.bagAmount,
                //红包数量
                message: pd.bagMessage //红包留言
            };

            ajax_payqunred(qunredjson, function (data) {
                red_ispost = false;
                if (data.message.type == 1) {
                    window.localStorage.clear();
                    sendred(data.message.msg_id, data.message.data);
                    $(document).minTipsBox({
                        tipsContent: '发放成功',
                        tipsTime: 1
                    });
                } else {
                    window.localStorage.setItem("SendRedBag", JSON.stringify(qunredjson));
                    var bi_num = (pd.bagMoney * bi_lv).toFixed(2);
                    mui.confirm(bi_name + '不足、发放红包共需要' + bi_num + '个' + bi_name, '温馨提示', ['去充值', '我没钱'], function (e) {
                        if (e.index == 0) {
                            red_send_fail = 1;
                            var i = mhcms_ajax._querystring('i');
                            var j = mhcms_ajax._querystring('j');
                            window.location.href = './index.php?i=' + i + '&j=' + j + '&c=entry&do=pay_bi&m=meepo_live&liveid=' + liveid + '&money=' + parseInt(pd.bagMoney);
                        }
                    });
                }
            }, function (errmsg) {
                red_ispost = false;
                window.localStorage.clear();
                $(document).minTipsBox({
                    tipsContent: errmsg,
                    tipsTime: 1
                });
            });
        }
        //单个红包(列表)
    }).on('click', '.chat_left .iconfont', function (event) {
        personal.uid = $(this).attr('value').replace('mhcms_live_user_', '');
        personal.nickname = $(this).parents('.chat_left').next().find('.char_name').html().split('<span')[0];
        personal.headurl = $(this).prev().children('img').attr('src');
        /*$('.redenvelope .RedPack_Info').find('.packimgs').children('img').attr('src', personal.headurl);
        $('.redenvelope .RedPack_Info').find('p').eq(0).html(personal.nickname);
        $(".mask").show();
        $(".redenvelope").show();*/
        $('.redbagBox .live_redbag').find('.live_headpic').children('img').attr('src', personal.headurl);
        $('.redbagBox .live_redbag').find('.live_towho').eq(0).html(personal.nickname);
        //主播红包
        $(".redbagBox").show();
        //红包遮罩层关闭
    }).on('click', '.mask', function (event) {
        $(".redenvelope").hide();
        $(".Banned").hide();
        $(".grap").hide();
        //单人红包关闭
    }).on('click', '.cose,.redbag_cancel', function (event) {
        //$(".redenvelope").hide();
        $(".redbagBox").hide();
        //禁言管理关闭
    }).on('click', '.gene_cancel', function (event) {
        //$(".redenvelope").hide();
        $(".otherRedmoneyBox").hide();
        //禁言管理关闭
    }).on('click', '.colose', function (event) {
        $(".Bannedmages").hide();
        $(".showfunction").slideUp('fast');
        $(".grap").hide();
        hideflag = true;
        //其他金额弹出
    }).on('click', '.other-m', function (event) {
        $(".two-top").show();
        //其他金额遮罩层关闭
    }).on('click', '.live_othermoney', function (event) {
        event.preventDefault();
        $(".otherRedmoneyBox").show();
        //其他金额遮罩层关闭
    }).on('click', '.masks', function (event) {
        $(".two-top").hide();
        $('.redlist').hide();
        //头像点击底部向上滑动(拉黑、禁言)
    }).on('click', '.clickhead', function (event) {//拉黑、禁言
        event.preventDefault();
        var user = $(this).attr('user');
        var username = $(this).attr("username");
        var options = {
            'GroupId': avChatRoomId
        };
        webim.shutuplist(options, function (resp) {

            if (resp.ActionStatus == 'OK') {
                var json = JSON.stringify(resp.ShuttedUinList);
                if (json.indexOf('"' + user + '"') > 0) {
                    $('#shutup').attr('onclick', 'cancelshutup("' + user + '","' + username + '")');
                    $('#shutup').html('取消禁言');
                } else {
                    $('#shutup').attr('onclick', 'shutup("' + user + '","' + username + '")');
                    $('#shutup').html('禁言');
                }

            } else {
                $('#shutup').remove();
            }
            is_black(user);
        }, function (err) {
            console.log(err);
            $('#shutup').remove();
        });


        //关闭底部
    }).on('click', '.opclose', function (event) {
        $(".grap").hide();
        $(".operation").slideUp('fast');
        //最外遮罩层关闭事件
    }).on('click', '.grap', function (event) {
        $('.Banned').hide();
        $(".function-all").slideUp('fast');
        $(".showfunction").slideUp('fast');
        $(".focus-on").hide();
        $(".collection").hide();
        $(".operation").slideUp('fast');
        $('.Banned').hide();
        $(this).removeClass("graps").hide();
        $(".allgift").slideUp('fast');
        hideflag = true;
        //公众号关注弹出
    }).on('click', '.focus-onto', function (event) {
        $(".grap").css({"z-index": "7"});
        $(".grap").toggle();
        $(".focus-on").toggle();
        //消息取消、撤回
    }).on('click', '.goto_index', function (event) {
        var i = mhcms_ajax._querystring('i');
        var j = mhcms_ajax._querystring('j');
        window.location.href = './index.php?i=' + i + '&j=' + j + '&c=entry&do=index&m=meepo_live';
        //消息取消、撤回
    }).on('click', '.close', function (event) {
        var chehui = $(this);
        mui.confirm('撤回此消息？', '警告', ['撤回', '取消'], function (e) {
            if (e.index == 0) {
                if (chehui.parents("li")[0].id.length > 0) {
                    if (chehui.parents("li")[0].id.length > 0 && chehui.parents("li")[0].id.indexOf('seq') != -1) {
                        var msg_id = chehui.parents("li").find('.new_msg_id').attr('data-msgid');
                        var close_type = 'close_new';
                    } else {
                        var msg_id = chehui.parents("li")[0].id.replace('old_msg_', '');
                        var close_type = 'close_old';
                    }
                    sendclose(close_type, chehui.parents("li")[0].id.replace('seq', ''), msg_id);
                }
            }
        })
        //收藏
    }).on('click', '.function-live ul li', function (event) {
        var item = $(this).index();
        if (item == 0) {
            $(".grap").show().addClass("graps");
            $(".collection").toggle();
        } else if (item == 3) {
        } else if (item == 1) {
            $(".grap").show();
            $(".focus-on").show();
            $(".function-all").slideUp('fast');
        }
    }).on('input', '.text-input', function (event) {
        event.preventDefault();
        if ($(this).val().length >= 1) {
            $("#mune").empty().removeClass('icon showfun').addClass('btn-sub').html('发送');
        } else {
            $("#mune").empty().removeClass('btn-sub').addClass('icon showfun').html();
        }
        //点击红包(显示)
    }).on('click', '.red_message', function (event) {
        event.preventDefault();
        var $this = $(this);
        $('.redlist').find('.alllist').children('h5').nextAll().remove();
        if ($this.hasClass('red_message')) {
            red.redid = $this.find('.red-red').attr('value');
            red.toredName = $this.prev().attr('username');
            red.toredHead = $this.parents('.redbao').find('.head_portrait').children('img').attr('src');
        } else if ($this.hasClass('lookred')) {
            red.redid = $this.attr('value');
        }
        if (red.redid > 0) {
            mhcms_ajax._ajax({
                control: 'click_red',
                fromData: {
                    red_id: red.redid,
                    liveid: liveid
                },
                success: function (json) {
                    if (json.errno == -1) {
                        $(document).minTipsBox({
                            tipsContent: 'error',
                            tipsTime: 1
                        });
                        return;
                    }
                    red.redtitle = json.data.message;
                    red.redtype = json.data.type == 1 ? '随机' : '固定';
                    red.redMoney = json.data.fee;
                    red.rednum = json.data.nums;
                    if ($this.hasClass('lookred')) {
                        red.toredName = json.user.nickname;
                        red.toredHead = json.user.avatar;
                    }
                    ;
                    if (json.hasget == 0) {
                        $('.open').show();
                        $('.open').find('.packimgs').children('img').attr('src', red.toredHead);
                        $('.open').find('.RedPack_Info').children('p').eq(0).html(red.toredName);
                        $('.open').find('.RedPack_Info').children('p').eq(1).html('发了一个红包，金额' + red.redtype + '');
                        if (json.status == 1) {
                            $('.open').find('.openkai').show();
                            $('.open').find('.RedPack_Info').children('p').eq(2).html(red.redtitle);
                            $('.mar10').css({"margin-top": "85px"});
                        } else {
                            $('.open').find('.openkai').hide();
                            $('.open').find('.RedPack_Info').children('p').eq(2).html('手慢了,红包派完了');
                            $('.mar10').css({"margin-top": "0px"});
                        }
                    } else {
                        $('.open').hide();
                        $('.redlist').show();
                        $('.redlist').find('.alllist').children('h5').nextAll().remove();
                        $('.redlist').find('.packimgs').children('img').attr('src', red.toredHead);
                        var type = red.redtype == 1 ? '<i class="iconfont">&#xe631;</i>' : '';
                        $('.redlist').find('.RedPack_Info').children('p').eq(0).html(red.toredName + '的红包' + type);
                        $('.redlist').find('.RedPack_Info').children('p').eq(1).html(red.redtitle);
                        // $('.redlist').find('.RedPack_Info').children('p').eq(2).html();
                        $('.redlist').find('.alllist').children('h5').html(red.rednum + '个红包,共' + red.redMoney + '元');
                        var str = '';
                        if (json.list.length > 0) {
                            $.each(json.list, function (index, value) {
                                if (value.user == loginInfo.identifier) {
                                    $('.redlist').find('.RedPack_Info').children('p').eq(2).html(value.money + '元');
                                }
                                var start = new Date(value.createtime * 1000);
                                var times = (start.getMonth() + 1) + '-' + start.getDate() + ' ' + (start.getHours() < 10 ? '0' + start.getHours() : start.getHours()) + ':' + (start.getMinutes() < 10 ? '0' + start.getMinutes() : start.getMinutes());
                                str += '<li><img src="' + value.avatar + '"><div><p>' + value.nickname + '</p><p>' + times + '</p></div><span>' + value.money + '元</span></li>';
                            });
                            $('.redlist').find('.alllist').children('h5').after(str);
                        }
                    }
                }
            });
        } else {
            $(document).minTipsBox({
                tipsContent: 'error',
                tipsTime: 1
            });
        }
    }).on('click', '.redcenters .cose', function (event) {
        $('.Banned').hide();
        $('.grap').removeClass("graps").hide();
    });
    $('.main').on('click', '#guan', function (event) {
        $('.open').hide();
        //红包开
    }).on('click', '.openkai', function (event) {
        $(this).addClass('rey');
        $('.redlist').find('.alllist').children('h5').nextAll().remove();
        var type = red.redtype == 1 ? '<i class="iconfont">&#xe631;</i>' : '';
        $('.redlist').find('.packimgs').children('img').attr('src', red.toredHead);
        $('.redlist').find('.RedPack_Info').children('p').eq(0).html(red.toredName + '的红包' + type);
        $('.redlist').find('.RedPack_Info').children('p').eq(1).html(red.redtitle);
        $('.redlist').find('.alllist').children('h5').html(red.rednum + '个红包,共' + red.redMoney + '元');
        if (red.redid > 0) {
            var i = mhcms_ajax._querystring('i');
            var j = mhcms_ajax._querystring('j');
            var url = './index.php?i=' + i + '&j=' + j + '&c=entry&do=get_red&m=meepo_live';
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    red_id: red.redid,
                    liveid: liveid,
                    nickname: app_user.nickname,
                    user: loginInfo.identifier,
                    from_nickname: red.toredName,
                },
                success: function (json) {
                    if (json.errno == -1) {
                        $(document).minTipsBox({
                            tipsContent: 'error',
                            tipsTime: 1
                        });
                        return;
                    }
                    var str = '';

                    if (json.data.length > 0) {
                        $.each(json.data, function (index, value) {
                            if (value.openid == app_user.openid) {
                                $('.redlist').find('.RedPack_Info').children('p').eq(2).html(value.money + '元');
                            }
                            var start = new Date(value.createtime * 1000);
                            var times = (start.getMonth() + 1) + '-' + start.getDate() + ' ' + (start.getHours() < 10 ? '0' + start.getHours() : start.getHours()) + ':' + (start.getMinutes() < 10 ? '0' + start.getMinutes() : start.getMinutes());
                            str += '<li><img src="' + value.avatar + '"><div><p>' + value.nickname + '</p><p>' + times + '</p></div><span>' + value.money + '元</span></li>';
                        });
                        $('.redlist').find('.alllist').children('h5').after(str);
                    }
                    $('.open').hide();
                    $('.redlist').show();
                    $('.openkai').removeClass('rey');
                    sendgetred(json.content);
                },
                error: function () {
                    $(document).minTipsBox({
                        tipsContent: 'error',
                        tipsTime: 1
                    });
                }
            });
        } else {
            $(document).minTipsBox({
                tipsContent: 'error',
                tipsTime: 1
            });
        }

        //关闭抢红包列表
    }).on('click', '#redlistcose', function (event) {
        $('.redlist').hide();
    }).on('click', '.mar10', function (event) {
        event.preventDefault();
        $('.open').hide();
        $('.redlist').show();
        $('.redlist').find('.alllist').children('h5').nextAll().remove();
        $('.redlist').find('.packimgs').children('img').attr('src', red.toredHead);
        var type = red.redtype == 1 ? '<i class="iconfont">&#xe631;</i>' : '';
        $('.redlist').find('.RedPack_Info').children('p').eq(0).html(red.toredName + '的红包' + type);
        $('.redlist').find('.RedPack_Info').children('p').eq(1).html(red.redtitle);
        // $('.redlist').find('.RedPack_Info').children('p').eq(2).html();
        $('.redlist').find('.alllist').children('h5').html(red.rednum + '个红包,共' + red.redMoney + '元');
        mhcms_ajax._ajax({
            control: 'look_red',
            fromData: {
                red_id: red.redid,
                liveid: liveid
            },
            success: function (json) {
                if (json.errno == -1) {
                    $(document).minTipsBox({
                        tipsContent: 'error',
                        tipsTime: 1
                    });
                    return;
                }
                var str = '';
                if (json.list.length > 0) {
                    $.each(json.list, function (index, value) {
                        if (value.openid == app_user.openid) {
                            $('.redlist').find('.RedPack_Info').children('p').eq(2).html(value.money + '元');
                        }
                        var start = new Date(value.createtime * 1000);
                        var times = (start.getMonth() + 1) + '-' + start.getDate() + ' ' + (start.getHours() < 10 ? '0' + start.getHours() : start.getHours()) + ':' + (start.getMinutes() < 10 ? '0' + start.getMinutes() : start.getMinutes());
                        str += '<li><img src="' + value.avatar + '"><div><p>' + value.nickname + '</p><p>' + times + '</p></div><span>' + value.money + '元</span></li>';
                    });
                    $('.redlist').find('.alllist').children('h5').after(str);
                }
            }
        });
    });
    $(".text-input").keyup(function (event) {
        if (event.keyCode == 13) {
            if ($('.text-input').val().length == 0 || $('.text-input').val().match(/^\s+$/g)) {
                $(document).minTipsBox({
                    tipsContent: '请输入内容',
                    tipsTime: 1
                });
                $('.text-input').val('');
            } else {
                onSendMsg();
            }
        }
    });

    $(".bannerdbtn").on('click', function () {
        if (loginInfo.allshutup == '0') {//open shutup
            allshutup(0);
        } else {//close shutup
            allshutup(1);
        }
    });
    //红包(关)

    //打赏红包列表点击
    $('.c-money li,.live_redbaglist li').on('click', function (event) {
        event.preventDefault();
        var $this = $(this);
        var reward = {
            touid: personal.uid || 0,
            liveid: liveid || 0,
            money: $this.attr('data-money') || 0,
            user_nickname: app_user.nickname,
            touser_nickname: personal.nickname
        };
        ajax_payred(reward, function (data) {
            if (data.message.type == 1) {
                sendreward(data.message.content, data.message.touid);
                $(document).minTipsBox({
                    tipsContent: '打赏成功',
                    tipsTime: 1
                });
            } else {
                var bi_num = (reward.money * bi_lv).toFixed(2);
                mui.confirm(bi_name + '不足、打赏红包共需要' + bi_num + '个' + bi_name, '温馨提示', ['去充值', '我没钱'], function (e) {
                    //mui.confirm(bi_name+'不足', '温馨提示',['去充值','我没钱'], function(e) {
                    if (e.index == 0) {
                        var i = mhcms_ajax._querystring('i');
                        var j = mhcms_ajax._querystring('j');
                        window.location.href = './index.php?i=' + i + '&j=' + j + '&c=entry&do=pay_bi&m=meepo_live&liveid=' + liveid + '&money=' + reward.money;
                    }
                });
            }
        }, function (errmsg) {
            $(document).minTipsBox({
                tipsContent: errmsg,
                tipsTime: 1
            });
        });
    });

    //其他金额(输入框)
    $('#othermoney').on('input', function (event) {
        var money = $(this).val();
        if (/\s+/.test(money)) {
            return;
        }
        ;
        if (money >= ds_minMoney) {
            $('.font-s').removeAttr('disabled');
        } else {
            $(".font-s").attr("disabled", "disabled");
        }
    });
    $('#qita_money').on('input', function (event) {
        var money = $(this).val();
        //console.log(money);
        if (/\s+/.test(money)) {
            return;
        }
        ;
        if (money >= ds_minMoney) {
            $('.gene_confirm').removeAttr('disabled');
        } else {
            $(".gene_confirm").attr("disabled", "disabled");
        }
    });
    //其他金额确认按钮
    $('.font-s').on('click', function (event) {
        var reward = {
            touid: personal.uid || 0,
            liveid: liveid || 0,
            money: parseInt($('#othermoney').val()) || 0,
            user_nickname: app_user.nickname,
            touser_nickname: personal.nickname
        };
        ajax_payred(reward, function (data) {
            if (data.message.type == 1) {
                sendreward(data.message.content, data.message.touid);
                $(document).minTipsBox({
                    tipsContent: '打赏成功',
                    tipsTime: 1
                });
            } else {
                var bi_num = (reward.money * bi_lv).toFixed(2);
                mui.confirm(bi_name + '不足、打赏红包共需要' + bi_num + '个' + bi_name, '温馨提示', ['去充值', '我没钱'], function (e) {
                    //mui.confirm(bi_name+'不足', '温馨提示',['去充值','我没钱'], function(e) {
                    if (e.index == 0) {
                        var i = mhcms_ajax._querystring('i');
                        var j = mhcms_ajax._querystring('j');
                        window.location.href = './index.php?i=' + i + '&j=' + j + '&c=entry&do=pay_bi&m=meepo_live&liveid=' + liveid + '&money=' + reward.money;
                    }
                });
            }
        }, function (errmsg) {
            $(document).minTipsBox({
                tipsContent: errmsg,
                tipsTime: 1
            });
        });
    });
    $('.gene_confirm').on('click', function (event) {
        if (parseInt($('.money_count').val()) <= ds_minMoney) {
            return;
        }
        var reward = {
            touid: personal.uid || 0,
            liveid: liveid || 0,
            money: parseInt($('.money_count').val()) || 0,
            user_nickname: app_user.nickname,
            touser_nickname: personal.nickname
        };
        ajax_payred(reward, function (data) {
            if (data.message.type == 1) {
                sendreward(data.message.content, data.message.touid);
                $(document).minTipsBox({
                    tipsContent: '打赏成功',
                    tipsTime: 1
                });
            } else {
                var bi_num = (reward.money * bi_lv).toFixed(2);
                mui.confirm(bi_name + '不足、打赏红包共需要' + bi_num + '个' + bi_name, '温馨提示', ['去充值', '我没钱'], function (e) {
                    //mui.confirm(bi_name+'不足', '温馨提示',['去充值','我没钱'], function(e) {
                    if (e.index == 0) {
                        var i = mhcms_ajax._querystring('i');
                        var j = mhcms_ajax._querystring('j');
                        window.location.href = './index.php?i=' + i + '&j=' + j + '&c=entry&do=pay_bi&m=meepo_live&liveid=' + liveid + '&money=' + reward.money;
                    }
                });
            }
        }, function (errmsg) {
            $(document).minTipsBox({
                tipsContent: errmsg,
                tipsTime: 1
            });
        });
    });
    //礼物支付
    $('.give').on('click', function (event) {
        if ($('.gift-list li.select').length > 0) {
            var gift_li = $('.gift-list li.select');
            var gift_one_bi = parseInt(gift_li.find('.moneynum').text()) || 1;

            var gift_num = parseInt($('.countKuang').val());
            var giftjson = {
                gift_num: gift_num,
                gift_name: gift_li.find('.gift_name').text(),
                gift_img: gift_li.find("img").attr('src'),
                gift_bi: gift_num * gift_one_bi,
                user_nickname: app_user.nickname,
                touid: founder.uid || 0,
                liveid: liveid || 0
            };

            if (gift_num < 1) {
                $(document).minTipsBox({
                    tipsContent: '礼物数量错误',
                    tipsTime: 1
                });
                return;
            }
            ajax_paygift(giftjson, function (data) {
                if (data.message.type == 1) {
                    sendgift(data.message.content, data.message.touid);
                    $(document).minTipsBox({
                        tipsContent: '送礼成功',
                        tipsTime: 1
                    });
                } else {
                    var bi_num = giftjson.gift_bi;
                    mui.confirm(bi_name + '不足、礼物共需要' + bi_num + '个' + bi_name, '温馨提示', ['去充值', '我没钱'], function (e) {
                        //mui.confirm(bi_name+'不足', '温馨提示',['去充值','我没钱'], function(e) {
                        if (e.index == 0) {
                            var gift_money = bi_num / bi_lv;
                            var i = mhcms_ajax._querystring('i');
                            var j = mhcms_ajax._querystring('j');
                            window.location.href = './index.php?i=' + i + '&j=' + j + '&c=entry&do=pay_bi&m=meepo_live&liveid=' + liveid + '&money=' + gift_money;
                        }
                    });
                }
            }, function (errmsg) {
                $(document).minTipsBox({
                    tipsContent: errmsg,
                    tipsTime: 1
                });
            });
        } else {
            $(document).minTipsBox({
                tipsContent: '请先选中礼物',
                tipsTime: 1
            });
        }
    });

    //群红包输入框判断
    function calc() {
        var amountline = $("#bagAmount").closest(".line");
        var moneyline = $("#bagMoney").closest(".line");
        var servicemoneyline = $(".service-money");
        if (/\s+/.test($("#bagAmount").val()) || /\s+/.test($("#bagMoney").val())) {
            return;
        }
        var bagamount = parseInt($("#bagAmount").val()) || 0;
        var bagmoney = parseFloat($("#bagMoney").val()) || 0;
        //随机
        if (pd.bagType == 1) {
            pd.bagMoney = bagmoney.toFixed(2);
            //固定
        } else if (pd.bagType == 2) {
            pd.bagMoney = parseFloat((bagamount * bagmoney).toFixed(2));
        }
        pd.bagAmount = bagamount;

        if (bagamount <= 0) {
            amountline.addClass("line-error");
            msgbar.html(zbRedBag.msg1).show();

        } else {
            amountline.removeClass("line-error");
            msgbar.html("").hide();
        }

        if (pd.bagType == 1) {
            if ((pd.bagMoney / pd.bagAmount) < minMoney) {
                moneyline.addClass("line-error");
                msgbar.html(zbRedBag.msg2).show();
            } else {
                moneyline.removeClass("line-error");
                msgbar.html("").hide();

            }
        } else {
            if (pd.bagMoney < 0.01) {
                moneyline.addClass("line-error");
                msgbar.html(zbRedBag.msg2).show();
            }
        }
        var bi_num = (pd.bagMoney * bi_lv).toFixed(2);
        bag_money_show.text('共需: ' + bi_num + " 个" + bi_name);
        if (pd.bagAmount > 0 && pd.bagMoney > 0) {
            if (pd.bagType == 1) {
                if ((pd.bagMoney / pd.bagAmount) >= minMoney) {
                    $("#btnSendRedBag").removeAttr("disabled");
                    $("#btnSendRedBag").css({"padding": '0'});
                } else {
                    $("#btnSendRedBag").attr("disabled", "disabled");
                }
            } else {
                if (pd.bagMoney >= 0.01) {
                    $("#btnSendRedBag").removeAttr("disabled");
                    $("#btnSendRedBag").css({"padding": '0'});
                } else {
                    $("#btnSendRedBag").attr("disabled", "disabled");
                }
            }

        } else {
            $("#btnSendRedBag").attr("disabled", "disabled");
        }
    }

    $(".readcolse").click(function () {
        $(".reading").hide();
    })
});

function showtipsbox(content) {
    $(document).minTipsBox({
        tipsContent: content,
        tipsTime: 1
    });
}

function content2emo(emo_content) {
    emo_content = emo_content.replace(/\</g, '&lt;');
    emo_content = emo_content.replace(/\>/g, '&gt;');
    emo_content = emo_content.replace(/\n/g, '<br/>');
    return emo_content.replace(/\[emo_([0-9]*)\]/g, function (match) {
        var match = match.replace("[emo_", '');
        match = match.replace("]", '');
        return '<img src="' + webim.Emotions[parseInt(match)][1] + '" class="smallface_img" />';
    });


}

function showshutup() {
    shutuplist();
    $('.Banned').show();
    $(".function-all").slideUp('fast');
    $(".showfunction").slideUp('fast');
    hideflag = true;
}

function allshutup(type) {
    if (type == 0) {//0 开启禁言
        sendallshutup();
        $('#send_msg_text').attr('readonly', 'true');
        $('#send_msg_text').attr('placeholder', '全员禁言中');
        loginInfo.allshutup = 1;
    } else {
        sendallcancel();
        $('#send_msg_text').removeAttr('readonly');
        $('#send_msg_text').attr('placeholder', '和大伙说点什么吧');
        loginInfo.allshutup = 0;
    }
}

function isWX() {
    var u = navigator.userAgent,
        app = navigator.appVersion;
    return u.indexOf("MicroMessenger") > -1;
}

function is_black(u) {
    mhcms_ajax._ajax({
        control: 'check_black',
        fromData: {"liveid": loginInfo.liveid, "user": u},
        success: function (data) {
            if (data.errno == 0) {//是黑名单
                $('#black').attr('onclick', 'black("' + u + '","1")');
                $('#black').html('拉黑');
            } else {//正常用怒
                $('#black').attr('onclick', 'black("' + u + '","0")');
                $('#black').html('恢复白名单');
            }
            $(".grap").show();
            $(".operation").slideDown('fast');
        }
    });

}

function black(u, type) {
    mhcms_ajax._ajax({
        control: 'pull_black',
        fromData: {"liveid": loginInfo.liveid, "user": u, "type": type},
        success: function (data) {
            if (data.errno == 0) {
                if (type == 1) {
                    $(document).minTipsBox({
                        tipsContent: "拉黑成功",
                        tipsTime: 1
                    });
                } else {
                    $(document).minTipsBox({
                        tipsContent: "取消拉黑成功",
                        tipsTime: 1
                    });
                }
                $(".grap").hide();
                $(".operation").slideUp('fast');
            } else {
                $(document).minTipsBox({
                    tipsContent: "操作失败",
                    tipsTime: 1
                });
            }
        }
    });
}

var ajax_payred = function (red_data, fn1, fn2) {
    mhcms_ajax._ajax({
        control: 'pay_red',
        fromData: red_data,
        success: function (data) {
            $(".otherRedmoneyBox").slideUp('fast');
            $('.redbagBox').slideUp('fast');
            if (data.errno == 0) {
                fn1(data);
            } else {
                fn2(data.message);
            }
        }
    });
};
var ajax_paygift = function (gift_data, fn1, fn2) {
    mhcms_ajax._ajax({
        control: 'pay_gift',
        fromData: gift_data,
        success: function (data) {
            $(".allgift").slideUp('fast');
            $(".grap").slideUp('fast');
            if (data.errno == 0) {
                fn1(data);
            } else {
                fn2(data.message);
            }
        }
    });
};
var ajax_payqunred = function (qun_red_data, fn1, fn2) {
    mhcms_ajax._ajax({
        control: 'pay_qunred',
        fromData: qun_red_data,
        success: function (data) {
            $(".sendredbagwin").hide();
            $('.grap').hide();
            if (data.errno == 0) {
                fn1(data);
            } else {
                fn2(data.message);
            }
        }
    });
};
var ajax_usercount = function () {
    mhcms_ajax._ajax({
        control: 'user_count',
        fromData: {"liveid": liveid},
        success: function (data) {
            $("#browsenum").text(data.message);
        }
    });
};
