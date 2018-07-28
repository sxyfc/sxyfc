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
namespace app\advertise\controller;

use app\advertise\model\Advertise;
use app\common\controller\AdminBase;
use app\common\model\Models;
use think\Db;

class AdminPositionData extends AdminBase
{

    public $position = "position";


    public function erase($position_id)
    {
        global $_W, $_GPC;
        set_model('position_data')->where(['position_id' => $position_id, 'site_id' => $_W['site']['id']])->delete();

        return [
            'code' => 1,
            'msg' => '清空完毕'
        ];
    }

}