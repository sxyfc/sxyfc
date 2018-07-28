<?php

namespace app\pay\payment\micropay\utils;
use app\common\model\File;
use app\common\model\SitesWechat;
use app\pay\model\PaymentConfig;
use think\Log;

/**
* 	配置账号信息
*/

/**
 * 	配置账号信息
 */

class WxPayConfig
{

    //=======【证书路径设置】=====================================
    /**
     * TODO：设置商户证书路径
     * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
     * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
     * @var path
     */
    const SSLCERT_PATH =  '../cert/apiclient_cert.pem';
    const SSLKEY_PATH = '../cert/apiclient_key.pem';

    //=======【curl代理设置】===================================
    /**
     * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
     * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
     * @var unknown_type
     */
    const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
    const CURL_PROXY_PORT = 0;//8080;

    //=======【上报信息配置】===================================
    /**
     * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
     * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
     * 开启错误上报。
     * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
     * @var int
     */
    const REPORT_LEVENL = 1;


    /**
     * WxPayConfig constructor.
     * @param int $site_id
     * @throws \think\exception\DbException
     */
    public function __construct($site_id = 0 )
    {
        global $_W;
        if(!$site_id){
            $site_id = $_W['site']['id'];
        }
        //只加载主站配置
        $payment_config = PaymentConfig::get(['site_id' => $_W['site']['id'] , 'payment_id' => 1 ]);

        //加载当前网站公众号配置
        $site_wechat = SitesWechat::get(['site_id' => $_W['site']['id']]);

        //todo 加载小程序配置
        $micropay = [
            'app_id' => $payment_config['config']['app_id'] ,
            'app_secret' => $payment_config['config']['app_secret'] ,
            'mchid' => $payment_config['config']['mchid'] ,
            'api_key' => $payment_config['config']['apikey'] ,
            'sub_app_id' => $payment_config['config']['sub_app_id'] ,
            'sub_mch_id' => $payment_config['config']['sub_mchid'] ,
        ];
        $this->is_service_mode =  $payment_config['config']['is_service_mode'];

        $this->SSLCERT_PATH =  to_local_media(File::get(['file_id' => $payment_config['config']['apiclient_cert']]));
        $this->SSLKEY_PATH =    to_local_media(File::get(['file_id' => $payment_config['config']['apiclient_key']]));

        switch ($_W['pay_mode']){
            case "WX_XCX" :
                $this->APPID = $site_wechat['xcx_config']['app_id'];
                $this->MCHID = $micropay['mchid'];
                $this->KEY = $micropay['api_key'];
                break;
            case "WX_GZH":
                $this->APPID = $site_wechat->app_id;
                $this->APPSECRET = $site_wechat->app_secret;

                $this->MCHID = $micropay['mchid'];
                $this->KEY = $micropay['api_key'];
                break;

            case "WX_NATIVE":
                $this->APPID = $site_wechat->app_id;
                $this->APPSECRET = $site_wechat->app_secret;

                $this->MCHID = $micropay['mchid'];
                $this->KEY = $micropay['api_key'];

                $this->SubMchid = $micropay['sub_mch_id'];
                //$this->NOTIFY_URL = nb_url(['r'=>'pay.api.callback' , 'payment_name' => 'micropay'] , 'www.zxw.bz' );
                break;

        }


    }

    public static function get_config(){
        global $_W;
        //只加载主站配置
        $site_wechat = SitesWechat::get(['site_id' => $_W['site']['id']]);
        $payment_config = PaymentConfig::get(['site_id' => $_W['site']['id'] , 'payment_id' => 1 ]);
        $config = [
            'app_id' => $site_wechat['app_id'] ?  $site_wechat['app_id'] : $payment_config['config']['app_id'] ,
            'app_secret' => $site_wechat['app_secret'] ,
            'mchid' => $payment_config['config']['mchid'] ,
            'api_key' => $payment_config['config']['apikey']
        ];
        $config['site_wechat'] = $site_wechat;
        //加载当前网站公众号配置
        $config['SSLCERT_PATH'] =  to_local_media(File::get(['file_id' => $payment_config['config']['apiclient_cert']]));
        $config['SSLKEY_PATH'] =    to_local_media(File::get(['file_id' => $payment_config['config']['apiclient_key']]));
        return $config;
    }

    public static function get_mini_config($app_id = 0){
        global $_W;
        if($app_id){
            $config['small_app'] = $_W['small_app'] = set_model('sites_smallapp')->where(['app_id' => $app_id ])->find();
        }else{
            $config['small_app'] = $_W['small_app'];
        }
        //只加载主站配置
        $payment_config = PaymentConfig::get(['site_id' => $_W['site']['id'] , 'payment_id' => 1 ]);
        $config = [
            'app_id' => $_W['small_app']['app_id'] ,
            'app_secret' => $_W['small_app']['app_secret'] ,
            'mchid' => $payment_config['config']['mchid'] ,
            'api_key' => $payment_config['config']['apikey']
        ];
        //加载当前网站公众号配置$_W['small_app']
        $config['SSLCERT_PATH'] =  to_local_media(File::get(['file_id' => $payment_config['config']['apiclient_cert']]));
        $config['SSLKEY_PATH'] =    to_local_media(File::get(['file_id' => $payment_config['config']['apiclient_key']]));
        return $config;
    }
}
