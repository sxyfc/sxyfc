<?php

namespace app\wechat\util;

use think\Log;

/**
 * 消息处理器
 * Class WechatProcessor
 * @package app\wechat\util
 */
abstract class WechatProcessor
{

    public $priority;

    public $message;

    public $inContext;

    public $rule;

    abstract function respond();

    protected function respText($content)
    {
        if (empty($content)) {
            return error(-1, 'Invaild value');
        }

        $content= htmlspecialchars_decode($content);
        $content = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $content);
        $content = strip_tags($content, '<a>');

        if (stripos($content, './') !== false) {
            preg_match_all('/<a .*?href="(.*?)".*?>/is', $content, $urls);
            if (!empty($urls[1])) {
                foreach ($urls[1] as $url) {
                    // $content = str_replace($url, $this->buildSiteUrl($url), $content);
                }
            }
        }
        $content = str_replace("\r\n", "\n", $content);
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'text';
        $response['Content'] = htmlspecialchars_decode($content);
        preg_match_all('/\[U\+(\\w{4,})\]/i', $response['Content'], $matchArray);
        if (!empty($matchArray[1])) {
            foreach ($matchArray[1] as $emojiUSB) {
                $response['Content'] = str_ireplace("[U+{$emojiUSB}]", utf8_bytes(hexdec($emojiUSB)), $response['Content']);
            }
        }
        return $response;
    }

    protected function respImage($mid)
    {
        if (empty($mid)) {
            return error(-1, 'Invaild value');
        }
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'image';
        $response['Image']['MediaId'] = $mid;
        return $response;
    }

    protected function respVoice($mid)
    {
        if (empty($mid)) {
            return error(-1, 'Invaild value');
        }
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'voice';
        $response['Voice']['MediaId'] = $mid;
        return $response;
    }

    protected function respVideo(array $video)
    {
        if (empty($video)) {
            return error(-1, 'Invaild value');
        }
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'video';
        $response['Video']['MediaId'] = $video['media_id'];
        $response['Video']['Title'] = $video['tag']['title'];
        $response['Video']['Description'] = $video['digest'];
        return $response;
    }

    protected function respMusic(array $music)
    {
        if (empty($music)) {
            return error(-1, 'Invaild value');
        }
        global $_W;
        $music = array_change_key_case($music);
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'music';
        $response['Music'] = array(
            'Title' => $music['title'],
            'Description' => $music['description'],
            'MusicUrl' => tomedia($music['musicurl'])
        );
        if (empty($music['hqmusicurl'])) {
            $response['Music']['HQMusicUrl'] = $response['Music']['MusicUrl'];
        } else {
            $response['Music']['HQMusicUrl'] = tomedia($music['hqmusicurl']);
        }
        if ($music['thumb']) {
            $response['Music']['ThumbMediaId'] = $music['thumb'];
        }
        return $response;
    }


    protected function make_news_respond()
    {
        global $_W;
        $commends = self::material_build_reply($this->rule['reply_content']);

        $news = array();

        foreach ($commends as $key => $commend) {
            $row = array();
            if (!empty($commend['media_id'])) {
                if (empty($news[$key]['url'])) {
                    $news[$key]['url'] = url('wechat/article/detail', array('id' => $commend['id']));
                }
            } else {
                $row['title'] = $commend['title'];
                $row['description'] = $commend['description'];
                $row['picurl'] = !empty($commend['picurl']) ?  $commend['picurl']  : "";
                $row['url'] = empty($commend['url']) ? url('wechat/article/detail', array('id' => $commend['id'])) : $commend['url'];
                $news[] = $row;
            }
        }
        return $news;
    }


    //todo cache the news for performance
    public static function material_build_reply($media_id)
    {
        if (empty($media_id)) {
            return error(1, "素材id参数不能为空");
        }

        $main_media = set_model("sites_wechat_material")->where(['parent_id' => 0, 'media_id' => $media_id])->find();

        $reply_materials = set_model("sites_wechat_material")->where(['parent_id' => $main_media['id']])->select();

        $reply = array();
        foreach ($reply_materials as $material) {
            $reply[] = array(
                'title' => $material['title'],
                'description' => $material['digest'],
                'picurl' => $material['thumb_url'],
                'url' => !empty($material['content_source_url']) ? $material['content_source_url'] : $material['url'],
            );
        }
        return $reply;
    }

    protected function respNews(array $news)
    {
        if (empty($news) || count($news) > 10) {
            return error(-1, 'Invaild value');
        }
        $news = array_change_key_case($news);
        if (!empty($news['title'])) {
            $news = array($news);
        }
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'news';
        $response['ArticleCount'] = count($news);
        $response['Articles'] = array();
        foreach ($news as $row) {
            $response['Articles'][] = array(
                'Title' => $row['title'],
                'Description' => ($response['ArticleCount'] > 1) ? '' : $row['description'],
                'PicUrl' => $row['picurl'],
                'Url' => $this->build_url($row['url']),
                'TagName' => 'item'
            );
        }
        return $response;
    }

    protected function build_url($url)
    {
        global $_W;
        $mapping = array(
            '[from]' => $this->message['from'],
            '[to]' => $this->message['to'],
            '[rule]' => $this->rule,
            '[uniacid]' => $_W['uniacid'],
        );
        $url = str_replace(array_keys($mapping), array_values($mapping), $url);
        $url = preg_replace('/(http|https):\/\/.\/index.php/', './index.php', $url);
        if (strexists($url, 'http://') || strexists($url, 'https://')) {
            return $url;
        }
        //todo gen analyze url
    }


    protected function respCustom(array $message = array())
    {
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'transfer_customer_service';
        if (!empty($message['TransInfo']['KfAccount'])) {
            $response['TransInfo']['KfAccount'] = $message['TransInfo']['KfAccount'];
        }
        return $response;
    }

}

