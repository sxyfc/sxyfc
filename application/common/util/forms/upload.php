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

use app\attachment\storges\StorgeEngine;
use app\attachment\storges\StorgeFactory;
use app\common\model\AttachConfig;
use app\common\model\File;
use think\Cache;

class upload extends Form
{
    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function storge_upload($field)
    {
        $files = [];
        $item = "";
        //TODO External
        $field->node_field_default_value = explode(',', $field->node_field_default_value);
        if (is_array($field->node_field_default_value) && count($field->node_field_default_value) > 0) {
            foreach ($field->node_field_default_value as $v) {
                $files[] = File::get(['file_id' => $v]);
            }
        }
        foreach ($files as $k => $f) {
            if (!$f) {
                unset($files[$k]);
                continue;
            } else {
                $url = render_file($f);
            }
            if (strpos($f["filemime"], "image") === false) {
                $f->url = "";
            }
            $item .= "<tr ondblclick='$(this).remove()' class=\"state-complete\">
<td class=\"title\">$f->filename<p class=\"imgWrap imgWrapper\"><img onerror='this.src=\"/statics/images/logo.png\"' src=\"$url\"></p></td><td>$f->filesize</td><td>
<p class=\"progress\"><span style=\"display: none; width: 0px;\"></span></p><span class=\"success\"><input type=\"hidden\" value=\"$f->file_id\" name=\"$field->form_group[$field->node_field_name]$field->multiple\"></span></td></tr>
	    ";
        }
        /** @var StorgeEngine $storge */
        $storge = StorgeFactory::get_storge();
        $form_str = $storge->form($field, $item);
        return $form_str;
    }

    public function single_file_upload($field){

        global $_W, $_GPC;

        $file_ids = array_filter(explode(",", $field->node_field_default_value));

        $exts = str_replace("," ,"|" , $field->node_field_data_source_config);
        $form_str = "
<div class='weui-uploader__input-box'>
<div class=\"needsclick weui-uploader__input needsclick layui_single_upload layui_single_upload_$field->node_field_name\" name='$field->form_group[$field->node_field_name]$field->multiple' >
  <i class=\"layui-icon\">&#xe67c;</i>$field->slug
</div>
</div>
        <div class=\"weui-uploader__files has-text-centered\" id=\"$field->node_field_name\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);

            if(strpos($file['filemime'] , "image" ) !==false){
                $form_str .= "<div   class='layui-upload-img weui-uploader__file'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='data[$field->node_field_name]'>
</div>";
            }

            if(strpos($file['filemime'] , "video"  ) !==false){
                $form_str .= "<div   class='layui-upload-img weui-uploader__file mhcms-upload-file-video'>
<i class='icon video'></i>
<input type='hidden' value='{$file_id}' name='data[$field->node_field_name]'>
</div>";
            }


            if(strpos($file['filemime'] , "audio"  ) !==false){
                $form_str .= "<div   class='layui-upload-img weui-uploader__file mhcms-upload-file-audio'>
<i class='icon audio'></i>
<input type='hidden' value='{$file_id}' name='data[$field->node_field_name]'>
</div>";
            }


            if(strpos($file['filemime'] , "application"  ) !==false){
                $form_str .= "<div   class='layui-upload-img weui-uploader__file mhcms-upload-file-audio'>
<i class='icon app'></i>
<input type='hidden' value='{$file_id}' name='data[$field->node_field_name]'>
</div>";
            }
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
        new_better_upload.init_single_upload('$field->node_field_name' , '$exts' , 'file');
    });

});

</script>";
        return $form_str;
    }


    public function layui_single_upload($field)
    {
        global $_W, $_GPC;
        $file_ids = array_filter(explode(",", $field->node_field_default_value));
        $form_str = "
<div class='weui-uploader__input-box'>
<div class=\"needsclick weui-uploader__input needsclick layui_single_upload layui_single_upload_$field->node_field_name\" name='$field->form_group[$field->node_field_name]$field->multiple' >
  <i class=\"layui-icon\">&#xe67c;</i>$field->slug
</div>
</div>
        <div class=\"layui-upload-list\" id=\"$field->node_field_name\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);
            $form_str .= "<div   class='layui-upload-img weui-uploader__file'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='data[$field->node_field_name]'>
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
        new_better_upload.init_single_upload('$field->node_field_name');
    });

});

</script>";
        return $form_str;
    }

    public function mutil_upload($field){
        global $_W, $_GPC;
        $file_ids = array_filter(explode(",", $field->node_field_default_value));

        $field->multiple = "[]";
        $form_str = "
<div class='weui-uploader__input-box'>
<div  class=\"weui-uploader__input needsclick layui_mutil_upload layui_mutil_upload_$field->node_field_name\" data-name='$field->form_group[$field->node_field_name]$field->multiple' >

</div></div>
        <ul class=\"layui-upload-list weui-uploader__files\" id=\"$field->node_field_name\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);
            $form_str .= "<li   class='layui-upload-img weui-uploader__file'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='data[$field->node_field_name]$field->multiple'>
<i class='icon close' onclick='remove_parent(this , \".layui-upload-img\")'></i>
</li>";
        }
        $form_str .= "</ul>";
        if(is_weixin()){
            $form_str .= "<script>

require(['layui','jquery'] , function(layui) {
    layui.config({ 
        base: '/statics/components/layui/libs/' 
    }); 
    layui.use(['new_better_upload' , 'layer'], function(){
           //layui.layer.msg('test1');
           layui.new_better_upload.init_mutil_wx_upload('$field->node_field_name');
           //layui.new_better_upload.init_mutil_upload('$field->node_field_name');
    });
});</script>";
        }else{
            $form_str .= "<script>

require(['layui'] , function(layui) {
    layui.config({ base: '/statics/components/layui/libs/' }); 
     layui.use(['new_better_upload' , 'layer'], function(){
         //layui.layer.msg('test');
         layui.new_better_upload.init_mutil_upload('$field->node_field_name');
    });
});

</script>";
        }
        return $form_str;
    }

    public function layui_mutil_upload($field)
    {
        return Forms::layui_mutil_image_upload($field->node_field_default_value  ,$field->form_name ,$field->node_field_name ,$field->form_group );

    }

    public function upload($field)
    {
        global $_W;
        $cdn_url = $_W['cdn_url'];
        $files = [];
        $item = "";
        if ($field->is_core == 1) { //core
            if (is_array($field->node_field_default_value) && count($field->node_field_default_value) > 0) {
                foreach ($field->node_field_default_value as $v) {
                    $files[] = $v;
                }
            } else {
            }
        } else {
            //TODO External
            $field->node_field_default_value = explode(',', $field->node_field_default_value);
            if (is_array($field->node_field_default_value) && count($field->node_field_default_value) > 0) {
                foreach ($field->node_field_default_value as $v) {
                    $files[] = File::get(['file_id' => $v]);
                }
            }
        }
        foreach ($files as $k => $f) {
            if (!$f) {
                unset($files[$k]);
                continue;
            } else {
                $url = render_file($f);
            }
            if (strpos($f["filemime"], "image") === false) {
                $f->url = "";
            }
            $item .= "<li  class=\"state-complete\"><p class=\"title\">$f->filename</p><p class=\"imgWrap\"><img src=\"$url\"></p>
<p class=\"progress\"><span style=\"display: none; width: 0px;\"></span></p><span class=\"success\"><input type=\"hidden\" value=\"$f->file_id\" name=\"$field->form_group[$field->node_field_name]$field->multiple\"></span></li>
	    ";
        }
        $form_str = "";
        if (!defined("WEB_UPLOADER")) {
            //load css && js
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/components/webuploader/webuploader.min.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/components/webuploader/md5.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/components/webuploader/upload.js\"></script>";
            $form_str .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$cdn_url}statics/components/webuploader/upload.css\" />";
            $form_str .= "    <script type='text/javascript'>";
            $form_str .= "  prepare_upload();";
            $form_str .= "   ";
            $form_str .= "    </script>";
            define("WEB_UPLOADER", 1);
        }
        $queue = "<ul class=\"filelist {$field->node_field_name}_file_list\">$item</ul>";
        $form_str .= "<div class='uploader' name='$field->form_group[$field->node_field_name]$field->multiple' id=\"uploader_$field->node_field_name\">";
        $form_str .= "        <div class=\"queueList\">" . $queue;
        $form_str .= "            <div id=\"dndArea$field->node_field_name\" class=\"placeholder\">";
        $form_str .= "                <div id=\"filePicker$field->node_field_name\"></div>";
        $form_str .= "                <span style='display: none'>或将文件拖到这里，限<span class=\"text-danger\">$field->node_field_is_multiple</span>个文件, 支持格式 <span class=\"text-info\">$field->node_field_data_source_config</span></span>";
        $form_str .= "            </div>";
        $form_str .= "        </div>";
        $form_str .= "        <div class=\"statusBar\" style=\"display:none;\">";
        $form_str .= "            <div class=\"progress\">";
        $form_str .= "                <span class=\"text\">0%</span>";
        $form_str .= "                <span class=\"percentage\"></span>";
        $form_str .= "            </div><div class=\"info\"></div>";
        $form_str .= "            <div class=\"btns\">";
        $form_str .= "                <div id=\"filePicker2$field->node_field_name\"></div><div class=\"uploadBtn\">开始上传</div>";
        $form_str .= "            </div>";
        $form_str .= "        </div>";
        $form_str .= "    </div>";
        $form_str .= "    <script type='text/javascript'>";
        $form_str .= "  $(document).ready(function(){  init_upload('$field->node_field_name','$field->node_field_data_source_config',$field->node_field_is_multiple)});";
        //$form_str .= "  $(document).ready(function(){  init_upload('$field->node_field_name','$field->node_field_data_source_config',$field->node_field_is_multiple)});";
        $form_str .= "    </script>";
        return $form_str;
    }

    public function process_model_output($input, &$base)
    {
        $out_put = [];
        $input = array_filter(explode(",", $input));
        foreach ($input as $f) {
            $file = File::get(['file_id' => $f]);
            if ($file) {
                $file['url'] = tomedia($file);
                if ($f && $file) {
                    $out_put[] = $file;
                }
            }
        }

        if (defined('IN_MHCMS_ADMIN')) {
            $_out_put = '';
            foreach ($out_put as $o) {
                $_out_put .= "<img class='backend-list-img' src='" . $o->url . "' />";break;
            }
            $out_put = $_out_put;
            //$base['']
        }
        return $out_put;
    }

    public function process_model_input($input, &$base)
    {
        if (!is_array($input)) {
            $input = array_filter(explode(",", $input));
        } else {
            $input = array_filter($input);
        }

        if (count(array_filter($input)) > 1) {
            return "," . join(",", array_filter($input)) . ",";
        }
        if (count(array_filter($input)) == 1) {
            return join(",", array_filter($input));
        }
        if (count(array_filter($input)) == 0) {
            return "";
        }
    }


    public function recorder($field){
        if(!is_weixin()){
            return "不支持录音操作";
        }
        $form_str = "<a class='layui-btn lay-btn-sm' id='talk_btn_{$field->node_field_name}'>点击开始录音</a> 
<ul class='audio_list' id='{$field->node_field_name}_list'>

</ul>
<script>
        //按下开始录音
var recording = false;
var START = 0;
var END = 0;

require(['wx'] , function(wx) {
  $('#talk_btn_{$field->node_field_name}').on('click', function(event){
    event.preventDefault();
    //alert(recording);
    if(recording){
        END = new Date().getTime();
    
        if((END - START) < 3000){
            END = 0;
            START = 0;
            layer.msg('录音时间太短');
            recording = false;
            $('#talk_btn_{$field->node_field_name}').html('点击开始录音');
        }else{
            wx.stopRecord({
              success: function (res) {
                    layer.msg(\"录音结束，正在上传请稍后\");
                    uploadVoice(res.localId);
              },
              fail: function (res) {
                alert(JSON.stringify(res) + \"失败\");
                recording = false;
              }
            });
        } 
    }else{
        START = new Date().getTime();
        wx.startRecord({
            success: function(){
                $('#talk_btn_{$field->node_field_name}').html('正在录音 , 点击停止');
                layer.msg(\"正在录音\");
                recording = true;
                localStorage.rainAllowRecord = 'true';
            },
            cancel: function () {
                recording = false;
                alert('用户拒绝授权录音');
            }
        });
    }
    
    
});

//松手结束录音
$('#talk_btn_{$field->node_field_name}').on('touchend111', function(event){
    event.preventDefault();
    var END = new Date().getTime();
    
    if((END - START) < 300){
        END = 0;
        var START = 0;
        //小于300ms，不录音
        clearTimeout(recordTimer);
    }else{
        wx.stopRecord({
          success: function (res) {
              layer.msg(\"录音结束，正在上传请稍后\");
            uploadVoice(res.localId);
          },
          fail: function (res) {
            alert(JSON.stringify(res) + \"失败\");
          }
        });
    } 
});


//上传录音
function uploadVoice(localId){
    //调用微信的上传录音接口把本地录音先上传到微信的服务器
    //不过，微信只保留3天，而我们需要长期保存，我们需要把资源从微信服务器下载到自己的服务器
    wx.uploadVoice({
        localId: localId, // 需要上传的音频的本地ID，由stopRecord接口获得
        isShowProgressTips: 1, // 默认为1，显示进度提示
        success: function (res) {
            res.type = 'audio';
            //把录音在微信服务器上的id（res.serverId）发送到自己的服务器供下载。
            $.ajax({
                url: wechat_download_url,
                type: 'post',
                data: {
                    media_id : res.serverId ,type : 'audio'
                },
                dataType: \"json\",
                success: function (data) {
                    $('#talk_btn_{$field->form_name}').html('开始录音');
                    var hidden_str = '<input type=\'hidden\' name=\'$field->field_name\' value=\''+data.data.file_id +'\' />';
                    $('<li class=\'record_voice_item ui item\' data-href=\''+data.data.play_url +'\' ><i data-href=\'' + data.data.play_url +'\' onclick=\'play_music(this)\' class=\'video play outline icon\'></i> 试听录音 ' + hidden_str +'<i onclick=\"remove_parent(this , \'li\')\" style=\'float:right\' class=\'remove circle outline icon\'></i>'+' </li>').appendTo('#{$field->node_field_name}_list');
                  recording = false;
                }, 
                error: function (xhr, errorType, error) {
                    console.log(error);
                }
            });
        }
    });
}

 

    
});


        </script>";
        return $form_str;

    }




    public function video_upload($field)
    {
        global $_W, $_GPC;
        $file_ids = array_filter(explode(",", $field->node_field_default_value));
        $form_str = "
<button type=\"button\" class=\"layui-btn layui_single_upload layui_single_upload_$field->node_field_name\" name='$field->form_group[$field->node_field_name]$field->multiple' >
  <i class=\"layui-icon\">&#xe67c;</i>$field->slug
</button>
        <div class=\"layui-upload-list\" id=\"$field->node_field_name\">";
        foreach ($file_ids as $file_id) {
            $file = File::get(['file_id' => $file_id]);
            $src = tomedia($file);
            $form_str .= "<div   class='layui-upload-img'>
<img src='{$src}' alt='{$file['filename']}' class='layui-upload-img'>
<input type='hidden' value='{$file_id}' name='data[$field->node_field_name]'>
</div>";
        }
        $form_str .= "</div>";
        if (!defined("LAYUI_SINGLE_UPLOADER")) {
            define("LAYUI_SINGLE_UPLOADER", 1);
        }
        $form_str .= "<script>layui.use(['new_better_upload'], function(){
  var new_better_upload = layui.new_better_upload;
   new_better_upload.init_single_video_upload('$field->node_field_name' , 'mp4|mov');
});</script>";
        return $form_str;
    }
}