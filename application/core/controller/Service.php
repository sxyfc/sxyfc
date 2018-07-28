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
use app\common\model\Users;
use app\common\util\forms\input;
use app\core\util\mhcms_index\MhcmsIndex;
use app\core\util\MhcmsTxMap;
use think\Db;

class Service extends AdminBase
{

    public function update()
    {
        $sql = "ALTER TABLE `admin_mhcms`.`mhcms_sites_wechat_material`   
  CHANGE `material_type` `material_type` ENUM('video','news','images','text','music','voice') CHARSET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '视频 新闻 图片 链接 音频';";

    }

    /**
     * secure issue |
     * todo: fields property hidden in service
     * @param $model_id
     * @param string $f
     * @return \think\response\Json|\think\response\Jsonp
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list_item($model_id, $f = 'json', $limit = 10, $page = 1)
    {
        global $_W, $_GPC;
        $model = set_model($model_id);
        $page = max((int)$page, 1);

        $user_id = (int)$_GPC['user_id'];

        $site_id = $_W['site']['id'];

        $q = $_GPC['q'];

        $limit = (int)$limit;
        $limit = min(10, $limit);


        $where = [];
        if (Models::field_exits('site_id', $model_id)) {
            $where['site_id'] = $site_id;
        }
        if ($user_id && Models::field_exits('user_id', $model_id)) {
            $where['user_id'] = $user_id;
        }

        if (Models::field_exits('status', $model_id)) {
            $where['status'] = "99";
        }

        //设置了搜索域的 必须提供关键字才可以进行数据操作
        if ($model->model_info['name_key'] && !$q) {
            $where[$model->model_info['name_key']] = ['=', "0"];
        } else {
            $where[$model->model_info['name_key']] = ['like', "%$q%"];
        }

        $lists = $model->where($where)->page($page)->order('id desc')->page($page)->limit($limit)->select();
        foreach ($lists as $k => $item) {
            $item = Models::get_item($item['id'], $model_id);
            if ($where['user_id']) {
                $user = Users::get(['user_id' => $item['user_id']]);
                if ($user) {
                    $item['user_info'] = $user->toArray();
                }
            }
            $item['create_time'] = format_date($item['create_time']);
            $lists[$k] = $item;
        }

        $ret['code'] = 1;
        $ret['success'] = true;
        switch ($f) {
            case 'json' :
                $ret['data'] = $lists->toArray();
                return json($ret);
                break;
            case 'jsonp' :
                $ret['data'] = $lists->toArray();
                return jsonp($ret);
                break;
            case 'sematic_drop_down' :
                $results = $lists->toArray();
                $_res = [];
                $_res['name'] = "请选择或者搜索";
                $_res['value'] = 0;
                $_res['text'] = "请选择或者搜索";

                $new_res = [];
                $new_res[] = $_res;
                foreach ($results as $result) {
                    $_res = [];
                    $_res['name'] = $result[$model->model_info['name_key']];
                    $_res['value'] = $result[$model->model_info['id_key']];
                    $_res['text'] = $result[$model->model_info['name_key']];
                    $new_res[] = $_res;
                }
                // Expected server response
                $ret['results'] = $new_res;
                return json($ret);
                break;
        }
    }


    /**
     * secure issue |
     * todo: fields property hidden in service
     * @param $model_id
     * @param $target_field
     * @param $from_field
     * @param string $f
     * @param int $limit
     * @param int $page
     * @return \think\response\Json|\think\response\Jsonp
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function linkage_list_item($model_id, $target_field, $from_field, $id_key_val, $f = 'json', $limit = 10, $page = 1)
    {
        global $_W, $_GPC;
        $model = set_model($model_id);
        $fields = $model->model_info->setting['fields'];
        $target_field = $fields[$target_field];
        $from_field = $fields[$from_field];
        if (!$from_field || !$target_field || !$id_key_val) {
            return [];
        }

        $target_model_id = $target_field['node_field_data_source_config'];

        $where = [];
        $where[$from_field['target_foreign_key']] = $id_key_val;


        if (Models::field_exits('site_id', $target_model_id)) {
            $where['site_id'] = $_W['site']['id'];
        }
        if (Models::field_exits('status', $target_model_id)) {
            $where['status'] = "99";
        }
        $target_model = set_model($target_model_id);
        $lists = set_model($target_model_id)->where($where)->page($page)->order('id desc')->select();
        foreach ($lists as $k => $item) {

            $item['id'] = $item[$target_model->model_info->id_key];
            $item['name'] = $item[$target_model->model_info->name_key];
            $lists[$k] = $item;
        }
//"id":1,"department_name":"市行政环保","site_id":1,"type_id":1,"name":"市行政环保"
        $default = [
            'id' => "",
            'name' => '请选择'
        ];
        $ret['code'] = 1;
        $ret['success'] = true;
        switch ($f) {
            case 'json' :
                $ret['data'] = $lists->toArray();

                array_unshift($ret['data'], $default);
                return json($ret);
                break;
            case 'jsonp' :
                $ret['data'] = $lists->toArray();
                return jsonp($ret);
                break;
            case 'sematic_drop_down' :
                $results = $lists->toArray();
                $_res = [];
                $_res['name'] = "请选择或者搜索";
                $_res['value'] = 0;
                $_res['text'] = "请选择或者搜索";

                $new_res = [];
                $new_res[] = $_res;
                foreach ($results as $result) {
                    $_res = [];
                    $_res['name'] = $result[$model->model_info['name_key']];
                    $_res['value'] = $result[$model->model_info['id_key']];
                    $_res['text'] = $result[$model->model_info['name_key']];
                    $new_res[] = $_res;
                }
                // Expected server response
                $ret['results'] = $new_res;
                return json($ret);
                break;
        }
    }

    /**
     * 字段数据源
     * @param $model_id
     * @param $field_name
     * @return \think\response\Json|\think\response\Jsonp
     * @throws \think\exception\DbException
     *
     * @throws \think\Exception
     */
    public function field_data($model_id, $field_name, $limit = 10, $page = 1)
    {
        /** @var Models $model_info */
        $model = set_model($model_id);
        $model_info = $model->model_info;
        $field = $model_info['setting']['fields'][$field_name];
        $model_id = $field['node_field_data_source_config'];
        return $this->list_item($model_id, 'json', $limit, $page);
    }


    public function re_index($model_id, $page, $limit)
    {
        //todo 删除索引
        $model = set_model($model_id);
        if (Models::field_exits('status', $model_id)) {
            $where['status'] = 99;
        }
        $total_count = $model->where($where)->count();
        $total_pages = ceil($total_count / $limit);
        $percent = $page / $total_pages * 100;
        $items = $model->where($where)->order('id desc')->page($page)->limit($limit)->select();

        foreach ($items as $item) {
            MhcmsIndex::delete($model_id, $item['id']);
            MhcmsIndex::create($item['id'], $model_id, $model->model_info['module']);
        }

        return ['code' => 1, 'percent' => $percent];
    }

}