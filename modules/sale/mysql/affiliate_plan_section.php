<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/affiliate_plan_section.php");

class CSaleAffiliatePlanSection extends CAllSaleAffiliatePlanSection
{
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "PLAN_ID", "MODULE_ID", "SECTION_ID", "RATE", "RATE_TYPE", "RATE_CURRENCY");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "APS.ID", "TYPE" => "int"),
				"PLAN_ID" => array("FIELD" => "APS.PLAN_ID", "TYPE" => "int"),
				"MODULE_ID" => array("FIELD" => "APS.MODULE_ID", "TYPE" => "string"),
				"SECTION_ID" => array("FIELD" => "APS.SECTION_ID", "TYPE" => "string"),
				"RATE" => array("FIELD" => "APS.RATE", "TYPE" => "double"),
				"RATE_TYPE" => array("FIELD" => "APS.RATE_TYPE", "TYPE" => "char"),
				"RATE_CURRENCY" => array("FIELD" => "APS.RATE_CURRENCY", "TYPE" => "string"),

				"PLAN_SITE_ID" => array("FIELD" => "AP.SITE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_NAME" => array("FIELD" => "AP.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_DESCRIPTION" => array("FIELD" => "AP.DESCRIPTION", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_TIMESTAMP_X" => array("FIELD" => "AP.TIMESTAMP_X", "TYPE" => "datetime", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_ACTIVE" => array("FIELD" => "AP.ACTIVE", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_BASE_RATE" => array("FIELD" => "AP.BASE_RATE", "TYPE" => "double", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_BASE_RATE_TYPE" => array("FIELD" => "AP.BASE_RATE_TYPE", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_BASE_RATE_CURRENCY" => array("FIELD" => "AP.BASE_RATE_CURRENCY", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_MIN_PAY" => array("FIELD" => "AP.MIN_PAY", "TYPE" => "double", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
				"PLAN_MIN_PLAN_VALUE" => array("FIELD" => "AP.MIN_PLAN_VALUE", "TYPE" => "double", "FROM" => "LEFT JOIN b_sale_affiliate_plan AP ON (APS.PLAN_ID = AP.ID)"),
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_affiliate_plan_section APS ".
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
			"FROM b_sale_affiliate_plan_section APS ".
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
				"FROM b_sale_affiliate_plan_section APS ".
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

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleAffiliatePlanSection::CheckFields("ADD", $arFields, 0))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_affiliate_plan_section", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($arInsert[0] <> '')
			{
				$arInsert[0] .= ", ";
				$arInsert[1] .= ", ";
			}
			$arInsert[0] .= $key;
			$arInsert[1] .= $value;
		}

		$strSql =
			"INSERT INTO b_sale_affiliate_plan_section(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql);

		$ID = intval($DB->LastID());

		return $ID;
	}
}
