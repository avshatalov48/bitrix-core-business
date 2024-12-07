<?php

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class CAllSocNetUserRelations
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $arSocNetAllowedRelations;

		if ($ACTION != "ADD" && intval($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "FIRST_USER_ID") || $ACTION=="ADD") && intval($arFields["FIRST_USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_FIRST_USER_ID"), "EMPTY_FIRST_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "FIRST_USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["FIRST_USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_ERROR_NO_FIRST_USER_ID"), "ERROR_NO_FIRST_USER_ID");
				return false;
			}
		}

		if ((is_set($arFields, "SECOND_USER_ID") || $ACTION=="ADD") && intval($arFields["SECOND_USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SECOND_USER_ID"), "EMPTY_SECOND_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "SECOND_USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["SECOND_USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_ERROR_NO_SECOND_USER_ID"), "ERROR_NO_SECOND_USER_ID");
				return false;
			}
		}

		if ((is_set($arFields, "RELATION") || $ACTION=="ADD") && $arFields["RELATION"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_RELATION"), "EMPTY_RELATION");
			return false;
		}
		elseif (is_set($arFields, "RELATION") && !in_array($arFields["RELATION"], $arSocNetAllowedRelations))
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["RELATION"], GetMessage("SONET_UR_ERROR_NO_RELATION")), "ERROR_NO_RELATION");
			return false;
		}
		elseif (is_set($arFields, "RELATION") && $arFields["RELATION"] == SONET_RELATIONS_TYPE_FRIENDS2)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_ERROR_RELATION_FRIENDS2"), "ERROR_RELATION_FRIENDS2");
			return false;
		}

		if ((is_set($arFields, "INITIATED_BY") || $ACTION=="ADD") && !in_array($arFields["INITIATED_BY"], array("F", "S")))
			$arFields["INITIATED_BY"] = "F";

		if (is_set($arFields, "DATE_CREATE") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_DATE_CREATE"), "EMPTY_DATE_CREATE");
			return false;
		}

		if (is_set($arFields, "DATE_UPDATE") && (!$DB->IsDate($arFields["DATE_UPDATE"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_DATE_UPDATE"), "EMPTY_DATE_UPDATE");
			return false;
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);
		$bSuccess = True;

		$rsUser2UserOld = $DB->Query("SELECT * FROM b_sonet_user_relations WHERE ID = ".$ID."");
		if($arUser2UserOld = $rsUser2UserOld->Fetch())
		{
			CSocNetSearch::OnUserRelationsChange($arUser2UserOld["FIRST_USER_ID"]);
			CSocNetSearch::OnUserRelationsChange($arUser2UserOld["SECOND_USER_ID"]);
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserRelationsDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		$events = GetModuleEvents("socialnetwork", "OnSocNetUserRelationsDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));

		if ($bSuccess)
		{
			$arRelation = CSocNetUserRelations::GetByID($ID);
			$bSuccess = $DB->Query("DELETE FROM b_sonet_user_relations WHERE ID = ".$ID."", true);
		}

		if ($bSuccess)
		{
			if ($arRelation && $arRelation["RELATION"] == SONET_RELATIONS_FRIEND)
				$GLOBALS["DB"]->Query("DELETE FROM b_sonet_event_user_view WHERE
					ENTITY_TYPE = '".SONET_ENTITY_USER."'
					AND (
						(USER_ID = ".$arRelation["FIRST_USER_ID"]." AND ENTITY_ID = ".$arRelation["SECOND_USER_ID"].")
						OR (USER_ID = ".$arRelation["SECOND_USER_ID"]." AND ENTITY_ID = ".$arRelation["FIRST_USER_ID"].")
						OR (ENTITY_ID = ".$arRelation["FIRST_USER_ID"]." AND USER_IM_ID = ".$arRelation["SECOND_USER_ID"].")
						OR (ENTITY_ID = ".$arRelation["SECOND_USER_ID"]." AND USER_IM_ID = ".$arRelation["FIRST_USER_ID"].")
						OR (USER_ID = ".$arRelation["FIRST_USER_ID"]." AND USER_IM_ID = ".$arRelation["SECOND_USER_ID"].")
						OR (USER_ID = ".$arRelation["SECOND_USER_ID"]." AND USER_IM_ID = ".$arRelation["FIRST_USER_ID"].")
						)", true);
		}

		return $bSuccess;
	}

	public static function DeleteNoDemand($userID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($userID))
			return false;

		$userID = intval($userID);
		$bSuccess = True;

		$rsUser2UserOld = $DB->Query("SELECT * FROM b_sonet_user_relations WHERE FIRST_USER_ID = ".$userID." OR SECOND_USER_ID = ".$userID."");
		while($arUser2UserOld = $rsUser2UserOld->Fetch())
		{
			CSocNetSearch::OnUserRelationsChange($arUser2UserOld["FIRST_USER_ID"]);
			CSocNetSearch::OnUserRelationsChange($arUser2UserOld["SECOND_USER_ID"]);
		}

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_user_relations WHERE FIRST_USER_ID = ".$userID." OR SECOND_USER_ID = ".$userID."", true);

		if ($bSuccess)
			$DB->Query("DELETE FROM b_sonet_event_user_view WHERE
				ENTITY_TYPE = '".SONET_ENTITY_USER."'
				AND (
					USER_ID = ".$userID."
					OR ENTITY_ID = ".$userID."
					OR USER_IM_ID = ".$userID."
				)", true);

		CSocNetUserRelations::__SpeedFileDelete($userID);

		return $bSuccess;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetByID($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$dbResult = CSocNetUserRelations::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}

	public static function GetByUserID($user1ID, $user2ID)
	{
		global $DB;

		$user1ID = intval($user1ID);
		if ($user1ID <= 0)
			return false;
		$user2ID = intval($user2ID);
		if ($user2ID <= 0)
			return false;

		$strSql =
			"SELECT ID, FIRST_USER_ID, SECOND_USER_ID, RELATION, DATE_CREATE, DATE_UPDATE, MESSAGE, INITIATED_BY ".
			"FROM b_sonet_user_relations ".
			"WHERE FIRST_USER_ID = ".$user1ID." AND SECOND_USER_ID = ".$user2ID." ".
			"	OR FIRST_USER_ID = ".$user2ID." AND SECOND_USER_ID = ".$user1ID." ";

		$dbResult = $DB->Query($strSql);
		if ($arResult = $dbResult->Fetch())
			return $arResult;

		return False;
	}

	public static function GetRelatedUsers($userID, $relation, $arNavStartParams = false, $bActiveOnly = "N")
	{
		global $DB, $arSocNetAllowedRelations;

		$userID = intval($userID);
		if ($userID <= 0)
			return false;

		if (!in_array($relation, $arSocNetAllowedRelations))
			return false;

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
			$arOrderBy = array(
				"RAND" => "ASC"
			);
		else
			$arOrderBy = array(
				"DATE_UPDATE" => "DESC"
			);

		$dbResult = CSocNetUserRelations::GetList(
			$arOrderBy,
			array(
				"USER_ID" => $userID,
				"RELATION" => $relation,
				"ACTIVE_ONLY" => $bActiveOnly
			),
			false,
			$arNavStartParams,
			array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY",
				"FIRST_USER_NAME", "FIRST_USER_LAST_NAME", "FIRST_USER_PERSONAL_PHOTO", "FIRST_USER_PERSONAL_GENDER", "FIRST_USER_SECOND_NAME", "FIRST_USER_LOGIN", "FIRST_USER_EMAIL", "FIRST_USER_IS_ONLINE",
				"SECOND_USER_NAME", "SECOND_USER_LAST_NAME", "SECOND_USER_PERSONAL_PHOTO", "SECOND_USER_PERSONAL_GENDER", "SECOND_USER_SECOND_NAME", "SECOND_USER_LOGIN", "SECOND_USER_EMAIL", "SECOND_USER_IS_ONLINE"
			)
		);

		return $dbResult;
	}

	/***************************************/
	/**********  COMMON METHODS  ***********/
	/***************************************/
	public static function GetRelation($firstUserID, $secondUserID)
	{
		global $DB;

		static $arSocNetURNCache = array();
		static $arSocNetUserRelationsCache1 = array();

		$firstUserID = intval($firstUserID);
		if ($firstUserID <= 0)
		{
			return false;
		}

		$secondUserID = intval($secondUserID);
		if ($secondUserID <= 0)
		{
			return false;
		}

		if (array_key_exists($firstUserID, $arSocNetURNCache))
		{
			if (array_key_exists($secondUserID, $arSocNetURNCache[$firstUserID]))
			{
				return $arSocNetURNCache[$firstUserID][$secondUserID];
			}
			elseif(count($arSocNetURNCache[$firstUserID]) != 100)
			{
				return false;
			}
		}
		elseif (array_key_exists($secondUserID, $arSocNetURNCache))
		{
			if (array_key_exists($firstUserID, $arSocNetURNCache[$secondUserID]))
			{
				return $arSocNetURNCache[$secondUserID][$firstUserID];
			}
			elseif(count($arSocNetURNCache[$secondUserID]) != 100)
			{
				return false;
			}
		}

		// get top N relations of user1		
		$arSocNetURNCache[$firstUserID] = array();
		$dbResult = CSocNetUserRelations::GetRelationsTop($firstUserID, 100);
		while ($arResult = $dbResult->Fetch())
		{
			if ($arResult["FIRST_USER_ID"] == $firstUserID)
			{
				$arSocNetURNCache[$firstUserID][$arResult["SECOND_USER_ID"]] = $arResult["RELATION"];
			}
			else
			{
				$arSocNetURNCache[$firstUserID][$arResult["FIRST_USER_ID"]] = $arResult["RELATION"];
			}
		}

		// get top N relations of user2
		$arSocNetURNCache[$secondUserID] = array();		
		$dbResult = CSocNetUserRelations::GetRelationsTop($secondUserID, 100);
		while ($arResult = $dbResult->Fetch())
		{
			if ($arResult["FIRST_USER_ID"] == $secondUserID)
			{
				$arSocNetURNCache[$secondUserID][$arResult["SECOND_USER_ID"]] = $arResult["RELATION"];
			}
			else
			{
				$arSocNetURNCache[$secondUserID][$arResult["FIRST_USER_ID"]] = $arResult["RELATION"];
			}
		}

		if (!array_key_exists($firstUserID."_".$secondUserID, $arSocNetUserRelationsCache1))
		{
			$strSql =
				"SELECT UR.RELATION ".
				"FROM b_sonet_user_relations UR ".
				"WHERE UR.FIRST_USER_ID = ".$firstUserID." ".
				"	AND UR.SECOND_USER_ID = ".$secondUserID." ".
				"UNION ".
				"SELECT UR.RELATION ".
				"FROM b_sonet_user_relations UR ".
				"WHERE UR.FIRST_USER_ID = ".$secondUserID." ".
				"	AND UR.SECOND_USER_ID = ".$firstUserID." ";

			$dbResult = $DB->Query($strSql);
			if ($arResult = $dbResult->Fetch())
			{
				$arSocNetUserRelationsCache1[$firstUserID."_".$secondUserID] = $arResult["RELATION"];
			}
			else
			{
				$arSocNetUserRelationsCache1[$firstUserID."_".$secondUserID] = false;
			}
		}

		return $arSocNetUserRelationsCache1[$firstUserID."_".$secondUserID];
	}

	public static function IsFriends($firstUserID, $secondUserID)
	{
		global $DB;
		static $arSocNetUserRelationsCache = array();

		$firstUserID = intval($firstUserID);
		if ($firstUserID <= 0)
		{
			return false;
		}

		$secondUserID = intval($secondUserID);
		if ($secondUserID <= 0)
		{
			return false;
		}

		if (!array_key_exists($firstUserID."_".$secondUserID, $arSocNetUserRelationsCache))
		{
			$strSql =
				"SELECT 'x' ".
				"FROM b_sonet_user_relations UR ".
				"WHERE UR.FIRST_USER_ID = ".$firstUserID." ".
				"	AND UR.SECOND_USER_ID = ".$secondUserID." ".
				"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"UNION ".
				"SELECT 'x' ".
				"FROM b_sonet_user_relations UR ".
				"WHERE UR.FIRST_USER_ID = ".$secondUserID." ".
				"	AND UR.SECOND_USER_ID = ".$firstUserID." ".
				"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ";

			$dbResult = $DB->Query($strSql);
			if ($dbResult->Fetch())
			{
				$arSocNetUserRelationsCache[$firstUserID."_".$secondUserID] = true;
			}
			else
			{
				$arSocNetUserRelationsCache[$firstUserID."_".$secondUserID] = false;
			}
		}

		return $arSocNetUserRelationsCache[$firstUserID."_".$secondUserID];
	}

	public static function IsFriends2($firstUserID, $secondUserID)
	{
		global $DB;
		static $arSocNetUser2RelationsCache = array();

		$firstUserID = intval($firstUserID);
		if ($firstUserID <= 0)
		{
			return false;
		}

		$secondUserID = intval($secondUserID);
		if ($secondUserID <= 0)
		{
			return false;
		}

		if (!array_key_exists($firstUserID."_".$secondUserID, $arSocNetUser2RelationsCache))
		{
			$strSql =
				"SELECT 'x' ".
				"FROM b_sonet_user_relations UR, b_sonet_user_relations UR1 ".
				"WHERE UR.FIRST_USER_ID = ".$firstUserID." ".
				"	AND UR.SECOND_USER_ID = UR1.FIRST_USER_ID ".
				"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"	AND UR1.SECOND_USER_ID = ".$secondUserID." ".
				"	AND UR1.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"UNION ".
				"SELECT 'x' ".
				"FROM b_sonet_user_relations UR, b_sonet_user_relations UR1 ".
				"WHERE UR.FIRST_USER_ID = ".$firstUserID." ".
				"	AND UR.SECOND_USER_ID = UR1.SECOND_USER_ID ".
				"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"	AND UR1.FIRST_USER_ID = ".$secondUserID." ".
				"	AND UR1.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"UNION ".
				"SELECT 'x' ".
				"FROM b_sonet_user_relations UR, b_sonet_user_relations UR1 ".
				"WHERE UR.SECOND_USER_ID = ".$firstUserID." ".
				"	AND UR.FIRST_USER_ID = UR1.FIRST_USER_ID ".
				"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"	AND UR1.SECOND_USER_ID = ".$secondUserID." ".
				"	AND UR1.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"UNION ".
				"SELECT 'x' ".
				"FROM b_sonet_user_relations UR, b_sonet_user_relations UR1 ".
				"WHERE UR.SECOND_USER_ID = ".$firstUserID." ".
				"	AND UR.FIRST_USER_ID = UR1.SECOND_USER_ID ".
				"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
				"	AND UR1.FIRST_USER_ID = ".$secondUserID." ".
				"	AND UR1.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ";

			$dbResult = $DB->Query($strSql);
			if ($dbResult->Fetch())
			{
				$arSocNetUser2RelationsCache[$firstUserID."_".$secondUserID] = true;
			}
			else
			{
				$arSocNetUser2RelationsCache[$firstUserID."_".$secondUserID] = false;
			}
		}

		return $arSocNetUser2RelationsCache[$firstUserID."_".$secondUserID];
	}

	/***************************************/
	/**********  SEND EVENTS  **************/
	/***************************************/
	public static function SendEvent($relationID, $mailType = "INVITE_FRIEND")
	{
		$relationID = intval($relationID);
		if ($relationID <= 0)
			return false;

		$dbRelation = CSocNetUserRelations::GetList(
			array(),
			array("ID" => $relationID),
			false,
			false,
			array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "RELATION", "DATE_CREATE", "MESSAGE", "INITIATED_BY", "FIRST_USER_NAME", "FIRST_USER_LAST_NAME", "FIRST_USER_LOGIN", "FIRST_USER_EMAIL", "FIRST_USER_LID", "SECOND_USER_NAME", "SECOND_USER_LAST_NAME", "SECOND_USER_LOGIN", "SECOND_USER_EMAIL", "SECOND_USER_LID")
		);
		$arRelation = $dbRelation->Fetch();
		if (!$arRelation)
			return false;

		$fromUserPref = "FIRST";
		$toUserPref = "SECOND";
		if ($arRelation["INITIATED_BY"] == "S")
		{
			$fromUserPref = "SECOND";
			$toUserPref = "FIRST";
		}

		$mailTemplate = "SONET_INVITE_FRIEND";
		if ($mailType == "AGREE_FRIEND")
			$mailTemplate = "SONET_AGREE_FRIEND";
		elseif ($mailType == "BAN_FRIEND")
			$mailTemplate = "SONET_BAN_FRIEND";

		$defSiteID = $arRelation[$toUserPref."_USER_LID"];
		$siteID = CSocNetUserEvents::GetEventSite($arRelation[$toUserPref."_USER_ID"], $mailTemplate, $defSiteID);
		if ($siteID == false || $siteID == '')
			return false;

		if (IsModuleInstalled("im"))
		{
			$url = str_replace("#USER_ID#", $arRelation[$toUserPref."_USER_ID"], COption::GetOptionString("socialnetwork", "user_request_page", 
				(IsModuleInstalled("intranet")) ? "/company/personal/user/#USER_ID#/requests/" : "/club/user/#USER_ID#/requests/", $siteID));
		}
		else
			$url = COption::GetOptionString("socialnetwork", "messages_path", "/company/personal/messages/", $siteID);

		$arFields = array(
			"RELATION_ID" => $relationID,
			"SENDER_USER_ID" => $arRelation[$fromUserPref."_USER_ID"],
			"SENDER_USER_NAME" => $arRelation[$fromUserPref."_USER_NAME"],
			"SENDER_USER_LAST_NAME" => $arRelation[$fromUserPref."_USER_LAST_NAME"],
			"SENDER_EMAIL_TO" => $arRelation[$fromUserPref."_USER_EMAIL"],
			"RECIPIENT_USER_ID" => $arRelation[$toUserPref."_USER_ID"],
			"RECIPIENT_USER_NAME" => $arRelation[$toUserPref."_USER_NAME"],
			"RECIPIENT_USER_LAST_NAME" => $arRelation[$toUserPref."_USER_LAST_NAME"],
			"RECIPIENT_USER_EMAIL_TO" => $arRelation[$toUserPref."_USER_EMAIL"],
			"MESSAGE" => $arRelation["MESSAGE"],
			"URL" => $url
		);

		$event = new CEvent;
		$event->Send($mailTemplate, $siteID, $arFields, "N");

		return true;
	}

	/***************************************/
	/************  ACTIONS  ****************/
	/***************************************/
	public static function SendRequestToBeFriend($senderUserID, $targetUserID, $message)
	{
		global $APPLICATION;

		$senderUserID = intval($senderUserID);
		if ($senderUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$targetUserID = intval($targetUserID);
		if ($targetUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_TARGET_USER_ID"), "ERROR_TARGET_USER_ID");
			return false;
		}

		$arFields = array(
			"FIRST_USER_ID" => $senderUserID,
			"SECOND_USER_ID" => $targetUserID,
			"RELATION" => SONET_RELATIONS_REQUEST,
			"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
			"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
			"MESSAGE" => $message,
			"INITIATED_BY" => "F",
		);

		//check if relation already exists to overwrite it
		//if user is banned but im is installed and black-list isn't used anymore
		$dbResult = CSocNetUserRelations::GetList(
			array(),
			array(
				"FIRST_USER_ID" => $senderUserID,
				"SECOND_USER_ID" => $targetUserID
			)
		);

		$ID = false;
		if ($arResult = $dbResult->GetNext())
		{
			if ($arResult["RELATION"] == SONET_RELATIONS_BAN && IsModuleInstalled("im"))
				$ID = CSocNetUserRelations::Update($arResult["ID"], $arFields);
		}
		else
			$ID = CSocNetUserRelations::Add($arFields);

		if (!$ID)
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
				$errorMessage = $e->GetString();
			if ($errorMessage == '')
				$errorMessage = GetMessage("SONET_UR_ERROR_CREATE_RELATION");

			$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_CREATE_RELATION");
			return false;
		}

		if (CModule::IncludeModule("im"))
		{
			$dbSenderUser = CUser::GetByID($senderUserID);
			$arSenderUser = $dbSenderUser->Fetch();
			$senderUser = CUser::FormatName(CSite::GetNameFormat(), $arSenderUser, true);	

			$arMessageFields = array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"TO_USER_ID" => (int)$targetUserID,
				"FROM_USER_ID" => (int)$senderUserID,
				"NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
				"NOTIFY_MODULE" => "socialnetwork",
				"NOTIFY_EVENT" => "invite_user_btn",
				"NOTIFY_TAG" => "SOCNET|INVITE_USER|". (int)$targetUserID ."|". $ID,
				"NOTIFY_TITLE" => Loc::getMessage('SONET_U_INVITE_CONFIRM_TITLE', ["#USER#" => $senderUser]),
				"NOTIFY_MESSAGE" => fn (?string $languageId = null) => Loc::getMessage(
					'SONET_U_INVITE_CONFIRM_TEXT',
					['#TEXT#' => $message],
					$languageId
				),
				"NOTIFY_BUTTONS" => Array(
					Array('TITLE' => GetMessage('SONET_U_INVITE_CONFIRM'), 'VALUE' => 'Y', 'TYPE' => 'accept'),
					Array('TITLE' => GetMessage('SONET_U_INVITE_REJECT'), 'VALUE' => 'N', 'TYPE' => 'cancel'),
				)
			);

			$dbSite = CSite::GetByID(SITE_ID);
			$arSite = $dbSite->Fetch();
			$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
			if ($serverName == '')
			{
				if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
				{
					$serverName = SITE_SERVER_NAME;
				}
				else
				{
					$serverName = COption::GetOptionString("main", "server_name", "");
				}
				if ($serverName == '')
				{
					$serverName = $_SERVER["SERVER_NAME"];
				}
			}
			$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".$serverName;

			$requestUrl = COption::GetOptionString("socialnetwork", "user_request_page", 
				(IsModuleInstalled("intranet")) ? "/company/personal/user/#USER_ID#/requests/" : "/club/user/#USER_ID#/requests/", SITE_ID);

			$requestUrl = $serverName.str_replace(array("#USER_ID#", "#user_id#"), $targetUserID, $requestUrl);

			$arMessageFields['NOTIFY_MESSAGE_OUT'] = fn (?string $languageId = null) =>
				Loc::getMessage(
					'SONET_U_INVITE_CONFIRM_TEXT',
					['#TEXT#' => $message],
					$languageId
				)
				. "\n\n".Loc::getMessage('SONET_U_INVITE_CONFIRM', null, $languageId).": ".$requestUrl.'?INVITE_USER='.$ID.'&CONFIRM=Y'
				. "\n\n".Loc::getMessage('SONET_U_INVITE_REJECT', null, $languageId).": ".$requestUrl.'?INVITE_USER='.$ID.'&CONFIRM=N'
			;

			CIMNotify::Add($arMessageFields);
		}

		CSocNetUserRelations::__SpeedFileCreate($targetUserID);

		return true;
	}

	public static function ConfirmRequestToBeFriend($senderUserID, $relationID, $bAutoSubscribe = true)
	{
		global $APPLICATION;

		$senderUserID = intval($senderUserID);
		if ($senderUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$relationID = intval($relationID);
		if ($relationID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_TARGET_USER_ID"), "ERROR_RELATION_ID");
			return false;
		}

		$dbResult = CSocNetUserRelations::GetList(
			array(),
			array(
				"ID" => $relationID,
				"SECOND_USER_ID" => $senderUserID,
				"RELATION" => SONET_RELATIONS_REQUEST
			),
			false,
			false,
			array("ID", "FIRST_USER_ID", "SECOND_USER_ID")
		);

		if ($arResult = $dbResult->Fetch())
		{
			$rsUser = CUser::GetByID(intval($arResult["FIRST_USER_ID"]));
			$arUser = $rsUser->Fetch();
			if (!is_array($arUser) || $arUser["ACTIVE"] !== "Y")
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_INVALID_TARGET_USER_ID"), "ERROR_INVALID_TARGET_USER_ID");
				return false;
			}

			$arFields = array(
				"RELATION" => SONET_RELATIONS_FRIEND,
				"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"INITIATED_BY" => "S",
			);

			if (CSocNetUserRelations::Update($arResult["ID"], $arFields))
			{
				if ($bAutoSubscribe)
				{
					CSocNetLogEvents::AutoSubscribe($senderUserID, SONET_ENTITY_USER, $arResult["FIRST_USER_ID"]);
					CSocNetLogEvents::AutoSubscribe($arResult["FIRST_USER_ID"], SONET_ENTITY_USER, $senderUserID);
				}

				if (CModule::IncludeModule("im"))
				{
					CIMNotify::DeleteByTag("SOCNET|INVITE_USER|".intval($senderUserID)."|".intval($arResult["ID"]));
					$arMessageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $arResult["FIRST_USER_ID"],
						"FROM_USER_ID" => $senderUserID,
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "inout_user",
						"NOTIFY_TAG" => "SOCNET|INVITE_USER_CONFIRM",
						"NOTIFY_MESSAGE" => fn (?string $languageId = null) => Loc::getMessage('SONET_UR_AGREE_FRIEND_MESSAGE', null, $languageId),
					);
					CIMNotify::Add($arMessageFields);
				}
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
					$errorMessage = $e->GetString();
				if ($errorMessage == '')
					$errorMessage = GetMessage("SONET_UR_ERROR_UPDATE_RELATION");

				$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_CREATE_RELATION");
				return false;
			}
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_NO_FRIEND_REQUEST"), "ERROR_NO_FRIEND_REQUEST");
			return false;
		}

		$arUserID = array(
			$arResult["FIRST_USER_ID"],
			$arResult["SECOND_USER_ID"]
		);

		$dbFriends = CSocNetUserRelations::GetRelatedUsers($arResult["FIRST_USER_ID"], SONET_RELATIONS_FRIEND);
		while ($arFriends = $dbFriends->Fetch())
		{
			$pref = (($arResult["FIRST_USER_ID"] == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");
			$arUserID[] = $arResult[$pref."_USER_ID"];
		}

		$dbFriends = CSocNetUserRelations::GetRelatedUsers($arResult["SECOND_USER_ID"], SONET_RELATIONS_FRIEND);
		while ($arFriends = $dbFriends->Fetch())
		{
			$pref = (($arResult["FIRST_USER_ID"] == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");
			$arUserID[] = $arResult[$pref."_USER_ID"];
		}

		$arUserID = array_unique($arUserID);

		CSocNetUserRelations::__SpeedFileCheckMessages($senderUserID);

		return true;
	}

	public static function RejectRequestToBeFriend($senderUserID, $relationID)
	{
		global $APPLICATION;

		$senderUserID = intval($senderUserID);
		if ($senderUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$relationID = intval($relationID);
		if ($relationID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_TARGET_USER_ID"), "ERROR_RELATION_ID");
			return false;
		}

		$dbResult = CSocNetUserRelations::GetList(
			array(),
			array(
				"ID" => $relationID,
				"SECOND_USER_ID" => $senderUserID,
				"RELATION" => SONET_RELATIONS_REQUEST
			),
			false,
			false,
			array("ID", "FIRST_USER_ID")
		);

		if ($arResult = $dbResult->Fetch())
		{
			if (CSocNetUserRelations::Delete($arResult["ID"]))
			{
				if (CModule::IncludeModule("im"))
				{
					CIMNotify::DeleteByTag("SOCNET|INVITE_USER|".intval($senderUserID)."|".intval($arResult["ID"]));
					$arMessageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $arResult["FIRST_USER_ID"],
						"FROM_USER_ID" => $senderUserID,
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "inout_user",
						"NOTIFY_TAG" => "SOCNET|INVITE_USER_REJECT",
						"NOTIFY_MESSAGE" => fn (?string $languageId = null) => Loc::getMessage('SONET_UR_REJECT_FRIEND_MESSAGE', null, $languageId),
					);
					CIMNotify::Add($arMessageFields);
				}
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
					$errorMessage = $e->GetString();
				if ($errorMessage == '')
					$errorMessage = GetMessage("SONET_UR_RELATION_DELETE_ERROR");

				$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_DELETE_RELATION");
				return false;
			}
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_NO_FRIEND_REQUEST"), "ERROR_NO_FRIEND_REQUEST");
			return false;
		}

		CSocNetUserRelations::__SpeedFileCheckMessages($senderUserID);

		return true;
	}

	public static function DeleteRelation($senderUserID, $targetUserID)
	{
		global $APPLICATION;

		$senderUserID = intval($senderUserID);
		if ($senderUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$targetUserID = intval($targetUserID);
		if ($targetUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_TARGET_USER_ID"), "ERROR_TARGET_USER_ID");
			return false;
		}

		$arRelation = CSocNetUserRelations::GetByUserID($senderUserID, $targetUserID);
		if (!$arRelation)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_RELATION_NOT_FOUND"), "ERROR_RELATION_NOT_FOUND");
			return false;
		}

		if (CSocNetUserRelations::Delete($arRelation["ID"]))
		{
			if (CModule::IncludeModule("im"))
			{
				$rsUser = CUser::GetByID($senderUserID);
				$gender_suffix = '';
				if ($arUser = $rsUser->Fetch())
				{
					$gender_suffix = match ($arUser["PERSONAL_GENDER"])
					{
						"M" => "_M",
						"F" => "_F",
						default => "",
					};
				}

				$arMessageFields = array(
					"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
					"FROM_USER_ID" => $senderUserID,
					"TO_USER_ID" => $targetUserID,
					"NOTIFY_TYPE" => IM_NOTIFY_FROM,
					"NOTIFY_MODULE" => "socialnetwork",
					"NOTIFY_EVENT" => "inout_user",
					"NOTIFY_TAG" => "SOCNET|FRIENDS|". (int)$arRelation["ID"],
					"NOTIFY_MESSAGE" => fn (?string $languageId = null) => Loc::getMessage("SONET_UR_IM_UNFRIEND" . $gender_suffix, null, $languageId),
				);
				CIMNotify::Add($arMessageFields);
			}

			if ($arRelation["RELATION"] == SONET_RELATIONS_FRIEND)
				$GLOBALS["DB"]->Query("DELETE FROM b_sonet_event_user_view WHERE
					ENTITY_TYPE = '".SONET_ENTITY_USER."'
					AND (
						(USER_ID = ".$arRelation["FIRST_USER_ID"]." AND ENTITY_ID = ".$arRelation["SECOND_USER_ID"].")
						OR (USER_ID = ".$arRelation["SECOND_USER_ID"]." AND ENTITY_ID = ".$arRelation["FIRST_USER_ID"].")
						OR (ENTITY_ID = ".$arRelation["FIRST_USER_ID"]." AND USER_IM_ID = ".$arRelation["SECOND_USER_ID"].")
						OR (ENTITY_ID = ".$arRelation["SECOND_USER_ID"]." AND USER_IM_ID = ".$arRelation["FIRST_USER_ID"].")
						OR (USER_ID = ".$arRelation["FIRST_USER_ID"]." AND USER_IM_ID = ".$arRelation["SECOND_USER_ID"].")
						OR (USER_ID = ".$arRelation["SECOND_USER_ID"]." AND USER_IM_ID = ".$arRelation["FIRST_USER_ID"].")
					)", true);
		}
		else
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
				$errorMessage = $e->GetString();
			if ($errorMessage == '')
				$errorMessage = GetMessage("SONET_UR_RELATION_DELETE_ERROR");

			$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_DELETE_RELATION");
			return false;
		}

		CSocNetUserRelations::__SpeedFileCheckMessages($senderUserID);

		return true;
	}

	public static function BanUser($senderUserID, $targetUserID)
	{
		global $APPLICATION, $DB;

		$senderUserID = intval($senderUserID);
		if ($senderUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$targetUserID = intval($targetUserID);
		if ($targetUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_TARGET_USER_ID"), "ERROR_TARGET_USER_ID");
			return false;
		}
		elseif (CSocNetUser::IsUserModuleAdmin($targetUserID, false))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_ERROR_CREATE_RELATION"), "ERROR_TARGET_USER_ID");
			return false;
		}

		$strSql =
			"SELECT UR.ID, UR.FIRST_USER_ID, UR.SECOND_USER_ID, UR.RELATION ".
			"FROM b_sonet_user_relations UR ".
			"WHERE UR.FIRST_USER_ID = ".$senderUserID." ".
			"	AND UR.SECOND_USER_ID = ".$targetUserID." ".
			"UNION ".
			"SELECT UR.ID, UR.FIRST_USER_ID, UR.SECOND_USER_ID, UR.RELATION ".
			"FROM b_sonet_user_relations UR ".
			"WHERE UR.FIRST_USER_ID = ".$targetUserID." ".
			"	AND UR.SECOND_USER_ID = ".$senderUserID." ";

		$dbResult = $DB->Query($strSql);
		if ($arResult = $dbResult->Fetch())
		{
			if ($arResult["RELATION"] != SONET_RELATIONS_BAN)
			{
				$arFields = array(
					"RELATION" => SONET_RELATIONS_BAN,
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				);
				if ($arResult["FIRST_USER_ID"] == $senderUserID)
					$arFields["INITIATED_BY"] = "F";
				else
					$arFields["INITIATED_BY"] = "S";

				if (CSocNetUserRelations::Update($arResult["ID"], $arFields))
				{
					$arMessageFields = array(
						"FROM_USER_ID" => $senderUserID,
						"TO_USER_ID" => $targetUserID,
						"MESSAGE" => fn (?string $languageId = null) => Loc::getMessage("SONET_UR_BANUSER_MESSAGE", null, $languageId),
						"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
					);

					CSocNetMessages::Add($arMessageFields);

					if ($arResult["RELATION"] == SONET_RELATIONS_FRIEND)
						$GLOBALS["DB"]->Query("DELETE FROM b_sonet_event_user_view WHERE
							ENTITY_TYPE = '".SONET_ENTITY_USER."'
							AND (
								(USER_ID = ".$arRelation["FIRST_USER_ID"]." AND ENTITY_ID = ".$arRelation["SECOND_USER_ID"].")
								OR (USER_ID = ".$arRelation["SECOND_USER_ID"]." AND ENTITY_ID = ".$arRelation["FIRST_USER_ID"].")
								OR (ENTITY_ID = ".$arRelation["FIRST_USER_ID"]." AND USER_IM_ID = ".$arRelation["SECOND_USER_ID"].")
								OR (ENTITY_ID = ".$arRelation["SECOND_USER_ID"]." AND USER_IM_ID = ".$arRelation["FIRST_USER_ID"].")
								OR (USER_ID = ".$arRelation["FIRST_USER_ID"]." AND USER_IM_ID = ".$arRelation["SECOND_USER_ID"].")
								OR (USER_ID = ".$arRelation["SECOND_USER_ID"]." AND USER_IM_ID = ".$arRelation["FIRST_USER_ID"].")
							)", true);
				}
				else
				{
					$errorMessage = "";
					if ($e = $APPLICATION->GetException())
						$errorMessage = $e->GetString();
					if ($errorMessage == '')
						$errorMessage = GetMessage("SONET_UR_ERROR_UPDATE_RELATION");

					$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_UPDATE_RELATION");
					return false;
				}
			}
			else
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_ALREADY_BAN"), "ERROR_ALREADY_BAN");
				return false;
			}
		}
		else
		{
			$arFields = array(
				"FIRST_USER_ID" => $senderUserID,
				"SECOND_USER_ID" => $targetUserID,
				"RELATION" => SONET_RELATIONS_BAN,
				"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"INITIATED_BY" => "F",
			);
			if (CSocNetUserRelations::Add($arFields))
			{
				$arMessageFields = array(
					"FROM_USER_ID" => $senderUserID,
					"TO_USER_ID" => $targetUserID,
					"MESSAGE" => fn (?string $languageId = null) => Loc::getMessage("SONET_UR_BANUSER_MESSAGE", null, $languageId),
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
				);

				CSocNetMessages::Add($arMessageFields);
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
					$errorMessage = $e->GetString();
				if ($errorMessage == '')
					$errorMessage = GetMessage("SONET_UR_ERROR_CREATE_RELATION");

				$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_CREATE_RELATION");
				return false;
			}
		}

		return true;
	}

	public static function UnBanMember($senderUserID, $relationID)
	{
		global $APPLICATION, $DB;

		$senderUserID = intval($senderUserID);
		if ($senderUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$relationID = intval($relationID);
		if ($relationID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_RELATION"), "ERROR_RELATIONID");
			return false;
		}

		$arRelation = CSocNetUserRelations::GetByID($relationID);
		if (!$arRelation)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_ERROR_NO_RELATION"), "ERROR_NO_RELATION");
			return false;
		}

		if ($arRelation["RELATION"] == SONET_RELATIONS_BAN
			&&
			($arRelation["FIRST_USER_ID"] == $senderUserID && $arRelation["INITIATED_BY"] == "F"
			|| $arRelation["SECOND_USER_ID"] == $senderUserID && $arRelation["INITIATED_BY"] == "S"))
		{
			if (CSocNetUserRelations::Delete($arRelation["ID"]))
			{
				$arMessageFields = array(
					"FROM_USER_ID" => $senderUserID,
					"TO_USER_ID" => ($arRelation["FIRST_USER_ID"] == $senderUserID ? $arRelation["SECOND_USER_ID"] : $arRelation["FIRST_USER_ID"]),
					"MESSAGE" => fn (?string $languageId = null) => Loc::getMessage("SONET_UR_UNBANUSER_MESSAGE", null, $languageId),
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
				);

				CSocNetMessages::Add($arMessageFields);
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
					$errorMessage = $e->GetString();
				if ($errorMessage == '')
					$errorMessage = GetMessage("SONET_UR_RELATION_DELETE_ERROR");

				$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_DELETE_RELATION");
				return false;
			}
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_UNBAN_ERROR"), "ERROR_UNBAN");
			return false;
		}

		return true;
	}

	public static function __SpeedFileCheckMessages($userID)
	{
		$userID = intval($userID);
		if ($userID <= 0)
			return;

		$cnt = 0;
		$dbResult = $GLOBALS["DB"]->Query(
			"SELECT COUNT(ID) as CNT ".
			"FROM b_sonet_user_relations ".
			"WHERE SECOND_USER_ID = ".$userID." ".
			"	AND RELATION = '".$GLOBALS["DB"]->ForSql(SONET_RELATIONS_REQUEST, 1)."' "
		);
		if ($arResult = $dbResult->Fetch())
			$cnt = intval($arResult["CNT"]);

		if ($cnt > 0)
			CSocNetUserRelations::__SpeedFileCreate($userID);
		else
			CSocNetUserRelations::__SpeedFileDelete($userID);
	}

	public static function __SpeedFileCreate($userID)
	{
		global $CACHE_MANAGER;
		
		$userID = intval($userID);
		if ($userID <= 0)
			return;

		if ($CACHE_MANAGER->Read(86400*30, "socnet_cf_".$userID))
			$CACHE_MANAGER->Clean("socnet_cf_".$userID);
/*
		$filePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_flags/socnet/c/".IntVal($userID / 1000)."/";
		$fileName = $userID."_f";

		if (!file_exists($filePath.$fileName))
		{
			CheckDirPath($filePath);
			@fclose(@fopen($filePath.$fileName, "w"));
		}
*/
	}

	public static function __SpeedFileDelete($userID)
	{
		global $CACHE_MANAGER;

		$userID = intval($userID);
		if ($userID <= 0)
			return;

		if (!$CACHE_MANAGER->Read(86400*30, "socnet_cf_".$userID))
			$CACHE_MANAGER->Set("socnet_cf_".$userID, true);
/*
		$fileName = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_flags/socnet/c/".IntVal($userID / 1000)."/".$userID."_f";
		if (file_exists($fileName))
			@unlink($fileName);
*/
	}

	public static function SpeedFileExists($userID)
	{
		global $CACHE_MANAGER;

		$userID = intval($userID);
		if ($userID <= 0)
			return;

		return (!$CACHE_MANAGER->Read(86400*30, "socnet_cf_".$userID));
/*
		$fileName = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_flags/socnet/c/".IntVal($userID / 1000)."/".$userID."_f";
		return file_exists($fileName);
*/
	}

	/* Module IM callback */
	public static function OnBeforeConfirmNotify($module, $tag, $value, $arParams)
	{
		if ($module == "socialnetwork")
		{
			$arTag = explode("|", $tag);
			if (count($arTag) == 4 && $arTag[1] == 'INVITE_USER')
			{
				if ($value == 'Y')
				{
					self::ConfirmRequestToBeFriend($arTag[2], $arTag[3]);
					return true;
				}
				else
				{
					self::RejectRequestToBeFriend($arTag[2], $arTag[3]);
					return true;
				}
			}
		}
	}
}
