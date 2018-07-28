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
use app\wechat\util\MhcmsWechatEngine;
use app\wechat\util\WechatUtility;
use think\Cache;
use think\Log;

class PlatformService extends Base
{
    public function notify()
    {
        global $_W, $_GPC;
        $platform_setting = setting_load('wechat_platform');
        $post = file_get_contents('php://input');
        //WechatUtility::logging('start debug ', 'account-ticket' . $post);

        $decode_ticket = aes_decode($post, $platform_setting['encodingaeskey']);

        if (empty($post) || empty($decode_ticket)) {
            //WechatUtility::logging("ticket 解密失败");
            exit('fail');
        }
        Log::write($decode_ticket);
        $ticket_xml = isimplexml_load_string($decode_ticket, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (empty($ticket_xml)) {
            //Log::write(" ticket_xml failed");
            //Log::write($ticket_xml);
            exit('fail');
        }
        if (!empty($ticket_xml->ComponentVerifyTicket) && $ticket_xml->InfoType == 'component_verify_ticket') {
            Cache::set('account:ticket', strval($ticket_xml->ComponentVerifyTicket));
            //WechatUtility::logging('success debug ', 'account-ticket' . strval($ticket_xml->ComponentVerifyTicket));
        } else {
            WechatUtility::logging('failed debug ', 'account-ticket' . $post);
        }
        exit('success');
    }
}