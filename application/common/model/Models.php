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
namespace app\common\model;

use app\common\util\forms\Field;
use app\common\util\forms\FormFactory;
use app\core\util\mhcms_index\MhcmsIndex;
use app\sms\model\Notice;
use think\Db;
use think\Log;
use think\Request;
use think\Validate;

class Models extends Common
{

    /** @var self $model_info */
    public $model_info;

    public $module;

    protected $type = [
        'setting' => 'json',
    ];

    public function get_index_fields()
    {
        $ret = [];
        $new_field_list = $this->setting['fields'];
        foreach ($new_field_list as $k => $field) {
            if ($field['is_index']) {
                $ret[$k] = $field;
            }
        }
        return $ret;
    }

    public static function get_item($id, $model_id, $_where = [], $format = true)
    {
        global $_W;

        $model = set_model($model_id);
        $model_info = $model->model_info;
        $form_facroty = new FormFactory();
        $form_facroty->model_id = $model_info['id'];
        //$pk = Db::name($model_info['table_name'])->getPk();
        $pk = $model_info['id_key'] ? $model_info['id_key'] : "id";

        $where[$pk] = $id;
        if (is_array($_where) && $_where) {
            $where = array_merge($where, $_where);
        }
        if (Models::field_exits("site_id", $model_id) && !$_W['super_power']) {
            $where["site_id"] = $_W["site_id"];
        }

        $item = $old_data = Db::name($model_info['table_name'])->where($where)->find();

        if (!$old_data) {
            return [];

        }
        //demantic bind module
        if (Models::field_exits('model_id', $model_id)) {
            $bind_module = set_model($item['model_id']);
            $model_info->module = $bind_module->model_info['module'];
        }


        if ($old_data && $format) {
            $item['old_data'] = $old_data;
            foreach ($model_info['setting']['fields'] as $field) {
                $field['module'] = $model_info->module;

                if (isset($field['field_name'])) {
                    switch ($field['field_name']) {
                        case "user_id" :
                            $item['user'] = Users::get(['id' => $item[$field['field_name']]]);
                            break;
                        default:
                            $item[$field['field_name']] = $form_facroty->process_model_output($field, $item[$field['field_name']], $old_data);
                    }
                }


            }
            //todo 是否启用点击系统

            if (self::field_exits("views", $model_id)) {
                $item['hits'] = Hits::get_hit($id, $model_id);

            }

            return $item;
        }

        return false;
    }


    public static function get_item_by($where, $model_id, $format = true)
    {
        global $_W;

        $model = set_model($model_id);
        $model_info = $model->model_info;
        $form_facroty = new FormFactory();
        $form_facroty->model_id = $model_info['id'];

        if (Models::field_exits("site_id", $model_id)) {
            $where["site_id"] = $_W["site_id"];
        }

        $item = $old_data = $model->where($where)->find();

        //demantic bind module
        if (Models::field_exits('model_id', $model_id)) {
            $bind_module = set_model($item['model_id']);
            $model_info->module = $bind_module->model_info['module'];
        }


        if ($old_data && $format) {
            $item['old_data'] = $old_data;
            foreach ($model_info['setting']['fields'] as $field) {
                $field['module'] = $model_info->module;
                switch ($field['field_name']) {
                    case "user_id" :
                        $item['user'] = Users::get(['id' => $item[$field['field_name']]]);
                        break;
                    default:
                        $item[$field['field_name']] = $form_facroty->process_model_output($field, $item[$field['field_name']], $old_data);
                }

            }
            //todo 是否启用点击系统

            if (self::field_exits("views", $model_id)) {
                $item['hits'] = Hits::get_hit($item['id'], $model_id);
            }

            return $item;
        }

        return false;
    }


    public static function delete_item($id, $model_id, $user_id = 0)
    {
        global $_W;
        $where['site_id'] = $_W['site']['id'];
        $where['id'] = (int)$id;

        if (self::field_exits('user_id', $model_id)) {
            //前台删除 必须设置会员字段
            if (!defined("IN_ADMIN")) {
                if (empty($user_id)) {
                    return false;
                } else {
                    $where['user_id'] = (int)$user_id;
                }
            }
        } else {
            //没有会员字段 禁止前台删除
            if (!defined("IN_ADMIN")) {
                return false;
            }
        }
        $model = set_model($model_id);
        $detail = $model->where($where)->find();
        //check if the model is indexed

        if (!$detail) {
            return false;
        } else {
            $indexed = $model->model_info->is_index;
            $model->where($where)->delete();
        }


        if ($indexed) {
            $where = [];
            $where['model_id'] = $model->model_info['id'];
            $where['item_id'] = (int)$id;
            //todo delete the index
            $index = set_model("index")->where($where)->find();

            set_model("index")->where($where)->delete();
            set_model("index_data")->where(['id' => $index['id']])->delete();
        }

        // delete hits

        if (Models::field_exits('views', $model_id)) {
            set_model("hits")->where(['item_id' => (int)$id, 'model_id' => $model->model_info['id']])->delete();
        }

        //todo delete files


        return true;

    }

    /**
     * @param $where
     * @param $model_id
     * @param bool $format
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetch_one($where, $model_id, $format = false)
    {
        $form_facroty = new FormFactory();
        $model = set_model($model_id);
        $model_info = $model->model_info;
        //$pk = Db::name($model_info['table_name'])->getPk();
        $item = $model->where($where)->find();
        if ($format) {
            foreach ($model_info['setting']['fields'] as $field) {
                $item[$field['field_name']] = $form_facroty->process_model_output($field, $item[$field['field_name']]);
            }
        }
        return $item;
    }

    /**
     * @param $where
     * @param $model_id
     * @param bool $render
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function get_all($where, $model_id, $render = false)
    {
        $form_facroty = new FormFactory();
        $model = self::get(['id' => $model_id]);
        $items = Db::name($model['table_name'])->where($where)->select();
        if ($render) {
            foreach ($items as $k => $item) {
                foreach ($model['setting']['fields'] as $field) {
                    $item[$field['field_name']] = $form_facroty->process_model_output($field, $item[$field['field_name']]);
                }
                $items[$k] = $item;
            }
        }
        return $items;
    }

    /**
     * @param $where
     * @param $model_id
     * @param bool $render
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function list_item($where, $model_id, $render = true)
    {
        $model = set_model($model_id);// self::get(['id' => $model_id]);
        $model_info = $model->model_info;
        $items = $model->where($where)->field($model_info['id_key'])->select()->toArray();

        if ($render) {
            foreach ($items as $k => &$item) {
                if (!$item[$model_info['id_key']]) {
                    die("$model_id idkey");
                }
                $item = self::get_item($item[$model_info['id_key']], $model_id);
            }
        }
        return $items;
    }

    /**
     * 筛选SQL语句生成
     * @param $model_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function gen_filter_where($model_id)
    {
        //
        global $_W, $_GPC;
        $where = [];
        $model = set_model($model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $search_keys = explode(",", $model_info['search_keys']);
        //字段列表 没有设置则默认为[]
        $new_field_list = is_array($model_info['setting']['fields']) ? $model_info['setting']['fields'] : [];
        foreach ($new_field_list as $k => $field) {
            if (empty($field['node_field_mode']) || $field['node_field_disabled'] == 1) {
                unset($new_field_list[$k]);
                continue;
            }
            //筛选条件
            if ($field['node_field_is_filter'] && $_GPC[$k]) {
                switch ($field['node_field_mode']) {
                    case "layui_checkbox":
                    case "checkbox":
                    case "multiple_select":
                    case "left_2_right":
                        $where[$k] = ['LIKE', "%,{$_GPC[$k]},%"];
                        break;
                    case "layui_radio":
                    case "single_select":
                    case "semantic_ajax_select":
                    case "radio":
                        $where[$k] = ['EQ', $_GPC[$k]];
                        break;
                }
            }
            if (in_array($k, $search_keys) && $_GPC['q']) {
                $where[$k] = ['like', "%{$_GPC['q']}%"];
            }
            $new_field_list[$k] = $field;
        }
        $ret['where'] = $where;
        $ret['fields'] = $new_field_list;
        return $ret;
    }

    /**
     * 生成后台列表筛选依据
     * @param $model_id
     * @param $user_menu_id
     * @param string $route
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function gen_admin_filter($model_id, $user_menu_id, $route = "")
    {
        global $_GPC, $_W;
        $_where = [];
        $title = "";
        $filter_fields = [];
        $model = set_model($model_id);
        /** @var Models $model_info */
        $filter_model_info = $model->model_info;
        $fields = $filter_model_info['setting']['fields'];
        $query_params = [];
        if (!$route) {
            $route = [
                ROUTE_M, Request::instance()->controller(), ROUTE_A
            ];
            $route = join("/", $route);
        }
        foreach ($fields as $k => $field) {
            if (isset($field['disabled']) && $field['disabled']) {
                continue;
            }
            /**
             * 先获取筛选字段
             * 并获取其选项值
             *
             */
            if (isset($field['node_field_is_filter']) && $field['node_field_is_filter']) {
                switch ($field['node_field_data_source_type']) {
                    case "mhcms_options" :
                        $model = set_model($field['node_field_data_source_config']);
                        $model_info = $model->model_info;
                        $where = [];
                        $where['field_name'] = $field['field_name'];
                        $where['model_id'] = $filter_model_info['id'];
                        $where['site_id'] = $_W['site']['id'];
                        $options = $model->where($where)->select();
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $option['name'] = $option[$model_info['name_key']];
                            $option['id'] = $option[$model_info['id_key']];
                            $new_options[$option['id']] = $option;
                        }

                        if (!$field['node_field_pk_key']) {
                            $field['node_field_pk_key'] = $model_info['id_key'];
                        }


                        //options
                        $field['options'] = $new_options;

                        $filter_fields[] = $field;
                        break;
                    case "options" :
                        $model->set_table("options", 1);
                        $where = [];
                        $where['field_name'] = $field['field_name'];
                        $where['model_id'] = $model_id;
                        $options = $model->select($where);
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $new_options[$option['id']] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        if ($_GPC[$field['field_name']]) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $filter_fields[] = $field;
                        break;
                    case "static":
                        $field['field_pk_key'] = 'id';
                        $field['field_name_key'] = 'name';
                        $new_options = [];
                        $common_options = new_better_base::load_sys_class('common_options', NEW_BETTER_CORE_PATH . 'libs' . DIRECTORY_SEPARATOR . 'sys_classes' . DIRECTORY_SEPARATOR . "forms");
                        $options = $common_options->$field['field_mode'](true);
                        foreach ($options as $k => $option) {
                            $_option['id'] = $k;
                            $_option['name'] = $option;
                            $new_options[] = $_option;
                        }
                        $field['options'] = $new_options;
                        if ($_GPC[$field['field_name']]) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $filter_fields[] = $field;
                        break;
                    case "model" :
                        //todo 判断是否为地区
                        /**
                         * if ($field['field_name'] == 'area_id' && $_W['site']['parent_id'] == 0) {
                         * $sub_sites = $model->set_table('sites')->where(['parent_id' => $_W['site']['id']])->select();
                         * $new_options = [];
                         * $model->set_model($field['field_data_source_config']);
                         * foreach ($sub_sites as $sub_site) {
                         * $sub_site[$field['field_name_key']] = $sub_site['site_name'];
                         * $new_options[$sub_site['id']] = $sub_site;
                         * $new_options[$sub_site['id']]['sub_areas'] = $model->where(['site_id' => $sub_site['id']])->select();
                         * }
                         * $field['options'] = $new_options;
                         * } else {}
                         */
                        $_model = set_model($field['node_field_data_source_config']);
                        $_model_info = $_model->model_info;
                        $where = [];

                        if ($field['where']) {
                            $_where_data = explode('$', $field['where']);
                            $where[$_where_data[0]] = [$_where_data[1], parseParam($_where_data[2], $_GPC)];
                        }
                        if (self::field_exits("site_id", $field['node_field_data_source_config'])) {
                            $where['site_id'] = $_W['site']['id'];
                        }
                        $options = $_model->where($where)->select();
                        if ($_model_info['id_key']) {
                            $id_key = $_model_info['id_key'];
                        } else {
                            $id_key = $field['node_field_pk_key'];
                        }
                        if ($_model_info['name_key']) {
                            $name_key = $_model_info['name_key'];
                        } else {
                            $name_key = $field['node_field_name_key'];
                        }
                        if (!$name_key || !$id_key) {
                            var_dump($id_key);
                            test($_model_info['name_key']);
                        }
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $option['id'] = $option[$id_key];
                            $option['name'] = $option[$name_key];
                            $new_options[$option[$id_key]] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        //selected value equal
                        if (isset($_GPC[$field['field_name']])) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $field['node_field_pk_key'] = $id_key;
                        $field['node_field_name_key'] = $name_key;

                        $filter_fields[] = $field;
                        break;
                    case "category":
                        $where = [];
                        $model->set_table("cate");
                        $where['type_id'] = $model_id;
                        $options = $model->select($where);
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $new_options[$option['id']] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        if (isset($_GPC[$field['field_name']])) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $filter_fields[] = $field;
                        break;
                    case "diy_arr":
                        $options = "";
                        //todo diy arr filter
                        $name_key = "name";
                        $id_key = "id";

                        if (!$name_key || !$id_key) {
                            var_dump($id_key);
                            test($name_key);
                        }

                        $rows = explode("\r\n", $field['node_field_data_source_config']);
                        $new_options = [];
                        foreach ($rows as $row) {
                            $row_data = explode("|", $row);
                            $option = [];
                            $option[$id_key] = $row_data[0];
                            $option[$name_key] = $row_data[1];
                            $new_options[$option[$id_key]] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        //selected value equal
                        if ($_GPC[$field['field_name']]) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }

                        $field['node_field_pk_key'] = $id_key;
                        $field['node_field_name_key'] = $name_key;
                        $filter_fields[] = $field;
                        break;
                }
                if (isset($_GPC[$field['field_name']]) && $_GPC[$field['field_name']] !== "") {
                    $query_params[$field['field_name']] = $_GPC[$field['field_name']];
                    $_where[$field['field_name']] = $query_params[$field['field_name']];
                }
            } else {
                unset($fields[$k]);
            }
        }
        ksort($query_params);
        foreach ($filter_fields as $k => $field) {
            //get_current val
            foreach ($field['options'] as $kk => $opt) {
                //设置全部连接
                if (!isset($filter_fields[$k]['href'])) {
                    $copy_params = $query_params;
                    $copy_params['user_menu_id'] = $user_menu_id;
                    //如果参数未被设置 则全部选项选中
                    if (isset($copy_params[$field['field_name']])) {
                    } else {
                        $filter_fields[$k]['class'] = "selected";
                    }
                    unset($copy_params[$field['field_name']]);
                    $filter_fields[$k]['href'] = url($route, $copy_params);
                }
                //设置选项链接 generate the a tag
                $title = "";
                $copy_params = $query_params;
                $copy_params['user_menu_id'] = $user_menu_id;
                if (isset($copy_params[$field['field_name']]) && $copy_params[$field['field_name']] == $opt[$field['node_field_pk_key']]) {
                    //selected
                    $filter_fields[$k]['options'][$kk]['href'] = "JavaScript:void(0)";
                    $filter_fields[$k]['options'][$kk]['class'] = "selected";
                    $title .= $opt[$field['field_name']] . "、";
                } else {
                    //not selected items 生成的URL参数  增加条件
                    $copy_params[$field['field_name']] = $opt[$field['node_field_pk_key']];
                    $filter_fields[$k]['options'][$kk]['href'] = url($route, $copy_params);
                }
            }
        }
        $ret = [
            'where' => $_where,
            'title' => $title,
            'fields' => $filter_fields
        ];
        return $ret;
    }

    public static function gen_user_filter($model_id, $user_menu_id, $route = "")
    {
        global $_GPC, $_W;
        $_where = $filter_fields = $query_params = $_query = [];
        $title = "";
        $model = set_model($model_id);
        $filter_model_info = $model->model_info;
        $fields = $filter_model_info['setting']['fields'];
        if (!$route) {
            $route = [
                ROUTE_M, Request::instance()->controller(), ROUTE_A
            ];
            $route = join("/", $route);
        }
        foreach ($fields as $k => $field) {
            if (isset($field['disabled']) && $field['disabled']) {
                continue;
            }
            /**
             * 先获取筛选字段
             * 并获取其选项值
             */
            if (isset($field['node_field_is_filter']) && $field['node_field_is_filter']) {

                if (!isset($field['node_field_data_source_type'])) {
                    die("模型 $model_id 中无法筛选的字段，请取消字段的筛选：" . $field['field_name']);
                }
                switch ($field['node_field_data_source_type']) {
                    case "mhcms_options" :
                        $model = set_model($field['node_field_data_source_config']);
                        $model_info = $model->model_info;
                        $where = [];
                        $where['field_name'] = $field['field_name'];
                        $where['model_id'] = $filter_model_info['id'];

                        if (Models::field_exits('site_id', $field['node_field_data_source_config'])) {
                            $where['site_id'] = ["IN", [$_W['root']['site_id'], $_W['site']['id']]];
                        }

                        $options = $model->where($where)->select();
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $option['name'] = $option[$model_info['name_key']];
                            $option['id'] = $option[$model_info['id_key']];
                            $new_options[$option['id']] = $option;
                        }

                        if (!$field['node_field_pk_key']) {
                            $field['node_field_pk_key'] = $model_info['id_key'];
                        }


                        //options
                        $field['options'] = $new_options;

                        $filter_fields[] = $field;
                        break;
                    case "model" :
                        //todo 判断是否为地区
                        $_model = set_model($field['node_field_data_source_config']);
                        $_model_info = $_model->model_info;
                        $where = [];
                        if (self::field_exits("site_id", $field['node_field_data_source_config'])) {
                            $where['site_id'] = ["IN", [$_W['root']['site_id'], $_W['site']['id']]];
                        }
                        $options = $_model->where($where)->select();
                        if ($_model_info['id_key']) {
                            $id_key = $_model_info['id_key'];
                        } else {
                            $id_key = $field['node_field_pk_key'];
                        }
                        if ($_model_info['name_key']) {
                            $name_key = $_model_info['name_key'];
                        } else {
                            $name_key = $field['node_field_name_key'];
                        }
                        if (!$name_key || !$id_key) {
                            var_dump($id_key);
                            test($name_key);
                        }
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $option['id'] = $option[$id_key];
                            $option['name'] = $option[$name_key];
                            $new_options[$option[$id_key]] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        //selected value equal
                        if (isset($_GPC[$field['field_name']])) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $field['node_field_pk_key'] = $id_key;
                        $field['node_field_name_key'] = $name_key;
                        $filter_fields[] = $field;
                        break;
                    case "category":
                        $where = [];
                        $model->set_table("cate");
                        $where['type_id'] = $model_id;
                        $options = $model->select($where);
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $new_options[$option['id']] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        if (isset($_GPC[$field['field_name']])) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $filter_fields[] = $field;
                        break;
                    case "diy_arr":


                        $options = "";
                        //todo diy arr filter
                        $name_key = "name";
                        $id_key = "id";

                        if (!$name_key || !$id_key) {
                            var_dump($id_key);
                            test($name_key);
                        }

                        $rows = explode("\r\n", $field['node_field_data_source_config']);
                        $new_options = [];
                        foreach ($rows as $row) {
                            $row_data = explode("|", $row);
                            $option = [];
                            $option[$id_key] = $row_data[0];
                            $option[$name_key] = $row_data[1];
                            $new_options[$option[$id_key]] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        //selected value equal
                        if ($_GPC[$field['field_name']]) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }

                        $field['node_field_pk_key'] = $id_key;
                        $field['node_field_name_key'] = $name_key;
                        $filter_fields[] = $field;
                        break;
                }
                if (isset($_GPC[$field['field_name']])) {
                    $query_params[$field['field_name']] = $_GPC[$field['field_name']];


                    $field_obj = new Field($field);
                    switch ($field_obj->node_field_mode) {

                        case "layui_checkbox":
                            $is_multi = 1;
                            break;
                        default :
                            $is_multi = 0;
                    }
                    $_query[$field['field_name']] = $query_params[$field['field_name']];
                    if ($is_multi) {
                        $_where[$field['field_name']] = ['like', '%,' . $query_params[$field['field_name']] . ',%'];
                    } else {
                        $_where[$field['field_name']] = $query_params[$field['field_name']];
                    }
                }
            } else {
                unset($fields[$k]);
            }
        }
        ksort($query_params);
        foreach ($filter_fields as $k => &$field) {
            //get_current val
            foreach ($field['options'] as $kk => $opt) {
                //设置全部连接
                if (!isset($field['href'])) {
                    $copy_params = $query_params;
                    $copy_params['user_menu_id'] = $user_menu_id;
                    //如果参数未被设置 则全部选项选中
                    if (isset($copy_params[$field['field_name']])) {
                    } else {
                        $field['class'] = "selected";
                    }
                    unset($copy_params[$field['field_name']]);
                    $field['href'] = url($route, $copy_params);
                }


                //设置选项链接 generate the a tag
                $copy_params = $query_params;
                $copy_params['user_menu_id'] = $user_menu_id;
                /**
                 * 如果当前传值
                 */
                if (isset($copy_params[$field['field_name']]) && $copy_params[$field['field_name']] == $opt[$field['node_field_pk_key']]) {
                    //selected
                    $field['options'][$kk]['href'] = "JavaScript:void(0)";
                    $field['options'][$kk]['class'] = "selected";
                    if (!isset($opt[$field['field_name']])) {
                        $opt[$field['field_name']] = $opt[$field['node_field_name_key']];
                    }
                    $title .= $opt[$field['field_name']] . "、";
                } else {
                    //not selected items 生成的URL参数  增加条件

                    $copy_params[$field['field_name']] = $opt['id'];
                    $field['options'][$kk]['href'] = url($route, $copy_params);
                }
            }
        }

        // test($filter_fields);
        $ret = [
            'where' => $_where,
            'title' => $title,
            'fields' => $filter_fields,
            'query' => $_query
        ];
        return $ret;
    }

    /**
     * 仅能用于User.php的条件查询生产 html
     * @param $model_id
     * @param $user_menu_id
     * @param string $route
     * @param $model_2
     * @param $check_field
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gen_user_filter_two($model_id, $user_menu_id, $route = "", $model_2, $check_field)
    {
        global $_GPC, $_W;
        $_where = $filter_fields = $query_params = $_query = [];
        $title = "";
        $model = set_model($model_id);

        $filter_model_info = $model->model_info;
        $fields = $filter_model_info['setting']['fields'];
        if (!$route) {
            $route = [
                ROUTE_M, Request::instance()->controller(), ROUTE_A
            ];
            $route = join("/", $route);
        }

        foreach ($fields as $k => $field) {
            if (isset($field['disabled']) && $field['disabled']) {
                continue;
            }
            /**
             * 先获取筛选字段
             * 并获取其选项值
             */
            if (isset($field['node_field_is_filter']) && $field['node_field_is_filter']) {

                if (!isset($field['node_field_data_source_type'])) {
                    die("模型 $model_id 中无法筛选的字段，请取消字段的筛选：" . $field['field_name']);
                }
                switch ($field['node_field_data_source_type']) {
                    case "mhcms_options" :
                        $model = set_model($field['node_field_data_source_config']);
                        $model_info = $model->model_info;
                        $where = [];
                        $where['field_name'] = $field['field_name'];
                        $where['model_id'] = $filter_model_info['id'];

                        if (Models::field_exits('site_id', $field['node_field_data_source_config'])) {
                            $where['site_id'] = ["IN", [$_W['root']['site_id'], $_W['site']['id']]];
                        }

                        $options = $model->where($where)->select();
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $option['name'] = $option[$model_info['name_key']];
                            $option['id'] = $option[$model_info['id_key']];
                            $new_options[$option['id']] = $option;
                        }

                        if (!$field['node_field_pk_key']) {
                            $field['node_field_pk_key'] = $model_info['id_key'];
                        }


                        //options
                        $field['options'] = $new_options;

                        $filter_fields[] = $field;
                        break;
                    case "model" :
                        //todo 判断是否为地区
                        $_model = set_model($field['node_field_data_source_config']);
                        $_model_info = $_model->model_info;
                        $where = [];
                        if (self::field_exits("site_id", $field['node_field_data_source_config'])) {
                            $where['site_id'] = ["IN", [$_W['root']['site_id'], $_W['site']['id']]];
                        }
                        $options = $_model->where($where)->select();
                        if ($_model_info['id_key']) {
                            $id_key = $_model_info['id_key'];
                        } else {
                            $id_key = $field['node_field_pk_key'];
                        }
                        if ($_model_info['name_key']) {
                            $name_key = $_model_info['name_key'];
                        } else {
                            $name_key = $field['node_field_name_key'];
                        }
                        if (!$name_key || !$id_key) {
                            var_dump($id_key);
                            test($name_key);
                        }
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $option['id'] = $option[$id_key];
                            $option['name'] = $option[$name_key];
                            $new_options[$option[$id_key]] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        //selected value equal
                        if (isset($_GPC[$field['field_name']])) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $field['node_field_pk_key'] = $id_key;
                        $field['node_field_name_key'] = $name_key;
                        $filter_fields[] = $field;
                        break;
                    case "category":
                        $where = [];
                        $model->set_table("cate");
                        $where['type_id'] = $model_id;
                        $options = $model->select($where);
                        $new_options = [];
                        foreach ($options as $k => $option) {
                            $new_options[$option['id']] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        if (isset($_GPC[$field['field_name']])) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }
                        $filter_fields[] = $field;
                        break;
                    case "diy_arr":


                        $options = "";
                        //todo diy arr filter
                        $name_key = "name";
                        $id_key = "id";

                        if (!$name_key || !$id_key) {
                            var_dump($id_key);
                            test($name_key);
                        }

                        $rows = explode("\r\n", $field['node_field_data_source_config']);
                        $new_options = [];
                        foreach ($rows as $row) {
                            $row_data = explode("|", $row);
                            $option = [];
                            $option[$id_key] = $row_data[0];
                            $option[$name_key] = $row_data[1];
                            $new_options[$option[$id_key]] = $option;
                        }
                        //options
                        $field['options'] = $new_options;
                        //selected value equal
                        if ($_GPC[$field['field_name']]) {
                            $field['selected'] = $_GPC[$field['field_name']];
                        }

                        $field['node_field_pk_key'] = $id_key;
                        $field['node_field_name_key'] = $name_key;
                        $filter_fields[] = $field;
                        break;
                }
                if (isset($_GPC[$field['field_name']])) {
                    $query_params[$field['field_name']] = $_GPC[$field['field_name']];


                    $field_obj = new Field($field);
                    switch ($field_obj->node_field_mode) {

                        case "layui_checkbox":
                            $is_multi = 1;
                            break;
                        default :
                            $is_multi = 0;
                    }
                    $_query[$field['field_name']] = $query_params[$field['field_name']];
                    if ($is_multi) {
                        $_where[$field['field_name']] = ['like', '%,' . $query_params[$field['field_name']] . ',%'];
                    } else {
                        $_where[$field['field_name']] = $query_params[$field['field_name']];
                    }
                }
            } else {
                unset($fields[$k]);
            }
        }
        ksort($query_params);
        foreach ($filter_fields as $k => &$field) {
            //get_current val
            foreach ($field['options'] as $kk => $opt) {
                //设置全部连接
                if (!isset($field['href'])) {
                    $copy_params = $query_params;
                    $copy_params['user_menu_id'] = $user_menu_id;
                    //如果参数未被设置 则全部选项选中
                    if (isset($copy_params[$field['field_name']])) {
                    } else {
                        $field['class'] = "selected";
                    }
                    unset($copy_params[$field['field_name']]);
                    $field['href'] = url($route, $copy_params);
                }


                //设置选项链接 generate the a tag
                $copy_params = $query_params;
                $copy_params['user_menu_id'] = $user_menu_id;
                /**
                 * 如果当前传值
                 */
                if (isset($copy_params[$field['field_name']]) && $copy_params[$field['field_name']] == $opt[$field['node_field_pk_key']]) {
                    //selected
                    $field['options'][$kk]['href'] = "JavaScript:void(0)";
                    $field['options'][$kk]['class'] = "selected";
                    if (!isset($opt[$field['field_name']])) {
                        $opt[$field['field_name']] = $opt[$field['node_field_name_key']];
                    }
                    $title .= $opt[$field['field_name']] . "、";
                } else {
                    //not selected items 生成的URL参数  增加条件

                    $copy_params[$field['field_name']] = $opt['id'];
                    $field['options'][$kk]['href'] = url($route, $copy_params);
                }
            }
        }

// test($filter_fields);
        $ret = [
            'where' => $_where,
            'title' => $title,
            'fields' => $filter_fields,
            'query' => $_query
        ];
        return $ret;
    }


    public
    function add_content($base)
    {
        global $_W;
        //$this->table_name 对应数据库字段table_name//Db::name($this->table_name);
        $base_model = set_model($this->table_name);
        //模型信息
        $model_info = $base_model->model_info;
        $this->node_fields = $this->setting['fields'];
        $info['code'] = 1;
        /*
         * TODO：if the content need check
         * */
        /*
         * 自动分配用户
         * */
        if (self::field_exits('user_id', $this->table_name)) {
            if (defined("IN_MHCMS_ADMIN")) {
                if (!isset($base['user_id']) && isset($this->user_id)) {
                    $base['user_id'] = $this->user_id;
                }
            } else {
                /**
                 * if the user is empty ,assign it to the current user
                 */
                if (!isset($base['user_id'])) {
                    $base['user_id'] = $_W['user']['id'];
                }
                if ($base['user_id']) {
                    if ($this->amount_per_user != 0) {
                        /**
                         * limit user post num
                         */
                        $test_where['user_id'] = $this->user_id;
                        $count = Db::name($this->table_name)->where($test_where)->count('*');
                        if ($count >= $this->amount_per_user) {
                            $info['code'] = 0;
                            $info['msg'] = "you can not post more than $count post in this channel! ";
                            return $info;
                        }
                    }

                } else {
                    if ($this->node_fields['user_id']['node_field_must_fill']) {
                        $info['code'] = 0;
                        $info['msg'] = "对不起，需要提供用户信息才能录入信息";
                        return $info;
                    }

                }
            }

            $base['user_id'] = (int)$base['user_id'];
        }

        // handle input for base fields here
        foreach ($base as $k => $v) {
            if (isset($this->node_fields[$k])) {
                $base[$k] = $this->form_factory->process_model_input($this->node_fields[$k], $v, $base); //
            } else {
                unset($base[$k]);
            }
        }

        $bad_words = explode(',', $_W['site']['config']['bad_words']);

        /* 遍历每一个当前节点类型的字段进行验证 */
        foreach ($this->node_fields as $k => $v) {
            if (!self::field_exits($k, $this->table_name)) {
                continue;
            }

            //检测安全过滤
            if ($bad_words) {
                foreach ($bad_words as $bad_word) {

                    if ($base[$k] && strpos($base[$k], $bad_word) !== false) {

                        $info['code'] = 0;
                        $info['msg'] = "对不起 安全检验失败";
                        //send info to admin user


                        $tpl_config = mhcms_json_decode($_W['tpl_config']);
                        $tpl_config['tp_url'] = "";
                        unset($tpl_config['miniprogram']);
                        $params['header'] = "检测到未成功发布的非法信息，非法信息：$bad_word";
                        $params['footer'] = "";
                        if (isset($_W['user'])) {
                            $params['footer'] = "用户ID" . $_W['user']['id'];
                        }
                        $params['footer'] .= "  登录管理员ID:" . $_W['admin']['id'];
                        Notice::send('系统通知', 'wxmsg', $_W['site']['config']['admin_openid'], $params, $tpl_config);

                        return $info;
                    }
                }

            }

            $tmp_data = $base;

            //没有禁用 并且strlen < 1
            if (isset($v['node_field_must_fill']) && $v['node_field_must_fill'] && strlen($tmp_data[$k]) < 1) {
                $info['code'] = 0;
                $info['msg'] = ($this->node_fields[$k]['slug'] ? $this->node_fields[$k]['slug'] : $k) . "  必须填写";
                return $info;
            }

            //&& verify data with think php rules
            if (isset($v['node_field_expression']) && $v['node_field_expression'] && $tmp_data[$k]) {
                $validate = new Validate([
                    $k => $v['node_field_expression']
                ]);
                if ($v['node_field_is_multiple'] <= 1) {
                    $data = [
                        $k => $tmp_data[$k]
                    ];
                    if (!$validate->check($data)) {
                        $info['code'] = 0;
                        $info['msg'] = $v['slug'] . ": 验证失败！"; //$validate->getError()
                        return $info;
                    }
                } else {
                    //multiple verify
                    foreach ($tmp_data[$k] as $multiple_v) {
                        $data = [
                            $k => $multiple_v
                        ];
                        if (!$validate->check($data)) {
                            $info['code'] = 0;
                            $info['msg'] = $v['slug'] . ":" . $validate->getError();
                            return $info;
                        }
                    }
                }
            }
        }

        //临时解决views not default value 问题
        if ((!isset($base['views']) || empty($base['views'])) && self::field_exits('views', $this->table_name)) {
            $base['views'] = 0;
        }

        if ((!isset($base['site_id']) || empty($base['site_id'])) && self::field_exits('site_id', $this->table_name)) {
            $base['site_id'] = (int)$_W['site']['id'];
        }
        //!$base['module']
        if ((!isset($base['module']) || empty($base['module'])) && self::field_exits('module', $this->table_name)) {
            $base['module'] = ROUTE_M;
        }
        //Verify and process end
        /*
         *  所有验证通过 开始处理数据
         *  生成所有不需填写的字段
         * */
        if ((!isset($base['create_at']) || empty($base['create_at'])) && self::field_exits('create_at', $this->table_name)) {
            $base['create_at'] = !empty($base['create_at']) ? $base['create_at'] : date("Y-m-d H:i:s", SYS_TIME);
        }

        if ((!isset($base['update_at']) || empty($base['update_at'])) && self::field_exits('update_at', $this->table_name)) {
            $base['update_at'] = !empty($base['update_at']) ? $base['update_at'] : date("Y-m-d H:i:s", SYS_TIME);
        }

        try {
            if ($item_id = $base_model->insert($base, false, true)) {
                $base['id'] = $item_id;
                $ret['code'] = 1;
                $ret['msg'] = "操作完成! ";
                $ret['item'] = $base;
                $ret['data'] = $base;

//                if ($this->is_index) {
//                    MhcmsIndex::create($ret['item']['id'], $this->id);
//                }

                return $ret;
            } else {
                $info['code'] = 0;
                $info['msg'] = "O! BASE BIG FAILES !";
                return $info;
            }
        } catch (\Exception $e) {
            Log::error("TING_EXE" . $e->getMessage() . "--" . $e->getTraceAsString());
            $info['code'] = 0;
            $info['msg'] = $e->getMessage();
            return $info;
        }
    }

    /**
     * TODO 优化性能
     * 用来检测字段是否存在
     * 存在条件第一是字段没有禁用
     * @param $field_name
     * @param $model_id
     * @param int $level | level 为 0 时效率最高
     * @return bool
     * @throws \think\exception\DbException
     */
    public
    static function field_exits($field_name, $model_id, $debug = 0)
    {
        static $table_fields;
        $model = set_model($model_id);

        if (!isset($table_fields[$model_id])) {
            $table_fields[$model_id] = Db::name($model->model_info['table_name'])->getTableFields([]);
        }

        $test = array_search($field_name, $table_fields[$model_id]);
        if ($debug) {
            test($table_fields);
        }
        if ($test === false) {
            return false;
        }

        $model_fields = $model->model_info['setting']['fields'];
        // field   set , and disabled
        if (isset($model_fields[$field_name]) && (isset($model_fields[$field_name]['disabled']) && $model_fields[$field_name]['disabled'] == 1)) {
            return false;
        }
        if ($debug) {
            test($test);
        }
        return true;
    }

    /**
     * @param $base
     * @param $where
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public
    function edit_content($base, $where)
    {
        $base_model = set_model($this->table_name);// Db::name($this->table_name);
        //todo get the auth fields that current user can modify

        $this->form_factory->model_id = $this->table_name;
        $this->node_fields = $this->setting['fields'];


        $form_data = $base;
        $old_data = $base_model->where($where)->find();

        $base = array_merge($old_data, $base);

        $ret['code'] = 1;
        /* 遍历每一个当前节点类型的字段进行验证 */
        foreach ($this->node_fields as $k => $v) {
            if ($v['node_field_mode'] == "layui_checkbox") {
                if (empty($base[$k])) {
                    $base[$k] = "";
                }
            }
            $tmp_data = $base;//test($tmp_data);
            //必填字段
            if (isset($v['node_field_must_fill']) && $v['node_field_must_fill'] && (!isset($v['disabled']) || $v['disabled'] == 0)) {

                if (!isset($tmp_data[$k]) || $tmp_data[$k] == "") {
                    $info['code'] = 0;
                    $info['msg'] = ($this->node_fields[$k]['slug'] ? $this->node_fields[$k]['slug'] : $k) . "  $k 必须填写" . $base['cate_id'];
                    return $info;
                }
            }

            //&& verify data with think php rules
            if (isset($v['node_field_expression']) && $v['node_field_expression']) {

                $validate = new Validate([
                    $k => $v['node_field_expression']
                ]);
                if ($v['node_field_is_multiple'] <= 1) {
                    $data = [
                        $k => $tmp_data[$k]
                    ];
                    if (!$validate->check($data)) {
                        $ret['code'] = 0;
                        $ret['errmsg'] = $validate->getError();
                        $ret['msg'] = ($this->node_fields[$k]['slug'] ? $this->node_fields[$k]['slug'] : $k) . "  必须填写";
                        return $ret;
                    }
                } else {
                    //multiple verify
                    foreach ($tmp_data[$k] as $multiple_v) {
                        $data = [
                            $k => $multiple_v
                        ];
                        if (!$validate->check($data)) {
                            $ret['code'] = 0;
                            $ret['msg'] = $validate->getError();
                            return $ret;
                        }
                    }
                }
            }
        }
        // handle input for base fields here
        foreach ($base as $k => $v) {

            if (isset($this->node_fields[$k])) {
                if ($this->node_fields[$k]['node_field_asform'] && !$this->node_fields[$k]['disabled']) {
                    $base[$k] = $this->form_factory->process_model_input($this->node_fields[$k], $v, $base); //
                }
            } else {
                unset($base[$k]);
            }
        }

        /*
         * TODO：if the content need check
         * */
        if (isset($this->node_fields['user_id'])) {

            if (defined("IN_ADMIN")) {
                //管理员
                /** * user_id  */
                if (!isset($base['user_id']) || empty($base['user_id'])) {
                }
            } else {
                /**
                 * if the user is empty ,assign it to the current user
                 */
                if (empty($base['user_id']) && isset($this->user_id)) {
                    $base['user_id'] = $this->user_id;
                }
                /*
                 * 第二级 处理审核
                 * TODO :检查当前用户组是否有权限发帖
                 * */
                /*
                 * :检查用户本node type是否已经超过允许的数量
                 * */
                if ($this->amount_per_user != 0) {
                    /**
                     * limit user post num
                     */
                    $test_where['user_id'] = $this->user_id;
                    $count = Db::name($this->table_name)->where($test_where)->count('*');
                    if ($count >= $this->amount_per_user) {
                        $ret['code'] = 0;
                        $ret['msg'] = "you can not post more than $count post in this channel! ";
                        return $ret;
                    }
                }
            }
        }

        //记录操作人
        if (isset($this->node_fields['update_id']) && $this->node_fields['update_id']) {
            $base['update_id'] = $base['user_id'];
        }

        if (isset($this->node_fields['create_at']) && $this->node_fields['create_at']) {
            $base['create_at'] = $create_time = !empty($base['create_at']) ? $base['create_at'] : date("Y-m-d H:i:s", SYS_TIME);
        }
        if (self::field_exits("update_at", $this->table_name)) {
            $base['update_at'] = date("Y-m-d H:i:s", SYS_TIME);
        }
        if ($res_id = $base_model->where($where)->update($base)) {
            if ($old_data['id']) {
                $base['id'] = $old_data['id'];
            }
            $ret['item'] = $base;
        } else {
            $ret['code'] = 1;
            $ret['msg'] = "O! 您没有改变任何数据 ";
            $ret['item'] = $base;
            return $ret;
        }
        $ret['code'] = 1;
        $ret['msg'] = "操作完成! ";
        if ($this->is_index) {
            MhcmsIndex::update($ret['item']['id'], $this->id);
        }

        return $ret;
    }

    /**
     * 没有过滤的更新
     * @param $data
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @internal param $model_id
     */
    public
    function update_item($data, $where)
    {
        $item = Db::name($this->table_name)->where($where)->update($data);
        return $item;
    }

    public
    function get_admin_column_fields($detail = [], $hide_fields = [])
    {
        global $_W;
        if (!$hide_fields) {
            $hide_fields = [];
        }
        $new_field_list = [];
        $form_factory = new FormFactory($_W['site']['id']);
        $form_factory->model_id = $this->id;
        $form_factory->bind_module = isset($this->bind_module) ? $this->bind_module : ROUTE_M;
        $form_factory->model_info = $this;
        $new_field_list = $this->setting['fields'];
        foreach ($new_field_list as $k => $field) {
            if (empty($field['node_field_mode']) || in_array($k, $hide_fields) || !$field['show_admin_colum'] || (isset($field['disabled']) && $field['disabled'] == 1)) {
                unset($new_field_list[$k]);
                continue;
            }
            if (isset($detail[$field['field_name']])) {
                $field['node_field_default_value'] = $detail[$field['field_name']];
            }

            $new_field_list[$k] = $field;
        }
        return $new_field_list;
    }

    public
    function get_user_publish_fields($detail = [], $hide_fields = [], $show_fields = [], $render_form = true)
    {
        global $_W, $_GPC;
        $new_field_list = [];
        $form_factory = new FormFactory($_W['site']['id']);
        $form_factory->model_id = $this->id;
        $form_factory->bind_module = isset($this->bind_module) ? $this->bind_module : ROUTE_M;
        $form_factory->model_info = $this;
        //model_info['id'];
        $new_field_list = $this->setting['fields'];
        foreach ($new_field_list as $k => $field) {
            //todo filter the auth fields with the disabled roles in the field
            $field['module'] = $this->module;
            if (empty($field['node_field_mode']) || in_array($k, $hide_fields) || !$field['node_field_asform'] || !$field['node_field_display_form'] || (isset($field['disabled']) && $field['disabled'] == 1)) {

                unset($new_field_list[$k]);
                continue;
            }

            if ($show_fields && !in_array($k, $show_fields)) {
                unset($new_field_list[$k]);
                continue;
            }

            if (isset($detail[$field['field_name']])) {
                $field['node_field_default_value'] = $detail[$field['field_name']];
            }

            if ($render_form) {
                $field['form_str'] = $form_factory->config_model_form($field, $detail);
            }
            $new_field_list[$k] = $field;
        }

        return $new_field_list;
    }

    public
    function get_user_column_fields($detail = [], $hide_fields = [], $render_form = true)
    {
        global $_W, $_GPC;
        $new_field_list = [];
        $form_factory = new FormFactory($_W['site']['id']);
        $form_factory->model_id = $this->id;
        $form_factory->bind_module = isset($this->bind_module) ? $this->bind_module : ROUTE_M;
        $form_factory->model_info = $this;
        //model_info['id'];
        $new_field_list = $this->setting['fields'];
        foreach ($new_field_list as $k => $field) {
            //todo filter the auth fields with the disabled roles in the field
            $field['module'] = $this->module;
            if (empty($field['node_field_mode']) || in_array($k, $hide_fields) || !$field['node_field_asform'] || !$field['show_user_column'] || (isset($field['disabled']) && $field['disabled'] == 1)) {
                unset($new_field_list[$k]);
                continue;
            }

            if (isset($detail[$field['field_name']])) {
                $field['node_field_default_value'] = $detail[$field['field_name']];
            }

            if ($render_form) {
                $field['form_str'] = $form_factory->config_model_form($field, $detail);
            }
            $new_field_list[$k] = $field;
        }

        return $new_field_list;
    }

    /**
     * @param $base_info
     * @return array
     */
    public
    function filter_auth_fields($base_info)
    {
        return $base_info;
    }

    /**
     * 允许管理员发布内容的字段
     * @param array $detail
     * @param array $hide_fields
     * @param array $show_fields
     * @return
     * @throws \think\Exception
     */
    public
    function get_admin_publish_fields($detail = [], $hide_fields = [], $show_fields = [], $form_group = "data")
    {
        global $_W, $_GPC;
        $form_factory = new FormFactory($_W['site']['id']);
        $form_factory->model_id = $this->id;
        $form_factory->form_group = $form_group;
        $new_field_list = $this->setting['fields'];


        foreach ($new_field_list as $k => $field) {
            $field['model_id'] = $this->id;
            $field['module'] = $this->module;
            if (empty($field['node_field_mode']) || in_array($k, $hide_fields) || !$field['node_field_asform'] || (isset($field['disabled']) && $field['disabled'] == 1)) {
                unset($new_field_list[$k]);
                continue;
            }

            /**
             * 设置显示
             */
            if (!in_array($k, $show_fields) && !empty($show_fields)) {
                unset($new_field_list[$k]);
                continue;
            }

            if (isset($detail[$field['field_name']])) {
                $field['node_field_default_value'] = $detail[$field['field_name']];
            }
            $field['form_str'] = $form_factory->config_model_form($field, $detail);
            $new_field_list[$k] = $field;
        }
        return $new_field_list;
    }
}
