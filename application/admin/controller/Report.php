<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserRoles;
use app\common\model\Users;
use app\common\datas\csv;
use think\Db;
use think\Log;

class Report extends AdminBase
{
    // 分润报表
    public function share_profit()
    {
        $data = array();
        $nickname = trim(input('param.nickname', ' ', 'htmlspecialchars'));
        if ($nickname) {
            $where['nickname'] = array('LIKE', '%' . $nickname . '%');
            $user = db('users')->where($where)->field('id')->find();
            $user_id = $user['id'];
            $this->view->assign('user_id', $user_id);
        }

        // 超级管理员
        if ($this->super_power) {
            if ($user_id) {
                $share = db('distribute_orders')->where(['status' => 1, 'user_id' => $user_id])->order('id desc')->paginate(config('list_rows'));
                $data['total'] = db('distribute_orders')->where(['status' => 1, 'user_id' => $user_id])->sum('amount');
            } else {
                $share = db('distribute_orders')->where(['status' => 1])->order('id desc')->paginate(config('list_rows'));
                $data['total'] = db('distribute_orders')->where(['status' => 1])->sum('amount');
            }
            $shares = $share->toArray();
            foreach ($shares['data'] as $key => $value) {
                $user_info = db('users')->where(['id' => $value['user_id']])->find();
                $shares['data'][$key]['user_name'] = $user_info['user_name'];
            }

            $data['head'] = db('distribute_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');

            $area_agents = db('users')->where(['user_role_id' => 22])->field('id')->select()->toArray();
            $area_ids = array_column($area_agents, 'id');
            $area_where['status'] = 1;
            $area_where['user_id'] = array('IN', $area_ids);
            $data['area'] = db('distribute_orders')->where($area_where)->sum('amount');

            $house_agents = db('users')->where(['user_role_id' => 23])->field('id')->select()->toArray();
            $house_ids = array_column($house_agents, 'id');
            $house_where['status'] = 1;
            $house_where['user_id'] = array('IN', $house_ids);
            $data['house'] = db('distribute_orders')->where($house_where)->sum('amount');

            $this->view->assign('data', $data);
        } else {
            // 根据角色查数据
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                if ($user_id) {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id'], 'id' => $user_id])->order('id desc')->field('id')->select()->toArray();
                    $data['total'] = db('distribute_orders')->where(['status' => 1, 'user_id' => $user_id])->sum('amount');
                } else {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                    $data['total'] = db('distribute_orders')->where(['status' => 1])->sum('amount');
                }
                $ids = array_column($user_ids, 'id');

                $where_child['id'] = array('IN', $ids);
                $user_child_ids = db('users')->where($where_child)->field('id')->select()->toArray();
                $child_ids = array_column($user_child_ids, 'id');
                $ids = array_merge($ids, $child_ids);

                array_push($ids, $this->user['id']);
                $where['status'] = 1;
                $where['user_id'] = array('IN', $ids);
                $share = db('distribute_orders')->where($where)->order('id desc')->paginate(config('list_rows'));

                $shares = $share->toArray();
                foreach ($shares['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $shares['data'][$key]['user_name'] = $user_info['user_name'];
                }

                $data['self'] = db('distribute_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');
                $this->view->assign('data', $data);
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                if ($user_id) {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id'], 'id' => $user_id])->order('id desc')->field('id')->select()->toArray();
                    $data['total'] = db('distribute_orders')->where(['status' => 1, 'user_id' => $user_id])->sum('amount');
                } else {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                    $data['total'] = db('distribute_orders')->where(['status' => 1])->sum('amount');
                }
                $ids = array_column($user_ids, 'id');
                array_push($ids, $this->user['id']);

                $where['status'] = 1;
                $where['user_id'] = array('IN', $ids);
                $share = db('distribute_orders')->where($where)->order('id desc')->paginate(config('list_rows'));

                $shares = $share->toArray();
                foreach ($shares['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $shares['data'][$key]['user_name'] = $user_info['user_name'];
                }

                $data['self'] = db('distribute_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');
                $this->view->assign('data', $data);
            } else {
                // 普通用户
                $where['status'] = 1;
                $where['user_id'] = $this->user['id'];
                $share = db('distribute_orders')->where($where)->order('id desc')->paginate(config('list_rows'));

                $shares = $share->toArray();
                foreach ($shares['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $shares['data'][$key]['user_name'] = $user_info['user_name'];
                }

                $data['total'] = db('distribute_orders')->where(['status' => 1])->sum('amount');
                $data['self'] = db('distribute_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');
                $this->view->assign('data', $data);
            }
        }

        $pages = $share->render();
        $this->view->assign('shares', $shares['data']);
        $this->view->assign('page', $pages);
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    // 分润报表下载
    public function download_profit()
    {
        $user_id = trim(input('param.user_id', ' ', 'htmlspecialchars'));
        if ($this->super_power) {
            if ($user_id) {
                $share = db()->query('select mhcms_distribute_orders.*,mhcms_users.user_name from mhcms_distribute_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribute_orders.user_id where mhcms_distribute_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
            } else {
                $share = db()->query('select mhcms_distribute_orders.*,mhcms_users.user_name from mhcms_distribute_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribute_orders.user_id ORDER BY id DESC');
            }
        } else {
            // 根据角色查数据
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                $ids = array_column($user_ids, 'id');

                $where_child['id'] = array('IN', $ids);
                $user_child_ids = db('users')->where($where_child)->field('id')->select()->toArray();
                $child_ids = array_column($user_child_ids, 'id');
                $ids = array_merge($ids, $child_ids);
                array_push($ids, $this->user['id']);

                $ids = implode($ids, ',');
                $idstr = '(' . $ids . ')';

                if ($user_id) {
                    $share = db()->query('select mhcms_distribute_orders.*,mhcms_users.user_name from mhcms_distribute_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribute_orders.user_id WHERE mhcms_distribute_orders.status=1 AND mhcms_distribute_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $share = db()->query('select mhcms_distribute_orders.*,mhcms_users.user_name from mhcms_distribute_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribute_orders.user_id WHERE mhcms_distribute_orders.status=1 AND mhcms_distribute_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                $ids = array_column($user_ids, 'id');
                array_push($ids, $this->user['id']);

                $ids = implode($ids, ',');
                $idstr = '(' . $ids . ')';

                if ($user_id) {
                    $share = db()->query('select mhcms_distribute_orders.*,mhcms_users.user_name from mhcms_distribute_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribute_orders.user_id WHERE mhcms_distribute_orders.status=1 AND mhcms_distribute_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $share = db()->query('select mhcms_distribute_orders.*,mhcms_users.user_name from mhcms_distribute_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribute_orders.user_id WHERE mhcms_distribute_orders.status=1 AND mhcms_distribute_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } else {
                // 普通用户
                $share = db()->query('select mhcms_distribute_orders.*,mhcms_users.user_name from mhcms_distribute_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribute_orders.user_id WHERE mhcms_distribute_orders.status=1 AND mhcms_distribute_orders.user_id = ' . $this->user['id'] . ' ORDER BY id DESC');
            }
        }
        $csv_data = array();
        foreach ($share as $key => $value) {
            $csv_data[$key]['id'] = strval($value['id']);
            $csv_data[$key]['user_name'] = $value['user_name'];
            $csv_data[$key]['amount'] = $value['amount'];
            $csv_data[$key]['order_id'] = '订单号：' . strval($value['order_id']);
            $csv_data[$key]['pay_time'] = $value['pay_time'];
            $csv_data[$key]['reject_time'] = $value['reject_time'];
            $csv_data[$key]['create_at'] = $value['create_at'];
        }
        $csv_title = array('ID', '用户名', '分润金额', '订单编号', '支付时间', '拒绝时间', '创建时间');

        $this->download_report($csv_data, $csv_title);
    }

    //充值列表
    public function recharge()
    {
        $nickname = trim(input('param.nickname', ' ', 'htmlspecialchars'));
        if ($nickname) {
            $where['nickname'] = array('LIKE', '%' . $nickname . '%');
            $user = db('users')->where($where)->field('id')->find();
            $user_id = $user['id'];
            $this->view->assign('user_id', $user_id);
        }

        if ($user_id) {
            $total = db('orders')->where(['user_id' => $user_id])->sum('total_fee');
            $this->view->assign('total', $total);
        } else {
            $total = db('orders')->sum('total_fee');
            $this->view->assign('total', $total);
        }

        // 超级管理员
        if ($this->super_power) {
            if ($user_id) {
                $recharge = db('orders')->where(['user_id' => $user_id])->order('id desc')->paginate(config('list_rows'));
            } else {
                $recharge = db('orders')->order('id desc')->paginate(config('list_rows'));
            }
            $recharges = $recharge->toArray();
            foreach ($recharges['data'] as $key => $value) {
                $user_info = db('users')->where(['id' => $value['user_id']])->find();
                $recharges['data'][$key]['user_name'] = $user_info['user_name'];
            }
        } else {
            // 根据角色查数据
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                if ($user_id) {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id'], 'id' => $user_id])->order('id desc')->field('id')->select()->toArray();
                } else {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                }
                $ids = array_column($user_ids, 'id');

                $where_child['id'] = array('IN', $ids);
                $user_child_ids = db('users')->where($where_child)->field('id')->select()->toArray();
                $child_ids = array_column($user_child_ids, 'id');
                $ids = array_merge($ids, $child_ids);
                array_push($ids, $this->user['id']);

                $where['user_id'] = array('IN', $ids);
                $recharge = db('orders')->where($where)->order('id desc')->paginate(config('list_rows'));

                $recharges = $recharge->toArray();
                foreach ($recharges['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $recharges['data'][$key]['user_name'] = $user_info['user_name'];
                }
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                if ($user_id) {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id'], 'id' => $user_id])->order('id desc')->field('id')->select()->toArray();
                } else {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                }
                $ids = array_column($user_ids, 'id');
                array_push($ids, $this->user['id']);

                $where['user_id'] = array('IN', $ids);
                $recharge = db('orders')->where($where)->order('id desc')->paginate(config('list_rows'));

                $recharges = $recharge->toArray();
                foreach ($recharges['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $recharges['data'][$key]['user_name'] = $user_info['user_name'];
                }
            } else {
                // 普通用户
                $where['user_id'] = $this->user['id'];
                $recharge = db('orders')->where($where)->order('id desc')->paginate(config('list_rows'));

                $recharges = $recharge->toArray();
                foreach ($recharges['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $recharges['data'][$key]['user_name'] = $user_info['user_name'];
                }
            }
        }

        $pages = $recharge->render();
        $this->view->assign('recharges', $recharges['data']);
        $this->view->assign('page', $pages);
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }

    //充值列表下载
    public function download_recharge()
    {
        $user_id = trim(input('param.user_id', ' ', 'htmlspecialchars'));

        if ($this->super_power) {
            if ($user_id) {
                $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id where mhcms_orders.user_id=' . $user_id . ' ORDER BY id DESC');
            } else {
                $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id ORDER BY id DESC');
            }
        } else {
            // 根据角色查数据
            $users = db('users')->where(['id' => $this->user['id']])->find();
            if ($users['user_role_id'] == 22) {
                // 区域管理
                $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                $ids = array_column($user_ids, 'id');

                $where_child['id'] = array('IN', $ids);
                $user_child_ids = db('users')->where($where_child)->field('id')->select()->toArray();
                $child_ids = array_column($user_child_ids, 'id');
                $ids = array_merge($ids, $child_ids);
                array_push($ids, $this->user['id']);

                $ids = implode($ids, ',');
                $idstr = '(' . $ids . ')';

                if ($user_id) {
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_distribute_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_distribute_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                $ids = array_column($user_ids, 'id');
                array_push($ids, $this->user['id']);

                $ids = implode($ids, ',');
                $idstr = '(' . $ids . ')';
                if ($user_id) {
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_distribute_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_distribute_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } else {
                // 普通用户
                $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_distribute_orders.user_id = ' . $this->user['id'] . ' ORDER BY id DESC');
            }
        }

        $csv_data = array();
        foreach ($recharge as $key => $value) {
            $csv_data[$key]['id'] = '订单号：' . $value['id'];
            $csv_data[$key]['user_name'] = $value['user_name'];
            $csv_data[$key]['mobile'] = $value['mobile'];
            $csv_data[$key]['note'] = $value['note'];
            $csv_data[$key]['total_fee'] = $value['total_fee'];
            $csv_data[$key]['status'] = $value['status'];
            $csv_data[$key]['pay_time'] = $value['pay_time'];
            $csv_data[$key]['create_time'] = $value['create_time'];
        }

        $csv_title = array('订单编号', '用户名', '手机号', '充值备注', '充值金额（元）', '充值状态', '充值时间', '创建时间');
        $this->download_report($csv_data, $csv_title);
    }

    //调用报表下载
    public function download_report($csv_data, $csv_title)
    {
        $csv = new csv();
        $csv->put_csv($csv_data, $csv_title);
    }
}
