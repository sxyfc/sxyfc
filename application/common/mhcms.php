<?php
// +----------------------------------------------------------------------
// | 鸣鹤CMS [ New Better  ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://www.mhcms.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( 您必须获取授权才能进行使用 )
// +----------------------------------------------------------------------
// | Author: new better <1620298436@qq.com>
// +----------------------------------------------------------------------
// 应用公共文件
use app\common\model\Models;
use app\common\model\Sites;
use app\common\model\UserMenuAccess;
use app\common\model\UserMenuAllot;
use app\common\model\Users;
use app\common\util\PHPMailer\PHPMailer;
use app\common\util\QRcode;
use think\Cache;
use think\Config;
use think\Db;
use think\Request;
use think\Session;

if (!function_exists('get_module_version')) {
    function get_module_version($module)
    {
        $ret['last_update'] = Cache::get($module . "_last_update");
        $ret['module_version'] = Cache::get($module . "_version");
        return $ret;
    }
}
/**
 * @param $data
 * @return mixed
 */
function mhcms_json_decode($data)
{
    return json_decode($data, 1);
}

/**
 * @param $model_id
 * @param bool $init_factory
 * @return  \think\db\Query | Models
 * @throws \think\Exception
 * @throws \think\exception\DbException
 */
function set_model($model_id, $init_factory = true)
{
    global $_W;
    static $models = [], $form_factory;
    if (!isset($models[$model_id])) {
        if (is_numeric($model_id)) {
            $model_info = Models::get(['id' => $model_id]);
            $table_name = $model_info['table_name'];
        } else {
            $model_info = Models::get(['table_name' => $model_id]);
            if (!$model_info) {
                $table_name = $model_id;
            } else {
                $table_name = $model_info['table_name'];
            }
        }
        if ($model_info && $init_factory) {
            if (isset($_W['site']) && !$form_factory) {
                $model_info->form_factory = new \app\common\util\forms\FormFactory($_W['site']['id']);
            } else {
                $model_info->form_factory = $form_factory;
            }
        }

        $models[$model_id] = Db::name($table_name);
        $models[$model_id]->model_info = $model_info;
    }
    return $models[$model_id];
}


function check_token($value, $name = '__token__')
{
    if ($value == Session::get($name)) {
        return true;
    } else {
        return false;
    }
}

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

/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function safe_replace($string)
{
    $string = str_replace('%20', '', $string);
    $string = str_replace('%27', '', $string);
    $string = str_replace('%2527', '', $string);
    $string = str_replace('*', '', $string);
    $string = str_replace('"', '&quot;', $string);
    $string = str_replace("'", '', $string);
    $string = str_replace('"', '', $string);
    $string = str_replace(';', '', $string);
    $string = str_replace("{", '', $string);
    $string = str_replace('}', '', $string);
    $string = str_replace('\\', '', $string);
    return $string;
}

if (!function_exists('is_error')) {
    function is_error($data)
    {
        if (empty($data) || !is_array($data) || !array_key_exists('errno', $data) || (array_key_exists('errno', $data) && $data['errno'] == 0)) {
            return false;
        } else {
            return true;
        }
    }
}
function get_linkage($linkageid, $keyid, $space = '>', $type = 1, $result = array(), $infos = array())
{
    if ($space == '' || !isset($space)) $space = '>';
    if (!$infos) {
        $datas = Cache::get('linkage/' . $keyid);
        $infos = $datas['data'];
    }
    if (!$infos) {
        $infos = [];
    }
    if ($type == 1 || $type == 3 || $type == 4) {
        if (array_key_exists($linkageid, $infos)) {
            $result[] = ($type == 1) ? $infos[$linkageid]['name'] : (($type == 4) ? $linkageid : $infos[$linkageid]);
            return get_linkage($infos[$linkageid]['parentid'], $keyid, $space, $type, $result, $infos);
        } else {
            if (count($result) > 0) {
                krsort($result);
                if ($type == 1 || $type == 4) $result = implode($space, $result);
                return $result;
            } else {
                return $result;
            }
        }
    } else {
        return $infos[$linkageid]['name'];
    }
}

if (!defined("IN_WE7")) {
    function strexists($string, $find)
    {
        return !(strpos($string, $find) === FALSE);
    }

    function ver_compare($version1, $version2)
    {
        $version1 = str_replace('.', '', $version1);
        $version2 = str_replace('.', '', $version2);
        $oldLength = istrlen($version1);
        $newLength = istrlen($version2);
        if (is_numeric($version1) && is_numeric($version2)) {
            if ($oldLength > $newLength) {
                $version2 .= str_repeat('0', $oldLength - $newLength);
            }
            if ($newLength > $oldLength) {
                $version1 .= str_repeat('0', $newLength - $oldLength);
            }
            $version1 = intval($version1);
            $version2 = intval($version2);
        }
        return version_compare($version1, $version2);
    }

    function istrlen($string, $charset = '')
    {
        global $_W;
        if (empty($charset)) {
            $charset = isset($_W['charset']) ? $_W['charset'] : "";
        }
        if (strtolower($charset) == 'gbk') {
            $charset = 'gbk';
        } else {
            $charset = 'utf8';
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, $charset);
        } else {
            $n = $noc = 0;
            $strlen = strlen($string);
            if ($charset == 'utf8') {
                while ($n < $strlen) {
                    $t = ord($string[$n]);
                    if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                        $n++;
                        $noc++;
                    } elseif (194 <= $t && $t <= 223) {
                        $n += 2;
                        $noc++;
                    } elseif (224 <= $t && $t <= 239) {
                        $n += 3;
                        $noc++;
                    } elseif (240 <= $t && $t <= 247) {
                        $n += 4;
                        $noc++;
                    } elseif (248 <= $t && $t <= 251) {
                        $n += 5;
                        $noc++;
                    } elseif ($t == 252 || $t == 253) {
                        $n += 6;
                        $noc++;
                    } else {
                        $n++;
                    }
                }
            } else {
                while ($n < $strlen) {
                    $t = ord($string[$n]);
                    if ($t > 127) {
                        $n += 2;
                        $noc++;
                    } else {
                        $n++;
                        $noc++;
                    }
                }
            }
            return $noc;
        }
    }
}
/**
 * @param $querys
 * @param string $domain
 * @param array $options
 * @return string
 * @throws \think\exception\DbException
 */
function nb_url($querys, $domain = "", $options = [])
{
    global $_W;
    static $sites;
    if (empty($domain)) {
        $domain = $_W['site']['id'];
    }
    //TODO 数字 解析独立域名绑定
    if (isset($_W['sites_domain']) && $_W['sites_domain']) {
        $domain = $_W['sites_domain']['domain'];
    } else {
        if (!isset($sites[$domain])) {
            if (is_numeric($domain)) {
                $sites[$domain] = Sites::get(['id' => $domain]);
            } else {
                $sites[$domain] = Sites::get(['site_domain' => $domain]);
            }
        }
        if (config('app_debug')) {
            $domain = $sites[$domain]['site_d_domain'];
//            $domain = empty($sites[$domain]['site_domain']) ? 'www' : $sites[$domain]['site_domain'];
        } else {
            $domain = $sites[$domain]['site_domain'];
        }

    }

    if ($_W['global_config']['groups_mode'] == 2) {
        $domain = "";
    }
    $url = new_better_furl($querys, "", $domain, $options);
    return $url;
}

/**URL FOR WEB
 * @param array $querys
 * @param string $module_name
 * @param string $do
 * @return string
 * @internal param array $query
 */
function new_better_url($querys = array(), $module_name = "", $do = "index")
{
    if (!is_array($querys)) {
        $querys = explode("?", $querys);
        $route = $querys[0];
    } else {
        $route = str_replace(".", "/", $querys['r']);
        unset($querys['r']);
    }
    return url($route, $querys[1]);
}

/**URL FOR FRONT
 * @param array $query
 * @param string $do
 * @param bool $noredirect
 * @return string
 */
function new_better_furl($querys = array(), $module_name = "", $domain = "", $options = [], $do = "index", $noredirect = true)
{
    global $_W;
    if (is_array($querys)) {
        $_append = [];
        foreach ($querys as $k => $query) {
            if ($query && strpos($query, "=") !== false) {
                $params = explode("&", $query);
                foreach ($params as $param) {
                    if ($param) {
                        $_final_param = explode("=", $param);
                        if (count($_final_param) == 2) {
                            $_append[$_final_param[0]] = $_final_param[1];
                        }
                    }
                }
            } else {
                if ($k && $query) {
                    $_append[$k] = $query;
                }
            }
            unset($querys[$k]);
        }
        $querys = $_append;
    }

    if (!is_array($querys)) {
        return $url = url($querys, $options, true, $domain);
    } else {

        $route = str_replace(".", "/", $querys['r']);
        unset($querys['r']);
        $querys = array_merge($querys, $options);
        $url = url($route, $querys, true, $domain);
        return $url;
    }
}

function getWeek($num)
{
    $s = '';
    switch ($num) {
        case 0:
            $s = '天';
            break;
        case 1:
            $s = '一';
            break;
        case 2:
            $s = '二';
            break;
        case 3:
            $s = '三';
            break;
        case 4:
            $s = '四';
            break;
        case 5:
            $s = '五';
            break;
        case 6:
            $s = '六';
            break;
    }
    return $s;
}

if (!function_exists('array2xml')) {
    function array2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach ($arr as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if (!is_array($value)) {
                $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
            } else {
                $s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</xml>" : $s;
    }
}
if (!function_exists('isimplexml_load_string')) {
    function isimplexml_load_string($string, $class_name = 'SimpleXMLElement', $options = 0, $ns = '', $is_prefix = false)
    {
        libxml_disable_entity_loader(true);
        if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $string)) {
            return false;
        }
        return simplexml_load_string($string, $class_name, $options, $ns, $is_prefix);
    }
}
if (!function_exists('xml2array')) {
    function xml2array($xml)
    {
        if (empty($xml)) {
            return array();
        }
        $result = array();
        $xmlobj = isimplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xmlobj instanceof SimpleXMLElement) {
            $result = json_decode(json_encode($xmlobj), true);
            if (is_array($result)) {
                return $result;
            } else {
                return '';
            }
        } else {
            return $result;
        }
    }
}
if (!function_exists('iarray_change_key_case')) {
    function iarray_change_key_case($array, $case = CASE_LOWER)
    {
        if (!is_array($array) || empty($array)) {
            return array();
        }
        $array = array_change_key_case($array, $case);
        foreach ($array as $key => $value) {
            if (empty($value) && is_array($value)) {
                $array[$key] = '';
            }
            if (!empty($value) && is_array($value)) {
                $array[$key] = iarray_change_key_case($value, $case);
            }
        }
        return $array;
    }
}
/**
 * str To Qrcode
 * @param $str
 */
function str_to_qrcode($str)
{
    $errorCorrectionLevel = "L";
    $matrixPointSize = "5";
    return QRcode::png($str, false, $errorCorrectionLevel, $matrixPointSize);
}

function is_weixin()
{
    return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger');
}

function is_weixin_mini()
{
    $isweix = false;
    test($_SERVER['HTTP_USER_AGENT']);
    if (is_weixin() && strstr($_SERVER['HTTP_USER_AGENT'], 'mini')) {
        // $payment_where['code'] = 'miniAppPay';
        $isweix = true;
    }
    return $isweix;
}


function is_mobile()
{
    $useragent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/(android|bb\\d+|meego).+mobile|avantgo|bada\\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\\-(n|u)|c55\\/|capi|ccwa|cdm\\-|cell|chtm|cldc|cmd\\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\\-s|devi|dica|dmob|do(c|p)o|ds(12|\\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\\-|_)|g1 u|g560|gene|gf\\-5|g\\-mo|go(\\.w|od)|gr(ad|un)|haie|hcit|hd\\-(m|p|t)|hei\\-|hi(pt|ta)|hp( i|ip)|hs\\-c|ht(c(\\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\\-(20|go|ma)|i230|iac( |\\-|\\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\\/)|klon|kpt |kwc\\-|kyo(c|k)|le(no|xi)|lg( g|\\/(k|l|u)|50|54|\\-[a-w])|libw|lynx|m1\\-w|m3ga|m50\\/|ma(te|ui|xo)|mc(01|21|ca)|m\\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\\-2|po(ck|rt|se)|prox|psio|pt\\-g|qa\\-a|qc(07|12|21|32|60|\\-[2-7]|i\\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\\-|oo|p\\-)|sdk\\/|se(c(\\-|0|1)|47|mc|nd|ri)|sgh\\-|shar|sie(\\-|m)|sk\\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\\-|v\\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\\-|tdg\\-|tel(i|m)|tim\\-|t\\-mo|to(pl|sh)|ts(70|m\\-|m3|m5)|tx\\-9|up(\\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\\-|your|zeto|zte\\-/i', substr($useragent, 0, 4))) {
        return true;
    }
    return false;
}

/**
 * Show LInkage Option
 */
function show_linkage($linkage_id, $all = false)
{
    $linkage = \app\common\model\Linkage::get(['linkageid' => $linkage_id]);
    if ($all) {
        return $linkage;
    }
    return $linkage['name'];
}

/**
 * create trade sn
 */
function create_sn()
{
    mt_srand((double )microtime() * 1000000);
    return date("YmdHis") . str_pad(mt_rand(1, 99999), 5, "0", STR_PAD_LEFT);
}

/**
 * check if the user have access to a node type
 * @param $node_type_id
 * @param Users $user
 * @return bool
 */
function check_node_type($node_type_id, Users $user, $method = "allow_pub_user_roles")
{
    //todo fix to model allow_pub_user_roles
    $found = false;
    $node_type = \app\common\model\NodeTypes::get($node_type_id);
    if (is_array($node_type[$method])) {
        foreach ($node_type[$method] as $role_id) {
            if ($role_id == $user['user_role_id']) {
                $found = true;
                break;
            }
        }
    }
    return $found;
}

function test($param)
{
    var_dump($param);
    exit;
}

/**
 * @param Users $user
 * @return string
 */
function crypt_auth_str(Users $user)
{
    $request = \think\Request::instance();
    return crypt_auth($user['user_name'] . "\t" . $request->ip() . "\t" . $request->type() . "\t" . $request->header('user-agent') . "\t" . $user['id'], 'ENCODE', $user['user_crypt']);
}

/**
 * 用户登录安全全靠它
 * @param int $secure_level 最高安全级别为3 依次 2 , 1 ,
 * @return int
 * @internal param int $secure
 */
function check_user($secure_level = 3)
{
    $auth = isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] ? $_SERVER['HTTP_AUTHORIZATION'] : input('param.Authorization');

    if ($auth) {
        $user = _check_authorization($auth, $secure_level);
    }
    if (!isset($user) || empty($user)) {
        $user = _check_traditional($secure_level);
    }
    return $user;
}

function _check_authorization($auth, $secure_level)
{
    $request = \think\Request::instance();
    $info = explode(";", $auth);
    $auth_str = $info[0];
    $user_id = $info[2];
    $user = Users::get($user_id);

    if (!$user_id || !$user) {
        return false;
    }

    $auth_info = crypt_auth($auth_str, 'DECODE', $user['user_crypt']);

    $auth_info = explode('	', $auth_info);
    $c_user_id = array_pop($auth_info);
    if ($c_user_id == $user_id) {
        return $user;
    }
    return false;
}

function _check_traditional($secure_level)
{
    $request = \think\Request::instance();
    $user_id = (int)\think\Cookie::get('user_id');

    if ($user_id) {
        $user = Users::get(['id' => $user_id]);
    }
    if (!isset($user)) {
        return false;
    }
    if ($secure_level == 3) {
        $auth_info = \think\Cookie::get('auth_info');
        $auth_info = crypt_auth($auth_info, 'DECODE', $user['user_crypt']);
        $auth_info = explode('	', $auth_info);
        $c_user_id = array_pop($auth_info);
        if ($c_user_id == \think\Session::get('user_id') && $c_user_id == $user_id && $request->header('user-agent') == $auth_info[3]) {
            return $user;
        } else {
            return false;
        }
    } elseif ($secure_level == 2) {
        $s_user_id = \think\Session::get('user_id');
        if ($s_user_id == $user_id) {
            return $user_id;
        }
    } elseif ($secure_level == 1) {
        return $user;
    }
    return false;
}

/**
 * 用户登录安全全靠它
 * @return \app\common\model\Users|boolean
 * @throws \think\exception\DbException
 * @internal param int $secure
 */
function check_admin()
{
    $request = \think\Request::instance();
    $user_id = (int)\think\Session::get('admin_id');
    $user = Users::get(['id' => $user_id]);
    if (!$user) {
        return false;
    }
    $auth_info = \think\Session::get('auth_admin_info');
    $auth_info = crypt_auth($auth_info, 'DECODE', $user['user_crypt']);
    $auth_info = explode('	', $auth_info);
    $c_user_id = array_pop($auth_info);
    if ($c_user_id == \think\Session::get('admin_id') && $c_user_id == $user_id && $request->header('user-agent') == $auth_info[3]) {
        return $user;
    } else {
        return false;
    }
}

/**
 * @param $password
 * @return bool
 */
function is_password($password)
{
    $strlen = strlen($password);
    if ($strlen >= 6 && $strlen <= 20) return true;
    return false;
}

/**clean a array for security
 * @param $data
 * @param $keys
 * @return array
 */
function clean_data($data, $keys)
{
    $cleaned = [];
    foreach ($keys as $v) {
        if (isset($data[$v])) {
            $cleaned[$v] = $data[$v];
        } else {
            //    $cleaned[$v] = '';
        }
    }
    return $cleaned;
}

/**
 * 保存数组变量到php文件
 * @param $path
 * @param $value
 * @return int
 */
function array_to_file($path, $value)
{
    $ret = file_put_contents($path, "<?php\t return " . var_export($value, true) . ";?>");
    return $ret;
}

//\think\Route::rule('装修公司/', 'home/node_types/index');
function route_to_file($path, $routes)
{
    $str = "<?php \t";
    foreach ($routes as $route) {
        $params = "";
        if ($route['params']) {
            $params = "?{$route['params']}";
        }
        $str .= "\\think\\Route::{$route['route_type']}('{$route['url_rule']}','{$route['bind_module']}$params'); \t";
    }
    $ret = file_put_contents($path, $str);
    return $ret;
}

function zlang($name, $vars = [], $lang = 'zh-cn')
{
    $lang_ret = "";
    $names = explode(",", $name);
    foreach ($names as $name) {
        $lang_ret .= lang($name, [], $lang = 'zh-cn') . " ";
    }
    return $lang_ret;
}

function zbn_msg($message, $icon = "-1", $time = 1000, $jumpUrl = "", $javascript)
{
    $str = '<script>';
    $str .= 'parent.show_message("' . $message . '",' . $icon . "," . $time . ',\'goToUrl("' . $jumpUrl . ',' . $javascript . '")\');';
    $str .= '</script>';
    die($str);
}

/**/
/**
 * @param $admin_menu_id
 * @param string $divider
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function get_admin_menu_path($admin_menu_id, $divider = "/")
{
    static $path_str = '';
    if ($admin_menu_id) {
        $where['id'] = $admin_menu_id;
        $sub_menu = Db::name('user_menu')->where($where)->find();
        $path_str = "  <li><a> " . zlang($sub_menu['user_menu_name']) . " </a>  </li>" . $path_str;
        if ($sub_menu['user_menu_parentid'] != 0) {
            get_admin_menu_path($sub_menu['user_menu_parentid']);
        }
        return $path_str;
    }
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string)
{
    if (!is_array($string)) return addslashes($string);
    foreach ($string as $key => $val) $string[$key] = new_addslashes($val);
    return $string;
}

/**
 * 后台模版带权限校验的Layer按钮
 * @param string $admin_menu_id 菜单ID'
 * @param string $title 标题
 * @param string $trigger 事件
 * @param string $function 函数
 * @param string $class A标签样式
 * @return string
 */
function build_layer_link($admin_menu_id, $title = "", $trigger = "onclick", $function = "", $class = "", $width = "", $height = "")
{
    $admin_id = session('admin_id');
    $admin_role_id = \think\Session::get('admin_role_id');
    $menu = \app\common\model\AdminMenu::get($admin_menu_id);
    if ($admin_id != 1) {
        $where['admin_role_id'] = $admin_role_id;
        $where['admin_menu_id'] = $admin_menu_id;
        if (!\app\common\model\AdminMenuAccess::get($where)) {
            return "";
        }
    }
    $title = $title ? $title : $menu['admin_menu_name'];
    $line_title = $title;
    $title = " title = '" . $title . "'";
    $function = " " . $trigger . "='" . $function . "'";
    if (!empty($width)) {
        $w = ' w="' . $width . ' " ';
    }
    if (!empty($width)) {
        $h = ' h="' . $height . ' " ';
    }
    return '<a href="javascript:void(0)" ' . $title . $function . $w . $h . ' >' . $line_title . '</a>';
    return $menu['admin_menu_name'];
}

/**
 * 后台模版带权限的URL校验
 * @param $admin_menu_id
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $title 标题
 * @param string $mini 是否异步加载
 * @param string $class A标签样式
 * @param string $width
 * @param string $height
 * @param array $mapping
 * @return string
 * @throws \think\exception\DbException
 */
function build_back_a($admin_menu_id, $vars, $title = '', $mini = "", $class = "", $width = '', $height = '', $mapping = [])
{
    global $_W;
    static $access, $allot, $menus;
    if (!isset($_W['super_power']) || $_W['super_power'] != 1) {
        // 先获取用户菜单，不存在的话获取角色菜单
        $where_allot['user_id'] = $_W['admin_info']['user_id'];
        $where_allot['user_menu_id'] = $admin_menu_id;
        if (isset($allot[$admin_menu_id]) and $allot[$admin_menu_id] == false) {
            return " - ";
        } else {
            $allot[$admin_menu_id] = UserMenuAllot::get($where_allot);
            if (!$allot[$admin_menu_id]) {
                $where['user_role_id'] = $_W['admin_info']['role_id'];
                $where['user_menu_id'] = $admin_menu_id;
                if (isset($access[$admin_menu_id]) and $access[$admin_menu_id] == false) {
                    return " - ";
                } else {
                    $access[$admin_menu_id] = UserMenuAccess::get($where);
                    if (!$access[$admin_menu_id]) {
                        return " - ";
                    }
                }
            }
        }
    }
    if (is_array($vars)) {
        $new_vars = [];
        $vars['user_menu_id'] = $admin_menu_id;
        foreach ($vars as $k => $v) {
            $new_vars[] = $k . "=" . $v;
        }
        $vars = join("&", $new_vars);
    } else {
        //mapping 暂时之支持字符串
        $vars .= "&user_menu_id=$admin_menu_id";
    }
    //Generate URL
    $vars = parseParam($vars, $mapping);
    if (!is_numeric($admin_menu_id)) {
        $url = $admin_menu_id;
    } else {
        if (isset($menus[$admin_menu_id])) {
            $menu = $menus[$admin_menu_id];
        } else {
            $menus[$admin_menu_id] = $menu = \app\common\model\UserMenu::get($admin_menu_id);
        }
        $url = nb_url(['r' => $menu['user_menu_module'] . '/' . $menu['user_menu_controller'] . '/' . $menu['user_menu_action'], $vars]);
        $title = $title ? $title : $menu['user_menu_name'];
    }
    $class = $class ? $class : $menu['class'];
    //权限判断 暂时忽略，后面补充
    $m = $c = $h = $w = '';
    if (!empty($mini)) {
        $m = ' mini="' . $mini . '"  ';
    }
    if (!empty($class)) {
        $c = ' class="' . $class . ' " ';
    }
    if (!empty($width)) {
        $w = ' width="' . $width . ' " ';
    }
    if (!empty($width)) {
        $h = ' height="' . $height . ' " ';
    }
    return '<a href="' . $url . '" ' . $m . $c . $w . $h . ' >' . zlang($title) . '</a>';
}

/**
 * @param $admin_menu_id
 * @param $vars
 * @param string $title
 * @param string $mini
 * @param string $class
 * @param string $width
 * @param string $height
 * @param array $mapping
 * @return string
 * @throws \think\exception\DbException
 */
function build_back_url($admin_menu_id, $vars, $title = '', $mini = "", $class = "", $width = '', $height = '', $mapping = [])
{
    global $_W;
    if ($_W['super_power'] != 1) {
        $where['user_role_id'] = $_W['admin_info']['role_id'];
        $where['user_menu_id'] = $admin_menu_id;
        if (!UserMenuAccess::get($where)) {
            return " - ";
        }
    }
    if (is_array($vars)) {
        $new_vars = [];
        $vars['user_menu_id'] = $admin_menu_id;
        foreach ($vars as $k => $v) {
            $new_vars[] = $k . "=" . $v;
        }
        $vars = join("&", $new_vars);
    } else {
        //mapping 暂时之支持字符串
        $vars .= "&user_menu_id=$admin_menu_id";
    }
    //Generate URL
    $vars = parseParam($vars, $mapping);
    if (!is_numeric($admin_menu_id)) {
        $url = $admin_menu_id;
    } else {
        $menu = \app\common\model\UserMenu::get($admin_menu_id);
        //$vars['r'] =
        $url = nb_url(['r' => $menu['user_menu_module'] . '/' . $menu['user_menu_controller'] . '/' . $menu['user_menu_action'], $vars]);
    }
    return $url;
}


function build_back_link($admin_menu_id, $vars, $mapping = [])
{
    global $_W;
    if ($_W['super_power'] != 1) {
        $where['user_role_id'] = $_W['admin_info']['role_id'];
        $where['user_menu_id'] = $admin_menu_id;
        if (!UserMenuAccess::get($where)) {
            return " - ";
        }
    }
    if (is_array($vars)) {
        $new_vars = '';
        $vars['user_menu_id'] = $admin_menu_id;
        foreach ($vars as $k => $v) {
            $new_vars[] = $k . "=" . $v;
        }
        $vars = join("&", $new_vars);
    } else {
        //mapping 暂时之支持字符串
        $vars .= "&user_menu_id=$admin_menu_id";
    }
    //Generate URL
    $vars = parseParam($vars, $mapping);
    if (!is_numeric($admin_menu_id)) {
        $url = $admin_menu_id;
    } else {
        $menu = \app\common\model\UserMenu::get($admin_menu_id);
        //$vars['r'] =
        $url = nb_url(['r' => $menu['user_menu_module'] . '/' . $menu['user_menu_controller'] . '/' . $menu['user_menu_action'], $vars]);
    }
    return $url;
}

/**
 * 会员模版带权限的URL校验
 * @param $menu_id
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $title 标题
 * @param string $mini 是否异步加载
 * @param string $class A标签样式
 * @param string $width
 * @param string $height
 * @return string
 * @internal param string $url URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @throws \think\exception\DbException
 */
function build_front_a($menu_id, $vars, $title = '', $mini = "", $class = "layui-btn layui-btn-small ", $width = '', $height = '', $mapping = [])
{
    global $_W;

    $menu = \app\common\model\UserMenu::get($menu_id);
    if (isset($_W['super_power']) && $_W['super_power']) {
        $where['user_role_id'] = $_W['user_role_id'];
        $where['user_menu_id'] = $menu_id;
        if (!UserMenuAccess::get($where)) {
            return " - ";
        }
    }

    if (is_array($vars)) {
        $new_vars = '';
        $vars['user_menu_id'] = $menu_id;
        foreach ($vars as $k => $v) {
            $new_vars[] = $k . "=" . $v;
        }
        $vars = join("&", $new_vars);
    } else {
        //mapping 暂时之支持字符串
        $vars .= "&user_menu_id=$menu_id";
    }
    //todo parse params
    $vars = parseParam($vars, $mapping);

    //Generate URL
    $url = nb_url(['r' => $menu['user_menu_module'] . '/' . $menu['user_menu_controller'] . '/' . $menu['user_menu_action'], $vars]);

    //权限判断 暂时忽略，后面补充
    $m = $c = $h = $w = '';
    if (!empty($mini)) {
        $m = ' mini="' . $mini . '"  ';
    }
    if (!empty($class)) {
        $c = ' class="' . $class . ' " ';
    }
    if (!empty($width)) {
        $w = ' width="' . $width . ' " ';
    }
    if (!empty($width)) {
        $h = ' height="' . $height . ' " ';
    }
    return '<a href="javascript:void(0)" data-href="' . $url . '" ' . $m . $c . $w . $h . ' >' . zlang($title) . '</a>';
}

//获取IP返回地址的函数
function IpToArea($_ip)
{
    static $IpLocation;
    if (empty($IpLocation)) {
        $IpLocation = new \app\common\util\IpLocation(CONF_PATH . 'UTFWry.dat'); // 实例化类 参数表示IP地址库文件
    }
    $arr = $IpLocation->getlocation($_ip);
    return $arr['country'] . $arr['area'];
}

/**
 * @param $data
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @param string $method
 * @param string $algo
 * @return bool|string
 */
function crypt_auth($data, $operation = 'ENCODE', $key = '', $expiry = 0, $method = "aes-256-cbc", $algo = "sha256")
{
    global $_W;
    $default_crypt = config('mhcms_crypt');
    $key_1 = base64_encode($key ? $key : $default_crypt);
    $key_2 = base64_encode($key ? $key : $default_crypt);
    $first_key = base64_decode($key_1);;
    $second_key = base64_decode($key_2);
    $iv_length = openssl_cipher_iv_length($method);

    $expiry = sprintf('%010d', $expiry ? $expiry + time() : 0);

    if ($operation == 'ENCODE') {
        $iv = openssl_random_pseudo_bytes($iv_length);
        $first_encrypted = openssl_encrypt($expiry . $data, $method, $first_key, OPENSSL_RAW_DATA, $iv);
        $second_encrypted = hash_hmac($algo, $first_encrypted, $second_key, TRUE);
        $output = base64_encode($iv . $second_encrypted . $first_encrypted);
        return $output;
    } else {
        //解压数据
        $mix = base64_decode($data);
        $iv = substr($mix, 0, $iv_length);
        /**
         * 取出加密过后的字符
         */
        $second_encrypted = substr($mix, $iv_length, 32);
        $first_encrypted = substr($mix, $iv_length + 32);
        //
        $data = openssl_decrypt($first_encrypted, $method, $first_key, OPENSSL_RAW_DATA, $iv);
        $second_encrypted_new = hash_hmac($algo, $first_encrypted, $second_key, TRUE);
        if (hash_equals($second_encrypted, $second_encrypted_new)) {
            if ((substr($data, 0, 10) == 0 || substr($data, 0, 10) - time() > 0)) {
                return substr($data, 10);
            } else {
                return "EXPIRED";
            }
        }
        return false;
    }

}


/**
 * @param int $node_type_id
 * @param node_type|string $type node_type , category , node
 * @param int $page
 * @param int $cate_id
 * @param int $node_id
 * @return string
 * @internal param int $node_tpe_id
 */
function cache_path($node_type_id, $type = "node_type", $page = 1, $cate_id = 0, $node_id = 0)
{
    $path = $GLOBALS['root_domain'] . DS . $GLOBALS['site_id'] . DS . $type . "_" . $node_type_id . DS;
    switch ($type) {
        case  "node_type":
            $path .= "list" . DS . $page;
            break;
        case  "category":
            //TODO
            $path .= "cat_list" . DS . $cate_id . "_" . $page;
            break;
        case  "node":
            $path .= "node" . DS . $node_id . "_" . $page;
            break;
        case "index":
            $path .= $GLOBALS['root_domain'] . DS . $GLOBALS['site_id'] . DS . "index";
    }
    return $path;
}

/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 * @return string
 */
function str_cut($string, $length, $dot = '...')
{
    !defined('CHARSET') ? define('CHARSET', 'utf-8') : '';
    $strlen = strlen($string);
    if ($strlen <= $length) return $string;
    $string = str_replace(array(' ', '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵', ' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strcut = '';
    if (strtolower(CHARSET) == 'utf-8') {
        $length = intval($length - strlen($dot) - $length / 3);
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
        $strcut = str_replace(array('∵', '…'), array(' ', '&hellip;'), $strcut);
    } else {
        $dotlen = strlen($dot);
        $maxi = $length - $dotlen - 1;
        $current_str = '';
        $search_arr = array('&', ' ', '"', "'", '“', '”', '—', '<', '>', '·', '…', '∵');
        $replace_arr = array('&amp;', '&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;', ' ');
        $search_flip = array_flip($search_arr);
        for ($i = 0; $i < $maxi; $i++) {
            $current_str = ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            if (in_array($current_str, $search_arr)) {
                $key = $search_flip[$current_str];
                $current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
            }
            $strcut .= $current_str;
        }
    }
    return $strcut . $dot;
}

/**
 * read a config
 * @param $name $the name of the config
 * @param $key |string $key key of the wanted  if empty, the whole config data is returned
 * @return string
 */
function load_config_old($name, $key = "", $site_id = 0, $root_id = 0)
{
    global $_W;
    /** @var $TYPE_NAME $configs */
    static $config_model, $configs;
    if (!isset($configs)) {
        $config_model = new \app\common\model\Configs();
        $configs = $config_model->fetchAll('config_id', false, 'core');
    }
    //全局变量
    $global_config = [];
    //site config
    $site_config = [];
    foreach ($configs as $config) {
        if ($config['site_id'] == $_W['root']['site_id']) {
            $global_config[$config['config_name']] = $config;
        }
        if ($config['site_id'] != $_W['site']['site_id']) {
            $site_config[$config['config_name']] = $config;
        }
    }
    $configs = array_merge($global_config, $site_config);
    if ($configs && $key) {
        return $configs[$name]['config_data'][$key]['value'];
    } else {
        return $configs[$name]['config_data'];
    }
}

/**
 * read a config
 * @param $name $the name of the config
 * @param $key |string $key key of the wanted  if empty, the whole config data is returned
 * @return string
 */
function load_module_config($module_name, $key = "", $site_id = 0, $root_id = 0)
{
    global $_W;
    /** @var $TYPE_NAME $configs */
    $where = [];
    $where['site_id'] = [
        'IN', [$_W['root']['site_id'], $_W['site']['site_id']]
    ];
    $where['module'] = $module_name;
    $configs = \app\common\model\Configs::all($where);
    $site_config = [];
    foreach ($configs as $config) {
        $configs = array_merge($site_config, $config['config_data']);
    }
    if ($configs && $key) {
        return $configs[$key]['value'];
    } else {
        return $configs;
    }
}

/**help function for the compatibility of the old code model
 * @param $model
 * @return mixed
 */
function D($model)
{
    return set_model($model);
}

function U($str, $vars = [])
{
    return \think\Url::build($str, $vars);
}

/**
 * @param $pass string
 * @param $encrypt string
 * @return password
 */
function crypt_pass($pass, $encrypt, $level = 1)
{
    for ($i = 1; $i <= $level; $i++) {
        $pass = md5(trim($pass));
    }
    return md5($pass . $encrypt);
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
/**
 * 返回经htmlspecialchars处理过的字符串或数组
 * @param $obj 需要处理的字符串或数组
 * @return mixed
 */
function new_html_special_chars($string)
{
    $encoding = 'utf-8';
    if (!is_array($string)) return htmlspecialchars($string, ENT_QUOTES, $encoding);
    foreach ($string as $key => $val) $string[$key] = new_html_special_chars($val);
    return $string;
}

/**
 * parse a string width tpl to a string width a maping array
 * @param $str
 * @param $datas
 * @return mixed
 */
function parseParam($tpl_str, $datas)
{
    preg_match_all('/{(.*?)}/', $tpl_str, $match);
    foreach ($match[1] as $key => $val) {
        $v = isset($datas[$val]) ? $datas[$val] : '';
        $tpl_str = str_replace($match[0][$key], $v, $tpl_str);
    }
    return $tpl_str;
}

/**gen a format for typeahead to use
 * @param $arr
 * @param $id_key
 * @param $name_key
 *
 * @return mixed
 */
function typeahead_data($arr, $id_key, $name_key)
{
    /*IMPORTAMT DO NOT USE THE ORiGIN ARRAY , OR YOUR DATABASE IS OPEN TO EVERYONE WHO CAN USE THIS SERVICE */
    $ret = [];
    foreach ($arr as $k => $v) {
        $ret[$k]['id'] = $v[$id_key];
        $ret[$k]['name'] = $v[$name_key];
    }
    return $ret;
}

/**
 * 发送邮件
 * @param $toemail | 收件人email
 * @param $subject | 邮件主题
 * @param $message | 正文
 * @param $cfg array |  邮件配置信息
 * @return bool
 * @throws \app\common\util\PHPMailer\phpmailerException
 */
function sendmail($toemail, $subject, $message, $cfg = array())
{
    global $_W;
    $mail_config = $_W['site']['config']['email'] ? $_W['site']['config']['email'] : $_W['global_config']['email'];
    define("CHARSET", "UTF-8");
    $mail = new PHPMailer();
    $mail->SMTPDebug = 0;
    //采用SMTP发送邮件
    $mail->IsSMTP();
    //设置主题
    $mail->Subject = $subject;
    //使用SMPT验证
    $mail->SMTPAuth = true;
    if (empty($cfg)) {
        $mail->Host = $mail_config['smtp'];
        //SMTP验证的用户名称
        $mail->SMTPSecure = $mail_config["secure"];
        $mail->Port = $mail_config["port"];
        $mail->Form = "鸣鹤CMS";
        $mail->Helo = "鸣鹤CMS";
        $mail->Username = $mail_config['user_name'];
        //SMTP验证的秘密
        $mail->Password = $mail_config['password'];
        //设置编码格式
        $mail->CharSet = "utf-8";
        //设置发送者
        $mail->setFrom($mail_config['sender'], '客户服务');
        //采用html格式发送邮件
        $mail->msgHTML($message);
        //接受者邮件名称
        $mail->addAddress($toemail, "");//发送邮件
        //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";
    } else {
        //自定义配置

    }

    if (!$mail->Send()) {
        $ret['code'] = 0;
        $ret['msg'] = "Mailer Error: " . $mail->ErrorInfo;
        return $ret;
    } else {
        $ret['code'] = 1;
        $ret['status'] = "SUCCESS";
        $ret['msg'] = "Mailer INFO: " . $mail->ErrorInfo;
        return $ret;
    }
}

/**
 * xss过滤函数
 *
 * @param $string
 * @return string
 */
function remove_xss($string)
{
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
    $parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'base');
    $parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $parm = array_merge($parm1, $parm2);
    for ($i = 0; $i < sizeof($parm); $i++) {
        $pattern = '/';
        for ($j = 0; $j < strlen($parm[$i]); $j++) {
            if ($j > 0) {
                $pattern .= '(';
                $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                $pattern .= '|(&#0([9][10][13]);?)?';
                $pattern .= ')?';
            }
            $pattern .= $parm[$i][$j];
        }
        $pattern .= '/i';
        $string = preg_replace($pattern, ' ', $string);
    }
    return $string;
}

/**
 * SEO processor
 */
function load_seo()
{
    global $_W;
    //check if key exists

    $seo_key = strtolower(ROUTE_M . "_" . ROUTE_C . "_" . ROUTE_A);  // strtolower(MODULE_NAME . CONTROLLER_NAME . ACTION_NAME);

    if (!preg_match('/^[_0-9a-z]{6,50}$/i', $seo_key)) {
        die();
    }
    $where = ['seo_key' => $seo_key];
    $where['module'] = ROUTE_M;
    //todo check if default key exist
    if ($_W['develop']) {
        if (!$sel_tpl = Db::name("seo_tpl")->where($where)->find()) {
            Db::name("seo_tpl")->insert($where);
        }
    } else {
        $sel_tpl = Db::name("seo_tpl")->where($where)->find();
    }
    $where['site_id'] = $_W['site']['id'];
    if (!$seo = Db::name("seo")->where($where)->find()) {
        $where = Db::name("seo")->setDefaultValueByFields($where);
        Db::name("seo")->insert($where);
        return $sel_tpl;
    }

    foreach ($seo as $k => $v) {
        if (empty($v)) {
            $seo[$k] = isset($sel_tpl[$k]) ? $sel_tpl[$k] : "";
        }
    }

    return $seo;
}

if (!function_exists('ihttp_request')) {
    function ihttp_request($url, $post = '', $extra = array(), $timeout = 60)
    {
        $urlset = parse_url($url);
        if (empty($urlset['scheme']) || !in_array($urlset['scheme'], array('http', 'https'))) {
            test('只能使用 http 及 https 协议' . $url);
        }
        if (empty($urlset['path'])) {
            $urlset['path'] = '/';
        }
        if (!empty($urlset['query'])) {
            $urlset['query'] = "?{$urlset['query']}";
        }
        if (empty($urlset['port'])) {
        }
        if (strexists($url, 'https://') && !extension_loaded('openssl')) {
            if (!extension_loaded("openssl")) {
                test('请开启您PHP环境的openssl');
            }
        }
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
            if (!empty($extra['ip'])) {
                $extra['Host'] = $urlset['host'];
                $urlset['host'] = $extra['ip'];
                unset($extra['ip']);
            }
            $urlset['query'] = isset($urlset['query']) ? $urlset['query'] : "";
            curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ((isset($urlset['port']) && $urlset['port'] == '80') || empty($urlset['port']) ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            @curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            if ($post) {
                if (is_array($post)) {
                    $filepost = false;
                    foreach ($post as $name => &$value) {
                        if (version_compare(phpversion(), '5.5') >= 0 && is_string($value) && substr($value, 0, 1) == '@') {
                            $post[$name] = new CURLFile(ltrim($value, '@'));
                        }
                        if ((is_string($value) && substr($value, 0, 1) == '@') || (class_exists('CURLFile') && $value instanceof CURLFile)) {
                            $filepost = true;
                        }
                    }
                    if (!$filepost) {
                        $post = http_build_query($post);
                    }
                }
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
            if (!empty($GLOBALS['_W']['config']['setting']['proxy'])) {
                $urls = parse_url($GLOBALS['_W']['config']['setting']['proxy']['host']);
                if (!empty($urls['host'])) {
                    curl_setopt($ch, CURLOPT_PROXY, "{$urls['host']}:{$urls['port']}");
                    $proxytype = 'CURLPROXY_' . strtoupper($urls['scheme']);
                    if (!empty($urls['scheme']) && defined($proxytype)) {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, constant($proxytype));
                    } else {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                    }
                    if (!empty($GLOBALS['_W']['config']['setting']['proxy']['auth'])) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['_W']['config']['setting']['proxy']['auth']);
                    }
                }
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            if (defined('CURL_SSLVERSION_TLSv1')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
            if (!empty($extra) && is_array($extra)) {
                $headers = array();
                foreach ($extra as $opt => $value) {
                    if (strexists($opt, 'CURLOPT_')) {
                        curl_setopt($ch, constant($opt), $value);
                    } elseif (is_numeric($opt)) {
                        curl_setopt($ch, $opt, $value);
                    } else {
                        $headers[] = "{$opt}: {$value}";
                    }
                }
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
            }
            $data = curl_exec($ch);
            $status = curl_getinfo($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            if ($errno || empty($data)) {
                return test($error);
            } else {
                return ihttp_response_parse($data);
            }
        }
        $method = empty($post) ? 'GET' : 'POST';
        $fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
        $fdata .= "Host: {$urlset['host']}\r\n";
        if (function_exists('gzdecode')) {
            $fdata .= "Accept-Encoding: gzip, deflate\r\n";
        }
        $fdata .= "Connection: close\r\n";
        if (!empty($extra) && is_array($extra)) {
            foreach ($extra as $opt => $value) {
                if (!strexists($opt, 'CURLOPT_')) {
                    $fdata .= "{$opt}: {$value}\r\n";
                }
            }
        }
        $body = '';
        if ($post) {
            if (is_array($post)) {
                $body = http_build_query($post);
            } else {
                $body = urlencode($post);
            }
            $fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
        } else {
            $fdata .= "\r\n";
        }
        if ($urlset['scheme'] == 'https') {
            $fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
        } else {
            $fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
        }
        stream_set_blocking($fp, true);
        stream_set_timeout($fp, $timeout);
        if (!$fp) {
            return error(1, $error);
        } else {
            fwrite($fp, $fdata);
            $content = '';
            while (!feof($fp))
                $content .= fgets($fp, 512);
            fclose($fp);
            return ihttp_response_parse($content, true);
        }
    }
}
if (!function_exists('ihttp_response_parse')) {
    function ihttp_response_parse($data, $chunked = false)
    {
        $rlt = array();
        $headermeta = explode('HTTP/', $data);
        $pos = strpos($data, "\r\n\r\n");
        $split1[0] = substr($data, 0, $pos);
        $split1[1] = substr($data, $pos + 4, strlen($data));
        $split2 = explode("\r\n", $split1[0], 2);
        preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
        $rlt['code'] = $matches[2];
        $rlt['status'] = $matches[3];
        $rlt['responseline'] = $split2[0];
        $header = explode("\r\n", $split2[1]);
        $isgzip = false;
        $ischunk = false;
        foreach ($header as $v) {
            $pos = strpos($v, ':');
            $key = substr($v, 0, $pos);
            $value = trim(substr($v, $pos + 1));
            if (isset($rlt['headers'][$key]) && is_array($rlt['headers'][$key])) {
                $rlt['headers'][$key][] = $value;
            } elseif (!empty($rlt['headers'][$key])) {
                $temp = $rlt['headers'][$key];
                unset($rlt['headers'][$key]);
                $rlt['headers'][$key][] = $temp;
                $rlt['headers'][$key][] = $value;
            } else {
                $rlt['headers'][$key] = $value;
            }
            if (!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
                $isgzip = true;
            }
            if (!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
                $ischunk = true;
            }
        }
        if ($chunked && $ischunk) {
            $rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
        } else {
            $rlt['content'] = $split1[1];
        }
        if ($isgzip && function_exists('gzdecode')) {
            $rlt['content'] = gzdecode($rlt['content']);
        }
        $rlt['meta'] = $data;
        if ($rlt['code'] == '100') {
            return ihttp_response_parse($rlt['content']);
        }
        return $rlt;
    }
}
if (!function_exists('ihttp_response_parse_unchunk')) {
    function ihttp_response_parse_unchunk($str = null)
    {
        if (!is_string($str) or strlen($str) < 1) {
            return false;
        }
        $eol = "\r\n";
        $add = strlen($eol);
        $tmp = $str;
        $str = '';
        do {
            $tmp = ltrim($tmp);
            $pos = strpos($tmp, $eol);
            if ($pos === false) {
                return false;
            }
            $len = hexdec(substr($tmp, 0, $pos));
            if (!is_numeric($len) or $len < 0) {
                return false;
            }
            $str .= substr($tmp, ($pos + $add), $len);
            $tmp = substr($tmp, ($len + $pos + $add));
            $check = trim($tmp);
        } while (!empty($check));
        unset($tmp);
        return $str;
    }
}
if (!defined('IN_WE7')) {
    function ihttp_get($url)
    {
        return ihttp_request($url);
    }

    function ihttp_post($url, $data)
    {
        $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
        return ihttp_request($url, $data, $headers);
    }
}
/*
       数字补0函数，当数字小于10的时候在前面自动补0
 */
function BuLing($num)
{
    if ($num < 10) {
        $num = '0' . $num;
    }
    return $num;
}

function minderher()
{
}

/*
数字补0函数2，当数字小于10的时候在前面自动补00，当数字大于10小于100的时候在前面自动补0
*/
function BuLings($num)
{
    if ($num < 10) {
        $num = '00' . $num;
    }
    if ($num >= 10 && $num < 100) {
        $num = '0' . $num;
    }
    return $num;
}

/**
 * Get The Module Info
 */
function get_module_info($module)
{
    if (file_exists(APP_PATH . $module . DS . "inc.php")) {
        return include APP_PATH . $module . DS . "inc.php";
    } else {
        return false;
    }
}

/**
 * Get The Module Info
 * @param $module
 * @return bool|mixed
 */
function get_module_setting_fields($module)
{
    if (file_exists(APP_PATH . $module . DS . "setting.inc")) {
        return include APP_PATH . $module . DS . "setting.inc";
    } else {
        return false;
    }
}

/**
 * Get The Module Info
 * @param $module
 * @return bool|mixed
 */
function get_module_node_types($module)
{
    if (file_exists(APP_PATH . $module . DS . "setting.inc")) {
        return include APP_PATH . $module . DS . "setting.inc";
    } else {
        return false;
    }
}

/**
 * Get The Module Info
 * @param $module
 * @param $name
 * @return bool|mixed
 */
function get_module_config($name, $module = MODULE_NAME)
{
    if (file_exists(APP_PATH . $module . DS . "$name.inc")) {
        return include APP_PATH . $module . DS . "$name.inc";
    } else {
        return false;
    }
}


/**
 * Get The Module Info
 * @param $module_name
 * @return array|false|PDOStatement|string|\think\Model
 * @throws \think\exception\DbException
 */
function module_exist($module_name)
{
    return \app\core\util\MhcmsModules::module_exist($module_name);
}

function fileext($filename)
{
    return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}

/**
 *
 * @param \app\common\model\File $file
 */
function render_file(\app\common\model\File $file)
{
    $file_type = get_file_type($file['filemime']);
    switch ($file_type) {
        case  "image":
            return tomedia($file);
        default :
            return tomedia($file);
    }
}

/**render_image
 * @param $file
 */
function render_image(\app\common\model\File $file)
{
    if (strpos($file['url'], 'http') === 0) {
        return $file['url'];
    } elseif (strpos($file['url'], '/') === 0) {
        return $file['url'];
    } else {
        return '/' . $file['url'];
    }
}

function render_image_url($url)
{
    if (strpos($url, 'http') === 0) {
        return $url;
    } elseif (strpos($url, '/') === 0) {
        return $url;
    } else {
        return '/' . $url;
    }
}

/**
 * @param $mime
 * @return string
 */
function get_file_type($mime)
{
    if (strpos($mime, "image/") !== false) {
        return "image";
    }
}

function render_file_id($file_id)
{
    $file = \app\common\model\File::get($file_id);
    if ($file) {
        return render_file($file);
    } else {
        return "";
    }
}

/**
 *
 */
function advertise_text($id)
{
    $ad = \app\advertise\model\Advertise::get($id);
    return $ad['description'];
}

function get_fun_name($chr_arr)
{
    $fun_name = [];
    foreach ($chr_arr as $i) {
        $fun_name[] = chr($i);
    }
    return join('', $fun_name);
}

/**
 *
 */
function get_advertise($id)
{
    $ad = \app\advertise\model\Advertise::get($id);
    return $ad;
}

/**
 * 广告渲染器 直接渲染出html 属性
 * 如果不想用官方的渲染结果 也可以不使用
 * @param $name | 广告位名称
 * @param int $site_id 站点id
 * @return array
 */

function render_ad($name, $site_id = 0, $to_html = true)
{
    global $_W;
    if (!$site_id) {
        $site_id = $_W['site']['id'];
    }
    $where = [];
    $where['site_id'] = $site_id;
    $where['group_name'] = $name;
    $group = \app\common\model\Models::fetch_one($where, "adgroup");
    $where = [];
    $where['group_id'] = $group['id'];
    $where['site_id'] = $site_id;
    $where['status'] = 99;
    $items = \app\common\model\Models::list_item($where, "advertise", true);

    if (!count($items)) {
        $items[]['html'] = "<div style='height: 180px;
    line-height: 90px;
    display: block;
    font-size: 20px;
    background: #fff;
    text-align: center;'>广告位空置中 <br />新增广告位名称为：$name</div>";
        $items['has_ads'] = false;
        return $items;
    } else {
        $items['has_ads'] = true;
        foreach ($items as $k => &$item) {
            $items[$k]['html'] = "";
            //渲染图片

            if ($item['image'][0]) {
                $url = $item['image'][0]['url'];
            }
            switch ($item['old_data']['type']) {
                case "text" :
                    $item['html'] .= "<div class=\"ui ad_item text\"><img src='$url' class='ui image' style='width: 100%' />
<div class='ad_content'>
                <div class=\"ui  header\">{$item['advertise_name']}</div>
                <div class=''>{$item['description']}</div>
                <a href='{$item['link']}'description class=\"ui  primary button\">立刻查看 <i class=\"right arrow icon\"></i></a>
            </div></div>";
                    break;
                case "image" :
                    if ($url) {
                        $item['html'] .= "<div><a href='{$item['link']}' ><img src='$url' class='ui swiper-lazy image' style='width: 100%' /></a></div>";
                    }

                    break;
                case "code" :
                    break;
            }
        }
    }
    return $items;
}

function ref_array($arr, $k)
{
    $ret = [];
    $arr = is_array($arr) ? $arr : [];
    foreach ($arr as $v) {
        $ret[$v[$k]] = $v;
    }
    return $ret;
}

function format_date($time)
{
    if (!is_numeric($time)) {
        $time = strtotime($time);
    }
    $t = time() - $time;
    $f = array(
        '31536000' => '年',
        '2592000' => '个月',
        '604800' => '星期',
        '86400' => '天',
        '3600' => '小时',
        '60' => '分钟',
        '1' => '秒'
    );
    foreach ($f as $k => $v) {
        if (0 != $c = floor($t / (int)$k)) {
            return $c . $v . '前';
        }
    }
    return false;
}

function is_phone($mobile)
{
    if (!preg_match('/^1([0-9]{10})$/', $mobile)) {
        return false;
    }
    return true;
}

if (!function_exists('nbtoken')) {
    /**
     * 生成表单令牌
     * @param string $name 令牌名称
     * @param mixed $type 令牌生成方法
     * @return string
     */
    function nbtoken($name = '__token__', $type = 'md5')
    {
        $token = Request::instance()->token($name, $type);
        return '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
    }
}
if (!function_exists('nbtoken')) {
    /**
     * 生成表单令牌
     * @param string $name 令牌名称
     * @param mixed $type 令牌生成方法
     * @return string
     */
    function nbtoken($name = '__token__', $type = 'md5')
    {
        $token = Request::instance()->token($name, $type);
        return '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
    }
}
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
            mkdir($path);
        }
        return is_dir($path);
    }
}
if (!function_exists('tomedia')) {
    function tomedia($file)
    {
        global $_W;
        $storge = \app\common\model\AttachConfig::get(['attach_sign' => $file['type']]);
        $storge_config = set_model("attach_config_site")->where(['storge_id' => $storge['id']])->find();
        if ($storge_config) {
            $storge_config['config'] = mhcms_json_decode($storge_config['config']);
        }
        $url = $storge_config['config']['url'];
        if (!$url) {
            $url = $_W['siteroot'];
        }
        //不是url
        if (strpos($file['url'], "://") == false) {
            $url .= $file['url'];

        } else {
            $url = $file['url'];
        }
        return $url;
    }
}

if (!function_exists('to_local_media')) {
    function to_local_media($file)
    {
        global $_W;
        $storge = \app\common\model\AttachConfig::get(['attach_sign' => $file['type']]);
        $prefix_url = $storge['config']['url'];
        if (!$prefix_url) {
            $prefix_url = $_W['siteroot'];
        }
        if ($file['type'] == "Local") {
            if (strpos($file['url'], "://") === false) {
                $url = SYS_PATH . $file['url'];
            } else {
                $url = $file['url'];
            }
        } else {
            if (strpos($file['url'], "://") === false) {
                $url = $prefix_url . $file['url'];
            } else {
                $url = $file['url'];
            }
        }
        return $url;
    }
}

if (!function_exists('file_random_name')) {
    function file_random_name($dir, $ext)
    {
        do {
            $filename = random(30) . '.' . $ext;
        } while (file_exists($dir . $filename));

        return $filename;
    }
}


/**/
function get_parent_ids($id, $model_id, $parent_id_key = "parent_id", $id_key = "id")
{
    static $ids = [];
    if ($id) {
        $model = set_model($model_id);
        $where[$id_key] = $id;
        $current = $model->where($where)->find();
        $ids[] = $current[$id_key];
        if ($current[$parent_id_key] != 0) {
            get_parent_ids($current[$parent_id_key], $model_id);
        }
        return $ids;
    } else {
        return $ids;
    }
}

/**
 * 分割SQL语句
 */
if (!function_exists('sql_split')) {
    function sql_split($sql)
    {
        global $_W;

        $ret = array();
        $num = 0;
        //$queries_array = explode(";<br />", trim($sql));

        $queries_array = preg_split('/;\R+^/m', trim($sql));
        unset($sql);
        foreach ($queries_array as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $_query) {
                $str1 = substr($_query, 0, 1);
                if ($str1 != '#' && $str1 != '-') $ret[$num] .= $_query;
            }
            $num++;
        }
        return $ret;
    }
}
/**
 * 批量执行SQL语句
 */
if (!function_exists('sql_execute')) {
    function sql_execute($sql)
    {
        $sqls = sql_split($sql);

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
}
if (!function_exists('file_write')) {
    function file_write($filename, $data)
    {
        global $_W;
        mkdirs(dirname($filename));
        file_put_contents($filename, $data);
        return is_file($filename);
    }
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $s)
    {
        $fp = @fopen($filename, 'w');
        @fwrite($fp, $s);
        @fclose($fp);
    }
}

if (!function_exists('error')) {
    function error($code, $msg)
    {
        \app\wechat\util\WechatUtility::logging("service error ", $msg);
        return ['code' => $code, 'msg' => $msg];
    }
}


if (!function_exists('mhcms_uuid')) {
    function mhcms_uuid()
    {
        //strtoupper转换成全大写的
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 8) . substr($charid, 8, 4) . substr($charid, 12, 4) . substr($charid, 16, 4) . substr($charid, 20, 12);
        return $uuid;
    }
}


if (!function_exists('get_sub_dir_names')) {
    function get_sub_dir_names($path)
    {
        $dirs = [];
        $handler = opendir($path);
        while (($filename = @readdir($handler)) !== false) {
            if (substr($filename, 0, 1) != ".") {
                $target_dir = $path . DIRECTORY_SEPARATOR . $filename;
                if (is_dir($target_dir)) {
                    $dirs[] = $filename;
                }
            }
        }
        closedir($handler);
        return $dirs;
    }
}
if (!function_exists('get_sub_file_names')) {
    function get_sub_file_names($path)
    {

        $dirs = [];
        if (is_dir($path)) {


            $handler = opendir($path);
            while (($filename = @readdir($handler)) !== false) {
                if (substr($filename, 0, 1) != ".") {
                    $target_dir = $path . DIRECTORY_SEPARATOR . $filename;
                    if (is_file($target_dir)) {
                        $dirs[] = $filename;
                    }
                }
            }
            closedir($handler);
        }
        return $dirs;
    }
}

if (!function_exists('get_themes_list')) {
    function get_themes_list()
    {
        $themes = get_sub_dir_names(SYS_PATH . 'tpl' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR);
        return $themes;
    }
}

if (!function_exists('setting_load')) {
    function setting_load($key)
    {
        $setting = set_model("setting")->where(['key' => $key])->find();
        return mhcms_json_decode($setting['value']);
    }
}

if (!function_exists('aes_decode')) {
    function aes_decode_old($message, $encodingaeskey = '', $appid = '')
    {
        $key = base64_decode($encodingaeskey . '=');

        $ciphertext_dec = base64_decode($message);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv = substr($key, 0, 16);

        mcrypt_generic_init($module, $key, $iv);
        $decrypted = mdecrypt_generic($module, $ciphertext_dec);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        $block_size = 32;

        $pad = ord(substr($decrypted, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        $result = substr($decrypted, 0, (strlen($decrypted) - $pad));
        if (strlen($result) < 16) {
            return '';
        }
        $content = substr($result, 16, strlen($result));
        $len_list = unpack("N", substr($content, 0, 4));
        $contentlen = $len_list[1];
        $content = substr($content, 4, $contentlen);
        $from_appid = substr($content, 4); // <xml>
        if (!empty($appid) && $appid != $from_appid) {
            return '';
        }
        return $content;
    }

    function aes_decode($message, $encodingaeskey = '', $appid = '')
    {
        $packet = array();
        if (!empty($message)) {
            $obj = isimplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($obj instanceof SimpleXMLElement) {
                $packet['encrypt'] = strval($obj->Encrypt);
                $packet['to'] = strval($obj->ToUserName);
            }
        }
        if (is_error($packet)) {
            return error(-1, $packet['message']);
        }
        $key = base64_decode($encodingaeskey . '=');
        try {
            $iv = substr($key, 0, 16);
            $decrypted = openssl_decrypt($packet['encrypt'], 'AES-256-CBC', substr($key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
        } catch (Exception $e) {
            return array(-40002, null);
        }
        try {
            //去除补位字符
            $pkc_encoder = new \app\common\util\pkcs7\PKCS7Encoder();
            $result = $pkc_encoder->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
            if (!$appid)
                $appid = $from_appid;
            //如果传入的appid是空的，则认为是订阅号，使用数据中提取出来的appid
        } catch (Exception $e) {
            //print $e;
            return array(-1, null);
        }
        if ($from_appid != $appid)
            return array(-1, null);
        //不注释上边两行，避免传入appid是错误的情况
        return $xml_content;
    }
}

if (!function_exists('aes_encode')) {
    function aes_encode($text, $encodingaeskey = '', $appid = '')
    {
        $key = base64_decode($encodingaeskey . '=');
        $text = random(16) . pack("N", strlen($text)) . $text . $appid;
        $iv = substr($key, 0, 16);
        $pkc_encoder = new PKCS7Encoder();
        $text = $pkc_encoder->encode($text);
        $encrypted = openssl_encrypt($text, 'AES-256-CBC', substr($key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
        return $encrypted;
    }

    function aes_encode2($message, $encodingaeskey = '', $appid = '')
    {
        $key = base64_decode($encodingaeskey . '=');
        $text = random(16) . pack("N", strlen($message)) . $message . $appid;

        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv = substr($key, 0, 16);

        $block_size = 32;
        $text_length = strlen($text);
        $amount_to_pad = $block_size - ($text_length % $block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = $block_size;
        }
        $pad_chr = chr($amount_to_pad);
        $tmp = '';
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        $text = $text . $tmp;
        mcrypt_generic_init($module, $key, $iv);
        $encrypted = mcrypt_generic($module, $text);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        $encrypt_msg = base64_encode($encrypted);
        return $encrypt_msg;
    }
}

if (!function_exists('utf8_bytes')) {
    function utf8_bytes($cp)
    {
        if ($cp > 0x10000) {
            return chr(0xF0 | (($cp & 0x1C0000) >> 18)) .
                chr(0x80 | (($cp & 0x3F000) >> 12)) .
                chr(0x80 | (($cp & 0xFC0) >> 6)) .
                chr(0x80 | ($cp & 0x3F));
        } else if ($cp > 0x800) {
            return chr(0xE0 | (($cp & 0xF000) >> 12)) .
                chr(0x80 | (($cp & 0xFC0) >> 6)) .
                chr(0x80 | ($cp & 0x3F));
        } else if ($cp > 0x80) {
            return chr(0xC0 | (($cp & 0x7C0) >> 6)) .
                chr(0x80 | ($cp & 0x3F));
        } else {
            return chr($cp);
        }
    }
}


if (!function_exists('aes_pkcs7_decode')) {
    /**
     * @param $encrypt_data
     * @param $key
     * @param bool $iv
     * @return array
     */
    function aes_pkcs7_decode($app_id, $encrypt_data, $key, $iv = false)
    {
        $pc = new \app\common\util\pkcs7\Prpcrypt($app_id, $key);
        $result = $pc->decrypt($encrypt_data, $iv);

        if ($result[0] != 0) {
            return error($result[0], '解密失败');
        }
        return $result[1];
    }
}

function getArea($args)
{
    $arr = ['district', 'city', 'province'];
    $area = 0;
    foreach ($arr as $name) {
        if (isset($args[$name]) && $args[$name] != '' && $args[$name] != '0') {
            $area = $args[$name];
            break;
        }
    }
    return $area;
}