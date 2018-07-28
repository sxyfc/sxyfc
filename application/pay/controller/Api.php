<?php
namespace  app\pay\controller;

use app\common\controller\Base;

class Api extends Base {

    public function call_back(){
        $gateway = input('param.gateway') ;
        $gateway = "\\app\\pay\\util\\$gateway";
        $gateway = new $gateway();
        $gateway->callback();
    }


}