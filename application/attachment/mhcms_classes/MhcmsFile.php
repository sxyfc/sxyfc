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
namespace app\attachment\mhcms_classes;

use app\common\controller\AdminBase;
use app\common\controller\Base;
use app\common\model\File;
use think\Cookie;

class MhcmsFile
{
    public static function create($url, $file_type = "image")
    {
        global $_W;
        $data = [];
        $current_user = check_user() ? check_user() : check_admin();
        $data['url'] = $url;
        $data['user_id'] = (int)$current_user['id'];
        $data['created'] = date("Y-m-d H:i:s", SYS_TIME);;
        $data['file_type'] = $file_type;
        $data['site_id'] = $_W['site']['id'];
        $file = File::create($data);
        return $file;
    }

    /**
     * @param $file_id
     * @throws \think\exception\DbException
     */
    public static function delete($file_id)
    {
        $file = File::get(['file_id' => $file_id]);
        //todo remove file
        //todo remove file record
    }
}