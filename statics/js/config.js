var path_obj = {
    baseUrl: '/statics/js/app',
    paths: {
        'wx': ['../../components/weixin/jweixin-1.3.0'],
        'css': '../lib/css.min',
        'html2canvas': ['../../components/html2canvas/html2canvas.min'],
        'Vue': ['../../components/vue/vue'],
        'vue': ['../../components/vue/require-vuejs.min'],
        'mhui': ['../../components/vue_components/mhui'],
        'mhui_filters': ['../../components/vue_components/filters'],
        'axios': ['https://unpkg.com/axios/dist/axios.min'],
        'jquery': '../jquery.min',
        'jquery.qrcode': '../../components/qrcode/jquery.qrcode.min',
        'jquery.ui': '../../components/jquery-ui/jquery-ui.min',
        'jquery.caret': '../lib/jquery.caret',
        'jquery.jplayer': '../../components/jplayer/jquery.jplayer.min',
        'jquery.zclip': '../../components/zclip/jquery.zclip.min',
        'bootstrap': 'https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min',
        'bootstrap.switch': '../../components/switch/bootstrap-switch.min',
        'angular': '../lib/angular.min',
        'angular.sanitize': '../lib/angular-sanitize.min',
        'underscore': '../lib/underscore-min',
        'chart': '../lib/chart.min',
        'moment': '../lib/moment',
        'filestyle': '../lib/bootstrap-filestyle.min',
        'datetimepicker': '../../components/datetimepicker/jquery.datetimepicker',
        'daterangepicker': '../../components/daterangepicker/daterangepicker',
        'colorpicker': '../../components/colorpicker/spectrum',
        'map': 'http://api.map.baidu.com/getscript?v=2.0&ak=F51571495f717ff1194de02366bb8da9&services=&t=20140530104353',
        'editor': '../../components/tinymce/tinymce.min',
        'kindeditor': '../../components/kindeditor/lang/zh_CN',
        'kindeditor.main': '../../components/kindeditor/kindeditor-min',
        'webuploader': '../../components/webuploader/webuploader.min',
        'fileUploader': '../../components/fileuploader/fileuploader.min',
        'swiper': '../../components/swiper/swiper.jquery.min',
        'swiper4': '../../components/swiper/swiper4.min',
        'semantic': '../../components/semantic/semantic.min',
        'json2': '../lib/json2',
        'wapeditor': './wapeditor',
        'jquery.wookmark': '../lib/jquery.wookmark.min',
        'validator': '../lib/bootstrapValidator.min',
        'select2': '../../components/select2/zh-CN',
        'clockpicker': '../../components/clockpicker/clockpicker.min',
        'district': '../lib/district',
        'hammer': '../lib/hammer.min',
        'handlebars': '../lib/handlebars',
        'layui': '../../components/layui/layui',
        'ELEMENT': '../../components/element_ui/element-ui',
        'pjax': '../../components/pjax/jquery.pjax',
        'fastclick': '../../components/fastclick/fastclick',
        'VueLazyload': '../../components/vue_components/vue-lazyload',
        'mhcms':'../../components/mhcms/mhcms',
        'weui':'../../components/weui/weui.min',
        'mhcms_level_picker':'../../components/weui/level_picker',
        'sortable':'../../components/sortable/sortable.min',
        'distpicker':'../../components/weui/distpicker',

    },
    shim: {
        'mhcms': {
            deps: ['jquery'],
            exports: "mhcms"
        },
        'weui': {
            deps: ['jquery'],
            exports: "weui"
        },
        'sortable': {
            deps: ['jquery'],
        },
        'mhcms_level_picker': {
            deps: ['jquery' , 'weui'],
            exports: "level_picker"
        },
        'distpicker': {
            deps: ['jquery'],
            exports: "distpicker"
        },
        'wx': {
            exports: "wx"
        },
        'VueLazyload': {
            deps: ['jquery'],
            exports: "VueLazyload"
        },
        "Vue": {"exports": "Vue"}
        ,
        'jquery.ui': {
            exports: "jquery-ui",
            deps: ['jquery', 'css!../../components/jquery-ui/jquery-ui.min']
        },
        'pjax': {
            deps: ['jquery']
        },
        'fastclick': {
            deps: ['jquery']
        },
        'ELEMENT': {
            exports: "element",
            deps: ['Vue']
        },
        'jplayer': {
            exports: "jplayer",
            deps: ['jquery']
        }
        ,
        'mhui': {
            exports: "mhui",
            deps: ['vue']
        }
        ,
        'mhui_filters': {
            exports: "mhui_filters",
            deps: ['vue']
        }
        ,

        'swiper': {
            deps: ['jquery', 'css!../../components/swiper/swiper.min']
        },
        swiper4: {
            deps: ['css!../../components/swiper/swiper4.min']
        },
        'layui': {
            exports: "layui",
            deps: ['jquery', 'css!../../components/layui/css/layui.css']
        },
        'adminTabs': {
            deps: ['jquery']
        },
        'semantic': {
            deps: ['jquery']
        },
        'jquery.caret': {
            exports: "$",
            deps: ['jquery']
        },
        'jquery.jplayer': {
            exports: "$",
            deps: ['jquery']
        },
        'bootstrap': {
            exports: "$",
            deps: ['jquery']
        },
        'bootstrap.switch': {
            exports: "$",
            deps: ['bootstrap', 'css!../../components/switch/bootstrap-switch.min.css']
        },
        'angular': {
            exports: 'angular',
            deps: ['jquery']
        },
        'angular.sanitize': {
            exports: 'angular',
            deps: ['angular']
        },
        'emotion': {
            deps: ['jquery']
        },
        'chart': {
            exports: 'Chart'
        },
        'filestyle': {
            exports: '$',
            deps: ['bootstrap']
        },
        'daterangepicker': {
            exports: '$',
            deps: ['bootstrap', 'moment', 'css!../../components/daterangepicker/daterangepicker.css']
        },
        'datetimepicker': {
            exports: '$',
            deps: ['jquery', 'css!../../components/datetimepicker/jquery.datetimepicker.css']
        },
        'kindeditor': {
            deps: ['kindeditor.main', 'css!../../components/kindeditor/themes/default/default.css']
        },
        'colorpicker': {
            exports: '$',
            deps: ['css!../../components/colorpicker/spectrum.css']
        },
        'map': {
            exports: 'BMap'
        },
        'json2': {
            exports: 'JSON'
        },
        'fileUploader': {
            deps: ['webuploader', 'css!../../components/webuploader/webuploader.css', 'css!../../components/webuploader/style.css']
        },
        'webuploader': {
            deps: ['css!../../components/webuploader/webuploader.css', 'css!../../components/webuploader/style.css']
        },
        'wapeditor': {
            exports: 'angular',
            deps: ['angular.sanitize', 'jquery.ui', 'underscore', 'fileUploader', 'json2', 'datetimepicker']
        },
        'jquery.wookmark': {
            exports: "$",
            deps: ['jquery']
        },
        'validator': {
            exports: "$",
            deps: ['bootstrap']
        },
        'select2': {
            deps: ['css!../../components/select2/select2.min.css', './resource/components/select2/select2.min.js']
        },
        'clockpicker': {
            exports: "$",
            deps: ['css!../../components/clockpicker/clockpicker.min.css', 'bootstrap']
        },
        'jquery.qrcode': {
            exports: "$",
            deps: ['jquery']
        },
        'district': {
            exports: "$",
            deps: ['jquery']
        },
        'hammer': {
            exports: 'hammer'
        }
    },
    waitSeconds: 0,

};

if(typeof urlArgs !== "undefined"){
    path_obj.urlArgs = urlArgs;
}
require.config(path_obj);