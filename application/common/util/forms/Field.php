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

class Field extends AbsFormTag
{

    public $node_field_default_value = "";
    // if post an array
    public $multiple = 1;
    //table field name
    public $field_name = "";
    // form name attr
    public $form_name = "";
    // help tips
    public $node_field_tips;
    //height
    public $node_field_height;
    // class name
    public $node_field_class_name;

    public $field_type_name;
    // property
    public $node_field_form_property;
    public $node_field_pk_key;
    public $node_field_name_key;
    public $node_field_parentid_key = "parent_id";
    public $model_id;

    /**
     * @var $slug string alias
     */
    public $slug;
    public $node_field_is_filter;
    public $default_value;
    public $node_field_must_fill;

    public $node_field_asform;

    public $show_admin_colum;

    public $show_user_column;

    public $node_field_display_form;


    public $module;
    public function __construct($config_arr = [])
    {
        if (count($config_arr) > 0) {
            $this->set_config($config_arr);
        }
    }

    public function set_config($config_arr)
    {
        foreach ($config_arr as $k => $v) {
            $this->set($k, $v);
        }
    }

}