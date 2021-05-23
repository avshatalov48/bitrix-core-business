<?php

/**
 * @deprecated
 */
class CAllOption
{
	public static function err_mess()
	{
		return "<br>Class: CAllOption<br>File: ".__FILE__;
	}

	public static function GetOptionString($module_id, $name, $def="", $site=false, $bExactSite=false)
	{
		$v = null;

		try
		{
			if ($bExactSite)
			{
				$v = \Bitrix\Main\Config\Option::getRealValue($module_id, $name, $site);
				return $v === null ? false : $v;
			}

			$v = \Bitrix\Main\Config\Option::get($module_id, $name, $def, $site);
		}
		catch (\Bitrix\Main\ArgumentNullException $e)
		{

		}

		return $v;
	}

	public static function SetOptionString($module_id, $name, $value="", $desc=false, $site="")
	{
		\Bitrix\Main\Config\Option::set($module_id, $name, $value, $site);
		return true;
	}

	public static function RemoveOption($module_id, $name="", $site=false)
	{
		$filter = array();
		if ($name <> '')
			$filter["name"] = $name;
		if ($site <> '')
			$filter["site_id"] = $site;
		\Bitrix\Main\Config\Option::delete($module_id, $filter);
	}

	public static function GetOptionInt($module_id, $name, $def="", $site=false)
	{
		return intval(COption::GetOptionString($module_id, $name, $def, $site));
	}

	public static function SetOptionInt($module_id, $name, $value="", $desc="", $site="")
	{
		return COption::SetOptionString($module_id, $name, intval($value), $desc, $site);
	}
}

class COption extends CAllOption
{
}
