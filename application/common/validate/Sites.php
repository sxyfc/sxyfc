<?php
namespace app\common\validate;
use think\Validate;
class Sites extends Validate{
    private $Sites;
    protected $rule = [
        'site_pinyin'  =>  'require|checkName:'
    ];
    
     // 自定义验证规则
    protected function checkName($value ,$rule ,$data)
    { 
        $res = \app\common\model\Sites::get(['site_pinyin'=>$value]);
        if($res){
            return "拼音不能重复";
        }else{
            return true;
        }
    }
}