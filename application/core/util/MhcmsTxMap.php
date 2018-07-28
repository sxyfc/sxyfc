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

use app\common\model\UserMenu;
use think\Db;

/**
 * 文件类型缓存类
 * @author    liu21st <liu21st@gmail.com>
 */
class MhcmsTxMap
{
    public static function get_address($lng = "" ,$lat = "" ){
        global $_W , $_GPC;
        $key = $_W['site']['config']['map']['tx_key'];
        $lng = $lng ? $lng : $_GPC['lng'];
        $lat = $lat ? $lat : $_GPC['lat'];
        $api = "http://apis.map.qq.com/ws/geocoder/v1/?location=$lat,$lng&key=$key";

        $resp = ihttp_get($api);
        return  mhcms_json_decode($resp['content']);
    }
}