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
namespace app\common\model;

use think\Cache;

class Modules extends Common
{


    /**
     *
     * get the site's node types
     *
     * @param bool $update
     *
     * @return array|mixed
     */
    public static function get_module_types($site = [], $module = "", $update = false)
    {
        $found = 0;
        $types = Sites::get_node_types($site, $module);

        $module_types = [];
        foreach ($types as $type) {
            if ($type['module'] == $module) {
                $found = 1;
                $module_types[] = $type;
                break;
            }
        }

        if ($found) {
            return $module_types;
        } else {
            return [];
        }

    }

    /**
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_site_module_config()
    {
        global $_W;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['module'] = $this->module;
        $this->config = $site_module_config = set_model("modules_setting")->where($where)->find();
        return $site_module_config;
    }
}
