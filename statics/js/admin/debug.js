(function(gVWQc1){"use strict";var JOS2=JOS2||{};function i(VTv3){if(typeof VTv3==="function"){VTv3={onchange:VTv3}}VTv3=VTv3||{};var VLnuHYvFT4=VTv3["delay"]||1e3;var uAu5={};uAu5["onchange"]=VTv3["onchange"];var bb6;var LCa7=new Image;LCa7["__defineGetter__"]("id",function(){bb6="on"});var GgVw8="unknown";function f(){return GgVw8}uAu5["getStatus"]=f;function u(){if(window["Firebug"]&&window["Firebug"]["chrome"]&&window["Firebug"]["chrome"]["isInitialized"]){a("on");return}bb6="off";console["log"](LCa7);console["clear"]();a(bb6)}function a(eGRF9){if(GgVw8!==eGRF9){GgVw8=eGRF9;if(typeof uAu5["onchange"]==="function"){uAu5["onchange"](eGRF9)}}}var YcavK10=setInterval(u,VLnuHYvFT4);window["addEventListener"]("resize",u);var y12;function l(){if(y12){return}y12=true;window["removeEventListener"]("resize",u);clearInterval(YcavK10)}uAu5["free"]=l;return uAu5}JOS2["create"]=i;if(typeof define==="function"){if(define["amd"]||define["cmd"]){define(function(){return JOS2})}}else if(typeof module!=="undefined"&&module["exports"]){module["exports"]=JOS2}else{window[gVWQc1]=JOS2}})("larry");

layui.use(["jquery", "layer", "element", "common"], function () {
    var o = layui.jquery, r = layui.layer, e = layui.common, i = layui.device(), t = layui.element;
    var n = {};
    var a = navigator.userAgent.toLowerCase();
    var c;
    (c = a.match(/msie ([\d.]+)/)) ? n.ie = c[1] : (c = a.match(/firefox\/([\d.]+)/)) ? n.firefox = c[1] : (c = a.match(/chrome\/([\d.]+)/)) ? n.chrome = c[1] : (c = a.match(/opera.([\d.]+)/)) ? n.opera = c[1] : (c = a.match(/version\/([\d.]+).*safari/)) ? n.safari = c[1] : 0;
    if (n.firefox) {
        window.location.href = "/home/index/"
    }
    if (n.safari) {
        window.location.href = "https://fc.xyzswl.cn"
    }
    if (n.opera) {
        window.location.href = "https://fc.xyzswl.cn"
    }
    if (!n.chrome) {
        if (!n.ie) {
            window.location.href = "https://fc.xyzswl.cn"
        } else if (n.ie && i.ie < 8) {
            window.location.href = "https://fc.xyzswl.cn"
        } else if (n.ie && i.ie > 8) {
            n.larry = true
        }
    }
    var l = window.location.host;
    var f = document.domain;

    function w() {
        window.location.href = "https://fc.xyzswl.cn/";
        return false
    }

    function s() {
        if (window.console && (console.firebug || console.table && /firebug/i.test(console.table())) || typeof opera == "object" && typeof opera.postError == "function" && console.profile.length > 0) {
            w()
        }
        if (typeof console.profiles == "object" && console.profiles.length > 0) {
            w()
        }
    }

    s();
    window.onresize = function () {
        if (top.window.outerHeight - top.window.innerHeight > 200) {
            w()
        }
        if (top.window.outerWidth - top.window.innerWidth > 200) {
            w()
        }
    };
    o(document).keydown(function () {
        return m(arguments[0])
    });
    function m(o) {
        var r;
        if (window.event) {
            r = o.keyCode
        } else if (o.which) {
            r = o.which
        }
        if (r == 123) {
            e.larryCmsError("Debug Error", e.larryCore.tit);
            return false
        }
        if (o.ctrlKey) {
            if (o.shiftKey && r == 73) {
                e.larryCmsError("Debug Error", e.larryCore.tit);
                return false
            }
        }
        if (o.ctrlKey && r == 83) {
            e.larryCmsError("Debug Error", e.larryCore.tit);
            return false
        }
    }
});