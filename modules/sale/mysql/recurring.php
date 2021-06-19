<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/recurring.php");

/***********************************************************************/
/***********  CSaleRecurring  ******************************************/
/***********************************************************************/
class CSaleRecurring extends CAllSaleRecurring
{
	public static function GetByID($ID)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if (isset($GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID]) && is_array($GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID]) && is_set($GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT SR.ID, SR.USER_ID, SR.MODULE, SR.PRODUCT_ID, SR.PRODUCT_NAME, ".
				"	SR.PRODUCT_URL, SR.PRODUCT_PRICE_ID, SR.RECUR_SCHEME_TYPE, ".
				"	SR.RECUR_SCHEME_LENGTH, SR.WITHOUT_ORDER, SR.PRICE, SR.CURRENCY, SR.ORDER_ID, ".
				"	SR.CANCELED, SR.DESCRIPTION, SR.CALLBACK_FUNC, SR.PRODUCT_PROVIDER_CLASS, ".
				"	SR.REMAINING_ATTEMPTS, SR.SUCCESS_PAYMENT, SR.CANCELED_REASON, ".
				"	".$DB->DateToCharFunction("SR.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
				"	".$DB->DateToCharFunction("SR.DATE_CANCELED", "FULL")." as DATE_CANCELED, ".
				"	".$DB->DateToCharFunction("SR.PRIOR_DATE", "FULL")." as PRIOR_DATE, ".
				"	".$DB->DateToCharFunction("SR.NEXT_DATE", "FULL")." as NEXT_DATE ".
				"FROM b_sale_recurring SR WHERE SR.ID = ".$ID;

			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($res = $db_res->Fetch())
			{
				$GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID] = $res;
				return $res;
			}
		}

		return false;
	}

	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (empty($arSelectFields))
			$arSelectFields = array("ID", "USER_ID", "MODULE", "PRODUCT_ID", "PRODUCT_NAME", "PRODUCT_URL", "PRODUCT_PRICE_ID", "RECUR_SCHEME_TYPE", "RECUR_SCHEME_LENGTH", "WITHOUT_ORDER", "PRICE", "CURRENCY", "ORDER_ID", "CANCELED", "DATE_CANCELED", "CANCELED_REASON", "CALLBACK_FUNC", "PRODUCT_PROVIDER_CLASS", "DESCRIPTION", "TIMESTAMP_X", "PRIOR_DATE", "NEXT_DATE", "REMAINING_ATTEMPTS", "SUCCESS_PAYMENT");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "SR.ID", "TYPE" => "int"),
				"USER_ID" => array("FIELD" => "SR.USER_ID", "TYPE" => "int"),
				"MODULE" => array("FIELD" => "SR.MODULE", "TYPE" => "string"),
				"PRODUCT_ID" => array("FIELD" => "SR.PRODUCT_ID", "TYPE" => "int"),
				"PRODUCT_NAME" => array("FIELD" => "SR.PRODUCT_NAME", "TYPE" => "string"),
				"PRODUCT_URL" => array("FIELD" => "SR.PRODUCT_URL", "TYPE" => "string"),
				"PRODUCT_PRICE_ID" => array("FIELD" => "SR.PRODUCT_PRICE_ID", "TYPE" => "int"),
				"RECUR_SCHEME_TYPE" => array("FIELD" => "SR.RECUR_SCHEME_TYPE", "TYPE" => "char"),
				"RECUR_SCHEME_LENGTH" => array("FIELD" => "SR.RECUR_SCHEME_LENGTH", "TYPE" => "int"),
				"WITHOUT_ORDER" => array("FIELD" => "SR.WITHOUT_ORDER", "TYPE" => "char"),
				"PRICE" => array("FIELD" => "SR.PRICE", "TYPE" => "double"),
				"CURRENCY" => array("FIELD" => "SR.CURRENCY", "TYPE" => "string"),
				"ORDER_ID" => array("FIELD" => "SR.ORDER_ID", "TYPE" => "int"),
				"CANCELED" => array("FIELD" => "SR.CANCELED", "TYPE" => "char"),
				"DATE_CANCELED" => array("FIELD" => "SR.DATE_CANCELED", "TYPE" => "datetime"),
				"CANCELED_REASON" => array("FIELD" => "SR.CANCELED_REASON", "TYPE" => "string"),
				"CALLBACK_FUNC" => array("FIELD" => "SR.CALLBACK_FUNC", "TYPE" => "string"),
				"PRODUCT_PROVIDER_CLASS" => array("FIELD" => "SR.PRODUCT_PROVIDER_CLASS", "TYPE" => "string"),
				"DESCRIPTION" => array("FIELD" => "SR.DESCRIPTION", "TYPE" => "string"),
				"TIMESTAMP_X" => array("FIELD" => "SR.TIMESTAMP_X", "TYPE" => "datetime"),
				"PRIOR_DATE" => array("FIELD" => "SR.PRIOR_DATE", "TYPE" => "datetime"),
				"NEXT_DATE" => array("FIELD" => "SR.NEXT_DATE", "TYPE" => "datetime"),
				"REMAINING_ATTEMPTS" => array("FIELD" => "SR.REMAINING_ATTEMPTS", "TYPE" => "int"),
				"SUCCESS_PAYMENT" => array("FIELD" => "SR.SUCCESS_PAYMENT", "TYPE" => "char"),
				"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (SR.USER_ID = U.ID)"),
				"USER_ACTIVE" => array("FIELD" => "U.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_user U ON (SR.USER_ID = U.ID)"),
				"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (SR.USER_ID = U.ID)"),
				"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (SR.USER_ID = U.ID)"),
				"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (SR.USER_ID = U.ID)"),
				"USER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (SR.USER_ID = U.ID)")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_recurring SR ".
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
			"FROM b_sale_recurring SR ".
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
				"FROM b_sale_recurring SR ".
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

		if (!CSaleRecurring::CheckFields("ADD", $arFields, 0))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_recurring", $arFields);

		$strSql =
			"INSERT INTO b_sale_recurring(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		return $ID;
	}
}
