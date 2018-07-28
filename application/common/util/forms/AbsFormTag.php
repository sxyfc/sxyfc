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

use app\common\util\forms\Forms;
use think\Log;

abstract class  AbsFormTag
{
    //single or multiple [ multiple select, single select, ajax single , ajax multiple]
    public $node_field_mode;
    //data source type [linkage,node_type,category,diy_array]
    public $node_fielddata_source_type;
    //the config value for the data source   [linkage id , node type id, category id, the array that passed in]
    public $node_field_data_source_config;

    // the default value that is selected for default or default input value
    public $node_field_default_value,
        //the form name
        $node_field_form_name,
        //the form name,default value will be the form name
        $node_field_form_id,
        //the form css class name
        $node_field_class_name,
        //the form primary option
        $primary_option,
        //the form property like style=''
        $node_field_form_property, $is_core, $node_id,
        //only take the child to display
        $child_id, $node_type_id, $node_field_name, $node_field_width, $node_field_height, $node_field_tips, $node_field_is_multiple;
    protected $model_id;




    /**
     * setter
     * @param $field_name
     * @param $value
     */
    public function set($field_name, $value)
    {
        $this->$field_name = $value;
    }

    /**
     * getter
     * @param $field_name
     * @return mixed
     */
    public function get($field_name)
    {
        return $this->$field_name;
    }

}