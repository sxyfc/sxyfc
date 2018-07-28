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
namespace app\sms\providers;

use Flc\Alidayu\App;
use Flc\Alidayu\Client;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;

class Alidayu extends Providers{

    private $config;

    /**
     * Alidayu constructor.
     * @param $sms_site_config
     */
    public function __construct($sms_site_config)
    {
        global $_W;
        //todo init config
        $this->config = json_decode($sms_site_config['config'] , 1);
    }

    /**
     * @param $target
     * @param $params
     * @param $tpl_id
     * @return false|object
     */
    function send($target, $params ,$tpl_id)
    {
        // TODO: Implement send() method.
// 使用方法一
        $client = new Client(new App($this->config));
        $req    = new AlibabaAliqinFcSmsNumSend;
        $req->setRecNum($target)
            ->setSmsParam($params)
            ->setSmsFreeSignName($this->config['signature'])
            ->setSmsTemplateCode($tpl_id);
        $resp = $client->execute($req);
        if($resp->result->success){
            $res['code'] = 1;
            $res['log'] = $resp;
        }else{
            $res['code'] = 2;
            $res['log'] = $resp;
        }
        return $res;
    }
}