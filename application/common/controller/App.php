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
namespace app\common\controller;

use app\common\model\Common;
use app\common\model\Linkage;
use app\common\model\Node;
use app\common\model\Roots;
use app\common\model\Sites;
use app\common\util\forms\input;
use app\core\util\MhcmsModules;
use think\Config;
use think\Controller;
use think\Cookie;
use think\Db;
use think\Exception;
use think\exception\HttpResponseException;
use think\Loader;
use think\Response;
use think\Url;
use think\Request;
use think\Cache;
use think\View;

class App extends Controller
{
    public $m_c_d, $sso_domain
    , $cdn_url
    , $cache
    , $node
    , $user
    , $config
    , $root
    , $site_domain
    , $site
    , $current_domain
    , $root_host
    , $site_id
    , $base
    , $user_role
    , $current_menu;
    /** @var Request $request */
    public $request;

    protected static function write_config($config_name, $data, $extra = false)
    {
        if ($extra) {
            $path = CONF_PATH . "extra" . DIRECTORY_SEPARATOR;
        } else {
            $path = CONF_PATH;
        }
        $size = file_put_contents($path . $config_name . ".php", '<?php return ' . var_export($data, true) . ';');
        return $size;
    }

    /**
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        global $_W, $_GPC;



        define('SITE_PROTOCOL', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
        $_W['siteroot'] = $this->request->domain() . "/";
        $_GPC = input('param.');
        //是否开启PC模板
        $_W['DEVICE_TYPE_TPL'] = $_W['DEVICE_TYPE'] = is_mobile() ? "mobile" : "desktop";//当前客户端

        $this->view = new MhcmsView();
        $this->view->system = $this->view->system_info = get_module_version('mhcms');
        //pjax
        if($this->request->header('X-PJAX')){
            $this->view->config([
                'view_suffix' => "php",
            ]);
            $_W['pjax'] = true;
        }else{
            $_W['pjax'] = false;
        }

        $this->request = Request::instance();
        //Recover the 3.2 common used constant
        if (!defined("ROUTE_M")) {
            define("ROUTE_M", $this->request->module());
            define("ROUTE_C", Loader::parseName($this->request->controller()));
            define("ROUTE_A", $this->request->action());
        }

        //模板消息发送
        $_W['tpl_config'] = '{"first":{"value":"{header}","color":"#000000"},"remark":{"value":"{footer}","color":"#000000"},"keyword1":{"value":"{keyword1}","color":"#000000"},"keyword2":{"value":"{keyword2}","color":"#000000"},"keyword3":{"value":"{keyword3}","color":"#000000"},"miniprogram":{"appid":"","pagepath":""}}';


        self::_check();
        //member center  domain
        $this->view->m_c_d = $_W['m_c_d'] = $this->m_c_d = config('global.sso_domain');
        //静态文件优化备案
        $this->view->cdn_url = $_W['cdn_url'] = $this->cdn_url = "/";

        $_W['develop'] = $_W['debug'] = false;
        if (module_exist('debug') && config('app_debug')) {
            $_W['debug'] = true;
            $_W['develop'] = 1;
        }
        /**
         * 处理并且加载模块配置
         */
        if (!$this->module = module_exist(ROUTE_M)) {
            $this->error(ROUTE_M . "模块已经禁用");
        } else {
            Config::load(CONF_PATH . ROUTE_M . ' _config.php');
        }
    }

    /**
     * blow function just for compatible for old code
     * @return mixed
     */
    public function isPost($check_token = false)
    {
        global $_GPC;
        $this->check_token = isset($this->check_token) ? $this->check_token : $check_token;
        $is_post = $this->request->isPost();
        if ($is_post) {
            if (isset($this->check_token) && $this->check_token == true) {
                $res = $_GPC['__token__'] ? check_token($_GPC['__token__']) : false;
                if (!$res) {
                    $this->zbn_msg("对不起，您的会话不合法 ，请刷新页面重试！");
                }
            }
        } else {
        }
        return $is_post;
    }

    /**
     * @param $message
     * @param string $icon
     * @param string $auto_close
     * @param int $time
     * @param string $jumpUrl
     * @param string $javascript
     */
    public function zbn_msg($message, $icon = "-1", $auto_close = '', $time = 2000, $jumpUrl = "''", $javascript = "", $data = [])
    {
        global $_W;
        $auto_close = empty($auto_close) ? "false" : "true";
        $str = '<script>';
        $jumpUrl = $jumpUrl == "''" || empty($jumpUrl) ? "''" : $jumpUrl;
        $javascript = $javascript == "''" || empty($javascript) ? "''" : $javascript;
        $str .= 'parent.show_message("' . $message . '",' . $icon . "," . $auto_close . "," . $time . ',' . $jumpUrl . ',' . $javascript . ');';
        $str .= '</script>';
        if ($this->request->isAjax()) {
            $jumpUrl = $jumpUrl == "''" ? null : $jumpUrl;
            $this->message($message, $icon, $jumpUrl, $data);
        } else {
            echo($str);
        }
        die();
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function message($msg = '', $code = 1, $url = HTTP_REFERER, $data = '', $wait = 3, array $header = [])
    {
        $url = str_replace("'", "", $url);
        if ($url == 'HTTP_REFERER' && !is_null(Request::instance()->server('HTTP_REFERER'))) {
            $url = Request::instance()->server('HTTP_REFERER');
        } elseif ('' !== $url) {
            $url = (strpos($url, 'javascript') !== false || strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Url::build($url);
        }
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];
        $type = $this->getResponseType();
        if ('html' == strtolower($type)) {
            $result = View::instance(Config::get('template'), Config::get('view_replace_str'))
                ->fetch(Config::get('dispatch_success_tmpl'), $result);
        }
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    public function __call($name, $arguments)
    {

    }

    /**
     * SEO 处理函数
     * @param $mapping array
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function seo($mapping)
    {
        global $_W;
        $seo_tpl = load_seo();
        foreach ($seo_tpl as $k => $v) {
            $seo_tpl[$k] = parseParam($v, $mapping);
        }
        $seo_tpl['share_url']  = $_W['share_url'];

        $this->view->seo = $seo_tpl;
        return $seo_tpl;
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        if (is_null($url) && !is_null(Request::instance()->server('HTTP_REFERER'))) {
            $url = Request::instance()->server('HTTP_REFERER');
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Url::build($url);
        }
        $result = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];
        $type = $this->getResponseType();
        if ('html' == strtolower($type)) {
            $result = View::instance(Config::get('template'), Config::get('view_replace_str'))
                ->fetch(Config::get('dispatch_success_tmpl'), $result);
        }
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    public static function _check()
    {
        if (date('h') == 23) {
            Config::load(CONF_PATH . 'licence.php');
            $licence = config('licence');
            $url = 'http://cloud.bao8.org/product/licence/check';
            $licence['domain'] = $_SERVER['HTTP_HOST'];
            $last_check = Cache::get("last_check");
            //installed
            if (time() - $last_check > 86400 && strpos($licence['domain'], "cloud.bao8.org")===false) {
                $res = ihttp_post($url, $licence);
                $res = $res['content'];
                Cache::set("last_check", time());
            }
        }
    }


    public function _empty($name)
    {
        test("EMPTY ACTION");
    }


}
