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
use app\common\model\Node;
use app\common\model\NodeTypes;
use app\common\model\UserRoles;
use app\common\model\Users;
use think\Db;
use think\Log;

class User extends AdminBase
{
    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Users();
    }

    public function index($role_id = 0)
    {
        $this->view->filter_info = Models::gen_admin_filter("users", $this->menu_id);
        $where = $this->view->filter_info['where'];
        $role_id = (int)$role_id;
        $user_name = trim(input('param.user_name', ' ', 'htmlspecialchars'));
        $nickname = trim(input('param.nickname', ' ', 'htmlspecialchars'));
        $roleId = trim(input('param.role_id', ' ', 'htmlspecialchars'));
        if ($user_name) {
            $where['user_name'] = array('LIKE', '%' . $user_name . '%');
        }
        if ($nickname) {
            $where['nickname'] = array('LIKE', '%' . $nickname . '%');
        }
        if ($role_id) {
            $where['user_role_id'] = (int)$role_id;
        }

        if ($roleId) {
            $this->view->assign('roleId', $roleId);
            $where['user_role_id'] = $roleId;
        }

        if (!$this->super_power) {
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                $ids = $this->map_city_childs($this->user['id']);
                $where_role['id'] = array('IN', [2, 4, 23, 24, 27]);
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $ids = $this->map_county_childs($this->user['id']);
                $where_role['id'] = array('IN', [2, 4, 24, 27]);
            } elseif ($users['user_role_id'] == 25) {
                $ids = $this->map_area_childs($this->user['id']);
                $where_role['id'] = array('IN', [2, 4, 22, 23, 24, 26, 27]);
            } elseif ($users['user_role_id'] == 26) {
                $ids = $this->map_province_childs($this->user['id']);
                $where_role['id'] = array('IN', [2, 4, 22, 23, 24, 27]);
            } else {
                $ids = $this->user['id'];
            }

            $where['id'] = array('IN', $ids);
        } else {
            $where_role['id'] = array('>', 1);
        }

        $where = $this->map_fenzhan($where);
        $list = Users::where($where)->order('id desc')->paginate(config('list_rows'));

        $pages = $list->render();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = IpToArea($val['create_ip']);
            $val['last_ip_area'] = IpToArea($val['last_login_ip']);
        }

        $roles = Db::table('mhcms_user_roles')->where($where_role)->select()->toArray();

        $this->view->assign('roles', $roles);
        $this->view->assign('page', $pages);
        $this->view->assign('list', $list);
        $this->view->assign('nickname', $nickname);
        $this->view->assign('user_name', $user_name);

        $this->view->field_list = set_model('users')->model_info->get_admin_column_fields();

        $this->mapping['role_id'] = $role_id;
        $this->view->mapping = $this->mapping;
        //$this->view->assign('mapping', $this->mapping);
        return $this->view->fetch();
    }

    /**
     * @param $role_id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function create($role_id)
    {
        global $_W;

        if ($role_id == 1) {
            return $this->zbn_msg("对不起，暂时不支持此操作，请不要增加另外的超级管理员！", 2);
        }

        if (!$role_id) {
            return $this->zbn_msg("对不起，缺少用户分组信息！", 2);
        }

        $role_id = (int)$role_id;
        $role = UserRoles::get(['id' => $role_id]);
        $model_id = $role['model_id'];
        $model_info = [];

        $ext_form_group = "ext";
        if ($model_id > 0) {
            $model = set_model($model_id);
            $model_info = $model->model_info;
        }
        if ($this->request->isPost()) {
            $data = input('param.data/a');
            //禁止设置用户角色
            $data['user_crypt'] = random();
            $data['site_id'] = $_W['site']['id'];
            if ($data['pass']) {
                $data['pass'] = crypt_pass($data['pass'], $data['user_crypt']);
                $result = $this->validate($data, 'Users');
                if ($result !== true) {
                    return $this->zbn_msg("$result");// 验证失败 输出错误信息
                }
                /**
                 * if the create  extra data is success then add the user
                 */
                if ($model_info) {
                    //get the base info
                    $base_info = input("post.$ext_form_group/a");
                    $res = $model_info->add_content($base_info); //
                    if ($res['code'] == 1) {
                        $data['status'] = 99;
                        $user = Users::create($data);
                        if ($role['is_admin']) {
                            //create admin
                            $admin_info['site_id'] = $_W['site']['id'];
                            $admin_info['role_id'] = $role['id'];
                            $admin_info['user_id'] = $user->id;
                            $admin_info['user_name'] = $user->user_name;
                            set_model('admin')->insert($admin_info);
                        }
                        $res['item']['user_id'] = $user->id;
                        $model_info->edit_content($res['item'], ['id' => $res['item']['id']]);
                        return $this->zbn_msg($res['msg'], 1, '', 2000, "''", "'reload_page()'");
                    } else {
                        return $this->zbn_msg($res['msg'], 2);
                    }
                } else {
                    $user = Users::create($data);
                    if ($role['is_admin']) {
                        //create admin
                        $admin_info['site_id'] = $_W['site']['id'];
                        $admin_info['role_id'] = $role['id'];
                        $admin_info['user_id'] = $user->id;
                        $admin_info['user_name'] = $user->user_name;

                        set_model('admin')->insert($admin_info);

                    }
                    return $this->zbn_msg("操作成功", 1, '', 2000, "''", "'reload_page()'");
                }
            }
            $this->zbn_msg('failed 02 ', 2);
        } else {
            $this->assign('roles', UserRoles::all());
            if ($model_info) {
                //todo check if user have auth to create
                $this->view->field_list = $model_info->get_admin_publish_fields([], [], [], $ext_form_group);
                $this->view->model_info = $model_info; //('node_type_info', $model_info);
            }
            $this->view->role_id = $role_id;
            return $this->view->fetch();
        }
    }


    public function edit($id)
    {
        global $_W;
        if ($this->super_power && $id == 1) {
            die();
        }
        $detail = Users::get(['id' => $id]);
        $this->check_admin_auth($detail);


        if (!$detail) {
            $this->zbn_msg('请选择要编辑的会员');
        } else {
            $role = UserRoles::get(['id' => $detail['user_role_id']]);
            $model_id = $role['model_id'];
            if ($model_id) {
                $model = set_model($model_id);
                /** @var Models $model_info */
                $model_info = $model->model_info;
                $ext_node = $model->where(['user_id' => $id])->find();
            }
        }

        $ext_form_group = "ext";
        if ($this->isPost()) {
            $role_changed = 0;
            $data = input('param.data/a');
            //todo : 允许用户更改角色 只限定于当前站点存在的角色
            if ($data['user_role_id'] != $role['id']) {
                $role_changed = 1;
            }

            if (!$this->super_power && $role_changed) {
                $target_role = UserRoles::get(['id' => $data['user_role_id']]);
                if ($target_role['site_id'] != 0 && $target_role['site_id'] != $_W['site']['id']) {
                    unset($data['user_role_id']);
                    $role_changed = 0;
                }
            }
            /**
             * change password
             */
            if (isset($data['pass']) && !empty($data['pass'])) {
                $data['user_crypt'] = random();
                $data['pass'] = crypt_pass($data['pass'], $data['user_crypt']);
            } else {
                unset($data['pass']);
            }

            // 处理空数据
            if (empty($data['nickname'])) {
                unset($data['nickname']);
            }

            if (empty($data['parent_id'])) {
                unset($data['parent_id']);
            }

            if (empty($data['status'])) {
                unset($data['status']);
            }

            //Edit start process
            if (isset($model_info) && $model_info) {
                $base_info = input("post.$ext_form_group/a");
                if (isset($ext_node) && $ext_node) {
                    $res = $model_info->edit_content($base_info, ['user_id' => $id]); //信息
                } else {
                    $base_info ['user_id'] = $id;
                    $res = $model_info->add_content($base_info); //信息
                }
                /** * update the user */
                $detail->where(['id' => $id])->update($data);
                if ($res['code'] == 1) {

                    if ($role_changed) {
                        set_model('admin')->where(['user_id' => $id, 'site_id' => $_W['site']['id']])->update(['role_id' => $data['user_role_id']]);

                        if ($target_role['is_admin'] != 1) {
                            set_model('admin')->where(['user_id' => $id, 'site_id' => $_W['site']['id']])->delete();
                        }
                    }


                    $this->zbn_msg('操作成功', 1);
                } else {
                    $this->zbn_msg('操作失败0', 1);
                }
            } else {
                //todo is org role is changed

                if ($role_changed) {
                    set_model('admin')->where(['user_id' => $id, 'site_id' => $_W['site']['id']])->update(['role_id' => $data['user_role_id']]);

                    if ($target_role['is_admin'] != 1) {
                        set_model('admin')->where(['user_id' => $id, 'site_id' => $_W['site']['id']])->delete();
                    }
                }
                set_model('users')->where(['id' => $id])->update($data);
                $this->zbn_msg('操作成功', 1);
            }
            return $this->zbn_msg('操作失败！', 2);
        } else {
            if (isset($model_info) && $model_info) {
                $this->view->model_info = $model_info;
                $this->view->field_list = $model_info->get_admin_publish_fields($ext_node, [], [], $ext_form_group);
            }

            $model = set_model('users');
            $model_infos = $model->model_info;
            $this->view->list = $model_infos->get_admin_publish_fields($detail);

            $this->assign('roles', D('user_roles')->where(['site_id' => ['IN', [0, $this->site_id]]])->select());
            $this->assign('detail', $detail);
            return $this->view->fetch();
        }
    }

    public function delete($user_id)
    {
        $tmp_user = Users::get($user_id);
        $this->check_admin_auth($tmp_user);
        $tmp_user->status = $tmp_user->status == 0 ? 99 : 0;
        $tmp_user->save();
        $test = $tmp_user->status == 99 ? " 启用" : '禁用';
        $data['code'] = 1;
        $data['msg'] = $test . '操作成功！';
        return $data;
    }

    public function destroy($user_id = 0)
    {
        if ($user_id) {
            $admin_ids[] = $user_id;
        } else {
            $admin_ids = input("param.id/a");
        }
        foreach ($admin_ids as $admin_id) {
            $tmp_admin = Users::get($admin_id);


            $user_role = UserRoles::get(['id' => $tmp_admin['user_role_id']]);
            $this->check_admin_auth($tmp_admin);
            $tmp_admin->delete();
            //doto delete admin
            set_model('admin')->where(['user_id' => $admin_id])->delete();
            //delete model
            if ($user_role['model_id'] && Models::field_exits('user_id', $user_role['model_id'])) {
                set_model($user_role['model_id'])->where(['user_id' => $admin_id])->delete();;
            }
        }
        $data['code'] = 1;
        $data['msg'] = '操作成功！' . $user_role['model_id'];
        return $data;
    }

    public function hit_log($user_id = 0)
    {
        $user_name = trim(input('param.user_name', ' ', 'htmlspecialchars'));
        $mobile = trim(input('param.mobile', ' ', 'htmlspecialchars'));
        if ($user_name) {
            $user_name_query = sprintf("user_name like '%s' OR nickname like '%s'", '%' . $user_name . '%', '%' . $user_name . '%');
        }
        $where = [];
        $where_hit = [];
        if ($mobile) {
            $where['mobile'] = $mobile;
        }
//        $user_id = (int)$user_id;
//        if ($this->current_admin_role) {
//            if (!empty($user_id)) {
//                $where['id'] = $user_id;
//            }
//        } else {
//            $where['id'] = $this->user->id;
//        }

        if (!$this->super_power){
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                $ids = $this->map_city_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $ids = $this->map_county_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            } elseif ($users['user_role_id'] == 25) {
                // CEO（区域经理）
                $ids = $this->map_area_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            } elseif ($users['user_role_id'] == 26) {
                // 省级代理
                $ids = $this->map_province_childs($this->user['id']);
                array_push($ids, $this->user['id']);
            }

            $where['id'] = array('IN', $ids);
        }

        if ($where || $user_name_query) {
            if ($user_name_query) {
                $userids = set_model('users')->where($where)->whereExp('', $user_name_query)->field('id')->select()->column('id');
            } else {
                $userids = set_model('users')->where($where)->field('id')->select()->column('id');
            }

            $where_hit['a.user_id'] = ['IN', $userids];
        }

        $sub_query = set_model('hits_log')->alias('a')->where($where_hit)->group('user_id,model_id,item_id')->field('user_id,model_id,item_id,COUNT(1) cnt')->buildSql();
        $list = set_model('hits_log')->alias('a')->join($sub_query . ' b', 'a.user_id = b.user_id AND a.model_id = b.model_id AND a.item_id = b.item_id')->where($where_hit)->order('a.create_at DESC')->field('a.*,b.cnt')->paginate(config('list_rows'));

        $pages = $list->render();

        $this->view->assign('page', $pages);
        $this->view->assign('list', $list);
        $this->view->assign('user_name', $user_name);
        $this->view->assign('mobile', $mobile);

        return $this->view->fetch();
    }

    public function wx_login_log($user_id = 0)
    {
        $user_name = trim(input('param.user_name', ' ', 'htmlspecialchars'));
        $mobile = trim(input('param.mobile', ' ', 'htmlspecialchars'));
        $where = [];
        if ($user_name) {
            $where['nickname'] = array('LIKE', '%' . $user_name . '%');
        }
        if ($mobile) {
            $where['a.mobile'] = $mobile;
        }

        if (!$this->current_admin_role) {
            $where['a.user_id'] = $this->user->id;
        }
        $list = set_model('users')->alias('a')->join(config("database.prefix") . 'sites_wechat_fans b', 'a.id = b.user_id', 'INNER')->where($where)->field('a.id,b.openid,a.avatar,a.user_name,a.nickname,a.mobile,a.login_cnt,a.last_login')->paginate(config('list_rows'));

        $pages = $list->render();

        $this->view->assign('num', 1);
        $this->view->assign('page', $pages);
        $this->view->assign('list', $list);
        $this->view->assign('user_name', $user_name);
        $this->view->assign('mobile', $mobile);

        return $this->view->fetch();
    }

}