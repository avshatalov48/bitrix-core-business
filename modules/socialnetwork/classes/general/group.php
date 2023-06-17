<?php

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\Helper\Workgroup;
use Bitrix\Socialnetwork\Helper\Path;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit\ScrumLimit;
use Bitrix\Tasks\Control\Tag;
use Bitrix\Socialnetwork\Internals\EventService;

class CAllSocNetGroup
{
	protected static $staticCache = array();

	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0): bool
	{
		global $DB, $APPLICATION, $USER_FIELD_MANAGER, $arSocNetAllowedInitiatePerms, $arSocNetAllowedSpamPerms;

		if ($ACTION !== "ADD" && (int)$ID <= 0)
		{
			$APPLICATION->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if(
			($ID === 0 && !is_set($arFields, "SITE_ID"))
			||
			(
				is_set($arFields, "SITE_ID")
				&& (
					(is_array($arFields["SITE_ID"]) && count($arFields["SITE_ID"]) <= 0)
					||
					(!is_array($arFields["SITE_ID"]) && $arFields["SITE_ID"] == '')
				)
			)
		)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GG_EMPTY_SITE_ID"), "EMPTY_SITE_ID");
			return false;
		}
		elseif(is_set($arFields, "SITE_ID"))
		{
			if(!is_array($arFields["SITE_ID"]))
				$arFields["SITE_ID"] = array($arFields["SITE_ID"]);

			foreach($arFields["SITE_ID"] as $v)
			{
				$r = CSite::GetByID($v);
				if(!$r->Fetch())
				{
					$APPLICATION->ThrowException(str_replace("#ID#", $v, GetMessage("SONET_GG_ERROR_NO_SITE")), "ERROR_NO_SITE");
					return false;
				}
			}
		}

		if ((is_set($arFields, "NAME") || $ACTION === "ADD") && ($arFields["NAME"] ?? '') === '')
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_NAME"), "EMPTY_NAME");
			return false;
		}

		if (is_set($arFields, "DATE_CREATE") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_DATE_CREATE"), "EMPTY_DATE_CREATE");
			return false;
		}

		if (is_set($arFields, "DATE_UPDATE") && (!$DB->IsDate($arFields["DATE_UPDATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_DATE_UPDATE"), "EMPTY_DATE_UPDATE");
			return false;
		}

		if (is_set($arFields, "DATE_ACTIVITY") && (!$DB->IsDate($arFields["DATE_ACTIVITY"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_DATE_ACTIVITY"), "EMPTY_DATE_ACTIVITY");
			return false;
		}

		if ((is_set($arFields, "OWNER_ID") || $ACTION === "ADD") && (int)$arFields["OWNER_ID"] <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_OWNER_ID"), "EMPTY_OWNER_ID");
			return false;
		}

		if (is_set($arFields, "OWNER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["OWNER_ID"]);
			if (!$dbResult->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GB_ERROR_NO_OWNER_ID"), "ERROR_NO_OWNER_ID");
				return false;
			}
		}

		if ((is_set($arFields, "SUBJECT_ID") || $ACTION === "ADD") && (int)$arFields["SUBJECT_ID"] <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GB_EMPTY_SUBJECT_ID"), "EMPTY_SUBJECT_ID");
			return false;
		}

		if (is_set($arFields, "SUBJECT_ID"))
		{
			$arResult = CSocNetGroupSubject::GetByID($arFields["SUBJECT_ID"]);
			if ($arResult == false)
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GB_ERROR_NO_SUBJECT_ID"), "ERROR_NO_SUBJECT_ID");
				return false;
			}
		}

		if ((is_set($arFields, "ACTIVE") || $ACTION === "ADD") && $arFields["ACTIVE"] !== "Y" && $arFields["ACTIVE"] !== "N")
		{
			$arFields["ACTIVE"] = "Y";
		}

		if (
			(is_set($arFields, "VISIBLE") || $ACTION === "ADD")
			&& ($arFields["VISIBLE"] ?? null) !== "Y"
			&& ($arFields["VISIBLE"] ?? null) !== "N"
		)
		{
			$arFields["VISIBLE"] = "Y";
		}

		if (
			(is_set($arFields, "OPENED") || $ACTION === "ADD")
			&& ($arFields["OPENED"] ?? null) !== "Y"
			&& ($arFields["OPENED"] ?? null) !== "N"
		)
		{
			$arFields["OPENED"] = "N";
		}

		if (
			(is_set($arFields, "CLOSED") || $ACTION=="ADD")
			&& ($arFields["CLOSED"] ?? null) != "Y"
			&& ($arFields["CLOSED"] ?? null) !== "N"
		)
		{
			$arFields["CLOSED"] = "N";
		}

		if ((is_set($arFields, "INITIATE_PERMS") || $ACTION === "ADD") && $arFields["INITIATE_PERMS"] == '')
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_EMPTY_INITIATE_PERMS"), "EMPTY_INITIATE_PERMS");
			return false;
		}

		if (is_set($arFields, "INITIATE_PERMS") && !in_array($arFields["INITIATE_PERMS"], $arSocNetAllowedInitiatePerms))
		{
			$APPLICATION->ThrowException(str_replace("#ID#", $arFields["INITIATE_PERMS"], GetMessage("SONET_UG_ERROR_NO_INITIATE_PERMS")), "ERROR_NO_INITIATE_PERMS");
			return false;
		}

		if ((is_set($arFields, "SPAM_PERMS") || $ACTION === "ADD") && $arFields["SPAM_PERMS"] == '')
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_EMPTY_SPAM_PERMS"), "EMPTY_SPAM_PERMS");
			return false;
		}

		if (is_set($arFields, "SPAM_PERMS") && !in_array($arFields["SPAM_PERMS"], $arSocNetAllowedSpamPerms))
		{
			$APPLICATION->ThrowException(str_replace("#ID#", $arFields["SPAM_PERMS"], GetMessage("SONET_UG_ERROR_NO_SPAM_PERMS")), "ERROR_NO_SPAM_PERMS");
			return false;
		}

		if (
			is_set($arFields, "IMAGE_ID")
			&& is_array($arFields["IMAGE_ID"])
			&& ($arFields["IMAGE_ID"]["name"] ?? '') == ''
			&& ($arFields["IMAGE_ID"]["del"] == '' || $arFields["IMAGE_ID"]["del"] !== "Y")
		)
		{
			unset($arFields["IMAGE_ID"]);
		}

		if (is_set($arFields, "IMAGE_ID"))
		{
			$arResult = CFile::CheckImageFile($arFields["IMAGE_ID"]);
			if ($arResult <> '')
			{
				$APPLICATION->ThrowException(GetMessage("SONET_GP_ERROR_IMAGE_ID").": ".$arResult, "ERROR_IMAGE_ID");
				return false;
			}
		}

		if (
			is_set($arFields, 'AVATAR_TYPE')
			&& !array_key_exists($arFields['AVATAR_TYPE'], Workgroup::getAvatarTypes())
		)
		{
			unset($arFields['AVATAR_TYPE']);
		}

		if (!$USER_FIELD_MANAGER->CheckFields("SONET_GROUP", $ID, $arFields))
		{
			return false;
		}

		if (!empty($arFields['NAME']))
		{
			$arFields['NAME'] = Emoji::encode($arFields['NAME']);
		}

		if (!empty($arFields['DESCRIPTION']))
		{
			$arFields['DESCRIPTION'] = Emoji::encode($arFields['DESCRIPTION']);
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER, $USER_FIELD_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
		{
			return false;
		}

		$ID = intval($ID);

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetGroupDelete");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				return false;
			}
		}

		$arGroup = CSocNetGroup::GetByID($ID);
		if (!$arGroup)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_NO_GROUP"), "ERROR_NO_GROUP");
			return false;
		}

		$DB->StartTransaction();

		$events = GetModuleEvents("socialnetwork", "OnSocNetGroupDelete");
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array($ID));
		}

		EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_DELETE, [
			'GROUP_ID' => $ID,
		]);

		$res = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $ID
			],
			'select' => [ 'USER_ID' ]
		]);
		while($relationFields = $res->fetch())
		{
			CSocNetSearch::onUserRelationsChange($relationFields['USER_ID']);
		}
		$bSuccess = $DB->Query("DELETE FROM b_sonet_user2group WHERE GROUP_ID = ".$ID, true);

		if ($bSuccess)
		{
			Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup::unlinkChat(array(
				'group_id' => $ID
			));

			$bSuccessTmp = true;
			$dbResult = CSocNetFeatures::GetList(
				array(),
				array("ENTITY_ID" => $ID, "ENTITY_TYPE" => SONET_ENTITY_GROUP)
			);
			while ($arResult = $dbResult->Fetch())
			{
				$bSuccessTmp = $DB->Query("DELETE FROM b_sonet_features2perms WHERE FEATURE_ID = ".$arResult["ID"]."", true);
				if (!$bSuccessTmp)
				{
					break;
				}
			}
			if (!$bSuccessTmp)
			{
				$bSuccess = false;
			}
		}
		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_features WHERE ENTITY_ID = ".$ID." AND ENTITY_TYPE = '".$DB->ForSql(SONET_ENTITY_GROUP, 1)."'", true);
		}
		if ($bSuccess)
		{
			$dbResult = CSocNetLog::GetList(
				array(),
				array("ENTITY_ID" => $ID, "ENTITY_TYPE" => SONET_ENTITY_GROUP),
				false,
				false,
				array("ID")
			);
			while ($arResult = $dbResult->Fetch())
			{
				$bSuccessTmp = $DB->Query("DELETE FROM b_sonet_log_site WHERE LOG_ID = ".$arResult["ID"]."", true);
				if (!$bSuccessTmp)
				{
					break;
				}

				$bSuccessTmp = $DB->Query("DELETE FROM b_sonet_log_right WHERE LOG_ID = ".$arResult["ID"]."", true);
				if (!$bSuccessTmp)
				{
					break;
				}
			}
			if (!$bSuccessTmp)
			{
				$bSuccess = false;
			}
		}
		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_log WHERE ENTITY_TYPE = '".SONET_ENTITY_GROUP."' AND ENTITY_ID = ".$ID."", true);
		}
		if ($bSuccess)
		{
			$bSuccess = CSocNetLog::DeleteSystemEventsByGroupID($ID);
		}
		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_log_events WHERE ENTITY_TYPE = 'G' AND ENTITY_ID = ".$ID."", true);
		}
		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_group_site WHERE GROUP_ID = ".$ID."", true);
		}
		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_log_right WHERE GROUP_CODE LIKE 'OSG".$ID."\_%'", true);
		}
		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_log_right WHERE GROUP_CODE LIKE 'SG".$ID."\_%'", true);
		}
		if ($bSuccess)
		{
			$bSuccess = $DB->Query("DELETE FROM b_sonet_log_right WHERE GROUP_CODE = 'SG".$ID."'", true);
		}
		if ($bSuccess)
		{
			$bSuccess = CSocNetSubscription::DeleteEx(false, "SG".$ID);
		}
		if ($bSuccess)
		{
			$bSuccess = \Bitrix\Socialnetwork\WorkgroupTagTable::deleteByGroupId(['groupId' => $ID]);
		}

		if ($bSuccess)
		{
			CFile::Delete($arGroup["IMAGE_ID"]);
			$bSuccess = $DB->Query("DELETE FROM b_sonet_group WHERE ID = ".$ID."", true);
		}

		$sonetGroupCache = self::getStaticCache();

		if ($bSuccess)
		{
			CUserOptions::DeleteOption("socialnetwork", "~menu_".SONET_ENTITY_GROUP."_".$ID, false, 0);

			unset($sonetGroupCache[$ID]);
			self::setStaticCache($sonetGroupCache);
		}

		if ($bSuccess)
		{
			$DB->Commit();
		}
		else
		{
			$DB->Rollback();
		}

		if ($bSuccess)
		{
			unset($sonetGroupCache[$ID]);
			self::setStaticCache($sonetGroupCache);

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$ID);
				$CACHE_MANAGER->ClearByTag("sonet_user2group");
				$CACHE_MANAGER->ClearByTag("sonet_group_".$ID);
				$CACHE_MANAGER->ClearByTag("sonet_group");
			}
		}

		if (
			$bSuccess
			&& CModule::IncludeModule("search")
		)
		{
			CSearch::DeleteIndex("socialnetwork", "G".$ID);
		}

		if ($bSuccess)
		{
			$DB->Query("DELETE FROM b_sonet_event_user_view WHERE ENTITY_TYPE = '".SONET_ENTITY_GROUP."' AND ENTITY_ID = ".$ID, true);
		}

		if ($bSuccess)
		{
			$USER_FIELD_MANAGER->Delete("SONET_GROUP", $ID);
		}

		if (Loader::includeModule('tasks'))
		{
			$tagService = new Tag(0);
			$tagService->deleteGroupTags($ID);
		}

		return $bSuccess;
	}

	public static function DeleteNoDemand($userID): bool
	{
		global $APPLICATION;

		if (!CSocNetGroup::__ValidateID($userID))
		{
			return false;
		}

		$userID = intval($userID);

		$err = "";
		$dbResult = CSocNetGroup::GetList(array(), array("OWNER_ID" => $userID), false, false, array("ID", "NAME"));
		while ($arResult = $dbResult->GetNext())
		{
			$err .= Emoji::decode($arResult["NAME"]) . "<br>";
		}

		if ($err == '')
		{
			return true;
		}
		else
		{
			$err = GetMessage("SONET_GG_ERROR_CANNOT_DELETE_USER_1").$err;
			$err .= GetMessage("SONET_GG_ERROR_CANNOT_DELETE_USER_2");
			$APPLICATION->ThrowException($err);
			return false;
		}
	}

	public static function SetStat($ID): void
	{
		if (!CSocNetGroup::__ValidateID($ID))
			return;

		$ID = intval($ID);

		$num = CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $ID,
				"USER_ACTIVE" => "Y",
				"<=ROLE" => SONET_ROLES_USER
			),
			array()
		);

		$num_mods = CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $ID,
				"USER_ACTIVE" => "Y",
				"<=ROLE" => SONET_ROLES_MODERATOR
			),
			array()
		);

		CSocNetGroup::Update(
			$ID,
			array(
				"NUMBER_OF_MEMBERS" => $num,
				"NUMBER_OF_MODERATORS" => $num_mods
			),
			true,
			false,
			false
		);
	}

	public static function SetLastActivity($ID, $date = false): void
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
		{
			return;
		}

		$ID = intval($ID);

		CSocNetGroup::Update($ID, (
			$date
				? array("DATE_ACTIVITY" => $date)
				: array("=DATE_ACTIVITY" => $DB->CurrentTimeFunction())
			),
			true,
			false,
			false
		);

		if (defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->clearByTag("sonet_group_activity");
		}
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function getById($id, $checkPermissions = false, array $options = [])
	{
		global $USER, $CACHE_MANAGER;

		$id = (int)$id;

		if (!CSocNetGroup::__ValidateID($id))
		{
			return false;
		}

		$staticCacheKey = implode('_', array_merge([
			($checkPermissions ? 'Y' : 'N'),
		], $options));

		$sonetGroupCache = self::getStaticCache();

		if (
			is_array($sonetGroupCache)
			&& is_array(($sonetGroupCache[$id] ?? null))
			&& is_array(($sonetGroupCache[$id][$staticCacheKey] ?? null))
		)
		{
			return $sonetGroupCache[$id][$staticCacheKey];
		}

		$cache = false;
		$cachePath = false;

		if (!$checkPermissions)
		{
			$cache = new CPHPCache;
			$cacheTime = 31536000;
			$cacheId = implode('_', array_merge([
				'group',
				$id,
				LANGUAGE_ID,
				CTimeZone::getOffset(),
				Context::getCurrent()->getCulture()->getDateTimeFormat()
			], $options));
			$cachePath = '/sonet/group/' . $id . '/';
		}

		if (
			$cache
			&& $cache->initCache($cacheTime, $cacheId, $cachePath)
		)
		{
			$cacheVars = $cache->getVars();
			$result = $cacheVars['FIELDS'];
		}
		else
		{
			if ($cache)
			{
				$cache->startDataCache($cacheTime, $cacheId, $cachePath);
			}

			$filter = [
				'ID' => $id,
			];

			if (
				$checkPermissions
				&& is_object($USER)
				&& ($USER->GetID() > 0)
			)
			{
				$filter['CHECK_PERMISSIONS'] = $USER->getId();
			}

			$select = [
				"ID", "SITE_ID", "NAME", "DESCRIPTION", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "VISIBLE", "OPENED", "CLOSED", "SUBJECT_ID", "OWNER_ID", "KEYWORDS",
				'IMAGE_ID', 'AVATAR_TYPE',
				"NUMBER_OF_MEMBERS", "NUMBER_OF_MODERATORS",
				"INITIATE_PERMS", "SPAM_PERMS",
				"DATE_ACTIVITY", "SUBJECT_NAME", "UF_*",
			];
			if (ModuleManager::isModuleInstalled('intranet'))
			{
				$select = array_merge($select, [ "PROJECT", "PROJECT_DATE_START", "PROJECT_DATE_FINISH" ]);
			}
			if (ModuleManager::isModuleInstalled('landing'))
			{
				$select = array_merge($select, [ "LANDING" ]);
			}
			if (ModuleManager::isModuleInstalled('tasks'))
			{
				$select = array_merge($select, [ "SCRUM_OWNER_ID", "SCRUM_MASTER_ID", "SCRUM_SPRINT_DURATION", "SCRUM_TASK_RESPONSIBLE" ]);
			}
			$res = CSocNetGroup::getList(
				[],
				$filter,
				false,
				false,
				$select
			);
			if ($result = $res->getNext())
			{
				if (
					defined('BX_COMP_MANAGED_CACHE')
					&& $cache
				)
				{
					$CACHE_MANAGER->StartTagCache($cachePath);
					$CACHE_MANAGER->RegisterTag('sonet_group_' . $id);
					$CACHE_MANAGER->RegisterTag('sonet_group');
				}

				if (!empty($result['NAME']))
				{
					$result['NAME'] = Emoji::decode($result['NAME']);
				}
				if (!empty($result['DESCRIPTION']))
				{
					$result['DESCRIPTION'] = Emoji::decode($result['DESCRIPTION']);
				}

				$result['NAME_FORMATTED'] = $result['NAME'];

				if (($options['getSites'] ?? false))
				{
					$result['SITE_LIST'] = [];
					$res = CSocNetGroup::getSite($id);
					while ($groupSiteFields = $res->fetch())
					{
						$result['SITE_LIST'][] = $groupSiteFields['LID'];
					}
				}

				$result['SCRUM'] = (isset($result['SCRUM_MASTER_ID']) && (int)$result['SCRUM_MASTER_ID'] > 0 ? 'Y' : 'N');
				$result['SCRUM_PROJECT'] = $result['SCRUM'];
			}
			else
			{
				$result = false;
			}

			if ($cache)
			{
				$cacheData = [
					'FIELDS' => $result,
				];
				$cache->EndDataCache($cacheData);
				if (defined('BX_COMP_MANAGED_CACHE'))
				{
					$CACHE_MANAGER->EndTagCache();
				}
			}
		}

		if (!is_array($sonetGroupCache))
		{
			$sonetGroupCache = [];
		}
		if (
			!array_key_exists($id, $sonetGroupCache)
			|| !is_array($sonetGroupCache[$id])
		)
		{
			$sonetGroupCache[$id] = [];
		}
		$sonetGroupCache[$id][$staticCacheKey] = $result;

		self::setStaticCache($sonetGroupCache);

		return $result;
	}

	protected static function getStaticCache(): array
	{
		return self::$staticCache;
	}

	protected static function setStaticCache($cache = array())
	{
		self::$staticCache = $cache;
	}

	/***************************************/
	/**********  COMMON METHODS  ***********/
	/***************************************/
	public static function CanUserInitiate($userID, $groupID): bool
	{
		$userID = intval($userID);
		$groupID = intval($groupID);
		if ($groupID <= 0)
			return false;

		$userRoleInGroup = CSocNetUserToGroup::GetUserRole($userID, $groupID);
		if ($userRoleInGroup == false)
			return false;

		$arGroup = CSocNetGroup::GetById($groupID);
		if ($arGroup == false)
			return false;

		if ($arGroup["INITIATE_PERMS"] == SONET_ROLES_MODERATOR)
		{
			if ($userRoleInGroup == SONET_ROLES_MODERATOR || $userRoleInGroup == SONET_ROLES_OWNER)
				return true;
			else
				return false;
		}
		elseif ($arGroup["INITIATE_PERMS"] == SONET_ROLES_USER)
		{
			if ($userRoleInGroup == SONET_ROLES_MODERATOR || $userRoleInGroup == SONET_ROLES_OWNER || $userRoleInGroup == SONET_ROLES_USER)
				return true;
			else
				return false;
		}
		elseif ($arGroup["INITIATE_PERMS"] == SONET_ROLES_OWNER)
		{
			if ($userRoleInGroup == SONET_ROLES_OWNER)
				return true;
			else
				return false;
		}

		return false;
	}

	public static function CanUserViewGroup($userID, $groupID): bool
	{
		$userID = intval($userID);
		$groupID = intval($groupID);
		if ($groupID <= 0)
			return false;

		$arGroup = CSocNetGroup::GetById($groupID);
		if ($arGroup == false)
			return false;

		if ($arGroup["VISIBLE"] == "Y")
			return true;

		$userRoleInGroup = CSocNetUserToGroup::GetUserRole($userID, $groupID);
		if ($userRoleInGroup == false)
			return false;

		return in_array($userRoleInGroup, array(SONET_ROLES_OWNER, SONET_ROLES_MODERATOR, SONET_ROLES_USER));
	}

	public static function CanUserReadGroup($userID, $groupID): bool
	{
		$userID = intval($userID);
		$groupID = intval($groupID);
		if ($groupID <= 0)
			return false;

		$arGroup = CSocNetGroup::GetById($groupID);
		if ($arGroup == false)
			return false;

		if ($arGroup["OPENED"] == "Y")
			return true;

		$userRoleInGroup = CSocNetUserToGroup::GetUserRole($userID, $groupID);
		if ($userRoleInGroup == false)
			return false;

		return in_array($userRoleInGroup, array(SONET_ROLES_OWNER, SONET_ROLES_MODERATOR, SONET_ROLES_USER));
	}

	/***************************************/
	/************  ACTIONS  ****************/
	/***************************************/
	public static function createGroup($ownerID, $arFields, $bAutoSubscribe = true)
	{
		global $APPLICATION, $DB;

		$ownerID = (int)$ownerID;
		if ($ownerID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_OWNERID").". ", "ERROR_OWNERID");
			return false;
		}

		if (!isset($arFields) || !is_array($arFields))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_FIELDS").". ", "ERROR_FIELDS");
			return false;
		}

		if (!empty($arFields['SCRUM_MASTER_ID']) && CModule::includeModule("tasks"))
		{
			if (ScrumLimit::isLimitExceeded())
			{
				$APPLICATION->ThrowException("Scrum limit exceeded");

				return false;
			}
		}

		$DB->StartTransaction();

		if (!isset($arFields["DATE_CREATE"]))
		{
			$arFields["=DATE_CREATE"] = CDatabase::currentTimeFunction();
		}

		if (!isset($arFields["DATE_UPDATE"]))
		{
			$arFields["=DATE_UPDATE"] = CDatabase::currentTimeFunction();
		}

		if (!isset($arFields["DATE_ACTIVITY"]))
		{
			$arFields["=DATE_ACTIVITY"] = CDatabase::currentTimeFunction();
		}

		$arFields["ACTIVE"] = "Y";
		$arFields["OWNER_ID"] = $ownerID;

		if (!is_set($arFields, "SPAM_PERMS") || $arFields["SPAM_PERMS"] == '')
		{
			$arFields["SPAM_PERMS"] = SONET_ROLES_OWNER;
		}

		$groupID = CSocNetGroup::add($arFields);

		if (
			!$groupID
			|| $groupID <= 0
		)
		{
			$errorMessage = $errorID = "";
			if ($e = $APPLICATION->getException())
			{
				$errorMessage = $e->GetString();
				$errorID = $e->GetID();

				if (
					$errorID == ''
					&& isset($e->messages)
					&& is_array($e->messages)
					&& is_array($e->messages[0])
					&& array_key_exists("id", $e->messages[0])
				)
				{
					$errorID = $e->messages[0]["id"];
				}
			}

			if ($errorMessage == '')
			{
				$errorMessage = GetMessage("SONET_UR_ERROR_CREATE_GROUP").". ";
			}

			if ($errorID == '')
			{
				$errorID = "ERROR_CREATE_GROUP";
			}

			$APPLICATION->ThrowException($errorMessage, $errorID);
			$DB->Rollback();

			return false;
		}

		$arFields1 = array(
			"USER_ID" => $ownerID,
			"GROUP_ID" => $groupID,
			"ROLE" => SONET_ROLES_OWNER,
			"=DATE_CREATE" => $DB->CurrentTimeFunction(),
			"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
			"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
			"INITIATED_BY_USER_ID" => $ownerID,
			"MESSAGE" => false
		);

		if (!CSocNetUserToGroup::Add($arFields1))
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
			{
				$errorMessage = $e->GetString();
			}

			if ($errorMessage == '')
			{
				$errorMessage = GetMessage("SONET_UR_ERROR_CREATE_U_GROUP").". ";
			}

			$APPLICATION->ThrowException($errorMessage, "ERROR_CREATE_GROUP");
			$DB->Rollback();
			return false;
		}

		if ($bAutoSubscribe)
		{
			CSocNetLogEvents::AutoSubscribe($ownerID, SONET_ENTITY_GROUP, $groupID);
		}

		CSocNetSubscription::Set($ownerID, "SG".$groupID, "Y");

		$DB->Commit();

		return $groupID;
	}

	/***************************************/
	/*************  UTILITIES  *************/
	/***************************************/
	public static function __ValidateID($ID): bool
	{
		global $APPLICATION;

		if (intval($ID)."|" == $ID."|")
			return true;

		$APPLICATION->ThrowException(GetMessage("SONET_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
		return false;
	}

	public static function GetFilterOperation($key): array
	{
		$strNegative = "N";
		if (mb_substr($key, 0, 1) == "!")
		{
			$key = mb_substr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (mb_substr($key, 0, 1) == "+")
		{
			$key = mb_substr($key, 1);
			$strOrNull = "Y";
		}

		if (mb_substr($key, 0, 2) == ">=")
		{
			$key = mb_substr($key, 2);
			$strOperation = ">=";
		}
		elseif (mb_substr($key, 0, 1) == ">")
		{
			$key = mb_substr($key, 1);
			$strOperation = ">";
		}
		elseif (mb_substr($key, 0, 2) == "<=")
		{
			$key = mb_substr($key, 2);
			$strOperation = "<=";
		}
		elseif (mb_substr($key, 0, 1) == "<")
		{
			$key = mb_substr($key, 1);
			$strOperation = "<";
		}
		elseif (mb_substr($key, 0, 1) == "@")
		{
			$key = mb_substr($key, 1);
			$strOperation = "IN";
		}
		elseif (mb_substr($key, 0, 1) == "~")
		{
			$key = mb_substr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (mb_substr($key, 0, 1) == "%")
		{
			$key = mb_substr($key, 1);
			$strOperation = "QUERY";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull);
	}

	public static function PrepareSql(&$arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, $arUF = array()): array
	{
		global $DB;

		$obUserFieldsSql = false;

		if (is_array($arUF) && array_key_exists("ENTITY_ID", $arUF))
		{
			$obUserFieldsSql = new CUserTypeSQL;
			$obUserFieldsSql->SetEntity($arUF["ENTITY_ID"], $arFields["ID"]["FIELD"]);
			$obUserFieldsSql->SetSelect($arSelectFields);
			$obUserFieldsSql->SetFilter($arFilter);
			$obUserFieldsSql->SetOrder($arOrder);
		}

		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlWhere = "";
		$strSqlGroupBy = "";

		$arGroupByFunct = array("COUNT", "AVG", "MIN", "MAX", "SUM");

		$arAlreadyJoined = array();

		// GROUP BY -->
		if (is_array($arGroupBy) && count($arGroupBy)>0)
		{
			$arSelectFields = $arGroupBy;
			foreach ($arGroupBy as $key => $val)
			{
				$val = mb_strtoupper($val);
				$key = mb_strtoupper($key);
				if (array_key_exists($val, $arFields) && !in_array($key, $arGroupByFunct))
				{
					if ($strSqlGroupBy <> '')
						$strSqlGroupBy .= ", ";
					$strSqlGroupBy .= $arFields[$val]["FIELD"];

					if (isset($arFields[$val]["FROM"])
						&& $arFields[$val]["FROM"] <> ''
						&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
					{
						if ($strSqlFrom <> '')
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$val]["FROM"];
						$arAlreadyJoined[] = $arFields[$val]["FROM"];
					}
				}
			}
		}
		// <-- GROUP BY

		// WHERE -->
		$arAlreadyJoinedOld = $arAlreadyJoined;

		if (
			isset($arFilter['ID'])
			&& is_array($arFilter['ID'])
		)
		{
			$arFilter['@ID'] = $arFilter['ID'];
			unset($arFilter['ID']);
		}
		if (
			isset($arFilter['!ID'])
			&& is_array($arFilter['!ID'])
		)
		{
			$arFilter['!@ID'] = $arFilter['!ID'];
			unset($arFilter['!ID']);
		}

		$strSqlWhere .= CSqlUtil::PrepareWhere($arFields, $arFilter, $arAlreadyJoined);
		$arAlreadyJoinedDiff = array_diff($arAlreadyJoined, $arAlreadyJoinedOld);

		foreach($arAlreadyJoinedDiff as $from_tmp)
		{
			if ($strSqlFrom <> '')
				$strSqlFrom .= " ";
			$strSqlFrom .= $from_tmp;
		}

		if ($obUserFieldsSql)
		{
			$r = $obUserFieldsSql->GetFilter();
			if($r <> '')
				$strSqlWhere .= ($strSqlWhere <> '' ? " AND" : "")." (".$r.") ";
		}
		// <-- WHERE

		// ORDER BY -->
		$arSqlOrder = Array();
		foreach ($arOrder as $by => $order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);

			if ($order != "ASC")
				$order = "DESC";
			else
				$order = "ASC";

			if (array_key_exists($by, $arFields))
			{
				if ($arFields[$by]["TYPE"] == "datetime" || $arFields[$by]["TYPE"] == "date")
				{
					$arSqlOrder[] = " ".$by."_X1 ".$order." ";
					if (!is_array($arSelectFields) || !in_array($by, $arSelectFields))
						$arSelectFields[] = $by;
				}
				else
					$arSqlOrder[] = " ".$arFields[$by]["FIELD"]." ".$order." ";

				if (isset($arFields[$by]["FROM"])
					&& $arFields[$by]["FROM"] <> ''
					&& !in_array($arFields[$by]["FROM"], $arAlreadyJoined))
				{
					if ($strSqlFrom <> '')
						$strSqlFrom .= " ";
					$strSqlFrom .= $arFields[$by]["FROM"];
					$arAlreadyJoined[] = $arFields[$by]["FROM"];
				}
			}
			elseif($obUserFieldsSql && $s = $obUserFieldsSql->GetOrder($by))
				$arSqlOrder[$by] = " ".$s." ".$order." ";
		}

		$strSqlOrderBy = "";
		DelDuplicateSort($arSqlOrder);
		$tmp_count = count($arSqlOrder);
		for ($i=0; $i < $tmp_count; $i++)
		{
			if ($strSqlOrderBy <> '')
				$strSqlOrderBy .= ", ";

			if($DB->type == "ORACLE")
			{
				if(mb_substr($arSqlOrder[$i], -3) == "ASC")
					$strSqlOrderBy .= $arSqlOrder[$i]." NULLS FIRST";
				else
					$strSqlOrderBy .= $arSqlOrder[$i]." NULLS LAST";
			}
			else
				$strSqlOrderBy .= $arSqlOrder[$i];
		}
		// <-- ORDER BY

		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
			$strSqlSelect = "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT ";
		else
		{
			if (isset($arSelectFields) && !is_array($arSelectFields) && is_string($arSelectFields) && $arSelectFields <> '' && array_key_exists($arSelectFields, $arFields))
				$arSelectFields = array($arSelectFields);

			if (!isset($arSelectFields)
				|| !is_array($arSelectFields)
				|| count($arSelectFields) <= 0
				|| in_array("*", $arSelectFields))
			{
				$tmp_count = count($arFieldsKeys);
				for ($i = 0; $i < $tmp_count; $i++)
				{
					if (isset($arFields[$arFieldsKeys[$i]]["WHERE_ONLY"])
						&& $arFields[$arFieldsKeys[$i]]["WHERE_ONLY"] == "Y")
						continue;

					if ($strSqlSelect <> '')
						$strSqlSelect .= ", ";

					if ($arFields[$arFieldsKeys[$i]]["TYPE"] == "datetime")
					{
						if (array_key_exists($arFieldsKeys[$i], $arOrder))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"])." as ".$arFieldsKeys[$i];
					}
					elseif ($arFields[$arFieldsKeys[$i]]["TYPE"] == "date")
					{
						if (array_key_exists($arFieldsKeys[$i], $arOrder))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"], "SHORT")." as ".$arFieldsKeys[$i];
					}
					else
						$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i];

					if (isset($arFields[$arFieldsKeys[$i]]["FROM"])
						&& $arFields[$arFieldsKeys[$i]]["FROM"] <> ''
						&& !in_array($arFields[$arFieldsKeys[$i]]["FROM"], $arAlreadyJoined))
					{
						if ($strSqlFrom <> '')
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$arFieldsKeys[$i]]["FROM"];
						$arAlreadyJoined[] = $arFields[$arFieldsKeys[$i]]["FROM"];
					}
				}
			}
			else
			{
				foreach ($arSelectFields as $key => $val)
				{
					$val = mb_strtoupper($val);
					$key = mb_strtoupper($key);
					if (array_key_exists($val, $arFields))
					{
						if ($strSqlSelect <> '')
							$strSqlSelect .= ", ";

						if (in_array($key, $arGroupByFunct))
							$strSqlSelect .= $key."(".$arFields[$val]["FIELD"].") as ".$val;
						else
						{
							if (($arFields[$val]["TYPE"] ?? null) === "datetime")
							{
								if (array_key_exists($val, $arOrder))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"])." as ".$val;
							}
							elseif (($arFields[$val]["TYPE"] ?? null) === "date")
							{
								if (array_key_exists($val, $arOrder))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "SHORT")." as ".$val;
							}
							else
								$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val;
						}

						if (isset($arFields[$val]["FROM"])
							&& $arFields[$val]["FROM"] <> ''
							&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
						{
							if ($strSqlFrom <> '')
								$strSqlFrom .= " ";
							$strSqlFrom .= $arFields[$val]["FROM"];
							$arAlreadyJoined[] = $arFields[$val]["FROM"];
						}
					}
				}
			}

			if ($obUserFieldsSql)
				$strSqlSelect .= ($strSqlSelect == '' ? $arFields["ID"]["FIELD"] : "").$obUserFieldsSql->GetSelect();

			if ($strSqlGroupBy <> '')
			{
				if ($strSqlSelect <> '')
				{
					$strSqlSelect .= ", ";
				}
				$strSqlSelect .= "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT";
			}
			else
			{
				$strSqlSelect = "%%_DISTINCT_%% ".$strSqlSelect;
			}
		}
		// <-- SELECT

		if ($obUserFieldsSql)
		{
			$strSqlFrom .= " ".$obUserFieldsSql->GetJoin($arFields["ID"]["FIELD"]);
		}

		return array(
			"SELECT" => $strSqlSelect,
			"FROM" => $strSqlFrom,
			"WHERE" => $strSqlWhere,
			"GROUPBY" => $strSqlGroupBy,
			"ORDERBY" => $strSqlOrderBy
		);
	}

	/***************************************/
	/*************    *************/
	/***************************************/

	public static function GetSite($group_id)
	{
		global $DB;

		if (is_array($group_id))
		{
			if (empty($group_id))
			{
				return false;
			}

			$strVal = "";

			foreach ($group_id as $val)
			{
				if ($strVal <> '')
				{
					$strVal .= ', ';
				}
				$strVal .= intval($val);
			}
			$strSql = "SELECT L.*, SGS.* FROM b_sonet_group_site SGS, b_lang L WHERE L.LID=SGS.SITE_ID AND SGS.GROUP_ID IN (".$strVal.")";
		}
		else
		{
			$strSql = "SELECT L.*, SGS.* FROM b_sonet_group_site SGS, b_lang L WHERE L.LID=SGS.SITE_ID AND SGS.GROUP_ID=".intval($group_id);
		}

		return $DB->Query($strSql);
	}

	public static function GetDefaultSiteId($groupId, $siteId = false)
	{
		$groupSiteId = ($siteId ?: SITE_ID);

		if (CModule::IncludeModule("extranet"))
		{
			$extranetSiteId = CExtranet::GetExtranetSiteID();

			$rsGroupSite = CSocNetGroup::GetSite($groupId);
			while ($arGroupSite = $rsGroupSite->Fetch())
			{
				if (
					!$extranetSiteId
					|| $arGroupSite["LID"] != $extranetSiteId
				)
				{
					$groupSiteId = $arGroupSite["LID"];
					break;
				}
			}
		}

		return $groupSiteId;
	}

	public static function OnBeforeLangDelete($lang): bool
	{
		global $APPLICATION, $DB;
		$r = $DB->Query("
			SELECT GROUP_ID
			FROM b_sonet_group_site
			WHERE SITE_ID='".$DB->ForSQL($lang, 2)."'
			ORDER BY GROUP_ID
		");
		$arSocNetGroups = array();
		while($a = $r->Fetch())
			$arSocNetGroups[] = $a["GROUP_ID"];
		if(count($arSocNetGroups) > 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GROUP_SITE_LINKS_EXISTS", array("#ID_LIST#" => implode(", ", $arSocNetGroups))));
			return false;
		}
		else
			return true;
	}

	public static function SearchIndex($groupId, $arSiteID = array(), $arGroupOld = array()): void
	{
		if (intval($groupId) <= 0)
		{
			return;
		}

		if (CModule::IncludeModule("search"))
		{
			$arGroupNew = CSocNetGroup::GetByID($groupId);
			if ($arGroupNew)
			{
				if (
					$arGroupNew["ACTIVE"] == "N"
					&& isset($arGroupOld)
					&& is_array($arGroupOld)
					&& isset($arGroupOld["ACTIVE"])
					&& $arGroupOld["ACTIVE"] == "Y"
				)
				{
					CSearch::DeleteIndex("socialnetwork", "G".$groupId);
				}
				elseif ($arGroupNew["ACTIVE"] == "Y")
				{
					$BODY = CSocNetTextParser::killAllTags($arGroupNew["~DESCRIPTION"]);
					$BODY .= $GLOBALS["USER_FIELD_MANAGER"]->OnSearchIndex("SONET_GROUP", $groupId);

					$arSearchIndexSiteID = array();
					if (
						is_array($arSiteID)
						&& !empty($arSiteID)
					)
					{
						foreach ($arSiteID as $site_id_tmp)
						{
							$arSearchIndexSiteID[$site_id_tmp] = str_replace("#group_id#", $groupId, Path::get('group_path_template', $site_id_tmp));
						}
					}
					else
					{
						$rsGroupSite = CSocNetGroup::GetSite($groupId);
						while ($arGroupSite = $rsGroupSite->Fetch())
						{
							$arSearchIndexSiteID[$arGroupSite["LID"]] = str_replace("#group_id#", $groupId, Path::get('group_path_template', $arGroupSite['LID']));
						}
					}

					$arSearchIndex = array(
						"SITE_ID" => $arSearchIndexSiteID,
						"LAST_MODIFIED" => $arGroupNew["DATE_ACTIVITY"],
						"PARAM1" => $arGroupNew["SUBJECT_ID"],
						"PARAM2" => $groupId,
						"PARAM3" => "GROUP",
						"PERMISSIONS" => (
							$arGroupNew["VISIBLE"] == "Y"?
								array('G2')://public
								array(
									'SG'.$groupId.'_A',//admins
									'SG'.$groupId.'_E',//moderators
									'SG'.$groupId.'_K',//members
								)
						),
						"PARAMS" =>array(
							"socnet_group" 	=> $groupId,
							"entity" 		=> "socnet_group",
						),
						"TITLE" => $arGroupNew["~NAME"],
						"BODY" => $BODY,
						"TAGS" => $arGroupNew["~KEYWORDS"],
					);

					CSearch::Index("socialnetwork", "G".$groupId, $arSearchIndex, True);
				}
			}
		}
	}

	public static function ConfirmAllRequests($groupId, $bAutoSubscribe = true)
	{
		$dbRequests = CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $groupId,
				"ROLE" => SONET_ROLES_REQUEST,
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER
			),
			false,
			false,
			array("ID")
		);
		if ($dbRequests)
		{
			$arIDs = array();
			while ($arRequests = $dbRequests->GetNext())
			{
				$arIDs[] = $arRequests["ID"];
			}
			CSocNetUserToGroup::ConfirmRequestToBeMember($GLOBALS["USER"]->GetID(), $groupId, $arIDs, $bAutoSubscribe);
		}
	}

}