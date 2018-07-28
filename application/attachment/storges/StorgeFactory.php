<?php
namespace app\attachment\storges;

use app\common\model\AttachConfig;
use app\common\model\File;

class StorgeFactory
{
    public static function get_storge(){
        global $_GPC  , $_W;
        $storage_config = set_model("attach_config_site")->where(['default' => 1 ,  'site_id' => $_W['site']['id']])->find();

        if(!$storage_config){
            return false;
        }
        $storge = AttachConfig::get(['id' => $storage_config['storge_id']]);
        $class_name = "\\app\\attachment\\storges\\".$storge['attach_sign'];
        $storge_engine = new $class_name(mhcms_json_decode($storage_config['config']));
        return $storge_engine;
    }

    public function upload(File $file , $local_path = ''){
        $storge = self::get_storge();
        if($storge !== false){
            $storge->upload($file , $local_path);
        }
    }
}