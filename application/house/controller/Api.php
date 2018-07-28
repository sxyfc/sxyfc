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

use app\common\controller\ApiBase;
use app\common\controller\ApiUserBase;
use app\common\controller\Base;
use app\common\model\Models;
use app\common\util\Money;
use app\common\util\Point;
use app\core\util\ContentTag;
use think\Controller;
use think\Db;
use think\Exception;

class Api extends ApiUserBase
{
    public function my_esf_list(){
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if(!is_array($query)){
            $query = mhcms_json_decode($query);
        }
        $model_id = 'house_esf';
        $model = set_model($model_id);
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['user_id'] = $this->user['id'];

        $res = $model->field('id')->where($where)->order('is_top desc,update_at desc')->paginate(null, true, ['page' => $query['page']])->toArray();

        foreach ($res['data'] as &$data) {
            $data = Models::get_item($data['id'], $model_id);
        }

        $ret['data'] = $res;
        $ret['code'] = 1;

        echo json_encode($ret);
    }

    public function my_info_list(){
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if(!is_array($query)){
            $query = mhcms_json_decode($query);
        }
        $model_id = 'house_info';
        $model = set_model($model_id);
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['user_id'] = $this->user['id'];

        $res = $model->field('id')->where($where)->order('update_at desc')->paginate(null, true, ['page' => $query['page']])->toArray();

        foreach ($res['data'] as &$data) {
            $data = Models::get_item($data['id'], $model_id);
        }

        $ret['data'] = $res;
        $ret['code'] = 1;

        echo json_encode($ret);
    }



    public function my_rent_list(){
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if(!is_array($query)){
            $query = mhcms_json_decode($query);
        }
        $model_id = 'house_rent';
        $model = set_model($model_id);
        $where = [];
        $where['site_id'] =  $_W['site']['id'];
        $where['user_id'] = $this->user['id'];

        $res = $model->field('id')->where($where)->order('is_top desc,update_at desc')->paginate(null, true, ['page' => $query['page']])->toArray();

        foreach ($res['data'] as &$data) {
            $data = Models::get_item($data['id'], $model_id);
        }

        $ret['data'] = $res;
        $ret['code'] = 1;

        echo json_encode($ret);
    }

    public function refresh_item(){
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if(!is_array($query)){
            $query = mhcms_json_decode($query);
        }
        $model_id = "house_" . $query['model_id'];
        $id = $query['id'];
        $model = set_model($model_id);
        $model_info = $model->model_info;
        $res = $model_info::get_item($id , $model_id , ['user_id' => $this->user['id']]);


        if($_W['module_config']['refresh_gap'] && time() - strtotime($res['update_at'])  < $_W['module_config']['refresh_gap'] * 60){
            $ret['code'] = 1;
            $ret['msg'] = "您好 暂时还不能刷新！！";
            echo json_encode($ret) ;die();
        }
        if($res){
            $update = [];
            $update['update_at'] = date("Y-m-d H:i:s");
            $model->where(['id'=>$id])->update($update);
        }
        $ret['code'] = 1;
        $ret['msg'] = "刷新成功！";
        echo json_encode($ret) ;die();
    }

    public function delete_item(){
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if(!is_array($query)){
            $query = mhcms_json_decode($query);
        }
        $model_id = "house_" . $query['model_id'];
        $id = $query['id'];
        $model = set_model($model_id);
        $res = $model->model_info->delete_item($id , $model_id , $this->user['id']);

        if($res){
            $ret['code'] = 1;
            $ret['msg'] = "删除完成！";
        }else{
            $ret['code'] = 2;
            $ret['msg'] = "删除失败！";
        }
        echo json_encode($ret) ;die();
    }

    public function do_top_item(){
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if(!is_array($query)){
            $query = mhcms_json_decode($query);
        }
        $model_id = "house_" . $query['model_id'];

        if(!$query['top_set_index']){
            echo json_encode(['code'=>2 , 'msg' => '请选择一个套餐']) ;die();
        }

        if(!$query['model_id'] || !$query['top_item_id']){
            echo json_encode(['code'=>2 , 'msg' => '请选择一个信息']) ;die();
        }

        $_W['module_config']['top_set'] = $_W['module_config']['top_set'] ? $_W['module_config']['top_set'] : [];
        $set = [];
        foreach($_W['module_config']['top_'.$query['model_id'].'_set'] as $k=>$v){
            if($k == $query['top_set_index']){
                $set = $v;
                break;
            }
        }


        if(!$set){
            echo json_encode(['code'=>2 , 'msg' => '对不起系统忙 请稍后']) ;die();
        }

        //todo

        Db::startTrans();
        $base_info['top_days'] = $set['days'];
        $base_info['update_at'] = date('Y-m-d H:i:s' , SYS_TIME);
        $base_info['is_top'] = 1;
        //top expire top_expire
        $base_info['top_expire'] = date("Y-m-d H:i:s" , SYS_TIME + 86400 * $base_info['top_days']);
        if($set['unit_type'] == "balance"){
            $spend_top = Money::spend($this->user , $set['money']  , 0 , "置顶信息");
        }else{
            $spend_top = Point::spend($this->user , $set['money']  , 0 , "置顶信息");
        }
        if(!$spend_top){
            $spend_top_msg = ' 置顶失败 您' . $_W['site']['config']['trade'][$set['unit_type'].'_text'] . '不足';
        }

        $res = set_model($model_id)->where(['id'=>(int)$query['top_item_id']])->update($base_info);

        if($res && $spend_top){

            Db::commit();
            echo json_encode(['code'=>1 , 'msg' => '置顶成功']) ;die();
        }else{

            Db::rollback();
            echo json_encode(['code'=>2 , 'msg' => '对不起系统忙 请稍后']) ;die();
        }
    }

    /**
     * 审核信息
     */
    public function check_item(){


    }
}