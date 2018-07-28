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
namespace app\common\model;

use think\Cache;
use think\Db;

class Sites extends Common
{
    public $node;

    /**
     * add  a category for current site
     *
     * @param $data
     *
     * @return mixed
     */
    public static function add_category($data)
    {
        $where = ['title' => $data['title'], 'node_type_id' => 1, 'root_id' => $data['root_id'], 'site_id' => $GLOBALS['site_id']];
        $test = Node::where($where)->find();
        //$test = $this->node->where(['title'=>$data['title'] , 'node_type_id' => $data['node_type_id'] , 'root_id' => $data['root_id']])->find();
        if (!$test) {
            $node = new Node();
            $node->setNodeType(1);
            $node->add_node($data, []);

            return $node->node_id;
        } else {
            return $test->node_id;
        }
    }

    /**
     * get the site's categorys
     *
     * @param bool $update
     * @return mixed
     */
    public static function get_site_category($update = false)
    {
        if (config('app_debug')) {
            $update = true;
        }
        $cache_path_prefix = $GLOBALS['root_domain'] . DS . "site_categorys" . DS . "category" . "_";
        //process root
        $categorys = Cache::get($cache_path_prefix . 0);
        if ($update || empty($categorys)) {
            $where['node_type_id'] = 1; //1 means category
            $where['root_id'] = $GLOBALS['root_id'];
            $where['site_id'] = 0;
            $categorys = Node::all($where)->toArray();
            Cache::set($cache_path_prefix . 0, $categorys);
        }

        //process branch site if current is not the main site
        $branch_categorys = [];
        if ($GLOBALS['cache_site_id'] != 0) {
            $branch_categorys = Cache::get($cache_path_prefix . $GLOBALS['cache_site_id']);
            if ($update || empty($branch_categorys)) {
                $where['node_type_id'] = 1; //1 means category
                $where['root_id'] = $GLOBALS['root_id'];
                $where['site_id'] = $GLOBALS['site_id'];
                $branch_categorys = Node::all($where)->toArray();
                Cache::set($cache_path_prefix . $GLOBALS['site_id'], $branch_categorys);
            }
        }
        $categorys = array_merge($categorys, $branch_categorys);
        return $categorys;
    }

    /**
     * get site user roles
     */
    public static function get_site_user_roles()
    {
        $list = [];
        $user_role_model = New UserRoles();
        $UserRoles = $user_role_model->fetchAll("id");
        $map['site_id'] = $GLOBALS['cache_site_id'];
        $map['root_id'] = $GLOBALS['root_id'];
        foreach ($UserRoles as $role_id => $user_role) {
            if ($user_role['site_id'] == $GLOBALS['cache_site_id'] && $user_role['root_id'] == $GLOBALS['root_id']) {

            } else {
                unset($UserRoles[$role_id]);
            }
        }
        return $UserRoles;
    }

    /**
     * get the site's categorys
     *
     * @param $node_type_id
     * @param bool $update
     * @return mixed
     * @internal param array $where
     */
    public static function get_node_type_category($node_type_id, $update = false)
    {
        if (config('app_debug')) {
            $update = true;
        }
        $cache_path_prefix = $GLOBALS['root_domain'] . DS . "node_categorys" . DS . "category" . "_";
        //process root
        $categorys = Cache::get($cache_path_prefix . $node_type_id);

        if ($update || empty($categorys)) {
            $categorys = [];
            $where['select1'] = $node_type_id; //1 means category

            $category_indexs = NodeIndex::all($where)->toArray();

            foreach ($category_indexs as $index) {
                $categorys[] = Node::get($index['node_id'])->toArray();
            }
            Cache::set($cache_path_prefix . 0, $categorys);
        }

        return $categorys;
    }


    /**
     *
     * get the site's node types
     *
     * @param bool $update
     *
     * @return array|mixed
     */
    public static function get_node_types($site = [], $update = false)
    {
        if (config('app_debug')) {
            $update = true;
        }

        if (!empty($site)) {
            $cache_site_id = $site['site_id'];
            $root_id = $site['root_id'];
            $root = Roots::get($root_id);
            $root_domain = $root['root_domain'];
        } else {
            $cache_site_id = $GLOBALS['cache_site_id'];
            $root_id = $GLOBALS['root_id'];
            $root_domain = $GLOBALS['root_domain'];
        }
        $cache_path_prefix = $root_domain . DS . "node_types" . DS . "node_type" . "_";
        $node_types = Cache::get($cache_path_prefix . 0);
        //先获取根域名下面所有的
        if ($update || empty($node_types)) {
            $where['root_id'] = ["IN", [0, $root_id]];
            $where['site_id'] = 0;
            $node_types = NodeTypes::all($where)->toArray();

            Cache::set($cache_path_prefix . 0, $node_types);
        }

        $branch_node_types = [];
        if ($cache_site_id != 0) {

            $branch_node_types = Cache::get($cache_path_prefix . $cache_site_id);
            if ($update || empty($branch_node_types)) {
                $where['site_id'] = $cache_site_id;
                $where['root_id'] = $root_id;
                $branch_node_types = NodeTypes::all($where)->toArray();
                Cache::set($cache_path_prefix . $cache_site_id, $branch_node_types);
            }
        }
        $node_types = array_merge($node_types, $branch_node_types);
        $node_types = ref_array($node_types , 'node_type_id');
        return $node_types;
    }

    /**
     * 默认配置 和 当前站点配置
     * @param bool $update
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function get_config($update = false)
    {
        $site_config = set_model('sites_config')->where(['site_id'=>$this->id])->find();
        $this->config = mhcms_json_decode($site_config['config']);
    }

}
