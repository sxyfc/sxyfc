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
namespace app\core\controller;

use app\common\controller\Base;
use app\common\model\Hits;
use app\common\model\Models;
use app\common\model\Users;
use app\core\util\MhcmsSegment;
use think\Db;

class CommonService extends Base
{


    public function str_to_qr($str)
    {
        str_to_qrcode(urldecode($str));
        exit();
    }

    public function load_seo($route)
    {
        global $_W;
        $where = [];
        $where['site_id'] = $_W['site']['id'];
        $where['seo_key'] = $route;
        $test = set_model('seo')->where($where)->find();

        if (!$test['seo_explain']) {
            unset($where['site_id']);
            $test = set_model('seo_tpl')->where($where)->find();
        }

        return [
            'code' => 1,
            'data' => $test
        ];
    }


    public function load_redbag_logs()
    {
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if (!is_array($query)) {
            $query = mhcms_json_decode($query);
        }
        $page = max(1, $query['page']);
        $detail = Models::get_item($query['id'], $query['model_id']);
        $red_bag = set_model('redbag')->where(['item_id' => $query['id'], 'model_id' => $query['model_id']])->find();
        $test_where = [];
        $test_where['redbag_id'] = $red_bag['id'];
        $test_where['user_id'] = ['GT', 0];

        $order = $query['order'] ? $query['order'] : 'got_at desc';
        if ($red_bag && $detail['old_data']['site_id'] == $_W['site']['id']) {
            //加载记录
            $red_bag_logs = set_model('redbag_logs')->where($test_where)->order($order)->page($page)->paginate(null, true)->toArray();

            foreach ($red_bag_logs['data'] as &$data) {
                $data['user'] = Users::get(['id' => $data['user_id']]);
            }
            $ret['code'] = 1;
            $ret['data'] = $red_bag_logs;
            echo json_encode($ret);
        }
    }

    public function search()
    {
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if (!is_array($query)) {
            $query = mhcms_json_decode($query);
        }
        $page = max(1 , $query['page']);
        $limit = max(10 , $query['limit']);
        $model_id = $query['module'] . "_" . $query['model_id'];
        $model = set_model($model_id);

        $_i_model_id = $model->model_info['id'];
        //todo search
        //split_world
        $keywords = MhcmsSegment::split_world($query['keyword']);

        if (strpos($keywords, " ") !== false) {
            $data_where_sql = "  MATCH (`data`) AGAINST ('$keywords' IN BOOLEAN MODE)";
        } else {
            $data_where_sql = " `data` like '%{$query['keyword']}%'";
        }

        $_index_where['model_id'] = $_i_model_id;
        $_index_where['site_id'] = $_W['site']['id'];
        $res = Db::view('mhcms_index')->where($_index_where)->order('id desc')
            ->view('mhcms_index_data', 'data', 'mhcms_index_data.id=mhcms_index.id')->where($data_where_sql)
            ->page($page)->limit($limit)->paginate()->toArray();


        foreach($res['data'] as &$item){
            $item = Models::get_item($item['item_id'] , $model_id);
        }
        $ret['data'] = $res;
        $ret['code'] = 1;

        return $ret;

    }
}