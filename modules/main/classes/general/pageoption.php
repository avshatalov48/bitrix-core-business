<?php

/**
 * @deprecated
 */
class CAllPageOption
{
	protected  static $MAIN_PAGE_OPTIONS = [];

	public static function GetOptionString($module_id, $name, $def="", $site=false)
	{
		if($site===false)
			$site = SITE_ID;

		if(isset(static::$MAIN_PAGE_OPTIONS[$site][$module_id][$name]))
			return static::$MAIN_PAGE_OPTIONS[$site][$module_id][$name];
		elseif(isset(static::$MAIN_PAGE_OPTIONS["-"][$module_id][$name]))
			return static::$MAIN_PAGE_OPTIONS["-"][$module_id][$name];
		return $def;
	}

	public static function SetOptionString($module_id, $name, $value="", $desc=false, $site="")
	{
		if($site===false)
			$site = SITE_ID;
		if($site == '')
			$site = "-";

		static::$MAIN_PAGE_OPTIONS[$site][$module_id][$name] = $value;
		return true;
	}

	public static function RemoveOption($module_id, $name="", $site=false)
	{
		if ($site === false)
		{
			foreach (static::$MAIN_PAGE_OPTIONS as $site => $temp)
			{
				if ($name == "")
					unset(static::$MAIN_PAGE_OPTIONS[$site][$module_id]);
				else
					unset(static::$MAIN_PAGE_OPTIONS[$site][$module_id][$name]);
			}
		}
		else
		{
			if ($name == "")
				unset(static::$MAIN_PAGE_OPTIONS[$site][$module_id]);
			else
				unset(static::$MAIN_PAGE_OPTIONS[$site][$module_id][$name]);
		}
	}

	public static function GetOptionInt($module_id, $name, $def="", $site=false)
	{
		return static::GetOptionString($module_id, $name, $def, $site);
	}

	public static function SetOptionInt($module_id, $name, $value="", $desc="", $site="")
	{
		return static::SetOptionString($module_id, $name, intval($value), $desc, $site);
	}
}

class CPageOption extends CAllPageOption
{
}
