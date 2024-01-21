<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Type\DateTime;

IncludeModuleLangFile(__FILE__);

class CAccess
{
	const CHECK_CACHE_DIR = "access_check";
	const CACHE_DIR = "access_codes";

	protected static $arAuthProviders = [];
	protected static $arChecked = [];
	protected static $userCodes = [];
	protected $arParams = false;

	public function __construct($arParams = false)
	{
		$this->arParams = $arParams;

		if (empty(static::$arAuthProviders))
		{
			foreach (GetModuleEvents("main", "OnAuthProvidersBuildList", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent);
				if (is_array($res))
				{
					if (!is_array($res[0]))
					{
						$res = [$res];
					}
					foreach ($res as $provider)
					{
						static::$arAuthProviders[$provider["ID"]] = $provider;
					}
				}
			}
			sortByColumn(static::$arAuthProviders, "SORT");
		}
	}

	protected static function NeedToRecalculate($provider, $USER_ID, DateTime $now)
	{
		global $DB, $CACHE_MANAGER;

		$USER_ID = intval($USER_ID);

		if (!isset(static::$arChecked[$provider][$USER_ID]))
		{
			$cacheId = static::GetCheckCacheId($provider, $USER_ID);

			if (CACHED_b_user_access_check !== false && $CACHE_MANAGER->Read(CACHED_b_user_access_check, $cacheId, static::CHECK_CACHE_DIR))
			{
				static::$arChecked[$provider][$USER_ID] = $CACHE_MANAGER->Get($cacheId);
			}
			else
			{
				$res = $DB->Query("
					select DATE_CHECK
					from b_user_access_check
					where USER_ID = " . $USER_ID . "
						and PROVIDER_ID = '" . $DB->ForSql($provider) . "'
				");

				static::$arChecked[$provider][$USER_ID] = [];
				while ($check = $res->Fetch())
				{
					static::$arChecked[$provider][$USER_ID][] = $check['DATE_CHECK'];
				}

				if (CACHED_b_user_access_check !== false)
				{
					$CACHE_MANAGER->Set($cacheId, static::$arChecked[$provider][$USER_ID]);
				}
			}
		}

		$helper = Main\Application::getConnection()->getSqlHelper();

		foreach (static::$arChecked[$provider][$USER_ID] as $dateCheck)
		{
			$date = $helper->convertFromDbDateTime($dateCheck);
			if ($date && $date->getTimestamp() <= $now->getTimestamp())
			{
				return true;
			}
		}

		return false;
	}

	public function UpdateCodes($arParams = false)
	{
		global $USER;

		$USER_ID = 0;
		if (is_array($arParams) && isset($arParams["USER_ID"]))
		{
			$USER_ID = intval($arParams["USER_ID"]);
		}
		elseif (is_object($USER) && $USER->IsAuthorized())
		{
			$USER_ID = intval($USER->GetID());
		}

		if ($USER_ID > 0)
		{
			$connection = Main\Application::getConnection();
			$clearCache = false;
			$now = new DateTime();

			foreach (static::$arAuthProviders as $providerId => $providerDescription)
			{
				/** @var CGroupAuthProvider $provider For example */
				$provider = new $providerDescription["CLASS"];

				if (is_callable([$provider, "UpdateCodes"]))
				{
					//do we need to recalculate codes for the user?
					if (static::NeedToRecalculate($providerId, $USER_ID, $now))
					{
						$lockName = "update_codes.{$providerId}.{$USER_ID}";

						// we don't want to do the same job twice
						if ($connection->lock($lockName))
						{
							$connection->startTransaction();

							//should clear codes cache for the user
							$clearCache = true;

							//remove old codes
							static::DeleteCodes($providerId, $USER_ID);

							//call provider to insert access codes
							$provider->UpdateCodes($USER_ID);

							//update cache for checking
							static::UpdateStat($providerId, $USER_ID, $now);

							$connection->commitTransaction();
							$connection->unlock($lockName);
						}
					}
				}
			}

			if ($clearCache)
			{
				static::ClearCache($USER_ID);
			}
		}
	}

	/**
	 * @param int $userId
	 * @param string $provider
	 * @param string $code
	 */
	public static function AddCode($userId, $provider, $code)
	{
		$userId = (int)$userId;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$sql = $helper->getInsertIgnore(
			'b_user_access',
			'(USER_ID, PROVIDER_ID, ACCESS_CODE)',
			"VALUES ({$userId}, '{$helper->forSql($provider)}', '{$helper->forSql($code)}')"
		);

		$connection->query($sql);

		static::ClearCache($userId);
	}

	/**
	 * @param int $userId
	 * @param string $provider
	 * @param string $code
	 */
	public static function RemoveCode($userId, $provider, $code)
	{
		$userId = (int)$userId;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$connection->query("
			DELETE FROM b_user_access
			WHERE USER_ID = {$userId}
				AND PROVIDER_ID = '{$helper->forSql($provider)}'
				AND ACCESS_CODE = '{$helper->forSql($code)}'
		");

		static::ClearCache($userId);
	}

	/**
	 * @param int $userId User ID.
	 */
	public static function ClearCache($userId)
	{
		global $CACHE_MANAGER;

		$CACHE_MANAGER->Clean(static::GetCodesCacheId($userId), static::CACHE_DIR);
		unset(static::$userCodes[$userId]);
	}

	public static function RecalculateForUser($userId, $provider, DateTime $dateCheck = null)
	{
		global $DB;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$userId = intval($userId);

		if ($dateCheck === null)
		{
			$dateCheck = new DateTime('1974-12-07', 'Y-m-d'); // somewhen in the past
		}

		$sql = $helper->getInsertIgnore('b_user_access_check', '(USER_ID, PROVIDER_ID, DATE_CHECK)', "
			SELECT ID, '{$DB->ForSQL($provider)}', {$helper->convertToDbDateTime($dateCheck)}  
			FROM b_user
			WHERE ID = {$userId}"
		);
		$connection->query($sql);

		static::ClearCheckCache($provider, $userId);
	}

	public static function RecalculateForProvider($provider)
	{
		global $CACHE_MANAGER;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$dateCheck = new DateTime('1974-12-07', 'Y-m-d'); // somewhen in the past

		$sql = $helper->getInsertIgnore('b_user_access_check', '(USER_ID, PROVIDER_ID, DATE_CHECK)', "
			SELECT USER_ID, PROVIDER_ID, {$helper->convertToDbDateTime($dateCheck)}
			FROM b_user_access
			WHERE PROVIDER_ID = '" . $helper->forSQL($provider) . "'
			AND USER_ID > 0
			GROUP BY USER_ID, PROVIDER_ID 
		");
		$connection->query($sql);

		$CACHE_MANAGER->CleanDir(static::CHECK_CACHE_DIR);
		unset(static::$arChecked[$provider]);
	}

	protected static function GetCheckCacheId($provider, $userId)
	{
		return "access_check_v2_" . $provider . "_" . $userId;
	}

	protected static function GetCodesCacheId($userId)
	{
		return "access_codes" . $userId;
	}

	protected static function DeleteCodes($providerId, $userId)
	{
		$userId = (int)$userId;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$connection->query("
			delete from b_user_access 
			where user_id = {$userId} 
				and provider_id = '{$helper->forSql($providerId)}'
		");
	}

	protected static function UpdateStat($provider, $userId, DateTime $now)
	{
		global $DB;

		$userId = intval($userId);

		$helper = Main\Application::getConnection()->getSqlHelper();

		$DB->Query("
			delete from b_user_access_check 
			where user_id = {$userId} 
				and provider_id = '{$DB->ForSql($provider)}'
				and date_check <= {$helper->convertToDbDateTime($now)}
		");

		static::ClearCheckCache($provider, $userId);
	}

	protected static function ClearCheckCache($provider, $userId)
	{
		global $CACHE_MANAGER;

		$CACHE_MANAGER->Clean(static::GetCheckCacheId($provider, $userId), static::CHECK_CACHE_DIR);
		unset(static::$arChecked[$provider][$userId]);
	}

	public static function GetUserCodes($USER_ID, $arFilter = [], bool $updateCodes = true)
	{
		global $DB;

		if ($updateCodes)
		{
			$access = new CAccess();
			$access->UpdateCodes(['USER_ID' => $USER_ID]);
		}

		$arWhere = [];
		foreach ($arFilter as $key => $val)
		{
			$key = strtoupper($key);
			switch ($key)
			{
				case "ACCESS_CODE":
					if (!is_array($val))
					{
						$val = [$val];
					}
					$arIn = [];
					foreach ($val as $code)
					{
						if (trim($code) <> '')
						{
							$arIn[] = "'" . $DB->ForSQL(trim($code)) . "'";
						}
					}
					if (!empty($arIn))
					{
						$arWhere[] = "access_code in(" . implode(",", $arIn) . ")";
					}
					break;
				case "PROVIDER_ID":
					$arWhere[] = "provider_id='" . $DB->ForSQL($val) . "'";
					break;
			}
		}

		$sWhere = '';
		if (!empty($arWhere))
		{
			$sWhere = " and " . implode(" and ", $arWhere);
		}

		return $DB->Query("select * from b_user_access where user_id=" . intval($USER_ID) . $sWhere);
	}

	public static function GetUserCodesArray($USER_ID, $arFilter = [])
	{
		global $CACHE_MANAGER;

		$USER_ID = intval($USER_ID);

		$access = new CAccess();
		$access->UpdateCodes(['USER_ID' => $USER_ID]);

		if (empty($arFilter) && isset(static::$userCodes[$USER_ID]))
		{
			return static::$userCodes[$USER_ID];
		}

		$useCache = (empty($arFilter) && CACHED_b_user_access_check !== false);
		$cacheId = static::GetCodesCacheId($USER_ID);

		if ($useCache && $CACHE_MANAGER->Read(CACHED_b_user_access_check, $cacheId, static::CACHE_DIR))
		{
			$arCodes = $CACHE_MANAGER->Get($cacheId);
		}
		else
		{
			$arCodes = [];
			$res = CAccess::GetUserCodes($USER_ID, $arFilter, false);
			while ($arRes = $res->Fetch())
			{
				$arCodes[] = $arRes["ACCESS_CODE"];
			}

			if ($useCache)
			{
				$CACHE_MANAGER->Set($cacheId, $arCodes);
			}
		}

		if (empty($arFilter))
		{
			static::$userCodes[$USER_ID] = $arCodes;
		}

		return $arCodes;
	}

	public function GetFormHtml()
	{
		$arHtml = [];
		foreach (static::$arAuthProviders as $provider)
		{
			$cl = new $provider["CLASS"];
			if (is_callable([$cl, "GetFormHtml"]))
			{
				$res = call_user_func_array([$cl, "GetFormHtml"], [$this->arParams]);
				if ($res !== false)
				{
					$arHtml[$provider["ID"]] = [
						"NAME" => $provider["NAME"],
						"HTML" => $res["HTML"],
						"SELECTED" => $res["SELECTED"] ?? false,
					];
				}
			}
		}
		return $arHtml;
	}

	public function AjaxRequest($arParams)
	{
		if (isset(static::$arAuthProviders[$arParams["provider"]]))
		{
			$cl = new static::$arAuthProviders[$arParams["provider"]]["CLASS"];
			if (is_callable([$cl, "AjaxRequest"]))
			{
				CUtil::JSPostUnescape();
				return call_user_func_array([$cl, "AjaxRequest"], [$this->arParams]);
			}
		}
		return false;
	}

	public function GetNames($arCodes, $bSort = false)
	{
		$arResult = [];

		if (!is_array($arCodes) || empty($arCodes))
		{
			return $arResult;
		}

		foreach (static::$arAuthProviders as $provider)
		{
			$cl = new $provider["CLASS"];
			if (is_callable([$cl, "GetNames"]))
			{
				$res = call_user_func_array([$cl, "GetNames"], [$arCodes]);
				if (is_array($res))
				{
					foreach ($res as $codeId => $codeValues)
					{
						$codeValues['provider_id'] = $provider['ID'];
						$arResult[$codeId] = $codeValues;
					}
				}
			}
		}

		if ($bSort)
		{
			uasort($arResult, ['CAccess', 'CmpNames']);
		}

		return $arResult;
	}

	public static function CmpNames($a, $b)
	{
		$c = strcmp($a["provider"], $b["provider"]);
		if ($c <> 0)
		{
			return $c;
		}

		return strcmp($a["name"], $b["name"]);
	}

	public function GetProviderNames()
	{
		$arResult = [];
		foreach (static::$arAuthProviders as $ID => $provider)
		{
			$arResult[$ID] = [
				"name" => ($provider["PROVIDER_NAME"] ?? $ID),
				"prefixes" => ($provider["PREFIXES"] ?? []),
			];
		}
		return $arResult;
	}

	public static function GetProviders()
	{
		return [
			[
				"ID" => "group",
				"NAME" => GetMessage("access_groups"),
				"PROVIDER_NAME" => GetMessage("access_group"),
				"SORT" => 100,
				"CLASS" => "CGroupAuthProvider",
			],
			[
				"ID" => "user",
				"NAME" => GetMessage("access_users"),
				"PROVIDER_NAME" => GetMessage("access_user"),
				"SORT" => 200,
				"CLASS" => "CUserAuthProvider",
			],
			[
				"ID" => "other",
				"NAME" => GetMessage("access_other"),
				"PROVIDER_NAME" => "",
				"SORT" => 1000,
				"CLASS" => "COtherAuthProvider",
			],
		];
	}

	public static function OnUserDelete($USER_ID)
	{
		global $DB;
		$USER_ID = intval($USER_ID);

		$DB->Query("delete from b_user_access where user_id=" . $USER_ID);
		$DB->Query("delete from b_user_access_check where user_id=" . $USER_ID);

		//all providers for one user
		foreach (static::$arChecked as $provider => $dummy)
		{
			static::ClearCheckCache($provider, $USER_ID);
		}

		static::ClearCache($USER_ID);

		return true;
	}

	public static function SaveLastRecentlyUsed($arLRU)
	{
		foreach ($arLRU as $provider => $arRecent)
		{
			if (is_array($arRecent))
			{
				$arLastRecent = CUserOptions::GetOption("access_dialog_recent", $provider, []);

				$arItems = array_keys($arRecent);
				$arItems = array_unique(array_merge($arItems, $arLastRecent));
				$arItems = array_slice($arItems, 0, 20);

				CUserOptions::SetOption("access_dialog_recent", $provider, $arItems);
			}
		}
	}

	public static function GetLastRecentlyUsed($provider)
	{
		$res = CUserOptions::GetOption("access_dialog_recent", $provider, []);
		if (!is_array($res))
		{
			$res = [];
		}
		return $res;
	}
}

AddEventHandler("main", "OnAuthProvidersBuildList", ["CAccess", "GetProviders"]);
