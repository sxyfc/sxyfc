<?php

namespace app\common\controller;

use app\common\model\Sites;

class ApiUserBase extends ApiBase
{
    public function _initialize()
    {
        global $_W, $_GPC;
        parent::_initialize();
        if (!$this->user) {
            $ret['code'] = 3;
            $ret['msg'] = "请登录"; $url = url('/sso/passport/login');
            $ret['javascript'] = "mhcms_frame_work.goToUrl(\"$url\")";
            echo json_encode($ret);
            die();
        }else{
            //check fans

            //todo wechat openid

            if($_W['site_wechat']){
                $_W['wechat_fans'] = $_W['wechat_fans_model']->where(['user_id' =>$this->user['id']])->find();

                if( $_W['wechat_fans']){
                    $_W['openid'] = $_W['wechat_fans']['id'];
                }
            }else{

            }


        }
    }
}