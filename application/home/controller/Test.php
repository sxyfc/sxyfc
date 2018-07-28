<?php
// +----------------------------------------------------------------------
// | MHCMS [ 滨海贺喜鸟网络科技有限公司 版权所有 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015~2017 http://www.mhcms.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: new better <1620298436@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;

use app\common\controller\Base;
use app\common\controller\ModuleBase;
use app\common\model\AttachConfig;
use app\common\model\Hits;
use app\common\model\Models;
use app\common\model\Users;
use app\core\util\ContentTag;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use think\Db;

class Test extends ModuleBase
{
    public function t1(){

        echo url("zhuangxiu/company/detail", ['id'=>1]);
    }

    public function index($cate_id = 45){
        global $_GPC;
        $model = set_model("market_info");
        /** @var Models $model_info */
        $model_info = $model->model_info;

        if (is_weixin()) {
            $this->view->hide_fields = [
                'mobile', 'area_id', 'contractor', 'city'
            ];
        }

        if ($this->isPost(true) && $model_info) {


            $base_info = input('post.data/a');//get the base info

            $base_info['user_id'] = $this->user['id'];
            $res = $model_info->add_content($base_info);

            if ($res['code'] == 1) {
                token();
                $to_url = url('market/user_info/index');
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "'$to_url'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {

            $this->view->assign('field_list', $model_info->get_user_publish_fields(['cate_id' =>$cate_id], ['cate_id']));
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function t(){
        $ffmpeg =  FFMpeg::create();
        $audio = $ffmpeg->open(SYS_PATH . '1.amr');

        $format = new Mp3();

        $format
            ->setAudioChannels(2)
            ->setAudioKiloBitrate(256);

        $audio->save($format, SYS_PATH . 'track.mp3');
    }

    public function p(){
        return $this->view->fetch();
    }
    public function pi(){

        $where['wechat_unionid'] = "ofl-es9AJWwARFQ-55_8mQpiE93Q";
        $user = Users::where($where)->find();
        test($user);
    }
}