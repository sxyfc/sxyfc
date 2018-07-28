<?php
ini_set('display_errors', 'off');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept ,Authorization , Cookie");
header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Origin:*');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    //save resources
    die();
}
// 定义应用目录
define("MODULE_NAME" , "mhcms");
define('SYS_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR. 'application' . DIRECTORY_SEPARATOR);
define('CONF_PATH', SYS_PATH . 'config' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', SYS_PATH . 'upload_file' . DIRECTORY_SEPARATOR);
define('SYS_TIME', time());


// 检测PHP环境
if(version_compare(PHP_VERSION,'5.6.0','<')){
    header('Content-Type:text/plain;charset=utf-8');
    die('PHP版本过低，最少需要PHP5.6+，请升级PHP版本！');
}
//请先执行命令
if(!is_dir(SYS_PATH."thinkphp") || !is_dir(SYS_PATH."vendor")){
    header('Content-Type:text/plain;charset=utf-8');

    echo "请登录您的服务器，切换到您的'cd 您的网站目录'; 下方，执行'php composer.phar install',不包含引号 .<br />";
    echo "如果您是虚拟主机，请到官方论坛下载完整版本的安装程序在进行安装操作.<br />";
    die();
}
//来源
define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
//检测安装
if(!file_exists(CONF_PATH . 'install.lock')){
    define('BIND_MODULE', 'install');
}
// 加载框架基础引导文件

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';