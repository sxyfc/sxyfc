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
namespace app\core\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\core\model\ModulesSetting;
use think\Cache;
use think\Config;
use think\Db;
use think\View;

if (!defined('API_URL')) {
    define("API_URL", "http://cloud.bao8.org/");
}
Config::load(CONF_PATH . 'licence.php');

class Modules extends AdminBase
{

    public $modules = "modules";

    public function add()
    {
        global $_GPC;
        //后去模型信息
        $model = set_model($this->modules);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //手动处理类型的模型
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields();
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        global $_GPC;
        $id = (int)$id;
        $model = set_model($this->modules);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
            }
            // todo  process data input
            Db::name($model_info['table_name'])->where($where)->update($data);
            $this->zbn_msg("ok");
        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function index()
    {
        //
        $modules = [];
        Config::load(CONF_PATH . 'licence.php');
        $res = self::get_all_apps();
        $app_lists = $res['app_lists'];
        //test($app_lists);
        if ($res['code'] != 1) {
            $this->error($res['msg'], null, ['auto' => false]);
        }
        if (count($app_lists) == 0) {
            $app_lists = [];
        }
        $this->view->modules = $app_lists;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public static function get_all_apps()
    {
        $url = API_URL . 'product/service/get_apps';
        $licence = config('licence');
        //$licence['domain'] = $_SERVER['HTTP_HOST'];
        $res = ihttp_request($url, $licence);
        //test($res['content']);
        return json_decode($res['content'], 1);
    }

    public static function get_apps()
    {
        $url = API_URL . 'product/service/get_apps';
        $licence = config('licence');
        $licence['domain'] = $_SERVER['HTTP_HOST'];
        $res = ihttp_request($url, $licence);
        return json_decode($res['content'], 1);
    }

    public function uninstall($module)
    {
        $module_info = module_exist($module);
        if (!$module_info) {
            $this->error("模块未安装");
        } else {
            $module_info['status'] = 0;
            $module_info->save();
            $this->success("卸载成功");
        }
    }

    /**
     * @param $module
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function setting($module)
    {
        global $_GPC;
        $_module = module_exist($module);
        if (!$_module) {
            $this->error("模块未安装");
        }
        $this->view->module = $_module;
        if ($this->isPost()) {
            $data['mhcms_config'] = $_GPC;
            self::write_config("module_" . $module . '_config', $data, true);
            $this->zbn_msg("操作成功");
        } else {
            $this->view->config([
                'view_path' => strtolower(APP_PATH . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR),
            ]);
            $module_config = config("module_{$module}_config");
            $this->view->config = $module_config['mhcms_config'];
            return $this->view->fetch();
        }
    }

    /**
     * @param $module
     * @return mixed
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function module_setting($module)
    {
        global $_GPC, $_W;
        //todo verify auth
        $_module = module_exist($module);
        if (!$_module) {
            $this->error("模块未安装");
        } else {
            $this->view->module = $_module;
        }

        $module_set_model = set_model("modules_setting");
        $where['site_id'] = $_W['site']['id'];
        $where['module'] = $module;
        $detail = $module_set_model->where($where)->find();

        if ($detail) {
            $this->check_admin_auth($detail);
        }

        if ($this->isPost()) {
            $data = [];
            $data['site_id'] = $_W['site']['id'];
            $data['module'] = $module;
            $data['setting'] = json_encode($_GPC['data']);
            if ($detail) {
                $res = $module_set_model->where($where)->update($data);
            } else {
                $res = $module_set_model->insert($data, true);
            }

            $this->zbn_msg("操作成功");
        } else {
            $this->view->config([
                'view_path' => strtolower(APP_PATH . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR),
            ]);
            $this->view->config = json_decode($detail['setting'], true);// $detail;
            return $this->view->fetch();
        }
    }
}