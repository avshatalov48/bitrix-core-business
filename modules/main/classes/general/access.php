<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

IncludeModuleLangFile(__FILE__);

class CAccess
{
	const CACHE_DIR = "access_check";

	protected static $arAuthProviders = [];
	protected static $arChecked = [];
	protected $arParams = false;

	public function __construct($arParams=false)
	{
		$this->arParams = $arParams;

		if(empty(static::$arAuthProviders))
		{
			foreach(GetModuleEvents("main", "OnAuthProvidersBuildList", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent);
				if(is_array($res))
				{
					if(!is_array($res[0]))
						$res = array($res);
					foreach($res as $provider)
						static::$arAuthProviders[$provider["ID"]] = $provider;
				}
			}
			sortByColumn(static::$arAuthProviders, "SORT");
		}
	}

	protected static function NeedToRecalculate($provider, $USER_ID)
	{
		global $DB, $CACHE_MANAGER;

		$USER_ID = intval($USER_ID);

		if(!isset(static::$arChecked[$provider][$USER_ID]))
		{
			$cacheId = static::GetCheckCacheId($provider, $USER_ID);

			if (CACHED_b_user_access_check !== false && $CACHE_MANAGER->Read(CACHED_b_user_access_check, $cacheId, static::CACHE_DIR))
			{
				static::$arChecked[$provider][$USER_ID] = $CACHE_MANAGER->Get($cacheId);
			}
			else
			{
				$res = $DB->Query("
					select 'x'
					from b_user_access_check
					where USER_ID = ".$USER_ID."
						and PROVIDER_ID = '".$DB->ForSql($provider)."'
				");

				static::$arChecked[$provider][$USER_ID] = ($res->Fetch()? true : false);

				if (CACHED_b_user_access_check !== false)
				{
					$CACHE_MANAGER->Set($cacheId, static::$arChecked[$provider][$USER_ID]);
				}
			}
		}

		return (static::$arChecked[$provider][$USER_ID]);
	}

	public function UpdateCodes($arParams=false)
	{
		global $USER, $CACHE_MANAGER;

		$USER_ID = 0;
		if(is_array($arParams) && isset($arParams["USER_ID"]))
			$USER_ID = intval($arParams["USER_ID"]);
		elseif(is_object($USER) && $USER->IsAuthorized())
			$USER_ID = intval($USER->GetID());

		if($USER_ID > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();

			foreach(static::$arAuthProviders as $provider_id=>$provider)
			{
				if(is_callable(array($provider["CLASS"], "UpdateCodes")))
				{
					//do we need to recalculate codes for the user?
					if(static::NeedToRecalculate($provider_id, $USER_ID))
					{
						$name = "access.{$provider_id}.{$USER_ID}";

						if($connection->lock($name))
						{
							//remove old codes
							static::DeleteCodes($provider_id, $USER_ID);

							/** @var CGroupAuthProvider $pr For example*/
							$pr = new $provider["CLASS"];

							//call provider to insert access codes
							$pr->UpdateCodes($USER_ID);

							//update cache for checking
							static::UpdateStat($provider_id, $USER_ID);

							$connection->unlock($name);
						}
					}
				}
			}
			$CACHE_MANAGER->Clean(static::GetCodesCacheId($USER_ID), static::CACHE_DIR);
		}
	}

	public static function RecalculateForUser($userId, $provider)
	{
		global $DB, $CACHE_MANAGER;
		$userId = intval($userId);

		$DB->Query("
			INSERT INTO b_user_access_check (USER_ID, PROVIDER_ID)
			SELECT ID, '{$DB->ForSQL($provider)}'
			FROM b_user
			WHERE ID = {$userId}"
		);

		$CACHE_MANAGER->Clean(static::GetCheckCacheId($provider, $userId), static::CACHE_DIR);

		static::$arChecked[$provider][$userId] = true;
	}

	public static function RecalculateForProvider($provider)
	{
		global $DB, $CACHE_MANAGER;

		$DB->Query("
			INSERT INTO b_user_access_check (USER_ID, PROVIDER_ID)
			SELECT USER_ID, PROVIDER_ID
			FROM b_user_access
			WHERE PROVIDER_ID = '{$DB->ForSQL($provider)}'
				AND USER_ID > 0
			GROUP BY USER_ID, PROVIDER_ID 
		");

		$CACHE_MANAGER->CleanDir(static::CACHE_DIR);

		if(is_array(static::$arChecked[$provider]))
		{
			foreach(static::$arChecked[$provider] as $userId => $dummy)
			{
				static::$arChecked[$provider][$userId] = true;
			}
		}
	}

	protected static function GetCheckCacheId($provider, $userId)
	{
		return "access_check_".$provider."_".$userId;
	}

	protected static function GetCodesCacheId($userId)
	{
		return "access_codes".$userId;
	}

	protected static function DeleteCodes($providerId, $userId)
	{
		$userId = (int)$userId;

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$connection->query("
			delete from b_user_access 
			where user_id = {$userId} 
				and provider_id = '{$helper->forSql($providerId)}'
		");
	}

	protected static function UpdateStat($provider, $userId)
	{
		global $DB, $CACHE_MANAGER;
		$userId = intval($userId);

		$DB->Query("
			delete from b_user_access_check 
			where user_id = {$userId} 
				and provider_id = '{$DB->ForSql($provider)}'
		");
		$CACHE_MANAGER->Clean(static::GetCheckCacheId($provider, $userId), static::CACHE_DIR);

		static::$arChecked[$provider][$userId] = false;
	}

	public static function GetUserCodes($USER_ID, $arFilter=array())
	{
		global $DB;

		$access = new CAccess();
		$access->UpdateCodes(array('USER_ID' => $USER_ID));

		$arWhere = array();
		foreach($arFilter as $key=>$val)
		{
			$key = strtoupper($key);
			switch($key)
			{
				case "ACCESS_CODE":
					if(!is_array($val))
						$val = array($val);
					$arIn = array();
					foreach($val as $code)
						if(trim($code) <> '')
							$arIn[] = "'".$DB->ForSQL(trim($code))."'";
					if(!empty($arIn))
						$arWhere[] = "access_code in(".implode(",", $arIn).")";
					break;
				case "PROVIDER_ID":
					$arWhere[] = "provider_id='".$DB->ForSQL($val)."'";
					break;
			}
		}

		$sWhere = '';
		if(!empty($arWhere))
			$sWhere = " and ".implode(" and ", $arWhere);

		return $DB->Query("select * from b_user_access where user_id=".intval($USER_ID).$sWhere);
	}

	public static function GetUserCodesArray($USER_ID, $arFilter=array())
	{
		global $CACHE_MANAGER;
		$USER_ID = intval($USER_ID);

		$useCache = (empty($arFilter) && CACHED_b_user_access_check !== false);

		$cacheId = static::GetCodesCacheId($USER_ID);
		if ($useCache && $CACHE_MANAGER->Read(CACHED_b_user_access_check, $cacheId, static::CACHE_DIR))
		{
			return $CACHE_MANAGER->Get($cacheId);
		}
		else
		{
			$arCodes = array();
			$res = CAccess::GetUserCodes($USER_ID, $arFilter);
			while($arRes = $res->Fetch())
				$arCodes[] = $arRes["ACCESS_CODE"];

			if ($useCache)
				$CACHE_MANAGER->Set($cacheId, $arCodes);

			return $arCodes;
		}
	}

	public function GetFormHtml($arParams=false)
	{
		$arHtml = array();
		foreach(static::$arAuthProviders as $provider)
		{
			$cl = new $provider["CLASS"];
			if(is_callable(array($cl, "GetFormHtml")))
			{
				$res = call_user_func_array(array($cl, "GetFormHtml"), array($this->arParams));
				if($res !== false)
					$arHtml[$provider["ID"]] = array("NAME"=>$provider["NAME"], "HTML"=>$res["HTML"], "SELECTED"=>$res["SELECTED"]);
			}
		}
		return $arHtml;
	}

	public function AjaxRequest($arParams)
	{
		if(array_key_exists($arParams["provider"], static::$arAuthProviders))
		{
			$cl = new static::$arAuthProviders[$arParams["provider"]]["CLASS"];
			if(is_callable(array($cl, "AjaxRequest")))
			{
				CUtil::JSPostUnescape();
				return call_user_func_array(array($cl, "AjaxRequest"), array($this->arParams));
			}
		}
		return false;
	}

	public function GetNames($arCodes, $bSort=false)
	{
		$arResult = array();

		if(!is_array($arCodes) || empty($arCodes))
			return $arResult;

		foreach(static::$arAuthProviders as $provider)
		{

			$cl = new $provider["CLASS"];
			if(is_callable(array($cl, "GetNames")))
			{
				$res = call_user_func_array(array($cl, "GetNames"), array($arCodes));
				if(is_array($res))
				{
					foreach ($res as $codeId => $codeValues)
					{
						$codeValues['provider_id'] = $provider['ID'];
						$arResult[$codeId] = $codeValues;
					}
				}
			}
		}

		if($bSort)
			uasort($arResult, array('CAccess', 'CmpNames'));

		return $arResult;
	}

	public static function CmpNames($a, $b)
	{
		$c = strcmp($a["provider"], $b["provider"]);
		if($c <> 0)
			return $c;

		return strcmp($a["name"], $b["name"]);
	}

	public function GetProviderNames()
	{
		$arResult = array();
		foreach(static::$arAuthProviders as $ID=>$provider)
		{
			$arResult[$ID] = array(
				"name" => (isset($provider["PROVIDER_NAME"])? $provider["PROVIDER_NAME"] : $ID),
				"prefixes" => (isset($provider["PREFIXES"])? $provider["PREFIXES"] : array()),
			);
		}
		return $arResult;
	}

	public static function GetProviders()
	{
		return array(
			array(
				"ID" => "group",
				"NAME" => GetMessage("access_groups"),
				"PROVIDER_NAME" => GetMessage("access_group"),
				"SORT" => 100,
				"CLASS" => "CGroupAuthProvider",
			),
			array(
				"ID" => "user",
				"NAME" => GetMessage("access_users"),
				"PROVIDER_NAME" => GetMessage("access_user"),
				"SORT" => 200,
				"CLASS" => "CUserAuthProvider",
			),
			array(
				"ID" => "other",
				"NAME" => GetMessage("access_other"),
				"PROVIDER_NAME" => "",
				"SORT" => 1000,
				"CLASS" => "COtherAuthProvider",
			),
		);
	}

	public static function OnUserDelete($USER_ID)
	{
		global $DB, $CACHE_MANAGER;
		$USER_ID = intval($USER_ID);

		$DB->Query("delete from b_user_access where user_id=".$USER_ID);
		$DB->Query("delete from b_user_access_check where user_id=".$USER_ID);

		//all providers for one user
		foreach(static::$arChecked as $provider => $dummy)
		{
			unset(static::$arChecked[$provider][$USER_ID]);
			$CACHE_MANAGER->Clean(static::GetCheckCacheId($provider, $USER_ID), static::CACHE_DIR);
		}
		$CACHE_MANAGER->Clean(static::GetCodesCacheId($USER_ID), static::CACHE_DIR);

		return true;
	}

	public static function SaveLastRecentlyUsed($arLRU)
	{
		foreach($arLRU as $provider=>$arRecent)
		{
			if(is_array($arRecent))
			{
				$arLastRecent = CUserOptions::GetOption("access_dialog_recent", $provider, array());

				$arItems = array_keys($arRecent);
				$arItems = array_unique(array_merge($arItems, $arLastRecent));
				$arItems = array_slice($arItems, 0, 20);

				CUserOptions::SetOption("access_dialog_recent", $provider, $arItems);
			}
		}
	}

	public static function GetLastRecentlyUsed($provider)
	{
		$res = CUserOptions::GetOption("access_dialog_recent", $provider, array());
		if(!is_array($res))
			$res = array();
		return $res;
	}
}

AddEventHandler("main", "OnAuthProvidersBuildList", array("CAccess", "GetProviders"));
