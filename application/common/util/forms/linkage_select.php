<?php

namespace app\common\util\forms;

use app\common\util\Tree2;
use think\Cache;

class linkage_select extends Form
{

    /**
     * single_select form
     * @param Field $field
     * @return string the generated form
     */
    public function select(Field $field , $base)
    {
        $properties = [];
        $properties['mini'] = 'linkage_select';
        $properties['lay-ignore'] = 'linkage_select';
        $field->node_field_class_name  .= ' top_linkage_select '  ;
        $properties['data-service'] = $service = url('core/service/linkage_list_item');
        $properties['data-target_field'] = $field->target_field;
        $properties['data-target_element'] = $field->target_field  ;
        $properties['data-linkage_group'] = $field->linkage_group  ;
        $properties['data-from_field'] = $field->field_name  ;
        $properties['data-current_model_id'] = $field->model_id;
        $properties['data-default_value'] = $field->node_field_default_value;

        $field->node_field_form_property .=  self::gen_mhcms_property($properties);

        $main_select = Forms::select($field);
        return $main_select;
    }

    public function sub_select($field){
        $properties = [];
        $properties['mini'] = 'linkage_select';
        $properties['lay-ignore'] = 'linkage_select';
        $field->node_field_class_name  .= ' sub_linkage_select ' . $field->linkage_group;
        $properties['data-service'] = $service = url('core/service/linkage_list_item');
        $properties['data-target_field'] = $field->target_field;
        $properties['data-target_element'] = $field->target_field  ;
        $properties['data-linkage_group'] = $field->linkage_group  ;
        $properties['data-from_field'] = $field->field_name  ;
        $properties['data-current_model_id'] = $field->model_id;
        $properties['data-default_value'] = $field->node_field_default_value;
        $field->node_field_form_property .=  self::gen_mhcms_property($properties);
        $main_select = Forms::select($field);
        return $main_select;
    }
    public function process_model_output($input , &$base)
    {
        $out_put = $input;
        $field = $this->field;

        switch ($field->node_field_data_source_type) {
            case "linkage":
                $result = \think\Cache::get("linkage/$field->node_field_data_source_config");
                //$field = NodeFields::get($field->node_field_id);
                $out_put = $result['data'][$input];
                $out_put = $out_put['name'];
                break;
            case 'model' :
                $model = set_model($field->node_field_data_source_config);
                $model_info = $model->model_info;
                if (empty($model_info['id_key'])) {
                    die(" id_key" . $model_info['model_name']);
                }
                if (!$field->node_field_pk_key) {
                    $field->node_field_pk_key = $model_info['id_key'];
                }

                if (!$field->node_field_name_key) {
                    $field->node_field_name_key = $model_info['name_key'];
                }
                $out_put = $model->where([$model_info['id_key'] => $input])->find();
                $out_put = $out_put[$field->node_field_name_key];
                break;
            case 'sub_model' :
                $model = set_model($field->node_field_data_source_config);
                $model_info = $model->model_info;
                if (empty($model_info['id_key'])) {
                    die(" id_key" . $model_info['model_name']);
                }
                if (!$field->node_field_pk_key) {
                    $field->node_field_pk_key = $model_info['id_key'];
                }

                if (!$field->node_field_name_key) {
                    $field->node_field_name_key = $model_info['name_key'];
                }
                $out_put = $model->where([$model_info['id_key'] => $input])->find();
                $out_put = $out_put[$field->node_field_name_key];
                break;
            case 'area' :
                $out_put = $input;
                break;
            case 'diy_arr' :
                foreach ($field->node_field_data_source_config as $k => $v) {
                    if (!empty($field->parentid_key)) {
                        $v['parent_id'] = $v[$field->node_field_parentid_key];
                    } else {
                        $v['parent_id'] = "";
                    }
                    $field->data_source_config[$k] = $v;
                }
                $out_put = $field->data_source_config;
                break;
            case 'sub_cate_id' :
                $out_put = D($field->node_field_data_source_config)->where([$field->node_field_pk_key => $input])->find();
                return $out_put[$field->node_field_name_key];
                break;
        }
        return $out_put;
    }

    public static function gen_mhcms_property($properties)
    {
        $ret_property = "";
        foreach ($properties as $k => $property) {
            if ($property) {
                $ret_property .= " " . $k . "='" . $property . "' ";
            } else {
                $ret_property .= " " . $k . " ";
            }
        }
        return $ret_property;
    }
}