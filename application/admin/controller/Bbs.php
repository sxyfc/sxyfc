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
use app\common\model\Models;
use app\common\model\Users;
use think\Cache;
use think\Db;

class Bbs extends AdminBase
{
    private $table_name = "bbs";
    private $reply_table_name = "bbs_reply_log";

    public function index()
    {
        global $_W;
        $content_model_id = $this->table_name;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $this->view->lists = $model->where($where)->order("id desc")->paginate(config('list_rows'));
        $this->view->field_list = $model_info->get_user_publish_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        $this->view->user_id = $this->user['id'];
        return $this->view->fetch();
    }

    public function create()
    {
        global $_W, $_GPC;
        $content_model_id = $this->table_name;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['user_id'] = $this->user['id'];
            $base_info['create_time'] = date("Y-m-d H:i:s");
            $base_info['create_ip'] = $this->request->ip();
            $base_info['last_time'] = date("Y-m-d H:i:s");
            $base_info['last_ip'] = $this->request->ip();
            $base_info['zan'] = 0;

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_user_publish_fields([], ['user_id','create_time','create_ip','last_time','last_ip','zan']);
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
        $model = set_model($this->table_name);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $detail = $model->where($where)->find();

        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['last_time'] = date("Y-m-d H:i:s");
            $base_info['last_ip'] = $this->request->ip();

            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_user_publish_fields($detail, ['user_id','create_time','create_ip','last_time','last_ip','zan']);
            $detail['data'] = mhcms_json_decode($detail['data']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->table_name);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $detail = $model->where($where)->find();
        if ($detail) {
            $model->where($where)->delete();
        }

        return [
            'code' => 1,
            'msg' => "ok",
            'javascript' => "reload_page()"
        ];
    }

    public function reply_index($id = 0)
    {
        global $_W, $_GPC;
        $model_info = set_model($this->reply_table_name);
        $id = (int)$id;
        $pid = (int)input('param.pid', 0);
        $where['bbs_id'] = $id;
        $where['pid'] = $pid;
        if ($this->isPost()) {
            $count = $model_info->where($where)->count();

            $base_info['content'] = trim(input('param.data.content', ' ', 'htmlspecialchars'));
            $base_info['bbs_id'] = $id;
            $base_info['pid'] = $pid;
            $base_info['user_id'] = $this->user['id'];
            $base_info['create_time'] = date("Y-m-d H:i:s");
            $base_info['create_ip'] = $this->request->ip();
            $base_info['rank'] = $count + 1;
            $base_info['level'] = ($pid == 0) ? 1 : 2;
            $base_info['zan'] = 0;

            $res = $model_info->insert($base_info);
            if ($res) {
                return $this->zbn_msg('成功', 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg('失败', 2);
            }

        } else {
            if ($pid) {
                $note = $model_info->where(['id'=>$pid])->find();
                if ($note['user_id']) {
                    $user = Users::get($note['user_id']);
                    $note['user_name'] = $user->nickname;
                }
            } else {
                $note = set_model($this->table_name)->where(['id'=>$id])->find();
                if ($note['user_id']) {
                    $user = Users::get($note['user_id']);
                    $note['user_name'] = $user->nickname;
                }
            }
            $this->view->note = $note;
            $this->view->lists = $model_info->alias('a')->join(config("database.prefix").'users b', 'a.user_id = b.id', 'INNER')->where($where)->order('id')->field('a.*,b.nickname user_name')->paginate(config('list_rows'));
            return $this->view->fetch();
        }
    }
}