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
namespace app\attachment\controller;

use app\common\controller\Base;
use app\common\model\AttachConfig;
use app\common\model\File;

class Attach extends Base
{
    private $storge;

    public function _initialize()
    {
        global $_GPC;
        parent::_initialize();

        if ($_GPC['attach_sign']) {
            $storge = AttachConfig::get(['attach_sign' => $_GPC['attach_sign']]);
        } else {
            //  get_default storge engine
            $storge = AttachConfig::get(['default' => 1]);
        }
        $storage_config = set_model("attach_config_site")->where(['storge_id' => $storge['id'] ])->find();

        if ($storge) {
            $storge_name = $storge['attach_sign'];
            $class_name = "\\app\\attachment\\storges\\" . $storge_name;

            $this->storge = new $class_name(mhcms_json_decode($storage_config['config']));
        } else {

            die("Error");
        }
    }

    public function upload()
    {

    }


    public function get_token()
    {
        $ret['uptoken'] = $this->storge->get_token();


        if ($ret['uptoken']) {
            $ret['code'] = 1;
        }
        $ret['data'] = [
            'uptoken' => $ret['uptoken']
        ];
        echo json_encode($ret);
    }

    public function save()
    {

        global $_GPC;
        if (!$this->user) {
            $this->user = check_admin();
        }

        $ret_data = $_GPC;
        if (!$this->user) {
            $ret['code'] = 2;
        } else {
            $ret['code'] = 1;
            $file = new File();
            $_GPC['user_id'] = $this->user['id'];

            $test = $file->where(['md5' => $_GPC['md5']])->find();
            if (!$test) {
                $file->allowField(true)->save($_GPC);
            } else {
                $file = $test;
            }
            $ret['data'] = $file->toArray();
        }
        echo json_encode($ret);
    }

    /**
     * 文件检测 到数据库核对
     * @return mixed
     */
    public function check_md5()
    {
        global $_GPC;
        //$_GPC = input('param.');
        $where['md5'] = trim(htmlspecialchars(input('param.md5')));
        $file = File::get($where);//$this->file->where($where)->find();
        $ret_data = [];
        if ($file) {
            //todo check if file exist
            $ret_data = $file->toArray();
            $ret_data['ifExist'] = 1;

        } else {
            $ret_data['ifExist'] = 0;
        }
        $ret['code'] = 1;
        $ret['data'] = $ret_data;
        echo json_encode($ret);
    }
}
