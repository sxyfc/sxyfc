<?php
namespace app\member\controller;

use app\common\controller\UserBase;
use app\common\model\Node;
use app\common\model\NodeTypes;
use app\common\model\UserRoles;
use app\common\model\Users;

class User extends UserBase
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Users();
    }

    /**
     * front user manager
     * @param $role_id
     * @return mixed
     */
    public function index($role_id)
    {
        $role_id = (int)$role_id;
        $map = [];
        $keyword = trim(input('param.keyword', ' ', 'htmlspecialchars'));
        if ($keyword) {
            $map['user_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $map = $this->map_fenzhan($map);
        $map['user_role_id'] = (int)$role_id;
        $map['creator_id'] = $this->user->user_id;
        $list = Users::where($map)->paginate(config('list_rows'));
        $pages = $list->render();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = IpToArea($val['create_ip']);
            $val['last_ip_area'] = IpToArea($val['last_login_ip']);
        }
        $this->view->assign('page', $pages);
        $this->view->assign('list', $list);
        $this->view->assign('keyword', $keyword);
        $this->mapping['role_id'] = $role_id;
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    /**
     * front user adding
     * @param $role_id
     * @return mixed
     */
    public function create($role_id)
    {
        $role_id = (int)$role_id;
        $role = UserRoles::get($role_id);

        /**
         * TODO: check if the current user have access to target role
         **/
        if ($this->request->isPost()) {
            $this->node->user_id = $this->user_id;
            $data = input('param.data/a');
            /**
             *  if the role need to be checked!
             */
            if ($role['need_check'] == 1) {
                $data['status'] = 2;
            } else {
                $data['status'] = 1;
            }
            $data['user_crypt'] = random();
            $data['site_id'] = $GLOBALS['site_id'];
            $data['root_id'] = $GLOBALS['root_id'];
            /**
             * security section
             */
            $data['is_admin'] = 0;
            $data['creator_id'] = $this->user_id;

            if ($data['pass']) {
                $data['pass'] = crypt_pass($data['pass'], $data['user_crypt']);
                /**
                 * validate data
                 */
                $result = $this->validate($data, 'Users');
                if ($result !== true) {
                    return $this->zbn_msg("$result");
                }
                /**
                 * if the role is binding a type
                 */
                if ($role['node_type_id'] && $role['node_type_id'] != 9999) {
                    $base_info = input('post.base/a');                         //get the base info
                    $external = input('post.external/a');                     //external data
                    /**
                     * node process
                     */
                    $this->node->setNodeType($role['node_type_id']); //
                    $this->node->form_factory = $this->form_factory;
                    /**
                     * check if a user needs to check
                     */
                    if ($role['need_check']) {
                        $base_info['status'] = 2;
                    } else {
                        $base_info['status'] = 99;
                    }
                    $res = $this->node->add_node($base_info, $external);
                    /**
                     * if the node is ok then  create the user
                     */
                    if (isset($res['node_id']) && !empty($res['node_id'])) {
                        $this->node = Node::get($res['node_id']);
                        $user = Users::create($data);
                        /**
                         * give the node back to the new user
                         */
                        $this->node->user_id = $user->user_id;
                        $this->node->save();
                        return $this->zbn_msg($res['msg'], 1);
                    } else {
                        return $this->zbn_msg($res['msg'], 2);
                    }
                } else {
                    /**
                     * just create the user
                     */
                    Users::create($data);
                    return $this->zbn_msg(" Ok ", 1);
                }
                $this->zbn_msg('failed 01', 2);
            }
            $this->zbn_msg('failed 02 ', 2);
        } else {
            $node_type_id = $role['node_type_id'];
            if ($node_type_id != 9999) {
                if (!$node_type_id) {
                    $this->error("此操作需要一个绑定！");
                }
                $node_type_info = NodeTypes::get($node_type_id);
                /**
                 * auth check if the user have access the target role node type id
                 */
                if ($node_type_info['root_id'] != 0 && $node_type_info['root_id'] != $this->user['root_id']) {
                    $this->error("没有授权的操作 ， 错误的根域名 User！");
                }
                $this->form_factory->node_type_id = $node_type_info['node_type_id'];
                $new_field_list = $node_type_info->getNodeFields(0, true);
                foreach ($new_field_list as $k => $field) {
                    if (empty($field['node_field_mode']) || !$field['node_field_asform'] || !$field['node_field_display_form']) {
                        unset($new_field_list[$k]);
                        continue;
                    }
                    $field['form_str'] = $this->form_factory->config_form($field);
                    $new_field_list[$k] = $field;
                }
                $this->view->assign('node_type_name', $node_type_info['node_type_name']);
                $this->view->assign('node_type_info', $node_type_info);
                $this->view->assign('list', $new_field_list);
            }
            $maps = [
                'root_id' => $GLOBALS['root_id']
            ];
            $this->assign('roles', UserRoles::all($maps));
            $this->view->role_id = $role_id;
            return $this->view->fetch();
        }
    }

    /**
     * Editing a user
     * @param $id
     * @return mixed|void
     */
    public function edit($id)
    {
        $user_id = (int)$id;
        if (!$user = Users::get($user_id)) {
            $this->zbn_msg('请选择要编辑的会员');
        } else {

            if($user['creator_id'] != $this->user->user_id){
                $this->error('越权的操作！');
            }
            /**
             * u need to get the role and the node type
             * and field list
             */
            $role = UserRoles::get($user['user_role_id']);

            /**
             * if there is a bind to the role  ,  get the full node info
             */
            if($role['node_type_id'] != 9999){
                $node_type_id = $role['node_type_id'];
                $node_type_info = NodeTypes::get($node_type_id);
                $field_list = $node_type_info->getNodeFields();
                $node = Node::get(['user_id' => $user_id , 'node_type_id' => $node_type_id]);
                if($node){
                    $node = $this->node->get_node($node['node_id']);
                    $node_id = $node['node_id'];
                }
            }
        }
        if ($this->isPost()) {
            $data = input('param.data/a');
            /**
             * security section
             */
            $data['is_admin'] = 0;
            $data['creator_id'] = $this->user_id;

            $data['user_id'] = $user_id;
            /**
             * change password
             */
            if (isset($data['pass']) && !empty($data['pass'])) {
                $data['user_crypt'] = random();
                $data['pass'] = crypt_pass($data['pass'], $data['user_crypt']);
            }
            /**
             * update extra data
             */
            //Edit start process
            $base_info = input('post.base/a');//get the base info
            $external = input('post.external/a');//external data
            $this->node->setNodeType($node_type_info['node_type_id']); //制定节点类型
            $this->node->node_fields = $field_list;
            $this->node->form_factory = $this->form_factory;//$this->node->parent_id    = $parent_id; //$this->node->user_id      = $this->admin_id; //指定用户 //$this->node->site_id      = $this->site_id;
            if(isset($node_id)){
                $this->node->node_id = $node_id;
                /**
                 * give the node back to the  user
                 */
                $this->node->user_id = $user->user_id;
                $res = $this->node->edit_node($base_info, $external); //增加信息
            }else{
                /**
                 * fix the lost data
                 */
                $res = $this->node->add_node($base_info, $external);
                /**
                 * if the node is ok then  create the user
                 */
                if (isset($res['node_id']) && !empty($res['node_id'])) {
                    $this->node = Node::get($res['node_id']);
                    /**
                     * give the node back to the  user
                     */
                    $this->node->user_id = $user->user_id;
                    $this->node->save();
                }
            }
            /**
             * update the user
             */
            if ($user->update($data) && $res) {
                $node = $this->node->get_node($node['node_id'], true);
                $this->zbn_msg('操作成功', 1);
            }
            return $this->zbn_msg('操作失败', 2);
        }

        else {
            // $this->node_type_model->where(['node_type_id' => $node_type_id])->find();//加载Node Type
            //gei all the fields
            $this->form_factory->node_type_id = $node_type_info['node_type_id'];
            /**
             * then get the node
             */
            if (!$node) {
                //$this->error("U Have A Error");
            }

            $this->form_factory->node_id = $node['node_id'];
            foreach ($field_list as $k => $field) {
                if (empty($field['node_field_mode']) || !$field['node_field_asform']|| !$field['node_field_display_form']) {
                    unset($field_list[$k]);
                    continue;
                }
                //put data in to each  field
                $field['node_field_default_value'] = $node[$field['node_field_name']];
                $field['form_str'] = $this->form_factory->config_form($field);
                $field_list[$k] = $field;
            }
            $this->view->assign('list', $field_list);
            $this->assign('roles', D('UserRoles')->fetchAll());
            $this->assign('detail', $user);
            $this->view->assign('node_type_info', $node_type_info);
            return $this->view->fetch();
        }
    }

    public function delete()
    {
        $admin_ids = input("param.id/a");
        foreach ($admin_ids as $admin_id) {
            $tmp_admin = Users::get($admin_id);
            $this->check_admin_auth($tmp_admin);
            $tmp_admin->status = $tmp_admin->status == 0 ? 99 : 0;
            $tmp_admin->save();
        }
        $data['code'] = 1;
        $data['msg'] = '操作成功！';
        return $data;
    }

    public function destroy()
    {
        $admin_ids = input("param.id/a");
        foreach ($admin_ids as $admin_id) {
            $tmp_admin = Users::get($admin_id);
            $this->check_admin_auth($tmp_admin);
            $tmp_admin->delete();
        }
        $data['code'] = 1;
        $data['msg'] = '操作成功！';
        return $data;
    }

}
