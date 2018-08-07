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
namespace app\common\model;

class PaymentLogs extends Common
{

    public function getUnitTypeTextAttr($value,$data)
    {
        $value = $this->unit_type;
        $status = [1=>'房宝',2=>'金币'];
        return $status[$value];
    }

    public function getPayTypeTextAttr($value,$data)
    {
        $value = $this->pay_type;
        $status = [0 => '系统交易' ,1=>'余额提现',2=>'余额存款',3=>'佣金'];
        return $status[$value];
    }

}
