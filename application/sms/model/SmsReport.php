<?php

namespace app\sms\model;

use app\common\model\Common;

class SmsReport extends Common
{
    protected $type = [
        'log' => 'json',
        'content' => 'json'
    ];

}
