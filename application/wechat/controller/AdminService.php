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

namespace app\wechat\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserMenu;
use app\common\util\Tree2;
use app\wechat\util\MhcmsWechatAccountBase;
use app\wechat\util\MhcmsWechatEngine;
use think\Db;

class AdminService extends AdminBase
{
    public function update_fans_group($id, $group_id, $operation)
    {
        global $_W, $_GPC;

        $account_api = MhcmsWechatEngine::create($_W['account']);
        $group = set_model("sites_wechat_fansgroup")->where(['id'=>$group_id])->find();
        $fans = $_W['wechat_fans_model']->where(['id' => $id])->find();
        if ($fans) {
            $openid = $fans['openid'];
            $ids =array_filter( explode(",", $fans['group_ids']));
            if ($operation == "true") {
                if (!array_search($group['wechat_tag_id'], $ids)) {
                    $ids[] = $group['wechat_tag_id'];
                }
                $result = $account_api->fansTagTagging($openid, [$group['wechat_tag_id']]);
            }
            if ($operation == "false") {
                $index = array_search($group['wechat_tag_id'], $ids);
                if ($index !== false) {
                    unset($ids[$index]);
                }
                $account_api->fansTagBatchUntagging(array($id), $group['wechat_tag_id']);
            }
        }
        if (is_array($ids) && count($ids) > 0) {
            $group_ids = "," . join(",", $ids) . ",";
        }else{
            $group_ids = "";
        }

        $_W['wechat_fans_model']->where(['openid' => $openid])->update(['group_ids'=>$group_ids]);

        test($group_ids);
    }


    public function download_fans($next_open_id = "")
    {
        global $_W, $_GPC;
        $account_api = MhcmsWechatEngine::create($_W['account']);
        $wechat_fans_list = $account_api->fansAll($next_open_id);

        $model = $_W['wechat_fans_model'];
        if(empty($wechat_fans_list['total'])){
            return $wechat_fans_list;
        }else{

            $wechat_fans_list['code'] = 1;
            foreach($wechat_fans_list['fans'] as $fans){
                $test = $model->where(['openid'=>$fans])->find();
                if(!$test){
                    $model->insert(['openid'=>$fans]);
                }
            }
            return $wechat_fans_list;
        }

    }


    public function list_material($material_type , $data = 0)
    {
        global $_W  ,$_GPC;

        $this->view->material_type = $material_type;
        if($data==1){
            $this->content_model_id = "sites_wechat_material";
            /**过滤字段*/
            $_GPC['material_type'] = $material_type;
            $ret = Models::gen_admin_filter($this->content_model_id, $this->menu_id);
            //自定义筛选条件
            $where = $ret['where'];
            $this->view->filter_info = $ret;
            //获取模型信息
            $model = set_model($this->content_model_id);
            /** @var Models $model_info */
            $model_info = $model->model_info;

            //data list 如果不是超级管理员 并且数据是区分站群的
            if (Models::field_exits('site_id', $this->content_model_id)) {
                $where['site_id'] = $this->site['id'];
            }
            $where['parent_id'] = 0;
            //分配到当前模块
            if (Models::field_exits('module', $this->content_model_id)) {
                $where['module'] = ROUTE_M;
            }
            $keyword = input('param.keyword');
            if ($keyword && $model_info['search_keys']) {
                $search_keys = str_replace(",", "|", $model_info['search_keys']);
                $model = $model->where($search_keys, 'like', "%$keyword%");
                $this->view->keyword = $keyword;
            }
            $lists = $model->where($where)->order("createtime desc")->paginate()->toArray();
            //todo update data for render
            foreach ($lists['data'] as &$item){
                if($item['tag']){
                    $item['tag'] = mhcms_json_decode($item['tag']);
                    $item['title'] = $item['tag']['title'];
                }

                if($item['url'] && $item['material_type'] == 'image'){
                    $item['image'] = "<img src='".url('wechat/service/image' , ['url' => urlencode($item['url'])])."' />";
                }
            }

            $lists['code']  =1;
            return $lists;
        }else{
            return $this->view->fetch("material_".$material_type);
        }


    }


}