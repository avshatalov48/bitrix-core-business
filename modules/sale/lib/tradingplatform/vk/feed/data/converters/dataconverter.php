<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Converters;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class DataConverter
{
	protected $exportId;
	
	abstract public function convert($data);
	
	/**
	 * VK don't understand specialchars-quotes. Change them to the yolochki
	 *
	 * @param $str
	 * @return mixed
	 */
	protected static function convertQuotes($str)
	{
		if(strlen($str) > 0)
		{
//			check quotes
			$str = preg_replace(
				array('/"([^\s].*?[^\s])"/', '/&quot;([^\s].*?[^\s])&quot;/'),
				Loc::getMessage("SALE_VK_PRODUCT_LAQUO") . '$1' . Loc::getMessage("SALE_VK_PRODUCT_RAQUO"), $str
			);
			
//			check single quotes
			$str = preg_replace(
				array("/'([^\s].*?[^\s])'/", "/&apos;([^\s].*?[^\s])&apos;/"),
				Loc::getMessage("SALE_VK_PRODUCT_LAQUO_SINGLE") . '$1' . Loc::getMessage("SALE_VK_PRODUCT_RAQUO_SINGLE"), $str
			);
			
//			check inches
			$str = preg_replace(
				['/(\d+\s*)(")/', '/(\d+\s*)(&quot;)/'],
				'$1'.Loc::getMessage('SALE_VK_INCH_NEW'), $str
			);
		}
		return $str;
	}
	
	protected static function mb_str_pad($string, $padLength, $padString = " ", $padType = STR_PAD_RIGHT)
	{
		if (method_exists("\Bitrix\Main\Text\UtfSafeString", "pad"))
		{
			return \Bitrix\Main\Text\UtfSafeString::pad($string, $padLength, $padString, $padType);
		}
		else
		{
			$newPadLength = \Bitrix\Main\Text\BinaryString::getLength($string) - strlen($string) + $padLength;
			return str_pad($string, $newPadLength, $padString, $padType);
		}
	}
}


