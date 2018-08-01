<?php

use app\common\controller\AdminBase;

/**
 * Created by PhpStorm.
 * 信息检索
 * User: RoryHe
 * Date: 2018/8/1
 * Time: 上午11:02
 */
class Info extends AdminBase
{


    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Users();
    }

    public function house_agent()
    {

    }
}