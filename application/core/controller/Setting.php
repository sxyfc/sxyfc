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
use app\common\model\Configs;
use app\common\model\Sites;
use app\common\util\forms\input;
use think\Db;
use think\View;

class Setting extends AdminBase
{
    public $config_model, $key;

    /**
     *
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->config_model = new Configs();
    }

    /**
     * @return mixed
     */
    public function index($bind_root)
    {

        //顶级全局配置
        if(!$bind_root){
            $where['site_id'] = 0;
            $where['root_id'] = 0;
        }

        else{
            $where['root_id'] = $GLOBALS['root_id'];
            if($this->site['site_domain'] == "www"){

            }else{
                $where['site_id'] = $this->site['site_id'];
            }

        }
        $list = Configs::All($where);
        //test(Sites::get_config(true));
        $this->view->assign('list', $list);
        $this->mapping['bind_root'] = $bind_root;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    /**
     * @return mixed
     */
    public function create($bind_root)
    {
        if ($this->request->isPost()) {
            $this->key = input('param.config_name', "", "htmlspecialchars");
            if (empty($this->key)) {
                $this->error("错误的参数！");
            }

            $config['config_name'] = $this->key;
            if ($bind_root == 1) {
                $config['root_id'] = $GLOBALS['root_id'];
                /**
                 * if it is a branch site
                 */
                $config['site_id'] = $GLOBALS['site_id'];
            } else {
                $config['root_id'] = 0;
            }
            $config_data = [];
            if ($this->config_model->get($config)) {
                $this->zbn_msg("exist config name", 2);
            } else {
                $config_keys = Input('param.keys/a');
                $config_datas = Input('param.datas/a');
                if ($config_datas) {
                    foreach ($config_keys as $v) {
                        if (isset($config_datas[$v])) {
                            $config_data[$v] = $config_datas[$v];
                        }
                    }
                }
                $config['config_description'] = Input('param.config_description');
                $config['config_data'] = $config_data;
                $res = $this->config_model->create($config);
            }
            if ($res) {
                $this->zbn_msg("操作成功", 1);
            } else {
                $this->zbn_msg("失败", 2);
            }
        } else {
            return $this->view->fetch();
        }
    }

    /**Config for this site
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $id = (int)$id;
        $detail = Configs::get($id);
        $this->check_admin_auth($detail);
        $where = ['config_id' => $id];
        $config_data = [];
        if ($detail && $this->isPost()) {
            $config_keys = Input('param.keys/a');
            if (empty($config_keys)) {
                $this->zbn_msg("没有数据的配置", 2);
            }
            $config_datas = Input('param.datas/a');
            $config['is_core'] = Input('param.is_core', 0, 'intval');
            //$data = Input('param.' . $this->key . "/a");
            //prepare the data
            $config['config_description'] = Input('param.config_description');
            foreach ($config_keys as $v) {
                if (isset($config_datas[$v])) {
                    $config_data[$v] = $config_datas[$v];
                }
            }
            $config['config_data'] = $config_data;
            $is_global = input('param.is_global', 0, 'intval');
            if ($this->admin_id == 1 && $is_global == 1) {
                $config['site_id'] = 0;
                $config['root_id'] = 0;
            } else {
                $config['site_id'] = $GLOBALS['cache_site_id'];
                $config['root_id'] = $GLOBALS['root_id'];
            }

            if ($this->config_model->save($config, $where)) {
                $this->config_model->fetchAll(null, true);
                $this->zbn_msg("操作成功", 1);
            } else {
                $this->config_model->fetchAll(null, true);
                $this->zbn_msg("失败", 2);
            }
        } else {
            $this->view->assign("detail", $detail);
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $detail = Configs::get($id);
        if ($detail['is_core'] != 0) {
            $data['code'] = 2;
            $data['msg'] = "“this is a core component data that can not be deleted!”";
        } else {
            if ($this->config_model->destroy($id)) {
                $data['code'] = 1;
                $data['msg'] = "“操作完成”";
            } else {
                $data['code'] = 2;
                $data['msg'] = "“操作失败”";
            }
        }
        return $data;
    }

    public function theme()
    {

        if($this->user->id != 1){
            $area_id = $this->user->linkage;
            if(empty($area_id)){
                $this->error("无权操作");
            }
        }else{
            $area_id = 0;
        }

        if($this->isPost()){

            $data  = input('param.data/a');


            $data['linkage'] = $area_id;
            Db::name('theme')->update($data);
            $this->zbn_msg("操作成功!");
        }else{

            $detail = Db::name("theme")->find($area_id);
            if(!$detail){
                Db::name("theme")->insert(['linkage' => $area_id]);
            }
            $this->view->detail = $detail;
            return $this->view->fetch();
        }

    }
}