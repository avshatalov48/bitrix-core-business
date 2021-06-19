<?php

IncludeModuleLangFile(__FILE__);

class CAllSocNetMessages
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB;

		if ($ACTION != "ADD" && intval($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "FROM_USER_ID") || $ACTION=="ADD") && intval($arFields["FROM_USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_M_EMPTY_FROM_USER_ID"), "EMPTY_FROM_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "FROM_USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["FROM_USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_M_ERROR_NO_FROM_USER_ID"), "ERROR_NO_FROM_USER_ID");
				return false;
			}
		}

		if ((is_set($arFields, "TO_USER_ID") || $ACTION=="ADD") && intval($arFields["TO_USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_M_EMPTY_TO_USER_ID"), "EMPTY_TO_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "TO_USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["TO_USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_M_ERROR_NO_TO_USER_ID"), "ERROR_NO_TO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "DATE_CREATE") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GB_EMPTY_DATE_CREATE"), "EMPTY_DATE_CREATE");
			return false;
		}

		if (is_set($arFields, "DATE_VIEW") && $arFields["DATE_VIEW"] !== false && (!$DB->IsDate($arFields["DATE_VIEW"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_MM_EMPTY_DATE_VIEW"), "EMPTY_DATE_UPDATE");
			return false;
		}

		if ((is_set($arFields, "MESSAGE_TYPE") || $ACTION=="ADD") && $arFields["MESSAGE_TYPE"] != SONET_MESSAGE_PRIVATE && $arFields["MESSAGE_TYPE"] != SONET_MESSAGE_SYSTEM)
			$arFields["MESSAGE_TYPE"] = SONET_MESSAGE_PRIVATE;

		if ((is_set($arFields, "FROM_DELETED") || $ACTION=="ADD") && $arFields["FROM_DELETED"] != "Y" && $arFields["FROM_DELETED"] != "N")
			$arFields["FROM_DELETED"] = "N";

		if ((is_set($arFields, "TO_DELETED") || $ACTION=="ADD") && $arFields["TO_DELETED"] != "Y" && $arFields["TO_DELETED"] != "N")
			$arFields["TO_DELETED"] = "N";

		if ((is_set($arFields, "SEND_MAIL") || $ACTION=="ADD") && $arFields["SEND_MAIL"] != "Y" && $arFields["SEND_MAIL"] != "N")
			$arFields["SEND_MAIL"] = "N";

		if ((is_set($arFields, "IS_LOG") || $ACTION=="ADD") && $arFields["IS_LOG"] != "Y" && $arFields["SEND_MAIL"] != "N")
			$arFields["SEND_MAIL"] = "N";

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);
		$bSuccess = True;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetMessagesDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		$events = GetModuleEvents("socialnetwork", "OnSocNetMessagesDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_messages WHERE ID = ".$ID."", true);

		return $bSuccess;
	}

	public static function DeleteMessage($ID, $userID, $bCheckMessages = true)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_MESSAGE_ID"), "ERROR_MESSAGE_ID");
			return false;
		}

		$userID = intval($userID);
		if ($userID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$arMessage = CSocNetMessages::GetByID($ID);
		if (!$arMessage || !is_array($arMessage))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_NO_MESSAGE"), "ERROR_NO_MESSAGE");
			return false;
		}

		if (($arMessage["FROM_USER_ID"] == $userID) && ($arMessage["TO_USER_ID"] == $userID))
		{
			if (!CSocNetMessages::Delete($arMessage["ID"]))
			{
				$errorMessage = "";
				if ($e = $GLOBALS["APPLICATION"]->GetException())
					$errorMessage = $e->GetString();
				if ($errorMessage == '')
					$errorMessage = GetMessage("SONET_M_ERROR_DELETE_MESSAGE");
					$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_DELETE_MESSAGE");
				return false;
			}			
		}
		elseif ($arMessage["FROM_USER_ID"] == $userID)
		{
			if ($arMessage["TO_DELETED"] == "Y")
			{
				if (!CSocNetMessages::Delete($arMessage["ID"]))
				{
					$errorMessage = "";
					if ($e = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage = $e->GetString();
					if ($errorMessage == '')
						$errorMessage = GetMessage("SONET_M_ERROR_DELETE_MESSAGE");

					$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_DELETE_MESSAGE");
					return false;
				}
			}
			else
			{
				if (!CSocNetMessages::Update($arMessage["ID"], array("FROM_DELETED" => "Y")))
				{
					$errorMessage = "";
					if ($e = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage = $e->GetString();
					if ($errorMessage == '')
						$errorMessage = GetMessage("SONET_UR_ERROR_UPDATE_MESSAGE");

					$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_UPDATE_MESSAGE");
					return false;
				}
			}
		}
		elseif ($arMessage["TO_USER_ID"] == $userID)
		{
			if ($arMessage["FROM_DELETED"] == "Y")
			{
				if (!CSocNetMessages::Delete($arMessage["ID"]))
				{
					$errorMessage = "";
					if ($e = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage = $e->GetString();
					if ($errorMessage == '')
						$errorMessage = GetMessage("SONET_M_ERROR_DELETE_MESSAGE");

					$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_DELETE_MESSAGE");
					return false;
				}
			}
			else
			{
				if (!CSocNetMessages::Update($arMessage["ID"], array("TO_DELETED" => "Y")))
				{
					$errorMessage = "";
					if ($e = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage = $e->GetString();
					if ($errorMessage == '')
						$errorMessage = GetMessage("SONET_UR_ERROR_UPDATE_MESSAGE");

					$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_UPDATE_MESSAGE");
					return false;
				}
			}

			if ($bCheckMessages)
				CSocNetMessages::__SpeedFileCheckMessages($userID);
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_NO_MESSAGE"), "ERROR_NO_MESSAGE");
			return false;
		}

		return true;
	}

	public static function DeleteNoDemand($userID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($userID))
			return false;

		$userID = intval($userID);
		$bSuccess = True;

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_messages WHERE FROM_USER_ID = ".$userID." OR TO_USER_ID = ".$userID."", true);

		CSocNetMessages::__SpeedFileDelete($userID);

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

		$dbResult = CSocNetMessages::GetList(Array(), Array("ID" => $ID, "IS_LOG_ALL" => "Y"));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}
	
	/***************************************/
	/**********  SEND EVENTS  **************/
	/***************************************/
	public static function SendEvent($messageID, $mailTemplate = "SONET_NEW_MESSAGE")
	{
		$messageID = intval($messageID);
		if ($messageID <= 0)
			return false;

		$dbMessage = CSocNetMessages::GetList(
			array(),
			array("ID" => $messageID, "IS_LOG_ALL" => "Y"),
			false,
			false,
			array("ID", "FROM_USER_ID", "TO_USER_ID", "TITLE", "MESSAGE", "DATE_CREATE", "FROM_USER_NAME", "FROM_USER_LAST_NAME", "FROM_USER_LOGIN", "TO_USER_NAME", "TO_USER_LAST_NAME", "TO_USER_LOGIN", "TO_USER_EMAIL", "TO_USER_LID")
		);
		$arMessage = $dbMessage->Fetch();
		if (!$arMessage)
			return false;

		$defSiteID = (Defined("SITE_ID") ? SITE_ID : $arMessage["TO_USER_LID"]);

		$siteID = CSocNetUserEvents::GetEventSite($arMessage["TO_USER_ID"], $mailTemplate, $defSiteID);
		if ($siteID == false || $siteID == '')
			return false;

		$arFields = array(
			"MESSAGE_ID" => $messageID,
			"USER_ID" => $arMessage["TO_USER_ID"],
			"USER_NAME" => $arMessage["TO_USER_NAME"],
			"USER_LAST_NAME" => $arMessage["TO_USER_LAST_NAME"],
			"SENDER_ID" => $arMessage["FROM_USER_ID"],
			"SENDER_NAME" => $arMessage["FROM_USER_NAME"],
			"SENDER_LAST_NAME" => $arMessage["FROM_USER_LAST_NAME"],
			"EMAIL_TO" => $arMessage["TO_USER_EMAIL"],
			"TITLE" => $arMessage["TITLE"],
			"MESSAGE" => $arMessage["MESSAGE"]
		);

		$event = new CEvent;
		$event->Send($mailTemplate, $siteID, $arFields, "N");

		return true;
	}

	/***************************************/
	/************  ACTIONS  ****************/
	/***************************************/
	public static function MarkMessageRead($senderUserID, $messageID, $bRead = true)
	{
		global $APPLICATION;

		$senderUserID = intval($senderUserID);
		if ($senderUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$messageID = intval($messageID);
		if ($messageID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_MESSAGE_ID"), "ERROR_MESSAGE_ID");
			return false;
		}

		$arFilter = array(
				"ID" => $messageID,
				"TO_USER_ID" => $senderUserID,
				"IS_LOG_ALL" => "Y"
			);
		if ($bRead)
			$arFilter["DATE_VIEW"] = "";

		$dbResult = CSocNetMessages::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID", "DATE_CREATE")
		);

		if ($arResult = $dbResult->Fetch())
		{
			if ($bRead)
				$arFields = array("=DATE_VIEW" => $GLOBALS["DB"]->CurrentTimeFunction());
			else
				$arFields = array("DATE_VIEW" => false);

			if (!CSocNetMessages::Update($arResult["ID"], $arFields))
			{
				$errorMessage = "";
				if ($e = $GLOBALS["APPLICATION"]->GetException())
					$errorMessage = $e->GetString();
				if ($errorMessage == '')
					$errorMessage = GetMessage("SONET_UR_ERROR_UPDATE_MESSAGE");

				$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_UPDATE_MESSAGE");
				return false;
			}
			else
			{
				CSocNetMessages::__SpeedFileCheckMessages($senderUserID);
			}
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_NO_MESSAGE"), "ERROR_NO_MESSAGE");
			return false;
		}

		return true;
	}

	public static function CreateMessage($senderUserID, $targetUserID, $message, $title = false)
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

		$message = Trim($message);
		if ($message == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_MESSAGE"), "ERROR_MESSAGE");
			return false;
		}

		$arFields = array(
			"FROM_USER_ID" => $senderUserID,
			"TO_USER_ID" => $targetUserID,
			"TITLE" => $title,
			"MESSAGE" => $message,
			"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
			"DATE_VIEW" => false,
			"MESSAGE_TYPE" => SONET_MESSAGE_PRIVATE,
			"FROM_DELETED" => "N",
			"TO_DELETED" => "N",
			"SEND_MAIL" => "N",
		);
		if (!CSocNetMessages::Add($arFields))
		{
			$errorMessage = "";
			if ($e = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage = $e->GetString();
			if ($errorMessage == '')
				$errorMessage = GetMessage("SONET_UR_ERROR_CREATE_MESSAGE");

			$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_CREATE_MESSAGE");
			return false;
		}

		CSocNetMessages::__SpeedFileCreate($targetUserID);

		return true;
	}

	public static function MarkMessageReadMultiple($userID, $arIDs)
	{
		global $APPLICATION, $DB;

		$userID = intval($userID);
		if ($userID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_USER_ID");
			return false;
		}

		if (!is_array($arIDs))
			return true;

		foreach ($arIDs as $ID)
			CSocNetMessages::MarkMessageRead($userID, $ID);

		return true;
	}

	public static function DeleteMessageMultiple($userID, $arIDs)
	{
		global $APPLICATION, $DB;

		$userID = intval($userID);
		if ($userID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_USER_ID");
			return false;
		}

		if (!is_array($arIDs))
			return true;

		foreach ($arIDs as $ID)
			CSocNetMessages::DeleteMessage($ID, $userID);

		return true;
	}

	public static function DeleteConversation($CurrentUserID, $PartnerUserID)
	{
		global $APPLICATION, $DB;

		$CurrentUserID = intval($CurrentUserID);
		$PartnerUserID = intval($PartnerUserID);
		
		if ($CurrentUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_SENDER_USER_ID"), "ERROR_USER_ID");
			return false;
		}

		if ($PartnerUserID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UR_EMPTY_TARGET_USER_ID"), "ERROR_USER_ID");
			return false;
		}
		
		$dbMessages = CSocNetMessages::GetMessagesForChat($CurrentUserID, $PartnerUserID);
		while ($arMessages = $dbMessages->GetNext())
		{
			CSocNetMessages::DeleteMessage($arMessages["ID"], $CurrentUserID, false);		
		}
		
		CSocNetMessages::__SpeedFileCheckMessages($CurrentUserID);

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
			"FROM b_sonet_messages ".
			"WHERE TO_USER_ID = ".$userID." ".
			"	AND DATE_VIEW IS NULL ".
			"	AND TO_DELETED = 'N' "
		);
		if ($arResult = $dbResult->Fetch())
			$cnt = intval($arResult["CNT"]);

		if ($cnt > 0)
			CSocNetMessages::__SpeedFileCreate($userID);
		else
			CSocNetMessages::__SpeedFileDelete($userID);
	}

	public static function __SpeedFileCreate($userID)
	{
		global $CACHE_MANAGER;

		$userID = intval($userID);
		if ($userID <= 0)
			return;

		if ($CACHE_MANAGER->Read(86400*30, "socnet_cm_".$userID))
			$CACHE_MANAGER->Clean("socnet_cm_".$userID);
			
/*
			
		$filePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_flags/socnet/c/".IntVal($userID / 1000)."/";
		$fileName = $userID."_m";

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

		if (!$CACHE_MANAGER->Read(86400*30, "socnet_cm_".$userID))
			$CACHE_MANAGER->Set("socnet_cm_".$userID, true);
/*
		$fileName = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_flags/socnet/c/".IntVal($userID / 1000)."/".$userID."_m";
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

		return (!$CACHE_MANAGER->Read(86400*30, "socnet_cm_".$userID));
/*
		$fileName = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_flags/socnet/c/".IntVal($userID / 1000)."/".$userID."_m";
		return file_exists($fileName);
*/
	}

	public static function SendEventAgent()
	{
		global $DB;

		if (IsModuleInstalled("im"))
			return "CSocNetMessages::SendEventAgent();";	

		$dbMessage = CSocNetMessages::GetList(
			array(),
			array(
				"DATE_VIEW" => "", 
				"TO_DELETED" => "N", 
				"SEND_MAIL" => "N",
				"!IS_LOG" => "Y"
			),
			false,
			false,
			array("ID", "FROM_USER_ID", "TO_USER_ID", "TITLE", "MESSAGE", "DATE_CREATE", "FROM_USER_NAME", "FROM_USER_LAST_NAME", "FROM_USER_LOGIN", "TO_USER_NAME", "TO_USER_LAST_NAME", "TO_USER_LOGIN", "TO_USER_EMAIL", "TO_USER_LID", "EMAIL_TEMPLATE", "IS_LOG")
		);

		while ($arMessage = $dbMessage->Fetch())
		{
			if (isset($arMessage["EMAIL_TEMPLATE"]) && $arMessage["EMAIL_TEMPLATE"] <> '')
				$mailTemplate = $arMessage["EMAIL_TEMPLATE"];
			else
				$mailTemplate = "SONET_NEW_MESSAGE";
		
			$defSiteID = $arMessage["TO_USER_LID"];
			$siteID = CSocNetUserEvents::GetEventSite($arMessage["TO_USER_ID"], $mailTemplate, $defSiteID);
			if ($siteID == false || $siteID == '')
				$siteID = CSite::GetDefSite();

			if ($siteID == false || $siteID == '')
				continue;
				
			$arFields = array(
				"MESSAGE_ID" => $arMessage["ID"],
				"USER_ID" => $arMessage["TO_USER_ID"],
				"USER_NAME" => $arMessage["TO_USER_NAME"],
				"USER_LAST_NAME" => $arMessage["TO_USER_LAST_NAME"],
				"SENDER_ID" => $arMessage["FROM_USER_ID"],
				"SENDER_NAME" => $arMessage["FROM_USER_NAME"],
				"SENDER_LAST_NAME" => $arMessage["FROM_USER_LAST_NAME"],
				"EMAIL_TO" => $arMessage["TO_USER_EMAIL"],
				"TITLE" => $arMessage["TITLE"],
				"MESSAGE" => CSocNetTextParser::convert4mail($arMessage["MESSAGE"]),
			);

			$event = new CEvent;
			$event->Send($mailTemplate, $siteID, $arFields, "N");

			CSocNetMessages::Update($arMessage["ID"], array("SEND_MAIL" => "Y"));
		}

		return "CSocNetMessages::SendEventAgent();";
	}
}
