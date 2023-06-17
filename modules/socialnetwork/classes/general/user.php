<?php

use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

class CAllSocNetUser
{
	public static function OnUserDelete($ID)
	{
		global $APPLICATION;

		if (!CSocNetGroup::__ValidateID($ID))
		{
			return false;
		}

		$ID = (int)$ID;
		$bSuccess = True;

		if (!CSocNetGroup::DeleteNoDemand($ID))
		{
			if($ex = $APPLICATION->GetException())
			{
				$APPLICATION->ThrowException($ex->GetString());
			}
			$bSuccess = false;
		}

		if ($bSuccess)
		{
			CSocNetUserRelations::DeleteNoDemand($ID);
			CSocNetUserPerms::DeleteNoDemand($ID);
			CSocNetUserEvents::DeleteNoDemand($ID);
			CSocNetMessages::DeleteNoDemand($ID);
			CSocNetUserToGroup::DeleteNoDemand($ID);
			CSocNetLogEvents::DeleteNoDemand($ID);
			CSocNetLog::DeleteNoDemand($ID);
			CSocNetLogComments::DeleteNoDemand($ID);
			CSocNetFeatures::DeleteNoDemand($ID);
			CSocNetSubscription::DeleteEx($ID);
			\Bitrix\Socialnetwork\Item\UserContentView::deleteNoDemand($ID);
			\Bitrix\Socialnetwork\LogRightTable::deleteByGroupCode('U'.$ID);

			CUserOptions::DeleteOption("socialnetwork", "~menu_".SONET_ENTITY_USER."_".$ID, false, 0);
		}

		return $bSuccess;
	}

	public static function OnBeforeUserUpdate(&$arFields)
	{
		$rsUser = CUser::GetByID($arFields["ID"]);
		if (($arUser = $rsUser->Fetch()) && !defined("GLOBAL_ACTIVE_VALUE"))
		{
			define("GLOBAL_ACTIVE_VALUE", $arUser["ACTIVE"]);
		}
	}

	public static function OnAfterUserAdd(&$arFields)
	{
		return;
	}

	public static function OnAfterUserLogout(&$arParams)
	{
		\CSocNetUser::DisableModuleAdmin();
	}

	public static function OnAfterUserUpdate(&$arFields)
	{
		if (
			array_key_exists("ACTIVE", $arFields)
			&& defined("GLOBAL_ACTIVE_VALUE")
			&& GLOBAL_ACTIVE_VALUE != $arFields["ACTIVE"]
		)
		{
			$arGroups = array();
			$dbResult = CSocNetUserToGroup::GetList(
				array(),
				array(
					"USER_ID" => $arFields["ID"]
				),
				false,
				false,
				array("GROUP_ID")
			);
			while ($arResult = $dbResult->Fetch())
			{
				$arGroups[] = $arResult["GROUP_ID"];
			}

			foreach ($arGroups as $group)
			{
				CSocNetGroup::SetStat($group);
			}
		}
	}

	public static function OnBeforeProlog()
	{
		global $USER;

		if (!$USER->IsAuthorized())
			return;

		CUser::SetLastActivityDate($USER->GetID(), true);
	}

	public static function OnUserInitialize($user_id, $arFields = array())
	{
		global $CACHE_MANAGER;

		if ((int)$user_id <= 0)
		{
			return false;
		}

		$bIM = Loader::includeModule('im');

		$dbRelation = CSocNetUserToGroup::GetList(
			array(),
			array(
				"USER_ID" => $user_id,
				"ROLE" => SONET_ROLES_REQUEST,
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP
			),
			false,
			false,
			array("ID", "GROUP_ID")
		);
		while ($arRelation = $dbRelation->Fetch())
		{
			if (
				CSocNetUserToGroup::UserConfirmRequestToBeMember($user_id, $arRelation["ID"], false)
				&& defined("BX_COMP_MANAGED_CACHE")
			)
			{
				$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$arRelation["GROUP_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group_U".$user_id);
				$CACHE_MANAGER->ClearByTag("sonet_user2group");
				if ($bIM)
				{
					CIMNotify::DeleteByTag("SOCNET|INVITE_GROUP|".$user_id."|". (int)$arRelation["ID"]);
				}
			}
		}
	}

	public static function IsOnLine($userID)
	{
		$userID = (int)$userID;
		if ($userID <= 0)
		{
			return false;
		}

		return CUser::IsOnLine($userID); // TODO change to use CUser::GetOnlineStatus see more in docs.bx
	}

	public static function IsFriendsAllowed()
	{
		return (COption::GetOptionString("socialnetwork", "allow_frields", "Y") === "Y");
	}

	public static function IsFriendsFriendsAllowed()
	{
		return (COption::GetOptionString("socialnetwork", "allow_frields_friends", "Y") === "Y");
	}

	/**
	 * Tells true if the current user enabled module admin mode.
	 * @return bool
	 */
	public static function IsEnabledModuleAdmin(): bool
	{
		return isset(Application::getInstance()->getKernelSession()['SONET_ADMIN']);
	}

	/**
	 * Enables module admin mode for the current user.
	 * @return void
	 */
	public static function EnableModuleAdmin(): void
	{
		Application::getInstance()->getKernelSession()['SONET_ADMIN'] = 'Y';
	}

	/**
	 * Disables module admin mode for the current user.
	 * @return void
	 */
	public static function DisableModuleAdmin(): void
	{
		unset(Application::getInstance()->getKernelSession()['SONET_ADMIN']);
	}

	public static function IsCurrentUserModuleAdmin($site_id = SITE_ID, $bUseSession = true)
	{
		global $APPLICATION, $USER;

		static $cache = [];

		if (!is_object($USER) || !$USER->IsAuthorized())
		{
			return false;
		}

		$result = $USER->isAdmin();

		if (!$result)
		{
			$cacheKey = 'false';
			if (is_array($site_id))
			{
				$cacheKey = serialize($cacheKey);
			}
			elseif ($site_id)
			{
				$cacheKey = $site_id;
			}
			else
			{
				$cacheKey = 'false';
			}

			if (isset($cache[$cacheKey]))
			{
				$result = $cache[$cacheKey];
			}
			else
			{
				if (is_array($site_id))
				{
					foreach ($site_id as $site_id_tmp)
					{
						$modulePerms = $APPLICATION->GetGroupRight("socialnetwork", false, "Y", "Y", array($site_id_tmp, false));
						if ($modulePerms >= "W")
						{
							$result = true;
							break;
						}
					}
				}
				else
				{
					$modulePerms = $APPLICATION->GetGroupRight("socialnetwork", false, "Y", "Y", ($site_id ? array($site_id, false) : false));
					$result = ($modulePerms >= "W");
				}

				$cache[$cacheKey] = $result;
			}
		}

		$result = (
			$result
			&& (
				!$bUseSession
				|| CSocNetUser::IsEnabledModuleAdmin()
			)
		);

		return $result;
	}

	public static function IsUserModuleAdmin($userID, $site_id = SITE_ID)
	{
		if ($userID <= 0)
		{
			return false;
		}

		if ($site_id && !is_array($site_id))
		{
			$site_id = array($site_id, false);
		}
		elseif ($site_id && is_array($site_id))
		{
			$site_id = array_merge($site_id, array(false));
		}

		$arModuleAdmin = \Bitrix\Socialnetwork\User::getModuleAdminList($site_id);

		return (array_key_exists($userID, $arModuleAdmin));
	}

	public static function DeleteUserAdminCache()
	{
		BXClearCache(true, "/sonet/user_admin/");
	}

	public static function FormatName($name, $lastName, $login)
	{
		$name = Trim($name);
		$lastName = Trim($lastName);
		$login = Trim($login);

		$formatName = $name;
		if ($formatName <> '' && $lastName <> '')
		{
			$formatName .= " ";
		}
		$formatName .= $lastName;
		if ($formatName == '')
		{
			$formatName = $login;
		}

		return $formatName;
	}

	public static function FormatNameEx($name, $secondName, $lastName, $login, $email, $id)
	{
		$name = Trim($name);
		$lastName = Trim($lastName);
		$secondName = Trim($secondName);
		$login = Trim($login);
		$email = Trim($email);
		$id = (int)$id;

		$formatName = $name;
		if ($formatName <> '' && $secondName <> '')
		{
			$formatName .= " ";
		}
		$formatName .= $secondName;
		if ($formatName <> '' && $lastName <> '')
		{
			$formatName .= " ";
		}
		$formatName .= $lastName;
		if ($formatName == '')
		{
			$formatName = $login;
		}

		if ($email <> '')
		{
			$formatName .= " &lt;" . $email . "&gt;";
		}
		$formatName .= " [".$id."]";

		return $formatName;
	}

	public static function SearchUser($user, $bIntranet = false)
	{
		$user = Trim($user);
		if ($user == '')
			return false;

		$userID = 0;
		if ($user."|" == (int)$user ."|")
		{
			$userID = (int)$user;
		}

		if ($userID <= 0)
		{
			$arMatches = array();
			if (preg_match("#\[(\d+)\]#i", $user, $arMatches))
			{
				$userID = (int)$arMatches[1];
			}
		}


		$dbUsers =  false;
		if ($userID > 0)
		{
			$arFilter = array("ID_EQUAL_EXACT" => $userID);

			$dbUsers = CUser::GetList(
				"LAST_NAME",
				"asc",
				$arFilter,
				array(
					"NAV_PARAMS" => false,
				)
			);
		}
		else
		{
			$email = "";
			$arMatches = array();
			if (preg_match("#<(.+?)>#i", $user, $arMatches))
			{

				if (check_email($arMatches[1]))
				{
					$email = $arMatches[1];
					$user = Trim(Str_Replace("<".$email.">", "", $user));
				}
			}

			$arUser = array();
			$arUserTmp = Explode(" ", $user);
			foreach ($arUserTmp as $s)
			{
				$s = Trim($s);
				if ($s <> '')
				{
					$arUser[] = $s;
				}
			}

			if (
				count($arUser) <= 0
				&& $email <> ''
			)
			{
				$arFilter = array(
					"ACTIVE" => "Y",
					"EMAIL" => $email,
				);
				$dbUsers = CUser::GetList("id", "asc", $arFilter);
			}
			else
			{
				$dbUsers = CUser::SearchUserByName($arUser, $email);
			}
		}

		if ($dbUsers)
		{
			$arResult = array();
			while ($arUsers = $dbUsers->GetNext())
			{
				$arResult[$arUsers["ID"]] = CSocNetUser::FormatNameEx(
					$arUsers["NAME"],
					$arUsers["SECOND_NAME"],
					$arUsers["LAST_NAME"],
					$arUsers["LOGIN"],
					($bIntranet ? $arUsers["EMAIL"] : ""),
					$arUsers["ID"]
				);
			}

			return $arResult;
		}

		return false;
	}

	public static function GetByID($ID)
	{
		$ID = (int)$ID;

		$dbUser = CUser::GetByID($ID);
		if ($arUser = $dbUser->GetNext())
		{
			$arUser["NAME_FORMATTED"] = CUser::FormatName(CSite::GetNameFormat(false), $arUser);
			$arUser["~NAME_FORMATTED"] = htmlspecialcharsback($arUser["NAME_FORMATTED"]);
			return $arUser;
		}
		else
		{
			return false;
		}
	}

	public static function GetFields($bAdditional = false)
	{
		$arRes = array(
			"ID" => GetMessage("SONET_UP1_ID"),
			"LOGIN" => GetMessage("SONET_UP1_LOGIN"),
			"NAME" => GetMessage("SONET_UP1_NAME"),
			"SECOND_NAME" => GetMessage("SONET_UP1_SECOND_NAME"),
			"LAST_NAME" => GetMessage("SONET_UP1_LAST_NAME"),
			"EMAIL" => GetMessage("SONET_UP1_EMAIL"),
			"TIME_ZONE" => GetMessage("SONET_UP1_TIME_ZONE"),
			"LAST_LOGIN" => GetMessage("SONET_UP1_LAST_LOGIN"),
			"LAST_ACTIVITY_DATE" => GetMessage("SONET_UP1_LAST_ACTIVITY_DATE"),
			"DATE_REGISTER" => GetMessage("SONET_UP1_DATE_REGISTER"),
			"LID" => GetMessage("SONET_UP1_LID"),
			"PASSWORD" => GetMessage("SONET_UP1_PASSWORD"),
			"PERSONAL_BIRTHDAY" => GetMessage("SONET_UP1_PERSONAL_BIRTHDAY"),
			"PERSONAL_BIRTHDAY_YEAR" => GetMessage("SONET_UP1_PERSONAL_BIRTHDAY_YEAR"),
			"PERSONAL_BIRTHDAY_DAY" => GetMessage("SONET_UP1_PERSONAL_BIRTHDAY_DAY"),

			"PERSONAL_PROFESSION" => GetMessage("SONET_UP1_PERSONAL_PROFESSION"),
			"PERSONAL_WWW" => GetMessage("SONET_UP1_PERSONAL_WWW"),
			"PERSONAL_ICQ" => GetMessage("SONET_UP1_PERSONAL_ICQ"),
			"PERSONAL_GENDER" => GetMessage("SONET_UP1_PERSONAL_GENDER"),
			"PERSONAL_PHOTO" => GetMessage("SONET_UP1_PERSONAL_PHOTO"),
			"PERSONAL_NOTES" => GetMessage("SONET_UP1_PERSONAL_NOTES"),

			"PERSONAL_PHONE" => GetMessage("SONET_UP1_PERSONAL_PHONE"),
			"PERSONAL_FAX" => GetMessage("SONET_UP1_PERSONAL_FAX"),
			"PERSONAL_MOBILE" => GetMessage("SONET_UP1_PERSONAL_MOBILE"),
			"PERSONAL_PAGER" => GetMessage("SONET_UP1_PERSONAL_PAGER"),

			"PERSONAL_COUNTRY" => GetMessage("SONET_UP1_PERSONAL_COUNTRY"),
			"PERSONAL_STATE" => GetMessage("SONET_UP1_PERSONAL_STATE"),
			"PERSONAL_CITY" => GetMessage("SONET_UP1_PERSONAL_CITY"),
			"PERSONAL_ZIP" => GetMessage("SONET_UP1_PERSONAL_ZIP"),
			"PERSONAL_STREET" => GetMessage("SONET_UP1_PERSONAL_STREET"),
			"PERSONAL_MAILBOX" => GetMessage("SONET_UP1_PERSONAL_MAILBOX"),

			"WORK_COMPANY" => GetMessage("SONET_UP1_WORK_COMPANY"),
			"WORK_DEPARTMENT" => GetMessage("SONET_UP1_WORK_DEPARTMENT"),
			"WORK_POSITION" => GetMessage("SONET_UP1_WORK_POSITION"),
			"WORK_WWW" => GetMessage("SONET_UP1_WORK_WWW"),
			"WORK_PROFILE" => GetMessage("SONET_UP1_WORK_PROFILE"),
			"WORK_LOGO" => GetMessage("SONET_UP1_WORK_LOGO"),
			"WORK_NOTES" => GetMessage("SONET_UP1_WORK_NOTES"),

			"WORK_PHONE" => GetMessage("SONET_UP1_WORK_PHONE"),
			"WORK_FAX" => GetMessage("SONET_UP1_WORK_FAX"),
			"WORK_PAGER" => GetMessage("SONET_UP1_WORK_PAGER"),

			"WORK_COUNTRY" => GetMessage("SONET_UP1_WORK_COUNTRY"),
			"WORK_STATE" => GetMessage("SONET_UP1_WORK_STATE"),
			"WORK_CITY" => GetMessage("SONET_UP1_WORK_CITY"),
			"WORK_ZIP" => GetMessage("SONET_UP1_WORK_ZIP"),
			"WORK_STREET" => GetMessage("SONET_UP1_WORK_STREET"),
			"WORK_MAILBOX" => GetMessage("SONET_UP1_WORK_MAILBOX"),
		);

		if (ModuleManager::isModuleInstalled('forum'))
		{
			$arRes["FORUM_SHOW_NAME"] = GetMessage("SONET_UP1_FORUM_PREFIX").GetMessage("SONET_UP1_FORUM_SHOW_NAME");
			$arRes["FORUM_DESCRIPTION"] = GetMessage("SONET_UP1_FORUM_PREFIX").GetMessage("SONET_UP1_FORUM_DESCRIPTION");
			$arRes["FORUM_INTERESTS"] = GetMessage("SONET_UP1_FORUM_PREFIX").GetMessage("SONET_UP1_FORUM_INTERESTS");
			$arRes["FORUM_SIGNATURE"] = GetMessage("SONET_UP1_FORUM_PREFIX").GetMessage("SONET_UP1_FORUM_SIGNATURE");
			$arRes["FORUM_AVATAR"] = GetMessage("SONET_UP1_FORUM_PREFIX").GetMessage("SONET_UP1_FORUM_AVATAR");
			$arRes["FORUM_HIDE_FROM_ONLINE"] = GetMessage("SONET_UP1_FORUM_PREFIX").GetMessage("SONET_UP1_FORUM_HIDE_FROM_ONLINE");
			$arRes["FORUM_SUBSC_GET_MY_MESSAGE"] = GetMessage("SONET_UP1_FORUM_PREFIX").GetMessage("SONET_UP1_FORUM_SUBSC_GET_MY_MESSAGE");
		}

		if (ModuleManager::isModuleInstalled('blog'))
		{
			$arRes["BLOG_ALIAS"] = GetMessage("SONET_UP1_BLOG_PREFIX").GetMessage("SONET_UP1_BLOG_ALIAS");
			$arRes["BLOG_DESCRIPTION"] = GetMessage("SONET_UP1_BLOG_PREFIX").GetMessage("SONET_UP1_BLOG_DESCRIPTION");
			$arRes["BLOG_INTERESTS"] = GetMessage("SONET_UP1_BLOG_PREFIX").GetMessage("SONET_UP1_BLOG_INTERESTS");
			$arRes["BLOG_AVATAR"] = GetMessage("SONET_UP1_BLOG_PREFIX").GetMessage("SONET_UP1_BLOG_AVATAR");
		}

		return $arRes;
	}

	public static function GetFieldsMap($bAdditional = false)
	{
		$arUserFields = CSocNetUser::GetFields($bAdditional);
		return array_keys($arUserFields);
	}

	public static function CanProfileView($currentUserId, $arUser, $siteId = SITE_ID, $arContext = [])
	{
		global $USER;

		if (
			!is_array($arUser)
			&& (int)$arUser > 0
		)
		{
			$dbUser = \CUser::getById((int)$arUser);
			$arUser = $dbUser->fetch();
		}

		if (
			!is_array($arUser)
			|| !isset($arUser["ID"])
			|| (int)$arUser["ID"] <= 0
		)
		{
			return false;
		}

		if (
			(int)$currentUserId === (int)$USER->GetId()
			&& self::isCurrentUserModuleAdmin()
		)
		{
			return true;
		}

		if (self::OnGetProfileView($currentUserId, $arUser, $siteId, $arContext)) // only for email users
		{
			return true;
		}

		if (self::isCalendarSharingUser($currentUserId, $arUser, $siteId, $arContext)) // only for calendar sharing users
		{
			return true;
		}

		$bFound = false;
		foreach(GetModuleEvents("socialnetwork", "OnGetProfileView", true) as $arEvent)
		{
			if (ModuleManager::isModuleInstalled($arEvent['TO_MODULE_ID']))
			{
				$bFound = true;
				if (ExecuteModuleEventEx($arEvent, [ $currentUserId, $arUser, $siteId, $arContext, false ]) === true)
				{
					return true;
				}
			}
		}

		return (!$bFound);
	}

	public static function OnGetProfileView($currentUserId, $arUser, $siteId, $arContext)
	{
		if (!ModuleManager::isModuleInstalled('mail'))
		{
			return false;
		}

		$currentUserId = (int)$currentUserId;

		if (
			$currentUserId <= 0
			|| !is_array($arUser)
		)
		{
			return false;
		}

		if (
			isset($arUser['EXTERNAL_AUTH_ID'])
			&& $arUser['EXTERNAL_AUTH_ID'] === 'email'
			&& Loader::includeModule('intranet')
		)
		{
			$res = \Bitrix\Intranet\UserTable::getList([
				'filter' => [
					'=ID' => $currentUserId,
				],
				'select' => [ 'USER_TYPE' ],
			]);

			if (
				($currentUserFields = $res->fetch())
				&& $currentUserFields['USER_TYPE'] === 'employee'
			)
			{
				return true;
			}
		}

		if (
			!isset($arContext['ENTITY_TYPE'], $arContext['ENTITY_ID'], $arUser['ID'])
			|| (int)$arContext['ENTITY_ID'] <= 0
			|| $arContext['ENTITY_TYPE'] !== 'LOG_ENTRY'
			|| (int)$arUser['ID'] <= 0
		)
		{
			return false;
		}

		if (
			(
				isset($arUser['EXTERNAL_AUTH_ID'])
				&& $arUser['EXTERNAL_AUTH_ID'] === 'email'
			) // -> email user
			||
			(
				($res = \CUser::getById($currentUserId))
				&& ($currentUserFields = $res->fetch())
				&& ($currentUserFields['EXTERNAL_AUTH_ID'] === 'email')
			) // email user ->
		)
		{
			return self::CheckContext($currentUserId, $arUser['ID'], $arContext);
		}

		return false;
	}

	private static function isCalendarSharingUser($currentUserId, $arUser, $siteId, $arContext)
	{
		if (!ModuleManager::isModuleInstalled('calendar'))
		{
			return false;
		}

		$currentUserId = (int)$currentUserId;

		if (
			$currentUserId <= 0
			|| !is_array($arUser)
		)
		{
			return false;
		}

		if (
			isset($arUser['EXTERNAL_AUTH_ID'])
			&& $arUser['EXTERNAL_AUTH_ID'] === 'calendar_sharing'
			&& Loader::includeModule('intranet')
		)
		{
			$res = \Bitrix\Intranet\UserTable::getList([
				'filter' => [
					'=ID' => $currentUserId,
				],
				'select' => [ 'USER_TYPE' ],
			]);

			if (
				($currentUserFields = $res->fetch())
				&& $currentUserFields['USER_TYPE'] === 'employee'
			)
			{
				return true;
			}
		}

		if (
			!isset($arContext['ENTITY_TYPE'], $arContext['ENTITY_ID'], $arUser['ID'])
			|| (int)$arContext['ENTITY_ID'] <= 0
			|| $arContext['ENTITY_TYPE'] !== 'LOG_ENTRY'
			|| (int)$arUser['ID'] <= 0
		)
		{
			return false;
		}

		if (
			(
				isset($arUser['EXTERNAL_AUTH_ID'])
				&& $arUser['EXTERNAL_AUTH_ID'] === 'calendar_sharing'
			) // -> calendar_sharing user
			||
			(
				($res = \CUser::getById($currentUserId))
				&& ($currentUserFields = $res->fetch())
				&& ($currentUserFields['EXTERNAL_AUTH_ID'] === 'calendar_sharing')
			) // calendar_sharing user ->
		)
		{
			return self::CheckContext($currentUserId, $arUser['ID'], $arContext);
		}

		return false;
	}

	public static function CheckContext($currentUserId = false, $userId = false, $arContext = array())
	{
		if (
			(int)$currentUserId <= 0
			|| (int)$userId <= 0
			|| !is_array($arContext)
			|| empty($arContext["ENTITY_TYPE"])
			|| empty($arContext["ENTITY_ID"])
		)
		{
			return false;
		}

		if ($arContext["ENTITY_TYPE"] === "LOG_ENTRY")
		{
			$dbRes = CSocNetLogRights::GetList(
				array(),
				array(
					"LOG_ID" => (int)$arContext["ENTITY_ID"]
				)
			);

			$arLogEntryUserId = $arSonetGroupId = $arDepartmentId = array();
			$bIntranetInstalled = ModuleManager::IsModuleInstalled('intranet');

			while ($arRes = $dbRes->Fetch())
			{
				if (preg_match('/^U(\d+)$/', $arRes["GROUP_CODE"], $matches))
				{
					$arLogEntryUserId[] = $matches[1];
				}
				elseif (
					preg_match('/^SG(\d+)$/', $arRes["GROUP_CODE"], $matches)
					|| preg_match('/^SG(\d+)_'.SONET_ROLES_USER.'$/', $arRes["GROUP_CODE"], $matches)
					&& !in_array($matches[1], $arSonetGroupId)
				)
				{
					$arSonetGroupId[] = $matches[1];
				}
				elseif (
					$bIntranetInstalled
					&& preg_match('/^DR(\d+)$/', $arRes["GROUP_CODE"], $matches)
					&& !in_array($matches[1], $arDepartmentId)
				)
				{
					$arDepartmentId[] = $matches[1];
				}
				elseif ($arRes["GROUP_CODE"] === 'G2')
				{
					if (!empty($arContext['SITE_ID']))
					{
						$arLogSite = array();
						$rsSite = CSocNetLog::GetSite((int)$arContext["ENTITY_ID"]);
						while ($arSite = $rsSite->Fetch())
						{
							$arLogSite[] = $arSite["SITE_ID"];
						}

						return in_array($arContext['SITE_ID'], $arLogSite);
					}
				}
			}

			if (
				in_array($currentUserId, $arLogEntryUserId)
				&& in_array($userId, $arLogEntryUserId)
			)
			{
				return true;
			}

			if (in_array($userId, $arLogEntryUserId))
			{
				if (!empty($arSonetGroupId))
				{
					foreach($arSonetGroupId as $groupId)
					{
						if (CSocNetUserToGroup::GetUserRole($currentUserId, $groupId) <= SONET_ROLES_USER)
						{
							return true;
						}
					}
				}

				if (
					!empty($arDepartmentId)
					&& Loader::includeModule('intranet')
				)
				{
					$arDepartmentUserId = array();

					$rsDepartmentUserId = \Bitrix\Intranet\Util::getDepartmentEmployees(array(
						'DEPARTMENTS' => $arDepartmentId,
						'RECURSIVE' => 'Y',
						'ACTIVE' => 'Y',
						'CONFIRMED' => 'Y',
						'SELECT' => array('ID')
					));

					while ($arUser = $rsDepartmentUserId->Fetch())
					{
						$arDepartmentUserId[] = $arUser["ID"];
					}

					if (in_array($currentUserId, $arDepartmentUserId))
					{
						return true;
					}
				}
			}

			$rsLog = CSocNetLog::GetList(
				array(),
				array(
					"ID" => (int)$arContext["ENTITY_ID"]
				),
				false,
				false,
				array(
					"USER_ID"
				)
			);
			if ($arLog = $rsLog->Fetch())
			{
				return (
					(
						in_array($currentUserId, $arLogEntryUserId)
						&& ($userId == $arLog["USER_ID"])
					)
					|| (
						in_array($userId, $arLogEntryUserId)
						&& ($currentUserId == $arLog["USER_ID"])
					)
				);
			}
		}

		return false;
	}
}
