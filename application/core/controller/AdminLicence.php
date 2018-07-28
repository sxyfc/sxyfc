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
use app\common\model\Models;
use think\Config;
use think\Db;

class AdminLicence extends AdminBase
{
    public function index()
    {
        Config::load(CONF_PATH . 'licence.php');
        $licence = config('licence');
        if ($this->isPost()) {
            $data = input('param.data/a');
            $licence['product_licence_code'] = $data['product_licence_code'];
            $licence['domain'] = $this->request->host();
            $licence['product_sign'] = "mhcms";




            $licence_info  = $this->_check_licence($licence);
            if($licence_info['code'] == 1){
                $_licence = [];
                $_licence['licence'] = $licence;
                self::write_config('licence', $_licence);
                $this->zbn_msg("操作成功！");
            }else{
                $this->zbn_msg("您的授权不正确！");
            }


        } else {

            $this->view->licence = $licence;
            return $this->view->fetch();
        }
    }

    public  function _check_licence($licence)
    {
        $url = 'http://cloud.bao8.org/product/index/check_licence';
        $res = ihttp_post($url, $licence);
        $licence_info = mhcms_json_decode($res['content']);
        return $licence_info;
    }
}