<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/messages.php");

class CSocNetMessages extends CAllSocNetMessages
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function Add($arFields)
	{
		global $DB;

		if (IsModuleInstalled("im") && CModule::IncludeModule("im"))
		{
			if ($arFields["MESSAGE_TYPE"] == SONET_MESSAGE_SYSTEM)
			{
				$ID = CIMNotify::Add($arFields);
				return $ID;
			}
			else
			{
				CIMMessenger::SpeedFileDelete($arFields['TO_USER_ID'], IM_SPEED_MESSAGE);
			}
		}

		if (defined("INTASK_SKIP_SOCNET_MESSAGES1") && INTASK_SKIP_SOCNET_MESSAGES1)
			$arFields["=DATE_VIEW"] = $DB->CurrentTimeFunction();

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetMessages::CheckFields("ADD", $arFields))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetMessagesAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;

		$arInsert = $DB->PrepareInsert("b_sonet_messages", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToInsert($arFields1, $arInsert);

		$ID = false;
		if (strlen($arInsert[0]) > 0)
		{
			$strSql =
				"INSERT INTO b_sonet_messages(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());

			$events = GetModuleEvents("socialnetwork", "OnSocNetMessagesAdd");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			//CSocNetMessages::SendEvent($ID, "SONET_NEW_MESSAGE");

			CSocNetMessages::__SpeedFileCreate($arFields["TO_USER_ID"]);
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetMessages::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetMessagesUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields))===false)
				return false;

		$strUpdate = $DB->PrepareUpdate("b_sonet_messages", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_messages SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$events = GetModuleEvents("socialnetwork", "OnSocNetMessagesUpdate");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));
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
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "FROM_USER_ID", "TO_USER_ID", "TITLE", "MESSAGE", "DATE_CREATE", "DATE_VIEW", "MESSAGE_TYPE", "FROM_DELETED", "TO_DELETED");

		if (
			count($arFilter) <= 0
			|| 
			(
				!array_key_exists("IS_LOG_ALL", $arFilter)			
				&& !array_key_exists("IS_LOG", $arFilter)
				&& !array_key_exists("!IS_LOG", $arFilter)				
			)
		)
			$arFilter["!IS_LOG"] = "Y";
		
		if (array_key_exists("IS_LOG_ALL", $arFilter))
		{
			unset($arFilter["IS_LOG"]);
			unset($arFilter["!IS_LOG"]);
			unset($arFilter["IS_LOG_ALL"]);
		}

		$online_interval = (array_key_exists("ONLINE_INTERVAL", $arFilter) && intval($arFilter["ONLINE_INTERVAL"]) > 0 ? $arFilter["ONLINE_INTERVAL"] : 120);

		static $arFields = array(
			"ID" => Array("FIELD" => "M.ID", "TYPE" => "int"),
			"FROM_USER_ID" => Array("FIELD" => "M.FROM_USER_ID", "TYPE" => "int"),
			"TO_USER_ID" => Array("FIELD" => "M.TO_USER_ID", "TYPE" => "int"),
			"TITLE" => Array("FIELD" => "M.TITLE", "TYPE" => "string"),
			"MESSAGE" => Array("FIELD" => "M.MESSAGE", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "M.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_VIEW" => Array("FIELD" => "M.DATE_VIEW", "TYPE" => "datetime"),
			"MESSAGE_TYPE" => Array("FIELD" => "M.MESSAGE_TYPE", "TYPE" => "string"),
			"FROM_DELETED" => Array("FIELD" => "M.FROM_DELETED", "TYPE" => "string"),
			"TO_DELETED" => Array("FIELD" => "M.TO_DELETED", "TYPE" => "string"),
			"SEND_MAIL" => Array("FIELD" => "M.SEND_MAIL", "TYPE" => "string"),
			"IS_LOG" => Array("FIELD" => "M.IS_LOG", "TYPE" => "string"),
			"EMAIL_TEMPLATE" => Array("FIELD" => "M.EMAIL_TEMPLATE", "TYPE" => "string"),
			"FROM_USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)"),
			"FROM_USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)"),
			"FROM_USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)"),
			"FROM_USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)"),
			"FROM_USER_PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)"),
			"FROM_USER_PERSONAL_GENDER" => Array("FIELD" => "U.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)"),
			"FROM_USER_LID" => Array("FIELD" => "U.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)"),
			"TO_USER_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
			"TO_USER_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
			"TO_USER_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
			"TO_USER_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
			"TO_USER_EMAIL" => Array("FIELD" => "U1.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
			"TO_USER_PERSONAL_PHOTO" => Array("FIELD" => "U1.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
			"TO_USER_PERSONAL_GENDER" => Array("FIELD" => "U1.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
			"TO_USER_LID" => Array("FIELD" => "U1.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)"),
		);
		$arFields["FROM_USER_IS_ONLINE"] = Array("FIELD" => "IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N')", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (M.FROM_USER_ID = U.ID)");
		$arFields["TO_USER_IS_ONLINE"] = Array("FIELD" => "IF(U1.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N')", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (M.TO_USER_ID = U1.ID)");

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_messages M ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}


		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_messages M ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_messages M ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (strlen($arSqls["GROUPBY"]) <= 0)
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
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".IntVal($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	function GetChatLastDate($currentUserID, $userID)
	{
		global $DB;

		$currentUserID = IntVal($currentUserID);
		if ($currentUserID <= 0)
			return false;
		$userID = IntVal($userID);
		if ($userID <= 0)
			return false;

		$date = "";

		$strSql =
			"SELECT DATE_FORMAT(MAX(DATE_CREATE), '%Y-%m-%d 00:00:00') as DDD ".
			"FROM b_sonet_messages ".
			"WHERE ".
			"	(TO_USER_ID = ".$currentUserID." ".
			"	AND FROM_USER_ID = ".$userID." ".
			"	AND TO_DELETED = 'N' ".
			"	OR FROM_USER_ID = ".$currentUserID." ".
			"	AND TO_USER_ID = ".$userID." ".
			"	AND FROM_DELETED = 'N' ) ".
			"	AND MESSAGE_TYPE = 'P' ";

		$dbResult = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($arResult = $dbResult->Fetch())
			$date = $arResult["DDD"];

		$date = Trim($date);
		if (StrLen($date) <= 0)
			$date = date("Y-m-d 00:00:00");
		
		return $date;
	}

	function GetMessagesForChat($currentUserID, $userID, $date = false, $arNavStartParams = false, $replyMessId=false)
	{
		global $DB;

		$currentUserID = IntVal($currentUserID);
		if ($currentUserID <= 0)
			return false;

		$userID = IntVal($userID);

		if ($date !== false)
		{
			$date = Trim($date);
			if (StrLen($date) <= 0)
				return false;

			if (!preg_match("#\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d#i", $date))
				return false;
		}

		$replyMessId = intval($replyMessId);

		//time zone
		$diff = false;
		if(CTimeZone::Enabled())
			$diff = CTimeZone::GetOffset();

		if($diff !== false && $diff <> 0)
			$sDateFmt = "DATE_FORMAT(DATE_ADD(DATE_CREATE, INTERVAL ".$diff." SECOND), '%Y-%m-%d %H:%i:%s') as DATE_CREATE_FMT, ";			
		else
			$sDateFmt = "DATE_FORMAT(DATE_CREATE, '%Y-%m-%d %H:%i:%s') as DATE_CREATE_FMT, ";

		$strSql =
			"SELECT 'IN' as WHO, ID, FROM_USER_ID as USER_ID, TITLE, MESSAGE, DATE_VIEW as DATE_VIEW, DATE_CREATE, ".
			"	".$sDateFmt.
			"	".$DB->DateToCharFunction("DATE_CREATE", "FULL")." as DATE_CREATE_FORMAT ".
			"FROM b_sonet_messages ".
			"WHERE TO_USER_ID = ".$currentUserID." ".
			($userID > 0? "	AND FROM_USER_ID = ".$userID." ":"").
			"	AND TO_DELETED = 'N' ".
			"	AND (IS_LOG IS NULL OR NOT IS_LOG = 'Y') ".
			(($date !== false && $replyMessId <=0) ? " AND MESSAGE_TYPE = 'P' AND DATE_CREATE > '".$DB->ForSql($date)."' " : "").
			(($replyMessId > 0) ? " AND MESSAGE_TYPE = 'P' AND ID >= '".$replyMessId."' " : "").
			"UNION ALL ".
			"SELECT 'OUT' as WHO, ID, TO_USER_ID as USER_ID, TITLE, MESSAGE, DATE_CREATE as DATE_VIEW, DATE_CREATE, ".
			"	".$sDateFmt.
			"	".$DB->DateToCharFunction("DATE_CREATE", "FULL")." as DATE_CREATE_FORMAT ".
			"FROM b_sonet_messages ".
			"WHERE FROM_USER_ID = ".$currentUserID." ".
			($userID > 0? "	AND TO_USER_ID = ".$userID." ":"").
			"	AND FROM_DELETED = 'N' ".
			"	AND (IS_LOG IS NULL OR NOT IS_LOG = 'Y') ".
			(($date !== false && $replyMessId <=0) ? " AND MESSAGE_TYPE = 'P' AND DATE_CREATE > '".$DB->ForSql($date)."' " : "").
			(($replyMessId > 0) ? " AND MESSAGE_TYPE = 'P' AND ID >= '".$replyMessId."' " : "").
			"ORDER BY DATE_CREATE ".(($date !== false) ? "ASC" : "DESC")." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT(M.ID) as CNT ".
				"FROM b_sonet_messages M ".
				"WHERE (M.TO_USER_ID = ".$currentUserID." ".
				($userID > 0? "	AND M.FROM_USER_ID = ".$userID." ":"").
				"	AND M.TO_DELETED = 'N' ".
				"	OR ".
				"	M.FROM_USER_ID = ".$currentUserID." ".
				($userID > 0? "	AND M.TO_USER_ID = ".$userID." ":"").
				"	AND M.FROM_DELETED = 'N') ".
				"	AND (IS_LOG IS NULL OR NOT IS_LOG = 'Y') ".
				(($date !== false || $replyMessId > 0) ? " AND M.MESSAGE_TYPE = 'P' " : "");

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arRes = $dbRes->Fetch())
				$cnt = $arRes["CNT"];

			$dbRes = new CDBResult();

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".IntVal($arNavStartParams["nTopCount"]);

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	function GetMessagesUsers($userID, $arNavStartParams = false, $online_interval = 120)
	{
		global $DB;

		$userID = IntVal($userID);
		if ($userID <= 0)
			return false;

		$strSql =
			"SELECT U.ID, U.ACTIVE, U.LOGIN, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.PERSONAL_PHOTO, U.PERSONAL_GENDER, COUNT(M.ID) as TOTAL, MAX(M.DATE_CREATE) as MAX_DATE, ".
			"	IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".intval($online_interval)." SECOND), 'Y', 'N') IS_ONLINE, ".
			"	".$DB->DateToCharFunction("MAX(M.DATE_CREATE)", "FULL")." as MAX_DATE_FORMAT, ".
			"	SUM(CASE WHEN M.DATE_VIEW IS NULL AND M.TO_USER_ID = ".$userID." THEN 1 ELSE 0 END) as UNREAD ".
			"FROM b_user U, b_sonet_messages M ".
			"WHERE ".
			"	(M.IS_LOG IS NULL OR NOT M.IS_LOG = 'Y') ".
			"	AND ( ".
			"	M.TO_USER_ID = ".$userID." ".
			"	AND M.FROM_USER_ID = U.ID ".
			"	AND M.TO_DELETED = 'N' ".
			"	OR ".
			"	M.FROM_USER_ID = ".$userID." ".
			"	AND M.TO_USER_ID = U.ID ".
			"	AND M.FROM_DELETED = 'N' ".
			"	) ".
			"GROUP BY U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.PERSONAL_PHOTO, U.PERSONAL_GENDER ".
			"ORDER BY UNREAD DESC, MAX_DATE DESC ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT DISTINCT FROM_USER_ID ".
				"FROM b_sonet_messages M ".
				"WHERE ".
					"(M.IS_LOG IS NULL OR NOT M.IS_LOG = 'Y') ".
					"AND M.TO_USER_ID = ".$userID." ".
					"AND M.TO_DELETED = 'N' ".

				"UNION DISTINCT ".

				"SELECT DISTINCT TO_USER_ID ".
				"FROM b_sonet_messages ".
				"WHERE ".
					"(IS_LOG IS NULL OR NOT IS_LOG = 'Y') ".
					"AND FROM_USER_ID = ".$userID." ".
					"AND FROM_DELETED = 'N'";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($dbRes)
				$cnt = $dbRes->SelectedRowsCount();

			$dbRes = new CDBResult();
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".IntVal($arNavStartParams["nTopCount"]);

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	function Now()
	{
		global $DB;

		$strSql = "SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') as T ";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
			return $arRes["T"];
		else
			return date("Y-m-d H:i:s");
	}
}
?>