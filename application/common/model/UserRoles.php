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

class UserRoles extends Common
{
    protected $type = [
        'allow_admin_user_roles' => 'json',
        'allow_pub_user_roles' => 'json',
        'allow_view_user_roles' => 'json',
    ];

    public $append = ['is_admin_text'];
    //
    public  function getIsAdminTextAttr($value,$data){
        $value = $this->is_admin;
        $str = $value ?  "是"  : "否";
        return $str;
    }
}
