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
use think\Db;

class AdminSetting extends AdminBase
{
    public function set($key)
    {
        global $_GPC;
        $where = ['key' => $key];
        $detail = set_model('setting')->where($where)->find();
        if ($this->isPost()) {
            $data = $_GPC['data'];


            $_data['value'] = json_encode($data);
            $_data['key'] = $key;
            if ($detail) {
                set_model('setting')->where($where)->update($_data);
            } else {
                set_model('setting')->insert($_data);
            }
            $this->zbn_msg("ok");

        } else {
            if ($detail) {
                $this->view->config = mhcms_json_decode($detail['value']);
            }
            return $this->view->fetch($key);
        }
    }
}