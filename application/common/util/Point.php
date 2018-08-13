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

class Point
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
            $user = check_user();
        }
        $extra['unit_type'] = 2;
        $extra['operate'] = 1;
        if ($user) {
            $user['point'] = $user['point'] - abs($amount);
            if ($user['point']>= 0 && $user->save() ) {
                self::log($user->id, -$amount, $pay_type, $note, $extra);
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
        if (!$user) {
            return false;
        }
        $extra['unit_type'] = 2;
        $extra['operate'] = 2;
        if ($user) {
            $user['point'] = $user['point'] + abs($amount);
            if ($user->save()) {
                self::log($user->id, $amount, $pay_type, $note, $extra);
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
    public static function log($user_id, $amount, $pay_type, $note = "", $extra)
    {
        global $_W;
        $payment_logs = new PaymentLogs();
        $insert = [
            "user_id" => $user_id,
            "total_fee" => $amount,
            "pay_type" => $pay_type,
            "note" => $note,
            "create_at" => date("Y-m-d H:i:s"),
            "site_id" => $_W['site']['id'],
        ];

        if (!empty($extra)) {
            $insert = array_merge($insert, $extra);
        }

        $payment_logs->isUpdate(false)->save($insert);
    }
}