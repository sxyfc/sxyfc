<?php

namespace app\order\controller;

use app\common\controller\Base;
use app\order\model\Orders;
use app\orders\controller\Order;

class CommonService extends Base
{
    /**
     * @param $id
     * @return Orders|null
     * @throws \think\exception\DbException
     */
    public function get_order($id){
        return Orders::get(['id'=>$id]);
    }
}