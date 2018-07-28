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
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Configs;

class AdminSetting extends AdminBase
{
    public function edit($module)
    {
        $where['module'] = $module;
        $where['site_id'] = $this->site_id;

        $detail = Configs::get($where);
        if(!$detail){
            $insert = [];
            $insert = $where;
            $detail = Configs::create($insert);
        }
        $this->check_admin_auth($detail);
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

            if ($detail->save($config, $where)) {
                $detail->fetchAll(null, true);
                $this->zbn_msg("操作成功", 1);
            } else {
                $detail->fetchAll(null, true);
                $this->zbn_msg("失败", 2);
            }
        } else {
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

}