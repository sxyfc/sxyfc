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
namespace app\attachment\controller;

use app\attachment\storges\StorgeEngine;
use app\common\controller\AdminBase;
use app\common\model\AttachConfig;
use app\common\model\Models;
use think\Db;

class AdminStorge extends AdminBase
{
    public $attach_config = "attach_config";
    public $attach_config_site = "attach_config_site";

    public function config($id)
    {
        global $_W, $_GPC;
        $where = [];
        $where['storge_id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $storge_config = set_model($this->attach_config_site)->where($where)->find();
        $storge = AttachConfig::get(['id' => $id]);


        //$storge = AttachConfig::get(['id' => $id]);

        if ($this->isPost()) {
            if ($storge_config) {
                $update_data = [];
                $update_data['config'] = json_encode($_GPC[$storge['attach_sign']]);
                set_model($this->attach_config_site)->where($where)->update($update_data);

            } else {
                $insert_data = [];
                $insert_data['config'] = json_encode($_GPC[$storge['attach_sign']]);
                $insert_data['site_id'] = $_W['site']['id'];
                $insert_data['storge_id'] = $id;
                set_model($this->attach_config_site)->insert($insert_data);
            }

            $storge->save();
            $this->zbn_msg("操作成功！");
        } else {
            if ($storge) {
                $this->view->config = mhcms_json_decode($storge_config['config']);
            }
            $this->view->attach_sign = $storge['attach_sign'];
            return $this->view->fetch(strtolower($storge['attach_sign']));
        }
    }

    public function test($id)
    {
        global $_W , $_GPC;
        $storge = AttachConfig::get(['id' => $id]);
        $storage_config = set_model($this->attach_config_site)->where(['storge_id' => $id , 'site_id' => $_W['site']['id']])->find();
        $class_name = "\\app\\attachment\\storges\\" . $storge['attach_sign'];
        /** @var StorgeEngine $storge_engine */
        if($storage_config){
            $storge_engine = new $class_name(mhcms_json_decode($storage_config['config']) );
            return $storge_engine->test();
        }else{
            $ret = [
                'code' => 2,
                'msg' => '请先点击左侧配置 ， 配置好再来测试！'
            ];
            return $ret;
        }

    }

    public function set_default($id)
    {
        global $_W , $_GPC;
        $where_update['site_id'] = $_W['site']['id'];
        set_model($this->attach_config_site)->where($where_update)->update(['default' => 0]);
        $where_update['storge_id'] = $id;
        set_model($this->attach_config_site)->where($where_update)->update(['default' => 1]);
        $ret = [
            'code' => 1,
            'msg' => '操作成功！'
        ];
        return $ret;
    }

    public function init()
    {
        $model = set_model($this->attach_config);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //fields
        $this->view->field_list = $model_info->get_admin_publish_fields();
        //model_info
        $this->view->model_info = $model_info;
        $where = [];
        //data list
        $lists = Db::name($model_info['table_name'])->where($where)->paginate();
        $this->view->lists = $lists;
        return $this->view->fetch();
    }

    public function add()
    {
        $model_info = [];
        $model_info = Models::get(['id' => $this->attach_config]);
        if ($this->isPost() && $model_info) {
            $base_info = input('post.data/a');//get the base info
            //自动提取缩略图
            if (!isset($base_info['thumb']) && !empty($base_info['content'])) {
                $content = stripslashes($base_info['content']);
                $auto_thumb_no = 1 - 1;
                if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
                    $thumb_url = str_replace("&quot;", "", $matches[3][$auto_thumb_no]);
                }
                if (isset($thumb_url)) {
                    $file = Db::name('file')->where(['url' => $thumb_url])->find();//File::get(['url'=>$base_info['thumb']]);
                    $base_info['thumb'][] = $file['file_id'];
                }
            }
            //截取简介
            if (!isset($base_info['description']) && !empty($base_info['content'])) {
                $content = stripslashes($base_info['content']);
                $introcude_length = intval(255);
                $base_info['description'] = str_cut(str_replace(array("\r\n", "\t"), '', strip_tags($content)), $introcude_length);
            }
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            //todo auth
            if ($this->admin_id != 1 && ($model_info['root_id'] != 0 && $model_info['root_id'] != $this->current_admin['root_id'])) {
                test($this->admin_role_id);
                $this->error("没有授权的操作 , 错误的根域名！");
            }
            $this->form_factory->model_id = $model_info['id'];
            $new_field_list = $model_info['setting']['fields'];
            foreach ($new_field_list as $k => $field) {
                if (empty($field['node_field_mode']) || !$field['node_field_asform'] || $field['node_field_disabled'] == 1) {
                    unset($new_field_list[$k]);
                    continue;
                }
                $field['form_str'] = $this->form_factory->config_model_form($field);
                $new_field_list[$k] = $field;
            }
            $this->view->assign('list', $new_field_list);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function edit($id)
    {
        $id = (int)$id;
        $model_info = Models::get(['id' => $this->attach_config]);
        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        if ($this->isPost() && $model_info) {
            $data = input('param.data/a');
            // todo  process data input
            Db::name($model_info['table_name'])->where($where)->update($data);
            $this->zbn_msg("ok");
        } else {
            //todo auth
            if ($this->admin_id != 1 && ($model_info['root_id'] != 0 && $model_info['root_id'] != $this->current_admin['root_id'])) {
                test($this->admin_role_id);
                $this->error("没有授权的操作 , 错误的根域名！");
            }
            $this->form_factory->model_id = $model_info['id'];
            $new_field_list = $model_info['setting']['fields'];
            foreach ($new_field_list as $k => $field) {
                if (empty($field['node_field_mode']) || !$field['node_field_asform'] || $field['node_field_disabled'] == 1) {
                    unset($new_field_list[$k]);
                    continue;
                }
                if (isset($detail[$field['field_name']])) {
                    $field['node_field_default_value'] = $detail[$field['field_name']];
                }
                $field['form_str'] = $this->form_factory->config_model_form($field);
                $new_field_list[$k] = $field;
            }
            $this->view->assign('list', $new_field_list);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }
}