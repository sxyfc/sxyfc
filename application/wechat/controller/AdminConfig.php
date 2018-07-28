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

use app\attachment\mhcms_classes\MhcmsFile;
use app\common\controller\AdminBase;
use app\common\model\File;
use app\common\model\Models;
use app\common\model\SitesWechat;
use app\common\model\UserMenu;
use app\common\util\Tree2;
use app\mhcms_professional\wechat\WeiXinPlatform;
use app\wechat\util\WechatUtility;
use think\Cache;
use think\Db;

class AdminConfig extends AdminBase
{

    public $sites_wechat = 'sites_wechat';

    public function index()
    {
        global $_W;

        $this->content_model_id = $this->sites_wechat;

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

        $keyword = input('param.keyword');

        if ($keyword && $model_info['search_keys']) {
            $search_keys = str_replace(",", "|", $model_info['search_keys']);
            $model = $model->where($search_keys, 'like', "%$keyword%");
            $this->view->keyword = $keyword;
        }

        $lists = $model->where($where)->order("site_id desc")->paginate();
        //列表数据
        $this->view->lists = $lists;
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();
        //model_info
        $this->view->model_info = $model_info;
        //+--------------------------------以下为系统--------------------------
        //模板替换变量
        $this->mapping['site_id'] = $_W['site']['id'];
        $this->view->mapping = $this->mapping;
        $this->view->content_model_id = $this->content_model_id;
        return $this->view->fetch();
    }

    /**
     * 编辑或者新增
     * @param $site_id
     * @return string
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($site_id)
    {
        global $_GPC;

        $this->content_model_id = $this->sites_wechat;
        $model = set_model($this->content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $id = (int)$site_id;
        $where = ['site_id' => $id];
        $detail = $model->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
            }
            if ($detail) {
                $res = $model_info->edit_content($data, $where);
            } else {
                $res = $model_info->add_content($data);
            }

            if ($res['code'] == 1) {
                WechatUtility::create_wechat($res['item']['id']);
                $this->zbn_msg("ok");
            } else {
                $this->zbn_msg($res['msg']);
            }


        } else {
            //模板数据
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }


    /**
     * @return string
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function auto_auth()
    {
        global $_W, $_GPC;
        $account_platform = new WeiXinPlatform();
        if ($_GPC['do'] == "success") {
            if (empty($_GPC['auth_code'])) {
                $this->error('授权登录失败，请重试');
            }
            //check if there is already one
            $exist_account = SitesWechat::get(['site_id' => $_W['site']['id']]);

            $auth_info = $account_platform->getAuthInfo($_GPC['auth_code']);
            $auth_refresh_token = $auth_info['authorization_info']['authorizer_refresh_token'];
            $auth_appid = $auth_info['authorization_info']['authorizer_appid'];

            $account_info = $account_platform->getAccountInfo($auth_appid);

            if (!$account_info['authorizer_info']['alias']) {
                $this->error('授权登录新建公众号失败，请重试');
            }

            if ($account_info['authorizer_info']['service_type_info']['id'] == '0' || $account_info['authorizer_info']['service_type_info']['id'] == '1') {
                if ($account_info['authorizer_info']['verify_type_info']['id'] > '-1') {
                    $level = '3';
                } else {
                    $level = '1';
                }
            } elseif ($account_info['authorizer_info']['service_type_info']['id'] == '2') {
                if ($account_info['authorizer_info']['verify_type_info']['id'] > '-1') {
                    $level = '4';
                } else {
                    $level = '2';
                }
            }
            $account_found = set_model("sites_wechat")->where(array('org_id' => $account_info['authorizer_info']['user_name']))->find();

            if ($exist_account['id'] != $account_found['id']) {
                $this->error("对不起，目前一个分站只允许接入一个公众号！");
            }
            //todo create wechat account

            $sites_wechat_data = array(
                'type' => 3, // 授权接入
                'hash' => random(8),
                'isconnect' => 1,
                'account_name' => $account_info['authorizer_info']['nick_name'],
                'account' => $account_info['authorizer_info']['alias'],
                'org_id' => $account_info['authorizer_info']['user_name'],
                'level' => $level,
                'app_id' => $auth_appid,
                'auth_refresh_token' => $auth_refresh_token,
                'encodingaeskey' => $account_platform->encodingaeskey,
                'token' => $account_platform->token,
            );


            if (!$account_found) {
                //todo create sites_wechat
                $site_wechat_id = set_model("sites_wechat")->insert($sites_wechat_data, false, true);

                if ($site_wechat_id) {
                    // create fans data tablegroup_ids
                    WechatUtility::create_wechat($site_wechat_id);
                    Cache::set('account:auth:refreshtoken:' . $site_wechat_id, $auth_refresh_token);
                    //todo save pic
                    $headimg = ihttp_request($account_info['authorizer_info']['head_img']);
                    file_put_contents(SYS_PATH . 'upload_file/headimg_' . $site_wechat_id . '.jpg', $headimg['content']);
                    $headimg_file = MhcmsFile::create('upload_file/headimg_' . $site_wechat_id . '.jpg');

                    $qrcode = ihttp_request($account_info['authorizer_info']['qrcode_url']);
                    file_put_contents(SYS_PATH . 'upload_file/qrcode_' . $site_wechat_id . '.jpg', $qrcode['content']);
                    $qrcode_file = MhcmsFile::create('upload_file/qrcode_' . $site_wechat_id . '.jpg');
                    //todo save attach file
                    $update = [];
                    $update['qrcode'] = $qrcode_file['file_id'];
                    $update['avatar'] = $headimg_file['file_id'];
                    //update sites wechat account avatar and qrcode info
                    set_model("sites_wechat")->where(['id' => $site_wechat_id])->update($update);
                    $this->success('授权登录成功');
                }
                $this->error("创建接入数据失败！");

            } else {
                $update = array(
                    'auth_refresh_token' => $auth_refresh_token,
                    'encodingaeskey' => $account_platform->encodingaeskey,
                    'token' => $account_platform->token,
                    'level' => $level,
                    'app_id' => $auth_appid,
                    'isconnect' => 1,
                    'type' => 3
                );
                set_model("sites_wechat")->where(['id' => $account_found['id']])->update($update);
                $this->zbn_msg('更改公众号授权接入成功');
            }
        } else {


            $setting = setting_load('wechat_platform');
            $this->view->url = $setting['redirect_uri'] . url('wechat/index/auto_auth');
            return $this->view->fetch();
        }
    }


    /**
     * @param $id
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $id = (int)$id;
        //todo check auth
        $data = set_model($this->sites_wechat)->where(['id' => $id])->find();
        $this->check_admin_auth($data);
        set_model($this->sites_wechat)->where(['id' => $id])->delete();
        //todo delete fans
        $table_name = "sites_wechat_fans_" . $data['id'];
        if (Models::tableExists($table_name)) {
            sql_execute("Drop Table if EXISTS " . config('database.prefix') . $table_name);
        }
        return ['code' => 0, 'msg' => '删除成功'];
    }

    public function change_config($key = "default")
    {

        return $this->view->fetch();
    }

}
