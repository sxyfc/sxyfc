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

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserMenu;
use app\common\util\Tree2;
use think\Db;

class AdminCate extends AdminBase
{
    public $house_cate = "house_cate";

    /**
     * @return string
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        global $_W, $_GPC;
        $category_items = array();
        //自定义筛选条件
        $where = [];
        //获取模型信息
        $model = set_model($this->house_cate);
        $model_info = $model->model_info;
        $this->form_factory->model_id = $model_info['id'];
        $tree = new Tree2();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $categorys = array();
        //data module

        //读取缓存
        $where['site_id'] = $_W['site']['id'];
        $lists = $model->where($where)->order('listorder desc')->select();
        $show_detail = count($lists) < 500 ? 1 : 0;
        //$parent_id = $_GPC['parent_id'] ? intval($_GPC['parent_id']) : 0;
        $html_root = "";
        $types = array(1 => zlang('系统栏目'), 2 => zlang('单网页'), 3 => zlang('外链接'));
        if (!empty($lists)) {
            foreach ($lists as $r) {
                $_r = Models::get_item($r['id'] , $this->house_cate);
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
                $r['items'] = '';
                $r['help'] = '';
                $r['position'] = $_r['position'] ;


                $setting = [];
                $categorys[$r['id']] = $r;
            }
        }
        $str = "<tr>
                    <td align='center'>
					<input type='text' pk='id' pk_value='\$id' class='layui-input listorder' field='listorder' model='{$model_info['id']}' mini='blur' id='\$id' value='\$listorder' />
					</td>
					<td align='center'>\$id</td>
					<td >\$spacer\$cate_name</td>
					<td>\$typename</td>
					<td>\$position</td>
					<td align='center'>\$item_count</td> 
					<td align='center' >\$str_manage</td>
				</tr>";
        $tree->init($categorys);
        $this->view->categorys = $tree->get_tree(0, $str);

        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    public function add()
    {
        global $_GPC;
        //后去模型信息
        $model = set_model($this->house_cate);
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
            //自动提取缩略图
            if (!isset($base_info['thumb']) && !empty($base_info['content'])) {
                $content = stripslashes($base_info['content']);
                $auto_thumb_no = 1 - 1;
                if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
                    $thumb_url = str_replace("&quot;", "", $matches[3][$auto_thumb_no]);
                }
                if (isset($thumb_url)) {
                    $file = Db::name('file')->where(['url' => $thumb_url])->find();//File::get(['url'=>$base_info['thumb']]);
                    $base_info['thumb'][] = $file['file_id'];
                }
            }
            //自动截取简介
            if (!isset($base_info['description']) && !empty($base_info['content'])) {
                $content = stripslashes($base_info['content']);
                $introcude_length = intval(255);
                $base_info['description'] = str_cut(str_replace(array("\r\n", "\t"), '', strip_tags($content)), $introcude_length);
            }
            //分配到当前模块
            if (Models::field_exits('module', $this->house_cate)) {
                $base_info['module'] = ROUTE_M;
            }
            $res = $model_info->add_content($base_info);
            if ($res['parent_id']) {
                //todo add children
                $data = [];
                $data['children'] .= "," . $res['item']['id'];
                Db::name("article_cate")->where(['id' => $res['parent_id']])->update($data);
            }
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields();
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function edit($id)
    {
        global $_GPC;
        $id = (int)$id;
        $model = set_model($this->house_cate);
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
            // todo  process data input
            $model_info->edit_content($data, $where);
            $this->zbn_msg("ok");
        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields($detail);;
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        $id = (int)$id;
        set_model($this->house_cate)->where(['id' => $id])->delete();
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

    public function init_cate(){
        global $_W;
//todo test if there is any cates
        $cate_num = set_model($this->house_cate)->where(['site_id'=>$_W['site']['id']])->count();
        if($cate_num !=0){
            $ret = [
                'code' => 2 ,
                'msg' => "已经有栏目存在，系统已经禁止初始化菜单功能！"
            ];
            return $ret;
        }
        $sql = "insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('楼市资讯',1,0,'','',{site_id},0,1,'system',1,0,'list_article','item_article','parent_article','lists','','',1,'house_news','','iconfont icon-news2',0,'',',1,2,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('新楼盘',3,0,'','',{site_id},0,99,'system',1,0,'list_article','item_article','parent_article','lists','','',1,'553','/house/loupan','iconfont icon-loupan',0,'/pages/house/loupan/loupan',',1,3,4,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('看房团',3,0,'','',{site_id},0,0,'system',1,0,'list_article','item_article','parent_article','lists','','',1,'article','/house/kft','iconfont icon-kanfangtuan_news',0,'',',1,4,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('会员中心',3,0,'','',{site_id},0,0,'system',1,0,'list_article','item_article','parent_article','lists','','',1,'article','/house/user','iconfont icon-wode',0,'',',4,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('二手房',3,0,'','',{site_id},0,3,'system',1,0,'list_article','item_article','parent_article','lists','','',1,'article','/house/esf','iconfont icon-12qiuzhizhaopin',0,'/pages/house/esf/esf',',1,3,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('经纪人',3,0,'','',{site_id},0,0,'system',1,0,'list_article','item_article','parent_article','lists','','',1,'article','/house/agent/index','iconfont icon-xiaoqu',0,'',',1,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('求租求购',1,0,'','',{site_id},0,0,'system',1,0,'list_info','item_article','parent_article','lists','','',1,'house_info','','iconfont icon-bianminfuwugray',1,'','0');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('找租房',3,0,'','',{site_id},0,2,'',1,0,'list_article','item_article','parent_article','lists','','',1,'','/house/rent','iconfont icon-chuzu',0,'/pages/house/rent/rent',',1,3,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('首页',3,0,'','',{site_id},0,100,'',1,0,'list_article','item_article','parent_article','lists','','',2,'','/house','iconfont icon-shouye',0,'/pages/house/index/index',',2,4,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('发布',3,0,'','',{site_id},0,0,'',1,0,'list_article','item_article','parent_article','lists','','',2,'','/house/index/entry_publish','iconfont icon-fabu1',0,'',',4,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('我要卖房',3,0,'','',{site_id},0,0,'',1,0,'list_article','item_article','parent_article','lists','','',1,'','/house/weituo/create?type=1','icon-woyaomaifang iconfont',0,'/pages/house/weituo/weituo?type=1',',1,3,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('我要买房',3,0,'','',{site_id},0,0,'',1,0,'list_article','item_article','parent_article','lists','','',1,'','/house/weituo/esf_buy','iconfont icon-svgmoban06',0,'/pages/house/esf_buy/esf_buy',',1,3,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('我要出租',3,0,'','',{site_id},0,0,'',1,0,'list_article','item_article','parent_article','lists','','',1,'','/house/weituo/create?type=2','iconfont icon-woyaochuzu',0,'/pages/house/weituo/weituo?type=2',',1,3,');
insert  into `mhcms_house_cate`(`cate_name`,`cate_type`,`parent_id`,`description`,`children`,`site_id`,`image`,`listorder`,`module`,`show_menu`,`item_count`,`list_tpl`,`item_tpl`,`parent_tpl`,`admin_tpl`,`admin_add_tpl`,`admin_edit_tpl`,`in_parent_list`,`model_id`,`link_url`,`icon`,`allow_post`,`miniapp_nav_url`,`position`) values ('我要租房',3,0,'','',{site_id},0,0,'',1,0,'list_article','item_article','parent_article','lists','','',2,'','/house/weituo/rent_add','iconfont icon-chuzu',0,'/pages/house/rent_add/rent_add',',1,3,');
";

        $mapping = ['site_id' => $_W['site']['id']];
        $sql = parseParam($sql , $mapping);

        sql_execute($sql);

        $ret = [
            'code' => 1 ,
            'msg' => "初始化菜单完成！"
        ];
        return $ret;
    }
}