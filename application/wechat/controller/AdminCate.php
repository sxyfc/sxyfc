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
namespace app\wechat\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserMenu;
use app\common\util\Tree2;
use think\Db;

class AdminCate extends AdminBase
{
    public $wechat_article_cate = "wechat_article_cate";

    public function index()
    {
        global $_W, $_GPC;
        $where = $category_items = [];
        //自定义筛选条件
        //获取模型信息
        $model = set_model($this->wechat_article_cate);
        $model_info = $model->model_info;
        $this->form_factory->model_id = $model_info['id'];
        $tree = new Tree2();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $categorys = array();
        $where['site_id'] = $_W['site']['id'];
        //读取缓存
        $where = $this->map_fenzhan($where);

        $lists = $model->where($where)->order('listorder desc')->select();
        $show_detail = count($lists) < 500 ? 1 : 0;
        $parent_id = $_GPC['parent_id'] ? intval($_GPC['parent_id']) : 0;
        $html_root = "";
        $types = array(1 => zlang('系统栏目'), 2 => zlang('单网页'), 3 => zlang('外链接'));
        if (!empty($lists)) {
            foreach ($lists as $r) {
                $r['modelname'] = $r['module'];
                $r['str_manage'] = '';
                //栏目数量较少
                foreach ($this->view->sub_menu as $menu) {

                    if ($menu['user_menu_display'] == 0) {
                        if ($r['cate_type'] == 2 && $menu['user_menu_controller'] == "admin_article") {
                            continue;
                        }
                        if ($r['cate_type'] == 1 && $menu['user_menu_controller'] == "admin_pages") {
                            continue;
                        }
                        $mapping = $r;
                        $r['str_manage'] .= build_back_a($menu['id'], $menu['user_menu_params'], zlang($menu['user_menu_name']), $menu['user_menu_mini'], $menu['class'], '90%', '80%', $mapping);
                    }
                }
                $r['typename'] = $types[$r['cate_type']];
                $r['display_icon'] = $r['show_menu'] ? '' : 'hide';
                if ($r['type'] || $r['child']) {
                    $r['items'] = '';
                } else {
                    $r['items'] = $category_items[$r['modelid']][$r['catid']];
                }
                $r['help'] = '';
                $setting = [];
                $categorys[$r['id']] = $r;
            }
        }
        $str = "<tr>
                    <td align='center'>
					<input type='text' pk='id' pk_value='\$id' class='layui-input listorder' field='listorder' model='{$this->wechat_article_cate}' mini='blur' id='\$id' value='\$listorder' />
					</td>
					<td align='center'>\$id</td>
					<td >\$spacer\$cate_name</td>
					<td>\$typename</td>
					<td><i class='\$display_icon icon'></i></td>
					<td align='center'>\$item_count</td> 
					<td align='center' >\$str_manage</td>
				</tr>";
        $tree->init($categorys);
        $this->view->categorys = $tree->get_tree(0, $str);
        return $this->view->fetch();
    }

    public function add()
    {
        global $_GPC;
        //后去模型信息
        $model = set_model($this->wechat_article_cate);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //手动处理类型的模型
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            //分配到当前模块
            if (Models::field_exits('module', $this->wechat_article_cate)) {
                $base_info['module'] = ROUTE_M;
            }
            $res = $model_info->add_content($base_info);
            if ($res['parent_id']) {
                //  add children
                $data = [];
                $data['children'] .= "," . $res['item']['id'];
                Db::name($this->wechat_article_cate)->where(['id' => $res['parent_id']])->update($data);
            }
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            //模板数据
            $default  =[];
            if($_GPC['parent_id']){
                $default['parent_id'] = (int)$_GPC['parent_id'];
            }
            $this->view->list = $model_info->get_admin_publish_fields($default);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function edit($id)
    {
        global $_GPC;
        $id = (int)$id;
        $model = set_model($this->wechat_article_cate);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
            }
            //    process data input
            $model_info->edit_content($data, $where);
            $this->zbn_msg("ok");
        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        $id = (int)$id;
        set_model($this->wechat_article_cate)->where(['id' => $id])->delete();
        return ['code' => 0, 'msg' => '删除成功'];
    }

    public static function getDataTree($route, $params = [], $pid = 0, $level = 0, $data_all, $pk_key = "id", $name_key = "cate_name", $parent_id_key = "parent_id", $icon_key = "icon")
    {
        global $_W, $_GPC;
        static $user_menu;
        if (!$user_menu) {
            $user_menu = UserMenu::get(['id' => $_GPC['user_menu_id']]);
            //build_back_a($user_menu['user_menu_id'],$user_menu['user_menu_params'],zlang($user_menu['user_menu_name']),$mini,$btn_class,'90%','80%' , $mapping)
        }
        $menus = [];
        foreach ($data_all as $item) {
            if ($item['parent_id'] == $pid) {
                $item['title'] = $item[$name_key];
                $menus[] = $item;
            }
        }
        $level++;
        foreach ($menus as $key => &$item) {
            // 多语言
            $item['id'] = $item[$pk_key];
            $item['name'] = zlang($item[$name_key]);
            $item['icon'] = $item[$icon_key];
            $item['target'] = "sub_frame";
            $item['children'] = '';
            //$menu['url'] = nb_url(['r'=>$route , $menu['user_menu_params'] ,'user_menu_id'=>$menu['user_menu_id']]);
            $item['url'] = build_back_url($user_menu['id'], $user_menu['user_menu_params'], zlang($user_menu['user_menu_name']), null, '', '90%', '80%', $item);
            $item['children'] = self::getDataTree($route, $params, $item[$pk_key], $level, $data_all);
        }
        return $menus;
    }
}