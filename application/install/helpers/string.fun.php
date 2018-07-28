<?php
/*
 * String Mulp
 */
if (!function_exists('strexists')) {
    function strexists($string, $find)
    {
        return !(strpos($string, $find) === FALSE);
    }
}

if (!function_exists('get_url')) {
    /**
     * 获取当前页面完整URL地址
     */
    function get_url()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? safe_replace($_SERVER['PHP_SELF']) : safe_replace($_SERVER['SCRIPT_NAME']);
        $path_info = isset($_SERVER['PATH_INFO']) ? safe_replace($_SERVER['PATH_INFO']) : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? safe_replace($_SERVER['REQUEST_URI']) : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . safe_replace($_SERVER['QUERY_STRING']) : $path_info);
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }
}


/**
 * @param $pass string
 * @param $encrypt string
 * @return password
 */
if (!function_exists('crypt_pass')) {
    function crypt_pass($pass, $encrypt, $level = 1)
    {
        for ($i = 1; $i <= $level; $i++) {
            $pass = md5(trim($pass));
        }
        return md5($pass . $encrypt);
    }
}


/**
 * @param   $length int
 * @param   $chars string        default = 123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ
 * @return  string
 */
if (!function_exists('random')) {
    function random($length = 6, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
}