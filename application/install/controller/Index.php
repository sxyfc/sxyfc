<?php

namespace app\install\controller;

use app\install\util\update;
use think\Cache;
use think\Config;
use think\Controller;
use think\Db;
use think\Request;
use think\View;

class Index extends Controller
{
    public function _initialize()
    {//检测安装
        if (file_exists(CONF_PATH . 'install.lock')) {
            die("请删除文件config目录下面的" . 'install.lock文件');
        }
        fun_helper('http', 'string', 'file', 'debug');
        error_reporting(0);
        define("API_URL", "http://cloud.bao8.org/");
        $this->view = new View();
        //PHP >= 5.4.0
        //PDO PHP Extension
        //MBstring PHP Extension
        //CURL PHP Extension
        Config::set('app_debug', true);
        if (phpversion() < '5.4.0') exit('您的php版本过低，不能安装本软件，请升级到5.4.0或更高版本再安装，谢谢！');
        if (file_exists(CACHE_PATH . 'install.lock')) exit('您已经安装过MHCMS,如果需要重新安装，请删除 ' . CONF_PATH . "/install.lock 文件！");
        //MBstring
        if (!extension_loaded('PDO')) {
            $this->error_msg = "鸣鹤CMS 需要安装扩展 PDO";
        }

        if (!extension_loaded('MBstring')) {
            $this->error_msg = "鸣鹤CMS 需要安装扩展 MBstring";
        }

        if (!extension_loaded('CURL')) {
            $this->error_msg = "鸣鹤CMS 需要安装扩展 CURL";
        }
        if (isset($this->error_msg)) {
            $this->error("您好，发生了错误：" . $this->error_msg);
        }

    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function start_install($step = 1)
    {
        global $_GPC;
        $_GPC = input('param.');
        //执行当前安装
        if (Request::instance()->isPost()) {
            $action_step = "do_step_" . ($step - 1);
            if ($action_step) {
                $this->$action_step();
            }
        }
        //并展示下一步安装界面
        switch ($step) {
            case 1:
                $data = [];
                $data['env'] = self::checkNnv();
                $data['dir'] = self::checkDir();
                $data['func'] = self::checkFunc();
                $this->view->data = $data;
                break;
            case 2:
                //填写授权码
                Config::load(CONF_PATH . 'licence.php');
                $product_licence_code = config('licence.product_licence_code');
                $this->view->product_licence_code = $product_licence_code;
                break;
            case 3:

                break;
            case 4:
                break;
            case 5:
                // 获取系统需要更新的表

                $this->do_step_4();
                break;
            case 6 :


                $res1 = Db::name('users')->where(['id' => 1])->find();
                if (!$res1) {
                    // create admin account
                    $admin_info = Cache::get('admin_info');
                    $user_data['id'] = 1;
                    $user_data['user_name'] = $admin_info['user_name'];
                    $user_data['site_id'] = 0;
                $user_data['nickname'] = "超级管理员";
                $user_data['user_email'] = "";
                $user_data['user_crypt'] = random(6);
                $user_data['pass'] = crypt_pass($admin_info['password'], $user_data['user_crypt']);
                $user_data['user_status'] = 1;
                $user_data['user_role_id'] = 1;
                $user_data['created'] = date("Y-m-d H:i:s");
                $res1 = Db::name('users')->insert($user_data, false, true);
                Cache::set('admin_info', null);
        }


                //创建root
                $res2 = Db::name('roots')->where(['id' => 1])->find();

                if (!$res2) {
                    $root_info = [];
                    $root_info['id'] = 1;
                    $root_info['root_name'] = "默认域名";

                    $domain_info = explode(".", $_SERVER['HTTP_HOST']);

                    if (count($domain_info) == 2) {
                        $root_info['root_domain'] = $_SERVER['HTTP_HOST'];
                    }

                    if (count($domain_info) == 3) {
                        $root_info['root_domain'] = $domain_info[1] . "." . $domain_info[2];
                    }
                    $res2 = Db::name('roots')->insert($root_info, false, true);
                }


                //site root

                $res3 = Db::name('sites')->where(['id' => 1])->find();
                if (!$res3) {
                    $default_site = [];
                    $default_site['id'] = 1;
                    $default_site['site_name'] = "鸣鹤PHP内容管理系统";
                    $default_site['site_domain'] = $domain_info[0];
                    $default_site['default'] = 1;
                    $default_site['root_id'] = $res2;
                    $res3 = Db::name('sites')->insert($default_site);
                }else{
                    $default_site = [];
                    $default_site['id'] = 1;
                    $default_site['site_name'] = "鸣鹤PHP内容管理系统";
                    $default_site['site_domain'] = $domain_info[0];
                    $default_site['default'] = 1;
                    $default_site['root_id'] = $res2;
                    $res3 = Db::name('sites')->where(['id'=>1])->update($default_site);
                }

                if ($res1 && $res2 && $res3) {
                    // write install.lock
                    file_put_contents(CONF_PATH . 'install.lock', 1);
                } else {
                    $this->error("对不起，初始化数据失败！");
                }
                break;
        }

        return $this->view->fetch($step);
    }

    /**
     * 安装授权码
     */
    public function do_step_1()
    {
        global $_GPC;
        $product_licence_code = $_GPC['product_licence_code'];
        $res = self::check($product_licence_code);
        if ($res != "OK") {
            $this->error($res);
        }
    }

    /**
     *
     *  检测目录是否可写
     **/
    public static function checkDir()
    {
        $chmod_file = "chmod.txt";
        $files = file(CONF_PATH . $chmod_file);
        foreach ($files as $_k => $file) {
            $file = str_replace('*', '', $file);
            $file = trim($file);
            if (is_dir(SYS_PATH . $file)) {
                $is_dir = '1';
                $cname = '目录';
                //继续检查子目录权限，新加函数
                $write_able = writable_check(SYS_PATH . $file);
            } else {
                $is_dir = '0';
                $cname = '文件';
            }
            //新的判断
            if ($is_dir == '0' && is_writable(SYS_PATH . $file)) {
                $is_writable = 1;
            } elseif ($is_dir == '1' && dir_writeable(SYS_PATH . $file)) {
                $is_writable = $write_able;
                if ($is_writable == '0') {
                    $no_writablefile = 1;
                }
            } else {
                $is_writable = 0;
                $no_writablefile = 1;
            }

            $filesmod[$_k]['file'] = $file;
            $filesmod[$_k]['is_dir'] = $is_dir;
            $filesmod[$_k]['cname'] = $cname;
            $filesmod[$_k]['is_writable'] = $is_writable;
        }

        return $filesmod;
    }

    /**
     * 数据库信息 安装
     */
    public function do_step_2()
    {
        global $_GPC;
        $_GPC = input('param.');
        if ($this->request->isPost()) {
            if (file_exists(CONF_PATH . 'database.php') && !is_writable(CONF_PATH . 'database.php')) {
                $this->error(CONF_PATH . 'database.php 无读写权限！');
                return false;
            }

            $_GPC['type'] = 'mysql';
            $rule = [
                'hostname|服务器地址' => 'require',
                'hostport|数据库端口' => 'require|number',
                'database|数据库名称' => 'require',
                'username|数据库账号' => 'require',
                'password|数据库密码' => 'require',
                'prefix|数据库前缀' => 'require|regex:^[a-z0-9]{1,20}[_]{1}',
                'cover|覆盖数据库' => 'require|in:0,1',
            ];
            $validate = $this->validate($_GPC, $rule);
            if (true !== $validate) {
                $this->error($validate);
                return false;
            }
            $cover = $_GPC['cover'];
            unset($_GPC['cover']);
            // 不存在的数据库会导致连接失败
            $database = $_GPC['database'];
            unset($_GPC['database']);
            // 创建数据库连接
            $db_connect = Db::connect($_GPC);
            // 检测数据库连接


            try {
                $db_connect->execute("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8");
            } catch (\Exception $e) {
                $this->error('数据库连接失败，自动创建数据库失败！' . $db_connect->getError());
            }
            $test = $db_connect->execute( " show databases like '$database';");
            if(!$test){
                $this->error('数据库连接失败，数据库不存在！');
            }
            // 不覆盖检测是否已存在数据库
            if (!$cover) {
                $check = $db_connect->execute('SELECT * FROM information_schema.schemata WHERE schema_name="' . $database . '"');
                if ($check) {
                    $_GPC['database'] = $database;
                    self::write_db_config($_GPC);
                    $this->success('数据库连接成功 ，该数据库已存在，如需覆盖，请选择覆盖数据库！');
                }
            }
            // 创建数据库
            $_GPC['database'] = $database;
            // 生成配置文件
            self::write_db_config($_GPC);
            $this->success('数据库连接成功', '');
            return true;
        }

    }

    /**
     * 第三部 计算出需要更新的文件列表
     */
    public function do_step_3()
    {
        global $_GPC;
        //保存用户名//保存密码
        $admin_info['user_name'] = $_GPC['account'];
        $admin_info['password'] = $_GPC['password'];
        if ($admin_info['user_name'] && $admin_info['password']) {
            Cache::set('admin_info', $admin_info);
        }
        //计算文件差

        $updater = new update();
        $files_to_update = $updater->file_diff("system");
        $this->view->files_to_update = json_encode($files_to_update);

    }

    /**
     * 第四部 更新数据库
     */
    public function do_step_4()
    {
        global $_GPC;
        $tables = self::get_models();
        $this->view->sys_tables = $tables;
    }

    public static function get_models($module = 'system')
    {
        $models = update::get_models($module);
        return $models;
    }

    public function install_model($table, $module)
    {
        update::create_table($table, $module);
    }

    public static function check($product_licence_code)
    {
        global $_GPC;
        if (!$product_licence_code) {
            //todo load licence from cache
            Config::load(CONF_PATH . 'licence.php');
            $product_licence_code = config('licence.product_licence_code');
        }
        $url = API_URL . 'product/index/check_licence';
        $data['product_sign'] = MODULE_NAME;
        $data['product_licence_code'] = $product_licence_code;
        $data['domain'] = $_SERVER['HTTP_HOST'];
        $res = ihttp_post($url, $data);
        $res = json_decode($res['content'], true);

        if ($res['code'] == 1) {
            $licence['licence'] = $data;
            self::write_config('licence', $licence);
        }
        return $res;
    }

    private static function write_config($config_name, $data)
    {
        file_put_contents(CONF_PATH . $config_name . ".php", '<?php return ' . var_export($data, true) . ';');
    }

    /**
     * 生成数据库配置文件
     * @return array
     */
    private function write_db_config(array $data)
    {
        $code = <<<INFO
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '{$data['hostname']}',
    // 数据库名
    'database'        => '{$data['database']}',
    // 用户名
    'username'        => '{$data['username']}',
    // 密码
    'password'        => '{$data['password']}',
    // 端口
    'hostport'        => '{$data['hostport']}',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '{$data['prefix']}',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'collection',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
    // Builder类
    'builder'         => '',
    // Query类
    'query'           => '\\think\\db\\Query',
];
INFO;
        file_put_contents(CONF_PATH . 'database.php', $code);
        // 判断写入是否成功
        $config = include CONF_PATH . 'database.php';
        if (empty($config['database']) || $config['database'] != $data['database']) {
            return $this->error('数据库配置写入失败,请检测环境是否满足安装要求！');
            exit;
        }
    }

    /*
     * 下载文件
     * 模块 文件md5
     */
    public function download_file($module = 'system', $file_path)
    {
        $res = update::down_file($module, $file_path);
        return $res;
    }


    /**
     * 函数及扩展检查
     * @return array
     */
    private function checkFunc()
    {
        $items = [
            ['pdo', '支持', 'yes', '类'],
            ['pdo_mysql', '支持', 'yes', '模块'],
            ['fileinfo', '支持', 'yes', '模块'],
            ['curl', '支持', 'yes', '模块'],
            ['xml', '支持', 'yes', '函数'],
            ['file_get_contents', '支持', 'yes', '函数'],
            ['mb_strlen', '支持', 'yes', '函数'],
            ['gzopen', '支持', 'yes', '函数'],
        ];

        foreach ($items as &$v) {
            if (('类' == $v[3] && !class_exists($v[0])) || ('模块' == $v[3] && !extension_loaded($v[0])) || ('函数' == $v[3] && !function_exists($v[0]))) {
                $v[1] = '不支持';
                $v[2] = 'no';
                session('install_error', true);
            }
        }

        return $items;
    }

    /**
     * 环境检测
     * @return array
     */
    private function checkNnv()
    {
        $items = [
            'os' => ['操作系统', '不限制', '类Unix', PHP_OS, 'ok'],
            'php' => ['PHP版本', '5.6', '5.6及以上', PHP_VERSION, 'ok'],
            'gd' => ['GD库', '2.0', '2.0及以上', '未知', 'ok'],
        ];
        if ($items['php'][3] < $items['php'][1]) {
            $items['php'][4] = 'no';
            session('install_error', true);
        }
        $tmp = function_exists('gd_info') ? gd_info() : [];
        if (empty($tmp['GD Version'])) {
            $items['gd'][3] = '未安装';
            $items['gd'][4] = 'no';
            session('install_error', true);
        } else {
            $items['gd'][3] = $tmp['GD Version'];
        }

        return $items;
    }

}