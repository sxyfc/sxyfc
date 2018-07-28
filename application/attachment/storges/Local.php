<?php
namespace app\attachment\storges;


use app\attachment\storges\StorgeEngine;
use app\common\model\File;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Media\Waveform;

class Local extends StorgeEngine
{

    public function test()
    {
        // TODO: Implement test() method.

    }

    public function form($field)
    {
        // TODO: Implement form() method.
        global $_W;
        $cdn_url = $_W['cdn_url'];
        $files = [];
        $item = "";
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
            $item .= "<li  class=\"state-complete\"><p class=\"title\">$f->filename</p><p class=\"imgWrap\"><img src=\"$url\"></p>
<p class=\"progress\"><span style=\"display: none; width: 0px;\"></span></p><span class=\"success\"><input type=\"hidden\" value=\"$f->file_id\" name=\"$field->form_group[$field->node_field_name]$field->multiple\"></span></li>
	    ";
        }
        $form_str = "";
        if (!defined("WEB_UPLOADER")) {
            //load css && js
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/plugins/webuploader/webuploader.min.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/plugins/webuploader/md5.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/plugins/webuploader/upload.js\"></script>";
            $form_str .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$cdn_url}statics/plugins/webuploader/upload.css\" />";

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
        $form_str .= "                <span class='ui info'>或将文件拖到这里，限<span class=\"text-danger\">$field->node_field_is_multiple</span>个文件, 支持格式 <span class=\"text-info\">$field->node_field_data_source_config</span></span>";
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
        //$form_str .= "  $(document).ready(function(){  init_upload('$this->node_field_name','$this->node_field_data_source_config',$this->node_field_is_multiple)});";
        $form_str .= "    </script>";
        return $form_str;
    }

    public function upload(File $file)
    {
        // TODO: Implement upload() method.
        return true;
    }


    public function convert_amr($filePath, $mediaid ){
        $ffmpeg =  FFMpeg::create();
        $audio = $ffmpeg->open($filePath);
        $format = new Mp3();
        $format
            ->setAudioChannels(2)
            ->setAudioKiloBitrate(256);
        $converted_path = str_replace(".amr" , ".mp3" , $filePath);
        $audio->save($format, $converted_path);
        return $this->get_prefix() . str_replace(SYS_PATH . DIRECTORY_SEPARATOR ,"" , $converted_path) ;
    }
}