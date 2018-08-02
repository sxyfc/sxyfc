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
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\Users;
use app\common\model\UserMenu;
use app\common\model\UserMenuAccess;
use app\common\model\UserMenuAllot;
use app\common\model\UserRoles;
use think\Db;
use think\Log;

class AdminRole extends AdminBase
{
    public $user_roles = "user_roles";
    public $users = "users";
    public $user_access_model;
    public $user_allot_model;

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserRoles();
        $this->user_access_model = new UserMenuAccess();
        $this->user_allot_model = new UserMenuAllot();
    }

    /**
     * @param string $module
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function index($module = "")
    {
        //自定义筛选条件
        $where = [];
        //获取模型信息
        $model = set_model($this->user_roles);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $this->view->field_list = $model_info->get_admin_publish_fields();

        $site_ids = [];
        //data list 如果不是超级管理员 并且数据是区分站群的
        if ($this->super_power) {
            $site_ids[] = 0;
            $site_ids[] = $this->site['id'];
        } else {
            $site_ids[] = $this->site['id'];
        }

        if ($module) {
            $where['module'] = ['EQ', $module];
        }

        $where['site_id'] = ['IN', $site_ids];

        //----------------------
        $lists = $model->where($where)->order("id desc")->paginate();
        //列表数据
        $this->view->lists = $lists;
        //model_info
        $this->view->model_info = $model_info;
        //+--------------------------------以下为系统--------------------------
        //模板替换变量
        $this->mapping['module'] = $module;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    /**
     *  用户授权列表
     * @param string $module
     * @return string
     * @throws \Exception
     */
    public function user_auth($module = "")
    {
        $where = [];
        //获取模型信息
        $model = set_model($this->users);
        $model_info = $model->model_info;
        $this->view->field_list = $model_info->get_admin_publish_fields();

        //列表数据
        if (!$this->super_power) {
            $where['parent_id'] = ['EQ', $this->user['id']];
            $lists = $model->where($where)->order("id desc")->paginate();
        }else{
            $where['user_role_id'] = ['GT', 1];
            $lists = $model->where($where)->order("id desc")->paginate();
        }

        $this->view->lists = $lists;
        $this->view->model_info = $model_info;

        $this->mapping['module'] = $module;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public static function getAllChild($pid = 0, $level = 0, $menus_all = [])
    {
        global $_W, $_GPC;
        $menus = [];
        foreach ($menus_all as $menu) {

            if ($menu['user_menu_parentid'] == $pid) {
                $menu['title'] = $menu['user_menu_name'];
                $menus[] = $menu;
            }
        }
        $level++;
        foreach ($menus as $key => &$menu) {
            // 多语言
            $menu['name'] = zlang($menu['user_menu_name']);
            $menu['icon'] = $menu['user_menu_icon'];
            $menu['children'] = '';
            //$menu['url'] = nb_url(['r'=>$menu['user_menu_module'] ."." . $menu['user_menu_controller'] ."." . $menu['user_menu_action'] , $menu['user_menu_params'] ,'user_menu_id'=>$menu['user_menu_id']]);
            if (Db::name('user_menu')->where('user_menu_parentid = ' . $menu['id'])->find()) {
                //$menu['open'] = true;
                if ($level <= 2) {
                    $menu['open'] = true;
                }
                $menu['children'] = self::getAllChild($menu['id'], $level, $menus_all);
            }
        }
        return $menus;
    }

    public function authorize($role_id, $is_admin)
    {
        global $_W;
        $is_admin = (int)$is_admin;
        $this->user_access_model = new UserMenuAccess();
        $role_id = (int)$role_id;
        $detail = UserRoles::get(['id' => $role_id]);//detail role

        if ($is_admin && $detail['is_admin'] != 1) {
            //UserMenuAccess::where(['user_role_id' => $role_id])->delete();
            $this->message("后台授权只能是后台角色");
        }

        if ($role_id == 1) {
            $this->message("不能执行该操作");
        }

        $tpl = $is_admin ? "authorize_admin_ztree" : "authorize_user_ztree"; // user or admin

        $map['is_admin'] = $detail['is_admin'] = $is_admin;

        if (!$this->super_power) {
            //   $map = $this->map_fenzhan($map);
        }
        $this->check_admin_auth($detail);


        //当前菜单访问权限
        $formatted_admin_access = [];
        if (!$this->super_power) {
            $where_access = [];
            $where_access['user_role_id'] = $_W['admin_info']['role_id'];
            $current_admin_access = UserMenuAccess::all($where_access);
            $formatted_admin_access = [];
            foreach ($current_admin_access as $access) {
                $formatted_admin_access[$access['user_menu_id']] = $access;
            }
        }

        //target user role access
        $where_access = [];
        $where_access['user_role_id'] = $role_id;
        $current_target_access = UserMenuAccess::all($where_access);
        $formatted_target_access = [];
        foreach ($current_target_access as $access) {
            $formatted_target_access[$access['user_menu_id']] = $access;
        }


        if ($role_id && $detail) {
            $data = [];
            if ($this->isPost()) {
                $menu_ids = input('param.menu_ids');
                $menu_ids = explode(",", $menu_ids);

                //destroy old access we should separate the user  and admin menus
                //foreach the old access ,and delete the old access with the $is_admin group
                foreach ($formatted_target_access as $k => $v) {
                    $menu = UserMenu::get(['id' => $k]);
                    if ($menu['is_admin'] == $is_admin) {
                        //delete the same group : user  or admin
                        UserMenuAccess::where($v->toArray())->delete();
                    }
                }
                //check if current admin have the menus access

                foreach ($menu_ids as $val) {
                    if (!empty($val)) {
                        $_item = array(
                            'user_role_id' => $role_id,
                            'user_menu_id' => (int)$val,
                        );
                        //todo check if the current admin have the auth
                        if (!$this->super_power && !$formatted_admin_access[$val]) {
                            continue;
                        }
                        $data[] = $_item;
                    }
                }

                if ($this->user_access_model->saveAll($data)) {
                    $this->zbn_msg('operate success !', 1);
                } else {
                    $this->zbn_msg('operate success,但是您可能没有选择任何菜单 !', 1);
                }
            } else {
                //超级管理员列出所有
                if ($this->super_power) {
                    $menus = UserMenu::all($map);
                } else {
                    //可能拥有的菜单权限列出来
                    foreach ($current_admin_access as $access) {
                        $menu_ids[] = $access['user_menu_id'];
                    }
                    $map['id'] = ['IN', $menu_ids];
                    $menus = UserMenu::all($map);
                }

                $new_menus = [];
                foreach ($menus as $item) {
                    $new_menus[$item['id']] = $item;
                }

                //自动选取当前已经有的权限
                //format menu

                $newMenuIds = [];
                foreach ($formatted_target_access as $a) {
                    $newMenuIds[] = $a['user_menu_id'];
                    if (isset($new_menus[$a['user_menu_id']])) {
                        $new_menus[$a['user_menu_id']]['checked'] = true;
                    }
                }
                $formated_menus = self::getAllChild(0, 0, $new_menus);
                $this->assign('menuIds', $newMenuIds);
                $this->assign('role_id', $role_id);
                $this->assign('detail', $detail);
                $this->assign('menus', $formated_menus);
                return $this->view->fetch($tpl);
            }
        }
    }


    public function user_authorize_stage($user_id, $is_admin)
    {
        global $_W;
        $is_admin = (int)$is_admin;
        $this->user_allot_model = new UserMenuAllot();
        $user_id = (int)$user_id;
        $detail = Users::get(['id' => $user_id]);

//        if ($is_admin && $detail['is_admin'] != 1) {
//            $this->message("后台授权只能是后台角色");
//        }

        if ($detail['user_role_id'] == 1) {
            $this->message("不能执行该操作");
        }

        $tpl = $is_admin ? "auth_admin_ztree" : "auth_user_ztree";
        $map['is_admin'] = $is_admin;

        //当前菜单访问权限
        $formatted_admin_allot = [];
        if (!$this->super_power) {
            $where_manage = [];
            $where_manage['user_id'] = $this->user['id'];
            $current_admin_allot = UserMenuAllot::all($where_manage);
            $result_manage = $current_admin_allot->toArray();

            if(empty($result_manage)){
                $where_access['user_role_id'] = $_W['admin_info']['role_id'];
                $current_admin_allot = UserMenuAccess::all($where_access);
            }

            if($current_admin_allot){
                $formatted_admin_allot = [];
                foreach ($current_admin_allot as $allot) {
                    $formatted_admin_allot[$allot['user_menu_id']] = $allot;
                }
            }
        }

        //当前拥有的权限
        $is_user = 1;
        $where_allot = [];
        $where_allot['user_id'] = $user_id;
        $current_target_allot = UserMenuAllot::all($where_allot);
        $result_allot =  $current_target_allot->toArray();
        if(empty($result_allot)){
            $where_manage['user_id'] = $user_id;
            $current_target_allot = UserMenuAllot::all($where_manage);
            $result_manage =  $current_target_allot->toArray();

            if(empty($result_manage)){
                $is_user = 0;
                $where_access['user_role_id'] = $_W['admin_info']['role_id'];
                $current_target_allot = UserMenuAccess::all($where_access);
            }
        }

        $formatted_target_allot = [];
        foreach ($current_target_allot as $allot) {
            $formatted_target_allot[$allot['user_menu_id']] = $allot;
        }


        if ($user_id && $detail) {
            $data = [];
            if ($this->isPost()) {
                $menu_ids = input('param.menu_ids');
                $menu_ids = explode(",", $menu_ids);

                //destroy old access we should separate the user  and admin menus
                //foreach the old access ,and delete the old access with the $is_admin group
                foreach ($formatted_target_allot as $k => $v) {
                    $menu = UserMenu::get(['id' => $k]);
                    if ($menu['is_admin'] == $is_admin) {
                        //delete the same group : user or admin
                        if($is_user == 1){
                            UserMenuAllot::where($v->toArray())->delete();
                        }
                    }
                }

                foreach ($menu_ids as $val) {
                    if (!empty($val)) {
                        $_item = array(
                            'user_id' => $user_id,
                            'user_menu_id' => (int)$val,
                        );

                        if (!$this->super_power && !$formatted_admin_allot[$val]) {
                            continue;
                        }
                        $data[] = $_item;
                    }
                }

                if ($this->user_allot_model->saveAll($data)) {
                    $this->zbn_msg('operate success !', 1);
                } else {
                    $this->zbn_msg('operate success,但是您可能没有选择任何菜单 !', 1);
                }
            } else {
                $menu_ids = [];

                //超级管理员列出所有
                if ($this->super_power) {
                    $menus = UserMenu::all($map);
                } else {
                    //可能拥有的菜单权限列出来
                    foreach ($current_admin_allot as $allot) {
                        $menu_ids[] = $allot['user_menu_id'];
                    }
                    $map['id'] = ['IN', $menu_ids];
                    $menus = UserMenu::all($map);
                }

                $new_menus = [];
                foreach ($menus as $item) {
                    $new_menus[$item['id']] = $item;
                }

                //自动选取当前已经有的权限
                //format menu

                $newMenuIds = [];
                foreach ($formatted_target_allot as $a) {
                    $newMenuIds[] = $a['user_menu_id'];
                    if (isset($new_menus[$a['user_menu_id']])) {
                        $new_menus[$a['user_menu_id']]['checked'] = true;
                    }
                }
                $formated_menus = self::getAllChild(0, 0, $new_menus);
                $this->assign('menuIds', $newMenuIds);
                $this->assign('user_id', $user_id);
                $this->assign('detail', $detail);
                $this->assign('menus', $formated_menus);
                return $this->view->fetch($tpl);
            }
        }

    }
    /**
     * @param $role_id
     * @return string
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function authorize_old($role_id)
    {
        $role_id = (int)$role_id;
        $detail = UserRoles::get(['id' => $role_id]);//detail role
        $tpl = $detail['is_admin'] == 1 ? "authorize_admin" : "authorize_user"; // user or admin
        $map['is_admin'] = $detail['is_admin'] == 1;
        if (!$this->super_power) {
            $map = $this->map_fenzhan($map);
        }
        $this->check_admin_auth($detail);
        if ($role_id && $detail) {
            $data = [];
            if ($this->isPost()) {
                $menu_ids = input('param.menu_id/a');
                $menu_ids = $menu_ids ? $menu_ids : [];
                UserMenuAccess::where('user_role_id', '=', $role_id)->delete();
                foreach ($menu_ids as $val) {
                    if (!empty($val)) {
                        $data[] = array(
                            'user_role_id' => $role_id,
                            'user_menu_id' => (int)$val,
                        );
                    }
                }
                if ($this->user_access_model->saveAll($data)) {
                    $this->zbn_msg('operate success !', 1);
                }
            } else {
                //可能拥有的菜单权限列出来
                $menus = UserMenu::where($map)->select();
                $this->assign('menus', $menus);
                $new['user_role_id'] = $role_id;
                $current_access = UserMenuAccess::all($new);
                //自动选取当前已经有的权限
                $newMenuIds = [];
                foreach ($current_access as $a) {
                    $newMenuIds[] = $a['user_menu_id'];
                }
                $this->assign('menuIds', $newMenuIds);
                $this->assign('role_id', $role_id);
                $this->assign('detail', $detail);
                return $this->view->fetch($tpl);
            }
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function create($module = "")
    {
        global $_GPC;
        //后去模型信息
        $model = set_model($this->user_roles);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $model_info->bind_module = $module ? $module : ROUTE_M;
        //手动处理类型的模型
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            if ($module) {
                $base_info['module'] = $module;
            }
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            //模板数据

            $this->view->list = $model_info->get_admin_publish_fields([], true);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function edit($id)
    {
        global $_GPC;
        $id = (int)$id;
        $model = set_model($this->user_roles);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        $model_info->bind_module = $detail['module'] ? $detail['module'] : ROUTE_M;


        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
            }
            // todo  process data input
            $res = $model_info->edit_content($data, $where);
            if ($res['code'] == 1) {
                $this->zbn_msg("ok");
            } else {
                $this->zbn_msg($res["msg"]);
            }
        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        if ($id <= 4) {
            $data['code'] = 0;
            $data['msg'] = 'this can\'t be deleted';
            return $data;
        }
        $role = UserRoles::get(['id' => $id]);
        $this->check_admin_auth($role);
        // move the role's user to default group
        $res = Db::name('users')->where(['user_role_id' => $id])->update(['user_role_id' => 2]);
        // remove menu access auth
        $res = Db::name('user_menu_access')->where(['user_role_id' => $id])->delete();
        $role->delete();
        $data['code'] = 1;
        $data['msg'] = "操作完成";
        return $data;
    }
}