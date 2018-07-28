<?php
namespace app\attachment\storges;

use app\common\model\File;

abstract class StorgeEngine
{


    public abstract function test();

    public abstract function form($field);

    public abstract function upload(File $file , $local_path = '');

    public function get_prefix()
    {
        return $this->config['url'] ? $this->config['url'] : "/";
    }
}