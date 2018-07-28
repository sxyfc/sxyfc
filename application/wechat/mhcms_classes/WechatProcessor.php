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
namespace app\wechat\mhcms_classes;

use app\wechat\util\WechatProcessor as Processor;
use app\wechat\util\WechatUtility;
use think\Log;

class WechatProcessor extends Processor
{

    public function respond()
    {
        // TODO: Implement respond() method.
        if ($this->rule['reply_type'] == "text") {
            return $this->respText($this->rule['reply_content']);
        }


        if ($this->rule['reply_type'] == "image") {
            return $this->respImage($this->rule['reply_content']);
        }


        if ($this->rule['reply_type'] == "news") {
            $news = $this->make_news_respond();
            return $this->respNews($news);
        }

        if ($this->rule['reply_type'] == "voice") {
            return $this->respVoice($this->rule['reply_content']);
        }


        if ($this->rule['reply_type'] == "video") {
            $video = set_model("sites_wechat_material")->where(['media_id'=>$this->rule['reply_content']])->find();
            $video['tag'] = mhcms_json_decode($video['tag']);
            return $this->respVideo( $video);
        }
    }


}