<?php

namespace app\update\util;

use think\Cache;
use think\Config;
use think\Db;
use think\Exception;

class Upgrade
{
    //file
    private $_upgrade_md5 = 'http://cloud.bao8.org/product/service/list_files';
    public static $system_modules = ['core', 'admin', 'attachment', 'common', 'system', 'sms', 'update', 'member', 'advertise' ,'order' , 'pay', 'wechat', 'wechat_follow' , 'smallapp'  , 'home' , 'sso' , 'orders'];

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

    /**
     * 写入文件
     * @param $path
     * @param $data
     * @return bool|int
     */
    public static function write_file($path, $data)
    {
        @mkdirs(dirname($path));
        try {
            file_put_contents($path, $data);
        } catch (Exception $e) {
            return false;
        }
        return file_put_contents($path, $data);
    }

    /**
     * 获得模块的模型
     * @param $module
     * @return mixed
     */
    public static function get_models($module)
    {
        Config::load(CONF_PATH . 'licence.php');
        $url = API_URL . 'product/service/get_models';
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $res = ihttp_request($url, $licence);
        $rest = json_decode($res['content'], true);
        if (!is_array($rest)) {
            echo('对不起，鸣鹤CMS升级服务系统错误，请联系我们！！');
            die();
        }
        return $rest;
    }

    /**
     * 版本检测 并设定目标版本
     * @param $module
     * @return bool
     */
    public static function check_version($module)
    {
        $last_check = Cache::get($module . '_last_check');
        $module_version = get_module_version($module);
        Config::load(CONF_PATH . 'licence.php');
        $url = API_URL . 'product/service/check_version';
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $res = ihttp_request($url, $licence);
        Cache::set($module . '_last_check', time());
        $rest = json_decode($res['content'], true);
        if ($rest['code'] == 1) {
            if ($rest['data']['version'] > $module_version['module_version']) {
                Cache::set($module . '_target_version', $rest['data']['version']);
                $ret['code'] = 1;$ret['data'] = $rest;
                return $ret;
            }
        }
        $ret['code'] = 0;
        $ret['data'] = $rest;
        return $ret;
    }

    /**
     * 更新模型
     * @param $table
     * @param string $module
     */
    public static function update_model($table, $module = 'system')
    {
        Config::load(CONF_PATH . 'licence.php');
        $url = API_URL . 'product/service/get_table_fields';
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $licence['table_name'] = $table;
        $res = ihttp_request($url, $licence);
        $res = json_decode($res['content'], true);

        if ($res['code'] == 1) {
            //$res['code'] = self::sql_execute($res['data']);
            /**
             * 创建数据表
             */
            if (!self::tableExists($table)) {
                $res['code'] = self::sql_execute(str_replace("`mhcms_", "`" . config("database.prefix"), $res['data']['struct_sql']));
            }
            /**
             * 补足字段
             */
            if ($res['code'] && self::tableExists($table)) {
                $current_fields = Db::name($table)->getFieldsType();
                $add_sql = "";
                foreach ($res['data']['fields'] as $field_name => $field_type) {
                    if (!isset($current_fields[$field_name])) {
                        //创建添加列SQL
                        $add_sql .= "ALTER TABLE `" . Db::name($table)->getTable() . "` ADD COLUMN `$field_name` $field_type  NOT NULL;\n";
                    }
                }
                if ($add_sql) {
                    $res['code'] = self::sql_execute($add_sql);
                }
                /**
                 * 数据替换
                 */
                if (isset($res['data']['data_sql'])) {
                    $res['code'] = self::sql_execute(str_replace("`mhcms_", "`" . config("database.prefix"), $res['data']['data_sql']));
                }
            } else {
                $res['code'] = 0;
            }
            if ($res['code']) {
                $res['code'] = 1;
                $res['msg'] = "$table 表更新成功";
            } else {
                $res['code'] = 0;
                $res['msg'] = "$table 更新失败，表不存在";
            }
            echo json_encode($res);
        } else {
            $res['code'] = 0;
            $res['msg'] = $res['msg'] . "$table 该表已经过时跳过";
            echo json_encode($res);
        }
    }

    /**
     * 表是否存在
     * @param $table
     * @return bool
     */
    public static function tableExists($table)
    {
        try {
            //echo $table;
            $res = Db::query("SELECT 1 FROM " . config("database.prefix") . $table . "  limit 1");
            return true;
        } catch (\Exception $e) {
            return false;
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
        $this->gen_module_file_list($module); // 本地md5文件生成
        $server_md5s = $this->get_module_files($module); // 获取服务端文件列表
        //$server_md5s = json_decode($__server_md5s, 1);
        if (!is_array($server_md5s)) {
            return [];
        }

        if (!is_array($this->md5_arr)) {
            $this->md5_arr = [];
        }
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
        $themes = get_sub_dir_names(SYS_PATH . 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR);
        //更新核心
        if ($module == "system") {
            $sys_include_dirs = [];
            foreach (self::$system_modules as $_module) {
                $sys_include_dirs[] = 'application' . DIRECTORY_SEPARATOR . $_module . DIRECTORY_SEPARATOR;
                //todo calculate tpls
                foreach($themes as $theme){
                    $sys_include_dirs[] = 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . "$theme" . DIRECTORY_SEPARATOR . "mobile" . DIRECTORY_SEPARATOR . $_module . DIRECTORY_SEPARATOR;

                    $sys_include_dirs[] = 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . "$theme" . DIRECTORY_SEPARATOR . "desktop" . DIRECTORY_SEPARATOR . $_module . DIRECTORY_SEPARATOR;

                }

            }
            $include_dirs = [
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
            ];

            //模板
            foreach($themes as $theme){
                $include_dirs[] = 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . "$theme" . DIRECTORY_SEPARATOR . "mobile" . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;

                $include_dirs[] = 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . "$theme" . DIRECTORY_SEPARATOR . "desktop" . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;

            }
        }

        $this->read_dir(SYS_PATH, $include_dirs);
    }

    /**
     * 读取目录
     * @param string $path
     */
    private function read_dir($path = '', $include_dirs)
    {
        $path = str_replace("//", "/", $path);
        $path = str_replace("\\\\", "\\", $path);

        $found = 0;
        //todo if the dir is in $include_dirs

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

    /**
     * 获取远程模块文件
     * @param string $module
     * @return mixed
     */
    public function get_module_files($module = 'system')
    {
        global $_GPC;
        Config::load(CONF_PATH . 'licence.php');
        $url = $this->_upgrade_md5;
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $licence['module'] = $module;
        $res = ihttp_post($url, $licence);
        $file_md5s = json_decode($res['content'], 1);

        if (!is_array($file_md5s)) {
            test("Error getting Files:" . $res['content']);
        }
        return $file_md5s;
    }

    /**
     * 删除目录
     * @param $dir_name
     * @return bool
     */
    function delete_dir($dir_name)
    {
        $result = false;
        if (!is_dir($dir_name)) {
            echo " $dir_name is not a dir!";
            exit(0);
        }

        $handle = opendir($dir_name); //打开目录
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') { //排除"."和"."
                $dir = $dir_name . DIRECTORY_SEPARATOR . $file;
                //$dir是目录时递归调用deletedir,是文件则直接删除
                is_dir($dir) ? $this->delete_dir($dir) : unlink($dir);
            }
        }
        closedir($handle);
        $result = rmdir($dir_name) ? true : false;
        return $result;
    }
}