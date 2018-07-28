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
namespace app\common\util\forms;

use app\common\model\Models;
use think\Db;

class checkbox extends Form
{
    public $form_data;

    /**
     * @return string
     */
    public function layui_checkbox($field)
    {
        $form_id = $field->node_field_form_id ? $field->node_field_form_id : $field->node_field_form_name;
        $string = '';


        if ($field->node_field_default_value != '') {
            $field->node_field_default_value = strpos($field->node_field_default_value, ',') !== false ? explode(',', $field->node_field_default_value) : array($field->node_field_default_value);
        }


        $i = 1;
        foreach ($field->form_data as $key => $value) {
            $key = trim($key);
            $checked = (isset($field->node_field_default_value) && in_array($value[$field->node_field_pk_key], $field->node_field_default_value)) ? 'checked' : '';

            $string .= '<input lay-filter="' . $field->node_field_name . '" name="' . $field->form_name . '[]" class="' . $field->form_classname . '" type="checkbox" ' . $field->node_field_form_property . ' id="' . $form_id . '_' . $i . '" ' . $checked . ' value="' . new_html_special_chars($value[$field->node_field_pk_key]) . '" title="' . new_html_special_chars($value[$field->node_field_name_key]) . '"> ';
            $i++;
        }
        return $string;
    }

    public function layui_radio($field)
    {
        $default = $field->default_value = isset($field->node_field_default_value)   ? $field->node_field_default_value : $field->default_value;
        return Forms::radio($field->form_data , $default , $field->form_name , $field->form_group , $field->node_field_pk_key ,$field->node_field_name_key , $field->node_field_form_property  );
    }

    public function radio($field)
    {
        $default = $field->default_value = isset($field->node_field_default_value)   ? $field->node_field_default_value : $field->default_value;
        return Forms::radio($field->form_data , $default , $field->form_name , $field->form_group , $field->node_field_pk_key ,$field->node_field_name_key , $field->node_field_form_property , false );
    }


    public function process_model_input($input, &$base)
    {
        if (is_array($input)) {
            $_value = ",";
            $_value .= implode(",", $input);
            $_value .= ",";
        } else {
            $_value = $input;
        }
        return $_value;
    }


    public function process_model_output($input, &$base)
    {
        $out_put = $input;
        $field = $this->field;
        switch ($field->node_field_data_source_type) {
            case "mhcms_options":
                switch ($field->node_field_mode) {
                    //多选
                    case "checkbox":
                    case "layui_checkbox":

                        $ids = array_filter(explode(',', $input));
                        break;

                    //单选

                    case "radio":
                    case "layui_radio":

                        $ids[] = $input;
                        break;
                }

                $mapping['cate'] = ROUTE_M . "_cate";
                $model = set_model(parseParam($field->node_field_data_source_config, $mapping));
                $model_info = $model->model_info;


                $field->node_field_pk_key = $field->node_field_pk_key ? $field->node_field_pk_key : $model_info['id_key'];
                $field->node_field_name_key = $field->node_field_name_key ? $field->node_field_name_key : $model_info['name_key'];


                if (!$field->node_field_pk_key) {
                    die("ID KEY 1 " . $field->node_field_data_source_config);
                }
                if (!$field->node_field_name_key) {
                    die("name_key KEY 1" . $field->node_field_data_source_config);
                }

                $out_put = $model->where([$field->node_field_pk_key => ["IN", $ids]])->select();

                $out = "";
                foreach ($out_put as $k => $item) {
                    if ($out) {
                        $out .= " , ";
                    }
                    $out .= $item[$field->node_field_name_key];
                }
                return $out;
                break;
            case "linkage":
                $result = \think\Cache::get("linkage/$field->node_field_data_source_config");
                //$field = NodeFields::get($field->node_field_id);
                $out_put = $result['data'][$input];
                $out_put = $out_put['name'];
                break;
            case "user_roles":

            case 'model':
                switch ($field->node_field_mode) {
                    //多选
                    case "checkbox":
                    case "layui_checkbox":

                        $ids = array_filter(explode(',', $input));
                        break;

                    //单选

                    case "radio":
                    case "layui_radio":

                        $ids[] = $input;
                        break;
                }

                $mapping['cate'] = $field->module . "_cate";
                $field->node_field_data_source_config = parseParam($field->node_field_data_source_config, $mapping);

                $model = set_model($field->node_field_data_source_config);
                $model_info = $model->model_info;


if($model_info){


                $field->node_field_pk_key = $field->node_field_pk_key ? $field->node_field_pk_key : $model_info['id_key'];
                $field->node_field_name_key = $field->node_field_name_key ? $field->node_field_name_key : $model_info['name_key'];


                if (!$field->node_field_pk_key) {
                    die("ID KEY 2" . $field->node_field_data_source_config);
                }
                if (!$field->node_field_name_key) {
                    die("name_key KEY 2" . $field->node_field_data_source_config);
                }


                $out_put = $model->where([$field->node_field_pk_key => ["IN", $ids]])->select();

}
                $out = "";
                foreach ($out_put as $item) {
                    if ($out) {
                        $out .= " , ";
                    }
                    $out .= $item[$field->node_field_name_key];
                }
                return $out;
                break;
            case 'area' :
                $out_put = $input;
                break;
            case 'diy_arr' :
                $options = $this->get_diy_arr();
                $out_put = explode(",", $out_put);
                $out = "";
                if ($out_put) {

                    foreach ($options as $item) {
                        foreach ($out_put as $o) {
                            if ($o == $item[$field->node_field_pk_key]) {
                                if ($out) {
                                    $out .= " , ";
                                }
                                $out .= $item[$field->node_field_name_key];
                            }
                        }
                    }
                }


                return $out;
                break;
            case 'sub_cate_id' :
                $out_put = D($field->node_field_data_source_config)->where([$field->node_field_pk_key => $input])->find();
                return $out_put[$field->node_field_name_key];
                break;
        }
        return $out_put;
    }

    public function get_diy_arr()
    {
        $field = &$this->field;
        if (is_array($field->node_field_data_source_config)) {
            test($field->node_field_data_source_config);
        }

        $options = explode("\r\n", $field->node_field_data_source_config);

        if (!$field->node_field_pk_key) {
            $field->node_field_pk_key = "id";
        }

        if (!$field->node_field_name_key) {
            $field->node_field_name_key = "name";
        }

        foreach ($options as $option) {
            $_v = [];
            if (strpos($option, "|") !== false) {
                $data = explode("|", $option);
                $_v[$field->node_field_name_key] = $data[1];
                $_v[$field->node_field_pk_key] = $data[0];
            } else {
                $_v[$field->node_field_name_key] = $option;
                $_v[$field->node_field_pk_key] = $option;
            }
            $_new_options[$_v[$field->node_field_pk_key]] = $_v;
        }
        return $_new_options;
    }
}