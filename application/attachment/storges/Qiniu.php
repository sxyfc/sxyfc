<?php

namespace app\attachment\storges;

use app\common\model\File;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\UploadManager;
use think\Log;

/**
 * @property UploadManager uploadmgr
 */
class Qiniu extends StorgeEngine
{

    private $auth, $uploadMgr, $token;

    public function __construct($config)
    {
        $this->config = $config;
        $accessKey = $this->config['accesskey'];
        $secretKey = $this->config['secretkey'];
        $this->bucket = $this->config['bucket'];
        // 初始化签权对象
        $this->user = check_user();
        if (!$this->user) {
            $this->user = check_admin();
        }
        $this->auth = new Auth($accessKey, $secretKey);
    }


    public function get_token()
    {
        $this->token = $this->auth->uploadToken($this->config['bucket']);
        return $this->token;
    }


    public function get_manger()
    {
        $config = new Config();
        $this->uploadmgr = new UploadManager($config);
        return $this->uploadmgr;
    }

    // JARRY_PATH.'static/images/logo.jpg'

    /**
     * 上传小文件
     * @param $file_name
     * @param $file_path
     * @return bool
     */
    public function put_file($file_name, $file_path)
    {
        if (!$this->token) {
            $this->get_token();
        }
        if (!$this->uploadmgr) {
            $this->get_manger();
        }

        list($ret, $err) = $this->uploadmgr->putFile($this->token, $file_name, $file_path);
        if ($err !== null) {
            $err = (array)$err;
            $err = (array)array_pop($err);
            $err = json_decode($err['body'], true);
            return $err;
        } else {
            return true;
        }
    }

    public function upload(File $file ,$local_path = "" )
    {
        //todo gen file name
        $file_name = $file['filename'];
        if(!$local_path){
            $file_path = to_local_media($file);
        }else{
            $file_path = $local_path;
        }

        if (!$this->token) {
            $this->get_token();
        }
        if (!$this->uploadmgr) {
            $this->get_manger();
        }

        list($ret, $err) = $this->uploadmgr->putFile($this->token, $file['url'], $file_path);
        if ($err !== null) {
            $err = (array)$err;
            $err = (array)array_pop($err);
            $err = json_decode($err['body'], true);
            return $err;
        } else {
            //todo update file
            //Log::write($ret);
            $file->type = "Qiniu";
            $file->save();
            return $file;
        }
    }

    public function test()
    {
        if (!$this->token) {
            $this->get_token();
        }
        if (!$this->uploadmgr) {
            $this->get_manger();
        }
        $auth = $this->put_file(trim("logo.jpg"), SYS_PATH . 'statics/images/logo.png');

        if (!$auth) {
            $ret['code'] = 2;
            $ret['msg'] = "配置测试失败，请查看配置值";
            return $ret;
        }

        $url = $this->config['url'];
        $url = strexists($url, 'http') ? trim($url, '/') : 'http://' . trim($url, '/');
        $filename = 'logo.jpg';
        $response = ihttp_request($url . '/' . $filename, array(), array('CURLOPT_REFERER' => $_SERVER['SERVER_NAME']));
        if (is_error($response)) {
            list($code, $msg) = array(-1, '配置失败，七牛访问url错误');
        }
        if (intval($response['code']) != 200) {
            list($code, $msg) = array(-1, '配置失败，七牛访问url错误,请保证bucket为公共读取的');
        }
        $image = getimagesizefromstring($response['content']);
        if (!empty($image) && strexists($image['mime'], 'image')) {
            list($code, $msg) = array(0, '配置成功');
        } else {
            list($code, $msg) = array(-1, '配置失败，七牛访问url错误');
        }
        $ret['code'] = $code;
        $ret['msg'] = $msg;
        return $ret;
    }

    public function form($field)
    {
        global $_W;
        $cdn_url = $_W['cdn_url'];
        $token = $this->token;
        if (!$token) {
            $token = $this->get_token();
        }
        //生产七牛表单
        $form_str = "";
        if (!defined("INIT_SPARK_MD5")) {
            $form_str .= "<script type=\"text/javascript\" src=\"//cdn.bootcss.com/spark-md5/3.0.0/spark-md5.js\"></script>";
            define("INIT_SPARK_MD5", true);
        }
//load css && js
        if (!defined("INIT_QINIU_SDK")) {
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/plugins/qiniu/moxie.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"//cdn.staticfile.org/plupload/2.3.1/plupload.min.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/plugins/qiniu/qiniu.min.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/plugins/qiniu/file_progress.js\"></script>";
            $form_str .= "<script type=\"text/javascript\" src=\"{$cdn_url}statics/plugins/qiniu/qiniu_upload.js\"></script>";
            define("INIT_QINIU_SDK", true);
        }
        $form_str .= "
  
    <script type='text/javascript'>
         var field_name = '$field->node_field_name';
         var real_form_name = \"$field->form_group[$field->node_field_name]$field->multiple\";
         var qiniu_token = \"$token\";
         var user_id = {$this->user['id']};
    </script>
   <input type=\"hidden\" id=\"domain_{$field->node_field_name}\" value=\"{$this->config['url']}/\">
   <input type=\"hidden\" id=\"uptoken_url\" value=\"uptoken\"> 
   <div class=\"col-md-12\">
                        <div id=\"container_{$field->node_field_name}\">
                            <a class=\"layui-btn btn-sm \" id=\"pickfiles_{$field->node_field_name}\" href=\"#\" >
                                <i class=\"glyphicon glyphicon-plus\"></i>
                                <span>选择文件</span>
                            </a>
                        </div>
                    </div>
                    <div style=\"display:none\" id=\"success\" class=\"col-md-12\">
                        <div class=\"alert-success\" style='padding: 10px;margin: 10px 0 '>
                            队列全部文件处理完毕
                        </div>
                    </div>
                    <div class=\"col-md-12 \">
                        <table class=\"ui table table-striped table-hover table-bordered text-left\"   style=\"display:none\">
                            <thead>
                              <tr>
                                <th class=\"col-md-4\">文件名称</th>
                                <th class=\"col-md-2\">大小</th>
                                <th class=\"col-md-6\">详细信息</th>
                              </tr>
                            </thead>
                            <tbody id=\"fsUploadProgress_{$field->node_field_name}\">
                            </tbody>
                        </table>
                    </div>
    <script type='text/javascript'>
         
    </script>
";
        return $form_str;
    }

    public function get_prefix()
    {
        return $this->config['url'];
    }

    public function convert_amr($filePath, $mediaid)
    {
        global $_GPC;
        //数据处理队列名称,不设置代表不使用私有队列，使用公有队列。
        $pipeline = trim($_GPC['pipeline']);

        //通过添加'|saveas'参数，指定处理后的文件保存的bucket和key
        //不指定默认保存在当前空间，bucket为目标空间，后一个参数为转码之后文件名
        $savekey = \Qiniu\base64_urlSafeEncode($this->bucket . ':' . $mediaid . '.mp3');
        //设置转码参数
        $fops = "avthumb/mp3/ab/320k/ar/44100/acodec/libmp3lame";
        $fops = $fops . '|saveas/' . $savekey;
        if (!empty($pipeline)) {  //使用私有队列
            $policy = array(
                'persistentOps' => $fops,
                'persistentPipeline' => $pipeline
            );
        } else {                  //使用公有队列
            $policy = array(
                'persistentOps' => $fops
            );
        }

        //指定上传转码命令
        $uptoken = $this->auth->uploadToken($this->bucket, null, 3600, $policy);
        $key = $mediaid . '.amr'; //七牛云中保存的amr文件名
        $uploadMgr = new UploadManager();

        //上传文件并转码$filePath为本地文件路径
        list($ret, $err) = $uploadMgr->putFile($uptoken, $key, $filePath);
        if ($err !== null) {
            return false;
        } else {
            //此时七牛云中同一段音频文件有amr和MP3两个格式的两个文件同时存在
            $bucketMgr = new BucketManager($this->auth);
            //为节省空间,删除amr格式文件
            $bucketMgr->delete($this->bucket, $key);
            return $this->get_prefix() . '/' .str_replace(".amr" , '.mp3' , $ret['key']) ;
        }
    }
}