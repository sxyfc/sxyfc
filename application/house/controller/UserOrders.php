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
use app\core\util\ContentTag;
use app\core\util\MhcmsMenu;

class UserOrders extends HouseUserBase
{

    private $house_appointment = "house_appointment";


    public function index($status = 0)
    {
        $where = [];
        $where['agent_id'] = $this->agent['id'];
        if ($status) {
            $where['status'] = $status;
        }


        $this->view->status_options = ContentTag::load_options("house_appointment", 'status');

        $this->view->appointments = set_model($this->house_appointment)->where($where)->paginate();

        $this->view->status = $status;
        return $this->view->fetch();
    }

    public function create()
    {

        if (!$this->agent) {
            $this->error("对不起，您还不是经济人，先去申请一下吧");
        }

        $appoint_model = set_model($this->house_appointment);
        $model_info = $appoint_model->model_info;

        if ($this->isPost(true)) {

            $base_info = input('param.data/a');
            $res = $model_info->add_content($base_info);
            $base_info['user_id'] = 0; // 清空uid
            $base_info['agent_id'] = $this->agent['id'];
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'] . " 感谢您信息，我们将尽快处理，祝您生活愉快！", 1, 'true', 1000, "'/house/user'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_user_publish_fields();
            return $this->view->fetch();
        }


    }

}