<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/affiliate_transact.php");

class CSaleAffiliateTransact extends CAllSaleAffiliateTransact
{
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "AFFILIATE_ID", "TIMESTAMP_X", "TRANSACT_DATE", "AMOUNT", "CURRENCY", "DEBIT", "DESCRIPTION", "EMPLOYEE_ID");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "AT.ID", "TYPE" => "int"),
				"AFFILIATE_ID" => array("FIELD" => "AT.AFFILIATE_ID", "TYPE" => "int"),
				"AMOUNT" => array("FIELD" => "AT.AMOUNT", "TYPE" => "double"),
				"CURRENCY" => array("FIELD" => "AT.CURRENCY", "TYPE" => "string"),
				"DEBIT" => array("FIELD" => "AT.DEBIT", "TYPE" => "char"),
				"DESCRIPTION" => array("FIELD" => "AT.DESCRIPTION", "TYPE" => "string"),
				"TIMESTAMP_X" => array("FIELD" => "AT.TIMESTAMP_X", "TYPE" => "datetime"),
				"TRANSACT_DATE" => array("FIELD" => "AT.TRANSACT_DATE", "TYPE" => "datetime"),
				"EMPLOYEE_ID" => array("FIELD" => "AT.EMPLOYEE_ID", "TYPE" => "int"),

				"AFFILIATE_SITE_ID" => array("FIELD" => "A.SITE_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_affiliate A ON (AT.AFFILIATE_ID = A.ID)"),
				"AFFILIATE_USER_ID" => array("FIELD" => "A.USER_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_affiliate A ON (AT.AFFILIATE_ID = A.ID)"),
				"AFFILIATE_PLAN_ID" => array("FIELD" => "A.PLAN_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_affiliate A ON (AT.AFFILIATE_ID = A.ID)"),
				"AFFILIATE_ACTIVE" => array("FIELD" => "A.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_affiliate A ON (AT.AFFILIATE_ID = A.ID)"),

				"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
				"USER_ACTIVE" => array("FIELD" => "U.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
				"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
				"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
				"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
				"USER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_affiliate_transact AT ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
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
			"FROM b_sale_affiliate_transact AT ".
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
				"FROM b_sale_affiliate_transact AT ".
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

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	public static function Add($arFields)
	{
		global $DB;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleAffiliateTransact::CheckFields("ADD", $arFields, 0))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_affiliate_transact", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($arInsert[0] <> '') $arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if ($arInsert[1] <> '') $arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$strSql =
			"INSERT INTO b_sale_affiliate_transact(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		return $ID;
	}
}
