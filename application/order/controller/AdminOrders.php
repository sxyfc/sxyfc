<?php
namespace app\order\controller;

use app\common\controller\AdminBase;
use app\common\model\Modules;
use app\common\model\NodeFields;
use app\common\model\NodeTypes;
use app\common\model\Sites;
use app\order\model\Orders;
use app\order\model\OrdersLogs;
use think\Db;

class AdminOrders extends AdminBase
{

    public function index($module = "", $status = "")
    {

        $where = [];

        if (is_numeric($status)) {
            $where['status'] = $status;
        }

        if ($module) {
        //    $where['module'] = $module;
        }
        $keyword = input("param.keyword");

        if($keyword){
            $where['order_id'] = $keyword;
        }

        $order_model = new Orders();
        $this->view->lists = $order_model->where($where)->paginate();
        return $this->view->fetch();
    }

    public function view_logs($order_id){

        $order_log = new OrdersLogs();
        $order_logs = $order_log->where(['order_id'=>$order_id])->select();

        $this->view->lists = $order_logs;
        return $this->view->fetch();
    }


    public function log_add($order_id){
        $order = Orders::get(['order_id'=>$order_id]);

        if($this->isPost()){

            if($order){
                $insert = input('param.data/a');
                $insert['order_id'] = $order_id;
                $insert['create_time'] = NOW;
                $insert['user_id'] = $this->admin_id;
                OrdersLogs::create($insert);
                $this->zbn_msg("操作完成");
            }else{
                $this->zbn_msg("操作失败");
            }
        }else{
            return $this->view->fetch();
        }


    }

    public function close($order_id){
        $order = Orders::get(['order_id' =>$order_id ]);
        $cancle_data = json_decode( input('param.data') , true) ;
        $cancle_data['order_id'] = $order_id;
        $cancle_data['create_time'] = date("Y-m-d H:i:s");
        $cancle_data['user_id'] = $this->admin_id;
        $cancle_data['description'] .= "管理员后台操作";
        if($this->isPost()){
            if($order['status'] != "已完成"){
                $res = $order->cancle($order->seller_user_id);
                if($res){
                    OrdersLogs::create($cancle_data);
                    $ret = [
                        'code' => 0 ,
                        'msg' => '订单取消成功'
                    ];
                }else{
                    $ret = [
                        'code' => 0 ,
                        'msg' => '订单取消失败'
                    ];
                }
                return $ret;
            }else{
                $ret = [
                    'code' => 0 ,
                    'msg' => '订单取消失败 ， 因为订单已经完成'
                ];
                return $ret;
            }
        }else{
            return $this->view->fetch();
        }
    }
}