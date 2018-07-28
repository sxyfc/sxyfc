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


use think\Db;


/**
 * @return bool
 * @throws Exception
 */
function checkSignature()
{
    global $_GPC , $_W;
    // you must define TOKEN by yourself
    if (!defined("TOKEN")) {
        throw new \Exception('TOKEN is not defined!');
    }
    $signature = $_GPC["signature"];
    $SYS_TIME = $_GPC["timestamp"];
    $nonce = $_GPC["nonce"];
    $token = TOKEN;
    $tmpArr = array($token, $SYS_TIME, $nonce);
    // use SORT_STRING rule
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode($tmpArr);
    $tmpStr = sha1($tmpStr);
    return $tmpStr == $signature;
}