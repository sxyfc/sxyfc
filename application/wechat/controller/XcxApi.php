<?php
namespace app\wechat\controller;

use app\common\controller\Base;
use app\common\controller\UserBase;
use app\common\model\NodeHits;
use app\common\model\NodeIndex;
use app\common\model\Roots;
use app\common\model\Sites;
use app\common\model\SitesWechat;
use app\common\model\Users;
use app\sso\controller\Passport;
use think\Cache;
use think\Db;

/**
 * @property int node_type_id
 */
class XcxApi extends Base
{
    public function openid($site_id , $code){
        $site_wechat = SitesWechat::get(['site_id' => $site_id]);
        $this->app_id = $site_wechat['xcx_config']['app_id'];
        $this->app_secret = $site_wechat['xcx_config']['app_secret'];

        $api_url  ="https://api.weixin.qq.com/sns/jscode2session?appid={$this->app_id}&secret={$this->app_secret}&js_code=$code&grant_type=authorization_code";
        $res = ihttp_get($api_url);

        $res_content = json_decode($res['content'] , true);

        $where['openid'] =  $res_content['openid'];
        $where['connect_id'] =  2;

        $connect = Db::name('users_connect')->where($where)->find();
        if($connect){
            if($connect['user_id']){
                //TODO  直接返回授权字符串
                $sso = new Passport();
                $current_user = Users::get(['user_id' => $connect['user_id']]);//mobile
                $res_content['auth_str'] =  $sso->log_user_in($current_user);
                $res_content['user_id'] =  $current_user['id'];

                $ret['data'] = $res_content;
                $ret['code'] = 1;
            }else{
                $ret['data'] = $res_content;
                $ret['code'] = 1;
            }
        }else{
            Db::name('users_connect')->insert($where);
            $ret['code'] = 1;
            $ret['data'] = $res_content;
        }
        echo json_encode($ret) ;
    }


    public function bind_user(){
        global $_GPC;
        //check openid
        $connect_where = ['openid'=>$_GPC['openid'] , 'connect_id'=>$_GPC['connect_id']];
        $connect = Db::name("users_connect")->where($connect_where)->find();

        $current_user = Users::get(['user_name' => $_GPC['mobile']]);//mobile

        if($connect['user_id']){
            $sso = new Passport();
            $current_user = Users::get(['user_id' => $connect['user_id']]);//mobile
            $ret_data['auth_str'] =  $sso->log_user_in($current_user);
            $ret_data['user_id'] = $current_user['id'];
            $res['data'] = $ret_data;
            $res['code'] = 1;
        }else{
            if ($connect && $current_user['pass'] == crypt_pass($_GPC['password'], $current_user['user_crypt'])) {
                $sso = new Passport();
                $connect['user_id'] = $current_user['id'];
                Db::name("users_connect")->where($connect_where)->update($connect);
                $ret_data['user_id'] = $current_user['id'];
                $ret_data['auth_str'] = $sso->log_user_in($current_user);
                $res['data'] = $ret_data;
                $res['code'] = 1;
            }else{
                $res = [
                    'code' => 3 ,
                    'msg' => '对不起用户名密码不正确'
                ];
            }
        }

        echo json_encode($res);
    }

    public function create_user($openid , $site_id){
        global $_GPC;
        $ret = [];
        $foreword_url = "";
        $code = 2;
        $user_data['password'] = $_GPC['password'];//) == input('param.password1') ? input('param.password') : $this->zbn_msg("两次密码必须一样", 2);;
        $user_data['user_name'] = $_GPC['mobile'];
        if(!is_phone($user_data['user_name'])){
            $ret['code'] = 3;
            $ret['msg'] = "手机号码不正确！";
            echo json_encode($ret);
            return;
        }

        if(!$user_data['password']){
            $ret['code'] = 3;
            $ret['msg'] = "请输入密码！";
            echo json_encode($ret);
            return;
        }

        $connect_where = ['openid'=>$_GPC['openid'] , 'connect_id'=>$_GPC['connect_id']];
        $connect = Db::name("users_connect")->where($connect_where)->find();
        if(!$connect){
            $ret['code'] = 3;
            $ret['msg'] = "对不起，非法操作请重新打开程序！";
            echo json_encode($ret);
            return;
        }

        if($connect['user_id']){
            $ret['code'] = 3;
            $ret['msg'] = "对不起，非法操作请重新打开程序！";
            echo json_encode($ret);
            return;
        }

        $user = new Users();
        $user_data['site_id'] = $site_id;
        $user_data['user_crypt'] = random(6);
        $user_data['pass'] = crypt_pass($user_data['password'], $user_data['user_crypt']);
        $validate = \think\Loader::validate('Users');
        $user_data['status'] = 99;
        $user_data['user_role_id'] = 12;
        $user_data['created'] = date("Y-m-d H:i:s");

        $result = $validate->check($user_data);
        if (!$result) {
            $msg = $validate->getError();
            $ret['code']  = 3;
            $ret['msg'] = $msg;
        } else {
            if ($res = $user->allowField(true)->validate(true)->save($user_data)) {
                //注册成功以后 更新

                $connect['user_id'] = $user['id'];
                Db::name("users_connect")->where($connect_where)->update($connect);


                $passport = new Passport();
                $ret_data['auth_str'] = $passport->log_user_in($user);
                $ret_data['user_id'] =$user['id'];

                $ret['code'] = 1;
                $ret['data'] = $ret_data;
            } else {
                $ret['code']  = 3;
                $ret['msg'] = $res;
            }
        }
        echo json_encode($ret);

    }

}