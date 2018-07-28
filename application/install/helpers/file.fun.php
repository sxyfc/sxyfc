<?php


if (!function_exists('writable_check')) {
    function writable_check($path)
    {
        $dir = '';
        $is_writable = '1';
        if (!is_dir($path)) {
            return '0';
        }
        $dir = opendir($path);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_file($path . '/' . $file)) {
                    //是文件判断是否可写，不可写直接返回0，不向下继续
                    if (!is_writable($path . '/' . $file)) {
                        return '0';
                    }
                } else {
                    //目录，循环此函数,先判断此目录是否可写，不可写直接返回0 ，可写再判断子目录是否可写
                    $dir_wrt = dir_writeable($path . '/' . $file);
                    if ($dir_wrt == '0') {
                        return '0';
                    }
                    $is_writable = writable_check($path . '/' . $file);
                }
            }
        }
        return $is_writable;
    }
}

if (!function_exists('dir_writeable')) {
    function dir_writeable($dir)
    {
        $writeable = 0;
        if (is_dir($dir)) {
            if ($fp = @fopen("$dir/chkdir.test", 'w')) {
                @fclose($fp);
                @unlink("$dir/chkdir.test");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }
        return $writeable;
    }
}

if (!function_exists('_sql_split')) {
    function _sql_split($link, $sql, $r_tablepre = '', $s_tablepre = 'mhcms_')
    {
        global $dbcharset, $tablepre;
        $r_tablepre = $r_tablepre ? $r_tablepre : $tablepre;
        if (mysqli_get_server_info($link) > '4.1' && $dbcharset) {
            $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . $dbcharset, $sql);
        }

        if ($r_tablepre != $s_tablepre) $sql = str_replace($s_tablepre, $r_tablepre, $sql);
        $sql = str_replace("\r", "\n", $sql);
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
}

if (!function_exists('remote_file_exists')) {
    function remote_file_exists($url_file)
    {
        $headers = get_headers($url_file);
        if (!preg_match("/200/", $headers[0])) {
            return false;
        }
        return true;
    }
}

if (!function_exists('mkdirs')) {
    function mkdirs($path)
    {
        if (!is_dir($path)) {
            mkdirs(dirname($path));
            mkdir($path , 0777 , true);
        }
        return is_dir($path);
    }
}