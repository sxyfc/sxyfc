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
namespace app\attachment\controller;

use app\common\controller\Base;
use app\common\model\File;

class Image extends Base
{
    public function view_thumb($file_id, $size = "300X300")
    {
        list($width, $height) = explode("X", $size);
        $file = File::get($file_id);
        $imgurl_replace = $file->url;

        //检测文件是否存在
        $target_path = CACHE_PATH . dirname($imgurl_replace) . DS . "thumb_" . $file_id . "_" . $width . '_' . $height . '_' . basename($imgurl_replace);
        if (!file_exists($target_path)) {
            if(!is_dir(dirname($target_path))){
                mkdir(dirname($target_path) , 0777 , true);
            }

            $image = \think\Image::open(SYS_PATH . DIRECTORY_SEPARATOR  . $file->url);
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
            $image->thumb($width, $height)->save($target_path);
        }
        $contents = file_get_contents($target_path);
        //generqte
        header('Content-Type: image/png');
        echo $contents;
    }

    public function view($file_id, $size = "300X300")
    {
        list($width, $height) = explode("X", $size);
        $file = File::get($file_id);
        $imgurl_replace = $file->url;

        //检测文件是否存在
        $target_path = CACHE_PATH . dirname($imgurl_replace) . DS . "thumb_" . $file_id . "_" . $width . '_' . $height . '_' . basename($imgurl_replace);

        if (!file_exists($target_path)) {
            if(!is_dir(dirname($target_path))){
                mkdir(dirname($target_path) , 0777 , true);
            }

            $image = \think\Image::open(SYS_PATH . DIRECTORY_SEPARATOR  . $file->url);
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
            $image->thumb($width, $height)->save($target_path);
        }
        $contents = file_get_contents($target_path);
        //generqte
        header('Content-Type: image/png');
        echo $contents;
    }

}