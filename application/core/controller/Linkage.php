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
namespace app\core\controller;
use app\common\controller\AdminBase;
set_time_limit(0);
class Linkage extends AdminBase {
    private $linkage_model;
    public $childnode;
    public function _initialize() {
        parent::_initialize();
        $this->linkage_model = new \app\common\model\Linkage();
        $this->childnode = array();
    }
    /**
     * @param int $id
     * @return
     */
    public function index($id = 0) {//get the linkage info
	    $id = (int) $id;
        if (!$id) {
            $where = array('keyid' => 0);
            $infos = $this->linkage_model->all($where);
            $this->view->assign('list', $infos);
            return $this->view->fetch();
        } else {
            $linkage_data = $this->linkage_model->where(['linkageid' => $id])->find();
            $keyid = $linkage_data['keyid'] ? $linkage_data['keyid'] : $id;
            $sql_parentid = $linkage_data['keyid'] ? $id : $linkage_data['keyid'];
            $this->view->assign('list', $this->linkage_model->where(array('keyid' => $keyid, 'parentid' => $sql_parentid))->select());
            return $this->view->fetch("index_sub");
        }
    }
    /**
     * 添加联动菜单
     */
    function create() {
        if ($this->isPost()) {
            //normal add
            $info = input('param.data/a');
            $info['siteid'] = $info['siteid'] === 0 ? $info['siteid'] : $this->site_id;
            $insert_id = $this->linkage_model->save($info);
            if ($insert_id) {
                $this->zbn_msg(zlang('operation_success'));
            }
        } else {
            if (empty($keyid) && empty($parentid)) {
                return $this->view->fetch();
            } else {
                return $this->view->fetch("create_sub");
            }
        }
    }
    /**
     * 子菜单添加
     */
    public function create_sub($id) {
        $linkageid = (int)$id;
        $where = array('linkageid' => $linkageid);
        $parent_info = $this->linkage_model->where($where)->find();

        if ($this->isPost() && $parent_info) {
            $info = input('param.data/a');
            $info['keyid'] = empty($parent_info['keyid'])  ? $id : $parent_info['keyid'] ;
            $name = isset($info['name']) && trim($info['name']) ? trim($info['name']) : $this->zbn_msg(zlang('linkage_parameter_error'));
            $names = explode("\r\n", trim($name));
            foreach ($names as $name) {
                $name = trim($name);
                if (!$name) continue;
                $info['name'] = $name;
                \app\common\model\Linkage::create($info);
            }
            $this->zbn_msg(zlang('operation,success'));
        } else {
            $this->view->parentid = $linkageid;
            $this->view->keyid = $parent_info['keyid'];
            return $this->view->fetch();
        }
    }
    /**
     * 编辑联动菜单
     */
    public function edit($id) {
        $linkageid = (int)$id;
        $where = array('linkageid' => $linkageid);
        $info = $this->linkage_model->where($where)->find();
        if (!$info) {
            $this->zbn_msg("error params!");
        } else {
            if ($this->isPost()) {
                $info = input('param.data/a');
                //$info['siteid'] = isset($info['siteid']) ? (int) $info['siteid'] : $this->site_id;//
                $this->linkage_model->update($info, $where);

                $this->zbn_msg(zlang('operation_success'));
            }
            else {
                $this->view->detail = $info;
                //$this->view->form_factory = new \app\common\util\forms\FormFactory();
                if ($info['keyid']) {
                    $this->view->keyid = $info['keyid'];
                    $this->view->parentid = $info['parentid'];
                }else{
                    $this->view->keyid = $info['linkageid'];
                }
                return $this->view->fetch();
            }
        }
    }
    /**
     * 删除菜单
     */
    public function delete($id) {
        $linkageid = intval($id);
        $where = array('linkageid' => $linkageid);
        $info = $this->linkage_model->where($where)->find();
        if($info['is_core']){
            $data['code'] = 0;
            $data['msg'] = "failed, core function can not be deleted!";
            return $data;
        }
        $keyid = intval($info['keyid']);
        $this->_get_childnode($linkageid);
        if (is_array($this->childnode)) {
            foreach ($this->childnode as $linkageid_tmp) {
                $this->linkage_model->where(array('linkageid' => $linkageid_tmp))->delete();
            }
        }
        $this->linkage_model->where(array('keyid' => $linkageid))->delete();
        $id = $keyid ? $keyid : $linkageid;
        if (!$keyid) {
            \think\Cache::rm('linkage/'.$linkageid);
        }
        $data['code'] = 1;
        $data['msg'] = "success!";
        return $data;
    }
    /**
     * cache
     */
    public function cache($id) {
        $linkageid = intval($id);
        if($this->_cache($linkageid)){
            $data['msg'] = 'operate success!';
            $data['code'] = 1;
        }else{
            $data['msg'] = 'current we do not support this operation!';
            $data['code'] = 0;
        }

        return $data;
    }
    /**
     *
     * @param init $linkageid
     */
    private function _cache($linkageid) {
        $linkageid = intval($linkageid);
        $info = $this->linkage_model->where(array('linkageid' => $linkageid))->find()->toArray();
        //print_r($info);
        if($info['keyid']!=0){
            return false;
        }else{
            $info['data'] = $this->submenulist($linkageid);
            \think\Cache::set('linkage/' . $linkageid, $info);
            return true;
        }

    }
    /**
     * 子菜单列表
     * @param unknown_type|int $keyid
     * @return mixed
     */
    private function submenulist($keyid = 0) {
        $keyid = intval($keyid);
        $where = ($keyid > 0) ? array('keyid' => $keyid) : '';
        $linkages = $this->linkage_model->where($where)->order('listorder ,linkageid')->select()->toArray();
        //var_dump($linkages->toArray());exit;
        foreach ($linkages as $linkage) {
            $new_linkages[$linkage['linkageid']] = $linkage;
        }
        if (isset($new_linkages)) {
            $linkages = $new_linkages;
            unset($new_linkages);
        }
        $datas = [];
        foreach ($linkages as $k => $r) {
            $arrchildid = $r['arrchildid'] = $this->get_arrchildid($r['linkageid'], $linkages);
            unset($linkages[$k]);
            $child = $r['child'] = is_numeric($arrchildid) ? 0 : 1;
            $this->linkage_model->where(array('linkageid' => $r['linkageid']))->update(array('child' => $child, 'arrchildid' => $arrchildid));
            $datas[$r['linkageid']] = $r;
        }
        return $datas;
    }
    /**
     * @param $linkageid
     * @param $linkages
     * @return string $arrchildid|string
     */
    private function get_arrchildid($linkageid, $linkages) {
        static $i = 0;
        $arrchildid = $linkageid;
        foreach ($linkages as $k => $linkage) {
            if ($linkage['parentid'] && $linkage['linkageid'] != $linkageid && $linkage['parentid'] == $linkageid) {
                $arrchildid .= ',' . $this->get_arrchildid($linkage['linkageid'], $linkages);
            } else {
                unset($linkages[$k]);
            }
        }
        return $arrchildid;
    }


    public function ajax_getlist() {
        $keyid = intval($_GET['keyid']);
        $datas = getcache($keyid, 'linkage');
        $infos = $datas['data'];
        $where_id = isset($_GET['parentid']) ? $_GET['parentid'] : intval($infos[$_GET['linkageid']]['parentid']);
        $parent_menu_name = ($where_id == 0) ? $datas['title'] : $infos[$where_id]['name'];
        foreach ($infos AS $k => $v) {
            if ($v['parentid'] == $where_id) {
                $s[] = iconv('gb2312', 'utf-8', $v['linkageid'] . ',' . $v['name'] . ',' . $v['parentid'] . ',' . $parent_menu_name);
            }
        }
        if (count($s) > 0) {
            $jsonstr = json_encode($s);
            echo $_GET['callback'] . '(', $jsonstr, ')';
            exit;
        } else {
            echo $_GET['callback'] . '()';
            exit;
        }
    }

    /**
     * 获取联动菜单子节点
     * @param int $linkageid
     */
    private function _get_childnode($linkageid) {
	    $linkageid = (int) $linkageid;
        $where = array('parentid' => $linkageid);
        $this->childnode[] = intval($linkageid);
        $result = $this->linkage_model->where($where)->select();
        if ($result) {
            foreach ($result as $r) {
                $this->_get_childnode($r['linkageid']);
            }
        }
    }
    /**
     * 返回菜单ID
     */
    public function public_get_list() {
        $where = array('keyid' => 0);
        $infos = $this->linkage_model->select($where);
        include $infos;
    }
}
?>