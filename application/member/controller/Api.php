<?php

namespace app\member\controller;

use app\common\controller\ApiUserBase;
use app\common\model\Draw;
use app\common\model\UsersAddress;
use app\common\util\Money;

class Api extends ApiUserBase
{

    public function add_address()
    {
        global $_W, $_GPC;

        $address = $_GPC['address'];
        $address['user_id'] = $this->user['id'];

        if(!UsersAddress::get($address)){
            UsersAddress::create($address);
        }


        return [
            'code' => 1 ,
        ];
    }

    public function withdraw(){

        global $_W, $_GPC;
        $query = $_GPC['query'];

        $insert = [];
        $insert['user_id'] = $this->user['id'];
        $insert['type'] = 1;
        $insert['status'] = 0;
        $insert['name'] = $query['name'];
        $insert['from'] = $query['from'];
        $insert['small_app_id'] = $query['small_app_id'];
        $insert['create_time'] = date("Y-m-d H:i:s" , SYS_TIME);

        if (is_numeric($insert['amount']) && $insert['amount'] > 0) {
            $insert['create_time'] = date("Y-m-d H:i:s");
            if (Money::spend($this->user, $insert['amount'], 1, "余额提现申请")) {
                $insert['user_id'] = $this->user->id;
                Draw::create($insert);
                $ret['code'] = 1;
                $ret['msg'] = "申请成功！";
            } else {

                $ret['code'] = 2;
                $ret['msg'] = "申请失败 ， 可能是您的余额不足！";
            }
        } else {
            $ret['code'] = 2;
            $ret['msg'] = "提款金额必须是大于0的数字！";
        }

        echo json_encode($ret);
    }
}