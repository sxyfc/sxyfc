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
namespace app\common\model;

use think\Model;

class Hits extends Model
{

    public static function get_hit($id, $model_id)
    {
        global $_W, $_GPC;
        if (!is_numeric($model_id)) {
            $model = set_model($model_id);
            $model_info = $model->model_info;
            $model_id = $model_info['id'];
        }
        $id = (int)$id;
        if ((preg_match('/([^a-z0-9_\-]+)/i', $model_id))) exit('1');
        $where = array('item_id' => $id, "model_id" => $model_id);
        $r = self::get($where);
        if (!$r) {
            $sql_data['item_id'] = $id;
            $sql_data['model_id'] = $model_id;
            $sql_data['site_id'] = $_W['site']['id'];
            $sql_data['update_at'] = SYS_TIME;
            if( $_W['site']['config']['hit']['hit_base']){
                $sql_data['base'] = rand(0 , 10);
            }
            $r = Hits::create($sql_data);
            $r = self::get($where);
        }else{
            if($r['base'] < $_W['site']['config']['hit']['hit_base']){
                $r->base = $r['base'] + rand(0 , 10);
                $r->save();
            }
        }
        return $r;
    }

    public static function hit($id, $model_id, $user_id = 0)
    {
        global $_W, $_GPC;
        $id = (int)$id;
        if (!$id) {
            return false;
        }
        if ((preg_match('/([^a-z0-9_\-]+)/i', $model_id))) exit('1');
        if (!is_numeric($model_id)) {
            $model = set_model($model_id);
            $model_info = $model->model_info;
            $model_id = $model_info['id'];
        }

        $r = self::get_hit($id, $model_id);
        //流量数据
        $views = isset($r['views']) && $r['views'] ? $r['views'] +1  :  1;
        $yesterdayviews = (date('Ymd', $r['update_at']) == date('Ymd', strtotime('-1 day'))) ? $r['today'] : $r['yesterday'];
        $dayviews = (date('Ymd', $r['update_at']) == date('Ymd', SYS_TIME)) ? ($r['today'] + 1) : 1;
        $weekviews = (date('YW', $r['update_at']) == date('YW', SYS_TIME)) ? ($r['week'] + 1) : 1;
        $monthviews = (date('Ym', $r['update_at']) == date('Ym', SYS_TIME)) ? ($r['month'] + 1) : 1;
        $sql_data = array('views' => $views, 'yesterday' => $yesterdayviews, 'today' => $dayviews, 'week' => $weekviews, 'month' => $monthviews, 'update_at' => SYS_TIME);

        $r->save($sql_data);
        //todo update views

        set_model($model_id)->where(['id'=>$id])->update(['views'=>$views]);
        return $r;
    }


    public static function zan($id, $model_id, $user_id)
    {
        $r = self::get_hit($id, $model_id);
        //是否攒过了
        $where_zan = array('hits_id' => $r['id']);
        $where_zan['user_id'] = $user_id;
        $zan_test = set_model("hits_likes")->where($where_zan)->find();
        //没赞过
        if (!$zan_test) {
            //点赞目标
            $insert_zan['user_id'] = $user_id;
            $insert_zan['create_at'] = date("Y-m-d H:i:s");
            $insert_zan['hits_id'] = $r['id'];
            set_model("hits_likes")->insert($insert_zan);
            $sql_data['likes'] = $r['likes'] + 1;
            $is_good = true;
        } else {
            set_model("hits_likes")->where($where_zan)->delete();
            //攒过  取消点赞
            $sql_data['likes'] = $r['likes'] - 1;
            $is_good = false;
        }
        $r['is_good'] = $is_good;
        return $r;
    }
}
