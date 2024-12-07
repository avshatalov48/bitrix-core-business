<?php

IncludeModuleLangFile(__FILE__);

class CAllSocNetGroupSubject
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION;

		if ($ACTION != "ADD" && intval($ID) <= 0)
		{
			$APPLICATION->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") 
			&& (
				(is_array($arFields["SITE_ID"]) && count($arFields["SITE_ID"]) <= 0)
				||
				(!is_array($arFields["SITE_ID"]) && $arFields["SITE_ID"] == '')
			)
		)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GS_EMPTY_SITE_ID"), "EMPTY_SITE_ID");
			return false;
		}
		elseif (is_set($arFields, "SITE_ID"))
		{
			if(!is_array($arFields["SITE_ID"]))
				$arFields["SITE_ID"] = array($arFields["SITE_ID"]);

			foreach($arFields["SITE_ID"] as $v)
			{
				$dbResult = CSite::GetByID($v);
				if (!$dbResult->Fetch())
				{
					$APPLICATION->ThrowException(str_replace("#ID#", $v, GetMessage("SONET_GS_ERROR_NO_SITE")), "ERROR_NO_SITE");
					return false;
				}
			}
		}

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GS_EMPTY_NAME"), "EMPTY_NAME");
			return false;
		}

		if (is_set($arFields, "SORT") || $ACTION=="ADD")
			$arFields["SORT"] = (intval($arFields["SORT"]) > 0 ? intval($arFields["SORT"]) : 100);
		
		return True;
	}

	public static function Delete($ID)
	{
		global $DB, $CACHE_MANAGER, $APPLICATION;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$bCanDelete = true;
		$dbResult = CSocNetGroup::GetList(
			array(),
			array("SUBJECT_ID" => $ID)
		);
		if ($arResult = $dbResult->Fetch())
			$bCanDelete = false;

		if (!$bCanDelete)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_GS_NOT_EMPTY_SUBJECT"), "NOT_EMPTY_SUBJECT");
			return false;
		}

		$events = GetModuleEvents("socialnetwork", "OnSocNetGroupSubjectDelete");
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array($ID));
		}

		$bSuccess = $DB->Query("DELETE FROM b_sonet_group_subject_site WHERE SUBJECT_ID = ".$ID."", true);

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_group_subject WHERE ID = ".$ID."", true);

		if (CACHED_b_sonet_group_subjects != false)
			$CACHE_MANAGER->CleanDir("b_sonet_group_subjects");

		return $bSuccess;
	}

	public static function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetGroupSubject::CheckFields("UPDATE", $arFields, $ID))
			return false;
		else
		{
			$arSiteID = Array();
			if(is_set($arFields, "SITE_ID"))
			{
				if(is_array($arFields["SITE_ID"]))
					$arSiteID = $arFields["SITE_ID"];
				else
					$arSiteID[] = $arFields["SITE_ID"];

				$arFields["SITE_ID"] = false;
				$str_SiteID = "''";
				foreach($arSiteID as $v)
				{
					$arFields["SITE_ID"] = $v;
					$str_SiteID .= ", '".$DB->ForSql($v)."'";
				}
			}
		}

		$strUpdate = $DB->PrepareUpdate("b_sonet_group_subject", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_sonet_group_subject SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql);

			if(count($arSiteID)>0)
			{
				$strSql = "DELETE FROM b_sonet_group_subject_site WHERE SUBJECT_ID=".$ID;
				$DB->Query($strSql);

				$strSql =
					"INSERT INTO b_sonet_group_subject_site(SUBJECT_ID, SITE_ID) ".
					"SELECT ".$ID.", LID ".
					"FROM b_lang ".
					"WHERE LID IN (".$str_SiteID.") ";
				$DB->Query($strSql);
			}

			$events = GetModuleEvents("socialnetwork", "OnSocNetGroupSubjectUpdate");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, &$arFields));
			}

			if (CACHED_b_sonet_group_subjects != false)
				$CACHE_MANAGER->CleanDir("b_sonet_group_subjects");
		}
		else
			$ID = False;

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetByID($ID)
	{
		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$dbResult = CSocNetGroupSubject::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
			return $arResult;

		return False;
	}

	public static function GetSite($subject_id)
	{
		global $DB;
		$strSql = "SELECT L.*, SGSS.* FROM b_sonet_group_subject_site SGSS, b_lang L WHERE L.LID=SGSS.SITE_ID AND SGSS.SUBJECT_ID=".intval($subject_id);
		return $DB->Query($strSql);
	}
}
