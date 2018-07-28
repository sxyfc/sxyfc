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
class date extends Form
{
    /**
     *  field form type
     * @param Field $field
     * @return string
     */
    public function input_date(Field $field)
    {

        $form_str = "";
        $form_str .= "<input  type='text' id='$field->node_field_name' name='$field->form_group[$field->node_field_name]$field->multiple' id='$field->node_field_name'   value='" . $field->node_field_default_value . "' class='" . $field->node_field_class_name . " ' " . $field->node_field_form_property . ' ' . '>';
        $form_str .= "<script>
            
require(['layui'] , function(layui) {
            layui.use(['laydate'] , function() {
                var laydate = layui.laydate;
                laydate.render({ 
                  elem: '#$field->node_field_name'
                  ,type: 'datetime'
                });
            });
            });
</script>
";
        return $form_str;
    }

    public function time_picker(AbsFormTag $field)
    {
        $form_str = "";
        $form_str .= "<input  type='text' id='$field->node_field_name' name='$field->form_group[$field->node_field_name]$field->multiple' id='$field->node_field_name'   value='" . $field->node_field_default_value . "' class='" . $field->node_field_class_name . " ' " . $field->node_field_form_property . ' ' . '>';
        $form_str .= "<script>
require(['layui'] , function(layui) {
  

            
            layui.use(['laydate'] , function() {
                var laydate = layui.laydate;
                laydate.render({ 
                  elem: '#$field->node_field_name'
                  ,type: 'time'
                });
            });
 });
</script>
";
        return $form_str;
    }

    /**
     * form  for system node input
     * @param AbsFormTag $field
     * @return string
     */
    public function input_create_time(AbsFormTag $field)
    {
        $form_str = "";
        $form_str .= "<input  type='text' id='$field->node_field_name' name='$field->form_group[$field->node_field_name]$field->multiple' id='$field->node_field_name'   value='" . $field->node_field_default_value . "' class='" . $field->node_field_class_name . " ' " . $field->node_field_form_property . ' ' . '>';
        $form_str .= "<script>
            
require(['layui'] , function(layui) {
            layui.use(['laydate'] , function() {
                var laydate = layui.laydate;
                laydate.render({ 
                  elem: '#$field->node_field_name'
                  ,type: 'datetime'
                });
            });
            });
</script>
";
        return $form_str;
    }

    /**
     * 时间区间选择
     * @param Field $field
     * @param $base
     * @return string
     */
    public function datetime_period(Field $field, $base)
    {
        $field->node_field_data_source_config;
        $divider = " - ";

        $field->node_field_default_value = $field->node_field_default_value . $divider . $base[$field->node_field_data_source_config];

        $form_str = "";
        $form_str .= "<input  type='text' id='$field->node_field_name' name='$field->form_group[$field->node_field_name]$field->multiple' id='$field->node_field_name'   value='" . $field->node_field_default_value . "' class='" . $field->node_field_class_name . " ' " . $field->node_field_form_property . ' ' . '>';
        $form_str .= "<script>
require(['layui'] , function(layui) {
            layui.use(['laydate'] , function() {
                var laydate = layui.laydate;
                laydate.render({ 
                  elem: '#$field->node_field_name'
                  ,type: 'datetime',
                  range: true
                });
            });
            });
        </script>
        ";
        return $form_str;
    }


    /**
     * @param $input
     * @param $base
     * @return null|string
     */
    public function process_model_input($input, &$base)
    {
        if (!$this->field) {
            $_input = null;
            test("empty field info ");
        } else {
            switch ($this->field->node_field_mode) {
                case "datetime_period":
                    if ($this->field->node_field_asform) {
                        $divider = " - ";
                        $dates = explode($divider, $input);
                        $_input = trim($dates[0]);
                        $base[$this->field->node_field_data_source_config] = trim($dates[1]);
                    } else {
                        $_input = $base[$this->field->field_name];
                    }
                    break;
                default:
                    $_input = $input;
            }
        }
        return $_input;
    }

    public function process_model_output($input, &$base)
    {
        switch ($this->field->node_field_mode) {
            case "datetime_period":
                $divider = " - ";
                if ($this->field->node_field_asform) {
                    $divider = " - ";
                    $out_put = $input . $divider . $base[$this->field->node_field_data_source_config];
                    break;
                }

            default:
                $out_put = parent::process_model_output($input, $base); // TODO: Change the autogenerated stub
        }

        return $out_put;
    }
}