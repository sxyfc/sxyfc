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

use app\common\model\Sites;
use app\common\model\SitesWechat;
use app\common\model\UserMenu;
use app\common\model\UserRoles;
use app\common\model\Users;
use app\common\util\forms\FormFactory;
use app\common\util\wechat\wechat;
use app\core\util\MhcmsModules;
use app\wechat\util\MhcmsWechatEngine;
use think\Cache;
use think\Config;
use think\Cookie;
use think\Log;
use think\Request;
use think\Route;
use think\Lang;
use think\Db;
use app\common\model\Roots;

/**
 * Class Base
 * @package app\common\controller
 */
class Base extends App
{
    /** @var array mapping array for tpl string */
    public $mapping;
    /** @var Sites $site */
    public $site;

    /** @var boolean $in_app_mode */
    public $in_app_mode = false;
    /** @var Roots $site */
    public $root;
    public $root_host, $current_domain, $user_id;
    /** @var FormFactory $form_factory */
    public $form_factory;
    /** @var Users $user */
    public $user;
    /** @var boolean $super_power , $sub_super */
    public $super_power = false, $sub_super = false;

    public $menu_id;
    //System related members
    protected $beforeActionList = [
        '_',
    ];
    public $d_domain_model = false;

    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        global $_W, $_GPC;
        parent::_initialize();
        //2017-11-04 当前访问网址
        $_W['siteroot'] = $this->request->domain() . "/";
        $_W['current_url'] = $_W['siteroot'] . substr($this->request->url(), 1);
        $_W['share_url'] = $_W['current_url'];
        $this->identify_site();

        $this->global_config = $_W['global_config'] = config('mhcms_' . $this->root['id'] . '.mhcms_config');

        if (!module_exist("sites")) {
            $_W['global_config']['groups_mode'] = 1;
            $_W['global_config']['sso_domain'] = $this->request->host();
        }

        $bad_ips = explode("#" , $_W['global_config']['secure']['bad_ip']);

        if(count($bad_ips)){
            foreach($bad_ips as $bad_ip){
                if(Request::instance()->ip()==$bad_ip){
                    exit();
                }
            }
        }
        $this->module_config = $module_config = MhcmsModules::get_module_setting($this->module['module']);

        /**
         * process the config
         */
        $this->site->get_config();
        /**
         * 加载当前语言包 load language
         * */
        if ($this->site['site_language']) {
            Lang::range($this->site['site_language']);
            Lang::load(APP_PATH . 'common/lang/' . $this->site['site_language'] . '.php');
        }
        /**
         * basic node and it's form factory
         * 基础表单控制类
         */
        $this->form_factory = new FormFactory($this->site['id']);
        $this->form_factory->site = $this->site;
        $_W['form_factory'] = $this->view->form_factory = $this->form_factory;
        /**
         * process the user
         * 获取当前用户信息 并记录当前用户所在的站点
         */
        $this->user = $_W['user'] = check_user(); // ? check_user() :check_admin();
        $this->user_role = $_W['user_role'] = UserRoles::get($this->user['user_role_id']);
        if ($this->user) {


            if (strpos($_W['share_url'], "?") === false) {
                $_W['share_url'] .= "?refer=" . $this->user['id'];
            } else {
                $_W['share_url'] .= "&refer=" . $this->user['id'];
            }

            $this->view->user_role = $this->user_role;
            $this->view->user_id = $this->user_id = $_W['user_id'] = $this->user['id'];
            $this->view->user = $this->user;
            //todo 记录当前用户站群足迹
            if ($this->site['user_id'] == $this->user->id) {
                $_W['sub_super'] = 1;
                $_W['user_role_id'] = 3;
            } else {
                $_W['user_role_id'] = $this->user['user_role_id'];
            }
        }


        /*
         * 这里处理站群检测是否自动增加区县站点处
         * */
        $no_check_area = 0;
        if (isset($_W['global_config']['auto_create_site']) && $_W['global_config']['auto_create_site']) {
            $no_check_area = 1;
        }
        $this->view->no_check_area = $no_check_area;


        /**
         * front member menu is processed here
         * 菜单处理
         */
        $this->user_menu_id = $user_menu_id = input('user_menu_id', 0, "intval");

        //NOT  APP REQ
        if ($user_menu_id) {
            $this->current_menu = UserMenu::get(['id' => $user_menu_id]);
            if (!$this->user && $this->current_menu['is_admin'] == 0) {
                $this->zbn_msg("对不起，你的会话已经过期");
            }
            //check the route is match the author
            if (ROUTE_M != $this->current_menu['user_menu_module'] || ROUTE_C != $this->current_menu['user_menu_controller']
                || ROUTE_A != $this->current_menu['user_menu_action']) {
                die("ERROR ACCESS");
            }
            //前菜单处理
            if ($this->current_menu['is_admin'] == 0) {
                /**
                 * not a global menu
                 */
                $map['user_menu_id'] = $user_menu_id;
                $map['user_role_id'] = $_W['user_role_id'];

                if (!strpos(ROUTE_A, 'api') && !strpos(ROUTE_A, 'service')) {
                    $match['user_menu_id'] = $user_menu_id;
                    $match['user_id'] = $_W['id'];

                    if (!Db::name('user_menu_allot')->where($match)->find()) {
                        if (!Db::name('user_menu_access')->where($map)->find()) {
                            $this->error("you don't have a key for this door!");
                        }
                    }
                }
                //user verify load menu info
                $this->view->user_menu = $this->current_menu;

            } else {
                //admin menu

            }


            /**
             * sub menus
             */
            if ($this->current_menu['alias']) {
                $menu_id = $this->current_menu['alias'];
            } else {
                $menu_id = $this->current_menu['id'];
            }
            //加载显示的子菜单
            $map = array(
                'user_menu_parentid' => $menu_id
            );

            $sub_menus = Db::name('user_menu')->where($map)->order('user_menu_listorder desc')->select();
            //sub menu process params
            foreach ($sub_menus as $k => $sub_menu) {
                $sub_menu['user_menu_params'] = parseParam($sub_menu['user_menu_params'], $this->mapping);
            }


            /**
             * 筛选菜单
             */
            $this->menu_id = $menu_id;

            $this->view->assign("sub_menu", $sub_menus);

        } else {
            $current_menu = 0;
        }
        $this->view->current_menu = $_W['current_menu'] = $this->current_menu;
        /*
         * SEO Mapping SEO变量替换在这里开始
         * */
        $this->mapping['site_name'] = $this->site['site_name'];
        $this->mapping['root_name'] = $this->root['root_name'];


        /**
         * 处理访问日志
         */

        //todo 恢复模块日志处理设置
        if ($this->module['log_access']) {
            $this->access_logs();
        }


        //todo weixin share address

        $_W['in_wechat'] = false;
        //默认不借用整站的微信
        $this->is_borrow_wx = $_W['is_borrow_wx'] = false;

        if (module_exist('sites')) {
            $site_wechat = SitesWechat::get(['site_id' => $this->site_id]);
            if (!$site_wechat) {
                //todo mutile site wechat mode process

                //todo load default site_wechat
                $default_site = Sites::get(['id' => $_W['root']['site_id']]);

                if($default_site){
                    $default_site->get_config();
                    $site_wechat = SitesWechat::get(['site_id' => $default_site['id']]);
                    $this->is_borrow_wx = $_W['is_borrow_wx'] = true;
                }

            }
        } else {
            //sigle
            $site_wechat = SitesWechat::get(['site_id' => $this->site_id]);
        }

        if (!$site_wechat) {
            //未配置微信
            return false;
        }


        $_W['wechat_fans_model'] = set_model("sites_wechat_fans");


        $_W['account'] = $_W['site_wechat'] = $this->site['site_wechat'] = $this->site_wechat = $site_wechat;

        //todo :get wechat fans info
        if ($_W['is_borrow_wx'] == true) {
            return false;
        } else {
            $_W['openid'] = Cookie::get("openid_" . $this->site['id']);
            /**
             *
             * if (is_weixin() && !$this->in_app_mode) {
             * $this->wechat = $wechat = $_W['wechat_account'] = MhcmsWechatEngine::create($_W['account']);
             * $_W['in_wechat'] = true;
             * $_W['openid'] = Cookie::get("openid_" . $this->site['id']);
             * if (!$_W['openid']) {
             * $code = isset($_GPC['code']) ? $_GPC['code'] : '';
             * $get_base_info = $wechat->getOauthInfo($code);
             * $_W['openid'] = $get_base_info['openid'];
             * if ($_W['openid']) {
             * Cookie::set("openid_" . $this->site['id'], $_W['openid']);
             * }
             * }
             * }
             */
        }

    }

    public static function get_root()
    {
        $request = Request::instance();
        $request_host = $request->host();
        $domain_data = explode(".", $request_host);

        if (count($domain_data) != 3) {
            Log::write("error domain");
            die();
        }

        $request_root_host = $domain_data[1] . "." . $domain_data[2];
        $root = $_W['root'] = Roots::where(['root_domain' => $request_root_host])->find();
        return $root;
    }

    public function identify_site()
    {
        global $_W, $_GPC;
        //处理根域名
        $this->current_domain = $this->request->domain();
        $this->request_host = $this->request->host();
        $domain_data = explode(".", $this->request_host);
        if (count($domain_data) != 3) {
            Log::write("error domain");
            die();
        }

        $this->request_root_host = $domain_data[1] . "." . $domain_data[2];
        /**
         * 非官方开发站点 开启自定义模式
         */
        $_W["DIY_MODE"] = 0;

        if ($this->request_root_host != "zxw.bz") {
            $_W["DIY_MODE"] = 1;
        }

        $this->root = $_W['root'] = Roots::where(['root_domain' => $this->request_root_host])->find();


        $this->d_domain_model = false;

        // if not root found this will not running in app mode
        if (!$this->root || $this->root['root_domain'] != $this->request_root_host) {
            $this->d_domain_model = true;
        }

        $this->view->root_domain = $_W['root_domain'] = $this->root_host;


        $group_mode = $_W['root']['groups_mode'] ? $_W['root']['groups_mode'] : 1;//config('mhcms_config.groups_mode');


        /** check if current env is in app_mode */
        if (!$this->in_app_mode) {
            $this->in_app_mode = input('param.app_mode', false, 'intval');


        }
        if ($this->in_app_mode) {
            $this->site_id = (int)$_GPC['site_id'];
            if ($_GPC['app_id']) {
                $_W['smallapp'] = $app = set_model("sites_smallapp")->where(['app_id' => $_GPC['app_id']])->find();
                $this->site_id = $app['site_id'];
            }
            if ($this->site_id) {
                $where['id'] = $this->site_id;
                $this->site = Sites::get($where);
            } else {
                die("NO ACCESS APP");
            }
        } else {
            //site group mode
            switch ($group_mode) {
                case 1:
                    //load default site
                    $where['default'] = 1;
                    $this->site = Sites::get($where);
                    $this->site_id = $this->site['id'];
                    break;
                case 2 :
                    if (!$this->d_domain_model) {
                        $_W['root_id'] = $this->root['id'];
                        $where['site_domain'] = $domain_data[0];
                        $this->site = Sites::get($where);
                        $this->site_id = $this->site['id'];
                    } else {
                        $_W['sites_domain'] = $d_domain = Db::name("sites_domain")->where(['domain' => $this->request_host])->find();
                        if (!$d_domain) {
                            echo "D: Sorry , It's an Error , That's all we know! !";
                            die();
                        } else {
                            $this->site = Sites::get(['id' => $d_domain['site_id']]);
                            $this->site_id = $this->site['id'];
                        }
                    }
                    break;
                case 3:
                    /**
                     * Analyze current request domain info
                     * 非强制模式
                     */
                    if (!isset($_GPC['site_id']) || empty($_GPC['site_id'])) {
                        //读取历史
                        $this->site_id = (int)Cookie::get('site_id');
                        if ($this->site_id) {
                            $where['id'] = $this->site_id;
                            $this->site = Sites::get($where);
                        }
                    } else {
                        //如果强制了站点
                        $this->site_id = (int)$_GPC['site_id'];
                        if ($this->site_id) {
                            $where['id'] = $this->site_id;
                            $this->site = Sites::get($where);
                        }
                    }
                    //如果没有找到站点  load_ default site
                    if (!isset($this->site) || !$this->site) {
                        $where['default'] = 1;
                        $this->site = Sites::get($where);
                    }
                    $this->site_id = $this->site['id'];

                    break;
            }
        }
        if (!$this->site) {
            echo " Sorry , It's an Error , That's all we know! !";
            die();
        } else {
            // group model is ok
            $_W['site'] = $this->site;
            $this->site_id = $_W['site_id'] = $_W['site']['id'];
            $this->view->site = $this->site;
        }

    }

    /**
     * 简单的记录统计
     * TODO 访问统计
     */
    private function access_logs()
    {
        global $_W;
        $insert = [];
        $insert['url'] = get_url();
        $no_log_ctrls = ['api_ , service_'];
        foreach ($no_log_ctrls as $ctrl) {
            if (strpos($insert['url'], $ctrl)) {
                return false;
            }
        }

        $insert['user_agent'] = $this->request->header('user-agent');
        $insert['ip'] = $this->request->ip();
        $insert['referer'] = HTTP_REFERER;
        $filter_referer_arrays = [
            'www.domain2008.com', "http://www.baidu.com/s?wd=", "www.inboundlinks.win"
        ];
        foreach ($filter_referer_arrays as $key) {
            if (strpos($insert['referer'], $key) !== false) {
                die();
            }
        }
        if ($this->user) {
            $insert['user_name'] = $this->user['user_name'];
            $insert['user_id'] = $this->user['id'];
        }
        $admin = check_admin();
        if ($admin) {
            $insert['admin_id'] = $admin['id'];
        }

        $insert['module'] = ROUTE_M;
        $insert['controller'] = ROUTE_C;
        $insert['action'] = ROUTE_A;
        Db::name('access_logs')->insert($insert);
    }
}
