<?php

namespace app\member\controller;

use app\common\controller\ModuleUserBase;
use app\common\controller\UserBase;
use app\common\model\Models;
use app\common\model\Users;
use app\common\util\forms\input;
use app\member\model\UsersAddress;
use app\sms\model\SmsReport;
use think\Cache;
use think\Db;

class Info extends ModuleUserBase
{
    /**
     *
     */
    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function set_info()
    {
        if ($this->isPost()) {
            $allow_fields = ["sex", "real_name", "email", "description", "nickname", 'mobile', 'hangye', 'mobile'];

            $data = input('param.data/a');
            $data['sex'] = input('param.sex');
            //User Process
            $data = clean_data($data, $allow_fields);
            //Sys Process
            if (is_phone($data['mobile'])) {
                $test = Users::get(['user_name' => $data['mobile']]);
                if ($test && $test['id'] != $this->user['id']) {
                    $this->zbn_msg("手机号码已经被使用！无法更新资料");
                } else {
                    $data['is_verify'] = 1;
                }

            } else {
                $this->error("手机号码不正确");
            }

            if (!is_phone($this->user['user_name'])) {
                $data['user_name'] = $data['mobile'];
            }

            $this->user->save($data);
            $this->success("操作成功 , 您的手机号码已经通过检测", "/");
        } else {
            return $this->view->fetch();
        }
    }

    public function set_address()
    {

        if ($this->isPost()) {
        } else {
            $this->view->addresses = UsersAddress::all(['user_id' => $this->user_id]);
            return $this->view->fetch();
        }
    }


    public function set_password()
    {
        if ($this->isPost()) {
            $pass = input('param.nowpass');

            $pass2 = input('param.pass');
            $repass2 = input('param.repass');

            if (!is_password($pass2)) {
                $this->zbn_msg("密码必须6 ~ 20 位！");
            }
            if ($pass2 != $repass2) {
                $this->zbn_msg("两次密码必须相同！");
            }
            if ($this->user['pass'] != crypt_pass($pass, $this->user['user_crypt'])) {
                $this->zbn_msg("旧密码不正确！");
            } else {
                $this->user->pass = crypt_pass($pass2, $this->user['user_crypt']);
                $this->user->save();
            }
            $this->zbn_msg("操作成功", 1);
        } else {
            return $this->view->fetch();
        }
    }

    public function avatar()
    {
        return $this->view->fetch();
    }

    public function set_mobile()
    {
        global $_W, $_GPC;
        $forward = $_GPC['forward'] ? $_GPC['forward'] : "";
        if ($this->isPost()) {
            $input = json_decode(input("param.data"), true);

            $code = $input['code'];
            /***/
            if (!is_phone($input['mobile'])) {
                return [
                    'code' => 1,
                    'msg' => "请输入11位手机号码数"
                ];
            }
            if ($input['mobile']) {
                if ($code) {
                    // verify code
                    $data_sms = SmsReport::where(['type' => 'sms', 'target' => $input['mobile'], 'status' => 0])->order('id desc ')->find();
                    //$data_sms = Db::name('sms_report')->where()->order("id desc")->find();

                    //todo update code
                    if ($data_sms && $code == $data_sms['content']['code']['value']) {
                        $data_sms->status = 1;
                        $data_sms->save();
                        //Db::name('sms_report')->where(['id' => $data_sms['id'], 'status' => 0])->update($data_sms);
                    } else {
                        $ret = [
                            'code' => 1,
                            'msg' => "手机验证码不正确！"
                        ];
                        return $ret;
                    }
                } else {
                    $ret = [
                        'code' => 1,
                        'msg' => "手机验证码不正确！"
                    ];
                    return $ret;
                }


                if ($input['passport_id']) {
                    $this->user->passport_id = $input['passport_id'];
                }

                if ($input['real_name']) {
                    $this->user->real_name = $input['real_name'];
                }

                if ($input['mobile']) {
                    $this->user->mobile = $input['mobile'];
                    $this->user->user_name = $input['mobile'];
                }


                //
                if ($input['mobile']) {
                    $this->user->is_mobile_verify = 1;
                }

                if ($input['real_name']) {
                    $this->user->is_verify = 2;
                }

                //do mobile

                if ($this->user->save()) {
                    $code = 1;
                    $msg = "认证成功";
                } else {
                    $code = 1;;
                    $msg = "认证成功";
                }


                $ret = [
                    'code' => $code,
                    'msg' => $msg
                    , 'url' => $forward? $forward : nb_url(['r' => 'home/index/index'])
                ];

                return $ret;
            } else {
                return [
                    'code' => 1,
                    'msg' => "数据验证失败"
                ];
            }
        } else {
            return $this->view->fetch();
        }
    }


    public function set_connect()
    {
        if ($this->isPost()) {

        } else {
            return $this->view->fetch();
        }
    }

    public function public_send_email_verify($rand_auth = "")
    {
        $path = "member" . DS . $this->user['id'];
        if ($this->user['email_verify'] == 1) {
            $data['code'] = 1;
            $data['msg'] = "您的邮箱已经通过认证！";
        } else {
            if (empty($rand_auth)) {
                $rand_auth = random(6);
                Cache::set($path, $rand_auth);
                $url = url("member/info/public_send_email_verify", "rand_auth=$rand_auth");
                $send_result = sendmail($this->user['user_name'], "激活您的账户", "您的账户激活码是$rand_auth , 或者点击这里 <a href='$url'>$rand_auth</a> ,");
                if ($send_result) {
                    $data['code'] = 1;
                    $data['msg'] = "发送成功";
                } else {
                    $data['code'] = 2;
                    $data['msg'] = "发送失败";
                }
            } else {
                $auth = Cache::get($path);
                if ($auth == $rand_auth) {
                    Cache::set($path, null);
                    $data['code'] = 1;
                    $data['msg'] = "验证成功";
                } else {
                    $data['code'] = 1;
                    $data['msg'] = "验证失败";
                }
            }
        }

        return $data;
    }

    public function set_email()
    {
        $path = "member" . DS . $this->user['id'];
        if ($this->isPost()) {
            $rand_auth = input('param.rand_auth');
            $user_email = input("param.user_email");
            if ($rand_auth) {
                $auth = Cache::get($path);
                if ($auth && $auth == $rand_auth) {
                    Cache::rm($path);
                    $this->user->email_verify = "1";
                    $this->user->save();
                    $this->zbn_msg("操作成功", 1);
                } else {
                    $this->zbn_msg("操作失败，激活码不正确", 2);
                }
            } elseif ($user_email) {
                $this->zbn_msg("暂时不支持更换登录邮箱", 2);
                if (is_email($user_email)) {
                    $this->user->user_email = $user_email;
                    $this->user->save();
                }


            }
        } else {
            return $this->view->fetch();
        }
    }

    public function verify($type = "personal")
    {
        global $_GPC;
        $url = url('home/index/index');
        //后去模型信息
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = $model->where(['user_id' => $this->user['id']])->find();
        $skip_fields = [];
        switch ($type) {
            case "personal":
                $skip_fields = ['company_passport'];
                if ($detail['personal_verify'] == 99) {
                    $this->view->verifyed = 1;
                }

                break;
            case "company":
                if ($detail['company_verify'] == 99) {
                    $this->view->verifyed = 1;
                }
                break;
        }
        if ($this->isPost() && $model_info) {
            //todo 检测当前申请类型的状态 如果不是0 则终止更新
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['user_id'] = $this->user['id'];

            switch ($type) {
                case "personal":
                    if (!$base_info['personal_passport_pic']) {
                        $this->zbn_msg("对不起，个人认证必须上传手持身份证招聘", 2);
                    }
                    //设置审核状态为待审核
                    $base_info['personal_verify'] = 1;
                    break;
                case "company":
                    if (!$base_info['company_passport']) {
                        return $this->zbn_msg("对不起，企业认证必须上传营业执照", 2);
                    }
                    //设置审核状态为待审核
                    $base_info['company_verify'] = 1;
                    break;
            }

            if ($detail) {
                $res = $model_info->edit_content($base_info, ['user_id' => $this->user['id']]);
            } else {
                $res = $model_info->add_content($base_info);
            }


            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "'$url'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            //模板数据
            if ($type == 'personal') {
                $hide_fields = ['company_passport'];

                $this->view->wait = $detail['personal_verify'];
            }
            if ($type == 'company') {
                $this->view->wait = $detail['company_verify']; // 99  1
            }
            $this->view->type = $type;
            $this->view->list = $model_info->get_user_publish_fields($detail, $hide_fields);
            $this->view->type = $type;
            $this->view->detail = $detail;
            $this->view->model_info = $model_info;
            $this->view->view = $this->view;
            return $this->view->fetch();
        }
    }
}