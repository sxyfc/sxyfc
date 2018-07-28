<?php

namespace app\common\util\pkcs7;

use think\Exception;
use think\Log;


/**
 * Prpcrypt class
 *
 *
 */
class Prpcrypt
{
    public $sessionKey;
    public $appid;

    function __construct($appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }

    public function decrypt($aesCipher, $aesIV = '', &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey = base64_decode($this->sessionKey);


        if (strlen($aesIV) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV = base64_decode($aesIV);

        $aesCipher = base64_decode($aesCipher);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result;
        return ErrorCode::$OK;
    }


    public function decrypt_app_($aesCipher, $aesIV = '', &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey = base64_decode($this->sessionKey);

        if (strlen($aesIV) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV = base64_decode($aesIV);

        $aesCipher = base64_decode($aesCipher);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result;
        return ErrorCode::$OK;
    }

    public function decrypt_app( $encryptedData, $iv, &$data )
    {
        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey=base64_decode($this->sessionKey);


        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            Log::write("--------------" , true);
            Log::write($dataObj , true);
            Log::write("--------------"  , true);
            return ErrorCode::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->appid )
        {
            Log::write("+++++++++++++++++++++++"  , true);
            Log::write($dataObj , true);
            Log::write("+++++++++++++++++++++++"  , true);
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result;
        return ErrorCode::$OK;
    }

    public  static  function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}