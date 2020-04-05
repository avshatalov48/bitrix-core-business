<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/user_group.php");

class CSocNetUserToGroup extends CAllSocNetUserToGroup
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function Add($arFields)
	{
		global $DB, $CACHE_MANAGER;

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserToGroup::CheckFields("ADD", $arFields))
		{
			return false;
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserToGroupAdd");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				return false;
			}
		}

		$arInsert = $DB->PrepareInsert("b_sonet_user2group", $arFields);
		$strUpdate = $DB->PrepareUpdate("b_sonet_user2group", $arFields);

		\Bitrix\Socialnetwork\Util::processEqualityFieldsToInsert($arFields1, $arInsert);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		$ID = false;
		if (strlen($arInsert[0]) > 0)
		{
			$strSql =
				"INSERT INTO b_sonet_user2group(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].") 
				ON DUPLICATE KEY UPDATE ".$strUpdate;

			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());
		}

		if ($ID)
		{
			CSocNetGroup::SetStat($arFields["GROUP_ID"]);
			CSocNetSearch::OnUserRelationsChange($arFields["USER_ID"]);

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserToGroupAdd");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, &$arFields));
			}

			if (
				$arFields["INITIATED_BY_TYPE"] == SONET_INITIATED_BY_GROUP
				&& $arFields["SEND_MAIL"] != "N"
				&& !IsModuleInstalled("im")
			)
			{
				CSocNetUserToGroup::SendEvent($ID, "SONET_INVITE_GROUP");
			}

			self::$roleCache[$arFields["USER_ID"]."_".$arFields["GROUP_ID"]] = array(
				"ROLE" => $arFields["ROLE"],
				"AUTO_MEMBER" => (isset($arFields["AUTO_MEMBER"]) ? $arFields["AUTO_MEMBER"] : "N")
			);

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$arFields["GROUP_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group_U".$arFields["USER_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group");
			}
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$arUser2GroupOld = CSocNetUserToGroup::GetByID($ID);
		if (!$arUser2GroupOld)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_NO_USER2GROUP"), "ERROR_NO_USER2GROUP");
			return false;
		}

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserToGroup::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserToGroupUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields))===false)
				return false;

		$strUpdate = $DB->PrepareUpdate("b_sonet_user2group", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_user2group SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			CSocNetGroup::SetStat($arUser2GroupOld["GROUP_ID"]);
			CSocNetSearch::OnUserRelationsChange($arUser2GroupOld["USER_ID"]);
			if (
				array_key_exists("GROUP_ID", $arFields)
				&& $arUser2GroupOld["GROUP_ID"] != $arFields["GROUP_ID"]
			)
			{
				CSocNetGroup::SetStat($arFields["GROUP_ID"]);
			}

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserToGroupUpdate");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));
			}

			if (array_key_exists($arUser2GroupOld["USER_ID"]."_".$arUser2GroupOld["GROUP_ID"], self::$roleCache))
			{
				unset(self::$roleCache[$arUser2GroupOld["USER_ID"]."_".$arUser2GroupOld["GROUP_ID"]]);
			}

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$arUser2GroupOld["GROUP_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group_U".$arUser2GroupOld["USER_ID"]);
				$CACHE_MANAGER->ClearByTag("sonet_user2group");
			}
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
		{
			$arSelectFields = array("ID", "USER_ID", "GROUP_ID", "ROLE", "AUTO_MEMBER", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY_TYPE", "INITIATED_BY_USER_ID", "MESSAGE");
		}

		$online_interval = (
			array_key_exists("ONLINE_INTERVAL", $arFilter)
			&& intval($arFilter["ONLINE_INTERVAL"]) > 0
				? $arFilter["ONLINE_INTERVAL"]
				: 120
		);

		static $arFields1 = array(
			"ID" => Array("FIELD" => "UG.ID", "TYPE" => "int"),
			"USER_ID" => Array("FIELD" => "UG.USER_ID", "TYPE" => "int"),
			"GROUP_ID" => Array("FIELD" => "UG.GROUP_ID", "TYPE" => "int"),
			"ROLE" => Array("FIELD" => "UG.ROLE", "TYPE" => "string"),
			"AUTO_MEMBER" => Array("FIELD" => "UG.AUTO_MEMBER", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "UG.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_UPDATE" => Array("FIELD" => "UG.DATE_UPDATE", "TYPE" => "datetime"),
			"INITIATED_BY_TYPE" => Array("FIELD" => "UG.INITIATED_BY_TYPE", "TYPE" => "string"),
			"INITIATED_BY_USER_ID" => Array("FIELD" => "UG.INITIATED_BY_USER_ID", "TYPE" => "int"),
			"MESSAGE" => Array("FIELD" => "UG.MESSAGE", "TYPE" => "string"),
			"GROUP_NAME" => Array("FIELD" => "G.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_DESCRIPTION" => Array("FIELD" => "G.DESCRIPTION", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_ACTIVE" => Array("FIELD" => "G.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_PROJECT" => Array("FIELD" => "G.PROJECT", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_IMAGE_ID" => Array("FIELD" => "G.IMAGE_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_VISIBLE" => Array("FIELD" => "G.VISIBLE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_OWNER_ID" => Array("FIELD" => "G.OWNER_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_INITIATE_PERMS" => Array("FIELD" => "G.INITIATE_PERMS", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_OPENED" => Array("FIELD" => "G.OPENED", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_NUMBER_OF_MEMBERS" => Array("FIELD" => "G.NUMBER_OF_MEMBERS", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_DATE_ACTIVITY" => Array("FIELD" => "G.DATE_ACTIVITY", "TYPE" => "datetime", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"GROUP_CLOSED" => Array("FIELD" => "G.CLOSED", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)"),
			"USER_ACTIVE" => Array("FIELD" => "U.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_WORK_POSITION" => Array("FIELD" => "U.WORK_POSITION", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_EMAIL" => Array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_CONFIRM_CODE" => Array("FIELD" => "U.CONFIRM_CODE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_PERSONAL_GENDER" => Array("FIELD" => "U.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"USER_LID" => Array("FIELD" => "U.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)"),
			"INITIATED_BY_USER_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_EMAIL" => Array("FIELD" => "U1.EMAIL", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_PHOTO" => Array("FIELD" => "U1.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"INITIATED_BY_USER_GENDER" => Array("FIELD" => "U1.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON (UG.INITIATED_BY_USER_ID = U1.ID)"),
			"RAND" => Array("FIELD" => "RAND()", "TYPE" => "string"),
		);
		$arFields["USER_IS_ONLINE"] = Array("FIELD" => "IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N')", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UG.USER_ID = U.ID)");

		if (array_key_exists("GROUP_SITE_ID", $arFilter))
		{
			$arFields["GROUP_SITE_ID"] = Array("FIELD" => "SGS.SITE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group_site SGS ON UG.GROUP_ID = SGS.GROUP_ID");
			$strDistinct = " DISTINCT ";
			foreach ($arSelectFields as $i => $strFieldTmp)
			{
				if ($strFieldTmp == "GROUP_SITE_ID")
				{
					unset($arSelectFields[$i]);
				}
			}

			foreach ($arOrder as $by => $order)
			{
				if (!in_array($by, $arSelectFields))
				{
					$arSelectFields[] = $by;
				}
			}
		}
		else
		{
			$arFields["GROUP_SITE_ID"] = Array("FIELD" => "G.SITE_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (UG.GROUP_ID = G.ID)");
			$strDistinct = " ";
		}

		$arFields = array_merge($arFields1, $arFields);
		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", $strDistinct, $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_user2group UG ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			return (($arRes = $dbRes->Fetch()) ? $arRes["CNT"] : false);
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_user2group UG ".
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
				"FROM b_sonet_user2group UG ".
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
				{
					$cnt = $arRes["CNT"];
				}
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
			if (
				is_array($arNavStartParams)
				&& IntVal($arNavStartParams["nTopCount"]) > 0
			)
			{
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);
			}

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}
?>