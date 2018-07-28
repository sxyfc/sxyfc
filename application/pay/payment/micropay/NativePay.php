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
namespace app\pay\payment\micropay;

use app\pay\payment\micropay\utils\WxPayApi;
use app\pay\payment\micropay\utils\WxPayBizPayUrl;

/**
 * 
 * 刷卡支付实现类
 * @author widyhu
 *
 */
class NativePay
{
	/**
	 * 
	 * 生成扫描支付URL,模式一
	 * @param BizPayUrlInput $bizUrlInfo
	 */
	public function GetPrePayUrl($productId)
	{
		$biz = new WxPayBizPayUrl();
		$biz->SetProduct_id($productId);
		$values = WxpayApi::bizpayurl($biz);
		$url = "weixin://wxpay/bizpayurl?" . $this->ToUrlParams($values);
		return $url;
	}
	
	/**
	 * 
	 * 参数数组转换为url参数
	 * @param array $urlObj
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			$buff .= $k . "=" . $v . "&";
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 
	 * 生成直接支付url，支付url有效期为2小时,模式二
	 * @param UnifiedOrderInput $input
	 */
	public function GetPayUrl($input)
	{
		if($input->GetTrade_type() == "NATIVE")
		{
			$result = WxPayApi::unifiedOrder($input);
			return $result;
		}
	}
}