<?php

namespace app\core\util;

use app\common\model\Models;
use app\common\util\forms\select;
use think\Cache;
use think\Db;
use think\db\Query;
use think\Log;
use think\Model;

class ContentTag
{
    /**
     * 按照栏目类别渲染列表
     * @param array $config 栏目配置信息cate_id cate_model_id
     * @param null|bool $thumb
     * @param bool $filter_site_id 是否区分站群信息 默认开启
     * @param int $limit
     * @param string $order
     * @param array $ext_where
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @internal param $cate_id
     * @throws \think\Exception
     */
    public static function category_item($config, $thumb = null, $filter_site_id = true, $limit = 10, $order = " listorder desc, id desc ", $ext_where = [])
    {
        global $_W;
        //获取栏目信息
        $where = $config['where'] ? $config['where'] : [];
        if ($config['cate_id']) {
            $cate = Db::name($config['cate_model_id'])->where(['id' => $config['cate_id']])->find();

            //todo fix bug if cate table do not have a parent_id field
            $sub_categorys = [];

            if (Models::field_exits('parent_id', $config['cate_model_id'])) {
                $sub_categorys = Db::name($config['cate_model_id'])->where(['parent_id' => $cate['id']])->select()->toArray();
            }


            $cate_ids = [];
            if (count($sub_categorys) > 0) {
                foreach ($sub_categorys as $sub_category) {
                    $cate_ids[] = $sub_category['id'];
                }
            } else {
                $cate_ids[] = $cate['id'];
            }
            $where = ['cate_id' => ['IN', $cate_ids]];
        }

        //模型数据
        if (isset($cate)) {
            $content_model_id = $cate['model_id'];
        } else {
            $content_model_id = $config['model_id'];
        }
        $content_model = set_model($content_model_id);
        $content_model_info = $content_model->model_info;

        if (isset($thumb)) {
            if ($thumb) {
                $where['thumb'] = ['NEQ', ''];
            } else {
                $where['thumb'] = ['EQ', ''];
            }
        }

        if ($ext_where) {
            $where = array_merge($where, $ext_where);
        }
        if ($filter_site_id) {
            if (Models::field_exits('site_id', $content_model_id)) {
                $where['site_id'] = $_W['site']['id'];
            }
        }
        /** * 原始数据 */

        $articles = $content_model->field('id')->where($where)->limit($limit)->order($order)->select()->toArray();

        $rendered = [];
        foreach ($articles as $k => &$article) {
            $article = Models::get_item($article['id'], $content_model_id);
        }
        return $articles;
    }

    /**
     * 前台渲染栏目数据
     * @param array $params
     * @param int $pid
     * @param int $level
     * @param $data_all
     * @param string $pk_key
     * @param string $name_key
     * @param string $parent_id_key
     * @param string $icon_key
     * @return array
     */
    public static function get_cate_tree($params = [], $pid = 0, $level = 0, $data_all, $module, $pk_key = "id", $name_key = "cate_name", $parent_id_key = "parent_id", $icon_key = "icon")
    {
        global $_W, $_GPC;
        $menus = [];
        foreach ($data_all as $item) {
            if (isset($item[$parent_id_key]) && $item[$parent_id_key] == $pid) {
                $item['title'] = $item[$name_key];
                $menus[] = $item;
            }
        }
        $level++;
        foreach ($menus as $key => &$item) {
            // 多语言
            $item['id'] = $item[$pk_key];
            $item['name'] = $item[$name_key];
            $item['icon'] = $item[$icon_key];
            $item['target'] = "sub_frame";
            $item['children'] = '';
            if ($module) {
                $item['url'] = self::get_cate_front_url($item, $module);
            }
            $item['children'] = self::get_cate_tree($params, $item[$pk_key], $level, $data_all, $module, $pk_key, $name_key);
        }
        return $menus;
    }

    /**获取前台栏目地址
     * @param $cate
     * @return string
     */
    public static function get_cate_front_url($cate, $module)
    {
        $url = "";
        switch ($cate['cate_type']) {
            case 1 :
            case 2:
                $url = url($module . "/content/cate", ['cate_id' => $cate['id']]);
                break;
            case 3:
                $url = $cate['link_url'];
                break;
            default :
                $url = url($module . "/content/cate", ['cate_id' => $cate['id']]);
        }
        return $url;
    }

    /**
     * 根据数据生成栏目导航
     * @param $tree_nodes
     * @param string $parent_id_key
     * @param string $url_key
     * @param string $name_key
     * @return string
     */
    public static function to_tree($tree_nodes, $active_id = 0, $parent_id_key = "parent_id", $url_key = "url", $name_key = "cate_name")
    {
        $html = '';
        foreach ($tree_nodes as $node) {
            if (!$node['children']) {
                if ($node[$parent_id_key]) {
                    $left = "";
                } else {
                    $left = "left";
                }
                if ($node['show_menu']) {
                    if (!isset($node['class'])) {
                        $node['class'] = "";
                    }
                    $html .= "<a class='item {$node['class']}' href='{$node[$url_key]}'><i class='{$node['icon']}'></i> {$node[$name_key]}</a>";
                }
            } else {
                $left = "left";
                if ($node['show_menu']) {
                    $html .= "<div class='ui dropdown item'><a href='{$node[$url_key]}'>" . $node[$name_key] . " <i class=\"dropdown icon\"></i></a>";
                    $html .= self::to_tree($node['children']);
                    $html = $html . "</div>";
                }
            }
        }
        return $html ? "<div class='$left menu '>" . $html . '</div>' : $html;
    }

    public static function position_data($pos_name = "", $pos_id = 0, $limit = 0)
    {
        global $_W;
        $pos_model = set_model('position');
        if (is_numeric($pos_id) && $pos_id > 0) {
            $pos_info = $pos_model->where(['id' => $pos_model])->find();
        } else {
            $pos_info = $pos_model->where(['position_name' => $pos_name])->find();
            $pos_id = $pos_info['id'];
        }
        $position_data_model = set_model('position_data')->where(['position_id' => $pos_id, 'site_id' => $_W['site']['id']]);
        if ($limit > 0) {
            $res['pos'] = $datas = $position_data_model->limit($limit)->select();
        } else {
            $res['pos'] = $datas = $position_data_model->select();
        }
        foreach ($datas as $item) {
            $res['items'][] = Models::get_item($item['item_id'], $item['model_id']);
        }
        if (!$pos_info && $pos_name) {
            echo "请先建推荐位名称为：" . $pos_name;

            return false;
        }
        return $res;
    }


    public static function fetch_all($model_id, $rendered = true, $force_update = true, $tree_parent_key = null, $cache_time = 3600)
    {
        global $_W;
        $cache_key = $model_id . "_" . "all";

        $data = Cache::get($cache_key);
        if ($force_update) {
            $expire = true;
        }

        if (time() - $data['cache_time'] > $cache_time) {
            $expire = true;
        }


        if ($expire) {
            $model = set_model($model_id);
            $where = [];
            if (Models::field_exits('site_id', $model_id)) {
                $where['site_id'] = $_W['site']['id'];
            }
            if (Models::field_exits('parent_id', $model_id)) {
                $tree_parent_key = "parent_id";
            }

            $new_cache = [];
            $data_list = $model->where($where)->select();
            $new_cache['cache_time'] = time();
            if ($tree_parent_key) {
                $data_list = self::get_tree($data_list);
            } else {
                $new_db_list = [];
                foreach ($data_list as &$item) {
                    $new_db_list[] = Models::get_item($item['id'], $model_id);
                }
                $data_list = $new_db_list;
            }
            $new_cache['data'] = $data_list;
            Cache::set($cache_key, $new_cache);
            return $data_list;
        } else {
            return $data['data'];
        }
    }

    public static function get_tree($data_all, $pid = 0, $level = 0, $pk_key = "id", $parent_id_key = "parent_id")
    {
        $menus = [];
        foreach ($data_all as $item) {
            if (isset($item[$parent_id_key]) && $item[$parent_id_key] == $pid) {
                $menus[$item[$pk_key]] = $item;
            }
        }
        $level++;
        foreach ($menus as $key => &$item) {
            // 多语言
            $item['children'] = self::get_tree($data_all, $item[$pk_key], $level);
        }
        return $menus;
    }

    public static function model_data($model_id, $where = [], $order = "id desc", $limit = 8)
    {

        global $_W;
        $where['site_id'] = $_W['site']['id'];
        $items = set_model($model_id)->field('id')->where($where)->order($order)->limit(8)->select()->toArray();


        foreach ($items as &$item) {
            $item = Models::get_item($item['id'], $model_id);
        }

        return $items;
    }

    public static function model_tree($model_id, $module = "", $name_key = 'title', $id_key = 'id')
    {
        global $_W;
        $model = set_model("$model_id");

        if (Models::field_exits('site_id', $model_id)) {
            $where['site_id'] = $_W['site']['id'];
        }

        $lists = $model->where($where)->order('listorder desc')->select()->toArray();

        $cate_tree = ContentTag::get_cate_tree([], 0, 0, $lists, $module, $id_key, $name_key);
        return $cate_tree;
    }

    public static function model_tree_tow($model_id, $module = "", $name_key = 'title', $id_key = 'id', $second_model)
    {
        global $_W;
        $model = set_model("$model_id");

        if (Models::field_exits('site_id', $model_id)) {
            $where['site_id'] = $_W['site']['id'];
        }

        $lists = $model->where($where)->order('listorder desc')->select()->toArray();

        $cate_tree = ContentTag::get_cate_tree([], 0, 0, $lists, $module, $id_key, $name_key);


        return $cate_tree;
    }

    public static function load_options($model_id, $field_name)
    {
        $filter_info = Models::gen_user_filter($model_id, null);
        $new_field = [];
        foreach ($filter_info['fields'] as $field) {
            $new_field[$field['field_name']] = $field;
        }
        return $new_field[$field_name]['options'];
    }

    /**
     * 双表条件查询+条件字段+first表主键
     * @param $first_model  被关联单表名称
     * @param $field_name  查询关联条件字段
     * @param $second_model 第二个表的模型
     */
    public static function load_options_two($first_model, $field_name, $second_model, $check_field)
    {
        $filter_info = Models::gen_user_filter_two($first_model, null, "", $second_model, $check_field);

        $new_field = [];
        foreach ($filter_info['fields'] as $field) {
            $new_field[$field['field_name']] = $field;
        }
        return $new_field[$field_name]['options'];
    }


}