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
use app\common\model\NodeIndex;
use app\common\model\NodeTypes;
use app\common\util\Tree2;
use app\core\util\ContentTag;
use think\Db;

class select extends Form
{
    /**
     * single_select form
     * @return string the generated form
     */
    public function single_select($field)
    {

        $field->node_field_primary_option = isset($field->node_field_primary_option) ? $field->node_field_primary_option : zlang("please,select");
        $form_id = $field->node_field_form_id ? $field->node_field_form_id : $field->node_field_form_name;
        $string = "<select class='$field->node_field_class_name' name='$field->form_group[$field->node_field_name]$field->multiple' id='$form_id' $field->node_field_form_property>\n<option value='0'>" . zlang($field->node_field_primary_option) . "</option>\n";

        if ($field->form_data) {
            foreach ($field->form_data as $key => $value) {

                $tree_str[$key] = array(
                    "id"=> $value[$field->node_field_pk_key],
                    "name"=> $value[$field->node_field_name_key]
                );

                if($field->node_field_parentid_key){
                    $tree_str[$key]["parent_id"] = $field->node_field_parentid_key && isset($value[$field->node_field_parentid_key]) ? $value[$field->node_field_parentid_key] : false;
                }

                if ($value[$field->node_field_pk_key] == $field->default_value) {
                    $tree_str[$key]["selected"] = "selected";
                } else {
                    $tree_str[$key]["selected"] = "";
                }

            }

            $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
            $tree = new Tree2();
            $tree->init($tree_str);
            $tree->icon = array('   │ ', '   ├─ ', '   └─ ');
            $tree->nbsp = '   ';
            $string .= $tree->get_tree(0, $str, $field->node_field_default_value);
        }
        $string .= '</select>';
        return $string;
    }

    public function mhcms_picker($field){
        $text_val = "";
        if($field->node_field_default_value){
            if($field->form_data[$field->node_field_default_value]['parent_id'] !=0){
                $parent_id = $field->form_data[$field->node_field_default_value]['parent_id'];
                $first = $field->form_data[$parent_id][$field->node_field_name_key];

                $second = $field->form_data[$field->node_field_default_value][$field->node_field_name_key];

                $text_val = $first . " " . $second;
            }else{
                $text_val = $field->form_data[$field->node_field_default_value][$field->node_field_name_key];
            }
        }
        $pure = [];

        foreach($field->form_data as $item){
            $pure[]=$item;
        }
        $tree = ContentTag::get_cate_tree([] , 0 , 0 ,$field->form_data , '' , 'id' , $field->node_field_name_key);

        $tree = json_encode($tree);
        $string = <<<EOF
                <input readonly class="layui-input" type="text" id='picker_$field->node_field_name' data-val="$text_val" value="$text_val" placeholder="选择类别"/>
                <input type="hidden" id="jobs_types_id" name="$field->form_group[$field->node_field_name]" value="$field->node_field_default_value">
                 <script>
            var $field->node_field_name = $tree;
            require(['jquery' , 'weui' , "mhcms_level_picker"]  ,function ($ , weui , mhcms_level_picker) {
                $("#picker_$field->node_field_name").mhcms_level_picker({
                    title: "请选择职位分类",
                    showDistrict : false,
                    field_name : '$field->node_field_name'
                }, $field->node_field_name);
            });

        </script>

          
EOF;

        return $string;
    }
    /**
     * @return string
     */
    public function checkbox($field)
    {
        $form_id = $field->node_field_form_id ? $field->node_field_form_id : $field->node_field_form_name;
        $string = '';
        $field->node_field_default_value = trim($field->node_field_default_value);
        if ($field->node_field_default_value != '') $field->node_field_default_value = strpos($field->node_field_default_value, ',') ? explode(',', $field->node_field_default_value) : array($field->node_field_default_value);
        $i = 1;
        foreach ($field->form_data as $key => $value) {
            $key = trim($key);
            $checked = ($field->node_field_default_value && in_array($value[$field->node_field_pk_key], $field->node_field_default_value)) ? 'checked' : '';
            $string .= '<label class="helper_label">';
            $string .= '<input name="' . $field->form_group . '[' . $field->node_field_name . ']' . $field->multiple . '" class=\'$field->form_classname\' type="checkbox" ' . $field->node_field_form_property . ' id="' . $form_id . '_' . $i . '" ' . $checked . ' value="' . new_html_special_chars($value[$field->pk_key]) . '" title="' . new_html_special_chars($value[$field->name_key]) . '"> ';
            $string .= '</label>';
            $i++;
        }
        return $string;
    }

    /**
     * 单选框
     * @param $str 属性
     * @return string
     */
    public function radio($field)
    {
        $string = '';
        foreach ($field->form_data as $key => $value) {
            $checked = trim($field->node_field_default_value) == trim($value[$field->node_field_pk_key]) ? 'checked' : '';
            $string .= '<label class="helper_label" >';
            $string .= '<input type="radio" name="' . $field->form_group . '[' . $field->node_field_name . ']' . $field->multiple . '" ' . $field->node_field_form_property . ' id="' . $field->node_field_form_name . '_' . new_html_special_chars($key) . '" ' . $checked . ' value="' . $value[$field->node_field_pk_key] . '" title="' . $value[$field->node_field_name_key] . '"> ';
            $string .= '</label>';
        }
        return $string;
    }
    public function ajax_input($field)
    {
        //where come to a ajaxinput wo gen the selected options manually
        //\think\Db::name($field->node_field_data_source_config)->select([]);
        $items = "";
        $name_key = load_config($field->node_field_data_source_config . "_model", "name_key");
        $id_key = load_config($field->node_field_data_source_config . "_model", "id_key");
        if ($field->node_field_name == "keywords" && $field->node_id) {
            $field->node_field_default_value = \think\Db::view('tag_index', 'tag_id,node_id')
                ->view('tags', 'name', 'tag_index.tag_id=tags.tag_id')
                ->where('node_id', '=', $field->node_id)
                ->select()->toArray();
        } elseif ($field->node_id) {
            $field->node_field_default_value = \think\Db::name($field->node_field_data_source_config)->where([$id_key => ["IN", $field->node_field_default_value]])->select()->toArray();
        }
        if (is_array($field->node_field_default_value)) {
            foreach ($field->node_field_default_value as $value) {
                $items .= "<li class=\"table-bordered typehead-item pull-left item-close item_$value[$id_key]\">
$value[$name_key]<input type=\"hidden\" name=\"$field->form_group[$field->node_field_name]$field->multiple\" value=\"$value[$id_key]\"> <button type=\"button\" class=\"close\"><span aria-hidden=\"true\">×</span></button></li>";
            }
        }
        $form_str = "";
        $field->node_field_is_multiple = $field->node_field_is_multiple ? $field->node_field_is_multiple : 1;
        if (!defined('NODE_AJAX_INPUT_INIT')) {
            $form_str = "<script type='text/javascript' src='/static/js/admin/fields/node.js'></script>";
            define('NODE_AJAX_INPUT_INIT', true);
        }
        if (!defined('bootstrap3-typeahead')) {
            $form_str .= "<script type='text/javascript' src='/static/plugins/bootstrap3-typeahead/bootstrap3-typeahead.min.js'></script>";
            $form_str .= "<link rel='stylesheet' href='/static/css/forms/typeahead.css' />";
            define('bootstrap3-typeahead', true);
        }
        $form_str .= "<input name='handler_$field->node_field_name' data-node_type_id='$field->node_field_data_source_config' data-field_name='$field->node_field_name' data-formgroup='$field->form_group' data-multiple='$field->node_field_is_multiple' autocomplete='off' title=\"$field->node_field_tips\" data-toggle=\"tooltip\" type='text' class='{$field->node_field_name}_models_typeAhead typeahead form-control $field->node_field_class_name' id='{$field->node_field_form_name}_typehead' /> <ol class='typehead-wraper' id='{$field->node_field_name}_list_models'>$items</ol>";
        $form_str .= "<script type='text/javascript'>";
        $form_str .= "$(document).ready(function(){";
        $form_str .= "mindHer.ajax_input_node('.{$field->node_field_name}_models_typeAhead')";
        $form_str .= "});";
        $form_str .= "</script>";
        return $form_str;
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
            case 'mhcms_options':
                $model = set_model($field->node_field_data_source_config);
                $opt = $model->where([$model->model_info['id_key'] => $input])->find();

                $out_put = $opt[$model->model_info['name_key']];
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

    /**
     * semantic ui select
     */
    public function semantic_ajax_select($field)
    {

        $params = [];
        $params['model_id'] = $field->node_field_data_source_config;
        $service_api_url = url('core/service/list_item', $params);
        $form_str = "";


        $display_text = $field->node_field_default_value ? $field->node_field_default_value : "请输入关键字";
        $form_str .= "<div id='$field->node_field_name' class=\"SEMANTIC_AJAX_INPUT ui fluid  search normal selection dropdown\">
  <input type=\"hidden\" value='$field->node_field_default_value' name=\"$field->form_group[$field->node_field_name]$field->multiple\">
  <i class=\"dropdown icon\"></i>
  <div class=\"default text\">$field->node_field_default_value</div></div>";

        $form_str .= "
<script type='text/javascript'>
require(['jquery' , 'semantic' , 'layui'] , function($ , semantic , layui) {
  layui.use(['layer'] , function() {
    var $ = layui.$;
    $(document).ready(function() {
          $('#$field->node_field_name').dropdown({
                apiSettings: {
                  // this url parses query server side and returns filtered results
                  url: '$service_api_url' + '?q={query}' ,
                  data : {
                      f : 'sematic_drop_down' ,
                      _rnd : Math.random()
                  },
                  cache : false
                },
          });
    });

});
});

</script>
            ";
        return $form_str;
    }
}