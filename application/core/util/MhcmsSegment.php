<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace app\core\util;

use app\common\util\segment\VicWord;

class MhcmsSegment
{

    public static function split_world($words)
    {
        $dirName = str_replace("\\", "/", dirname(__FILE__));

        if (defined("SERIALIZER_IGBINARY")) {

            $dic_path = APP_PATH . "common" . DIRECTORY_SEPARATOR . "util" . DIRECTORY_SEPARATOR . "segment" . DIRECTORY_SEPARATOR . "dics" . DIRECTORY_SEPARATOR . "dict.igb";
            $fc = new VicWord($dic_path,'igb');
            $arr = $fc->getWord($words);
        } else {

            $dic_path = APP_PATH . "common" . DIRECTORY_SEPARATOR . "util" . DIRECTORY_SEPARATOR . "segment" . DIRECTORY_SEPARATOR . "dics" . DIRECTORY_SEPARATOR . "dict.json";
            $fc = new VicWord($dic_path,'json');
            $arr = $fc->getWord($words);
        }

        foreach($arr as $wd){
            $ws[] = $wd[0];
        }
        return join(" " , $ws);
    }

}