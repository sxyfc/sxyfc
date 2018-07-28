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
namespace app\core\controller;

use app\common\controller\AdminBase;
use app\common\model\SitesWechat;

class Weixin extends AdminBase
{
    public function config($id)
    {

        $site_id = (int)$id;
        $site_weixin_config = SitesWechat::get($site_id);
        if(!$site_weixin_config){
            $insert = [];
            $insert['site_id'] = $site_id;
            SitesWechat::create($insert);
        }

        if($this->isPost()){
            $data = input('param.data/a');
            $site_weixin_config->save($data);
            return $this->zbn_msg("ok");
        }else{
            $this->view->detail =$site_weixin_config = SitesWechat::get($site_id);
            return $this->view->fetch();
        }
    }

}