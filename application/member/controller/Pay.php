<?php
namespace app\member\controller;

use app\common\controller\ModuleUserBase;
use app\common\controller\UserBase;
use app\common\model\Draw;
use app\common\model\Node;
use app\common\model\NodeIndex;
use app\common\model\NodeTypes;
use app\common\model\PaymentLogs;
use app\common\model\UserRoles;
use app\common\model\Users;
use app\common\util\forms\input;
use app\common\util\Money;

class Pay  extends ModuleUserBase
{
    public function deposit(){

        return $this->view->fetch();
    }

    public function draw(){
        $extra_map = [
            'node_type_id' => 32,
            'user_id' => $this->user->user_id ,
        ];
        $extra = NodeIndex::get($extra_map);
        $bank_info = $this->node->get_node($extra['node_id']);
        if(strlen($bank_info['title']) < 5){
            $this->error('请先完善银行账号信息，再进行提现操作！',"/member/content/publish.html?user_menu_id=99&id=32");
        }
        if($this->isPost()){
            $insert = input('param.data/a');
            $insert['name'] = $bank_info['title'];
            $insert['bank_id'] = $bank_info['linkage'];
            $insert['card_number'] = $bank_info['input1'];
            $insert['bank_address'] = $bank_info['description'];


            if(is_numeric($insert['amount']) && $insert['amount'] >0){
                $insert['create_time'] = date("Y-m-d H:i:s");
                if(Money::spend($this->user , $insert['amount'] , 1 , "余额提现申请")){
                    $insert['user_id'] = $this->user->user_id;
                    Draw::create($insert);
                    $this->zbn_msg("申请成功！");
                }else{
                    $this->zbn_msg("申请失败 ， 可能是您的余额不足！");
                }
            }else{
                $this->zbn_msg("提款金额必须是大于0的数字！");
            }

        }else{
            $this->view->extra = $bank_info;
            return $this->view->fetch();
        }

    }

    public function bill_list(){
        $page =(int) input('param.page' , null , 'intval');
        $where = ['user_id' => $this->user->user_id];
        $list = PaymentLogs::where($where)->order('log_id desc')->paginate(10);
        $this->assign('list', $list);
        return $this->view->fetch();
    }
}