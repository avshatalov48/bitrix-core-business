<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Deprecated;

Loc::loadMessages(__FILE__);

class CAllSocNetLog
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		static $arSiteWorkgroupsPage;

		global $DB, $APPLICATION, $USER_FIELD_MANAGER;

		if (
			!$arSiteWorkgroupsPage
			&& IsModuleInstalled("extranet")
			&& isset($arFields["ENTITY_TYPE"])
			&& $arFields["ENTITY_TYPE"] === SONET_ENTITY_GROUP)
		{
			$rsSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
			while($arSite = $rsSite->Fetch())
			{
				$arSiteWorkgroupsPage[$arSite["ID"]] = COption::GetOptionString("socialnetwork", "workgroups_page", $arSite["DIR"]."workgroups/", $arSite["ID"]);
			}
		}

		if ($ACTION !== "ADD" && (int)$ID <= 0)
		{
			$APPLICATION->ThrowException("System error 870164", "ERROR");
			return false;
		}

		$newEntityType = '';

		if ((is_set($arFields, "ENTITY_TYPE") || $ACTION === "ADD") && (string)$arFields["ENTITY_TYPE"] === '')
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_EMPTY_ENTITY_TYPE"), "EMPTY_ENTITY_TYPE");
			return false;
		}

		if (is_set($arFields, "ENTITY_TYPE"))
		{
			if (!in_array((string)$arFields["ENTITY_TYPE"], CSocNetAllowed::GetAllowedEntityTypes(), true))
			{
				$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_ERROR_NO_ENTITY_TYPE"), "ERROR_NO_ENTITY_TYPE");
				return false;
			}

			$newEntityType = (string)$arFields["ENTITY_TYPE"];
		}

		if ((is_set($arFields, "ENTITY_ID") || $ACTION === "ADD") && (int)$arFields["ENTITY_ID"] <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_EMPTY_ENTITY_ID"), "EMPTY_ENTITY_ID");
			return false;
		}

		if (is_set($arFields, "ENTITY_ID"))
		{
			if ($newEntityType === '' && $ID > 0)
			{
				$arRe = self::GetByID($ID);
				if ($arRe)
				{
					$newEntityType = (string)$arRe["ENTITY_TYPE"];
				}
			}
			if ($newEntityType === '')
			{
				$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_ERROR_CALC_ENTITY_TYPE"), "ERROR_CALC_ENTITY_TYPE");
				return false;
			}

			if ($newEntityType === SONET_ENTITY_GROUP)
			{
				if (!CSocNetGroup::GetByID($arFields["ENTITY_ID"]))
				{
					$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
			elseif ($newEntityType === SONET_ENTITY_USER)
			{
				$dbResult = CUser::GetByID($arFields["ENTITY_ID"]);
				if (!$dbResult->Fetch())
				{
					$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
		}

		if (
			$ACTION === "ADD"
			&& (
				!is_set($arFields, "SITE_ID")
				|| (
					(is_array($arFields["SITE_ID"]) && count($arFields["SITE_ID"]) <= 0)
					|| (!is_array($arFields["SITE_ID"]) && $arFields["SITE_ID"] == '')
				)
			)
		)
		{
			if ($newEntityType === SONET_ENTITY_GROUP)
			{
				$arSites = array();
				$rsGroupSite = CSocNetGroup::GetSite($arFields["ENTITY_ID"]);
				while($arGroupSite = $rsGroupSite->Fetch())
				{
					$arSites[] = $arGroupSite["LID"];
				}
				$arFields["SITE_ID"] = $arSites;
			}
			else
			{
				$arFields["SITE_ID"] = array(SITE_ID);
			}
		}

		if (
			isset($arFields["TAG"])
			&& !is_array($arFields["TAG"])
		)
		{
			$arFields["TAG"] = array($arFields["TAG"]);
		}

		if ((is_set($arFields, "EVENT_ID") || $ACTION === "ADD") && $arFields["EVENT_ID"] == '')
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_EMPTY_EVENT_ID"), "EMPTY_EVENT_ID");
			return false;
		}

		if (is_set($arFields, "EVENT_ID"))
		{
			$arFields["EVENT_ID"] = mb_strtolower($arFields["EVENT_ID"]);
			$arEvent = CSocNetLogTools::FindLogEventByID(
				$arFields["EVENT_ID"],
				$arFields["ENTITY_TYPE"] ?? false
			);
			if (!$arEvent)
			{
				$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_ERROR_NO_FEATURE_ID"), "ERROR_NO_FEATURE");
				return false;
			}
		}

		if (is_set($arFields, "USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "LOG_DATE") && (!$DB->IsDate($arFields["LOG_DATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_EMPTY_DATE_CREATE"), "EMPTY_LOG_DATE");
			return false;
		}

		if ((is_set($arFields, "TITLE") || $ACTION === "ADD") && $arFields["TITLE"] == '')
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_EMPTY_TITLE"), "EMPTY_TITLE");
			return false;
		}

		if ((is_set($arFields, "TITLE") || $ACTION === "ADD") && is_string($arFields['TITLE'] ?? null))
		{
			$arFields['TITLE'] = \Bitrix\Main\Text\Emoji::encode($arFields['TITLE']);
		}

		if (
			isset($arFields["CONTEXT_USER_ID"])
			&& (int)$arFields["CONTEXT_USER_ID"] > 0
		)
		{
			$contextUserId = (int)$arFields["CONTEXT_USER_ID"];
		}
		elseif (
			isset($arFields["USER_ID"])
			&& (int)$arFields["USER_ID"] > 0
		)
		{
			$contextUserId = (int)$arFields["USER_ID"];
		}
		else
		{
			$contextUserId = false;
		}

		if (!$USER_FIELD_MANAGER->CheckFields("SONET_LOG", $ID, $arFields, $contextUserId))
		{
			return false;
		}

		if (
			is_array($arSiteWorkgroupsPage)
			&& isset($arFields["URL"]))
		{
			foreach($arSiteWorkgroupsPage as $groups_page)
			{
				if (mb_strpos($arFields["URL"], $groups_page) === 0)
				{
					$arFields["URL"] = "#GROUPS_PATH#".mb_substr($arFields["URL"], mb_strlen($groups_page));
				}
			}
		}

		return True;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetByID($ID)
	{
		global $APPLICATION;

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_GL_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$dbResult = CSocNetLog::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}

	public static function MakeTitle($titleTemplate, $title, $url = '', $bHtml = true)
	{
		$url = (string)$url;
		$titleTemplate = (string)$titleTemplate;

		if ($url !== '')
		{
			$title = (
				$bHtml
					? '<a href="' . $url . '">' . $title . '</a>'
					: $title . ' [' . $url . ']'
			);
		}

		if ($titleTemplate !== '')
		{
			return (
				(mb_strpos($titleTemplate, "#TITLE#") !== false)
					? Str_Replace("#TITLE#", $title, $titleTemplate)
					: $titleTemplate . ' "' . $title . '"'
			);
		}

		return $title;
	}

	/***************************************/
	/**********  SEND EVENTS  **************/
	/***************************************/
	/**
	 * @deprecated
	 */
	public static function __InitUserTmp($userID)
	{
		return Deprecated\Log::__InitUserTmp($userID);
	}

	/**
	 * @deprecated
	 */
	public static function __InitUsersTmp($message, $titleTemplate1, $titleTemplate2)
	{
		return Deprecated\Log::__InitUsersTmp($message, $titleTemplate1, $titleTemplate2);
	}

	/**
	 * @deprecated
	 */
	public static function __InitGroupTmp($groupID)
	{
		return Deprecated\Log::__InitGroupTmp($groupID);
	}

	/**
	 * @deprecated
	 */
	public static function __InitGroupsTmp($message, $titleTemplate1, $titleTemplate2)
	{
		return Deprecated\Log::__InitGroupsTmp($message, $titleTemplate1, $titleTemplate2);
	}

	public static function SendEventAgent($ID, $mailTemplate = "SONET_NEW_EVENT"): string
	{
		return (
			CSocNetLog::SendEvent($ID, $mailTemplate, 0, true)
				? ""
				: "CSocNetLog::SendEventAgent(".$ID.", '".$mailTemplate."');"
		);
	}

	public static function SendEvent(
		$ID,
		$mailTemplate = "SONET_NEW_EVENT",
		$tmpId = 0,
		$bAgent = false
	)
	{
		global $APPLICATION;

		$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

		$ID = (int)$ID;

		if ($ID <= 0)
		{
			return false;
		}

		$arFilter = array("ID" => $ID);

		$dbLog = CSocNetLog::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "EVENT_ID", "LOG_DATE", "TITLE_TEMPLATE", "TITLE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID", "CALLBACK_FUNC", "SITE_ID", "PARAMS", "SOURCE_ID", "GROUP_NAME", "CREATED_BY_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_LOGIN", "LOG_SOURCE_ID"),
			array("MIN_ID_JOIN" => true)
		);
		$arLog = $dbLog->Fetch();
		if (!$arLog)
		{
			return $bAgent;
		}

		if (MakeTimeStamp($arLog["LOG_DATE"]) > (time() + CTimeZone::GetOffset()))
		{
			$agent = "CSocNetLog::SendEventAgent(".$ID.", '".CUtil::addslashes($mailTemplate)."');";
			$rsAgents = CAgent::GetList(array("ID"=>"DESC"), array("NAME" => $agent));
			if(!$rsAgents->Fetch())
			{
				$res = CAgent::AddAgent($agent, "socialnetwork", "N", 0, $arLog["LOG_DATE"], "Y", $arLog["LOG_DATE"]);
				if(!$res)
				{
					$APPLICATION->ResetException();
				}
			}
			elseif ($bAgent)
			{
				CAgent::RemoveAgent($agent, "socialnetwork");
				CAgent::AddAgent($agent, "socialnetwork", "N", 0, $arLog["LOG_DATE"], "Y", $arLog["LOG_DATE"]);
				return true;
			}
			return false;
		}

		$arEvent = CSocNetLogTools::FindLogEventByID($arLog["EVENT_ID"], $arLog["ENTITY_TYPE"]);
		if (
			$arEvent
			&& isset($arEvent["CLASS_FORMAT"], $arEvent["METHOD_FORMAT"])
		)
		{
			$dbSiteCurrent = CSite::GetByID(SITE_ID);
			if (
				($arSiteCurrent = $dbSiteCurrent->Fetch())
				&& $arSiteCurrent["LANGUAGE_ID"] !== LANGUAGE_ID
			)
			{
				$arLog["MAIL_LANGUAGE_ID"] = $arSiteCurrent["LANGUAGE_ID"];
			}

			$arLog["FIELDS_FORMATTED"] = call_user_func(array($arEvent["CLASS_FORMAT"], $arEvent["METHOD_FORMAT"]), $arLog, array(), true);
		}

		if (
			isset(
				$arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["HAS_MY"],
				$arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["CLASS_OF"],
				$arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["METHOD_OF"]
			)
			&& $arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["HAS_MY"] === "Y"
			&& $arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["CLASS_OF"] !== ''
			&& $arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["METHOD_OF"] !== ''
			&& method_exists(
				$arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["CLASS_OF"],
				$arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["METHOD_OF"]
			)
		)
		{
			$arOfEntities = call_user_func(array($arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["CLASS_OF"], $arSocNetAllowedSubscribeEntityTypesDesc[$arLog["ENTITY_TYPE"]]["METHOD_OF"]), $arLog["ENTITY_ID"]);
		}
		else
		{
			$arOfEntities = false;
		}

		$hasAccessAll = false;


		$arUserIdToPush = ($arFields["USERS_TO_PUSH"] ?? []);

		CSocNetLog::CounterIncrement(array(
			'ENTITY_ID' => $arLog["ID"],
			'EVENT_ID' => $arLog["EVENT_ID"],
			'OF_ENTITIES' => $arOfEntities,
			'TYPE' => CSocNetLogCounter::TYPE_LOG_ENTRY,
			'FOR_ALL_ACCESS' => $hasAccessAll,
			'USERS_TO_PUSH' => (
				$hasAccessAll
				|| empty($arUserIdToPush)
				|| count($arUserIdToPush) > 20
					? []
					: $arUserIdToPush
			)
		));

		return true;
	}

	public static function CounterIncrement(
		$entityId,
		$eventId = '',
		$entitiesList = false,
		$type = CSocNetLogCounter::TYPE_LOG_ENTRY,
		$forAllAccess = false,
		$userIdToPushList = []
	): void
	{
		if (
			is_array($entityId)
			&& isset($entityId["ENTITY_ID"])
		)
		{
			$arFields = $entityId;
			$entityId = $arFields["ENTITY_ID"];
			$eventId = (string)($arFields["EVENT_ID"] ?? '');
			$type = ($arFields["TYPE"] ?? CSocNetLogCounter::TYPE_LOG_ENTRY);
			$forAllAccess = ($arFields["FOR_ALL_ACCESS"] ?? false);
			$userIdToPushList = ($arFields["USERS_TO_PUSH"] ?? []);
			$sendToAuthor = (
				isset($arFields['SEND_TO_AUTHOR'])
				&& $arFields['SEND_TO_AUTHOR'] === 'Y'
			);
		}
		else
		{
			$sendToAuthor = false;
		}

		if ((int)$entityId <= 0)
		{
			return;
		}

		if (
			$eventId === "tasks"
			&& !\Bitrix\Socialnetwork\ComponentHelper::checkLivefeedTasksAllowed()
		)
		{
			return;
		}

		if (!$forAllAccess)
		{
			CUserCounter::IncrementWithSelect(
				CSocNetLogCounter::GetSubSelect2(
					$entityId,
					[
						"TYPE" => $type,
						"FOR_ALL_ACCESS" => false,
						"MULTIPLE" => "Y",
						"SET_TIMESTAMP" => "Y",
						"SEND_TO_AUTHOR" => ($sendToAuthor ? 'Y' : 'N')
					]
				),
				false,
				[
					'SET_TIMESTAMP' => 'Y',
				]
			);
		}
		else // for all, mysql only
		{
			$tag = time();

			// don't send to pull for all records
			CUserCounter::IncrementWithSelect(
				CSocNetLogCounter::GetSubSelect2(
					$entityId,
					[
						"TYPE" => $type,
						"FOR_ALL_ACCESS_ONLY" => true,
						"TAG_SET" => $tag,
						"MULTIPLE" => "Y",
						"SET_TIMESTAMP" => "Y",
						"SEND_TO_AUTHOR" => ($sendToAuthor ? 'Y' : 'N')
					]
				),
				false,
				[
					"SET_TIMESTAMP" => "Y",
					"TAG_SET" => $tag,
				]
			);

			// send to pull discreet records (not for all)
			CUserCounter::IncrementWithSelect(
				CSocNetLogCounter::GetSubSelect2(
					$entityId,
					[
						"TYPE" => $type,
						"FOR_ALL_ACCESS_ONLY" => false,
						"MULTIPLE" => "Y",
						"SET_TIMESTAMP" => "Y",
						"SEND_TO_AUTHOR" => ($sendToAuthor ? 'Y' : 'N')
					]
				),
				true,
				[
					"SET_TIMESTAMP" => "Y",
					"TAG_CHECK" => $tag,
				]
			);
		}

		if ($eventId === "blog_post_important")
		{
			CUserCounter::IncrementWithSelect(
				CSocNetLogCounter::GetSubSelect2(
					$entityId,
					[
						"TYPE" => CSocNetLogCounter::TYPE_LOG_ENTRY,
						"CODE" => "'BLOG_POST_IMPORTANT'",
						"FOR_ALL_ACCESS" => $forAllAccess,
						"MULTIPLE" => "N",
						"SEND_TO_AUTHOR" => ($sendToAuthor ? 'Y' : 'N')
					]
				)
			);
		}

		if ($type === CSocNetLogCounter::TYPE_LOG_COMMENT)
		{
			\Bitrix\Socialnetwork\Item\LogSubscribe::sendPush(array(
				'commentId' => $entityId
			));
		}
	}

	public static function CounterDecrement(
		$logId,
		$eventId = '',
		$type = CSocNetLogCounter::TYPE_LOG_ENTRY,
		$forAllAccess = false
	): void
	{
		$logId = (int)$logId;
		if ($logId <= 0)
		{
			return;
		}

		CUserCounter::IncrementWithSelect(
			CSocNetLogCounter::GetSubSelect2(
				$logId,
				[
					"TYPE" => $type,
					"DECREMENT" => true,
					"FOR_ALL_ACCESS" => $forAllAccess,
				]
			)
		);

		if ($eventId === "blog_post_important")
		{
			CUserCounter::IncrementWithSelect(
				CSocNetLogCounter::GetSubSelect2(
					$logId,
					[
						"TYPE" => CSocNetLogCounter::TYPE_LOG_ENTRY,
						"CODE" => "'BLOG_POST_IMPORTANT'",
						"DECREMENT" => true,
						"FOR_ALL_ACCESS" => $forAllAccess,
					]
				)
			);
		}
	}

	public static function ClearOldAgent()
	{
		return "";
	}

	public static function GetSign($url, $userID = false, $site_id = false)
	{
		global $USER;

		if (!$url || trim($url) === '')
		{
			return false;
		}

		if (!$userID)
		{
			$userID = $USER->GetID();
		}

		if ($hash = CUser::GetHitAuthHash($url, $userID))
		{
			return $hash;
		}

		return CUser::AddHitAuthHash($url, $userID, $site_id);
	}

	public static function OnSocNetLogFormatEvent($arEvent, $arParams)
	{
		if (
			$arEvent["EVENT_ID"] === "system"
			|| $arEvent["EVENT_ID"] === "system_friends"
			|| $arEvent["EVENT_ID"] === "system_groups"
		)
		{
			$arEvent["TITLE_TEMPLATE"] = "";
			$arEvent["URL"] = "";

			switch ($arEvent["TITLE"])
			{
				case "join":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_JOIN1"),
						Loc::getMessage("SONET_GL_TITLE_JOIN2"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "unjoin":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_UNJOIN1"),
						Loc::getMessage("SONET_GL_TITLE_UNJOIN2"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "moderate":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_MODERATE1"),
						Loc::getMessage("SONET_GL_TITLE_MODERATE2"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "unmoderate":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_UNMODERATE1"),
						Loc::getMessage("SONET_GL_TITLE_UNMODERATE2"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "owner":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_OWNER1"),
						Loc::getMessage("SONET_GL_TITLE_OWNER1"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "friend":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_FRIEND1"),
						Loc::getMessage("SONET_GL_TITLE_FRIEND1"),
						$arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "unfriend":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_UNFRIEND1"),
						Loc::getMessage("SONET_GL_TITLE_UNFRIEND1"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "group":
					[$titleTmp, $messageTmp] = CSocNetLog::InitGroupsTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_GROUP1"),
						Loc::getMessage("SONET_GL_TITLE_GROUP1"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "ungroup":
					[$titleTmp, $messageTmp] = CSocNetLog::InitGroupsTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_UNGROUP1"),
						Loc::getMessage("SONET_GL_TITLE_UNGROUP1"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "exclude_user":
					[$titleTmp, $messageTmp] = CSocNetLog::InitGroupsTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_EXCLUDE_USER1"),
						Loc::getMessage("SONET_GL_TITLE_EXCLUDE_USER1"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "exclude_group":
					[$titleTmp, $messageTmp] = CSocNetLog::InitUsersTmp(
						$arEvent["MESSAGE"],
						Loc::getMessage("SONET_GL_TITLE_EXCLUDE_GROUP1"),
						Loc::getMessage("SONET_GL_TITLE_EXCLUDE_GROUP1"),
						$arParams
					);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				default:
					break;
			}
		}
		return $arEvent;
	}

	public static function InitUserTmp($userID, $arParams, $bCurrentUserIsAdmin = "unknown", $bRSS = false)
	{
		global $USER;

		$title = "";
		$message = "";
		$bUseLogin = ($arParams['SHOW_LOGIN'] !== "N");

		$dbUser = CUser::GetByID($userID);
		if ($arUser = $dbUser->Fetch())
		{
			if ($bCurrentUserIsAdmin === "unknown")
			{
				$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
			}

			$canViewProfile = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUser["ID"], "viewprofile", $bCurrentUserIsAdmin);
			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));

			if (!$bRSS && $canViewProfile)
			{
				$title .= "<a href=\"".$pu."\">";
			}

			$title .= CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin);
			if (!$bRSS && $canViewProfile)
			{
				$title .= "</a>";
			}

			if ((int)$arUser["PERSONAL_PHOTO"] <= 0)
			{
				switch ($arUser["PERSONAL_GENDER"])
				{
					case "M":
						$suffix = "male";
						break;
					case "F":
						$suffix = "female";
							break;
					default:
						$suffix = "unknown";
				}
				$arUser["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
			}
			$arImage = CSocNetTools::InitImage($arUser["PERSONAL_PHOTO"], 100, "/bitrix/images/socialnetwork/nopic_user_100.gif", 100, $pu, $canViewProfile);

			$message = $arImage["IMG"];
		}

		return array($title, $message);
	}

	public static function InitUsersTmp($message, $titleTemplate1, $titleTemplate2, $arParams, $bCurrentUserIsAdmin = "unknown", $bRSS = false)
	{
		$arUsersID = explode(",", $message);

		$message = "";
		$title = "";

		$bFirst = true;
		$count = 0;

		if ($bCurrentUserIsAdmin === "unknown")
		{
			$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
		}

		foreach ($arUsersID as $userID)
		{
			[ $titleTmp, $messageTmp ] = self::InitUserTmp($userID, $arParams, $bCurrentUserIsAdmin, $bRSS);

			$titleTmp = (string)$titleTmp;
			$messageTmp = (string)$messageTmp;

			if ($titleTmp !== '')
			{
				if (!$bFirst)
				{
					$title .= ", ";
				}
				$title .= $titleTmp;
				$count++;
			}

			if ($messageTmp !== '')
			{
				if (!$bFirst)
				{
					$message .= " ";
				}
				$message .= $messageTmp;
			}

			$bFirst = false;
		}
		return array(Str_Replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1)), $message);
	}

	public static function InitGroupTmp($groupID, $arParams, $bRSS = false)
	{
		$title = "";
		$message = "";

		$arGroup = CSocNetGroup::GetByID($groupID);
		if ($arGroup)
		{
			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroup["ID"]));

			if (!$bRSS)
			{
				$title .= "<a href=\"".$pu."\">";
			}
			$title .= $arGroup["NAME"];
			if (!$bRSS)
			{
				$title .= "</a>";
			}

			if ((int)$arGroup["IMAGE_ID"] <= 0)
			{
				$arGroup["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);
			}

			$arImage = CSocNetTools::InitImage($arGroup["IMAGE_ID"], 100, "/bitrix/images/socialnetwork/nopic_group_100.gif", 100, $pu, true);

			$message = $arImage["IMG"];
		}

		return array($title, $message);
	}

	public static function InitGroupsTmp($message, $titleTemplate1, $titleTemplate2, $arParams, $bRSS = false)
	{
		$arGroupsID = explode(",", $message);

		$message = "";
		$title = "";

		$bFirst = true;
		$count = 0;
		foreach ($arGroupsID as $groupID)
		{
			[ $titleTmp, $messageTmp ] = CSocNetLog::InitGroupTmp($groupID, $arParams, $bRSS);

			$titleTmp = (string)$titleTmp;
			$messageTmp = (string)$messageTmp;

			if ($titleTmp !== '')
			{
				if (!$bFirst)
				{
					$title .= ", ";
				}
				$title .= $titleTmp;
				$count++;
			}

			if ($messageTmp !== '')
			{
				if (!$bFirst)
				{
					$message .= " ";
				}
				$message .= $messageTmp;
			}

			$bFirst = false;
		}

		return array(Str_Replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1)), $message);
	}

	public static function ShowGroup($arEntityDesc, $strEntityURL, $arParams)
	{
		return CSocNetLogTools::ShowGroup($arEntityDesc, $strEntityURL, $arParams);
	}

	public static function ShowUser($arEntityDesc, $strEntityURL, $arParams)
	{
		return CSocNetLogTools::ShowUser($arEntityDesc, $strEntityURL, $arParams);
	}

	public static function FormatEvent_FillTooltip($arFields, $arParams)
	{
		return CSocNetLogTools::FormatEvent_FillTooltip($arFields, $arParams);
	}

	public static function FormatEvent_CreateAvatar($arFields, $arParams, $source = "CREATED_BY_")
	{
		return CSocNetLogTools::FormatEvent_CreateAvatar($arFields, $arParams, $source);
	}

	public static function FormatEvent_IsMessageShort($message, $short_message = false)
	{
		return CSocNetLogTools::FormatEvent_IsMessageShort($message, $short_message);
	}

	public static function FormatEvent_BlogPostComment($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_Blog($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_Forum($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_Forum($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_Photo($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_Photo($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_Files($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_Files($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_Task($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_Task($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_SystemGroups($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_SystemGroups($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_SystemFriends($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_SystemFriends($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_System($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_System($arFields, $arParams, $bMail);
	}

	public static function FormatEvent_Microblog($arFields, $arParams, $bMail = false)
	{
		return CSocNetLogTools::FormatEvent_Microblog($arFields, $arParams, $bMail);
	}

	public static function SetCacheLastLogID($id)
	{
		CSocNetLogTools::SetCacheLastLogID("log", $id);
	}

	public static function GetCacheLastLogID()
	{
		return CSocNetLogTools::GetCacheLastLogID("log");
	}

	public static function SetUserCache($user_id, $max_id, $max_viewed_id, $count)
	{
		CSocNetLogTools::SetUserCache("log", $user_id, $max_id, $max_viewed_id, $count);
	}

	public static function GetUserCache($user_id)
	{
		return CSocNetLogTools::GetUserCache("log", $user_id);
	}

	public static function GetSite($log_id)
	{
		global $DB;
		$strSql = "SELECT L.*, LS.* FROM b_sonet_log_site LS, b_lang L WHERE L.LID=LS.SITE_ID AND LS.LOG_ID=" . (int)$log_id;
		return $DB->Query($strSql);
	}
	
	public static function GetSimpleOrQuery($val, $key, $strOperation, $strNegative, $OrFields, &$arFields, &$arFilter)
	{
		global $DB;

		if ($strNegative !== "Y")
		{
			$arOrFields = explode("|", $OrFields);
			if (count($arOrFields) > 1)
			{
				$strOrFields = "";
				foreach($arOrFields as $i => $field)
				{
					if ($i > 0)
					{
						$strOrFields .= " OR ";
					}
					$strOrFields .= "(".$field." ".$strOperation." '".$DB->ForSql($val)."')";
				}
				return $strOrFields;
			}

			return false;
		}

		return false;
	}
}
