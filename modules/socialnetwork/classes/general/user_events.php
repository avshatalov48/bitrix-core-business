<?php

IncludeModuleLangFile(__FILE__);

class CAllSocNetUserEvents
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $arSocNetUserEvents;

		if ($ACTION != "ADD" && intval($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UE_EMPTY_USER_ID"), "EMPTY_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UE_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if ((is_set($arFields, "EVENT_ID") || $ACTION=="ADD") && $arFields["EVENT_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UE_EMPTY_EVENT_ID"), "EMPTY_EVENT_ID");
			return false;
		}
		elseif (is_set($arFields, "EVENT_ID") && !in_array($arFields["EVENT_ID"], $arSocNetUserEvents))
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["EVENT_ID"], GetMessage("SONET_UE_ERROR_NO_EVENT_ID")), "ERROR_NO_EVENT_ID");
			return false;
		}

		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && $arFields["SITE_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_UE_EMPTY_SITE_ID"), "EMPTY_SITE_ID");
			return false;
		}
		elseif (is_set($arFields, "SITE_ID"))
		{
			$dbResult = CSite::GetByID($arFields["SITE_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["SITE_ID"], GetMessage("SONET_UE_ERROR_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if ((is_set($arFields, "ACTIVE") || $ACTION=="ADD") && $arFields["ACTIVE"] != "Y" && $arFields["ACTIVE"] != "N")
			$arFields["ACTIVE"] = "Y";

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);
		$bSuccess = True;

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_user_events WHERE ID = ".$ID."", true);

		return $bSuccess;
	}

	public static function DeleteNoDemand($userID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($userID))
			return false;

		$userID = intval($userID);
		$bSuccess = True;

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_user_events WHERE USER_ID = ".$userID."", true);

		return $bSuccess;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserEvents::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sonet_user_events", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_sonet_user_events SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		else
		{
			$ID = False;
		}

		return $ID;
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

		$dbResult = CSocNetUserEvents::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}
	
	/***************************************/
	/**********  COMMON METHODS  ***********/
	/***************************************/
	public static function GetEventSite($userID, $event, $defSiteID)
	{
		global $arSocNetUserEvents;

		$userID = intval($userID);
		if ($userID <= 0)
			return false;
		$event = mb_strtoupper(Trim($event));
		if (!in_array($event, $arSocNetUserEvents))
			return false;

		$arUserEvents = array();
		if (isset($GLOBALS["SONET_USER_EVENTS_".$userID]) && is_array($GLOBALS["SONET_USER_EVENTS_".$userID]) && !in_array("SONET_USER_EVENTS_".$userID, $_REQUEST))
		{
			$arUserEvents = $GLOBALS["SONET_USER_EVENTS_".$userID];
		}
		else
		{
			$dbResult = CSocNetUserEvents::GetList(Array(), Array("USER_ID" => $userID));
			while ($arResult = $dbResult->Fetch())
				$arUserEvents[$arResult["EVENT_ID"]] = (($arResult["ACTIVE"] == "Y") ? $arResult["SITE_ID"] : false);
			$GLOBALS["SONET_USER_EVENTS_".$userID] = $arUserEvents;
		}

		if (!array_key_exists($event, $arUserEvents))
			return $defSiteID;

		return $arUserEvents[$event];
	}
}
