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
use app\attachment\storges\StorgeFactory;
use app\common\controller\Base;
use app\common\controller\ModuleBase;
use app\common\model\File;
use app\common\model\SitesWechat;
use app\mhcms_professional\wechat\WeiXinPlatform;
use app\wechat\util\MhcmsWechatEngine;
use app\wechat\util\WechatUtility;
use think\Cache;
use think\Controller;
use think\Cookie;

class Service extends ModuleBase
{
    public function get_current_ticket()
    {
        global $_W , $_GPC;
        $this->wechat = $wechat = $_W['wechat_account'] = MhcmsWechatEngine::create($_W['account']);
        $signPackage = $wechat->getJssdkConfig(urldecode( $_GPC['url']));
        echo json_encode($signPackage);
    }

    public function download_media($media_id)
    {
        global $_W;
        $account = MhcmsWechatEngine::create($_W['account']);

        $file_data = $account->downloadMedia($media_id);

        $filename = $file_data['url'];
        $file_data['url'] = str_replace(SYS_PATH, '', $filename);
        $file_data['type'] = "Local";
        $file = File::create($file_data);
        $file = File::get(['file_id' => $file['file_id']]);

        //todo upload to cloud storge
        $storge = new StorgeFactory();
        $storge->upload($file, $filename);
        $ret['code'] = 1;
        $ret['data'] = $file;
        $ret['url'] = tomedia($file);
        return $ret;
    }

    /**
     * view wechat material image
     */
    public function image()
    {
        global $_GPC;
        $image = urldecode(trim($_GPC['url']));
        if (empty($image)) {
            exit();
        }
        $content = ihttp_request($image, '', array('CURLOPT_REFERER' => 'http://www.qq.com'));
        header('Content-Type:image/jpg');
        echo $content['content'];
        exit();
    }
}