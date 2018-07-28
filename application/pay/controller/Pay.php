<?php
namespace app\pay\controller;

use app\common\controller\ModuleBase;
use app\common\controller\UserBase;
use app\common\model\Draw;
use app\common\model\NodeIndex;
use app\common\model\PaymentLogs;
use app\common\util\Money;
use app\order\model\Orders;

class Pay extends ModuleBase
{
    public function deposit()
    {
        if($this->isPost()){
            $order = [];
            $data['total_fee'] = input('param.amount');
            $data['create_time'] = date("Y-m-d H:i:s");
            $data['user_id'] = $this->user->id;
            $data['create_ip'] = $this->request->ip();
            $data['site_id'] = $this->site['site_id'];
            $gateway = input('param.gateway');
            //todo  get getway info  and set is_online mode
            $data['is_online'] = 1;

            if(is_numeric( $data['total_fee']) && $gateway){
                $order = Orders::create($data);
            }

            $pay_url = nb_url(['r'=>'order/index/view' , 'order_id' => $order['order_id']] , $this->sso_domain);
            header('location:'.$pay_url);
            return;
            if($order){
                $gateway ="\\app\\pay\\util\\".$gateway;
                $gw = new $gateway();
                $gw->call_back_url = url("pay/api/call_back" ,[] , 'html' , true);
                $gw->pay($order);
            }
        }else{
            return $this->view->fetch();
        }

    }

    public function draw()
    {
        $extra_map = [
            'node_type_id' => 32,
            'user_id' => $this->user->id,
        ];
        $extra = NodeIndex::get($extra_map);
        $bank_info = $this->node->get_node($extra['node_id']);
        if (strlen($bank_info['title']) < 5) {
            $this->error('请先完善银行账号信息，再进行提现操作！', "/member/content/publish.html?user_menu_id=99&id=32");
        }
        if ($this->isPost()) {
            $insert = input('param.data/a');
            $insert['name'] = $bank_info['title'];
            $insert['bank_id'] = $bank_info['linkage'];
            $insert['card_number'] = $bank_info['input1'];
            $insert['bank_address'] = $bank_info['description'];


            if (is_numeric($insert['amount']) && $insert['amount'] > 0) {
                $insert['create_time'] = date("Y-m-d H:i:s");
                if (Money::spend($this->user, $insert['amount'], 1, "余额提现申请")) {
                    $insert['user_id'] = $this->user->id;
                    Draw::create($insert);
                    $this->zbn_msg("申请成功！");
                } else {
                    $this->zbn_msg("申请失败 ， 可能是您的余额不足！");
                }
            } else {
                $this->zbn_msg("提款金额必须是大于0的数字！");
            }

        } else {
            $this->view->extra = $bank_info;
            return $this->view->fetch();
        }

    }


}