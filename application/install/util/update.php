<?php

namespace app\install\util;

use think\Config;
use think\Db;

class update
{
    private $_upgrade_md5 = 'http://cloud.bao8.org/product/service/list_files';
    public static $system_modules = ['core', 'admin', 'attachment', 'common', 'system', 'sms', 'update', 'member', 'advertise' ,'order' , 'pay', 'wechat' , 'wechat_follow' , 'smallapp' , 'home' , 'sso'];

    /**
     * 下载文件
     * @param $module
     * @param $file_path
     * @return array
     */
    public static function down_file($module = 'system', $file_path)
    {
        global $_GPC;
        Config::load(CONF_PATH . 'licence.php');
        $url = API_URL . 'product/service/download_file';
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $licence['file_path'] = $file_path;
        // $licence['method'] = 'application.shipping';
        $licence['gz'] = function_exists('gzcompress') && function_exists('gzuncompress') ? 'true' : 'false';
        $licence['download'] = 'true';
        $headers = array('content-type' => 'application/x-www-form-urlencoded');
        $res = ihttp_request($url, $licence, $headers, 62);
        if (!isset($res['headers']['鸣鹤CMS_file_ok'])) {
            $ret = [
                'code' => 0,
                'msg' => '下载文件失败' . SYS_PATH . $file_path
            ];
            return $ret;
        }
        $res = $res['content'];

        $res = self::write_file(SYS_PATH . $file_path, $res);
        if (false !== $res) {
            $ret = [
                'code' => 1,
                'msg' => '下载成功'
            ];
        } else {
            $ret = [
                'code' => 0,
                'msg' => '下载文件成功，文件写入权限不足' . SYS_PATH . $file_path
            ];
        }
        return $ret;
    }

    public static function write_file($path, $data)
    {
        @mkdirs(dirname($path), true);
        return file_put_contents($path, $data, LOCK_EX);
    }

    public static function get_models($module)
    {
        Config::load(CONF_PATH . 'licence.php');
        $url = API_URL . 'product/service/get_models';
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $res = ihttp_request($url, $licence, 300);
        $res = json_decode($res['content'], true);
        return $res;
    }

    public static function create_table($table, $module = 'system')
    {
        Config::load(CONF_PATH . 'licence.php');
        $url = API_URL . 'product/service/get_table_schema';
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $licence['table_name'] = $table;
        $res = ihttp_request($url, $licence);
        $res = json_decode($res['content'], true);
        if ($res['code'] == 1) {
            $res['code'] = self::sql_execute($res['data']);
            if ($res['code']) {
                $res['code'] = 1;
                $res['msg'] = "$table 表安装成功";
            } else {
                $res['code'] = 0;
                $res['msg'] = "$table 表安装失败";
            }
            echo json_encode($res);
        } else {
            $res['code'] = 0;
            $res['msg'] = "$table 该表已经过时跳过";
            echo json_encode($res);
        }
    }

    /**
     * 执行mysql.sql文件，创建数据表等
     * @param string $sql sql语句
     * @return bool
     */
    public static function sql_execute($sql)
    {
        $sqls = self::sql_split($sql);
        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if (trim($sql) != '') {
                    Db::execute($sql);
                }
            }
        } else {
            Db::execute($sqls);
        }
        return true;
    }

    /**
     * 处理sql语句，执行替换前缀都功能。
     * @param string $sql 原始的sql，将一些大众的部分替换成私有的
     * @return array
     */
    public static function sql_split($sql)
    {
        global $_W;
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
            }
            $num++;
        }
        return $ret;
    }

    /**
     * 检测需要更新的文件 对应的模块
     * @param $module
     * @return array
     */
    public function file_diff($module)
    {
        $this->gen_module_file_list('system'); // 本地md5文件生成
        $server_md5s = $this->get_module_files('system'); // 获取服务端文件列表
        //计算数组差集
        $ret['diffs'] = $diffs = array_diff($server_md5s, $this->md5_arr);
        //丢失文件列表
        $ret['lostfiles'] = $lostfiles = array();
        foreach ($server_md5s as $k => $v) {
            if (!in_array($k, array_keys($this->md5_arr))) {
                $lostfiles[] = $k;
                unset($diffs[$k]);
            }
        }
        $files_to_update = [];
        foreach ($diffs as $k => $diff) {
            $files_to_update[] = base64_decode($k);
        }
        foreach ($lostfiles as $k => $lostfile) {
            $files_to_update[] = base64_decode($lostfile);
        }
        return $files_to_update;
    }

    /**
     * 读取本地文件列表
     * @param $module
     */
    private function gen_module_file_list($module)
    {
        //更新核心
        if ($module == "system") {
            $sys_include_dirs = [];
            foreach (self::$system_modules as $_module) {
                $sys_include_dirs[] = 'application' . DIRECTORY_SEPARATOR . $_module . DIRECTORY_SEPARATOR;
            }
            $include_dirs = [
                //核心程序文件
                //'application' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR,
                //'application' . DIRECTORY_SEPARATOR . 'attachment' . DIRECTORY_SEPARATOR,
                //'application' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR,
                //'application' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR,
                //'application' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR,
                //静态文件
                'statics' . DIRECTORY_SEPARATOR,
                //public
                'tpl' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
            ];
            $include_dirs = array_merge($sys_include_dirs, $include_dirs);
        } else {
            $include_dirs = [
                //核心程序文件
                'application' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR,
                //templates
                'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "mobile" . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR,
                'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "desktop" . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR,
            ];
        }
        $this->read_dir(SYS_PATH, $include_dirs);
    }

    /**
     * @param string $path
     */
    private function read_dir($path = '', $include_dirs)
    {
        $path = str_replace("//", "/", $path);
        $found = 0;
        // if the dir is in $include_dirs
        foreach ($include_dirs as $include_dir) {
            //查询到字符串
            if (strpos($path, SYS_PATH . $include_dir) !== false) {
                $found = 1;
                break;
            }
        }
        if (is_dir($path)) {
            if (strpos($path, DIRECTORY_SEPARATOR . "upload_file") || strpos($path, DIRECTORY_SEPARATOR . "vendor") || strpos($path, DIRECTORY_SEPARATOR . "config")) {
                return;
            }
            $handler = opendir($path);
            while (($filename = @readdir($handler)) !== false) {
                if (substr($filename, 0, 1) != ".") {
                    $target_dir = $path . DIRECTORY_SEPARATOR . $filename;
                    self::read_dir($target_dir, $include_dirs);
                }
            }
            closedir($handler);
        } else {
            if ($found) {
                $md5 = md5_file($path);
                $path = str_replace(SYS_PATH, "", $path);
                $path = str_replace("\\", "/", $path);
                $this->md5_arr[base64_encode($path)] = $md5;
            }
        }
    }

    public function get_module_files($module = 'system')
    {
        global $_GPC;
        Config::load(CONF_PATH . 'licence.php');
        $url = $this->_upgrade_md5;
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $res = ihttp_post($url, $licence);
        return json_decode($res['content'], 1);
    }

    /**
     * 本地文件远程文件对比
     */
    public function check_file($module)
    {
        // update local file md5 list
        $this->gen_module_file_list('system');
        if ($module == 'system') {
            $allow_dirs = [
            ];
        }
        $this->md5_arr = array();
        $this->read_dir(SYS_PATH);
        $md5_arr = json_decode($md5s, 1);
        //计算数组差集
        $ret['diff'] = $diff = array_diff($md5_arr, $this->md5_arr);
        //丢失文件列表
        $ret['lostfile'] = $lostfile = array();
        foreach ($md5_arr as $k => $v) {
            if (!in_array($k, array_keys($this->md5_arr))) {
                $lostfile[] = $k;
                unset($diff[$k]);
            }
        }
        //未知文件列表
        $ret['unknowfile'] = $unknowfile = array_diff(array_keys($this->md5_arr), array_keys($md5_arr));
        return $ret;
    }

    function deletedir($dirname)
    {
        $result = false;
        if (!is_dir($dirname)) {
            echo " $dirname is not a dir!";
            exit(0);
        }
        $handle = opendir($dirname); //打开目录
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') { //排除"."和"."
                $dir = $dirname . DIRECTORY_SEPARATOR . $file;
                //$dir是目录时递归调用deletedir,是文件则直接删除
                is_dir($dir) ? $this->deletedir($dir) : unlink($dir);
            }
        }
        closedir($handle);
        $result = rmdir($dirname) ? true : false;
        return $result;
    }
}