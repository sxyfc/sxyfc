<?php

namespace app\smallapp\controller;

use app\common\controller\Base;
use app\common\model\Hits;
use app\common\model\Models;
use app\common\model\Users;
use app\common\util\pkcs7\Prpcrypt;
use app\sms\model\Notice;
use app\sso\controller\Passport;
use app\wechat\util\MhcmsWechatEngine;
use app\wechat\util\WechatUtility;
use think\Db;
use think\Loader;
use think\Log;

/**
 * @property int node_type_id
 */
class Service extends SmallApp
{

    public function get_config()
    {
        $ret['code'] = 1;
        $ret['data'] = mhcms_json_decode($this->small_app['config']);
        return $ret;
    }
    public function get_phone_number()
    {
        global $_W, $_GPC;
        $query = $_GPC['query'];
        if (!is_array($query)) {
            $query = mhcms_json_decode($query);
        }

        $code = $query['code'];
        $encryptedData = $query['encryptedData'];
        $iv = $query['iv'];


        $engine = MhcmsWechatEngine::create($this->small_app);
        $res = $engine->get_openid($code);
        $pc = new Prpcrypt($this->small_app['app_id'], $res['session_key']);
        $errCode = $pc->decrypt_app($encryptedData, $iv, $data);

        WechatUtility::logging("-openid info-" , $res);

        $data = mhcms_json_decode($data);

        if(!$this->user){
            $this->user = Users::get(['user_name' => $data['phoneNumber']]);
        }
        if (is_phone($data['phoneNumber']) && $this->user) {
            //todo find the mobile user adn update user
            $where = [];
            if (!$_W['global_config']['group']['share_account']) {
                $where['site_id'] = $_W['site']['id'];
            }

            //first get unionid
            if ($res['unionid']) {
                // 查找union id 用户
                $where1 = $where;
                $user_data['wechat_unionid'] = $where1['wechat_unionid'] = $res['unionid'];
                $union_current_user = Users::get($where1);

                //查找手机用户
                $_where2 = $where;
                $_where2['user_name'] = $data['phoneNumber'];
                $mobile_current_user = Users::get($_where2);


                if ($union_current_user && $mobile_current_user) {//两者
                    if($mobile_current_user['id'] != $mobile_current_user['id']){//两者不同一个
                        $union_current_user->wechat_unionid = "";
                        $union_current_user->save();
                    }
                    $current_user = $mobile_current_user;
                }else{
                    //两者一个

                    if ($union_current_user && !$mobile_current_user) {
                        $union_current_user->user_name = $data['phoneNumber'];
                        $union_current_user->save();
                        $current_user = $union_current_user;
                    }

                    if (!$union_current_user && $mobile_current_user) {
                        $mobile_current_user->wechat_unionid = $res['unionid'];
                        $union_current_user->save();
                        $current_user = $mobile_current_user;
                    }

                    if(!$union_current_user && !$mobile_current_user){
                        $user_data['user_name'] = $data['phoneNumber'];
                        $current_user = Users::create_user($user_data);
                    }
                }




            } else {
                $where['user_name'] = $data['phoneNumber'];
                $current_user = Users::get($where);
                if($current_user){
                    if($current_user['wechat_unionid'] !=  $res['unionid']){
                        $current_user->wechat_unionid = $res['unionid'];
                        $current_user->save();
                    }
                }else{
                    $user_data['user_name'] = $data['phoneNumber'];
                    $current_user = Users::create_user($user_data);
                }
            }

            if (!$current_user) {
                $ret['code'] = 3;
                $ret['msg'] = "对不起 系统错误！";
                return $ret;
            } else {
                //upgrade user group
                if ($current_user->user_role_id == 4) {
                    $current_user->user_role_id = 2;
                }
                $current_user->is_mobile_verify = 1;
                $current_user->save();

                if (!$this->fans['user_id']) {
                    $_W['app_fans_model']->where(['openid' => $this->fans['openid']])->update(['user_id' => $current_user['id']]);
                }
                $user = Users::get(['id' => $current_user['id']]);
                $auth_str = $user->log_user_in();
                $data['auth_str'] = $auth_str;
                $data['user'] = $user;
                $ret['code'] = 1;
                $ret['msg'] = "手机号码认证成功！";
                $ret['data'] = $data;
                echo json_encode($ret) ;
            }


        } else {
            WechatUtility::logging('mobile fetch fail', $encryptedData . '#' . $iv . '#' . $data);
            $ret['code'] = 3;
            $ret['msg'] = "对不起 手机号码获取失败！" . $errCode;

            echo json_encode($ret) ;
        }
    }

    public function get_openid()
    {
        global $_W, $_GPC;
        $query = $_GPC['query'];
        $code = $query['code'];
        $small_app = $this->small_app;
        // create wechat engine
        $engine = MhcmsWechatEngine::create($small_app);
        $res_content = $engine->get_openid($code);
        if (!$res_content['openid']) {
            $ret['code'] = 0;
            $ret['msg'] = "对不起 获取用户信息失败1" . $res_content['msg'];
            echo json_encode($ret);
            die();
        } else {
            $where = [];
            $where['openid'] = $res_content['openid'];
            $this->fans = $this->find_or_create($where, $where);

            if ($res_content['unionid']) {
                $user_data['wechat_unionid'] = $res_content['unionid'];
                $where = [];
                $where['wechat_unionid'] = $res_content['unionid'];
                $current_user = Users::get($where);
            }

            if (!$current_user) {
                $user_data['user_name'] = $this->fans['openid'];

                $where = [];
                $where['user_name'] = $res_content['openid'];
                $current_user = Users::get($where);
            }

            if (!$current_user) {
                $current_user = Users::create_connect_user($user_data);
            }else{
                $current_user->wechat_unionid = $res_content['unionid'];
                $current_user->save();
            }

            if(!$res_content['unionid']){
                WechatUtility::logging("----------" , "union id is not binded");
            }


            $where = [];
            $where['openid'] = $res_content['openid'];
            $_W['app_fans_model']->where($where)->update(['user_id' => $current_user['id']]);

            $res_content['auth_str'] = $current_user->log_user_in();
            $res_content['user_id'] = $current_user['id'];
            $res_content['user'] = $current_user;
            $ret['data'] = $res_content;
            $ret['code'] = 1;
            echo json_encode($ret);
            die();
        }
    }


    public function get_all_info(){
        global $_W, $_GPC;
        $query = $_GPC['query'];

        $code = $query['code'];
        $encryptedData = $query['encryptedData'];
        $iv = $query['iv'];


        $engine = MhcmsWechatEngine::create($this->small_app);
        $res = $engine->get_openid($code);
        $pc = new Prpcrypt($this->small_app['app_id'], $res['session_key']);
        $errCode = $pc->decrypt_app($encryptedData, $iv, $data);

        WechatUtility::logging("--------" , $data);
        $res_content = mhcms_json_decode($data);

        $openid = $res_content['openId'];
        $unionid = $res_content['unionId'];
        if (!$openid) {
            $ret['code'] = 0;
            $ret['msg'] = "对不起 获取用户信息失败1" . $res_content['msg'];
            echo json_encode($ret);
            die();
        } else {
            $where = [];
            $where['openid'] = $openid;
            $this->fans = $this->find_or_create($where, $where);

            if ($unionid) {
                $user_data['wechat_unionid'] = $unionid;
                $where = [];
                $where['wechat_unionid'] = $unionid;
                $current_user = Users::get($where);
            }

            if (!$current_user) {
                $user_data['user_name'] = $this->fans['openid'];

                $where = [];
                $where['user_name'] = $openid;
                $current_user = Users::get($where);
            }

            if (!$current_user) {
                $current_user = Users::create_connect_user($user_data);
            }else{
                $current_user->wechat_unionid = $unionid;
                $current_user->save();
            }

            if(!$unionid){
                WechatUtility::logging("----------" , "union id is not binded");
            }


            $where = [];
            $where['openid'] = $openid;
            $_W['app_fans_model']->where($where)->update(['user_id' => $current_user['id']]);
            $res_content['openid'] = $openid;
            $res_content['auth_str'] = $current_user->log_user_in();
            $res_content['user_id'] = $current_user['id'];
            $res_content['user'] = $current_user;
            $ret['data'] = $res_content;
            $ret['code'] = 1;
            echo json_encode($ret);
            die();
        }
    }
    /**
     *
     * 发送绑定收集验证码逻辑函数
     * 如果手机号码已经被注册 那么检测一下该用户是否有绑定过小程序
     * 如果绑定过了提示换号
     * 如果未绑定 发送收集验证码  隐藏密码框
     * 如果手机号码没有被注册
     * 则发送验证码
     * @param $mobile
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function send_bind_code($mobile)
    {
        global $_W;
        /**
         * 是否开启分站用户数据隔离
         */
        $where['user_name'] = $mobile;
        if ($_W['global_config']['split_user_data'] == 1) {
            $where['site_id'] = $_W['site']['id'];
        }
        $user = Users::get($where);
        if ($user) {
            $smallapp_fans = $_W['app_fans_model']->where(['user_id' => $user['id']])->find();
            if ($smallapp_fans) {
                $ret['code'] = 3;
                $ret['msg'] = "您输入的手机号码已经绑定过微信小程序了！";
                return $ret;
            }
        }

        //todo send sms_code
        $notice = Notice::get(['tpl_name' => '验证码发送']);
        //Notice::send_sms();
        $code = mt_rand(1000, 9999);
        $resp = $notice->send_sms($mobile, ['yzm' => $code]);

        if (!$resp) {
            $ret = [
                'code' => 0,
                'msg' => '对不起短信发送失败，请联系管理员配置后台短信'
            ];
            return $ret;
        } else {
            return $resp;
        }

    }


    /**
     * 开始绑定小程序用户
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function bind_user()
    {
        global $_W, $_GPC;
        //check openid
        $connect_where = ['openid' => $_GPC['openid']];
        $connect = Db::name("sites_smallapp_fans_" . $this->small_app['id'])->where($connect_where)->find();


        $current_user = Users::get(['user_name' => $_GPC['mobile']]);//mobile

        if ($connect['user_id']) {
            $sso = new Passport();
            $current_user = Users::get(['user_id' => $connect['user_id']]);//mobile
            $ret_data['auth_str'] = $sso->log_user_in($current_user);
            $ret_data['user_id'] = $current_user['id'];
            $res['data'] = $ret_data;
            $res['code'] = 1;
        } else {
            if ($connect && $current_user['pass'] == crypt_pass($_GPC['password'], $current_user['user_crypt'])) {
                $sso = new Passport();
                $connect['user_id'] = $current_user['id'];
                Db::name("users_connect")->where($connect_where)->update($connect);
                $ret_data['user_id'] = $current_user['id'];
                $ret_data['auth_str'] = $sso->log_user_in($current_user);
                $res['data'] = $ret_data;
                $res['code'] = 1;
            } else {
                $res = [
                    'code' => 3,
                    'msg' => '对不起用户名密码不正确'
                ];
            }
        }

        echo json_encode($res);
    }

    /**
     * @param $openid
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function create_user($openid)
    {
        global $_W, $_GPC;


        $ret = [];
        $foreword_url = "";
        $code = 2;
        $user_data['password'] = $_GPC['password'];//) == input('param.password1') ? input('param.password') : $this->zbn_msg("两次密码必须一样", 2);;
        $user_data['user_name'] = $_GPC['mobile'];

        //todo verify mobile code
        $code_where['target'] = $user_data['user_name'];
        $code_where['status'] = 0;
        //$code_where['create_at'] = ['GT' , date("Y-m-d H:i:s" , strtotime("-300 seconds"))];
        $res = set_model('sms_report')->where($code_where)->order('id desc')->find();
        if (!$res) {
            $ret['code'] = 3;
            $ret['msg'] = "验证码错误！";
            return $ret;
        }


        if (!is_phone($user_data['user_name'])) {
            $ret['code'] = 3;
            $ret['msg'] = "手机号码不正确！";
            echo json_encode($ret);
            return;
        }

        /**
         *
         * if (!$user_data['password']) {
         * $ret['code'] = 3;
         * $ret['msg'] = "请输入密码！";
         * echo json_encode($ret);
         * return;
         * }
         */

        $user_data['password'] = ""; //随机密码

        $connect_where = ['id' => $_GPC['openid']];
        $connect = Db::name("sites_smallapp_fans_" . $this->small_app['id'])->where($connect_where)->find();
        if (!$connect) {
            $ret['code'] = 3;
            $ret['msg'] = "对不起，非法操作请重新打开程序！";
            echo json_encode($ret);
            return;
        }

        if ($connect['user_id']) {
            $ret['code'] = 3;
            $ret['msg'] = "对不起，您已经绑定过用户了 无法再次绑定！";
            echo json_encode($ret);
            return;
        }

        $user = new Users();
        $user_data['site_id'] = $_W['site']['id'];
        $user_data['user_crypt'] = random(6);
        $user_data['pass'] = "NOTSET"; //crypt_pass($user_data['password'], $user_data['user_crypt']);

        $user_data['user_status'] = $user_data['status'] = 99;
        $user_data['user_role_id'] = 2;
        $user_data['created'] = date("Y-m-d H:i:s");

        //todo avatar

        //todo nickname

        //todo gender

        //todo ip


        //$validate = Loader::validate('Users');
        //$result = $validate->check($user_data);


        $where['user_name'] = $user_data['user_name'];
        /**
         * 是否开启分站用户数据隔离
         */
        if ($_W['global_config']['split_user_data'] == 1) {
            $where['site_id'] = $_W['site']['id'];
        }
        $user = Users::get($where);


        //已存在用户
        if ($user) {
            $smallapp_fans = Db::name("sites_smallapp_fans_" . $this->small_app['id'])->where(['user_id' => $user['id']])->find();
            if ($smallapp_fans) {
                $ret['code'] = 1;
                $ret['msg'] = "您输入的手机号码已经绑定过微信小程序了！";
            } else {
                //注册成功以后 绑定
                $connect['user_id'] = $user['id'];
                Db::name("sites_smallapp_fans_" . $this->small_app['id'])->where($connect_where)->update($connect);
                $ret['code'] = 1;
                $ret['msg'] = "绑定成功";
                $ret_data['auth_str'] = $user->log_user_in();
                $ret_data['user_id'] = $user['id'];
                $ret['data'] = $ret_data;
            }
        } else {
            $user = new Users();
            //不存在用户
            if ($res = $user->allowField(true)->validate(true)->save($user_data)) {
                //注册成功以后 绑定
                $connect['user_id'] = $user['id'];
                Db::name("sites_smallapp_fans_" . $this->small_app['id'])->where($connect_where)->update($connect);

                $ret_data['auth_str'] = $user->log_user_in();
                $ret_data['user_id'] = $user['id'];

                $ret['code'] = 1;
                $ret['data'] = $ret_data;
            } else {
                $ret['code'] = 3;
                $ret['msg'] = $res;
            }
        }
        return $ret;
    }


    /**
     * 加载广告
     * @param $ad_group_name
     * @return array
     * @throws \think\exception\DbException
     */
    public function load_ad()
    {
        global $_W, $_GPC;
        $query = $_GPC['query'];

        if (!is_array($query)) {
            $query = mhcms_json_decode($query);
        }
        $ad_group_name = $query['ad_group_name'];
        $where['group_name'] = $ad_group_name;
        $group = set_model('adgroup')->where($where)->find();
        if (!$group) {
            $ret = ['code' => 1, 'data' => []];
        } else {
            $where = [];
            $where['group_id'] = $group['id'];
            $where['site_id'] = $_W['site']['id'];
            $where['status'] = 99;
            $model = set_model('advertise');
            /** @var Models $model_info */
            $ads = Models::list_item($where, 'advertise', true);
            $ret = ['code' => 1, 'data' => $ads];
        }

        echo json_encode($ret);
        die();
    }

}