<?php
namespace app\member\controller;

use app\common\controller\UserBase;
use app\common\model\Node;
use app\common\model\NodeTypes;
use app\common\model\UserRoles;
use app\common\model\Users;
use think\Url;

class Shouyin extends UserBase
{
    /**
     * @return mixed
     */
    public function index()
    {


        /**
         * u need to get the role and the node type
         * and field list
         */
        $role = UserRoles::get($this->user['user_role_id']);

        /**
         * if there is a bind to the role  ,  get the full user extra node info
         */
        if($role['node_type_id'] != 9999){
            $node_type_id = $role['node_type_id'];
            $node_type_info = NodeTypes::get($node_type_id);
            $field_list = $node_type_info->getNodeFields();
            $node = Node::get(['user_id' => $this->user_id , 'node_type_id' => $node_type_id]);
            if($node){
                $node = $this->node->get_node($node['node_id']);
                $user_node_id = $node['node_id'];
            }
            $store_id = $node['所属门店'][0]['field_value'];
        }

        /**
         * $store info
         */

        $store = $this->node->get_node($store_id);
        $store_id = $store['node_id'];

        /**
         * retrieve shop info
         */
        $params = [
            "user_id" => $this->user_id,
        ];

        $url = Url::build("/shouyin/index/index",  $params ,  "" ,true);
        $this->view->url = Url::build("/service/public_service/str_to_qr" ) . "?str=" . $url ;

        $this->view->sk_url = $url;
        $this->view->store = $store;
        return $this->view->fetch();
    }

}