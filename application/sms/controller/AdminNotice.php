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
namespace app\sms\controller;

use app\common\controller\AdminBase;
use app\common\model\WeixinMsgconfig;
use app\sms\model\Notice;
use app\sms\model\SmsConfig;
use app\wechat\util\MhcmsWechatEngine;
use think\Db;

class AdminNotice extends AdminBase
{

    /**
     * @param string $module
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($module = '')
    {
        $wx_tpl = new Notice();
        $where = [];
        if ($module) {
            $where['module'] = $module;
        }

        $lists = $wx_tpl->where($where)->order('listorder asc')->paginate();
        $this->view->lists = $lists;
        return $this->view->fetch();

    }

    public function wxtpl_config($notice_id)
    {
        global $_W , $_GPC;
        $where = [];

        $where['notice_id'] = $notice_id;
        $where['site_id'] = $this->site['id'];

        $test = WeixinMsgconfig::get($where);


        if ($this->isPost()) {
            $insert = array();

            $data['first'] = array("value" => $_GPC ['tp_first'],
                "color" => $_GPC ['firstcolor'],
            );
            $data['remark'] = array("value" => $_GPC ['tp_remark'],
                "color" => $_GPC ['remarkcolor'],
            );
            for ($i = 0; $i < count($_GPC['keyword']); $i++) {
                if ($_GPC['keyword'][$i]) {
                    $data[$_GPC['keyword'][$i]] = array(
                        "value" => $_GPC['value'][$i],
                        "color" => $_GPC['color'][$i],
                    );
                }
            }

            if($_GPC['miniprogram']['appid']){
                $data['miniprogram'] = $_GPC['miniprogram'];
            }

            $insert['wxtpl_id'] = $_GPC['template_id'];
            $insert['notice_id'] = $notice_id;
            $insert['data'] = $data;
            $insert['tp_url'] = $_GPC['tp_url'];

            if (!$test) {
                $tpl_config = new WeixinMsgconfig();
                $insert['site_id'] = $this->site['id'];
                $tpl_config->isUpdate(false)->save($insert);
            } else {
                $test->isUpdate(true)->save($insert);
            }
            $this->zbn_msg("ok");
        } else {
            if (!$test) {
                $test['data'] = [];
            }

            $engine = MhcmsWechatEngine::create($_W['account']);
            $resp = $engine->getAllPrivateTemplate();
            $this->view->tpls = $resp['template_list'];
            $this->view->detail = $test;
            return $this->view->fetch();
        }
    }


    /**
     * @param $notice_id
     * @return string
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function email_config($notice_id)
    {
        global $_W, $_GPC;
        $where = [];
        $where['notice_id'] = $notice_id;
        $where['site_id'] = $this->site['id'];

        $test = Db::name("email_config")->where($where)->find();
        if ($this->isPost()) {
            $data = $_GPC['data'];
            $data['notice_id'] = $notice_id;
            $data['site_id'] = $this->site['id'];
            if (!$test) {
                $res = Db::name("email_config")->insert($data);
            } else {
                $res = Db::name("email_config")->where($where)->update($data);
            }

            if ($res) {
                $this->zbn_msg("ok");
            } else {
                $this->zbn_msg("操作失败，数据未改变");
            }
        } else {
            if (!$test) {
                $test['data'] = [];
            }

            $this->view->detail = $test;
            return $this->view->fetch();
        }
    }

    /**
     * @param $notice_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function smstpl_config($notice_id)
    {
        $where = [];
        $where['notice_id'] = $notice_id;
        $where['site_id'] = $this->site['id'];

        $test = SmsConfig::get($where);


        if ($this->isPost()) {
            $_GPC = input('param.');
            $insert = array();
            $data = [];
            for ($i = 0; $i < count($_GPC['keyword']); $i++) {
                if ($_GPC['keyword'][$i]) {
                    $data[$_GPC['keyword'][$i]] = array(
                        "value" => $_GPC['value'][$i],
                        "color" => $_GPC['color'][$i],
                    );
                }
            }

            $insert['tpl_id'] = $_GPC['template_id'];
            $insert['notice_id'] = $notice_id;
            $insert['data'] = $data;
            if (!$test) {
                $tpl_config = new SmsConfig();
                $insert['site_id'] = $this->site['id'];
                $tpl_config->isUpdate(false)->save($insert);
            } else {
                $test->isUpdate(true)->save($insert);
            }
            $this->zbn_msg("ok");
        } else {
            if (!$test) {
                $test['data'] = [];
            }
            $this->view->detail = $test;
            return $this->view->fetch();
        }
    }
}