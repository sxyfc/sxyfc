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

use app\common\controller\Base;
use app\wechat\util\MhcmsWechatAccountBase;
use app\wechat\util\MhcmsWechatEngine;
use app\wechat\util\WechatUtility;
use think\Controller;

class MessageService extends Base {

    /** @var MhcmsWechatAccountBase $engine */
    public $engine;
    /**
     * @param string $appid
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function callback($appid = ""){
        global $_W, $_GPC , $engine;

        //fuck platform in
        if(!empty($_GPC['appid'])) {
            $appid = ltrim($_GPC['appid'], '/');
            if ($appid == 'wx570bc396a51b8ff8') {
                $_W['account'] = array(
                    'type' => '3',
                    'app_id' => 'wx570bc396a51b8ff8',
                    'level' => 4,
                    'token' => 'platformtestaccount'
                );
            }
        }

        if (!isset($_W['account'])) {
            WechatUtility::logging("died 49");
            die();
        }

        $_W['debug'] = config('app_debug');
        define("TOKEN", $_W['account']['token']);
        $echoStr = isset($_GPC['echostr']) ? $_GPC['echostr'] : "";
        //This  code is only for Fucking WeChat In
        if ($echoStr && checkSignature()) {
            echo $echoStr;
            exit;
        }
        //serve our dear customer
        $_W['engine'] = $this->engine = new MhcmsWechatEngine($_W['account']);
        $this->engine->start();

    }
}