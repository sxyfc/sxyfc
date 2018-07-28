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
namespace app\common\controller;

use think\Db;

class UserBase extends ModuleBase
{
    public $user_id, $user_role_id, $user_user_name, $user_role, $current_menu;

    public function _initialize()
    {
        global $_W;
        parent::_initialize();

        if (empty($this->user)) {
            $for = $_W['current_url'];

            if (empty($_W['global_config']['sso_domain'])) {
                $sso_domain = $_SERVER['HTTP_HOST'];
            } else {
                $sso_domain = $_W['global_config']['sso_domain'];
            }
            if($sso_domain=="www.zxw.bz"){
            //    $sso_domain = $_SERVER['HTTP_HOST'];
            }
            if (is_weixin() && $_W['site']['config']['force_wechat']) {
                $url = SITE_PROTOCOL . $sso_domain . "/sso/passport/wx_login?to_site_id=" . $this->site['id'] . "&forward=" . $for;
                //header("location://" . $sso_domain . "/sso/passport/wx_login?site_id=" . $this->site['id'] . "&forward=" . $for);
            } else {

                $url = SITE_PROTOCOL . $sso_domain . "/sso/passport/login?to_site_id=" . $this->site['id'] . "&forward=" . $for;
                // header("location://" . $sso_domain . "/sso/passport/login?site_id=" . $this->site['id'] . "&forward=" . $for);
            }

            if (!$this->request->isAjax() || $_W['pjax']) {
                header("location:$url");
            } else {
                $ret['code'] = 3;
                $ret['msg'] = "请先登录";
                $ret['url'] = $url;
                echo json_encode($ret);
            }

            exit();
        }
        /**
         *  in what ever situation ,we should always check that if this user is disabled
         */
        if ($this->user['status'] != 99 || !$this->user_role['status']) {
            $this->error("对不起 您已经被系统禁止登录，或者您所在的分组被禁用！您当前角色为" . $this->user_role['role_name']);
        }
        /**
         * load sub menu if there is any code
         */
        $map = array(
            'user_menu_parentid' => $this->request->param('user_menu_id')
        );
        $sub_menus = Db::name('user_menu')->where($map)->order('user_menu_listorder asc')->select();
        /**
         * parse the params
         */
        foreach ($sub_menus as $k => $sub_menu) {
            $sub_menu['user_menu_params'] = parseParam($sub_menu['user_menu_params'], $this->mapping);
        }
        $this->view->assign("sub_menu", $sub_menus);
        $this->view->assign("user", $this->user);
    }


    public function map_fenzhan($map_old = [])
    {
        $map = [];
        return array_merge($map, $map_old);
    }

    public function check_user_auth($data)
    {
        global $_W;
        /**
         * 如果这个数据是该用户的才可以进行操作
         */
        if ($this->user['id'] != $data['user_id']) {
            $this->zbn_msg("权限检测失败", 0);
        } else {

        }
    }
}