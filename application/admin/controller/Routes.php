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
use app\common\util\forms\input;
use think\Db;

class Routes extends AdminBase
{
    public function index()
    {
        $where =$this->map_fenzhan([]);
        $list = Db::name('routes')->where($where)->order("listorder asc")->select();
        $this->view->list = $list;
        return $this->view->fetch();
    }

    /**
     * @return mixed
     */
    public function create()
    {
        global $_W, $_GPC;
        $content_model_id = "routes";
        $model = set_model($content_model_id);
        $model_info = $model->model_info;
        if ($this->isPost()) {
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
            $this->view->field_list = $model_info->get_admin_publish_fields([]);
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        global $_W, $_GPC;
        $model = set_model('routes');
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }


            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, []);
            $detail['data'] = mhcms_json_decode($detail['data']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        $route_id = (int) $id;
        $data = \app\common\model\Routes::get($route_id);
        $this->check_admin_auth($data);
        $data->delete();
        $data_ret['msg'] = "finished!";
        $data_ret['code'] = 1;
        self::cache_route();
        return $data_ret;
    }
    //
    public static function cache_route()
    {
        global $_W;
        if (!is_dir(CONF_PATH . $_W['root']['root_domain'] . DS)) {
            mkdir(CONF_PATH . $_W['root']['root_domain'] . DS);
        }
        $path = CONF_PATH . $_W['root']['root_domain'] . DS . "route.php";
        $map = ['root_id' => $_W['root']['id']];
        $list = Db::name('routes')->where($map)->order("listorder desc")->select();
        if ($list) {
            route_to_file($path, $list);
        }
    }

    public function gen(){
        self::cache_route();
        $ret['code'] = 1;
        $ret['msg'] = "操作成功！";
        return $ret;

    }
}