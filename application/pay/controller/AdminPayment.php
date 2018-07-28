<?php

namespace app\pay\controller;

use app\common\controller\AdminBase;
use app\pay\model\Payment;
use app\pay\model\PaymentConfig;

class AdminPayment extends AdminBase
{

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $lists = Payment::all();
        $this->view->lists = $lists;
        return $this->view->fetch();
    }

    public function config($id)
    {
        $payment_id = (int)$id;
        $Payment = Payment::get($payment_id);

        //get config
        $where = [
            'payment_id' => $payment_id,
            'site_id' => $this->site_id
        ];

        $payment_config = PaymentConfig::get($where);
        if (!$payment_config) {
            $payment_config = new PaymentConfig();
        }

        $config_data = [];
        if ($Payment && $this->isPost()) {
            $config_keys = input('param.data/a');

            $config = $where;
            $config['config'] = $config_keys;

            if ($payment_config->save($config)) {
                $this->zbn_msg("操作成功", 1);
            } else {
                $this->zbn_msg("操作成功 数据未改变", 2);
            }

        } else {

            $this->view->detail = $payment_config; //
            $this->view->payment = $Payment; //
            return $this->view->fetch($Payment['code']);
        }
    }
    public function config_old($id)
    {
        $payment_id = (int)$id;
        $Payment = Payment::get($payment_id);

        //get config
        $where = [
            'payment_id' => $payment_id,
            'site_id' => $this->site_id
        ];

        $payment_config = PaymentConfig::get($where);
        if (!$payment_config) {
            $payment_config = new PaymentConfig();
        }

        $config_data = [];
        if ($Payment && $this->isPost()) {
            $config_keys = Input('param.keys/a');
            if (empty($config_keys)) {
                $this->zbn_msg("没有数据的配置", 2);
            }
            $config_datas = Input('param.datas/a');
            //prepare the data
            foreach ($config_keys as $v) {
                if (isset($config_datas[$v])) {
                    $config_data[$v] = $config_datas[$v];
                }
            }
            $config = $where;
            $config['config'] = $config_data;

            if ($payment_config->save($config)) {
                $this->zbn_msg("操作成功", 1);
            } else {
                $this->zbn_msg("失败", 2);
            }

        } else {

            $this->view->detail = $payment_config; //
            $this->view->payment = $Payment; //
            return $this->view->fetch($Payment['code']);
        }
    }

    /**
     * 开启或者禁用一个字段
     */
    public function disable_toggle($id)
    {

        $Payment = Payment::get($id);
        if ($Payment['status'] == 1) {
            $ret['msg'] = "禁用成功";
            $Payment['status'] = 0;
        } else {
            $ret['msg'] = "启用成功";
            $Payment['status'] = 1;
        }

        $Payment->save();
        $ret['code'] = 1;
        return $ret;
    }

}