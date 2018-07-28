<?php
namespace  app\pay\controller;

use app\common\controller\ModuleBase;
use app\common\controller\ModuleUserBase;
use app\common\model\Draw;
use app\common\model\PaymentLogs;
use app\common\payment\micropay\utils\WxPayConfig;
use app\common\util\Money;

class Fund extends ModuleUserBase{
    public function deposit()
    {
        global $_W, $_GPC;

        if($this->isPost()){

        }else{
            return $this->view->fetch();
        }
        $gateway = $_GPC["gateway"];

        $pay_obj = new_better_base::load_core_module_class($gateway, "pay");

        $base = base_model();
        $data['create_time'] = date("Y-m-d H:i:s");
        if(!defined('USE_NEW_BETTER')) $data['uniacid'] = $_W['uniacid'];
        $data['user_id'] = $this->user['id'];
        $data['amount'] = $_GPC['amount'];
        if (!is_numeric($data['amount']) || (int)$data['amount'] < 0) {
            showmessage("出错了 ， 您要充值的金额必须是正数！");
        }
        $data['gateway'] = $gateway;
        $data['note'] = "自助在线充值";
        $base->set_table("orders", 1);

        $data['trade_sn'] = $data['id'] = create_sn();
        $res = $base->add($data);
        if($res){
            $jsApiParameters = $pay_obj->pay($data);
            if($_GPC['api'] == 1){
                echo $jsApiParameters;
            }else{
                include new_better_template();
            }
        }else{
            exit("创建订单失败！");
        }
    }


    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function bill_list()
    {
        $page = (int)input('param.page', null, 'intval');
        $where = ['user_id' => $this->user->id];
        $list = PaymentLogs::where($where)->order('log_id desc')->paginate(10);
        $this->assign('list', $list);
        return $this->view->fetch();
    }

    public function draw(){

        global $_W;
        if ($this->isPost()) {
            $insert = input('param.data/a');
            $insert['user_id'] = $this->user['id'];
            $insert['type'] = 1;
            $insert['status'] = 0;
            $insert['create_time'] = date("Y-m-d H:i:s" , SYS_TIME);

            if (is_numeric($insert['amount']) && $insert['amount'] > 0) {
                if($insert['amount'] < 1.2){
                    $this->zbn_msg("申请失败 ， 系统最低提现金额为1.2元！");
                }
                $insert['create_time'] = date("Y-m-d H:i:s");
                $insert['site_id'] = $_W['site']['id'];
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
            return $this->view->fetch();
        }
    }
}