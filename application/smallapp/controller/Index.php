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
namespace app\smallapp\controller;

use app\attachment\mhcms_classes\MhcmsFile;
use app\common\controller\Base;
use app\mhcms_professional\wechat\WeiXinPlatform;
use app\wechat\util\WechatUtility;
use think\Cache;

class Index extends Base
{

    /**
     * @param $site_id
     * @return string
     */
    public function auto_auth($site_id)
    {
        global $_W, $_GPC;
        $account_platform = new WeiXinPlatform();
        //check if there is already one
        $small_app = set_model("sites_smallapp");
        if ($_GPC['do'] == "success") {
            if (empty($_GPC['auth_code'])) {
                $this->error('授权登录失败，请重试');
            }

            $auth_info = $account_platform->getAuthInfo($_GPC['auth_code']);

            $auth_refresh_token = $auth_info['authorization_info']['authorizer_refresh_token'];
            $auth_appid = $auth_info['authorization_info']['authorizer_appid'];

            $account_info = $account_platform->getAccountInfo($auth_appid);

            if (!isset($account_info['authorizer_info']['MiniProgramInfo'])) {
                $this->error('小程序授权登录失败，您可能选择了公众号！');
            }
            $account_found = $small_app->where(array('org_id' => $account_info['authorizer_info']['user_name']))->whereOr(['app_id'=>$auth_appid])->find();
            //todo create wechat account



            if (!$account_found) {
                $smallapp_data = array(
                    'app_name' => $account_info['authorizer_info']['nick_name'],
                    'account' => $account_info['authorizer_info']['alias'],
                    'org_id' => $account_info['authorizer_info']['user_name'],
                    'app_id' => $auth_appid,
                    'auth_refresh_token' => $auth_refresh_token,
                    'encodingaeskey' => $account_platform->encodingaeskey,
                    'token' => $account_platform->token,
                    'site_id' => $site_id,
                    'type' => 2
                );

                //todo create sites_smallapp
                $smallapp_id = $small_app->insert($smallapp_data, false, true);

                if ($smallapp_id) {
                    // create fans data tablegroup_ids
                    WechatUtility::create_wechat_smallapp($smallapp_id);
                    Cache::set('account:auth:refreshtoken:' . $smallapp_id, $auth_refresh_token);
                    //todo save pic
                    $headimg = ihttp_request($account_info['authorizer_info']['head_img']);
                    file_put_contents(SYS_PATH . 'upload_file/headimg_' . $smallapp_id . '.jpg', $headimg['content']);
                    $headimg_file = MhcmsFile::create('upload_file/headimg_' . $smallapp_id . '.jpg');

                    $qrcode = ihttp_request($account_info['authorizer_info']['qrcode_url']);
                    file_put_contents(SYS_PATH . 'upload_file/qrcode_' . $smallapp_id . '.jpg', $qrcode['content']);
                    $qrcode_file = MhcmsFile::create('upload_file/qrcode_' . $smallapp_id . '.jpg');
                    //todo save attach file
                    $update = [];
                    $update['qrcode'] = $qrcode_file['file_id'];
                    $update['avatar'] = $headimg_file['file_id'];
                    //update sites wechat account avatar and qrcode info
                    $small_app->where(['id' => $smallapp_id])->update($update);
                    $this->message('授权登录成功', 1 , 'javascript:void(0);');
                }
                $this->error("创建接入数据失败！");

            } else {
                $update = array(
                    'auth_refresh_token' => $auth_refresh_token,
                    'encodingaeskey' => $account_platform->encodingaeskey,
                    'token' => $account_platform->token,
                    'app_id' => $auth_appid,
                    'type' => 2
                );
                Cache::set('account:auth:refreshtoken:' . $account_found['id'], $auth_refresh_token);
                $small_app->where(['id' => $account_found['id']])->update($update);
                $this->message('更改小程序授权接入成功' , 1 , 'javascript:void(0);');
            }
        } else {
            $authurl = $account_platform->getAuthLoginUrl($site_id, "smallapp");
            $this->view->authurl = $authurl;
            //header("location:$authurl");
            return $this->view->fetch();
        }
    }

}