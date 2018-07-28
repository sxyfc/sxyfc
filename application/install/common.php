<?php

if(!function_exists('fun_helper')){
    function fun_helper(){
        $helpers=  func_get_args();
        if(!empty($helpers)){
            foreach($helpers as $helper){
                $file= APP_PATH .'install/helpers/'.$helper.'.fun.php';
                if(file_exists($file)){
                    /** @var string $file */
                    include_once $file;
                }
            }
        }
    }
}

