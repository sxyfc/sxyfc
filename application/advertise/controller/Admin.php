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
namespace app\advertise\controller;

use app\advertise\model\Advertise;
use app\common\controller\AdminBase;
use app\common\model\Models;
use think\Db;

class Admin extends AdminBase
{
    public $adgroup_model_id = "adgroup", $advertise_model_id = "advertise";

    /**
     * @return mixed
     * @throws \think\exception\DbException
     * @internal param string $node_type
     */
    public function add()
    {
        $model = set_model($this->adgroup_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        if ($this->isPost() && $model_info) {
            $base_info = input('post.data/a');//get the base info

            if (Models::field_exits('site_id', $model_info['id'])) {
                $base_info['site_id'] = $this->site['id'];
            }

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {

            $this->view->list = $model_info->get_admin_publish_fields([], true);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        $id = (int)$id;
        $model = set_model($this->adgroup_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        $this->check_admin_auth($detail);
        if ($this->isPost() && $model_info) {
            $data = input('param.data/a');
            // todo  process data input
            Db::name($model_info['table_name'])->where($where)->update($data);
            $this->zbn_msg("ok");
        } else {
            //todo auth
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }


    /**
     * @param $id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        set_model($this->adgroup_model_id)->where(['id' => $id])->delete();
        set_model($this->advertise_model_id)->where(['group_id' => $id])->delete();
        return ['code' => 0, 'msg' => '删除成功'];
    }


    /**
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->view->filter_info = Models::gen_admin_filter("adgroup", $this->menu_id);
        $where = $this->view->filter_info['where'];
        $model = set_model($this->adgroup_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        //fields
        $this->view->field_list = $model_info->get_admin_column_fields([], false);
        //model_info
        $this->view->assign('model_info', $model_info);

        if (Models::field_exits('site_id', $model_info['id'])) {
            $where['site_id'] = $this->site['id'];
        }

        //data list
        $lists = Db::name($model_info['table_name'])->where($where)->paginate();
        $this->view->mapping = $this->mapping;
        $this->view->lists = $lists;
        return $this->view->fetch();
    }
}