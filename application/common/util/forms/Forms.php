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

use app\common\model\File;
use app\common\util\Tree2;

/**
 * All forms should came out from here first , then apply different in sub elements;
 * Class Forms
 * @package app\common\util\forms
 */
class Forms
{

    public static function semantic_select(Field $field)
    {
        $multiple = $field->multiple ? "multiple" : "";
        $form_name = $field->form_name;
        $string = <<<EOF
<div class="ui  selection dropdown">
  <input name="$form_name" type="hidden" value="default,default2">
  <i class="dropdown icon"></i>
  <div class="default text">请选择</div>
  <div class="menu">
EOF;
        foreach ($field->form_data as $value) {
            $string .= "<div class='item' data-value='{$value[$field->node_field_id_key]}'>{$value[$field->node_field_name_key]}</div>";
        }
        $string .= <<<EOF2
  </div>
</div>
EOF2;

        return $string;
    }

    public static function select(Field $field)
    {
        $field->node_field_primary_option = isset($field->node_field_primary_option) ? $field->node_field_primary_option : zlang("please,select");
        $form_id = $field->node_field_form_id ? $field->node_field_form_id : $field->field_name;
        $string = "<select class='$field->node_field_class_name' name='$field->form_group[$field->node_field_name]$field->multiple' id='$form_id' $field->node_field_form_property>\n<option value='0'>" . zlang($field->node_field_primary_option) . "</option>\n";

        if ($field->form_data) {
            foreach ($field->form_data as $key => $value) {

                $tree_str[$key] = array(
                    "id" => $value[$field->node_field_pk_key],
                    "name" => $value[$field->node_field_name_key]
                );

                if ($field->node_field_parentid_key) {
                    $tree_str[$key]["parent_id"] = $field->node_field_parentid_key && isset($value[$field->node_field_parentid_key]) ? $value[$field->node_field_parentid_key] : false;
                }

                if ($value[$field->node_field_pk_key] == $field->node_field_default_value) {
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
        // todo add sub level


        return $string;
    }

    public static function input($name, $value, $tips = "")
    {
        $str = '';
        $str .= "<input class='$name' type='text' placeholder='$tips' name='$name' value='$value' />";
        return $str;
    }


    public static function text(Field $field, $type = "text")
    {
        $default_values = [];
        $field->default_value = isset($field->node_field_default_value) && $field->node_field_default_value ? $field->node_field_default_value : $field->default_value;
        if ($field->default_value !== null) {
            $default_values = !is_array($field->default_value) ? ['0' => ['field_value' => $field->default_value]] : $field->default_value;
        } else {
            for ($i = 0; $i < $field->node_field_is_multiple; $i++) {
                $default_values[$i] = ['field_value' => ""];
            }
        }
        $str = "";
        $field->node_field_is_multiple = max(1, $field->node_field_is_multiple);
        for ($i = 0; $i < $field->node_field_is_multiple; $i++) {
            $str .= "<input type='$type' placeholder=\"$field->node_field_tips\" name='$field->form_name' id='$field->node_field_name' style='width:$field->node_field_width;height:$field->node_field_height' value='{$default_values[$i]['field_value']}' class='$field->node_field_class_name' $field->node_field_form_property >";
        }
        return $str;
    }

    public static function textarea(AbsFormTag $field)
    {
        if ($field->node_field_default_value) {
            $default_values = !is_array($field->node_field_default_value) ? ['0' => ['field_value' => $field->node_field_default_value]] : $field->node_field_default_value;
        } else {
            for ($i = 0; $i < $field->node_field_is_multiple; $i++) {
                $default_values[$i] = ['field_value' => ""];
            }
        }
        $str = "";
        $field->node_field_is_multiple = max(1, $field->node_field_is_multiple);
        for ($i = 0; $i < $field->node_field_is_multiple; $i++) {
            $str .= "<textarea placeholder='$field->node_field_tips'  data-toggle='tooltip' title='$field->node_field_tips' name='$field->form_group[$field->node_field_name]$field->multiple' id='$field->node_field_name'  $field->node_field_form_property class='$field->node_field_class_name' style='width:$field->node_field_width;height:$field->node_field_height' ";
            $str .= ">{$default_values[$i]['field_value']}</textarea>";
        }

        return $str;
    }

    public static function ueditor(Field $field)
    {
        $form_str = "";
        if (!defined("U_EDITOR")) {
            //load resource
            $form_str .= "<script type=\"text/javascript\" src=\"/statics/components/ueditor/ueditor.config.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"/statics/components/ueditor/ueditor.all.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"/statics/components/ueditor/pageBtn.js\"></script>";
            define("U_EDITOR", 1);
        }
        //gen form str
        $form_str .= "<script  style=\"width:$field->node_field_width;height:$field->node_field_height\" id='$field->node_field_name' name='$field->form_group[$field->node_field_name]$field->multiple' type='text/plain'>";
        $form_str .= $field->node_field_default_value;
        $form_str .= "</script>";

        if ($field->node_field_setting['tool_bar'] == "basic" && !defined("IN_MHCMS_ADMIN")) {
            $toolbars = "
            toolbars:[['FullScreen','bold','underline','strikethrough','subscript','superscript','horizontal','link','emotion','map', 'forecolor', 'backcolor','test' , 'drafts']], 
            ";
        }
        $form_str .= "
        <script type='text/javascript'>
            window.UEDITOR_HOME_URL = '/statics/components/ueditor/';
            require(['layui'] , function(layui) {
              layui.use([] , function() {
                var $ = layui.$;
          
                $(document).ready(function(){
                   var ueditor = UE.getEditor('$field->node_field_name', {
                    theme:'default',
                     $toolbars
                    lang:'zh-cn',
                    serverUrl: '/attachment/ueditor/index',
                    })
                });
            });
            });
            
        </script>";
        return $form_str;
    }

    public static function normal_date($default_value, $form_name, $id, $form_property, $class_name = "form-control", $format = "Y-m-d H:i:s", $lang = "ch")
    {
        $cdn_url = load_config('cdn', 'cdn_url');
        $form_str = "";
        if (!defined("INIT_DATE")) {
            //load file
            $form_str .= '<link rel="stylesheet" type="text/css" href="{$cdn_url}static/plugins/datetimepicker/build/jquery.datetimepicker.min.css"/ >';
            $form_str .= '<script src="{$cdn_url}static/plugins/datetimepicker/build/jquery.datetimepicker.full.min.js"></script>';
        }
        // form here
        $form_str .= "<input data-toggle=\"tooltip\" title='' type='text' name='$form_name' id='$id' size1='" .
            "' value='" . $default_value . "' class='" . $class_name . " ' " . $form_property . ' ' . '>';
        $form_str .= "<script>jQuery('#$id').datetimepicker({format:'$format'});$.datetimepicker.setLocale('$lang');</script>";
        return $form_str;
    }

    public static function multiple_upload($default_value, $form_name, $id = '', $form_group = 'data')
    {
        global $_W, $_GPC;
        $id = $id ? $id : $form_name;
        $file_ids = array_filter(explode(",", $default_value));

        $multiple = "[]";
        $form_str = "
<button type=\"button\" class=\"layui-btn layui_mutil_upload layui_mutil_upload_$form_name\" data-name='$form_group[$form_name]$multiple' >
  <i class=\"layui-icon\">&#xe65d;</i>
</button>
        <div class=\"layui-upload-list\" id=\"$id\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);
            $form_str .= "<div   class='layui-upload-img'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='{$form_group}[$form_name][]'>
<i class='icon close' onclick='remove_parent(this , \".layui-upload-img\")'></i>
</div>";
        }
        $form_str .= "</div>";
        if (is_weixin() && !$_W['is_borrow_wx']) {
            $form_str .= "<script>layui.use(['new_better_upload'], function(){
  var new_better_upload = layui.new_better_upload;
   new_better_upload.init_mutil_wx_upload('$form_name');
});</script>";
        } else {
            $form_str .= "<script>layui.use(['new_better_upload'], function(){
  var new_better_upload = layui.new_better_upload;
   new_better_upload.init_mutil_upload('$form_name');
});</script>";
        }
        return $form_str;

    }

    /** 2018-06-01
     * layui muti upload
     * @param $field
     * @return string
     * @throws \think\exception\DbException
     */

    public static function layui_mutil_image_upload($default_value, $form_name, $field_name, $form_group = 'data', $multiple = "[]")
    {
        global $_W, $_GPC;
        $file_ids = array_filter(explode(",", $default_value));
        $form_name = $form_name.'[]';
        $form_str = "
<div class='weui-uploader__input-box'>
<div  class=\"weui-uploader__input needsclick layui_mutil_upload layui_mutil_upload_$field_name\" data-name='$form_name' >

</div></div>
        <ul class=\"layui-upload-list weui-uploader__files\" id=\"$field_name\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);
            $form_str .= "<li   class='layui-upload-img weui-uploader__file'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='$form_name'>
<i class='icon close' onclick='remove_parent(this , \".layui-upload-img\")'></i>
</li>";
        }
        $form_str .= "</ul>";
        if (is_weixin() && !$_W['is_borrow_wx']) {
            $form_str .= "<script>

require(['layui'] , function(layui) {
    layui.config({ 
        base: '/statics/components/layui/libs/' 
    }); 
    layui.use(['new_better_upload' , 'layer'], function(){
           //layui.layer.msg('test1');
           layui.new_better_upload.init_mutil_wx_upload('$field_name');
           //layui.new_better_upload.init_mutil_upload('$form_name');
    });
});</script>";
        } else {
            $form_str .= "<script>

require(['layui'] , function(layui) {
    layui.config({ base: '/statics/components/layui/libs/' }); 
     layui.use(['new_better_upload' , 'layer'], function(){
         //layui.layer.msg('test');
         layui.new_better_upload.init_mutil_upload('$field_name');
    });
});

</script>";
        }
        return $form_str;
    }


    public static function single_upload($default_value, $form_name, $accept = 'image', $exts = "jpg|png|gif|bmp|jpeg", $text = '', $id = '', $form_group = 'data')
    {
        global $_W, $_GPC;
        $file_ids = array_filter(explode(",", $default_value));
        $form_str = "
<button type=\"button\" class=\"layui-btn layui_single_upload layui_single_upload_$form_name\" name='{$form_group}[$form_name]' >
  <i class=\"layui-icon\">&#xe67c;</i>$text
</button>
        <div class=\"layui-upload-list\" id=\"$form_name\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);
            if (strpos($file['filemime'], 'image') !== false) {
                $form_str .= "<div   class='layui-upload-img'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='data[$form_name]'>
</div>";
            } else {
                $form_str .= "<div   class='mhcms-upload-file'> {$file['filename']}
<input type='hidden' value='{$file_id}' name='data[$form_name]'>
</div>";
            }

        }
        $form_str .= "</div>";
        if (!defined("LAYUI_SINGLE_UPLOADER")) {
            define("LAYUI_SINGLE_UPLOADER", 1);
        }
        $form_str .= "<script>layui.use(['new_better_upload'], function(){
  var new_better_upload = layui.new_better_upload;
   new_better_upload.init_single_upload('$form_name' , '$exts' , '$accept');
});</script>";
        return $form_str;

    }

    /**
     * 新版上传组建
     */
    public static function single_uploader($field_name, $default_value, $form_group)
    {
        global $_W, $_GPC;

        if ($form_group) {
            $form_name = $form_group . "[$field_name]";
        } else {
            $form_name = $field_name;
        }
        $file_ids = array_filter(explode(",", $default_value));
        $form_str = "
<div class='weui-uploader__input-box'>
<div class=\"needsclick weui-uploader__input needsclick layui_single_upload layui_single_upload_$field_name\" name='$form_name' >
  <i class=\"layui-icon\">&#xe67c;</i> 
</div>
</div>
        <div class=\"layui-upload-list\" id=\"$field_name\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);
            $form_str .= "<div   class='layui-upload-img weui-uploader__file'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='$form_name'>
</div>";
        }
        $form_str .= "</div>";
        if (!defined("LAYUI_SINGLE_UPLOADER")) {
            define("LAYUI_SINGLE_UPLOADER", 1);
        }
        $form_str .= "<script> 

require(['layui'] , function(layui) {
     
    layui.config({ base: '/statics/components/layui/libs/' }); 
    layui.use(['new_better_upload'], function(){
        var new_better_upload = layui.new_better_upload;
        new_better_upload.init_single_upload('$field_name');
    });

});

</script>";
        return $form_str;
    }


    public static function radio($datas, $default_value, $form_name, $form_id , $id_key = "id", $name_key = "name", $property = "", $is_layui = true)
    {
        //$default = $field->default_value = isset($field->node_field_default_value)   ? $field->node_field_default_value : $field->default_value;
        $string = '';
        foreach ($datas as $key => $value) {
            $checked = trim($default_value) == trim($value[$id_key]) ? 'checked' : '';
            $string .= '<label class="helper_label" >';
            $string .= '<input type="radio" name="' . $form_name  . '" ' . $property . ' data-id="' . $form_id . '_' . new_html_special_chars($key) . '" ' . $checked . ' value="' . $value[$id_key] . '" title="' . $value[$name_key] . '"> ';

            if (!$is_layui) {
                $string .= $value[$name_key];
            }
            $string .= '</label>';
        }
        return $string;
    }

}
