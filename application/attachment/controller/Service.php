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
namespace app\attachment\controller;

use app\common\controller\Base;

class Service extends Base
{
    public function image($attach)
    {
        if ($attach) {
            $content = ihttp_request($attach, '', array('CURLOPT_REFERER' => 'http://www.qq.com'));
            header('Content-Type:image/jpg');
            echo $content['content'];
        }
        exit();
    }
}