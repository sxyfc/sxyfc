<?php
namespace  app\pay\controller;

use app\common\controller\ModuleBase;
use app\common\controller\ModuleUserBase;
use app\common\model\Draw;
use app\common\model\PaymentLogs;
use app\common\payment\micropay\utils\WxPayConfig;
use app\common\util\Point;

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
        $draw_amount = $this->user['point'] - $this->get_freeze_amount($this->user->id);
        if ($this->isPost()) {
            $insert = input('param.data/a');
            $insert['user_id'] = $this->user['id'];
            $insert['type'] = 1;
            $insert['status'] = 0;
            $insert['from'] = 1;
            $insert['create_time'] = date("Y-m-d H:i:s" , SYS_TIME);

            if (is_numeric($insert['amount']) && $insert['amount'] > 0) {
                if($insert['amount'] > $draw_amount){
                    $this->zbn_msg("余额不足！");
                }
                if(isset($_W['site']['config']['redbao']['min_withdraw']) && $insert['amount'] < $_W['site']['config']['redbao']['min_withdraw']){
                    $this->zbn_msg("申请失败 ， 系统最低提现金额为".$_W['site']['config']['redbao']['min_withdraw'].$_W['site']['config']['trade']['point_text']."！");
                }
                $insert['create_time'] = date("Y-m-d H:i:s");
                $insert['site_id'] = $_W['site']['id'];
                $insert = db('draw')->setDefaultValueByFields($insert);
                if (Point::spend($this->user, $insert['amount'], 1, "余额提现申请")) {
                    Draw::create($insert);
                    $this->zbn_msg("申请成功！", 1, 'true', 1000, "''", "'reload_page()'");
                } else {
                    $this->zbn_msg("申请失败 ， 可能是您的余额不足！");
                }
            } else {
                $this->zbn_msg("提款金额必须是大于0的数字！");
            }

        } else {
            $this->view->amount = $draw_amount;
            return $this->view->fetch();
        }
    }

    public function get_freeze_amount($user_id)
    {
        
       $data = db('')->query("SELECT SUM(a.total_fee) total_fee
  FROM ".config("database.prefix")."distribution_orders a INNER JOIN ".config("database.prefix")."orders b
       ON a.order_id = b.id
 WHERE b.`status` IN ('已支付', '已完成')
   AND a.create_time >= date_add(now(), interval -1 day)
   AND a.user_id = $user_id");
        return $data ? $data[0]['total_fee'] : 0;
    }
}