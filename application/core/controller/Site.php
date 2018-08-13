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
use app\common\model\Roots;
use app\common\model\Sites;
use app\common\model\Models;
use app\common\util\Tree2;
use think\Config;
use think\Db;
use think\Session;

class Site extends AdminBase
{
    /**
     * @param int $site_id
     * @return mixed
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @internal param $site_id
     */

    public function site_config($site_id = 0)
    {
        global $_GPC, $_W;
        //todo verify auth
        $where = ['site_id' => $site_id ? $site_id : $_W['site']['id']];
        $detail = Db::name('sites_config')->where($where)->find();

        if ($detail) {
            $this->check_admin_auth($detail);
        }

        if ($this->isPost()) {
            $data['site_id'] = $_W['site']['id'];
            $data['config'] = json_encode($_GPC);

            $site = $this->site;
            unset($site['config']);
            $site->site_name = $_GPC['site_name'];
            $site->save();

            if ($detail) {
                $res = Db::name('sites_config')->where($where)->update($data);
            } else {
                $res = Db::name('sites_config')->insert($data, true);
            }
            if ($_GPC['webim']['private_key']) {
                $res = file_write("upload_file/webim/private_keys/" . $_W['site']['id']. ".key", $_GPC['webim']['private_key']);
            }

            if ($res) {
                $this->zbn_msg("操作成功", 1, 'true', 1000, "''", "'reload_page()'");
            }
        } else {
            if (!$_W['site']['id'] || !$_W['site']) {
                $this->zbn_msg("系统错误");
            } else {
                $this->view->config = mhcms_json_decode($detail['config']);
                return $this->view->fetch();
            }
        }
    }

    /**
     * 全局配置
     * @return mixed
     * @throws \Exception
     */
    public function global_config()
    {
        global $_W, $_GPC;

        if ($this->isPost()) {
            $data['mhcms_config'] = $_GPC;
            self::write_config('mhcms_' . $this->root['id'], $data, true);
            $this->zbn_msg("操作成功");
        } else {

            $this->view->config = $this->global_config;
            return $this->view->fetch();
        }
    }

}