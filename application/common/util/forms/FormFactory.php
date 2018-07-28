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

use think\Exception;
use think\Log;

final class FormFactory
{
    public $tag_name, $form;
    public $site_id, $node_id, $site;
    public $node_type_id;

    public function __construct($site_id = 0)
    {
        global $_W;
        if (!$site_id) {
            $this->site_id = $_W['site']['id'];
        } else {
            $this->site_id = $site_id;
        }
        if (!$this->site_id) {
            throw new Exception();
        }
    }

    /**
     * @param $config
     * @return mixed
     */
    public function config_form($config , $base = [])
    {
        return $this->config_model_form($config , $base);
    }

    /**
     * @param $config
     * @return mixed
     */
    public function form($config)
    {
        return $this->config_model_form($config);
    }

    /**
     * @param $config
     * @param $base
     * @return mixed
     * @throws Exception
     */
    public function config_model_form($config  , &$base)
    {
        if(!is_array($config)){
            throw new Exception();
        }
        if (empty($config)) {
            throw new Exception();
        }

        //$form_object->site = $this->site;

        if (isset($config['field_name'])) {
            $config['node_field_name'] = $config['field_name'];
            $config['site_id'] = $this->site_id;
        } else {
            $config['field_name'] = $config['node_field_name'];
        }

        //默认分组 data
        if (!isset($config['form_group'])) {
            $config['form_group'] = isset($this->form_group) && $this->form_group ? $this->form_group : "data";
        }

        if (isset($config['form_group']) && $config['form_group']) {
            $config['form_name'] = $config['form_group'] . "[" . $config['field_name'] . "]";
        } else {
            $config['form_name'] = $config['field_name'];
        }

        if(!$config['model_id']){
            $config['model_id'] = $this->model_id;
        }

        $config['bind_module'] = isset($this->bind_module) && $this->bind_module ? $this->bind_module : ROUTE_M;
        /**
         * 多值表单
         */
        $config['multiple'] = (int)$config['node_field_is_multiple'] <= 1 ? "" : "[]";
        $config['form_name'] .= $config['multiple'];

        $field = new Field($config);
        //表单设置-默认值
        if (!isset($field->node_field_default_value) && isset($field->default_value)) {
            $field->node_field_default_value = $field->default_value;
        }

        if($field->field_name =="theme"){

        }
        $form_object = new Form($field);
        $form = $form_object->out_put_form( $base);
        return $form;
    }


    /**
     * 验证并生曾数据
     * @param $field 字段信息
     * @param $input 用户提交数据
     * @return mixed
     */
    public function process_input($field, $input, $node_id = 0)
    {
        $tag_name = $field['field_type_name'];
        $class_name = "\\app\\common\\util\\forms\\$tag_name";
        $form_object = new $class_name($this->site_id);
        foreach ($field as $k => $v) {
            $form_object->$k = $v;
        }
        if (!isset($this->node_id) && !empty($this->node_id)) {
            $form_object->node_id = $this->node_id;
        }
        $form_object->site_id = $this->site_id;
        return $form_object->process_input($input);
    }

    public function process_output($field, $input, $node_id = 0)
    {
        $tag_name = $field['field_type_name'];
        $class_name = "\\app\\common\\util\\forms\\$tag_name";
        $form_object = new $class_name($this->site_id);
        foreach ($field as $k => $v) {
            $form_object->$k = $v;
        }
        if (!isset($this->node_id)) {
            $this->node_id = $node_id;
        }
        $form_object->node_id = $this->node_id;
        $form_object->site_id = $this->site_id;
        return $form_object->process_output($input);
    }

    /**
     * @param $field
     * @param $input
     * @param $base
     * @return mixed
     */
    public function process_model_output($config, $input , &$base)
    {
        //todo 临时BUG 修复 与自定义表单冲突
        foreach ($config as $k => &$v) {
            if (is_string($v) && $k != "node_field_data_source_config") {
                if (strpos($v, "|") !== false) {
                    $field_mode = explode("|", $v);
                    $v = $field_mode[0];
                }
            }
        }

        $config['model_id'] = $this->model_id;
        $field = new Field($config);
        $tag_name = $field->field_type_name;
        $form = new Form($field);



        if ($tag_name) {
            return $form->out_put_value($input , $base);
        } else {
            return $input;
        }
    }


    public function process_model_input($config , $input , &$base)
    {
        foreach ($config as $k => &$v) {
            if (is_string($v)) {
                if (strpos($v, "|") !== false) {
                    $field_mode = explode("|", $v);
                    $v = $field_mode[0];
                }
            }
            $config[$k] = $v;
        }
        $config['model_id'] = $this->model_id;
        $field = new Field($config);
        $form = new Form($field);
        $tag_name = $field->field_type_name;
        //var_dump($tag_name);
        if ($tag_name) {
            return $form->input_put_value($input , $base);
        }else{
            //$input  = $base[$config['field_name']] ? $base[$config['field_name']] : $input;
            return $input;
        }
    }

}
