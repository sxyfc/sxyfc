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

use app\common\controller\HomeBase;
use app\common\controller\ModuleBase;
use app\core\util\ContentTag;
use think\Log;

class Weituo extends HouseBase
{
    public function create($type)
    {

        $model = set_model("house_weituo");
        $model_info = $model->model_info;
        if ($this->isPost(true)) {

        } else {
            $ext['title'] = $type == 1 ? "我要出售" : "我要出租";
            $this->mapping = array_merge($this->mapping, $ext);
            $this->view->seo = $this->seo($this->mapping);
            $this->view->type = $type;
            return $this->view->fetch();
        }

    }

    public function esf_buy()
    {
        $this->mapping['title'] = "我要买房子";
        //todo

        $model = set_model("house_weituo");
        $model_info = $model->model_info;
        if ($this->isPost(true)) {
            $base_info = input('post.data/a');
            $base_info['type'] = 2;
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                token();
                $this->zbn_msg("申请成功，感谢您的信任,我们工作人员会尽快联系您!！", "1", '', 2000, "''");
            } else {
                ;
                $this->zbn_msg("失败 ！" . $res['msg']);
            }
        } else {
            $this->view->seo = $this->seo($this->mapping);
            $this->view->fields = $model_info->get_user_publish_fields();
            return $this->view->fetch();
        }


    }

    public function rent_add()
    {
        $ext['title'] = "我要租房";
        $this->mapping = array_merge($this->mapping, $ext);
        $this->view->seo = $this->seo($this->mapping);
        return $this->view->fetch();
    }


    public function index()//默认求租
    {
        //求租\求购
        $house_info = 'house_info';
        //出租出售
        $house_weituo = 'house_weituo';

        $type = $_POST['type'];
        $model = set_model($house_info);
        $where = [];
        $where['type'] = 2;
        $where['user_id'] = $this->user['id'];
        Log::error($where['user_id']);
        if ($type == 1) {//出租
            $model = set_model($house_weituo);
            $where['type'] = 2;
        } elseif ($type == 2) {//出售
            $model = set_model($house_weituo);
            $where['type'] = 1;
        } elseif ($type == 3) {//求租
            $model = set_model($house_info);
            $where['type'] = 2;
        } elseif ($type == 4) {//求购
            $model = set_model($house_info);
            $where['type'] = 1;
        }

        $list = $model->where($where)->select()->toArray();
        $this->assign("list", $list);
        Log::error($list);
        $this->assign("type", $type);
        return $this->view->fetch();
    }
}