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
	var $bInited = False;
	var $arEngines = array();
	var $arArgs = array();
	var $bVarsFromForm = False;

	public function __construct($name, $arArgs = array())
	{
		$this->bInited = False;
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
			$this->bInited = True;
		}
	}

	function SetErrorState($bVarsFromForm = False)
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
			return True;

		$result = True;

		foreach ($this->arEngines as $value)
		{
			if (array_key_exists("Check", $value))
			{
				$resultTmp = call_user_func_array($value["Check"], array($this->arArgs));
				if ($result && !$resultTmp)
					$result = False;
			}
		}

		return $result;
	}

	function Action()
	{
		if (!$this->bInited)
			return True;

		$result = True;

		foreach ($this->arEngines as $value)
		{
			if (array_key_exists("Action", $value))
			{
				$resultTmp = call_user_func_array($value["Action"], array($this->arArgs));
				if ($result && !$resultTmp)
					$result = False;
			}
		}

		return $result;
	}

	function GetTabs()
	{
		if (!$this->bInited)
			return False;

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
			return False;

		foreach ($this->arEngines as $key => $value)
		{
			if (mb_substr($divName, 0, mb_strlen($key."_")) == $key."_")
			{
				if (array_key_exists("ShowTab", $value))
					call_user_func_array($value["ShowTab"], array(mb_substr($divName, mb_strlen($key."_")), $this->arArgs, $this->bVarsFromForm));
			}
		}
		return null;
	}
}
