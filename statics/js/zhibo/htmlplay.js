var u = navigator.userAgent,isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),videofs=$('#videofs');
var HTML5_ID_BASE=0;
var pro,stp;
function html5playerRun(conf){
	var mode = /^\d{0,6}(\%)?$/;
	var width = mode.test(conf.width) ? conf.width : '100%';
	var height = mode.test(conf.height) ? conf.height : '100%';
	HTML5_ID_BASE++;
	this.uuid  = 'html5Media' + HTML5_ID_BASE;
	this.hlsUrl=conf.hlsUrl;
	this.container=conf.mediaid;
	this.autostart=conf.autostart;
	this.volume = conf.volume ? conf.volume : 80;            //音量	
	this.adveDeAddr = conf.adveDeAddr ? conf.adveDeAddr : '';//播放前显示图片地址
	this.isdisplay = conf.controlbardisplay ? conf.controlbardisplay : 'enable';//进度条显示，取值："enable" 和 "disable"。 默认为disable
	var _this=this;
    if(this.isdisplay == 'disable'){
	  var html='<video id="'+this.uuid+'" preload="auto" width="'+width+'" height="'+height+'" poster="'+this.adveDeAddr+'" webkit-playsinline  playsinline src="'+this.hlsUrl+'" type="application/x-mpegURL"></video>';
	  if(this.autostart == true)
		 html='<video id="'+this.uuid+'" autoplay preload="auto" width="'+width+'" height="'+height+'" poster="'+this.adveDeAddr+'" webkit-playsinline playsinline type="application/x-mpegURL" src="'+this.hlsUrl+'" ></video>';
    }else if(this.isdisplay == 'enable'){
      var html='<video id="'+this.uuid+'" controls preload="auto" width="'+width+'" height="'+height+'" poster="'+this.adveDeAddr+'" webkit-playsinline playsinline src="'+this.hlsUrl+'" type="application/x-mpegURL"></video>';
	  if(this.autostart == true){ 
		 html='<video id="'+this.uuid+'" autoplay controls preload="auto" width="'+width+'" height="'+height+'" poster="'+this.adveDeAddr+'" webkit-playsinline playsinline type="application/x-mpegURL" src="'+this.hlsUrl+'" ></video>';
      }
	  if(!isiOS){
	     html='<video style="object-position:0px 0px" id="html5Media2"   width="'+width+'" height="'+height+'" poster="'+this.adveDeAddr+'" type="application/x-mpegURL" src="'+this.hlsUrl+'" x5-video-player-type="h5" x5-video-player-fullscreen="true"></video>';
	  }
	  document.getElementById(conf.container).innerHTML=html;
	   if(!isiOS){
				
				this.uuid='html5Media2';
				this.autostart=false;
				var istimeDispose=true;
				var $play = $("#html5Media2");
				var _play = $play[0];
				$('.max-start').show();
				$('.all-vide').click(function(){
						if (_play.paused){
							_play.play();
							$('.sche-start').html('&#xe672;');
							pro=setInterval(getProgress, 60);
						}	
				});
				 $(".process").click(function (e) {
					var mX = e.clientX;
					var l = mX-$(".process-bar").offset().left;
						console.log(mX+"+++++"+l);
						var fullwidth = $(".process").width();
						if(l<0){
							l=0;
						}else if(l>fullwidth){
							l=fullwidth;
						}
						clearInterval(pro);
						$("#mybar").css('left',l);
						var p = Math.floor(l*100/fullwidth);
						$(".process-bar").css('width',p+'%');
						var time=document.getElementById(_this.uuid).duration;
						document.getElementById(_this.uuid).currentTime=Math.floor(l*time/fullwidth);
						pro=setInterval(getProgress, 60);
				})
				$("#mybar").on('touchstart', function(event) {
					$(".process").on("touchmove", function (e) {
						clearInterval(pro);
						var mX = e.originalEvent.targetTouches[0].pageX;
						var l = mX-$(".process-bar").offset().left;
						 var fullwidth = $(".process").width();
						console.log(mX+"+++++"+l);
						if(l<0){
							l=0;
						}else if(l>fullwidth){
							l=fullwidth;
						}
						$("#mybar").css('left',l);
						var p = Math.floor(l*100/fullwidth);
						$(".process-bar").css('width',p+'%');
						var time=document.getElementById(_this.uuid).duration;
						document.getElementById(_this.uuid).currentTime=Math.floor(l*time/fullwidth);
						var ctime=document.getElementById(_this.uuid).currentTime;
						var ch=ctime/3600>=10?Math.floor(ctime/3600):'0'+Math.floor(ctime/3600);
						var cm=ctime%3600/60>=10?Math.floor(ctime%3600/60):'0'+Math.floor(ctime%3600/60);
						var cs=ctime%3600%60>=10?Math.floor(ctime%3600%60):'0'+Math.floor(ctime%3600%60);
						$('.sche-nowtime').html(ch+':'+cm+':'+cs);
					});
					$(".process").on("touchend", function () {
						document.getElementById(_this.uuid).play();
						pro=setInterval(getProgress, 60);
					})
				 });  
				
				function getProgress(){
						var ctime=document.getElementById(_this.uuid).currentTime;
						var time=document.getElementById(_this.uuid).duration; 
						var percent =ctime /time;
						var h=time/3600>=10?Math.floor(time/3600):'0'+Math.floor(time/3600);
						var m=time%3600/60>=10?Math.floor(time%3600/60):'0'+Math.floor(time%3600/60);
						var s=time%3600%60>=10?Math.floor(time%3600%60):'0'+Math.floor(time%3600%60);
						var ch=ctime/3600>=10?Math.floor(ctime/3600):'0'+Math.floor(ctime/3600);
						var cm=ctime%3600/60>=10?Math.floor(ctime%3600/60):'0'+Math.floor(ctime%3600/60);
						var cs=ctime%3600%60>=10?Math.floor(ctime%3600%60):'0'+Math.floor(ctime%3600%60);
						$('.sche-alltime').html(h+':'+m+':'+s);
						$('.process-bar').width((percent * 100).toFixed(1) + "%");
						$('.sche-nowtime').html(ch+':'+cm+':'+cs);
						$('#mybar').attr('style','left:'+($('.process').width()*percent-2)+'px');
				}	
				_play.addEventListener("pause", function () {
					$('.max-start').show();
					$('.sche-start').html('&#xe623;');//32开始
				}, false);
				_play.addEventListener("playing", function () {
					$('.max-start').hide();
					$('.sche-start').html('&#xe672;');//72暂停
				}, false);
				_play.addEventListener("x5videoenterfullscreen", function() {
					had_in_full = 1;
					video_andiro_in();
					$('.sche-start').html('&#xe672;');
				});
				_play.addEventListener("x5videoexitfullscreen", function(){
					 had_in_full = 0;
					 video_andiro_out();
					 $('.sche-start').html('&#xe623;');
					 $('.main').css('position','relative');
					 $play.removeAttr("x5-video-orientation");
					 $('.TAstate,.contents,.cont-right').show();
					_play.style["object-fit"]= "contain";
					$(".schedule").removeClass('fs-on');
				});
				$play.click(function(){
					var videocontrol=$('.schedule');
					if(videocontrol.css('display')=='block'){
						videocontrol.css('display','none');
					}else{
						videocontrol.css('display','block');
					}
				});

				$('.sche-start').on('click', function(e){
					e.stopPropagation();
					if (_play.paused){
						_play.play();
					}else{
						_play.pause();
					}
				});
				$(document).on("click", ".sche-fs", function(e) {
					var t = $play.attr("x5-video-orientation");
					if(void 0 == t || "" == t){
						$('.main').css('position','static');
						$play.attr("x5-video-orientation", "landscape");
						_play.style["object-position"]="0px 0px";
						$('.TAstate,.contents,.cont-right,.header-an').hide();
						_play.style["object-fit"]= "cover";
						 $(".schedule").addClass('fs-on');
					}else{
						$('.main').css('position','relative');
						$play.removeAttr("x5-video-orientation");
						//$('.topimg,.tabs,#tab,toolmenu,.login_box,#titname').show();
						//$(".onlineuser").css('top','6rem');
						$('.TAstate,.contents,.cont-right,.header-an').show();
						_play.style["object-fit"]= "contain";
						$(".schedule").removeClass('fs-on');
					}
					e.stopPropagation();
				});
		  }
	}
}
	