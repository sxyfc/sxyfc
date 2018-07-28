<?php
namespace app\orders\controller;

use app\common\controller\ModuleUserBase;
use app\common\datas\csv;
use app\common\model\Users;
use app\common\util\forms\Forms;
use app\orders\model\Orders;

class Order extends ModuleUserBase
{

    public function index($user_id = 0)
    {
        $target = Users::get($user_id);
        if (!$target) {
            $target = $this->user;
        }
        $this->view->start_time = $start_time = input('param.start_time') ? input('param.start_time') : date("Y-m-01 00:00:00");
        $this->view->stop_time = $stop_time = input('param.stop_time') ? input('param.stop_time') : date("Y-m-d H:i:s");
        $start_form = Forms::normal_date($start_time, 'start_time', "start_time", '');

        $stop_form = Forms::normal_date($stop_time, 'stop_time', "stop_time", '');
        //如果是收银员
        if ($target->creator_id && $target->user_role_id == 6) {
            $where['operator_id'] = $target->user_id;
        }
        $this->view->start_form = $start_form;
        $this->view->stop_form = $stop_form;

        $where = [
            'create_time' => ['between time', [$start_time, $stop_time]],
        ];

        //如果是商户

        if ($target->user_role_id == 6) {
            $q_user_name = input('param.user_name');
            if ($q_user_name) {
                $q_user = Users::get(['user_name' => $q_user_name]);

                if (!$q_user) {
                    $this->error("无权查看该用户的流水记录1！");
                }
            }
            //所有收银员的列表
            $syy = Users::all(['creator_id' => $target->user_id]);
            $s_uids[] = $target->user_id;
            foreach ($syy as $s) {
                $s_uids[] = $s['user_id'];
            }

            if ($q_user && !in_array($q_user->user_id, $s_uids)) {
                $this->error("无权查看该用户的流水记录！");
            }

            $where['operator_id'] = ["IN", $s_uids];
        } else {
            $where['operator_id'] = ["IN", $target->user_id];
        }

        $where['status'] = 1;
        if (input("export")) {
            $this->export($where);
        } else {
            $this->view->list = Orders::where($where)->order("order_id desc")->paginate(10);
            return $this->view->fetch();
        }

    }

    /**
     * @return mixed|static
     */
    public function update()
    {
        $feedback = input("post.");
        $feedback = $feedback['feedback'];
        $order_id = $feedback['merchOrderNo'];

        $order = Orders::get($order_id);
        $order->status = 1;
        $order->pay_type = $feedback['payType'] == "012" ? "微信" : "支付宝";
        $order->third_order_id = $feedback['hicardOrderNo'];
        $order->pay_time = $feedback['pay_time'];
        if ($order->save()) {
            return $order;
        } else {
            return input("post.");
        }


    }

    public function view($order_id)
    {
        $order = Orders::get($order_id);
        $gateway = "\\app\\pay\\util\\" . $order['payment_name'];
        $gateway = new $gateway();
        $order_out = $gateway->view($order);

    }

    public function order_list()
    {
        $where['operator_id'] = $this->user->user_id;
        $where['status'] = 1;
        $this->view->list = Orders::where($where)->order("order_id desc")->paginate(10);
        return $this->view->fetch();
    }

    public function order_list_xcx()
    {
        $where['operator_id'] = $this->user->user_id;
        $where['status'] = 1;
        $ret['liushui'] = Orders::where($where)->order("order_id desc")->paginate(10);
        $ret['total'] = Orders::where($where)->order("order_id desc")->sum('amount');;
        echo json_encode($ret);
    }


    private function export($where)
    {
        $csv = new csv();
        $field = "order_id,create_time,operator_id,amount";

        $list = Orders::field($field)->limit(100000)->select()->toArray();//查询数据，可以进行处理
        foreach($list as $k=>$t){
            $_user = Users::field('user_name')->find($t['operator_id']);
            $list[$k]['operator_id'] = $_user['user_name'];
        }
        $csv_title = array('订单好', '创建时间', '收银员', '金额');
        $csv->put_csv($list, $csv_title);
    }
}