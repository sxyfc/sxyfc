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
namespace app\advertise\controller;

use app\advertise\model\Advertise;
use app\common\controller\AdminBase;
use app\common\model\Models;
use think\Db;

class AdminService extends AdminBase
{

    public function rec_position_data($model_id , $rec_ids , $position_ids){
        global $_W;
        $model = set_model($model_id);
        $position_data_db = set_model('position_data');
        $where = [];
        foreach($position_ids as $position_id){
            $position = set_model('position')->where(['id' => $position_id , 'site_id' => $_W['site']['id']])->find();

            if(!$position){
                continue;
            }
            //count position data amount
            $count = $position_data_db->where(['position_id' => $position_id])->count();


            $i = 0;
            foreach($rec_ids as $k=>$rec_id){
                //todo if the data is already in this position
                $where = [];
                $where['item_id'] = $rec_id;
                $where['model_id'] = $model->model_info['id'];
                $where['site_id'] = $_W['site']['id'];
                $where['position_id'] = $position_id;
                $test = $position_data_db->where($where)->find();
                if($test){
                    unset($rec_ids[$k]);
                    $i ++ ;
                }
                if($i > $position['maxnum']){
                    break;
                }
            }
            //to delete amount
            $amount_to_delete = $position['maxnum'] - $count - count($rec_ids) + $i;
            $res = 0;
            if($amount_to_delete < 0){
                $amount_to_delete = abs($amount_to_delete);
                $res = $position_data_db->where(['position_id' => $position_id])->order('listorder asc')->limit($amount_to_delete)->delete();
            }

            $j = 1;
            foreach($rec_ids as $rec_id){
                    if($count - $res + $j >  $position['maxnum']){
                        continue;
                    }else{
                        $where = [];
                        $where['item_id'] = $rec_id;
                        $where['model_id'] = $model->model_info['id'];
                        $where['site_id'] = $_W['site']['id'];
                        $where['position_id'] = $position_id;

                        $position_data_db->insert($where);
                        $j++;
                    }
            }
        }

        $ret['code'] = 1;
        $ret['msg'] = "操作完成";
        return $ret;
    }

}