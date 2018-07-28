var hideflag = true;

function shutuplist() {
    $('.shutuplist').html('');
    var options = {'GroupId': avChatRoomId};
    webim.shutuplist(
        options,
        function (resp) {
            var arr = new Array();
            for (var i = 0; i < resp.ShuttedUinList.length; i++) {
                arr.push(resp.ShuttedUinList[i].Member_Account);
            }
            if (arr.length >= 1) {
                var options2 = {
                    'To_Account': arr,
                    'TagList': ['Tag_Profile_IM_Nick', 'Tag_Profile_IM_Image']
                };
                webim.getProfilePortrait(options2,
                    function (resp2) {
                        var str = '';
                        for (var i = 0; i < resp2.UserProfileItem.length; i++) {
                            str += '<li><img src="' + resp2.UserProfileItem[i].ProfileItem[1].Value + '"/><div><p>' + resp2.UserProfileItem[i].ProfileItem[0].Value + '</p></div><span onclick=cancelshutup("' + arr[i] + '","' + resp2.UserProfileItem[i].ProfileItem[0].Value + '")>移除</span></li>';

                        }
                        $('.shutuplist').append(str);
                    },
                    function (err) {
                        // alert(err.ErrorInfo);
                    }
                );
            } else {
                str = '<li class="no_shut_up_list">暂无禁言用户</li>';
                $('.shutuplist').append(str);
            }


        }, function (err) {
            // alert(err.ErrorInfo);
        }
    );
}

function shutup(user, username) {
    var options = {
        'GroupId': avChatRoomId, //群组id
        'Members_Account': [user], //被禁言的成员帐号列表
        'ShutUpTime': '31536000'//禁言时间，单位：秒 默认一年
    };
    webim.forbidSendMsg(options,
        function (resp) {
            sendshutup(user, username);
            $(document).minTipsBox({
                tipsContent: "设置" + username + "禁言成功",
                tipsTime: 1
            });
            $(".grap").hide();
            $(".operation").slideUp('fast');

        },
        function (err) {
            // alert(err.ErrorInfo);
        }
    );
}

function cancelshutup(user, username) {
    var options = {
        'GroupId': avChatRoomId, //群组id
        'Members_Account': [user], //被禁言的成员帐号列表
        'ShutUpTime': '0'//禁言时间，单位：秒
    };
    webim.forbidSendMsg(options, function (resp) {
            sendcancelshutup(user, username);
            $(document).minTipsBox({
                tipsContent: "取消" + username + "禁言成功",
                tipsTime: 1
            });
            $('.Banned').hide();
            $(".operation").slideUp('fast');
            $('.grap').hide();
        }, function (err) {
        }
    );

}

function sendshutup(user, username) {
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var msg = new webim.Msg(selSess, true, -1, -1, -1, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = username;
    var desc = 'shutup';
    var ext = user;
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        if (err.ErrorCode == 10017) {
            showtipsbox('发消息失败：您已被管理员禁言');
        }
    });
}

function sendcancelshutup(user, username) {
    if (!selToID) {
        showtipsbox("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var msg = new webim.Msg(selSess, true, -1, -1, -1, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = username;
    var desc = 'cancelshutup';
    var ext = user;
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            showtipsbox('发消息失败：您已被管理员禁言');
        }
    });
}

function sendallshutup() {
    if (!selToID) {
        alert("您还没有加入群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var msg = new webim.Msg(selSess, true, -1, -1, -1, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = '';
    var desc = 'allshutup';
    var ext = '1';
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            showtipsbox('发消息失败：您已被管理员禁言');
        }
    });
}

function sendallcancel() {
    if (!selToID) {
        showtipsbox("您还没有加入群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var msg = new webim.Msg(selSess, true, -1, -1, -1, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = '';
    var desc = 'allshutup';
    var ext = '0';
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            showtipsbox('发消息失败：您已被管理员禁言');
        }
    });
}

function showMsg(msg) {
    var isSelfSend, fromAccount, Nick, sessType, subType;
    var ul, li, paneDiv, textDiv, nickNameSpan, contentSpan;
    fromAccount = msg.getFromAccount();
    if (!fromAccount) {
        fromAccount = '';
    }
    fromAccountNick = msg.getFromAccountNick();
    if (!fromAccountNick) {
        fromAccountNick = '未知用户';
    }
    subType = msg.getSubType();
    isSelfSend = msg.getIsSend();
    if (msg.elems[0]['type'] == 'TIMCustomElem') {
        if (msg.elems[0]['content']['desc'] == 'shutup') {

            if (msg.elems[0]['content']['ext'] == loginInfo.identifier) {
                $('#msghistory').append('<p class="red-tishi">你已被管理员禁言</p>');
                $('#send_msg_text').attr('readonly', 'true');
                $('#send_msg_text').attr('placeholder', '您已被禁言');
                $('.footer_cover').show();
                app_user.is_jingyan = 1;
            } else {
                $('#msghistory').append('<p class="red-tishi">' + msg.elems[0]['content']['data'] + '已被管理员禁言</p>');
            }
            scrollend();
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'cancelshutup') {

            if (msg.elems[0]['content']['ext'] == loginInfo.identifier) {
                $('#msghistory').append('<p class="red-tishi">你已被管理员移除禁言</p>');
                $('#send_msg_text').removeAttr('readonly');
                $('#send_msg_text').attr('placeholder', '和大伙说点什么吧');
                if (loginInfo.allshutup == 0) {
                    $('.footer_cover').hide();
                }
                app_user.is_jingyan = 0;
            } else {
                $('#msghistory').append('<p class="red-tishi">' + msg.elems[0]['content']['data'] + '已被移除禁言</p>');
            }
            scrollend();
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'allshutup') {
            if (msg.elems[0]['content']['ext'] == '1') {
                $('#send_msg_text').attr('readonly', 'true');
                $('#send_msg_text').attr('placeholder', '全员禁言中');
                $('#msghistory').append('<p class="red-tishi">管理员已设置全员禁言</p>');
                $(".bannerdbtn").children('em').removeClass('btn-movel').addClass('btn-mover').parent().css({backgroundColor: '#31ac82'});
                if (isadmin == 0) {
                    $('.footer_cover').show();
                }
                loginInfo.allshutup = 1;
            } else {
                $('#msghistory').append('<p class="red-tishi">管理员已取消全员禁言</p>');
                $('#send_msg_text').removeAttr('readonly');
                $('#send_msg_text').attr('placeholder', '和大伙说点什么吧');
                $(".bannerdbtn").children('em').removeClass('btn-mover').addClass('btn-movel').parent().css({backgroundColor: '#ccc'});
                $('.footer_cover').hide();
                loginInfo.allshutup = 0;
            }
            scrollend();
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'red') {

            var str = '';
            if (roomadmin.indexOf('"' + fromAccount.replace('mhcms_live_user_', '') + '"') > 0) {
                str += '<span class="sec"> (管理员) </span>';
            }
            $('#msghistory').append('<li class="redbao"><div class="chat_left"><div class="head_portrait"><img src="' + msg.getSession().icon() + '"></div><i class="iconfont" value="' + fromAccount + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name" username="' + fromAccountNick + '">' + fromAccountNick + str + '</div><div class="char_message red_message"><div class="red-red" value="' + msg.elems[0]['content']['ext'] + '"><img src="http://v.weiya.tv/Public/Home/image/hongbao.png" height="45px"><div class="red-cent"><p class="red-title">' + msg.elems[0]['content']['data'] + '</p><p>领取红包</p></div></div><div class="send"></div><div class="red_name">' + fromAccountNick + '的直播间红包</div></div></div></li>');
            scrollend();
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'reward') {
            $('#msghistory').append('<p class="red-tishi">' + msg.elems[0]['content']['data'] + '</p>');
            scrollend();
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'getred') {
            $('#msghistory').append('<p class="red-tishi">' + msg.elems[0]['content']['data'] + '</p>');
            scrollend();
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'gift') {
            $('#msghistory').append('<p class="red-tishi">' + msg.elems[0]['content']['data'] + '</p>');
            scrollend();
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'close_new') {
            if ($('#seq' + msg.elems[0]['content']['ext']).length > 0) {
                $('#seq' + msg.elems[0]['content']['ext']).remove();
            }
            var check_old = msg.elems[0]['content']['data'];
            if ($('#old_msg_' + check_old).length > 0) {
                $('#old_msg_' + check_old).remove();
            }
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'close_old') {
            if ($('#old_msg_' + msg.elems[0]['content']['data']).length > 0) {
                $('#old_msg_' + msg.elems[0]['content']['data']).remove();
            }
            var check_new = $('.new_msg_id[data-msgid=' + msg.elems[0]['content']['data'] + ']');
            if (check_new.length > 0) {
                check_new.parents("li").remove();
            }
            return false;
        } else if (msg.elems[0]['content']['desc'] == 'pull_black') {
            if (msg.elems[0]['content']['ext'] == loginInfo.identifier) {
                if (msg.elems[0]['content']['data'] == "black_1") {
                    $('#msghistory').append('<p class="red-tishi">你已被管理员拉入黑名单</p>');
                    app_user.is_blacklist = 1;
                    mui.alert('你已被拉入直播间黑名单', '警告', function () {
                        WeixinJSBridge.call('closeWindow');
                    });
                    setTimeout(function () {
                        WeixinJSBridge.call('closeWindow');
                    }, 5000);
                } else {
                    $('#msghistory').append('<p class="red-tishi">你已被管理员移出黑名单</p>');
                    app_user.is_blacklist = 0;
                }
                scrollend();
            }

            return false;
        }
    }
    switch (subType) {
        case webim.GROUP_MSG_SUB_TYPE.COMMON://群普通消息

            var str = '';
            str += '<li id="seq' + msg.seq + '"><div class="chat_left">';
            if (isSelfSend) {
                str += '<div class="head_portrait"><img src="' + msg.getSession().icon() + '"></div><i class="iconfont" value="' + fromAccount + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + fromAccountNick;
                if (roomadmin.indexOf('"' + fromAccount.replace('mhcms_live_user_', '') + '"') > 0) {
                    str += '<span class="sec"> (管理员) </span>';
                }
                str += '</div><div class="char_message">' + convertMsgtoHtml(msg) + '<div class="send"></div><i class="iconfont close"><i class="remove circle outline icon "></i></i></div></div></li>';
            } else {
                if (roomadmin.indexOf('"' + fromAccount.replace('mhcms_live_user_', '') + '"') > 0) {
                    str += '<div class="head_portrait"><img src="' + msg.getSession().icon() + '"></div><i class="iconfont" value="' + fromAccount + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + fromAccountNick + '<span class="sec"> (管理员) </span></div><div class="char_message">' + convertMsgtoHtml(msg) + '<div class="send"></div></div></div></li>';
                } else {
                    if (loginInfo.isadmin == 1) {
                        str += '<div class="head_portrait clickhead" user="' + fromAccount + '" username="' + fromAccountNick + '"><img src="' + msg.getSession().icon() + '"></div><i class="iconfont" value="' + fromAccount + '">&#xe8be;</i></div><div class="chat_right"><div class="char_name">' + fromAccountNick + '</div><div class="char_message">' + convertMsgtoHtml(msg) + '<div class="send"></div><i class="iconfont close"><i class="remove circle icon outline"></i></i></div></div></li>';
                    } else {
                        str += '<div class="head_portrait"><img src="' + msg.getSession().icon() + '"></div><i class="iconfont" value="' + fromAccount + '"></i></div><div class="chat_right"><div class="char_name">' + fromAccountNick + '</div><div class="char_message">' + convertMsgtoHtml(msg) + '<div class="send"></div></div></div></li>';
                    }
                }
            }
            $('#msghistory').append(str);
            break;
        case webim.GROUP_MSG_SUB_TYPE.REDPACKET://群红包消息
            contentSpan.innerHTML = "[群红包消息]" + convertMsgtoHtml(msg);
            break;
        case webim.GROUP_MSG_SUB_TYPE.LOVEMSG://群点赞消息
            //业务自己可以增加逻辑，比如展示点赞动画效果
            contentSpan.innerHTML = "[群点赞消息]" + convertMsgtoHtml(msg);
            //展示点赞动画
            showLoveMsgAnimation();
            break;
        case webim.GROUP_MSG_SUB_TYPE.TIP://群提示消息
            // contentSpan.innerHTML = "[群提示消息]" + convertMsgtoHtml(msg);
            //$('#msglist').append('<li><div class="chat_time">时间</div><div class="chat_left"><div class="head_portrait"><img src="'+msg.getSession().icon()+'"></div><a class="btn_link">赏</a></div><div class="chat_right"><div class="char_name">'+fromAccountNick+'</div><div class="char_message">'+convertMsgtoHtml(msg)+'<div class="send"></div></div></div></li>');
            break;
    }
    // $('#msglist').prepend('<li><div class="chat_time">时间</div><div class="chat_left"><div class="head_portrait"><img src="'+msg.getSession().icon()+'"></div><a class="btn_link">赏</a></div><div class="chat_right"><div class="char_name">'+fromAccountNick+'</div><div class="char_message">'+convertMsgtoHtml(msg)+'<div class="send"></div></div></div></li>');
    if ((($('.chat_title').get(0).scrollHeight - $('.chat_title').height()) - $('.chat_title').scrollTop()) < $('.chat_title').height()) {
        $('.chat_title').scrollTop($('.chat_title').get(0).scrollHeight - $('.chat_title').height());
    }
}

function scrollend() {
    if ((($('.chat_title').get(0).scrollHeight - $('.chat_title').height()) - $('.chat_title').scrollTop()) < $('.chat_title').height()) {
        $('.chat_title').scrollTop($('.chat_title').get(0).scrollHeight - $('.chat_title').height());
    }
}

//IE9(含)以下浏览器用到的jsonp回调函数
function jsonpCallback(rspData) {
    //设置接口返回的数据
    webim.setJsonpLastRspData(rspData);
}

//监听大群新消息（普通，点赞，提示，红包）
function onBigGroupMsgNotify(msgList) {
    for (var i = msgList.length - 1; i >= 0; i--) {//遍历消息，按照时间从后往前
        var msg = msgList[i];
        //console.warn(msg);
        webim.Log.warn('receive a new avchatroom group msg: ' + msg.getFromAccountNick());
        console.log(msg);
        showMsg(msg);
    }
}

//监听新消息(私聊(包括普通消息、全员推送消息)，普通群(非直播聊天室)消息)事件
//newMsgList 为新消息数组，结构为[Msg]
function onMsgNotify(newMsgList) {
    var newMsg;
    for (var j in newMsgList) {//遍历新消息
        newMsg = newMsgList[j];
        handlderMsg(newMsg);//处理新消息
    }
}

function onCustomGroupNotify(notify) {
    webim.Log.warn("执行 用户自定义系统消息 回调：" + JSON.stringify(notify));
    var reportTypeCh = "[用户自定义系统消息]";
    var content = notify.UserDefinedField;//群自定义消息数据
    showGroupSystemMsg(notify.ReportType, reportTypeCh, notify.GroupId, notify.GroupName, content, notify.MsgTime);
}

//处理消息（私聊(包括普通消息和全员推送消息)，普通群(非直播聊天室)消息）
function handlderMsg(msg) {
    var fromAccount, fromAccountNick, sessType, subType, contentHtml;

    fromAccount = msg.getFromAccount();
    if (!fromAccount) {
        fromAccount = '';
    }
    fromAccountNick = msg.getFromAccountNick();
    if (!fromAccountNick) {
        fromAccountNick = fromAccount;
    }

    //解析消息
    //获取会话类型
    //webim.SESSION_TYPE.GROUP-群聊，
    //webim.SESSION_TYPE.C2C-私聊，
    sessType = msg.getSession().type();
    //获取消息子类型
    //会话类型为群聊时，子类型为：webim.GROUP_MSG_SUB_TYPE
    //会话类型为私聊时，子类型为：webim.C2C_MSG_SUB_TYPE
    subType = msg.getSubType();

    switch (sessType) {
        case webim.SESSION_TYPE.C2C://私聊消息
            switch (subType) {
                case webim.C2C_MSG_SUB_TYPE.COMMON://c2c普通消息
                    //业务可以根据发送者帐号fromAccount是否为app管理员帐号，来判断c2c消息是否为全员推送消息，还是普通好友消息
                    //或者业务在发送全员推送消息时，发送自定义类型(webim.MSG_ELEMENT_TYPE.CUSTOM,即TIMCustomElem)的消息，在里面增加一个字段来标识消息是否为推送消息
                    contentHtml = convertMsgtoHtml(msg);
                    webim.Log.warn('receive a new c2c msg: fromAccountNick=' + fromAccountNick + ", content=" + contentHtml);
                    //c2c消息一定要调用已读上报接口
                    var opts = {
                        'To_Account': fromAccount,//好友帐号
                        'LastedMsgTime': msg.getTime()//消息时间戳
                    };
                    webim.c2CMsgReaded(opts);
                    //alert('收到一条c2c消息(好友消息或者全员推送消息): 发送人=' + fromAccountNick+", 内容="+contentHtml);
                    break;
            }
            break;
        case webim.SESSION_TYPE.GROUP://普通群消息，对于直播聊天室场景，不需要作处理
            break;
    }
}

//sdk登录
function sdkLogin() {
    //web sdk 登录

    console.log(loginInfo);
    console.log(listeners);
    console.log(options);
    console.log(avChatRoomId);

    webim.login(loginInfo, listeners, options,
        function (identifierNick) {
            //identifierNick为登录用户昵称(没有设置时，为帐号)，无登录态时为空
            webim.Log.info(identifierNick + 'webim登录成功');
            nickname = identifierNick.identifierNick;
            applyJoinBigGroup(avChatRoomId);
            initEmotionUL();//初始化表情
        },
        function (err) {
            //alert('错误、进入直播间失败了、请重新进入!');
            console.log('错误、进入直播间失败了、请重新进入!');
            //window.location.reload();
        }
    );//
}

//进入大群
function applyJoinBigGroup(groupId) {
    var options = {
        'GroupId': groupId//群id
    };
    webim.applyJoinBigGroup(
        options,
        function (resp) {
            //JoinedSuccess:加入成功; WaitAdminApproval:等待管理员审批
            if (resp.JoinedStatus && resp.JoinedStatus == 'JoinedSuccess') {
                setProfilePortrait();
                selToID = groupId;
            } else {
                showtipsbox('进群失败');
            }
        },
        function (err) {
            showtipsbox('进群失败');//alert(err.ErrorInfo);
        }
    );
}

//显示消息（群普通+点赞+提示+红包）


//把消息转换成Html
function convertMsgtoHtml(msg) {
    var html = "", elems, elem, type, content;
    elems = msg.getElems();//获取消息包含的元素数组
    for (var i in elems) {
        elem = elems[i];
        type = elem.getType();//获取元素类型
        content = elem.getContent();//获取元素对象
        switch (type) {
            case webim.MSG_ELEMENT_TYPE.TEXT:
                html += convertTextMsgToHtml(content);
                break;
            case webim.MSG_ELEMENT_TYPE.FACE:
                html += convertFaceMsgToHtml(content);
                break;
            case webim.MSG_ELEMENT_TYPE.IMAGE:
                html += convertImageMsgToHtml(content);
                break;
            case webim.MSG_ELEMENT_TYPE.RED:
                html += convertImageMsgToHtml(content);
                break;
            case webim.MSG_ELEMENT_TYPE.SOUND:
                html += convertSoundMsgToHtml(content);
                break;
            case webim.MSG_ELEMENT_TYPE.FILE:
                html += convertFileMsgToHtml(content);
                break;
            case webim.MSG_ELEMENT_TYPE.LOCATION://暂不支持地理位置
                //html += convertLocationMsgToHtml(content);
                break;
            case webim.MSG_ELEMENT_TYPE.CUSTOM:
                if (content.getDesc() == 'insert') {
                    html += '<i class="new_msg_id" data-msgid="' + content.getExt() + '"></i>';
                } else if (content.getDesc() == 'close_old' || content.getDesc() == 'close_new') {

                } else {
                    html += convertCustomMsgToHtml(content);
                }

                break;
            case webim.MSG_ELEMENT_TYPE.GROUP_TIP:
                html += convertGroupTipMsgToHtml(content);
                break;
            default:
                webim.Log.error('未知消息元素类型: elemType=' + type);
                break;
        }
    }
    return html;
}

//解析文本消息元素

function convertTextMsgToHtml(content) {
    return content.getText();
}

//解析表情消息元素
function convertFaceMsgToHtml(content) {
    var index = content.getIndex();
    var data = content.getData();
    var faceUrl = null;
    var emotion = webim.Emotions[index];
    if (emotion && emotion[1]) {
        faceUrl = emotion[1];
    }
    if (faceUrl) {
        return "<img src='" + faceUrl + "' class='smallface_img' />";
    } else {
        return data;
    }
}

//解析图片消息元素
function convertImageMsgToHtml(content) {
    var smallImage = content.getImage(webim.IMAGE_TYPE.SMALL);//小图
    var bigImage = content.getImage(webim.IMAGE_TYPE.LARGE);//大图
    var oriImage = content.getImage(webim.IMAGE_TYPE.ORIGIN);//原图
    if (!bigImage) {
        bigImage = smallImage;
    }
    if (!oriImage) {
        oriImage = smallImage;
    }
    return "<img src='" + smallImage.getUrl() + "#" + bigImage.getUrl() + "#" + oriImage.getUrl() + "' style='CURSOR: hand' id='" + content.getImageId() + "' bigImgUrl='" + bigImage.getUrl() + "' onclick='imageClick(this)' />";
}

//解析语音消息元素
function convertSoundMsgToHtml(content) {
    var second = content.getSecond();//获取语音时长
    var downUrl = content.getDownUrl();
    if (webim.BROWSER_INFO.type == 'ie' && parseInt(webim.BROWSER_INFO.ver) <= 8) {
        return '[这是一条语音消息]demo暂不支持ie8(含)以下浏览器播放语音,语音URL:' + downUrl;
    }
    return '<audio src="' + downUrl + '" controls="controls" onplay="onChangePlayAudio(this)" preload="none"></audio>';
}

//解析文件消息元素
function convertFileMsgToHtml(content) {
    var fileSize = Math.round(content.getSize() / 1024);
    return '<a href="' + content.getDownUrl() + '" title="点击下载文件" ><i class="glyphicon glyphicon-file">&nbsp;' + content.getName() + '(' + fileSize + 'KB)</i></a>';

}

//解析位置消息元素
function convertLocationMsgToHtml(content) {
    return '经度=' + content.getLongitude() + ',纬度=' + content.getLatitude() + ',描述=' + content.getDesc();
}

//解析自定义消息元素
function convertCustomMsgToHtml(content) {
    var data = content.getData();
    var desc = content.getDesc();
    var ext = content.getExt();
    return data;//"data=" + data + ", desc=" + desc + ", ext=" + ext;   


}

//解析群提示消息元素
function convertGroupTipMsgToHtml(content) {
    var WEB_IM_GROUP_TIP_MAX_USER_COUNT = 10;
    var text = "";
    var maxIndex = WEB_IM_GROUP_TIP_MAX_USER_COUNT - 1;
    var opType, opUserId, userIdList;
    var memberCount;
    opType = content.getOpType();//群提示消息类型（操作类型）
    opUserId = content.getOpUserId();//操作人id
    switch (opType) {
        case webim.GROUP_TIP_TYPE.JOIN://加入群
            userIdList = content.getUserIdList();
            //text += opUserId + "邀请了";
            for (var m in userIdList) {
                text += userIdList[m] + ",";
                if (userIdList.length > WEB_IM_GROUP_TIP_MAX_USER_COUNT && m == maxIndex) {
                    text += "等" + userIdList.length + "人";
                    break;
                }
            }
            text = text.substring(0, text.length - 1);
            text += "进入房间1";
            //房间成员数加1
            memberCount = $('#user-icon-fans').html();
            $('#user-icon-fans').html(parseInt(memberCount) + 1);
            break;
        case webim.GROUP_TIP_TYPE.QUIT://退出群
            text += opUserId + "离开房间";
            //房间成员数减1
            memberCount = parseInt($('#user-icon-fans').html());
            if (memberCount > 0) {
                $('#user-icon-fans').html(memberCount - 1);
            }
            break;
        case webim.GROUP_TIP_TYPE.KICK://踢出群
            text += opUserId + "将";
            userIdList = content.getUserIdList();
            for (var m in userIdList) {
                text += userIdList[m] + ",";
                if (userIdList.length > WEB_IM_GROUP_TIP_MAX_USER_COUNT && m == maxIndex) {
                    text += "等" + userIdList.length + "人";
                    break;
                }
            }
            text += "踢出该群";
            break;
        case webim.GROUP_TIP_TYPE.SET_ADMIN://设置管理员
            text += opUserId + "将";
            userIdList = content.getUserIdList();
            for (var m in userIdList) {
                text += userIdList[m] + ",";
                if (userIdList.length > WEB_IM_GROUP_TIP_MAX_USER_COUNT && m == maxIndex) {
                    text += "等" + userIdList.length + "人";
                    break;
                }
            }
            text += "设为管理员";
            break;
        case webim.GROUP_TIP_TYPE.CANCEL_ADMIN://取消管理员
            text += opUserId + "取消";
            userIdList = content.getUserIdList();
            for (var m in userIdList) {
                text += userIdList[m] + ",";
                if (userIdList.length > WEB_IM_GROUP_TIP_MAX_USER_COUNT && m == maxIndex) {
                    text += "等" + userIdList.length + "人";
                    break;
                }
            }
            text += "的管理员资格";
            break;

        case webim.GROUP_TIP_TYPE.MODIFY_GROUP_INFO://群资料变更
            text += opUserId + "修改了群资料：";
            var groupInfoList = content.getGroupInfoList();
            var type, value;
            for (var m in groupInfoList) {
                type = groupInfoList[m].getType();
                value = groupInfoList[m].getValue();
                switch (type) {
                    case webim.GROUP_TIP_MODIFY_GROUP_INFO_TYPE.FACE_URL:
                        text += "群头像为" + value + "; ";
                        break;
                    case webim.GROUP_TIP_MODIFY_GROUP_INFO_TYPE.NAME:
                        text += "群名称为" + value + "; ";
                        break;
                    case webim.GROUP_TIP_MODIFY_GROUP_INFO_TYPE.OWNER:
                        text += "群主为" + value + "; ";
                        break;
                    case webim.GROUP_TIP_MODIFY_GROUP_INFO_TYPE.NOTIFICATION:
                        text += "群公告为" + value + "; ";
                        break;
                    case webim.GROUP_TIP_MODIFY_GROUP_INFO_TYPE.INTRODUCTION:
                        text += "群简介为" + value + "; ";
                        break;
                    default:
                        text += "未知信息为:type=" + type + ",value=" + value + "; ";
                        break;
                }
            }
            break;

        case webim.GROUP_TIP_TYPE.MODIFY_MEMBER_INFO://群成员资料变更(禁言时间)
            text += opUserId + "修改了群成员资料:";
            var memberInfoList = content.getMemberInfoList();
            var userId, shutupTime;
            for (var m in memberInfoList) {
                userId = memberInfoList[m].getUserId();
                shutupTime = memberInfoList[m].getShutupTime();
                text += userId + ": ";
                if (shutupTime != null && shutupTime !== undefined) {
                    if (shutupTime == 0) {
                        text += "取消禁言; ";
                    } else {
                        text += "禁言" + shutupTime + "秒; ";
                    }
                } else {
                    text += " shutupTime为空";
                }
                if (memberInfoList.length > WEB_IM_GROUP_TIP_MAX_USER_COUNT && m == maxIndex) {
                    text += "等" + memberInfoList.length + "人";
                    break;
                }
            }
            break;
        default:
            text += "未知群提示消息类型：type=" + opType;
            break;
    }
    return text;
}

//tls登录
function tlsLogin() {
    //跳转到TLS登录页面
    TLSHelper.goLogin({
        sdkappid: loginInfo.sdkAppID,
        acctype: loginInfo.accountType,
        url: window.location.href
    });
}

//第三方应用需要实现这个函数，并在这里拿到UserSig
function tlsGetUserSig(res) {
    //成功拿到凭证
    if (res.ErrorCode == webim.TLS_ERROR_CODE.OK) {
        //从当前URL中获取参数为identifier的值
        loginInfo.identifier = webim.Tool.getQueryString("identifier");
        //拿到正式身份凭证
        loginInfo.userSig = res.UserSig;
        //从当前URL中获取参数为sdkappid的值
        loginInfo.sdkAppID = loginInfo.appIDAt3rd = Number(webim.Tool.getQueryString("sdkappid"));
        //从cookie获取accountType
        var accountType = webim.Tool.getCookie('accountType');
        if (accountType) {
            loginInfo.accountType = accountType;
            sdkLogin();//sdk登录
        } else {
            showtipsbox('accountType非法');
        }
    } else {
        //签名过期，需要重新登录
        if (res.ErrorCode == webim.TLS_ERROR_CODE.SIGNATURE_EXPIRATION) {
            tlsLogin();
        } else {
            // alert("[" + res.ErrorCode + "]" + res.ErrorInfo);
        }
    }
}

//单击图片事件
function imageClick(imgObj) {
    var imgUrls = imgObj.src;
    var imgUrlArr = imgUrls.split("#"); //字符分割
    var smallImgUrl = imgUrlArr[0];//小图
    var bigImgUrl = imgUrlArr[1];//大图
    var oriImgUrl = imgUrlArr[2];//原图
    webim.Log.info("小图url:" + smallImgUrl);
    webim.Log.info("大图url:" + bigImgUrl);
    webim.Log.info("原图url:" + oriImgUrl);
}


//切换播放audio对象
function onChangePlayAudio(obj) {
    if (curPlayAudio) {//如果正在播放语音
        if (curPlayAudio != obj) {//要播放的语音跟当前播放的语音不一样
            curPlayAudio.currentTime = 0;
            curPlayAudio.pause();
            curPlayAudio = obj;
        }
    } else {
        curPlayAudio = obj;//记录当前播放的语音
    }
}

//单击评论图片
function smsPicClick() {
    if (!loginInfo.identifier) {//未登录
        if (accountMode == 1) {//托管模式
            //将account_type保存到cookie中,有效期是1天
            webim.Tool.setCookie('accountType', loginInfo.accountType, 3600 * 24);
            //调用tls登录服务
            tlsLogin();
        } else {//独立模式
            alert('请填写帐号和票据1');
        }
        return;
    } else {
        hideDiscussTool();//隐藏评论工具栏
        showDiscussForm();//显示评论表单
    }
}

//发送消息(普通消息)
function onSendMsg() {
    if (loginInfo.allshutup == 1) {
        showtipsbox('发消息失败：管理员已设置全员禁言');
        return false;
    }
    if (!loginInfo.identifier) {//未登录
        if (accountMode == 1) {//托管模式
            //将account_type保存到cookie中,有效期是1天
            webim.Tool.setCookie('accountType', loginInfo.accountType, 3600 * 24);
            //调用tls登录服务
            tlsLogin();
        } else {//独立模式
            alert('请填写帐号和票据2');
        }
        return;
    }
    if (!selToID) {
        alert("您还没有进入房间，暂不能聊天");
        $("#send_msg_text").val('');
        return;
    }
    //获取消息内容
    var msgtosend = $("#send_msg_text").val();
    var msgLen = webim.Tool.getStrBytes(msgtosend);
    if (msgtosend.length < 1) {
        return;
    }
    var maxLen, errInfo;
    if (selType == webim.SESSION_TYPE.GROUP) {
        maxLen = webim.MSG_MAX_LENGTH.GROUP;
        errInfo = "消息长度超出限制(最多" + Math.round(maxLen / 3) + "汉字)";
    } else {
        maxLen = webim.MSG_MAX_LENGTH.C2C;
        errInfo = "消息长度超出限制(最多" + Math.round(maxLen / 3) + "汉字)";
    }
    if (msgLen > maxLen) {
        alert('消息长度超出限制');
        // alert(errInfo);
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, selSessHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var isSend = true;//是否为自己发送
    var seq = -1;//消息序列，-1表示sdk自动生成，用于去重
    var random = Math.round(Math.random() * 4294967296);//消息随机数，用于去重
    var msgTime = Math.round(new Date().getTime() / 1000);//消息时间戳
    var subType;//消息子类型
    if (selType == webim.SESSION_TYPE.GROUP) {
        //群消息子类型如下：
        //webim.GROUP_MSG_SUB_TYPE.COMMON-普通消息,
        //webim.GROUP_MSG_SUB_TYPE.LOVEMSG-点赞消息，优先级最低
        //webim.GROUP_MSG_SUB_TYPE.TIP-提示消息(不支持发送，用于区分群消息子类型)，
        //webim.GROUP_MSG_SUB_TYPE.REDPACKET-红包消息，优先级最高
        subType = webim.GROUP_MSG_SUB_TYPE.COMMON;

    } else {
        //C2C消息子类型如下：
        //webim.C2C_MSG_SUB_TYPE.COMMON-普通消息,
        subType = webim.C2C_MSG_SUB_TYPE.COMMON;
    }
    var msg = new webim.Msg(selSess, isSend, seq, random, msgTime, loginInfo.identifier, subType, loginInfo.identifierNick);
    //解析文本和表情

    var expr = /\[[^[\]]{1,3}\]/mg;
    var emotions = msgtosend.match(expr);
    var text_obj, face_obj, tmsg, emotionIndex, emotion, restMsgIndex;
    if (!emotions || emotions.length < 1) {
        text_obj = new webim.Msg.Elem.Text(msgtosend);
        msg.addText(text_obj);
    } else {//有表情

        for (var i = 0; i < emotions.length; i++) {
            tmsg = msgtosend.substring(0, msgtosend.indexOf(emotions[i]));
            if (tmsg) {
                text_obj = new webim.Msg.Elem.Text(tmsg);
                msg.addText(text_obj);
            }
            emotionIndex = webim.EmotionDataIndexs[emotions[i]];
            emotion = webim.Emotions[emotionIndex];
            if (emotion) {
                face_obj = new webim.Msg.Elem.Face(emotionIndex, emotions[i]);
                msg.addFace(face_obj);
            } else {
                text_obj = new webim.Msg.Elem.Text(emotions[i]);
                msg.addText(text_obj);
            }
            restMsgIndex = msgtosend.indexOf(emotions[i]) + emotions[i].length;
            msgtosend = msgtosend.substring(restMsgIndex);
        }
        if (msgtosend) {
            text_obj = new webim.Msg.Elem.Text(msgtosend);
            msg.addText(text_obj);
        }
    }
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            showMsg(msg);
        }
        webim.Log.info("发消息成功");
        $("#send_msg_text").val('');
        $("#mune").empty().removeClass('btn-sub').addClass('icon showfun').html();
        $(".browlist").slideUp('fast');
        $(".allgift").slideUp('fast');
        $(".showfunction").slideUp('fast');
        hideflag = true;


        hideDiscussForm();//隐藏评论表单
        showDiscussTool();//显示评论工具栏
        hideDiscussEmotion();//隐藏表情
    }, function (err) {
        webim.Log.error("发消息失败:" + err.ErrorInfo);
        console.log(err);
        if (err.ErrorCode == 10017) {
            showtipsbox('发消息失败：您已被管理员禁言');
        }
        //console.log(err.ErrorInfo.response);
        //alert("发消息失败:" + err.ErrorInfo);
    });
}

function upimage(imgurl) {
    $("#send_msg_text").val('');
    $("#mune").empty().removeClass('btn-sub').addClass('icon showfun').html();
    $(".browlist").slideUp('fast');
    $(".allgift").slideUp('fast');
    $(".showfunction").slideUp('fast');
    hideflag = true;
    sendimage(imgurl);
}

function sendimage(imgurl) {
    if (loginInfo.allshutup == 1) {
        showtipsbox('发消息失败：管理员已设置全员禁言');
        return false;
    }
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var msg = new webim.Msg(selSess, true, -1, -1, -1, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = '<img src="' + imgurl + '"/>';
    var desc = 'image';
    var ext = '';
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }

    }, function (err) {
        //alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            alert('发消息失败：您已被管理员禁言');
        }
    });
}

function sendred(msg_id, title) {
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var random = Math.round(Math.random() * 4294967296);//消息随机数，用于去重
    var msgTime = Math.round(new Date().getTime() / 1000);//消息时间戳
    var msg = new webim.Msg(selSess, true, -1, random, msgTime, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = title;
    var desc = 'red';
    var ext = msg_id;
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            alert('发消息失败：您已被管理员禁言');
        }
    });
}

function sendgift(msg_content, touid) {
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var random = Math.round(Math.random() * 4294967296);//消息随机数，用于去重
    var msgTime = Math.round(new Date().getTime() / 1000);//消息时间戳
    var msg = new webim.Msg(selSess, true, -1, random, msgTime, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = msg_content;
    var desc = 'gift';
    var ext = touid;
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            alert('发消息失败：您已被管理员禁言');
        }
    });
}

function sendgetred(msg_content) {
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var random = Math.round(Math.random() * 4294967296);//消息随机数，用于去重
    var msgTime = Math.round(new Date().getTime() / 1000);//消息时间戳
    var msg = new webim.Msg(selSess, true, -1, random, msgTime, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = msg_content;
    var desc = 'getred';
    var ext = "";
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            alert('发消息失败：您已被管理员禁言');
        }
    });
}

function sendreward(msg_content, touid) {
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var random = Math.round(Math.random() * 4294967296);//消息随机数，用于去重
    var msgTime = Math.round(new Date().getTime() / 1000);//消息时间戳
    var msg = new webim.Msg(selSess, true, -1, random, msgTime, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = msg_content;
    var desc = 'reward';
    var ext = touid;
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            alert('发消息失败：您已被管理员禁言');
        }
    });
}

function sendCustomMsg() {
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var msg = new webim.Msg(selSess, true, -1, -1, -1, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = '<a href="http://m.baidu.com/"><img src="http://live1-1252753891.cosgz.myqcloud.com/test/10181734538970.png"/></a>';
    var desc = '';
    var ext = '';
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            alert('发消息失败：您已被管理员禁言');
        }
    });
}

function sendclose(close_type, seq, msg_id) {
    if (!selToID) {
        alert("您还没有好友或群组，暂不能聊天");
        return;
    }
    if (app_user.is_jingyan == '1') {
        alert("你已经被禁言、撤回失败！");
        return;
    }
    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, friendHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var msg = new webim.Msg(selSess, true, -1, -1, -1, loginInfo.identifier, 0, loginInfo.identifierNick);
    var data = msg_id;
    var desc = close_type;
    var ext = seq;
    var custom_obj = new webim.Msg.Elem.Custom(data, desc, ext);
    msg.addCustom(custom_obj);
    //调用发送消息接口
    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            addMsg(msg);
        }
    }, function (err) {
        // alert(err.ErrorInfo);
        if (err.ErrorCode == 10017) {
            alert('发消息失败：您已被管理员禁言');
        }
    });
}

//发送消息(群点赞消息)
function sendGroupLoveMsg() {

    if (!loginInfo.identifier) {//未登录
        if (accountMode == 1) {//托管模式
            //将account_type保存到cookie中,有效期是1天
            webim.Tool.setCookie('accountType', loginInfo.accountType, 3600 * 24);
            //调用tls登录服务
            tlsLogin();
        } else {//独立模式
            alert('请填写帐号和票据3');
        }
        return;
    }

    if (!selToID) {
        alert("您还没有进入房间，暂不能点赞");
        return;
    }

    if (!selSess) {
        selSess = new webim.Session(selType, selToID, selToID, selSessHeadUrl, Math.round(new Date().getTime() / 1000));
    }
    var isSend = true;//是否为自己发送
    var seq = -1;//消息序列，-1表示sdk自动生成，用于去重
    var random = Math.round(Math.random() * 4294967296);//消息随机数，用于去重
    var msgTime = Math.round(new Date().getTime() / 1000);//消息时间戳
    //群消息子类型如下：
    //webim.GROUP_MSG_SUB_TYPE.COMMON-普通消息,
    //webim.GROUP_MSG_SUB_TYPE.LOVEMSG-点赞消息，优先级最低
    //webim.GROUP_MSG_SUB_TYPE.TIP-提示消息(不支持发送，用于区分群消息子类型)，
    //webim.GROUP_MSG_SUB_TYPE.REDPACKET-红包消息，优先级最高
    var subType = webim.GROUP_MSG_SUB_TYPE.LOVEMSG;

    var msg = new webim.Msg(selSess, isSend, seq, random, msgTime, loginInfo.identifier, subType, loginInfo.identifierNick);
    var msgtosend = 'love_msg';
    var text_obj = new webim.Msg.Elem.Text(msgtosend);
    msg.addText(text_obj);

    webim.sendMsg(msg, function (resp) {
        if (selType == webim.SESSION_TYPE.C2C) {//私聊时，在聊天窗口手动添加一条发的消息，群聊时，长轮询接口会返回自己发的消息
            showMsg(msg);
        }
        webim.Log.info("点赞成功");
    }, function (err) {
        webim.Log.error("发送点赞消息失败:" + err.ErrorInfo);
        alert("发送点赞消息失败:" + err.ErrorInfo);
    });
}

//隐藏评论文本框
function hideDiscussForm() {
    $(".video-discuss-form").hide();
}

//显示评论文本框
function showDiscussForm() {
    $(".video-discuss-form").show();
}

//隐藏评论工具栏
function hideDiscussTool() {
    $(".video-discuss-tool").hide();
}

//显示评论工具栏
function showDiscussTool() {
    $(".video-discuss-tool").show();
}

//隐藏表情框
function hideDiscussEmotion() {
    $(".smile").hide();
    //$(".video-discuss-emotion").fadeOut("slow");
}

//显示表情框
function showDiscussEmotion() {
    $(".browlist").show();
    //$(".video-discuss-emotion").fadeIn("slow");

}

//展示点赞动画
function showLoveMsgAnimation() {
    //点赞数加1
    var loveCount = $('#user-icon-like').html();
    $('#user-icon-like').html(parseInt(loveCount) + 1);
    var toolDiv = document.getElementById("video-discuss-tool");
    var loveSpan = document.createElement("span");
    var colorList = ['red', 'green', 'blue'];
    var max = colorList.length - 1;
    var min = 0;
    var index = parseInt(Math.random() * (max - min + 1) + min, max + 1);
    var color = colorList[index];
    loveSpan.setAttribute('class', 'like-icon zoomIn ' + color);
    toolDiv.appendChild(loveSpan);
}

//初始化表情
function initEmotionUL() {
    for (var index in webim.Emotions) {
        var emotions = $('<img>').attr({
            "id": webim.Emotions[index][0],
            "src": webim.Emotions[index][1],
            "style": "cursor:pointer;"
        }).on('trouch click', function (event) {
            event.preventDefault();
            selectEmotionImg(this);
        });
        $('<li>').append(emotions).appendTo($('.browlist'));
    }
}

//打开或显示表情
function showEmotionDialog() {
    if (openEmotionFlag) {//如果已经打开
        openEmotionFlag = false;
        hideDiscussEmotion();//关闭
    } else {//如果未打开
        openEmotionFlag = true;
        showDiscussEmotion();//打开
    }
}

//选中表情
function selectEmotionImg(selImg) {
    if (loginInfo.allshutup == 1) {
        showtipsbox('发消息失败：管理员已设置全员禁言');
        return false;
    }
    $("#send_msg_text").val($("#send_msg_text").val() + selImg.id);
    $("#mune").empty().removeClass('icon showfun').addClass('btn-sub').html('发送');
    // $("#send_msg_text").focus();
    $(".bt3").show();
    $('.bt2').hide();
}

//退出大群
function quitBigGroup() {
    var options = {
        'GroupId': avChatRoomId//群id
    };
    webim.quitBigGroup(
        options,
        function (resp) {

            webim.Log.info('退群成功');
            $("#video_sms_list").find("li").remove();
            //webim.Log.error('进入另一个大群:'+avChatRoomId2);
            //applyJoinBigGroup(avChatRoomId2);//加入大群
        },
        function (err) {
            //   alert(err.ErrorInfo);
        }
    );
}

//登出
function logout() {
    //登出
    webim.logout(
        function (resp) {
            webim.Log.info('登出成功');
            loginInfo.identifier = null;
            loginInfo.userSig = null;
            $("#video_sms_list").find("li").remove();
            var indexUrl = window.location.href;
            var pos = indexUrl.indexOf('?');
            if (pos >= 0) {
                indexUrl = indexUrl.substring(0, pos);
            }
            window.location.href = indexUrl;
        }
    );
}

function setProfilePortrait() {
    var profile_item = [{
        "Tag": "Tag_Profile_IM_Nick",
        "Value": loginInfo.identifierNick
    }, {
        "Tag": "Tag_Profile_IM_Image",
        "Value": loginInfo.headurl
    }];
    var options = {
        'ProfileItem': profile_item
    };
    webim.setProfilePortrait(
        options,
        function (resp) {
            // alert('设置个人资料成功');
        },
        function (err) {
            // alert(err.ErrorInfo);
        }
    );
};


var getGroupMemberInfo = function (group_id) {
    //initGetGroupMemberTable([]);
    var options = {
        'GroupId': group_id,
        'Offset': 0, //必须从0开始
        'Limit': 1000000,
    };
    webim.getGroupMemberInfo(
        options,
        function (resp) {
            if (resp.MemberNum <= 0) {
                var MemberNum = 0;
            } else {
                var MemberNum = resp.MemberNum;
            }
            $("#browsenum").text(MemberNum);
            console.log(resp);
        },
        function (err) {
            alert(err.ErrorInfo);
        }
    );
};
//弹窗提示
        