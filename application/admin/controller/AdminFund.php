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
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Draw;
use app\common\model\PaymentLogs;
use app\common\model\Users;
use app\common\util\forms\Forms;
use app\common\util\Money;
use app\common\util\Point;
use app\core\util\MhcmsRegbag;
use think\Db;
use think\Exception;

class AdminFund extends AdminBase
{
    public function index()
    {
        return $this->view->fetch();
    }
    public function create($user_id)
    {
        return $this->view->fetch();
    }
    public function change($user_id = 0)
    {
        $data = input('param.data/a');
        if ($this->isPost()) {
            $user = Users::get(['user_name' => $data['user_name']]);
            if (!$user) {
                $this->zbn_msg("您操作的用户不存在！");
            }
            if (!is_numeric($data['amount']) || $data['amount'] < 0) {
                $this->zbn_msg("请输入正确的金额！");
            }
            if (empty($data['note'])) {
                $this->zbn_msg("请输入备注！");
            }
            if ($data['unit_type'] == 1) {
                if (empty($data['pay_type'])) {
                    $this->zbn_msg("您操作的是金币 ， 请选择交易类型！");
                }
                //1 消费
                if ($data['operate'] == 1) {
                    if ($data['pay_type'] != 1) {
                        $this->zbn_msg("减少只能选择取款！");
                    }
                    if (!Money::spend($user, $data['amount'], $data['pay_type'], $data['note'] . " . 管理员：" . $this->current_admin['user_name'])) {
                        $this->zbn_msg("余额不足！");
                    }
                }
                if ($data['operate'] == 2) {
                    //2 充值
                    if ($data['pay_type'] == 1) {
                        $this->zbn_msg("增加不可选择取款");
                    }
                    Money::deposit($user, $data['amount'], $data['pay_type'], $data['note'] . " . 管理员：" . $this->current_admin['user_name']);
                }
            } elseif ($data['unit_type'] == 2) {
                $data['pay_type'] = 0;
                //1 消费
                if ($data['operate'] == 1) {
                    Point::spend($user, $data['amount'], $data['pay_type'], $data['note'] . " . 管理员：" . $this->current_admin['user_name']);
                } else {
                    //2 充值
                    Point::deposit($user, $data['amount'], $data['pay_type'], $data['note'] . " . 管理员：" . $this->current_admin['user_name']);
                }
            }
            $this->zbn_msg("操作完成！", 1, 'true', 2000, "''", "'reload_page()'");
        } else {
            if ($user_id) {
                $target = Users::get($user_id);
                $this->view->target = $target;
            }
            return $this->view->fetch();
        }
    }
    public function logs_all($user_id = 0)
    {
        $keyword = input('param.keyword');
        $user = Users::get(['user_name' => trim($keyword)]);
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        $list = PaymentLogs::where($where)->order('log_id desc')->paginate(20, false, ['query' => array('keyword' => $keyword),]);
        $this->view->list = $list;
        return $this->view->fetch();
    }
    public function logs($user_id = 0)
    {
        $keyword = input('param.keyword');
        $user = Users::get(['user_name' => trim($keyword)]);
        $where = [];
        if ($user) {
            $where['user_id'] = $user->id;
        }
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        $list = PaymentLogs::where($where)->order('log_id desc')->paginate(20, false, ['query' => array('keyword' => $keyword),]);
        $this->view->list = $list;
        return $this->view->fetch();
    }
    public function deposit_logs($user_id = 0)
    {
        $keyword = input('param.keyword');
        $user = Users::get(['user_name' => trim($keyword)]);
        $where = [];
        if ($user) {
            $where['user_id'] = $user->id;
        }
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        $this->view->pay_type = $pay_type = input('param.pay_type', 0, "intval");
        $this->view->start_time = $start_time = input('param.start_time') ? input('param.start_time') : date("Y-m-01 00:00:00");
        $this->view->stop_time = $stop_time = input('param.stop_time') ? input('param.stop_time') : date("Y-m-d H:i:s");
        $start_form = Forms::normal_date($start_time, 'start_time', "start_time", '');
        $stop_form = Forms::normal_date($stop_time, 'stop_time', "stop_time", '');
        $this->view->start_form = $start_form;
        $this->view->stop_form = $stop_form;
        $where['pay_type'] = ['eq', $pay_type];
        $where['amount'] = ['gt', 0];
        $where['create_time'] = ['between time', [$start_time, $stop_time]];
        $list = PaymentLogs::where($where)->order('log_id desc')->paginate(20, false, ['query' => array('keyword' => $keyword, 'start_time' => $start_time, 'stop_time' => $stop_time, 'pay_type' => $pay_type),]);
        $this->view->list = $list;
        return $this->view->fetch();
    }
    public function withdraw($user_id = 0)
    {
        global $_W;
        $where = [];
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        $where['status'] = 0;
        $where['site_id'] = $_W['site']['id'];
        $list =  set_model('draw')->where($where)->order('id desc')->paginate(10);
        $this->view->list = $list;
        return $this->view->fetch();
    }
    public function withdraw_logs($user_id = 0)
    {
        $where = [];
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        $where['status'] = 1;
        $list = Draw::where($where)->order('id desc')->paginate(10);
        $this->view->list = $list;
        return $this->view->fetch();
    }
    public function withdraw_finish($id)
    {
        global $_W;
        $id = (int)$id;
        $where = [
            'id' => $id,
            'status' => 0 ,
            'site_id' => $_W['site']['id']
        ];
       Db::startTrans();
        try{
            $draw = set_model("draw")->where($where)->lock(true)->find();

            if(!$draw){
                throw new Exception();
            }


            $user = Users::get($draw['user_id']);
            if(!$user){
                throw new Exception();
            }
            $draw['finish_time'] = date("Y-m-d H:i:s");
            $draw['status'] = 1;
            $draw['operat_id'] = $this->current_admin['id'];

            if($draw['type'] == 1){
                //todo 微信企业打款
                $bag_config = [];
                $bag_config['wishing'] = "感谢您的支持，祝您生活愉快！";
                $bag_config['withdraw_id'] = $draw['id'];
                $bag_config['send_name'] = $_W['site']['config']['system_name'];

                $bag_config['amount'] = $draw['amount'];
                $bag_config['act_name'] = "红包奖励";

                $send_res = MhcmsRegbag::send_redbag($user , $bag_config);

                set_model('draw')->where($where)->update($draw);

                if($send_res['result_code'] !== "SUCCESS"){
                    $_W['err_msg'] = $send_res['err_code_des'];
                    throw new Exception();
                }

            }
            $ret['code'] = 1;
            $ret['msg'] = "操作完成！";
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            $ret['code'] = 0;
            $ret['msg'] = "操作失败！"  . $_W['err_msg'];
        }

        return $ret;
    }
    /**
     * @param $id
     * @return mixed
     */
    public function withdraw_rej($id)
    {
        $id = (int)$id;
        $where = [
            'id' => $id,
            'status' => 0
        ];
        if ($draw = Draw::get($where)) {
            $user = Users::get($draw['user_id']);
            $draw->finish_time = date("Y-m-d H:i:s");
            $draw->status = 2;
            $draw->operat_id = $this->current_admin['id'];
            if ($user && $draw->save()) {
                if (Money::deposit($user, $draw['amount'], 0, "申请驳回 ， ", [])) {
                    $ret['code'] = 1;
                    $ret['msg'] = "操作完成！";
                    return $ret;
                }
            }
        }
        $ret['code'] = 0;
        $ret['msg'] = "操作失败！";
        return $ret;
    }
}