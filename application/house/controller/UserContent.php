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
namespace app\house\controller;


use app\common\model\Models;
use app\common\util\Money;
use app\common\util\Point;


use app\sms\model\Notice;
use think\Db;


class UserContent extends HouseUserBase
{
    private $house_esf = "house_esf";
    private $house_rent = "house_rent";
    private $house_info = "house_info";
    private $house_loupan = "house_loupan";


    public function index()
    {
        global $_W;
        $where['user_id'] = $this->user['id'];
        $where['site_id'] = $_W['site']['id'];
        $this->view->lists = $lists = set_model("house_info")->where($where)->paginate();

        return $this->view->fetch();
    }

    public function publish_loupan()
    {
        global $_W, $_GPC;
        $model = set_model($this->house_loupan);
        $model_info = $model->model_info;
        if ($this->isPost(true) && $model_info) {
            $base_info = input('post.data/a');//get the base info


            $res = $model_info->add_content($base_info);

            if ($res['code'] == 1) {
                token();
                $to_url = url('house/index/index');
                //todo send
                $tpl_config = mhcms_json_decode($_W['tpl_config']);
                unset($tpl_config['miniprogram']);
                $params['header'] = "您好 有用户录入了楼盘， " . $res['item']['loupan_name'];
                $tpl_config['tp_url'] = url('house/check/index', [], true, true);
                Notice::send("系统通知", "wxmsg", $_W['module_config']['admin_openid'], $params, $tpl_config);


                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "'$to_url'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->assign('field_list', $model_info->get_user_publish_fields());
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function publish_esf()
    {
        global $_W, $_GPC;
        $model = set_model($this->house_esf);
        $model_info = $model->model_info;


        $charge = 0;
        if ($_W['module_config']['free_amount']) {
            //初始最为普通人
            $free_amount = $_W['module_config']['free_amount'];
            $agent = set_model('house_agent')->where(['status' => 99, 'user_id' => $this->user['id']])->find();
            //普通经纪人
            if ($agent && $agent['type'] == 1 && SYS_TIME < strtotime($agent['expire'])) {
                $free_amount = $_W['module_config']['normal_agent_free_amount'];
            }

            //内部不限制
            if ($agent && $agent['type'] == 2) {
                $free_amount = 0;
            }


            //count the amount
            $amount_where = [];
            $amount_where['site_id'] = $_W['site']['id'];
            $amount_where['user_id'] = $this->user['id'];
            $amount = $model->where($amount_where)->count();

            if ($free_amount && $free_amount <= $amount) {
                // check if the user's balance is great than the needs
                if ($_W['module_config']['charge_on_publish']) {

                    $charge = 1;
                    $unit_type = $_W['module_config']['charge_unit_type'] == 0 ? "balance" : "point";
                    $unit_type_name = $_W['site']['config']['trade'][$unit_type . '_text'];
                    if ($this->user[$unit_type] < $_W['module_config']['charge_amount']) {

                        $msg_data['buttons'] = [];

                        $msg_data['type'] = "confirm";
                        $button = [
                            'text' => "立刻冲值",
                            'url' => url('member/wallet/index')
                        ];
                        $msg_data['buttons']['second'] = $button;
                        $msg_data['buttons']['main'] = [
                            'text' => "返回",
                            'url' => HTTP_REFERER
                        ];

                        $this->message("对不起，您已经达到免费发布信息数量的上限,并且您的 <b style='color: red'>$unit_type_name 不足</b> 不足以支持本次信息发布 您可以升级经纪人来提高免费次数", 2, HTTP_REFERER, $msg_data);
                    }

                } else {

                    $this->message("对不起 ， 您的达到信息免费发布数量的上限");
                }
            }
        }

        if ($charge == 0 && $_W['module_config']['publish_gap']) {
            //免费发布消息 有时间间隔限制

            $amount_where = [];
            $amount_where['site_id'] = $_W['site']['id'];
            $amount_where['user_id'] = $this->user['id'];
            $last_item = $model->where($amount_where)->find();

            if (time() - strtotime($last_item['create_at']) < $_W['module_config']['publish_gap'] * 60) {
                $this->message("对不起 ， 您还需要过一段时间才能发布新的信息");
            }
        }

        if ($this->isPost(true) && $model_info) {
            //todo check check_repeat

            $base_info = input('post.data/a');//get the base info

            $test_where = [];
            $test_where['shi'] = $base_info['shi'];
            $test_where['mobile'] = $base_info['mobile'];
            $test = $model->where($test_where)->find();

            if ($test && $_W['module_config']['check_repeat']) {
                return $this->zbn_msg("该房源已经在数据库里面存在请勿重复录入", 2);
            }

            Db::startTrans();
            $is_top = 0;
            if ($_GPC['is_top'] == 1) {
                foreach ($_W['module_config']['top_esf_set'] as $k => $set) {
                    if ($k == $_GPC['set_index']) {
                        $is_top = 1;
                        $base_info['top_days'] = $set['days'];
                        $base_info['is_top'] = 1;
                        //top expire top_expire
                        $base_info['top_expire'] = date("Y-m-d H:i:s", SYS_TIME + 86400 * $base_info['top_days']);
                        break;

                    }
                }
            }
            if ($is_top == 1) {
                if ($set['unit_type'] == "balance") {
                    $spend_top = Money::spend($this->user, $set['money'], 0, "置顶二手房源信息");
                } else {
                    $spend_top = Point::spend($this->user, $set['money'], 0, "置顶二手房源信息");
                }
                if (!$spend_top) {
                    $spend_top_msg = ' 置顶失败 您' . $_W['site']['config']['trade'][$set['unit_type'] . '_text'] . '不足';
                }

            } else {
                $spend_top_msg = '';
                $spend_top = true;
                $base_info['top_days'] = 0;
                $base_info['is_top'] = 0;
            }

            if ($charge) {
                if ($unit_type == "balance") {
                    $spend = Money::spend($this->user, $_W['module_config']['charge_amount'], 0, "发布二手信息");
                } else {
                    $spend = Point::spend($this->user, $_W['module_config']['charge_amount'], 0, "发布二手信息");
                }
                $spend_msg = " 发布信息失败，余额不足!";
            } else {
                $spend = true;
                $spend_msg = '';
            }

            if ($spend) {

                if ($_W['module_config']['check_esf']) {
                    $base_info['status'] = 1;
                } else {
                    $base_info['status'] = 99;
                }
                $res = $model_info->add_content($base_info);
            } else {
                $res['code'] = 2;
            }


            if ($spend && $res['code'] == 1 && $spend_top) {
                Db::commit();
                token();
                $to_url = url('house/index/index');
                //todo send
                $tpl_config = mhcms_json_decode($_W['tpl_config']);
                unset($tpl_config['miniprogram']);
                $params['header'] = "您好 有用户发布了二手房信息， " . $res['item']['title'];
                $tpl_config['tp_url'] = url('house/check/index', [], true, true);
                Notice::send("系统通知", "wxmsg", $_W['module_config']['admin_openid'], $params, $tpl_config);
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "'$to_url'", "''");
            } else {
                Db::rollback();
                return $this->zbn_msg($spend_msg . $res['msg'] . $spend_top_msg, 2);
            }
        } else {
            $this->view->assign('field_list', $model_info->get_user_publish_fields());
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function my_esf_lists()
    {

        global $_W;
        $where['user_id'] = $this->user['id'];
        $where['site_id'] = $_W['site']['id'];
        $this->view->lists = $lists = set_model($this->house_esf)->where($where)->paginate();

        return $this->view->fetch();
    }

    public function select_cate()
    {

        return $this->view->fetch();
    }

    public function publish($cate_id)
    {
        global $_GPC;
        $cate = $this->cates[$cate_id];
        $model = set_model($cate['model_id']);
        /** @var Models $model_info */
        $model_info = $model->model_info;


        if (!$this->module_config['unverify_post'] && !$this->user['is_mobile_verify']) {
            $url = url('member/info/set_mobile');
            $this->message("对不起，请先完成手机认证后再发布商品！", 2, $url);
        }

        if ($this->isPost(true) && $model_info) {
            $base_info = input('post.data/a');//get the base info
            $res = $model_info->add_content($base_info);


            if ($res['code'] == 1) {
                token();
                $to_url = url('house/index/index');
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "'$to_url'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {

            $this->view->assign('field_list', $model_info->get_user_publish_fields());
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function publish_rent()
    {
        global $_W, $_GPC;
        $model = set_model($this->house_rent);
        $model_info = $model->model_info;

        $charge = 0;
        if ($_W['module_config']['free_amount']) {
            //初始最为普通人
            $free_amount = $_W['module_config']['free_amount'];
            $agent = set_model('house_agent')->where(['status' => 99, 'user_id' => $this->user['id']])->find();
            //普通经纪人
            if ($agent && $agent['type'] == 1 && SYS_TIME < strtotime($agent['expire'])) {
                $free_amount = $_W['module_config']['normal_agent_free_amount'];
            }

            //内部不限制
            if ($agent && $agent['type'] == 2) {
                $free_amount = 0;
            }


            //count the amount
            $amount_where = [];
            $amount_where['site_id'] = $_W['site']['id'];
            $amount_where['user_id'] = $this->user['id'];
            $amount = $model->where($amount_where)->count();

            if ($free_amount && $free_amount <= $amount) {
                // check if the user's balance is great than the needs
                if ($_W['module_config']['charge_on_publish']) {

                    $charge = 1;
                    $unit_type = $_W['module_config']['charge_unit_type'] == 0 ? "balance" : "point";
                    $unit_type_name = $_W['site']['config']['trade'][$unit_type . '_text'];
                    if ($this->user[$unit_type] < $_W['module_config']['charge_amount']) {

                        $msg_data['buttons'] = [];

                        $msg_data['type'] = "confirm";
                        $button = [
                            'text' => "立刻冲值",
                            'url' => url('member/wallet/index')
                        ];
                        $msg_data['buttons']['second'] = $button;
                        $msg_data['buttons']['main'] = [
                            'text' => "返回",
                            'url' => HTTP_REFERER
                        ];

                        $this->message("对不起，您已经达到免费发布信息数量的上限,并且您的 <b style='color: red'>$unit_type_name 不足</b> 不足以支持本次信息发布 您可以升级经纪人来提高免费次数", 2, HTTP_REFERER, $msg_data);
                    }

                } else {

                    $this->message("对不起 ， 您的达到信息免费发布数量的上限");
                }
            }
        }

        if ($charge == 0 && $_W['module_config']['publish_gap']) {
            //免费发布消息 有时间间隔限制
            $amount_where = [];
            $amount_where['site_id'] = $_W['site']['id'];
            $amount_where['user_id'] = $this->user['id'];
            $last_item = $model->where($amount_where)->find();
            if (time() - strtotime($last_item['create_at']) < $_W['module_config']['publish_gap'] * 60) {
                $this->message(" 对不起 ， 您还需要过一段时间才能发布新的信息");
            }
        }


        if ($this->isPost(true) && $model_info) {
            $base_info = input('post.data/a');//get the base info
            Db::startTrans();

            $is_top = 0;
            if ($_GPC['is_top'] == 1) {
                foreach ($_W['module_config']['top_set'] as $k => $set) {
                    if ($k == $_GPC['set_index']) {
                        $is_top = 1;
                        $base_info['top_days'] = $set['days'];
                        $base_info['is_top'] = 1;
                        //top expire top_expire
                        $base_info['top_expire'] = date("Y-m-d H:i:s", SYS_TIME + 86400 * $base_info['top_days']);
                        break;

                    }
                }
            }
            if ($is_top == 1) {
                if ($set['unit_type'] == "balance") {
                    $spend_top = Money::spend($this->user, $set['money'], 0, "置顶出租房源信息");
                } else {
                    $spend_top = Point::spend($this->user, $set['money'], 0, "置顶出租房源信息");
                }
                if (!$spend_top) {
                    $spend_top_msg = ' 置顶失败 您' . $_W['site']['config']['trade'][$set['unit_type'] . '_text'] . '不足';
                }

            } else {
                $spend_top_msg = '';
                $spend_top = true;
                $base_info['top_days'] = 0;
                $base_info['is_top'] = 0;
            }


            if ($charge) {
                if ($unit_type == "balance") {
                    $spend = Money::spend($this->user, $_W['module_config']['charge_amount'], 0, "发布出租房源信息");
                } else {
                    $spend = Point::spend($this->user, $_W['module_config']['charge_amount'], 0, "发布出租房源信息");
                }

                if (!$spend) {
                    $spend_msg = " 发布信息失败，余额不足!";
                }

            } else {
                $spend = true;
            }

            if ($spend) {
                $res = $model_info->add_content($base_info);
            } else {
                $res['code'] = 2;
            }


            if ($spend && $res['code'] == 1 && $spend_top) {
                Db::commit();
                token();
                $to_url = url('house/index/index');
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "'$to_url'", "''");
            } else {
                Db::rollback();
                return $this->zbn_msg($spend_msg . $res['msg'] . $spend_top_msg, 2);
            }
        } else {

            $this->view->assign('field_list', $model_info->get_user_publish_fields());
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }


    public function my_rent_lists()
    {

        global $_W;
        $where['user_id'] = $this->user['id'];
        $where['site_id'] = $_W['site']['id'];
        $this->view->lists = $lists = set_model($this->house_rent)->where($where)->paginate();
        return $this->view->fetch();
    }

    public function publish_info()
    {
        global $_W, $_GPC;
        $model = set_model($this->house_info);
        $model_info = $model->model_info;

        $charge = 0;
        if ($_W['module_config']['free_amount']) {
            //初始最为普通人
            $free_amount = $_W['module_config']['free_amount'];
            $agent = set_model('house_agent')->where(['status' => 99, 'user_id' => $this->user['id']])->find();
            //普通经纪人
            if ($agent && $agent['type'] == 1) {
                $free_amount = $_W['module_config']['normal_agent_free_amount'];
            }

            //内部不限制
            if ($agent && $agent['type'] == 2) {
                $free_amount = 0;
            }


            //count the amount
            $amount_where = [];
            $amount_where['site_id'] = $_W['site']['id'];
            $amount_where['user_id'] = $this->user['id'];
            $amount = $model->where($amount_where)->count();

            if ($free_amount && $free_amount <= $amount) {
                // check if the user's balance is great than the needs
                if ($_W['module_config']['charge_on_publish']) {

                    $charge = 1;
                    $unit_type = $_W['module_config']['charge_unit_type'] == 0 ? "balance" : "point";
                    $unit_type_name = $_W['site']['config']['trade'][$unit_type . '_text'];
                    if ($this->user[$unit_type] < $_W['module_config']['charge_amount']) {

                        $msg_data['buttons'] = [];

                        $msg_data['type'] = "confirm";
                        $button = [
                            'text' => "立刻冲值",
                            'url' => url('member/wallet/index')
                        ];
                        $msg_data['buttons']['second'] = $button;
                        $msg_data['buttons']['main'] = [
                            'text' => "返回",
                            'url' => HTTP_REFERER
                        ];

                        $this->message("对不起，您已经达到免费发布信息数量的上限,并且您的 <b style='color: red'>$unit_type_name 不足</b> 不足以支持本次信息发布 您可以升级经纪人来提高免费次数", 2, HTTP_REFERER, $msg_data);
                    }

                } else {

                    $this->message("对不起 ， 您的达到信息免费发布数量的上限");
                }
            }
        }

        if ($charge == 0 && $_W['module_config']['publish_gap']) {
            //免费发布消息 有时间间隔限制
            $amount_where = [];
            $amount_where['site_id'] = $_W['site']['id'];
            $amount_where['user_id'] = $this->user['id'];
            $last_item = $model->where($amount_where)->find();
            if (time() - strtotime($last_item['create_at']) < $_W['module_config']['publish_gap'] * 60) {
                $this->message(" 对不起 ， 您还需要过一段时间才能发布新的信息");
            }
        }


        if ($this->isPost(true) && $model_info) {
            $base_info = input('post.data/a');//get the base info
            Db::startTrans();

            $is_top = 0;
            if ($_GPC['is_top'] == 1) {
                foreach ($_W['module_config']['top_set'] as $k => $set) {
                    if ($k == $_GPC['set_index']) {
                        $is_top = 1;
                        $base_info['top_days'] = $set['days'];
                        $base_info['is_top'] = 1;
                        //top expire top_expire
                        $base_info['top_expire'] = date("Y-m-d H:i:s", SYS_TIME + 86400 * $base_info['top_days']);
                        break;

                    }
                }
            }
            if ($is_top == 1) {
                if ($set['unit_type'] == "balance") {
                    $spend_top = Money::spend($this->user, $set['money'], 0, "置顶出租房源信息");
                } else {
                    $spend_top = Point::spend($this->user, $set['money'], 0, "置顶出租房源信息");
                }
                if (!$spend_top) {
                    $spend_top_msg = ' 置顶失败 您' . $_W['site']['config']['trade'][$set['unit_type'] . '_text'] . '不足';
                }

            } else {
                $spend_top_msg = '';
                $spend_top = true;
                $base_info['top_days'] = 0;
                $base_info['is_top'] = 0;
            }


            if ($charge) {
                if ($unit_type == "balance") {
                    $spend = Money::spend($this->user, $_W['module_config']['charge_amount'], 0, "发布出租房源信息");
                } else {
                    $spend = Point::spend($this->user, $_W['module_config']['charge_amount'], 0, "发布出租房源信息");
                }

                if (!$spend) {
                    $spend_msg = " 发布信息失败，余额不足!";
                }

            } else {
                $spend = true;
            }

            if ($spend) {
                $res = $model_info->add_content($base_info);
            } else {
                $res['code'] = 2;
            }


            if ($spend && $res['code'] == 1 && $spend_top) {
                Db::commit();
                token();
                $to_url = url('house/index/index');
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "'$to_url'", "''");
            } else {
                Db::rollback();
                return $this->zbn_msg($spend_msg . $res['msg'] . $spend_top_msg, 2);
            }
        } else {

            $this->view->assign('field_list', $model_info->get_user_publish_fields());
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function my_info_lists()
    {

        global $_W;
        $where['user_id'] = $this->user['id'];
        $where['site_id'] = $_W['site']['id'];
        $this->view->lists = $lists = set_model($this->house_rent)->where($where)->paginate();
        return $this->view->fetch();
    }

}