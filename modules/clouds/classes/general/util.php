<?php
class CCloudUtil
{
	/**
	 * @param string $str
	 * @param string $charset
	 * @return string
	*/
	public static function URLEncode($str, $charset, $file_name = false)
	{
		global $APPLICATION;
		$strEncodedURL = '';
		if ($file_name)
			$arUrlComponents = preg_split("#(://|/)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
		else
			$arUrlComponents = preg_split("#(://|/|\\?|=|&)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach($arUrlComponents as $i => $part_of_url)
		{
			if((intval($i) % 2) == 1)
				$strEncodedURL .= (string)$part_of_url;
			else
				$strEncodedURL .= urlencode($APPLICATION->ConvertCharset(urldecode((string)$part_of_url), LANG_CHARSET, $charset));
		}
		return $strEncodedURL;
	}
	/**
	 * @param string $str
	 * @return \Bitrix\Main\Type\DateTime
	*/
	public static function gmtTimeToDateTime($str)
	{
		$timestamp = strtotime($str."Z");
		$datetime = \Bitrix\Main\Type\DateTime::createFromTimestamp($timestamp);
		return $datetime;
	}
}
?>