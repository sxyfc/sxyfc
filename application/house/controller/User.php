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

class User extends HouseUserBase
{
    public function index()
    {

        $this->view->seo = $this->seo($this->mapping);

        $menu = new MhcmsMenu();
        $this->view->menus = $menu->get_member_module_menu("house");
        return $this->view->fetch();
    }

    /**
     * 看房记录
     */
    public function kanfang_log()
    {
        global $_W, $_GPC;
        if (!$this->user['is_mobile_verify']) {
            $url = url('member/info/set_mobile', ['forward' => '/house/user/index']);
            $this->message("您好,您必须完善手机号码才能查看看房记录!", 1, $url);
        }

        //find appointment
        $mobile = is_phone($this->user['mobile']) ? $this->user['mobile'] :  $this->user['user_name'];
        $where['mobile'] =$mobile;
        $where['site_id'] = $_W['site']['id'];
        $appointment = set_model('house_appointment')->where($where)->find();

        $log_where = [];
        $log_where['log_type'] = 1;
        $log_where['appointment_id'] = $appointment['id'];
        $this->view->logs = set_model('house_appointment_log')->where($log_where)->select();

        return $this->view->fetch();
    }
}
