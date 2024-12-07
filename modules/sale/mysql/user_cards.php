<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/user_cards.php");

class CSaleUserCards extends CAllSaleUserCards
{
	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		$strSql = 
			"SELECT UC.ID, UC.USER_ID, UC.SORT, UC.PAY_SYSTEM_ACTION_ID, UC.CURRENCY, UC.CARD_CODE, ".
			"	UC.CARD_TYPE, UC.CARD_NUM, UC.CARD_EXP_MONTH, UC.CARD_EXP_YEAR, UC.DESCRIPTION, ".
			"	UC.SUM_MIN, UC.SUM_MAX, UC.SUM_CURRENCY, UC.LAST_STATUS, UC.LAST_STATUS_CODE, ".
			"	UC.LAST_STATUS_DESCRIPTION, UC.LAST_STATUS_MESSAGE, UC.LAST_SUM, ".
			"	UC.LAST_CURRENCY, UC.ACTIVE, ".
			"	".$DB->DateToCharFunction("UC.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
			"	".$DB->DateToCharFunction("UC.LAST_DATE", "FULL")." as LAST_DATE ".
			"FROM b_sale_user_cards UC ".
			"WHERE UC.ID = ".$ID." ";

		$db_res = $DB->Query($strSql);
		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}

	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "USER_ID", "ACTIVE", "SORT", "PAY_SYSTEM_ACTION_ID", "CURRENCY", "CARD_TYPE", "CARD_NUM", "CARD_CODE", "CARD_EXP_MONTH", "CARD_EXP_YEAR", "DESCRIPTION", "SUM_MIN", "SUM_MAX", "SUM_CURRENCY", "TIMESTAMP_X", "LAST_STATUS", "LAST_STATUS_CODE", "LAST_STATUS_DESCRIPTION", "LAST_STATUS_MESSAGE", "LAST_SUM", "LAST_CURRENCY", "LAST_DATE");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "UC.ID", "TYPE" => "int"),
				"USER_ID" => array("FIELD" => "UC.USER_ID", "TYPE" => "int"),
				"ACTIVE" => array("FIELD" => "UC.ACTIVE", "TYPE" => "char"),
				"SORT" => array("FIELD" => "UC.SORT", "TYPE" => "int"),
				"PAY_SYSTEM_ACTION_ID" => array("FIELD" => "UC.PAY_SYSTEM_ACTION_ID", "TYPE" => "int"),
				"CURRENCY" => array("FIELD" => "UC.CURRENCY", "TYPE" => "string"),
				"CARD_TYPE" => array("FIELD" => "UC.CARD_TYPE", "TYPE" => "string"),
				"CARD_NUM" => array("FIELD" => "UC.CARD_NUM", "TYPE" => "string"),
				"CARD_CODE" => array("FIELD" => "UC.CARD_CODE", "TYPE" => "string"),
				"CARD_EXP_MONTH" => array("FIELD" => "UC.CARD_EXP_MONTH", "TYPE" => "int"),
				"CARD_EXP_YEAR" => array("FIELD" => "UC.CARD_EXP_YEAR", "TYPE" => "int"),
				"DESCRIPTION" => array("FIELD" => "UC.DESCRIPTION", "TYPE" => "string"),
				"SUM_MIN" => array("FIELD" => "UC.SUM_MIN", "TYPE" => "double"),
				"SUM_MAX" => array("FIELD" => "UC.SUM_MAX", "TYPE" => "double"),
				"SUM_CURRENCY" => array("FIELD" => "UC.SUM_CURRENCY", "TYPE" => "string"),
				"TIMESTAMP_X" => array("FIELD" => "UC.TIMESTAMP_X", "TYPE" => "datetime"),
				"LAST_STATUS" => array("FIELD" => "UC.LAST_STATUS", "TYPE" => "char"),
				"LAST_STATUS_CODE" => array("FIELD" => "UC.LAST_STATUS_CODE", "TYPE" => "string"),
				"LAST_STATUS_DESCRIPTION" => array("FIELD" => "UC.LAST_STATUS_DESCRIPTION", "TYPE" => "string"),
				"LAST_STATUS_MESSAGE" => array("FIELD" => "UC.LAST_STATUS_MESSAGE", "TYPE" => "string"),
				"LAST_SUM" => array("FIELD" => "UC.LAST_SUM", "TYPE" => "double"),
				"LAST_CURRENCY" => array("FIELD" => "UC.LAST_CURRENCY", "TYPE" => "string"),
				"LAST_DATE" => array("FIELD" => "UC.LAST_DATE", "TYPE" => "datetime"),
				"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UC.USER_ID = U.ID)"),
				"USER_ACTIVE" => array("FIELD" => "U.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_user U ON (UC.USER_ID = U.ID)"),
				"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UC.USER_ID = U.ID)"),
				"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UC.USER_ID = U.ID)"),
				"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UC.USER_ID = U.ID)"),
				"USER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UC.USER_ID = U.ID)")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_user_cards UC ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql = 
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sale_user_cards UC ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_user_cards UC ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// FOR MYSQL!!! ANOTHER CODE FOR ORACLE
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])>0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
		}

		return $dbRes;
	}

	public static function Add($arFields)
	{
		global $DB;

		if (!CSaleUserCards::CheckFields("ADD", $arFields, 0))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_user_cards", $arFields);

		$strSql =
			"INSERT INTO b_sale_user_cards(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql);

		$ID = intval($DB->LastID());

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		if (!CSaleUserCards::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_user_cards", $arFields);
		$strSql = "UPDATE b_sale_user_cards SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		return $ID;
	}
}
