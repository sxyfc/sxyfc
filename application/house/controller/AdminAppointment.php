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
namespace app\house\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\Users;
use app\common\util\Money;
use app\common\util\Point;
use app\common\util\Tree2;
use app\core\util\MhcmsDistribution;
use think\Db;
use think\Exception;


class AdminAppointment extends AdminBase
{

    private $house_appointment = "house_appointment";

    /**
     * @return string
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function index()
    {
        global $_W;
        $content_model_id = $this->house_appointment;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $content_model_id;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }


    public function add()
    {
        global $_W, $_GPC;
        $content_model_id = $this->house_appointment;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields([]);
            return $this->view->fetch();
        }
    }


    public function edit($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_appointment);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }


            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, []);
            $detail['data'] = mhcms_json_decode($detail['data']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

    public function diaodu($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->house_appointment);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();

        $log_model = set_model('house_appointment_log');


        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
                $log_info = input('post.log/a');//get the base info
            }
            // change status if status need check or cancled before
            if ($detail['status'] < 2 || $detail['status'] == 4) {
                $check = 1;
                $base_info['status'] = $log_info['status'] = 2;
            } else {
                $log_info['status'] = $base_info['status'];
            }


            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                //todo log
                $log_info['operator_uid'] = $this->admin_id;
                $log_info['appointment_id'] = $id;
                $log_info['site_id'] = $_W['site']['id'];
                $log_info['status'] = $base_info['status'];
                if ($check == 1) {
                    $msg = "信息自动通过审核";
                }

                if ($base_info['employee_id']) {
                    $log_info['agent_id'] = $base_info['employee_id'];
                    $log_info['content'] .= " : 调度操作 $msg";
                } else {
                    $log_info['content'] .= " : 调度操作 未选择任何经纪人 $msg";
                }

                $log_model->model_info->add_content($log_info);


                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, [], ['employee_id', 'content']);


            $this->view->log_field_list = $log_model->model_info->get_admin_publish_fields([], [], [], 'log');

            $detail['data'] = mhcms_json_decode($detail['data']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }

    public function agent_reward($id)
    {
        global $_W, $_GPC;
        $need_check_distribute_order = false;
        $model = set_model($this->house_appointment);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        $agent = set_model('house_agent')->where(['id' => $detail['agent_id']])->find();

        if ($detail['status'] != 6 || $detail['agent_reword_status'] == 1) {

            if($detail['agent_reword_status'] == 1){
                $update = [];
                $update['agent_reword_status'] = 1;
                $update['status'] = 99;
                $model->where($where)->update($update);
            }

            $this->zbn_msg("该订单状态不是签约状态 ，或者已经发放过奖励 ， 导致无法进行奖励的发放！");
        }

        if (!$agent) {
            $this->zbn_msg("error , 非经纪人订单 或者 经纪人不存在");
        }
        //check if reward is set
        $self_user =Users::get(['id' =>$agent['user_id']]);

        if ($self_user && is_numeric($detail['agent_reword']) && is_numeric($detail['deal_price'])  && $detail['deal_price'] > 0) {
            Db::startTrans();

            try {
                //本人发 = 总金额 - 上级分成
                $total = $detail['agent_reword'] * $detail['deal_price'] / 100;
                if($total <= 0){
                    throw new Exception("不需要发放的订单");
                }
                $buddies = MhcmsDistribution::group_buddy($agent['user_id']);
                $total_up_level_amount = 0;
                $i = 0;
                foreach ($buddies as $buddy) {

                    if ($buddy == $agent['user_id']) {


                    } else {

                        $_distribute_user = set_model('distribute_user')->where(['user_id' => $buddy, 'site_id' => $_W['site']['id']])->find();
                        if (!$_distribute_user) {
                            //hash not been a distribute
                            continue;
                        }

                        //todo load user
                        $target = Users::get(['id' => $buddy]);
                        $i++;
                        $_distribute_user_level = set_model('distribute_level')->where(['id' => $_distribute_user['level_id'], 'site_id' => $_W['site']['id']])->find();
                        $rate = $_distribute_user_level['commission_' . $i];
                        $commission = $total * $rate;
                        $total_up_level_amount += $commission;

                        if ($total_up_level_amount > 0) {
                            //todo create distribute orders
                            $distribute_order_data = [];
                            $distribute_order_data['user_id'] = $target['id'];
                            $distribute_order_data['site_id'] = $_W['site']['id'];
                            $distribute_order_data['amount'] = $commission;
                            $distribute_order_data['create_at'] = date("Y-m-d H:i:s", SYS_TIME);
                            $distribute_order_data['level'] = $i;
                            $distribute_order_data['module'] = ROUTE_M;
                            //
                            if (!$need_check_distribute_order) {
                                $distribute_order_data['pay_time'] = date("Y-m-d H:i:s", SYS_TIME);
                                $distribute_order_data['status'] = 99;
                            }

                            set_model("distribute_orders")->insert($distribute_order_data);


                            if (!$need_check_distribute_order) {
                                Money::deposit($target, $total_up_level_amount, 3, '房产订单成交分销佣金，编号：#' . $detail['id']);
                            }

                        }


                    }

                }

                $self_reword = $total - $total_up_level_amount;
                Money::deposit($self_user, $self_reword, 3, '房产订单成交佣金，编号：#' . $detail['id']);

                //todo update the agent reward status
                $update = [];
                $update['agent_reword_status'] = 1;
                $update['status'] = 99;
                $model->where($where)->update($update);
                // 提交事务
                Db::commit();
                $this->zbn_msg("发放奖励成功" , 1);
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->zbn_msg("发放奖励没有成功");
            }

        }else{
            $this->zbn_msg("发放奖励没有成功 ， 因为奖励设置不合法");
        }
    }

    public function employee_reward($id)
    {
        global $_W, $_GPC;
        $need_check_distribute_order = false;
        $model = set_model($this->house_appointment);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        $agent = set_model('house_agent')->where(['id' => $detail['employee_id']])->find();

        if ($detail['status'] != 99 || $detail['employee_reword_status'] == 1) {
            $this->zbn_msg("该订单状态不是已结佣状态 ，或者已经发放过奖励 ， 导致无法进行奖励的发放！");
        }

        if($agent['type'] != 2){
            $this->zbn_msg("内部员工状态异常，请检测该员工是否已经离职！");
        }
        //check if reward is set
        $self_user =Users::get(['id' =>$agent['user_id']]);

        if ($self_user && is_numeric($detail['employee_reword']) && is_numeric($detail['deal_price']) && $detail['deal_price']> 0) {
            Db::startTrans();

            try {
                //本人发 = 总金额 - 上级分成
                $total = $detail['employee_reword'] * $detail['deal_price'] / 100;
                if($total <= 0){
                    throw new Exception("不需要发放的订单");
                }
                $res1 = Point::deposit($self_user, $total, 3, '房产订单成交业绩，编号：#' . $detail['id']);

                //todo update the agent reward status
                $update = [];
                $update['employee_reword_status'] = 1;
                $res2 = $model->where($where)->update($update);
                // 提交事务

                if($res1 && $res2 == 1){
                    Db::commit();
                }else{
                    throw new Exception("something  went wrong!");
                }

                $msg = "发放奖励成功";
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $msg = "发放奖励没有成功";
            }

            $this->zbn_msg("$msg" );
        }else{
            $this->zbn_msg("发放奖励没有成功 ， 因为奖励设置不合法");
        }

        $this->zbn_msg("通过检测！");
    }

    public function delete($id){

        global $_W, $_GPC;
        $need_check_distribute_order = false;
        $model = set_model($this->house_appointment);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['site_id'] = $_W['site']['id'];
        $detail = $model->where($where)->find();
        if($detail){
            $detail = $model->where($where)->delete();
        }
        $this->zbn_msg("删除成功！");
    }
}