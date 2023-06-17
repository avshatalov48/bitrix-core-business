<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/user_relations.php");

class CSocNetUserRelations extends CAllSocNetUserRelations
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields)
	{
		global $DB;

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserRelations::CheckFields("ADD", $arFields))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserRelationsAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;

		$arInsert = $DB->PrepareInsert("b_sonet_user_relations", $arFields);
		$strUpdate = $DB->PrepareUpdate("b_sonet_user_relations", $arFields);

		\Bitrix\Socialnetwork\Util::processEqualityFieldsToInsert($arFields1, $arInsert);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		$ID = false;
		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_sonet_user_relations(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].") 
				ON DUPLICATE KEY UPDATE ".$strUpdate;

			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = intval($DB->LastID());

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserRelationsAdd");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, &$arFields));

			if (
				(
					!array_key_exists("SEND_MAIL", $arFields)
					|| $arFields["SEND_MAIL"] != "N"
				)
				&& !IsModuleInstalled("im")
			)
			{
				$mailType = "INVITE_FRIEND";
				if ($arFields["RELATION"] == SONET_RELATIONS_BAN)
					$mailType = "BAN_FRIEND";

				CSocNetUserRelations::SendEvent($ID, $mailType);
			}

			CSocNetSearch::OnUserRelationsChange($arFields["FIRST_USER_ID"]);
			CSocNetSearch::OnUserRelationsChange($arFields["SECOND_USER_ID"]);
		}

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = intval($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserRelations::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserRelationsUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields))===false)
				return false;

		$arUserRelationOld = CSocNetUserRelations::GetByID($ID);

		$strUpdate = $DB->PrepareUpdate("b_sonet_user_relations", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_sonet_user_relations SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserRelationsUpdate");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			if (
				(!array_key_exists("SEND_MAIL", $arFields)
				|| $arFields["SEND_MAIL"] != "N")
				&& !IsModuleInstalled("im")
			)
			{
				$mailType = "";
				if ($arUserRelationOld["RELATION"] != SONET_RELATIONS_FRIEND && $arFields["RELATION"] == SONET_RELATIONS_FRIEND)
					$mailType = "AGREE_FRIEND";
				elseif ($arUserRelationOld["RELATION"] != SONET_RELATIONS_BAN && $arFields["RELATION"] == SONET_RELATIONS_BAN)
					$mailType = "BAN_FRIEND";
				elseif ($arUserRelationOld["RELATION"] != SONET_RELATIONS_REQUEST && $arFields["RELATION"] == SONET_RELATIONS_REQUEST)
					$mailType = "INVITE_FRIEND";

				if ($mailType <> '')
					CSocNetUserRelations::SendEvent($ID, $mailType);
			}

			CSocNetSearch::OnUserRelationsChange($arUserRelationOld["FIRST_USER_ID"]);
			CSocNetSearch::OnUserRelationsChange($arUserRelationOld["SECOND_USER_ID"]);
		}
		else
			$ID = False;

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "RELATION", "DATE_CREATE", "DATE_UPDATE", "MESSAGE", "INITIATED_BY");

		$online_interval = (array_key_exists("ONLINE_INTERVAL", $arFilter) && intval($arFilter["ONLINE_INTERVAL"]) > 0 ? $arFilter["ONLINE_INTERVAL"] : 120);

		static $arFields = array(
			"ID" => Array("FIELD" => "UR.ID", "TYPE" => "int"),
			"FIRST_USER_ID" => Array("FIELD" => "UR.FIRST_USER_ID", "TYPE" => "int"),
			"SECOND_USER_ID" => Array("FIELD" => "UR.SECOND_USER_ID", "TYPE" => "int"),
			"RELATION" => Array("FIELD" => "UR.RELATION", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "UR.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_UPDATE" => Array("FIELD" => "UR.DATE_UPDATE", "TYPE" => "datetime"),
			"MESSAGE" => Array("FIELD" => "UR.MESSAGE", "TYPE" => "string"),
			"INITIATED_BY" => Array("FIELD" => "UR.INITIATED_BY", "TYPE" => "string"),
			"FIRST_USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_EMAIL" => Array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_PERSONAL_GENDER" => Array("FIELD" => "U.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_LID" => Array("FIELD" => "U.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"SECOND_USER_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_EMAIL" => Array("FIELD" => "U1.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_PERSONAL_PHOTO" => Array("FIELD" => "U1.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_PERSONAL_GENDER" => Array("FIELD" => "U1.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_LID" => Array("FIELD" => "U1.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"RAND" => Array("FIELD" => "RAND()", "TYPE" => "string"),
		);
		$arFields["FIRST_USER_IS_ONLINE"] = Array("FIELD" => "IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N')", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)");
		$arFields["SECOND_USER_IS_ONLINE"] = Array("FIELD" => "IF(U1.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N')", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)");

		if (array_key_exists("ACTIVE_ONLY", $arFilter) && $arFilter["ACTIVE_ONLY"] == "Y")
		{
			$arFields["FIRST_USER_IS_ACTIVE"] = Array("FIELD" => "U.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)");
			$arFields["SECOND_USER_IS_ACTIVE"] = Array("FIELD" => "U1.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)");
			$arFilter["FIRST_USER_IS_ACTIVE"] = "Y";
			$arFilter["SECOND_USER_IS_ACTIVE"] = "Y";
		}

		$arSqls2 = null;
		
		if (
			(
				array_key_exists("USER_ID", $arFilter) 
				&& intval($arFilter["USER_ID"]) > 0
			)
			|| (
				array_key_exists("!USER_ID", $arFilter) 
				&& intval($arFilter["!USER_ID"]) > 0
			)
		)
		{
			$key = (array_key_exists("USER_ID", $arFilter) ? "USER_ID" : "!USER_ID");

			$filterVal = $arFilter[$key];
			unset($arFilter[$key]);

			$arFilter2 = $arFilter;

			if ($key == "USER_ID")
			{
				$arFilter2["SECOND_USER_ID"] = $filterVal;
			}
			else
			{
				$arFilter2["!SECOND_USER_ID"] = $filterVal;
			}

			$arSqls2 = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter2, $arGroupBy, $arSelectFields);

			if ($key == "USER_ID")
			{
				$arFilter["FIRST_USER_ID"] = $filterVal;
			}
			else
			{
				$arFilter["!FIRST_USER_ID"] = $filterVal;
			}
		}

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (
			is_array($arGroupBy)
			&& count($arGroupBy) == 0
		)
		{
			if (
				$arSqls2
				&& $arSqls2["WHERE"] <> ''
			)
			{
				$strSql = "SELECT COUNT(*) AS CNT FROM  (";

				$strSql .=
					"SELECT UR.ID ".
					"FROM b_sonet_user_relations UR ".
					"	".$arSqls["FROM"]." ".
					"WHERE ".$arSqls["WHERE"]." ".
					"UNION ".
					"SELECT UR.ID ".
					"FROM b_sonet_user_relations UR ".
					"	".$arSqls["FROM"]." ".
					"WHERE ".$arSqls2["WHERE"]." ";

				if ($arSqls["GROUPBY"] <> '')
				{
					$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
				}

				$strSql .= ") AS RELS";
			}
			else
			{
				$strSql =
					"SELECT ".$arSqls["SELECT"]." ".
					"FROM b_sonet_user_relations UR ".
					"	".$arSqls["FROM"]." ";

				if ($arSqls["WHERE"] <> '')
				{
					$strSql .= "WHERE ".$arSqls["WHERE"]." ";
				}

				if ($arSqls["GROUPBY"] <> '')
				{
					$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
				}
			}

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_user_relations UR ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		}
		if ($arSqls["ORDERBY"] <> '')
		{
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
		}

		if (
			$arSqls2 
			&& $arSqls2["WHERE"] <> ''
		)
		{
			$strSql = "(".$strSql.") ";
			$strSql .=
				"UNION (".
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_user_relations UR ".
				"	".$arSqls["FROM"]." ".
				"WHERE ".$arSqls2["WHERE"]." ";

			if ($arSqls2["ORDERBY"] <> '')
			{
				$strSql .= "ORDER BY ".$arSqls2["ORDERBY"]." ";
			}
			$strSql .= ") ";
		}

		if ($arSqls["GROUPBY"] <> '')
		{
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		}

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_user_relations UR ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// ÒÎËÜÊÎ ÄËß MYSQL!!! ÄËß ORACLE ÄÐÓÃÎÉ ÊÎÄ
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	public static function GetListBirthday($userID, $number = 5, $online_interval = 120)
	{
		global $DB;

		$userID = intval($userID);
		$number = intval($number);

		$curYear = intval(Date('Y'));

		$strSql =
			"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL, U.PERSONAL_PHOTO, U.PERSONAL_GENDER, U.PERSONAL_BIRTHDAY as PB, ".
			"	IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N') IS_ONLINE ".
			"FROM b_sonet_user_relations UR ".
			"	INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID) ".
			"WHERE UR.SECOND_USER_ID = ".$userID." ".
			"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
			"UNION ".
			"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL, U.PERSONAL_PHOTO, U.PERSONAL_GENDER, U.PERSONAL_BIRTHDAY as PB, ".
			"	IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N') IS_ONLINE ".
			"FROM b_sonet_user_relations UR ".
			"	INNER JOIN b_user U ON (UR.SECOND_USER_ID = U.ID) ".
			"WHERE UR.FIRST_USER_ID = ".$userID." ".
			"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
			"ORDER BY IF ( ".
			"	DATE_FORMAT(PB, '".$curYear."-%m-%d') < DATE_FORMAT(CURDATE(), '%Y-%m-%d'), ".
			"	DATE_FORMAT(PB, '".($curYear + 1)."-%m-%d'), ".
			"	DATE_FORMAT(PB, '".$curYear."-%m-%d') ".
			") ".
			($number > 0 ? "LIMIT 0, ".$number."" : "");

		return $DB->Query($strSql);
	}

	public static function GetRelationsTop($userID, $number = 100)
	{
		global $DB;

		$userID = intval($userID);
		$number = intval($number);

		$strSql =
			"SELECT UR.RELATION, UR.FIRST_USER_ID, UR.SECOND_USER_ID ".
			"FROM b_sonet_user_relations UR ".
			"WHERE UR.FIRST_USER_ID = ".$userID." ".
			"UNION ".
			"SELECT UR.RELATION, UR.FIRST_USER_ID, UR.SECOND_USER_ID ".
			"FROM b_sonet_user_relations UR ".
			"WHERE UR.SECOND_USER_ID = ".$userID." ".
			($number > 0 ? "LIMIT 0, ".$number : "");
			
		return $DB->Query($strSql);
	}
}
