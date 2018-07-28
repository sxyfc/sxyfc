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
namespace app\core\controller;

use app\common\controller\ApiBase;
use app\common\controller\ApiUserBase;
use app\common\controller\Base;
use app\common\model\Models;
use app\common\util\Money;
use app\common\util\Point;
use app\core\util\ContentTag;
use app\core\util\MhcmsRegbag;
use think\Controller;
use think\Db;
use think\Exception;

class Api extends ApiUserBase
{

    /**
     * 抢红包
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function qhb()
    {
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if (!is_array($query)) {
            $query = mhcms_json_decode($query);
        }
        $detail = Models::get_item($query['id'], $query['model_id']);

        $red_bag = set_model('redbag')->where(['item_id' => $query['id'], 'model_id' => $query['model_id']])->find();

        if ($red_bag && $detail['old_data']['site_id'] == $_W['site']['id']) {

            if ($red_bag['is_pass'] == 1) {
                if ($query['pass'] == $red_bag['pass']) {
                    $res = MhcmsRegbag::qhb($red_bag, $_W['user']);
                    echo json_encode($res);
                    return;
                } else {
                    $ret['code'] = 0;
                    $ret['msg'] = "对不起，您的口令不正确";
                    echo json_encode($ret);
                }
            }

            if ($red_bag['is_pass'] == 0) {
                $res = MhcmsRegbag::qhb($red_bag, $_W['user']);
                echo json_encode($res);
                return;
            }
        }
    }

}