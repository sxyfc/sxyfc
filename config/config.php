<?php
//error_reporting(E_ALL ^E_WARNING ^ E_NOTICE);
error_reporting(E_ERROR  | E_PARSE);
//error_reporting(E_ALL ^E_WARNING ^ E_NOTICE);
return [
    // 后台公共模板
    'mhcms_crypt' => 'HFYhjbiuidwhijewnkjcxbhk456456assw' ,
    'admin_base_layout' => APP_PATH . 'admin/view/public/content_frame.html',

    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    // 应用命名空间
    'app_namespace' => 'app',
    // 应用调试模式
    'app_debug' => true,
    // 应用Trace
    'app_trace' => false,
    // 应用模式状态
    'app_status' => '',
    // 是否支持多模块
    'app_multi_module' => true,
    // 入口自动绑定模块
    'auto_bind_module' => false,
    // 注册的根命名空间
    'root_namespace' => [],
    // 扩展配置文件
    // 扩展函数文件
    'extra_file_list' => [
        APP_PATH . 'common' . DIRECTORY_SEPARATOR . 'helper.php',
        THINK_PATH . 'helper' . EXT ,
        APP_PATH   . 'common' . DIRECTORY_SEPARATOR . 'mhcms' . EXT
    ],
    // 默认输出类型
    'default_return_type' => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return' => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler' => 'callback',
    // 默认时区
    'default_timezone' => 'PRC',
    // 是否开启多语言
    'lang_switch_on' => true,
    // 支持的语言列表
    'lang_list' => ['en-us', 'zh-cn'],
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter' => '',
    // 默认语言
    'default_lang' => 'zh-cn',
    // 应用类库后缀
    'class_suffix' => false,
    // 控制器类后缀
    'controller_suffix' => false,
    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------
    // 默认模块名
    'default_module' => 'home',
    // 禁止访问模块
    'deny_module_list' => ['common'],
    // 默认控制器名
    'default_controller' => 'Index',
    // 默认操作名
    'default_action' => 'index',
    // 默认验证器
    'default_validate' => '',
    // 默认的空控制器名
    'empty_controller' => 'Error',
    // 操作方法后缀
    'action_suffix' => '',
    // 自动搜索控制器
    'controller_auto_search' => false,
    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------
    // PATHINFO变量名 用于兼容模式
    'var_pathinfo' => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr' => '/',
    // URL伪静态后缀
    'url_html_suffix' => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param' => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 0,
    // 是否开启路由
    'url_route_on' => true,
    // 路由配置文件（支持配置多个）
    'route_config_file' => ['route', './node_types/route'],
    // 是否强制使用路由
    'url_route_must' => false,
    // 域名部署
    'url_domain_deploy' => true,
    // 域名根，如thinkphp.cn
    'url_domain_root' => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert' => true,
    // 默认的访问控制器层
    'url_controller_layer' => 'controller',
    // 表单请求类型伪装变量
    'var_method' => '_method',
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------
    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
        // 模板缓存
        'tpl_cache'    => true,
    ],
    // 视图输出字符串内容替换
    'view_replace_str' => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => SYS_PATH . "tpl" . DS . 'public' . DS . 'dispatch_success_tmpl.tpl',
    'dispatch_error_tmpl' => SYS_PATH  . 'tpl' . DS . 'public' . DS . 'dispatch_jump_error.php',
    'dispatch_message_tmpl' => SYS_PATH  . 'tpl' . DS . 'public' . DS . 'dispatch_message_tmpl.php',
    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------
    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message' => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg' => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle' => '\\app\\common\\exception\\Http',
    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------
    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type' => 'File',
        // 日志保存目录
        'path' => APP_PATH . "../logs/",
        // 日志记录级别
        'level' => ['error' , 'notice'],
    ],
    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace' => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],
    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache' => [
        // 使用复合缓存类型
        'type' => 'complex',
        // 默认使用的缓存
        'default' => [
            // 驱动方式
            'type' => '\\app\\core\\util\\MhcmsCache',
            // 缓存保存目录
            'path' => CACHE_PATH,
        ],
        // 文件缓存
        'file' => [
            // 驱动方式
            'type' => 'Sqlite',
            // 设置不同的缓存保存目录
            'path' => RUNTIME_PATH . 'file/',
        ],
        // redis缓存
        'redis' => [
            // 驱动方式
            'type' => 'redis',
            // 服务器地址
            'host' => '127.0.0.1',
        ],
    ],
    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------
    'session' => [
        'id' => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix' => 'mhcms_',
        // 驱动方式 支持redis memcache memcached
        'type' => '',
        // 是否自动开启 SESSION
        'auto_start' => true,
        'expire' => 86400
    ],
    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie' => [
        // cookie 名称前缀
        'prefix' => 'mhcms_',
        // cookie 保存时间
        'expire' => 864000,
        // cookie 保存路径
        'path' => '/',
        // cookie 有效域名
        'domain' => '',
        //  cookie 启用安全传输
        'secure' => false,
        // httponly设置
        'httponly' => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],
    // +----------------------------------------------------------------------
    // |  分页配置
    // +----------------------------------------------------------------------
    'paginate' => [
        'type' => 'app\common\util\pager\MhcmsPager',
        'var_page' => 'page',
        'list_rows' => 15,
    ],
    // +----------------------------------------------------------------------
    // | 验证码配置
    // +----------------------------------------------------------------------
    'captcha' => [
        // 验证码字符集合
        'codeSet' => '123456789zbBgGAqpPQdDl',
        // 验证码字体大小(px)
        'fontSize' => 18,
        // 是否画混淆曲线
        'useCurve' => true,
        // 验证码图片高度
        'imageH' => 38,
        // 验证码图片宽度
        'imageW' => 110,
        // 验证码位数
        'length' => mt_rand(1, 1),
        // 验证成功后是否重置
        'reset' => true
    ],
    'auto_timestamp' => "datetime",
    'datetime_format' => true,
    // +----------------------------------------------------------------------
    // | 错误网页模板配置
    // +----------------------------------------------------------------------
    'http_exception_template' => [
        // 定义404错误的重定向页面地址
        //404 => APP_PATH . '../tpl/public/errorpages/404.html',
        // 还可以定义其它的HTTP status
        //401 => APP_PATH . '401.html',
    ],

    // +----------------------------------------------------------------------
    // |  支付配置
    // +----------------------------------------------------------------------
    'pay' => [
        'recharge_ratio' => 10
    ],
    // +----------------------------------------------------------------------
    // |  返回结果
    // +----------------------------------------------------------------------
    'WEB_SUCCESS_RT' => [
        'result' => 0,
        'reason' => 'success',
    ],
];
