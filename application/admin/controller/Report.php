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
        $nickname = trim(input('param.nickname'));
        if ($nickname) {
            $where_nickname['nickname'] = array('LIKE', '%' . $nickname . '%');
            $user = db('users')->where($where_nickname)->field('id')->find();
            $user_id = $user['id'];
            $this->view->assign('nickname', $nickname);
            $this->view->assign('user_id', $user_id);
        }

        // 超级管理员
        if ($this->super_power) {
            if ($user_id) {
                $share = db('distribution_orders')->where(['status' => 1, 'user_id' => $user_id])->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);
                $data['total'] = db('distribution_orders')->where(['status' => 1, 'user_id' => $user_id])->sum('amount');
            } else {
                $share = db('distribution_orders')->where(['status' => 1])->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);
                $data['total'] = db('distribution_orders')->where(['status' => 1])->sum('amount');
            }
            $shares = $share->toArray();
            foreach ($shares['data'] as $key => $value) {
                $user_info = db('users')->where(['id' => $value['user_id']])->find();
                $shares['data'][$key]['user_name'] = $user_info['user_name'];
            }

            $area_agents = db('users')->where(['user_role_id' => 22])->field('id')->select()->toArray();
            $area_ids = array_column($area_agents, 'id');
            $area_where['status'] = 1;
            $area_where['user_id'] = array('IN', $area_ids);

            $house_agents = db('users')->where(['user_role_id' => 23])->field('id')->select()->toArray();
            $house_ids = array_column($house_agents, 'id');
            $house_where['status'] = 1;
            $house_where['user_id'] = array('IN', $house_ids);

            $data['head'] = db('distribution_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');
            $this->view->assign('data', $data);
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
                $where['status'] = 1;
                $where['user_id'] = array('IN', $ids);
                $share = db('distribution_orders')->where($where)->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);

                $shares = $share->toArray();
                foreach ($shares['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $shares['data'][$key]['user_name'] = $user_info['user_name'];
                }

                $data['self'] = db('distribution_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');
                $this->view->assign('data', $data);
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                if ($user_id) {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id'], 'id' => $user_id])->order('id desc')->field('id')->select()->toArray();
                } else {
                    $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                }
                $ids = array_column($user_ids, 'id');
                array_push($ids, $this->user['id']);

                $where['status'] = 1;
                $where['user_id'] = array('IN', $ids);

                $share = db('distribution_orders')->where($where)->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);
                $shares = $share->toArray();
                foreach ($shares['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $shares['data'][$key]['user_name'] = $user_info['user_name'];
                }

                $data['self'] = db('distribution_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');
                $this->view->assign('data', $data);
            } else {
                // 普通用户
                $where['status'] = 1;
                $where['user_id'] = $this->user['id'];
                $share = db('distribution_orders')->where($where)->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);

                $shares = $share->toArray();
                foreach ($shares['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $shares['data'][$key]['user_name'] = $user_info['user_name'];
                }

                $data['self'] = db('distribution_orders')->where(['status' => 1, 'user_id' => $this->user['id']])->sum('amount');
                $this->view->assign('data', $data);
            }
        }

        $pages = $share->render();
        $this->view->assign('shares', $shares['data']);
        $this->view->assign('page', $pages);
        $this->view->mapping = $this->mapping;
        return $this->view->fetch();
    }


    //充值列表
    public function recharge()
    {
        $nickname = trim(input('param.nickname'));
        if ($nickname) {
            $where_nickname['nickname'] = array('LIKE', '%' . $nickname . '%');
            $user = db('users')->where($where_nickname)->field('id')->find();
            $user_id = $user['id'];
            $this->view->assign('nickname', $nickname);
            $this->view->assign('user_id', $user_id);
        }

        // 超级管理员
        if ($this->super_power) {
            if ($user_id) {
                $total = db('orders')->where(['user_id' => $user_id,'source_type'=>1])->sum('amount');
                $this->view->assign('total', $total);
                $recharge = db('orders')->where(['user_id' => $user_id,'source_type'=>1])->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);
            } else {
                $total = db('orders')->where(['source_type'=>1])->sum('amount');
                $this->view->assign('total', $total);
                $recharge = db('orders')->where(['source_type'=>1])->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);
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
                $where['source_type'] = 1;
                $recharge = db('orders')->where($where)->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);

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
                $where['source_type'] = 1;
                $recharge = db('orders')->where($where)->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);

                $recharges = $recharge->toArray();
                foreach ($recharges['data'] as $key => $value) {
                    $user_info = db('users')->where(['id' => $value['user_id']])->find();
                    $recharges['data'][$key]['user_name'] = $user_info['user_name'];
                }
            } else {
                // 普通用户
                $where['user_id'] = $this->user['id'];
                $where['source_type'] = 1;
                $recharge = db('orders')->where($where)->order('id desc')->paginate(config('list_rows'),false, ['query' => array('nickname' => $nickname)]);

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

    // 分润报表下载
    public function download_profit()
    {
        $user_id = trim(input('param.user_id'));
        if ($this->super_power) {
            if ($user_id) {
                $share = db()->query('select mhcms_distribution_orders.*,mhcms_users.user_name from mhcms_distribution_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribution_orders.user_id where mhcms_distribution_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
            } else {
                $share = db()->query('select mhcms_distribution_orders.*,mhcms_users.user_name from mhcms_distribution_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribution_orders.user_id ORDER BY id DESC');
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
                    $share = db()->query('select mhcms_distribution_orders.*,mhcms_users.user_name from mhcms_distribution_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribution_orders.user_id WHERE mhcms_distribution_orders.status=1 AND mhcms_distribution_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $share = db()->query('select mhcms_distribution_orders.*,mhcms_users.user_name from mhcms_distribution_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribution_orders.user_id WHERE mhcms_distribution_orders.status=1 AND mhcms_distribution_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                $ids = array_column($user_ids, 'id');
                array_push($ids, $this->user['id']);

                $ids = implode($ids, ',');
                $idstr = '(' . $ids . ')';

                if ($user_id) {
                    $share = db()->query('select mhcms_distribution_orders.*,mhcms_users.user_name from mhcms_distribution_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribution_orders.user_id WHERE mhcms_distribution_orders.status=1 AND mhcms_distribution_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $share = db()->query('select mhcms_distribution_orders.*,mhcms_users.user_name from mhcms_distribution_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribution_orders.user_id WHERE mhcms_distribution_orders.status=1 AND mhcms_distribution_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } else {
                // 普通用户
                $share = db()->query('select mhcms_distribution_orders.*,mhcms_users.user_name from mhcms_distribution_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_distribution_orders.user_id WHERE mhcms_distribution_orders.status=1 AND mhcms_distribution_orders.user_id = ' . $this->user['id'] . ' ORDER BY id DESC');
            }
        }
        $csv_data = array();
        foreach ($share as $key => $value) {
            $csv_data[$key]['id'] = strval($value['id']);
            $csv_data[$key]['nickname'] = $value['nickname'];
            $csv_data[$key]['amount'] = $value['amount'];
            $csv_data[$key]['order_id'] = '订单号：' . strval($value['order_id']);
            $csv_data[$key]['create_time'] = $value['create_time'];
        }
        $csv_title = array('ID', '用户名', '分润金额', '订单编号', '创建时间');

        $this->download_report($csv_data, $csv_title);
    }

    //充值列表下载
    public function download_recharge()
    {
        $user_id = trim(input('param.user_id'));

        if ($this->super_power) {
            if ($user_id) {
                $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id where mhcms_orders.source_type = 1 and  mhcms_orders.user_id=' . $user_id . ' ORDER BY id DESC');
            } else {
                $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id where mhcms_orders.source_type = 1 ORDER BY id DESC');
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
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_orders.source_type = 1 and mhcms_distribution_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_orders.source_type = 1 and mhcms_distribution_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } elseif ($users['user_role_id'] == 23) {
                // 县级代理
                $user_ids = db('users')->where(['parent_id' => $this->user['id']])->order('id desc')->field('id')->select()->toArray();
                $ids = array_column($user_ids, 'id');
                array_push($ids, $this->user['id']);

                $ids = implode($ids, ',');
                $idstr = '(' . $ids . ')';
                if ($user_id) {
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_orders.source_type = 1 and mhcms_distribution_orders.user_id = ' . $user_id . ' ORDER BY id DESC');
                } else {
                    $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_orders.source_type = 1 and mhcms_distribution_orders.user_id IN ' . $idstr . ' ORDER BY id DESC');
                }
            } else {
                // 普通用户
                $recharge = db()->query('select mhcms_orders.*,mhcms_users.user_name from mhcms_orders LEFT JOIN mhcms_users ON mhcms_users.id = mhcms_orders.user_id WHERE mhcms_orders.source_type = 1 and mhcms_distribution_orders.user_id = ' . $this->user['id'] . ' ORDER BY id DESC');
            }
        }

        $csv_data = array();
        foreach ($recharge as $key => $value) {
            $csv_data[$key]['id'] = '订单号：' . $value['id'];
            $csv_data[$key]['nickname'] = $value['nickname'];
            $csv_data[$key]['mobile'] = $value['mobile'];
            $csv_data[$key]['note'] = $value['note'];
            $csv_data[$key]['amount'] = $value['amount'];
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
