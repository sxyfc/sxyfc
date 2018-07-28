define("mhcms", ["jquery" , 'wx' ], function ($ , wx) {
    return {
        ///
        init_seo: function ($seo_key, $seo_data) {
            $(document).attr("title", $seo_data.seo_title);//修改title值

        },

        get_sign : function(url, cb  ){
            var sdk_service = "/wechat/service/get_current_ticket";
            $.get(sdk_service, {
                'url' : url
            } , function ($signPackage) {
                var jssdk_obj = {
                    debug: false,
                    appId: $signPackage.appId,
                    timestamp: $signPackage.timestamp,
                    nonceStr: $signPackage.nonceStr,
                    signature: $signPackage.signature,
                    jsApiList: js_api_list
                };

                if(cb && typeof  cb ==="function"){
                    cb(jssdk_obj)
                }
            }, 'json');
        },

        init_wechat_share: function ($seo_data , $url ) {




            this.get_sign($url , function (jssdk_obj) {
                wx.config(jssdk_obj);

                wx.ready(function () {
                    var share = {
                        'title': $seo_data.seo_title,
                        'link': $seo_data.share_url,
                        'imgUrl': share_icon,
                        'desc': $seo_data.seo_desc,
                        'success': function () {

                        }
                    }
                    wx.onMenuShareAppMessage(share);
                    wx.onMenuShareQQ(share);
                    wx.onMenuShareWeibo(share);
                    wx.onMenuShareTimeline({
                        'title': $seo_data.seo_title,
                        'link': $url,
                        'imgUrl': share_icon,
                        'desc': $seo_data.seo_desc,
                        'success': function () {
                        }
                    })
                });
            })
        },

        list_loader : function (options , $api) {
            var that = this;
            that.is_loading = false;
            that.options = options;


            this.load_item_list = function (init , callback) {
                if (that.is_loading === false) {
                    that.is_loading = true;
                }



                $.get($api , options , function (data) {

                    if (typeof callback === "function") {
                        callback(data);
                    }

                } , 'json');

            };
            this.change_options = function (field_name , value , cb) {
                that.options.query[field_name] = value;
                that.load_item_list(1 , cb);
            };


        },
        mhcms_simple_tab : function (container , group_name) {
            $(container + " .tab-header li").each(function () {
                var target = $(this).data('target');
                console.log(target);
                $(this).click(function () {

                    $(container + " .tab-header li").removeClass('active1');
                    $(this).addClass('active');

                    $(container + " .tab-body").hide();
                    $(container + " .group_" + target).show();

                });
            })


        }
        ,
        common_player : function(container){

            var video = document.querySelector('video');
            video.onended = function() {

                pauseBtn.show();
            };
            var pauseBtn = $("#"+ container + " .pause");
            pauseBtn.on("click", function() {
                pauseBtn.hide();
                video.play();
            });
//渲染进度条信息
            function renderProgress() {
                var duration = parseInt(video.duration),
                    currentTime = parseInt(video.currentTime),
                    progressBar = document.querySelector('.video-progress-bar'),
                    currentTimeTxt = document.querySelector('.video-time__current'),
                    durationTxt = document.querySelector('.video-time__duration');
                durationPlus = document.querySelector('.video-time__plus');
                durationSeek = document.querySelector('.video-seek__container');
                if(isNaN(duration)){
                    currentTimeTxt.innerText = "\u5b9e\u65f6\u76f4\u64ad";
                    currentTimeTxt.style.height = "3rem" ;
                    $(".video-time__current").css("line-height","3rem");
                    $(".video-time__current").css("font-size","1.3rem");
                    durationTxt.innerText = "";
                    durationPlus.innerText = "";
                    progressBar.style.display="none";
                    durationSeek.style.display="none";
                }else{
                    progressBar.style.display="block";
                    durationSeek.style.display="block";
                    durationPlus.innerText = "/";
                    $(".video-time__current").css("line-height","");
                    $(".video-time__current").css("font-size","");

                    currentTimeTxt.innerText = formatSeconds(currentTime);
                    durationTxt.innerText = formatSeconds(duration);
                    progressBar.style.width = (currentTime / duration) * 100 + '%';
                }

            }
            // 获取元素的偏移量
            function left(elem) {
                var left = elem.offsetLeft,
                    parent = elem.offsetParent;
                while (parent) {
                    left += parent.offsetLeft;
                    parent = parent.offsetParent;
                }
                return left;
            }

            function formatSeconds(value) {
                var theTime = parseInt(value);// 秒
                var theTime1 = 0;// 分
                var theTime2 = 0;// 小时
                if(theTime > 60) {
                    theTime1 = parseInt(theTime/60);
                    theTime = parseInt(theTime%60);
                    if(theTime1 > 60) {
                        theTime2 = parseInt(theTime1/60);
                        theTime1 = parseInt(theTime1%60);
                    }
                }
                var result = ""+parseInt(theTime)+"";
                if(theTime1 > 0) {
                    result = ""+parseInt(theTime1)+":"+result;
                }else{
                    result = "00:"+result;
                }
                if(theTime2 > 0) {
                    result = ""+parseInt(theTime2)+":"+result;
                }else{
                    result = "00:"+result;
                }
                return result;
            }

            //增加全屏函数，modified by tim peng 20170627
            function fullscreenFunction(elem) {
                var prefix = 'webkit';
                if (elem[prefix + 'EnterFullScreen']) {
                    return prefix + 'EnterFullScreen';
                } else if (elem[prefix + 'RequestFullScreen']) {
                    return prefix + 'RequestFullScreen';
                };
                return false;
            };

            var videoControl = {
                main: document.querySelector(".video-controls"),
                init: function() {
                    var videoIcon = document.querySelector(".video-icon"),
                        fullscreenIcon = document.querySelector(".full-status"),    //增加全屏DIV modified by tim peng 20170627
                        videoSeek = document.querySelector(".video-seek");

                    // 播放暂停按钮
                    videoIcon.addEventListener("click", function() {
                        if (video.paused) {
                            video.play();
                            videoIcon.classList.remove('video-play');
                        } else {
                            video.pause();
                            videoIcon.classList.add('video-play');
                        }
                    });

                    // 全屏按钮 modified by tim peng 20170627
                    fullscreenIcon.addEventListener("click", function () {
                        try {

                            if (isAndroid) {  //安卓手机才处理
                                if(video.getAttribute("x5-video-orientation")=="portrait"){
                                    // canvas.style.width = width  + 'px';
                                    // canvas.style.height = height  + 'px';
                                    video.setAttribute("x5-video-orientation", "landscape");
                                    $(".videoTitle").css("display","none");
                                    var e = video.requestFullscreen || video.webkitEnterFullScreen || video.webkitRequestFullscreen || video.webkitRequestFullScreen;
                                    e.apply(video);
                                    $(".container").css("padding-bottom","56.5%");
                                    $(".liveHeader").css("top","5rem");
                                    $(".danmu_bar").css("top","5rem");
                                }else{
                                    // canvas.style.width = width * 9 / 16 + 'px';
                                    // canvas.style.height = height * 9 / 16 + 'px';
                                    video.setAttribute("x5-video-orientation", "portrait");
                                    $(".videoTitle").css("display","block");
                                    $(".liveHeader").css("top","2rem");
                                    $(".danmu_bar").css("top","2rem");
                                }
                            }else{
                                var fullscreenvideo = fullscreenFunction(video);
                                video[fullscreenvideo]();
                            }

                        }catch (e) {
                            //alert(e);
                        }

                    });

                    // 进度条拖放
                    var lastX, currentX = 0,
                        offsetX,
                        percentage,
                        offsetLeft = left(videoSeek),
                        clientWidth = videoSeek.clientWidth;

                    // 播放进度条拖动和点击


                    videoSeek.addEventListener('touchstart', function(e) {
                        transformOrigin2 = window.getComputedStyle(videoSeek).transformOrigin;
                        offsetLeft = left(videoSeek);
                        clientWidth = videoSeek.clientWidth;
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '0ms';
                        lastX = e.touches[0].pageX;
                        currentX = 0;
                    }, false);

                    videoSeek.addEventListener('touchstart',  function(e) {
                        transformOrigin2 = window.getComputedStyle(videoSeek).transformOrigin;
                        offsetLeft = left(videoSeek);
                        clientWidth = videoSeek.clientWidth;
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '0ms';
                        lastX = e.touches[0].pageX;
                        currentX = 0;
                    }, false);
                    videoSeek.addEventListener('touchmove', function(e) {
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '8ms';
                        currentX = e.touches[0].pageX;
                        percentage = (currentX - offsetLeft) / clientWidth;
                        video.currentTime = percentage * video.duration;
                    }, false);
                    videoSeek.addEventListener('touchend', function(e) {
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '8ms';
                        if (currentX == 0) {
                            percentage = (lastX - offsetLeft) / clientWidth;
                            video.currentTime = percentage * video.duration;
                        }
                    }, false);
                },
                videoPause: function() {
                    var videoIcon = document.querySelector(".video-icon");
                    video.pause();
                    videoIcon.classList.add('video-play');
                    //alert("test3");
                },
                toggleShow: function() {
                    var _self = this;
                    console.log(_self.main.classList.contains("show"))
                    if (_self.main.classList.contains("show")) {
                        _self.main.classList.remove("show");

                        //$(".video-controls").fadeOut();
                        //$(".icon-refresh").fadeOut();
                        //$(".liveHeader").fadeOut();

                        $(".icon-refresh").css("display","none");
                        $(".liveHeader").css("display","none");
                        showflag=0;
                    } else {
                        _self.main.classList.add("show");
                        $(".icon-refresh").css("display","block");
                        $(".liveHeader").css("display","block");
                        //$(".video-controls").fadeIn();
                        //$(".icon-refresh").fadeIn();
                        //$(".liveHeader").fadeIn();

                        showflag=1;
                    }
                    //自动消失弹出框
                    if(showflag==1 && showStart==1){
                        showStart=0;
                        setTimeout(function(){
                            _self.main.classList.remove("show");
                            //$(".video-controls").fadeOut();
                            $(".icon-refresh").fadeOut();
                            $(".liveHeader").fadeOut();
                            showStart=1;
                        }, 10000);

                    }

                    //如果暂停了，再播放。
                    if(video.pause)
                    {
                        video.play();
                        videoIcon.classList.remove('video-play');
                    }
                }
            }
            videoControl.init();
        },
        mhcms_player : function (container ) {
            var width = 640,
                height = 360;
            var poster = document.querySelector('.poster'),
                video = document.querySelector('video'),
                pauseBtn = document.querySelector(".pause");
            var cont = document.querySelector(".video_container"),
                //isAndroid = /android/ig.test(navigator.userAgent),
                isAndroid = true,
                canvas, canvasContext;
            var flag=0;
            var checkinfo=0;
            var showflag=0;
            var showStart=1;
            var videoIcon = document.querySelector(".video-icon");

            if (isAndroid) {
                video.classList.add('hidden');
                canvas = document.querySelector('canvas');
                canvas.classList.add('playing');

                canvasContext = canvas.getContext("2d");

                var PIXEL_RATIO = (function () {
                    var ctx = document.createElement('canvas').getContext('2d'),
                        dpr = window.devicePixelRatio || 1,
                        bsr = ctx.webkitBackingStorePixelRatio ||
                            ctx.mozBackingStorePixelRatio ||
                            ctx.msBackingStorePixelRatio ||
                            ctx.oBackingStorePixelRatio ||
                            ctx.backingStorePixelRatio || 1;

                    return dpr / bsr;
                })();

                // canvas.width = width;
                // canvas.height = height;

                //alert(PIXEL_RATIO);
                // 适配高清屏，canvas内容的宽高是实际的宽高的PIXEL_RATIO倍
                canvas.width = width * PIXEL_RATIO;
                canvas.height = height * PIXEL_RATIO;
                // canvas.style.width = width * 9 / 16 + 'px';
                // canvas.style.height = height * 9 / 16 + 'px';
                console.log('AA'+PIXEL_RATIO);
                // 缩放绘图
                canvasContext.setTransform(PIXEL_RATIO, 0, 0, PIXEL_RATIO, 0, 0);

                // canvasContext.scale(2,2);
                // canvasContext.beginPath();
                // canvasContext.fillStyle="yellow";
                // canvasContext.arc(160,90,60,0,2*Math.PI);
                // canvasContext.stroke();
                // canvasContext.fill();
            }


            pauseBtn.addEventListener("click", function() {
                $(".bannerSwiper").css("display","none");
                $(".bannerSwiper2").css("display","none");
                //加载中图标
                $(".pause").css("background","url(/statics/images/loading.png) no-repeat center");
                $(".pause").css("background-size","4.6rem");
                $(".pause").css("background-color","#000000");
                video.play();

            }, false);
            video.addEventListener("click", function() {

                videoControl.toggleShow();
            }, false);

            video.addEventListener("timeupdate", function() {

                //修复重复点击问题。

                if(video.currentTime<=1 && checkinfo==1){
                    $('.pause').trigger("click");
                    $('video').trigger("click");
                    videoControl.toggleShow();
                }

                poster.style.visibility = 'hidden';

                pauseBtn.style.visibility = 'hidden';

                pauseBtn.style.zIndex = -1;
                renderProgress();
            }, false);

            video.addEventListener("x5videoexitfullscreen", function() {

                //全屏问题
                if (isAndroid) {
                //    video.setAttribute("x5-video-orientation", "portrait"); ;
                }

                //修复重复点击问题。
                if(video.currentTime<=1){
                    checkinfo=1;
                    video.play();
                }else{
                    $(".videoTitle").css("display","none");
                    videoControl.videoPause();
                }


            }, false);

            video.addEventListener("play", function() {
                if (isAndroid) {
                    $(".videoTitle").css("display","block");
                    //alert('test');
                    renderCanvas();
                } else {
                    video.classList.add('playing');
                }
            }, false);

            if (isAndroid) {
                canvas.addEventListener("click", function() {
                    videoControl.toggleShow();
                }, false);
            }

            // 渲染canvas
            function renderCanvas() {
                draw(video, canvas, canvasContext, width, height);
            }

            //canvas渲染video
            function draw(v, c, c2, w, h) {
                if (v.paused || v.ended) {
                    cancelAnimationFrame(stop);
                    return false;
                }
                c2.drawImage(v, 0, 0, w, h);
                var stop = requestAnimationFrame(function() {
                    draw(v, c, c2, w, h);

                });
            }
            //渲染进度条信息
            function renderProgress() {
                var duration = parseInt(video.duration),
                    currentTime = parseInt(video.currentTime),
                    progressBar = document.querySelector('.video-progress-bar'),
                    currentTimeTxt = document.querySelector('.video-time__current'),
                    durationTxt = document.querySelector('.video-time__duration');
                durationPlus = document.querySelector('.video-time__plus');
                durationSeek = document.querySelector('.video-seek__container');
                if(isNaN(duration)){
                    currentTimeTxt.innerText = "\u5b9e\u65f6\u76f4\u64ad";
                    currentTimeTxt.style.height = "3rem" ;
                    $(".video-time__current").css("line-height","3rem");
                    $(".video-time__current").css("font-size","1.3rem");
                    durationTxt.innerText = "";
                    durationPlus.innerText = "";
                    progressBar.style.display="none";
                    durationSeek.style.display="none";
                }else{
                    progressBar.style.display="block";
                    durationSeek.style.display="block";
                    durationPlus.innerText = "/";
                    $(".video-time__current").css("line-height","");
                    $(".video-time__current").css("font-size","");

                    currentTimeTxt.innerText = formatSeconds(currentTime);
                    durationTxt.innerText = formatSeconds(duration);
                    progressBar.style.width = (currentTime / duration) * 100 + '%';
                }

            }
            // 获取元素的偏移量
            function left(elem) {
                var left = elem.offsetLeft,
                    parent = elem.offsetParent;
                while (parent) {
                    left += parent.offsetLeft;
                    parent = parent.offsetParent;
                }
                return left;
            }

            function formatSeconds(value) {
                var theTime = parseInt(value);// 秒
                var theTime1 = 0;// 分
                var theTime2 = 0;// 小时
                if(theTime > 60) {
                    theTime1 = parseInt(theTime/60);
                    theTime = parseInt(theTime%60);
                    if(theTime1 > 60) {
                        theTime2 = parseInt(theTime1/60);
                        theTime1 = parseInt(theTime1%60);
                    }
                }
                var result = ""+parseInt(theTime)+"";
                if(theTime1 > 0) {
                    result = ""+parseInt(theTime1)+":"+result;
                }else{
                    result = "00:"+result;
                }
                if(theTime2 > 0) {
                    result = ""+parseInt(theTime2)+":"+result;
                }else{
                    result = "00:"+result;
                }
                return result;
            }

            //增加全屏函数，modified by tim peng 20170627
            function fullscreenFunction(elem) {
                var prefix = 'webkit';
                if (elem[prefix + 'EnterFullScreen']) {
                    return prefix + 'EnterFullScreen';
                } else if (elem[prefix + 'RequestFullScreen']) {
                    return prefix + 'RequestFullScreen';
                };
                return false;
            };


            var videoControl = {
                main: document.querySelector(".video-controls"),
                init: function() {
                    var videoIcon = document.querySelector(".video-icon"),
                        fullscreenIcon = document.querySelector(".full-status"),    //增加全屏DIV modified by tim peng 20170627
                        videoSeek = document.querySelector(".video-seek");

                    // 播放暂停按钮
                    videoIcon.addEventListener("click", function() {
                        if (video.paused) {
                            video.play();
                            videoIcon.classList.remove('video-play');
                        } else {
                            video.pause();
                            videoIcon.classList.add('video-play');
                        }
                    });

                    // 全屏按钮 modified by tim peng 20170627
                    fullscreenIcon.addEventListener("click", function () {
                        try {

                            if (isAndroid) {  //安卓手机才处理
                                if(video.getAttribute("x5-video-orientation")=="portrait"){
                                    // canvas.style.width = width  + 'px';
                                    // canvas.style.height = height  + 'px';
                                    video.setAttribute("x5-video-orientation", "landscape");
                                    $(".videoTitle").css("display","none");
                                    var e = video.requestFullscreen || video.webkitEnterFullScreen || video.webkitRequestFullscreen || video.webkitRequestFullScreen;
                                    e.apply(video);
                                    $(".container").css("padding-bottom","56.5%");
                                    $(".liveHeader").css("top","5rem");
                                    $(".danmu_bar").css("top","5rem");
                                }else{
                                    // canvas.style.width = width * 9 / 16 + 'px';
                                    // canvas.style.height = height * 9 / 16 + 'px';
                                    video.setAttribute("x5-video-orientation", "portrait");
                                    $(".videoTitle").css("display","block");
                                    $(".liveHeader").css("top","2rem");
                                    $(".danmu_bar").css("top","2rem");
                                }
                            }else{
                                var fullscreenvideo = fullscreenFunction(video);
                                video[fullscreenvideo]();
                            }

                        }catch (e) {
                            //alert(e);
                        }

                    });

                    // 进度条拖放
                    var lastX, currentX = 0,
                        offsetX,
                        percentage,
                        offsetLeft = left(videoSeek),
                        clientWidth = videoSeek.clientWidth;

                    // 播放进度条拖动和点击


                    videoSeek.addEventListener('touchstart', function(e) {
                        transformOrigin2 = window.getComputedStyle(videoSeek).transformOrigin;
                        offsetLeft = left(videoSeek);
                        clientWidth = videoSeek.clientWidth;
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '0ms';
                        lastX = e.touches[0].pageX;
                        currentX = 0;
                    }, false);

                    videoSeek.addEventListener('touchstart',  function(e) {
                        transformOrigin2 = window.getComputedStyle(videoSeek).transformOrigin;
                        offsetLeft = left(videoSeek);
                        clientWidth = videoSeek.clientWidth;
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '0ms';
                        lastX = e.touches[0].pageX;
                        currentX = 0;
                    }, false);
                    videoSeek.addEventListener('touchmove', function(e) {
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '8ms';
                        currentX = e.touches[0].pageX;
                        percentage = (currentX - offsetLeft) / clientWidth;
                        video.currentTime = percentage * video.duration;
                    }, false);
                    videoSeek.addEventListener('touchend', function(e) {
                        var target = e.target;
                        target.style.webkitTransitionDuration = target.style.transitionDuration = '8ms';
                        if (currentX == 0) {
                            percentage = (lastX - offsetLeft) / clientWidth;
                            video.currentTime = percentage * video.duration;
                        }
                    }, false);
                },
                videoPause: function() {
                    var videoIcon = document.querySelector(".video-icon");
                    video.pause();
                    videoIcon.classList.add('video-play');
                    //alert("test3");
                },
                toggleShow: function() {
                    var _self = this;
                    console.log(_self.main.classList.contains("show"))
                    if (_self.main.classList.contains("show")) {
                        _self.main.classList.remove("show");

                        //$(".video-controls").fadeOut();
                        //$(".icon-refresh").fadeOut();
                        //$(".liveHeader").fadeOut();

                        $(".icon-refresh").css("display","none");
                        $(".liveHeader").css("display","none");
                        showflag=0;
                    } else {
                        _self.main.classList.add("show");
                        $(".icon-refresh").css("display","block");
                        $(".liveHeader").css("display","block");
                        //$(".video-controls").fadeIn();
                        //$(".icon-refresh").fadeIn();
                        //$(".liveHeader").fadeIn();

                        showflag=1;
                    }
                    //自动消失弹出框
                    if(showflag==1 && showStart==1){
                        showStart=0;
                        setTimeout(function(){
                            _self.main.classList.remove("show");
                            //$(".video-controls").fadeOut();
                            $(".icon-refresh").fadeOut();
                            $(".liveHeader").fadeOut();
                            showStart=1;
                        }, 10000);

                    }

                    //如果暂停了，再播放。
                    if(video.pause)
                    {
                        video.play();
                        videoIcon.classList.remove('video-play');
                    }
                }
            }
            videoControl.init();
        }
    };
});