<?php

namespace app\core\util\mhcms_index;

use app\common\model\Models;
use app\core\util\MhcmsSegment;
use think\Db;

class MhcmsIndex
{


    /**
     * index mhcms data
     * @param $model_id
     * @param $item_id
     * @param string|\think\Request $module
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function create($item_id, $model_id, $module = ROUTE_M)
    {
        $model = set_model($model_id);
        $model_id = $model->model_info->id;
        $item = Models::get_item($item_id, $model_id);

        if (empty($item['old_data']['id']) || !is_numeric($item['old_data']['id'])) {
            //标识列必须是整数型 才能编写进索引
            var_dump($model_id);
            var_dump($item_id);
            test($item['old_data']);
        } else {
            if ($module = module_exist($module)) {
                $index_model = set_model("index");
                $item['old_data']['item_id'] = $item['old_data']['id'];
                $item['old_data']['model_id'] = $model_id;
                $item['old_data']['module'] = $module['id'];
                $item['old_data']['index_group'] = $model->model_info->index_group;
                $res = $index_model->model_info->add_content($item['old_data']);

                if ($res['code'] == 1) {
                    //todo index data
                    $index_fields = $model->model_info->get_index_fields();
                    $index_data['id'] = $res['item']['id'];
                    foreach ($index_fields as $k => $field) {
                        $index_data['data'] .= $item[$k];
                    }
                    $index_data['data'] = MhcmsSegment::split_world($index_data['data']);
                    set_model("index_data")->insert($index_data);
                }
            } else {
                test("模块不存在" . $module);
            }
        }
    }


    public static function update($item_id, $model_id, $module = ROUTE_M)
    {

        $item = Models::get_item($item_id, $model_id);
        $model = set_model($model_id);
        $model_id = $model->model_info->id;
        $index_model = set_model("index");
        $current_index = $index_model->where(['model_id' => $model_id, 'item_id' => $item['id']])->find();
        if ($current_index) {
            $index_model = set_model("index");
            $item['old_data']['index_group'] = $model->model_info->index_group;
            $res = $index_model->model_info->edit_content($item['old_data'], ['id' => $current_index['id']]);

            //todo index data
            $index_fields = $model->model_info->get_index_fields();

            $index_data = [];
            foreach ($index_fields as $k => $field) {
                $index_data['data'] .= $item[$k];
            }
            $index_data['data'] = MhcmsSegment::split_world($index_data['data']);
            set_model("index_data")->where(['id' => $current_index['id']])->update($index_data);
            if ($res['code'] == 1) {

            }
        } else {
            self::create($item_id, $model_id, $module);
        }
    }


    public static function delete($model_id, $item_id)
    {
        $model = set_model($model_id);
        $model_id = $model->model_info->id;
        $item = Models::get_item($item_id, $model_id);
        $index_model = set_model("index");
        $current_indexes = $index_model->where(['model_id' => $model_id, 'item_id' => $item['id']])->select();
        foreach ($current_indexes as $current_index) {
            $index_model->where(['id' => $current_index['id']])->delete();
            set_model("index_data")->where(['id' => $current_index['id']])->delete();
        }
    }
}