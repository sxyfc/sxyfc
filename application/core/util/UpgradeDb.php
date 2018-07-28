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

use app\common\model\Models;

class UpgradeDb
{
    public static function fix()
    {


        if (!Models::field_exits("id", 'theme')) {
            $sql = "ALTER `mhcms_theme`
ADD COLUMN `id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);";
            //sql_execute($sql);
        }
        $sql = "ALTER TABLE `mhcms_index_data`  ADD FULLTEXT INDEX (`data`);";
        sql_execute($sql);
    }

    public static function manual_fix()
    {
        $sql = "ALTER TABLE `mhcms_users`   
  CHANGE `user_status` `user_status` TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL";
        sql_execute($sql);

    }


}