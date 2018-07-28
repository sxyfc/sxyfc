<?php
// +----------------------------------------------------------------------
// | MHCMS [ 滨海贺喜鸟网络科技有限公司 版权所有 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015~2017 http://www.mhcms.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: new better <1620298436@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;

use app\common\controller\ModuleBase;
use app\common\model\AttachConfig;
use app\common\model\Hits;
use app\common\model\Models;
use app\common\model\Users;
use app\core\util\ContentTag;
use think\Db;

class Service extends ModuleBase
{

    public function get_sys_config()
    {
        $config = $this->module_config;
        $storge = AttachConfig::get(['default' => 1]);
        $config['attach'] = $storge['attach_sign'];


        return $config;
    }
}