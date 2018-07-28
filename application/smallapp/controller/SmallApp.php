<?php

namespace app\smallapp\controller;

use app\common\controller\ApiBase;
use app\common\controller\Base;
use app\common\model\Users;
use app\sms\model\Notice;
use app\sso\controller\Passport;
use app\wechat\util\WechatUtility;
use think\Db;
use think\Loader;

/**
 * @property int node_type_id
 */
class SmallApp extends ApiBase
{

    public $small_app;
    public $fans;
    public $openid;


    public function _initialize()
    {
        global $_GPC, $_W;
        parent::_initialize();
        $_W['app_fans_model'] = Db::name('sites_smallapp_fans');
        if (!$this->small_app || $this->small_app['site_id'] != $_W['site']['id']) {
            WechatUtility::logging("debug info: small app no fount");
            die("small app no fount");
        } else {
            $_W['fans'] = $_W['app_fans_model']->where(['user_id' => $this->user['id']])->find();
        }


        if (!$_W['fans'] && $_GPC['openid']) {
            $_W['openid'] = $this->openid = $_GPC['openid'];
            $where['id'] = $this->openid;

            $data = $where;
            if ($this->find_or_create($data, $where)) {
                $this->fans = $where;
            }
        }
    }

    public function find_or_create($data, $where)
    {
        global $_GPC, $_W;
        $data['follow_time'] = date("Y-m-d H:i:s");
        $this->fans = $_W['app_fans_model']->where($where)->find();
        if (!$this->fans) {
            $data['app_id'] = $this->small_app['id'];
            $data['site_id'] = $_W['site']['id'];
            $data['id'] = $_W['app_fans_model']->insert($data , false , true);
            return $data;
        }else{
            return $this->fans;
        }
    }
}