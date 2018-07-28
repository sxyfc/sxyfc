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
use app\common\util\Tree2;
use think\Db;

class AdminContent extends AdminBase
{
    public $house_cate = "house_cate";
    private $content_model_id;
    private $page = "page";

    /**
     * @param int $cate_id
     * @return mixed
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index($cate_id = 0)
    {

        global $_GPC, $_W;
        $house_cate_model = set_model($this->house_cate);
        if (is_numeric($cate_id) && $cate_id) {
            $cate = $house_cate_model->where(['id' => $cate_id])->find();
            if (!$cate) {
                $this->error("栏目不存在");
            }
            $this->view->cate = $cate;
            //系统 内容列表栏目
            if ($cate['cate_type'] == 1) {
                $this->content_model_id = $cate['model_id'];
                //自定义筛选条件
                $where = [];
                //获取模型信息
                $model = set_model($this->content_model_id);
                /** @var Models $model_info */
                $model_info = $model->model_info;
                //data list 如果不是超级管理员 并且数据是区分站群的
                if (Models::field_exits('site_id', $this->content_model_id)) {
                    $where['site_id'] = $this->site['id'];
                }
                //分配到当前模块
                if (Models::field_exits('module', $this->content_model_id)) {
                    $where['module'] = ROUTE_M;
                }
                if ($cate_id) {
                    $where['cate_id'] = $cate_id;
                }
                $keyword = input('param.keyword');

                if ($keyword && $model_info['search_keys']) {
                    $search_keys = str_replace(",", "|", $model_info['search_keys']);
                    $model = $model->where($search_keys, 'like', "%$keyword%");
                    $this->view->keyword = $keyword;
                }

                $lists = $model->where($where)->order(" listorder desc, id desc")->paginate();
                //列表数据
                $this->view->lists = $lists;
                //fields

                $this->view->field_list = $model_info->get_admin_column_fields();
                //model_info
                $this->view->model_info = $model_info;
                //+--------------------------------以下为系统--------------------------
                //模板替换变量
                $this->mapping['cate_id'] = $cate_id;
                $this->view->mapping = $this->mapping;
                $this->view->content_model_id = $this->content_model_id;

                $cate['admin_tpl'] = $cate['admin_tpl'] ? $cate['admin_tpl'] : "lists";

                return $this->view->fetch($cate['admin_tpl']);
            }


            if ($cate['cate_type'] == 2) {
                //弹网页直接连接到
                $cate = $house_cate_model->where(['id' => $cate_id])->find();
                if (!$cate) {
                    $this->zbn_msg("对不起 ， 栏目不存在无法操作 ");
                }
                $page_model = set_model($this->page);
                /** @var Models $model_info */
                $model_info = $page_model->model_info;
                //$model_info = Models::get(['id' => $this->zwt_department]);
                $where = ['cate_id' => $cate_id, 'site_id' => $_W['site']['id']];
                $detail = $page_model->where($where)->find();

                if ($this->isPost() && $model_info) {
                    if (isset($_GPC['_form_manual'])) {
                        //手动处理数据
                        $data = $_GPC;
                    } else {
                        //自动获取data分组数据
                        $data = input('post.data/a');//get the base info
                    }
                    // todo  process data input
                    $data['cate_id'] = $cate['id'];
                    if ($detail) {
                        $res = $model_info->edit_content($data, $where);
                        if ($res['code'] == 1) {
                            $this->zbn_msg("编辑成功 " . $res['msg']);
                        }
                    } else {
                        $res = $model_info->add_content($data);
                        if ($res['code'] == "1") {
                            $this->zbn_msg("新增内容成功 " . $res['msg']);
                        } else {
                            $this->zbn_msg($res['msg']);
                        }

                    }
                    $this->zbn_msg("错误" . $res['msg']);
                } else {

                    $this->view->list = $model_info->get_admin_publish_fields($detail);
                    $this->view->model_info = $model_info;
                    $this->view->mapping = $this->mapping;
                    return $this->view->fetch('page');
                }
            }
            if ($cate['cate_type'] == 3) {
                echo "该频道无法进行内容操作";
            }


        } //内容管理主页面
        else {

            $this->view->hide_tools = true;
            $where['site_id'] = $_W['site']['id'];
            $where['cate_type'] = ["IN" , "1,2"];
            $cates = $house_cate_model->where($where)->select();

            foreach ($cates as $k => &$cate) {
                $cate['name'] = $cate['cate_name'];
                $cate['target'] = "sub_frame";
            }

            $res = AdminCate::getDataTree('house/admin_content/lists', ['cate_id' => "{cate_id}"], 0, 0, $cates);

            $this->view->cates = $res;
            $this->view->mapping = $this->mapping;
            return $this->view->fetch();
        }
    }

    /**
     * @param $cate_id
     * @return mixed
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add($cate_id)
    {
        global $_GPC;
        //后去模型信息
        $this->view->cate = $cate = set_model($this->house_cate)->where(['id' => $cate_id])->find();
        if (!$cate) {
            $this->error("栏目不存在");
        }
        $this->content_model_id = $cate['model_id'];
        $model = set_model($this->content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        if (!$model_info) {
            $this->error("被绑定的模型不存在");
        }
        //手动处理类型的模型
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['cate_id'] = $cate_id;
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
            if (Models::field_exits('module', $this->content_model_id)) {
                $base_info['module'] = ROUTE_M;
            }


            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields(['cate_id' => $cate_id], ['cate_id']);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @param $cate_id
     * @return mixed
     * @throws \think\exception\DbException
     * @throws \think\Exception
     * @throws \Exception
     */
    public function edit($id, $cate_id)
    {
        global $_GPC;

        $cate = set_model($this->house_cate)->where(['id' => $cate_id])->find();
        $this->content_model_id = $cate['model_id'];
        $model = set_model($this->content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;


        $id = (int)$id;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = ['id' => $id];
        $detail = $model->where($where)->find();


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
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @param $cate_id
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id, $cate_id)
    {
        $cate = set_model("house_cate")->where(['id' => $cate_id])->find();

        $this->content_model_id = $cate['model_id'];
        $model = set_model($this->content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $id = (int)$id;
        $model->where(['id' => $id])->delete();
        //todo remove hit

        return ['code' => 1, 'msg' => '删除成功'];
    }
}