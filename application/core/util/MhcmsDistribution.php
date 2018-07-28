<?php

namespace app\core\util;

use app\common\model\Models;
use app\common\model\Users;
use app\sms\model\Notice;
use think\Db;
use think\Exception;

class MhcmsDistribution
{

    /**
     * init the distribute
     */
    public static function init()
    {
        //read config
        global $_GPC, $_W;
        // 2018-06-01 分销配置 移动到根域名全局配置
        $_W['distribute'] = $_W['global_config']['distribute'];

        if (!isset($_W['distribute']['status']) || $_W['distribute']['status'] != 1) {
            //未开启
            return;
        } else {
            //开启 模式
            if ($_W['distribute']['mode'] === "0") {
                //self::apply(99);//auto apply
            }
        }
    }


    public static function apply($status = 0 , $data = [])
    {
        global $_W;
        if($_W['distribute']['mode'] === "0"){
            $status = 99;
        }
        if (!isset($_W['distribute']['status']) || $_W['distribute']['status'] != 1) {
            //未开启
            return;
        }
        $distribute_user_model = set_model('distribute_user');
        $agent = self::is_agent($_W['user']['id']);
        if ($agent) {
            if ($status == 99 && $agent['status'] == 0) {
                $update = [];
                $update['approve_at'] = date("Y-m-d H:i:s");
                $update['status'] = "99";
                $res = $distribute_user_model->model_info->edit_content($update , ['user_id' => $_W['user']['id']]);
                return $res;
            }
        } else {
            //todo load default level
            $distribute_level_model = set_model('distribute_level');
            $default_level = $distribute_level_model->where(['default' => 1])->find();

            if ($default_level) {
                $insert = [];
                $insert['user_id'] = $_W['user']['id'];
                $insert['create_at'] = date("Y-m-d H:i:s");
                if ($status == 99) {
                    $insert['approve_at'] = $insert['create_at'];
                }
                $insert['status'] = $status;
                $insert['site_id'] = $_W['site']['id'];
                $insert['level_id'] = (int)$default_level['id'];
                $insert = array_merge($insert , $data);
                //$insert['real_name'] = $insert['mobile'] = "-";
                $res = $distribute_user_model->model_info->add_content($insert);

                if($res['code']==1 && $status == 99){
                    //todo send tpl msg to the user
                    $wechat_fans = $_W['wechat_fans_model']->where(['user_id' => $_W['user']['id']])->find();
                    $ret['msg'] = "操作完成";
                    $tpl_config = mhcms_json_decode($_W['tpl_config']);
                    unset($tpl_config['miniprogram']);
                    $params['header'] = "您好，恭喜您成为网站合伙人!分享网址或者海报即可获得永久不断的收益!";
                    $tpl_config['tp_url'] = url('member/distribute/link',[], true, true);
                    Notice::send('系统通知', 'wxmsg', $wechat_fans['openid'], $params, $tpl_config);
                }
                return $res;
            }

        }
    }

    public static function is_agent($user_id)
    {
        $distribute_user_model = set_model('distribute_user');
        $agent = $distribute_user_model->where(['user_id' => $user_id, 'status' => "99"])->find();
        if ($agent) {
            return $agent;
        } else {
            return false;
        }
    }

    public static function make_down_line($user_id, $parent_id)
    {
        global $_W;
        if (!self::is_agent($parent_id)) {

            return false;
        }else{
            $data = [];
            $data['parent_id'] = $parent_id;
            if ($user_id && $parent_id && $user_id != $parent_id) {
                $user = Users::get(['id'=>$user_id]);
                $user->parent_id = $parent_id;
                $user->save();
                // send msg to the parent user

                try{
                    $wechat_fans = $_W['wechat_fans_model']->where(['user_id' =>$parent_id ])->find();
                    $ret['msg'] = "操作完成";
                    $tpl_config = mhcms_json_decode($_W['tpl_config']);
                    unset($tpl_config['miniprogram']);
                    $params['header'] = "有一个用户通过您的推荐成为了本站会员，恭喜您！" ;
                    //$tpl_config['tp_url'] = url('info/index/detail', ['id' => $query['id']], true, true);
                    $res = Notice::send('系统通知', 'wxmsg', $wechat_fans['openid'], $params, $tpl_config);
                }catch (Exception $e){


                }
            }
        }

    }


    public static function group_buddy($user_id, $uids = [], $l = 0)
    {
        $base = set_model('users');
        $where = ['id' => $user_id];
        $user = $base->field('id,parent_id')->where($where)->find();

        if ($user) {
            $uids[$user_id] = $user_id;

            if (count($uids) <= $l) {
                $where = ['user_id' => $user['parent_id']];
                $parent_user = $base->where($where)->find();

                if ($parent_user) {
                    $uids[$parent_user['id']] = $parent_user['id'];
                    return self::group_buddy($parent_user['id'], $uids, $l);
                }
            }
        }
        return $uids;
    }
}