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

use app\common\model\Models;
use app\core\util\MhcmsModules;
use app\core\util\MhcmsTheme;
use think\Db;

class Form implements FormInterface
{
    public $field;

    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    public function out_put_form(&$base)
    {
        //field info
        $field_type_name = $this->field->field_type_name;
        /** @var FormInterface $form_object */
        $class_name = "\\app\\common\\util\\forms\\$field_type_name";
        $form_element = new $class_name($this->field);
        $this->form_data($base);
        //todo out put form
        $temp = call_user_func_array(array($form_element, $this->field->node_field_mode), array($this->field, $base));
        return $temp;
    }

    public function out_put_value($input, & $base)
    {
        //field info
        $field_type_name = $this->field->field_type_name;
        /** @var FormInterface $form_object */
        $class_name = "\\app\\common\\util\\forms\\$field_type_name";
        $form_element = new $class_name($this->field);
        return call_user_func_array(array($form_element, "process_model_output"), [$input, &$base]);
    }


    public function input_put_value($input, &$base)
    {
        //field info
        $field_type_name = $this->field->field_type_name;
        /** @var FormInterface $form_object */
        $class_name = "\\app\\common\\util\\forms\\$field_type_name";
        $form_element = new $class_name($this->field);
        //$this->form_data();
        //todo out put form
        return call_user_func_array(array($form_element, "process_model_input"), [$input, &$base]);
    }

    public function form_data(&$base = [])
    {
        global $_W, $_GPC;
        $field = &$this->field;
        //node_field_data_souce_type
        if ($field->field_name == "is_new") {
        }
        //数据眼类型的
        if (isset($field->node_field_data_source_type) && $field->node_field_data_source_type) {
            switch ($field->node_field_data_source_type) {
                case "linkage":
                    $result = \think\Cache::get("linkage/$field->node_field_data_source_config");
                    //$field = NodeFields::get($field->node_field_id);
                    //TODO linkage Level test($field);
                    $this->__get_data($result['data']);
                    break;
                case "user_roles":
                    $where['site_id'] = ["IN" , [0 , $_W['site']['id']]];
                case 'model' :
                    //字段配置里面的where数据
                    if($field->where){
                        //支持site_id
                        if(Models::field_exits('site_id' , $field->node_field_data_source_config)){
                            $mapping['site_id'] = $_W['site']['id'];
                        }

                       $field->where = parseParam($field->where , $mapping);
                       $_where_data = explode('$' , $field->where);
                       $where[$_where_data[0]] = [$_where_data[1] , $_where_data[2]];
                    }

                    //{loupan_id}
                    if (is_numeric($field->node_field_data_source_config)) {
                        $model = Models::get(['id' => $field->node_field_data_source_config]);
                        $result = Db::name($model['table_name'])->where($where)->select();
                    } else {
                        //var_dump($field->module);
                        $mapping['cate'] = $field->module . "_cate";
                        $distribute = true;
                        $none_distribute_models = ['{cate}'];

                        if(in_array($field->node_field_data_source_config , $none_distribute_models)){
                         //   $distribute = false;
                        }

                        //var_dump($field->node_field_data_source_config);

                        //var_dump($mapping);
                        //test();
                        $field->node_field_data_source_config = parseParam($field->node_field_data_source_config , $mapping);

                        $model = set_model($field->node_field_data_source_config);
                        /** @var Models $model_info */
                        $model_info = $model->model_info;

                        /* 不区分模块的 模型 */

                        $sys_models = ['admin'];
                        if ($distribute  && !$where['module'] && !in_array(ROUTE_M, $sys_models) && ROUTE_M != "debug" ) {
                            if (Models::field_exits('module', $field->node_field_data_source_config)) {
                                $where['module'] = ROUTE_M;
                            }
                        }

                        if ($distribute && !$where['site_id'] && $model_info::field_exits('site_id', $field->node_field_data_source_config) ) {
                            //    $cate = set_model("article_cate")->where(['site_id'=>$_W['']])->find();
                            $where['site_id'] = $_W['site']['id'];
                        }

                        if ($field->node_field_name == "cate_id") {
                            //    $cate = set_model("article_cate")->where(['id'=>$field->node_field_default_value])->find();
                            //    $where['model_id'] = $cate['model_id'];
                        }
                        $model = $model->where($where);

                        //手动传入的where数据
                        if (isset($field->node_field_where)) {
                            if (!is_array($field->node_field_where)) {
                                $wheres = explode("\r\n", $field->node_field_where);
                                foreach ($wheres as $if) {
                                    $model = $model->where($if);
                                }
                            } else {
                                $model = $model->where($field->node_field_where);
                            }
                        }

                        $result = $model->select()->toArray();

                        if(!$field->node_field_pk_key){
                            $field->node_field_pk_key = $model->model_info['id_key'];
                        }
                        if(!$field->node_field_name_key){
                            $field->node_field_name_key = $model->model_info['name_key'];
                        }
                    }
                    if (!$field->node_field_pk_key || !$field->node_field_name_key) {
                        die("model " . $field->node_field_data_source_config . "'s id_key or name_key is empty");
                    }


                    $field->node_field_parentid_key = $field->node_field_parentid_key ? $field->node_field_parentid_key : $model->model_info['parent_id_key'];
                    $this->__get_data($result);
                    break;
                case "sub_model":
                    $where = [];
                    if (is_numeric($field->node_field_data_source_config)) {
                        $model = Models::get(['id' => $field->node_field_data_source_config]);
                        $result = Db::name($model['table_name'])->where($where)->select();
                    } else {
                        $model = set_model($field->node_field_data_source_config);
                        /** @var Models $model_info */
                        $model_info = $model->model_info;

                        /* 不区分模块的 模型 */
                        $sys_models = ['admin'];
                        if (!in_array(ROUTE_M, $sys_models) && ROUTE_M != "debug") {
                            if (Models::field_exits('module', $field->node_field_data_source_config)) {
                                $where['module'] = ROUTE_M;
                            }
                        }

                        if ($model_info::field_exits('site_id', $field->node_field_data_source_config)) {
                            //    $cate = set_model("article_cate")->where(['site_id'=>$_W['']])->find();
                            $where['site_id'] = $_W['site']['id'];
                        }

                        if ($field->node_field_name == "cate_id") {
                            //    $cate = set_model("article_cate")->where(['id'=>$field->node_field_default_value])->find();
                            //    $where['model_id'] = $cate['model_id'];
                        }
                        $model = $model->where($where);
                        if (isset($field->node_field_where)) {
                            if (!is_array($field->node_field_where)) {
                                $wheres = explode("\r\n", $field->node_field_where);
                                foreach ($wheres as $if) {
                                    $model = $model->where($if);
                                }
                            } else {
                                $model = $model->where($field->node_field_where);
                            }
                        }
                        $result = $model->select()->toArray();

                        $field->node_field_pk_key = $model->model_info['id_key'];
                        $field->node_field_name_key = $model->model_info['name_key'];
                    }
                    if (!$field->node_field_pk_key || !$field->node_field_name_key) {
                        die("model " . $field->node_field_data_source_config . "'s id_key or name_key is empty");
                    }
                    $this->__get_data($result);
                    break;
                case 'area' :
                    $where['linkage_id'] = $_W['site']['linkage_id'];
                    if (is_numeric($field->node_field_data_source_config)) {
                        $model = Models::get(['id' => $field->node_field_data_source_config]);
                        $result = Db::name($model['table_name'])->where($where)->select();
                    } else {
                        $result = D($field->node_field_data_source_config)->select($where)->toArray();
                    }
                    // $result = D($field->node_field_data_source_config)->fetchAll($index = 'id', $update = false , $path = "" , $where);
                    $this->__get_data($result);
                    break;
                case 'diy_arr' :
                    //自定义数组可以是 组织好的数组也可以是 一行一个的字符串
                    $field->node_field_pk_key = $field->node_field_pk_key ? $field->node_field_pk_key : 'id';
                    $field->node_field_name_key = $field->node_field_name_key ? $field->node_field_name_key : 'name';


                    if (!is_array($field->node_field_data_source_config)) {
                        $datas = explode("\r\n", $field->node_field_data_source_config);
                    } else {
                        $datas = $field->node_field_data_source_config;
                    }

                    if (!is_array($datas)) {
                        $datas = [];
                    }
                    foreach ($datas as $data) {
                        $_new_data = [];
                        if (is_array($data)) {
                            $_new_data = $data;
                        } else {
                            if (strpos($data, "|") !== false) {
                                $data = explode("|", $data);
                                $_new_data[$field->node_field_pk_key] = $data[0];
                                $_new_data[$field->node_field_name_key] = $data[1];
                            } else {
                                $_new_data = [];
                                $_new_data[$field->node_field_name_key] = $data;
                                $_new_data[$field->node_field_name_key] = $data;
                            }
                        }
                        $field->form_data[] = $_new_data;
                    }
                    break;
                case 'mhcms_options':
                    $where = [];
                    $where['field_name'] = $field->node_field_name;
                    $where['model_id'] = $field->model_id;
                    if (Models::field_exits('site_id', $field->model_id)) {
                        $where['site_id'] = $field->site_id;
                    }
                    if($field->bind_cate && $base['cate_id']){
                        $where['cate_ids'] = ['like' , "%,{$base['cate_id']},%"];
                    }

                    $model = set_model('option');
                    $field->form_data = $model->where($where)->select();
                    $field->node_field_pk_key = $model->model_info['id_key'];
                    $field->node_field_name_key = $model->model_info['name_key'];
                    break;
                case 'sub_cate_id':
                    $parent_id = $_GPC['cate_id'];
                    $where = [];
                    $where['parent_id'] = $parent_id;
                    if (is_numeric($field->node_field_data_source_config)) {
                        $model = set_model($field->node_field_data_source_config);
                        $result = $model->where($where)->select();
                    } else {
                        $result = D($field->node_field_data_source_config)->where($where)->select()->toArray();
                    }
                    $this->__get_data($result);
                    break;
                case 'theme':
                    if (!$field->module) {
                        test("empty_module");
                    }
                    $themes = MhcmsTheme::get_module_themes_list($field->module);

                    $this->__get_data($themes);;
                    break;
                case 'template':
                    //获取当前模块主题设置
                    $theme = MhcmsModules::get_module_theme(ROUTE_M);

                    $res = MhcmsTheme::get_theme_tpls(ROUTE_M, $theme);

                    $mobile_tpls = is_array($res['mobile']) ? count($res['mobile']) : 0;
                    $desktop_tpls = is_array($res['desktop']) ? count($res['desktop']) : 0;

                    if ($mobile_tpls < $desktop_tpls) {
                        $tpls = $res['desktop'];
                    } else {
                        $tpls = $res['mobile'];
                    }
                    $options = [];
                    foreach ($tpls as $tpl) {
                        $data = explode(".", $tpl);
                        if ($data[0] && strpos($data[0], $field->node_field_data_source_config) === 0) {
                            $options[] = ['id' => $data[0], 'name' => $data[0]];
                        }
                    }

                    $field->node_field_pk_key = 'id';
                    $field->node_field_name_key = 'name';
                    $this->__get_data($options);
                    break;

            }
        }


        $this->field = $field;
    }

    /**
     * Format Data For Select
     */
    public function __get_data($data)
    {
        $field = &$this->field;
        $result_data = [];
        if ($data) {
            foreach ($data as $v) {
                $_V = [];
                if (!is_array($v)) {
                    $v = $v->toArray();
                }
                $_V[$field->node_field_pk_key] = $v[$field->node_field_pk_key];
                $_V[$field->node_field_name_key] = $v['name'] = $v[$field->node_field_name_key];
                if (!empty($field->node_field_parentid_key)) {
                    $_V[$field->node_field_parentid_key] = $v['parent_id'] = $v[$field->node_field_parentid_key];
                } else {
                    $_V[$field->node_field_parentid_key] = $v['parent_id'] = "";
                }
                $result_data[$v[$field->node_field_pk_key]] = $_V;
            }
            $field->form_data = $result_data;
        } else {
            $field->form_data = [];
        }
    }


    public function process_model_output($input, &$base)
    {
        $out_put = $input;
        return $out_put;
    }

    public function process_model_input($input, &$base)
    {
        $out_put = $input;
        return $out_put;
    }
}