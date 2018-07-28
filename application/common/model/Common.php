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
use app\common\util\forms\FormFactory;
use think\Cache;
use think\Model;
use think\Db;

class Common extends Model {
    /** @var FormFactory $model_info */
    public $form_factory;
    public $cache_one_key;
    public $site_id;
	public $cache;
	public $root_domain;
	public $cache_path;
	//if it is not the main site  $suffix will be added to identify for sub sites
	public $suffix;

    public function initialize() {
        //$this->form_factory = new FormFactory();
    }
	/**
     * this function used to cache shared data in a root domain for all sites belong to the same root domain
     * @param null $index index to process , formate the data by modify the index
     * @param bool $update
     * @return false|mixed|static[]
     */
    public function fetchAll($index = null, $update = false , $path = "" , $where = []  ) {
        $list = $this->where($where)->select()->toArray();
	    $new_list = [];
	    if($index){
            /**
             * do the formate
             */
		    foreach ($list as $val) {
			    $new_list[$val[$index]] = $val;
		    }
	    }else{
		    $new_list = $list;
	    }
        return $new_list;
    }


    /** for perform this func should be removed in the future
     * current this function  is only used for fields listing
     * @param $where
     * @param $index
     * @return false|mixed|static[]
     * @internal param the $name cache name of the current model
     */
    public static function selectAll($where ,$index) {
        $list = self::all($where);
	    $new_list = [] ;
        foreach ($list as $val) {
            $new_list[$val[$index]] = $val;
        }
        return $new_list;
    }

    /**
     * cache one data of the current model
     * @param $where
     * @param $key :Must exist in $where array_keys
     * @return mixed|static
     */
    public function get_one($where, $update = false) {
        return $data = self::get($where);;
    }


    /** check if a table is currently exists
     * @param $table
     * @return bool
     */
    public static function tableExists($table) {
        try {
            //echo $table;
            $res = \think\Db::query("SELECT 1 FROM " . config("database.prefix") . $table . "  limit 1");
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }
    /**
     * @param $table
     * @param $new_name
     * @return bool
     */
    public static function renameTable($table, $new_name) {
        //echo 'ALTER TABLE `' . config("database.prefix") . $table . '` RENAME TO `' . config("database.prefix") . $new_name . '`';
        try {
             \think\Db::execute('ALTER TABLE `' . config("database.prefix") . $table . '` RENAME TO `' . config("database.prefix") . $new_name . '`');
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $table
     * @return bool
     */
    public function dropTable($table) {
        if (self::tableExists($table)) {
            \think\Db::execute('DROP TABLE ' .config("database.prefix") . $table . '');
            return true;
        }else{
            return false;
        }
    }

    /**test if a record exists
     * @param $key
     * @param $value
     * @return array|false|\PDOStatement|string|Model
     */
    public function exists($key , $value){
        $where[$key] = $value;
        return $this->where($where)->find();
    }

	/**gen a format for typeahead to use
	 * @param $arr
	 * @param $id_key
	 * @param $name_key
	 * @return mixed
	 */
	public function typeahead_data($arr , $id_key , $name_key){
    	foreach($arr as $k => $v){
		    $arr[$k]['id'] = $v[$id_key];
		    $arr[$k]['name'] = $v[$name_key];
	    }
	    return $arr;
    }

    /**Reports
     * @param array $where
     * @param string $order
     * @param array $sum_fields
     * @param int $page_size
     * @return mixed
     */

    public function reports($where = [] , $order ="" , $sum_fields = [] , $page_size = 20){
        $ret['list'] = $this->where($where)->order($order)->paginate($page_size);
        $ret['page'] = $ret['list']->render();
        if(count($sum_fields) > 0){
            foreach($sum_fields as $sum_field){
                $ret['sum_'.$sum_field] = $this->where($where)->sum($sum_field);
            }
        }

        return $ret;
    }

    /**
     * 指定默认数据表名（含前缀）
     * @access public
     * @param $model_id
     * @return $this
     * @internal param string $table 表名
     */
    public function setModel($model_id)
    {
        $model = Models::get(['id'=>$model_id]);
        if($model){
            $this->table = $model['table_name'];
            return $this;
        }
        return $this;

    }
}