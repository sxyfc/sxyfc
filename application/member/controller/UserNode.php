<?php

namespace app\member\controller;

use app\common\controller\UserBase;
use app\common\model\Node;
use app\common\model\NodeTypes;

class UserNode extends UserBase
{
    public function index($node_type_id)
    {

        $node_type_id =(int)  $node_type_id;
        if($node_type_id){
            /**
             * check if the user have access to the current node type
             */
            if(!check_node_type($node_type_id , $this->user)){
                $this->error("非法访问！");
            }

            /**
             * fetch data
             */
            $this->mapping['node_type_id']  = $where['node_type_id'] = $node_type_id;
            $where['user_id'] = $this->user_id;
            $where['root_id'] = $GLOBALS['root_id'];
            if ($GLOBALS['cache_site_id'] != 0) {
                $where['site_id'] = $GLOBALS['site_id'];
            }
            $list = $this->node->where($where)->order('node_id desc')->paginate(config('list_rows'));
            $pages = $list->render();
            $this->view->assign('list', $list);
            $this->view->node_model = $this->node;
            $this->view->mapping = $this->mapping;
            $this->view->assign('pages', $pages);

            return $this->view->fetch();
        }else{
            $this->error("非法访问！");
        }

    }

    public function create($node_type_id = 0)
    {
        $node_type_id =(int) $node_type_id;
        $node_type_info = NodeTypes::get($node_type_id);
        if($this->isPost()){

            $base_info = input( 'post.base/a' );//get the base info
            $external  = input( 'post.external/a' );//external data
            $this->node->setNodeType( $node_type_info['node_type_id'] ); //制定节点类型
            $this->node->form_factory = $this->form_factory;//$this->node->parent_id    = $parent_id; //$this->node->user_id      = $this->admin_id; //指定用户 //$this->node->site_id      = $this->site_id;
            //add info into the top node
            $res = $this->node->add_node( $base_info, $external ); //增加信息
            if ( $res['code'] == 1 ) {
                return $this->zbn_msg( $res['msg'], 1 );
            } else {
                return $this->zbn_msg( $res['msg'], 2 );
            }
        }else{
            if (empty($node_type_id)) {
                $this->view->node_types = $this->site->get_node_types();
                return $this->view->fetch('publish_node_type');
            } else {

                //TODO： auth check
                if (  $node_type_info['root_id']!=$this->user['root_id'] ) {

                }

                $this->form_factory->node_type_id = $node_type_info['node_type_id'];
                $new_field_list                   = $node_type_info->getNodeFields();
                foreach ( $new_field_list as $k => $field ) {
                    if (empty($field['node_field_mode']) || !$field['node_field_asform'] || $field['node_field_disabled'] == 1 || !$field['node_field_display_form']  ) {
                        unset( $new_field_list[ $k ] );
                        continue;
                    }
                    $field['form_str']    = $this->form_factory->config_form( $field );
                    $new_field_list[ $k ] = $field;
                }

                 $this->view->assign( 'node_type_info', $node_type_info );
                $this->view->assign( 'list', $new_field_list );
                $this->view->assign( 'node_type_name', $node_type_info['node_type_name'] );
                return $this->view->fetch();
            }
        }
    }


    public function edit($id)
    {
        $node_id = intval($id);
        $node = $this->node->get_node($node_id);
        //权限判断 是否可以编辑
        if ($this->user['id'] != 1 && $this->user['site_id'] != $node['site_id']) {
            $this->error("没有授权的操作！");
        }
        if (!$node) {
            $this->error("哎吆 , 我找不到你要的东西！");
        }
        $node_type_info = NodeTypes::get($node['node_type_id']);
        $field_list = $node_type_info->getNodeFields();//gei all the fields
        $this->form_factory->node_type_id = $node_type_info['node_type_id'];
        $this->form_factory->node_id = $node_id;
        /**
         * 处理节点模型
         */
        if ($node['root_id'] == $GLOBALS['root_id']) {
            if ($this->isPost()) {
                //Edit start process
                $base_info = input('post.base/a');//get the base info
                $external = input('post.external/a');//external data
                $this->node->setNodeType($node_type_info['node_type_id']); //制定节点类型
                $this->node->node_fields = $field_list;
                $this->node->form_factory = $this->form_factory;//$this->node->parent_id    = $parent_id; //$this->node->user_id      = $this->admin_id; //指定用户 //$this->node->site_id      = $this->site_id;
                $this->node->node_id = $node_id;
                $res = $this->node->edit_node($base_info, $external); //增加信息
                if ($res['code'] == 1) {
                    return $this->zbn_msg($res['msg'], 1);
                } else {
                    return $this->zbn_msg($res['msg'], 2);
                }
            } else {
                foreach ($field_list as $k => $field) {
                    if (empty($field['node_field_mode']) || !$field['node_field_asform'] || $field['node_field_disabled'] == 1 || !$field['node_field_display_form']) {
                        unset($field_list[$k]);
                        continue;
                    }
                    //put data in to each  field
                    $field['node_field_default_value'] = $node[$field['node_field_name']];
                    $field['form_str'] = $this->form_factory->config_form($field);
                    $field_list[$k] = $field;
                }
                $this->view->assign('list', $field_list);
                return $this->view->fetch();
            }
        } else {
            return $this->error("系统错误！您可能已经切换了网站 ， 该节点可能不是当前网站");
        }
    }
}