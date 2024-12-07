<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */

class CAdminTabEngine
{
	var $name;
	var $bInited = false;
	var $arEngines = array();
	var $arArgs = array();
	var $bVarsFromForm = false;

	public function __construct($name, $arArgs = array())
	{
		$this->bInited = false;
		$this->name = $name;
		$this->arEngines = array();
		$this->arArgs = $arArgs;

		foreach (GetModuleEvents("main", $this->name, true) as $arEvent)
		{
			$res = ExecuteModuleEventEx($arEvent, array($this->arArgs));
			if (is_array($res))
			{
				$res["TABSET"] = preg_replace('/[^A-Za-z0-9_-]/', '', $res["TABSET"]);
				$this->arEngines[$res["TABSET"]] = $res;
			}
			$this->bInited = true;
		}
	}

	function SetErrorState($bVarsFromForm = false)
	{
		$this->bVarsFromForm = $bVarsFromForm;
	}

	function SetArgs($arArgs = array())
	{
		$this->arArgs = $arArgs;
	}

	function Check()
	{
		if (!$this->bInited)
			return true;

		$result = true;

		foreach ($this->arEngines as $value)
		{
			if (array_key_exists("Check", $value))
			{
				$resultTmp = call_user_func_array($value["Check"], array($this->arArgs));
				if ($result && !$resultTmp)
					$result = false;
			}
		}

		return $result;
	}

	function Action()
	{
		if (!$this->bInited)
			return true;

		$result = true;

		foreach ($this->arEngines as $value)
		{
			if (array_key_exists("Action", $value))
			{
				$resultTmp = call_user_func_array($value["Action"], array($this->arArgs));
				if ($result && !$resultTmp)
					$result = false;
			}
		}

		return $result;
	}

	function GetTabs()
	{
		if (!$this->bInited)
			return false;

		$arTabs = array();
		foreach ($this->arEngines as $key => $value)
		{
			if (array_key_exists("GetTabs", $value))
			{
				$arTabsTmp = call_user_func_array($value["GetTabs"], array($this->arArgs));
				if (is_array($arTabsTmp))
				{
					foreach ($arTabsTmp as $key1 => $value1)
					{
						$arTabsTmp[$key1]["DIV"] = $key."_".$arTabsTmp[$key1]["DIV"];
					}

					$arTabs = array_merge($arTabs, $arTabsTmp);
				}
			}
		}

		return $arTabs;
	}

	function ShowTab($divName)
	{
		if (!$this->bInited)
			return false;

		foreach ($this->arEngines as $key => $value)
		{
			if (str_starts_with($divName, $key . "_"))
			{
				if (array_key_exists("ShowTab", $value))
					call_user_func_array($value["ShowTab"], array(mb_substr($divName, mb_strlen($key."_")), $this->arArgs, $this->bVarsFromForm));
			}
		}
		return null;
	}
}
