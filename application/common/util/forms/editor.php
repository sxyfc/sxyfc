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
namespace app\common\util\forms;

use app\common\util\forms\Forms;
use think\Cache;

class editor extends Form
{
    public function ueditor($field)
    {

        $content = '';
        if ($field->node_field_default_value && is_array($field->node_field_default_value)) {
            foreach ($field->node_field_default_value['contents'] as $k => $v) {
                $content .= '[page]' . $field->node_field_default_value['titles'][$k] . "[/page]";
                $content .= $v;
            }
        } else {
            $content = $field->node_field_default_value;
        }
        $field->node_field_default_value = $content;
        $field = new Field($field);
        return Forms::ueditor($field);
    }

    public function process_input($input)
    {
        $input = htmlspecialchars_decode($input);
        $input = remove_xss($input);
        return $input;
    }

    /**
     * @param $input
     * @return mixed
     */
    public function process_model_output($input , &$base)
    {
        $content = $input;
        $content_bck = html_entity_decode($content);
        $CONTENT_POS = strpos($content_bck, '[page]');
        $CONTENT_POS_END = strpos($content_bck, '[/page]');
        if ($CONTENT_POS !== false && $CONTENT_POS != 0) {
            $before = substr($content_bck, 0, $CONTENT_POS);
            $content_bck = substr($content_bck, $CONTENT_POS);


        }
        if ($CONTENT_POS !== false) { // 开启分页了
            $pattens = <<<TAG
|\[page\](.*)\[/page\]|U
TAG;
            $contents = array_filter(preg_split($pattens, $content_bck));
            $contents[1] = $before . $contents[1];
            $data['contents'] = $contents;
//获取子标题

            if (preg_match_all($pattens, $content_bck, $m, PREG_PATTERN_ORDER)) {
                foreach ($m[1] as $k => $v) {
                    $p = $k + 1;
                    $titles[$p] = strip_tags($v);
                }
            }
            $data['titles'] = $titles;
        } else {
            $data['contents'] = [$input];
        }

        if (defined("IN_MHCMS_ADMIN")) {
            $data = $input;
        }
        return $data;
    }

    public function process_model_input($input , &$base)
    {
        $input = htmlspecialchars_decode($input);
        $input = remove_xss($input);
        return $input;
    }

    /**
     * @param $input
     * @return mixed
     */
    public function process_output($input)
    {
        $content = $input;
        $content_bck = html_entity_decode($content);
        $CONTENT_POS = strpos($content_bck, '[page]');
        $CONTENT_POS_END = strpos($content_bck, '[/page]');
        if ($CONTENT_POS !== false && $CONTENT_POS != 0) {
            $before = substr($content_bck, 0, $CONTENT_POS);
            $content_bck = substr($content_bck, $CONTENT_POS);


        }
        if ($CONTENT_POS !== false) { // 开启分页了
            $pattens = <<<TAG
|\[page\](.*)\[/page\]|U
TAG;
            $contents = array_filter(preg_split($pattens, $content_bck));
            $contents[1] = $before . $contents[1];
            $data['contents'] = $contents;
//获取子标题

            if (preg_match_all($pattens, $content_bck, $m, PREG_PATTERN_ORDER)) {
                foreach ($m[1] as $k => $v) {
                    $p = $k + 1;
                    $titles[$p] = strip_tags($v);
                }
            }
            $data['titles'] = $titles;
        } else {
            $data['contents'] = [$input];
            $data['titles'] = [""];
        }
        return $data;
    }

    function parseSubject($content)
    {
        $pattern = <<<TAG
|\[subject\](.*)\[/subject\]|U
TAG;
        $replacement = '<div class="subjects" sid="${1}" id="subject_${1}"></div>';
        $content = preg_replace($pattern, $replacement, $content);
        return $content;
    }


}