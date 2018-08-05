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
namespace app\common\util;

use app\common\model\PaymentLogs;
use app\common\model\Users;

class Money
{

    /**消费
     * @param Users $user
     * @param $amount
     * @param $pay_type
     * @param string $note
     * @param array $extra
     * @return bool
     */
    public static function spend(Users $user, $amount, $pay_type, $note = "", $extra = [])
    {
        if (!$user) {
            return false;
        }
        $extra['unit_type'] = 1;
        $extra['operate'] = 1;
        if ($user) {
            $user['balance'] = $user['balance'] - abs($amount);
            if ($user['balance']>= 0 && $user->save() ) {
                self::log($user, -$amount, $pay_type, $note, $extra);
                return true;
            } else {
                return false;
            }
        }
    }

    /**充值
     * @param Users $user
     * @param $amount
     * @param $pay_type
     * @param string $note
     * @param array $extra
     * @return bool
     */
    public static function deposit(Users $user, $amount, $pay_type, $note = "", $extra = [])
    {
        $extra['unit_type'] = 1;
        $extra['operate'] = 2;
        if ($user) {
            $user['balance'] = $user['balance'] + abs($amount);
            if ($user->save()) {
                self::log($user, $amount, $pay_type, $note, $extra);
                return true;
            } else {
                return false;
            }
        }

    }


    /**日志
     * @param $user_id
     * @param $amount
     * @param $pay_type
     * @param string $note
     * @param $extra
     */
    public static function log($user, $amount, $pay_type, $note = "", $extra)
    {
        global $_W;
        $payment_logs = new PaymentLogs();
        $insert = [
            "balance" => $user["balance"] ,
            "user_id" =>$user['id'],
            "total_fee" => $amount,
            "pay_type" => $pay_type,
            "note" => $note,
            "create_at" => date("Y-m-d H:i:s"),
            "site_id" => $_W['site']['id'],
        ];

        if (!empty($extra)) {
            $insert = array_merge($insert, $extra);
        }
        if (!isset($insert['order_id'])) {
            $insert['order_id'] = 0;
        }

        $payment_logs->isUpdate(false)->save($insert);
    }
}