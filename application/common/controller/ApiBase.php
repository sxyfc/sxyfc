<?php

namespace app\common\controller;

use app\common\model\Roots;
use app\common\model\Sites;
use app\common\model\SitesWechat;
use app\core\util\MhcmsModules;

class ApiBase extends App
{
    public $small_app;

    public function _initialize()
    {
        global $_W, $_GPC;
        parent::_initialize();
        $_W['siteroot'] = $this->request->domain() . "/";
        $url = $_W['siteroot'] . substr($this->request->url(), 1);

        $url = str_replace("&_pjax=.app_container", '', $url);
        $_W['current_url'] = str_replace("?_pjax=.app_container", '', $url);

        /*
         * 按照小程序定位
         */
        if (isset($_GPC['app_id']) && $_GPC['app_id']) {
            $this->small_app = $_W['small_app'] = $small_app = set_model("sites_smallapp")->where(['app_id' => $_GPC['app_id']])->find();

            $this->site_id = $_W['site_id'] = (int)$small_app['site_id'];
            $this->site = $_W['site'] = Sites::get(['id' => $this->site_id]);
            if (!$this->small_app) {
                die("small_app not found");
            }
            //todo fetch fans
        } else {
            $this->site_id = $_W['site_id'] = (int)$_GPC['site_id'];
            $this->site = $_W['site'] = Sites::get(['id' => $this->site_id]);
        }
        if (!$this->site) {
            $ret = [
                'code' => 2,
                'msg' => 'site not found'
            ];

            echo json_encode($ret);
            die();
        }

        //todo load root
        $this->current_domain = $this->request->domain();
        $this->request_host = $this->request->host();
        $domain_data = explode(".", $this->request_host);
        if (count($domain_data) != 3) {
            die();
        }
        $this->request_root_host = $domain_data[1] . "." . $domain_data[2];
        $this->root = $_W['root'] = Roots::where(['root_domain' => $this->request_root_host])->find();


        $this->module_config = $_W['module_config'] = MhcmsModules::get_module_setting($this->module['module']);


        $_W['site_wechat'] = $site_wechat = SitesWechat::get(['site_id' => $this->site_id]);
        $_W['wechat_fans_model'] = set_model("sites_wechat_fans");


        $this->site->get_config();
        $this->user = $_W['user'] = check_user();

        $_W['account'] = SitesWechat::get(['site_id' => $this->site['id']]);

        $this->user = $_W['user'] = check_user();
    }


    public function identify_site()
    {
        global $_W, $_GPC;
        //处理根域名
        $this->current_domain = $this->request->domain();
        $this->request_host = $this->request->host();
        $domain_data = explode(".", $this->request_host);
        if (count($domain_data) != 3 && !config('app_debug')) {
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

        $this->root = $_W['root'] = Roots::find();

        $this->d_domain_model = false;

        //this will not running in app mode
        if ($this->root['root_domain'] != $this->request_root_host) {
            $this->d_domain_model = true;
        }

        $this->view->root_domain = $_W['root_domain'] = $this->root_host;


        $group_mode = $_W['root']['groups_mode'];//config('mhcms_config.groups_mode');


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
                    }

                    if (!$this->site_id || !$this->site && module_exist('motu')) {
                        $this->d_domain_model = true;
                        //独立域名
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

}