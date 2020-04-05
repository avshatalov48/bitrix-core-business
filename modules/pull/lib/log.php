<?php
namespace Bitrix\Pull;

class Log
{
	public static function isEnabled()
	{
		return \Bitrix\Main\Config\Option::get("pull", "debug", false) != false;
	}

	public static function write($data, $title = '')
	{
		if (!self::isEnabled())
			return false;

		$log = "\n------------------------\n";
		$log .= date("Y.m.d G:i:s")."\n";
		$log .= (strlen($title) > 0 ? $title : 'DEBUG')."\n";
		$log .= print_r($data, 1);
		$log .= "\n------------------------\n";

		if (function_exists('BXSiteLog'))
		{
			BXSiteLog("pull.log", $log);
		}
		else
		{
			\Bitrix\Main\IO\File::putFileContents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull.log", $log, \Bitrix\Main\IO\File::APPEND);
		}

		return true;
	}
}