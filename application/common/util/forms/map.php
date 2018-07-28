<?php

namespace app\common\util\forms;

use app\common\util\map\BaiduMap;
use think\Cache;

class map extends Form
{

    public function process_model_output($input, &$base)
    {
        $out_put = $input;
        return $out_put;

    }

    public function process_model_input($input, &$base)
    {
        $input = htmlspecialchars_decode($input);
        $lng_lat = explode(",", $input);
        $base['lng'] = $lng_lat[0];
        $base['lat'] = $lng_lat[1];

        return $input;
    }

    public function baidu_map($field)
    {
        return BaiduMap::render($field->field_name, $field->node_field_default_value, $field->form_group);
    }

}