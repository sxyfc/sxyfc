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
use app\core\util\MhcmsModules;
use app\core\util\MhcmsTheme;
use think\Db;

class mhcms_static extends Form
{

    /**
     * 主题选择器
     */
    public function theme_selector($field)
    {

        $string = '';
        foreach ($field->form_data as $key => $value) {
            $checked = trim($field->node_field_default_value) == trim($value[$field->node_field_pk_key]) ? 'checked' : '';

            $string .= '<label class="helper_label" >';
            $string .= '<input type="radio" name="' . $field->form_name . '" ' . $field->node_field_form_property . ' id="' . $field->node_field_form_name . '_' . new_html_special_chars($key) . '" ' . $checked . ' value="' . $value[$field->node_field_pk_key] . '" title="' . $value[$field->node_field_name_key] . '"> ';
            $string .= '</label>';
        }
        return $string;
    }

    /**
     * 模板选择器
     */
    public function mhcms_tpl_selector($field)
    {

        $string = '';
        foreach ($field->form_data as $key => $value) {
            $checked = trim($field->node_field_default_value) == trim($value[$field->node_field_pk_key]) ? 'checked' : '';
            $string .= '<label class="helper_label" >';
            $string .= '<input type="radio" name="' . $field->form_name . '" ' . $field->node_field_form_property . ' id="' . $field->node_field_form_name . '_' . new_html_special_chars($key) . '" ' . $checked . ' value="' . $value[$field->node_field_pk_key] . '" title="' . $value[$field->node_field_name_key] . '"> ';
            $string .= '</label>';
        }
        return $string;
    }

    /**
     * 图标选择器
     */
    public function mhcms_icon_selector()
    {
        $service = url('core/service/field_data', ['model_id' => $this->model_id, 'field_name' => $this->node_field_name]);
        $string = '';
        $string .= "<div id='{$this->node_field_name}_wrapper' class='ui labeled button' >";
        $string .= "<div class=\"ui button\">
                    <i class=\"heart icon\"></i>
                </div>";
        $string .= '<input type="hidden" name="' . $this->form_group . '[' . $this->node_field_name . ']' . $this->multiple . '" ' . $this->node_field_form_property . ' data-id="' . $this->node_field_form_name . '_' . '" ' . ' value="' . $this->node_field_default_value . '" title="' . '"> ';
        $string .= "<a class=\"ui basic label\" data-service='$service' data-model='zhibo_cate' data-field_name='$this->node_field_name'  mini=\"icon_form_picker\" mode=\"text\">选择{$this->slug}
                </a></div>";
        return $string;
    }
}