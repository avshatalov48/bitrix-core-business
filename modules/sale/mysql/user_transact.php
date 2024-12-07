<?php

use Bitrix\Main\Application;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/user_transact.php");

class CSaleUserTransact extends CAllSaleUserTransact
{
	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		$strSql =
			"SELECT UT.ID, UT.USER_ID, UT.AMOUNT, UT.CURRENCY, UT.DEBIT, UT.DESCRIPTION, ".
			"	UT.ORDER_ID, UT.NOTES, UT.EMPLOYEE_ID, ".
			"	".$DB->DateToCharFunction("UT.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
			"	".$DB->DateToCharFunction("UT.TRANSACT_DATE", "FULL")." as TRANSACT_DATE ".
			"FROM b_sale_user_transact UT ".
			"WHERE UT.ID = ".$ID." ";

		$db_res = $DB->Query($strSql);
		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}

	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "USER_ID", "TIMESTAMP_X", "TRANSACT_DATE", "AMOUNT", "CURRENCY", "DEBIT", "ORDER_ID", "DESCRIPTION", "NOTES");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "UT.ID", "TYPE" => "int"),
				"USER_ID" => array("FIELD" => "UT.USER_ID", "TYPE" => "int"),
				"AMOUNT" => array("FIELD" => "UT.AMOUNT", "TYPE" => "double"),
				"CURRENCY" => array("FIELD" => "UT.CURRENCY", "TYPE" => "string"),
				"DEBIT" => array("FIELD" => "UT.DEBIT", "TYPE" => "char"),
				"ORDER_ID" => array("FIELD" => "UT.ORDER_ID", "TYPE" => "int"),
				"DESCRIPTION" => array("FIELD" => "UT.DESCRIPTION", "TYPE" => "string"),
				"NOTES" => array("FIELD" => "UT.NOTES", "TYPE" => "string"),
				"TIMESTAMP_X" => array("FIELD" => "UT.TIMESTAMP_X", "TYPE" => "datetime"),
				"TRANSACT_DATE" => array("FIELD" => "UT.TRANSACT_DATE", "TYPE" => "datetime"),
				"EMPLOYEE_ID" => array("FIELD" => "UT.EMPLOYEE_ID", "TYPE" => "int"),
				"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UT.USER_ID = U.ID)"),
				"USER_ACTIVE" => array("FIELD" => "U.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_user U ON (UT.USER_ID = U.ID)"),
				"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UT.USER_ID = U.ID)"),
				"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UT.USER_ID = U.ID)"),
				"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UT.USER_ID = U.ID)"),
				"USER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UT.USER_ID = U.ID)")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_user_transact UT ".
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
			"FROM b_sale_user_transact UT ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		$nTopCount = (int)($arNavStartParams["nTopCount"] ?? 0);
		if (is_array($arNavStartParams) && $nTopCount <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_user_transact UT ".
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
			if ($nTopCount > 0)
			{
				$strSql .= "LIMIT " . $nTopCount;
			}

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
		}

		return $dbRes;
	}

	public static function Add($arFields)
	{
		global $DB;

		$arFields1 = [];
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleUserTransact::CheckFields("ADD", $arFields, 0))
		{
			return false;
		}

		if (!isset($arFields1['TIMESTAMP_X']))
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();
			unset($arFields['TIMESTAMP_X']);
			$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
			unset($helper, $connection);
		}

		$arInsert = $DB->PrepareInsert("b_sale_user_transact", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($arInsert[0] <> '') $arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if ($arInsert[1] <> '') $arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$strSql =
			"INSERT INTO b_sale_user_transact(".$arInsert[0].") ".
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
		{
			return false;
		}

		$arFields1 = [];
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleUserTransact::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		if (!isset($arFields1['TIMESTAMP_X']))
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();
			unset($arFields['TIMESTAMP_X']);
			$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
			unset($helper, $connection);
		}

		$strUpdate = $DB->PrepareUpdate("b_sale_user_transact", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '') $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		$strSql = "UPDATE b_sale_user_transact SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		return $ID;
	}
}
