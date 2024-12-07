<?php

use Bitrix\Main\Application;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/affiliate_plan.php");

class CSaleAffiliatePlan extends CAllSaleAffiliatePlan
{
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (empty($arSelectFields) || !is_array($arSelectFields))
		{
			$arSelectFields = [
				'ID',
				'SITE_ID',
				'NAME',
				'DESCRIPTION',
				'TIMESTAMP_X',
				'ACTIVE',
				'BASE_RATE',
				'BASE_RATE_TYPE',
				'BASE_RATE_CURRENCY',
				'MIN_PAY',
				'MIN_PLAN_VALUE',
			];
		}

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "AP.ID", "TYPE" => "int"),
				"SITE_ID" => array("FIELD" => "AP.SITE_ID", "TYPE" => "string"),
				"NAME" => array("FIELD" => "AP.NAME", "TYPE" => "string"),
				"DESCRIPTION" => array("FIELD" => "AP.DESCRIPTION", "TYPE" => "string"),
				"TIMESTAMP_X" => array("FIELD" => "AP.TIMESTAMP_X", "TYPE" => "datetime"),
				"ACTIVE" => array("FIELD" => "AP.ACTIVE", "TYPE" => "char"),
				"BASE_RATE" => array("FIELD" => "AP.BASE_RATE", "TYPE" => "double"),
				"BASE_RATE_TYPE" => array("FIELD" => "AP.BASE_RATE_TYPE", "TYPE" => "char"),
				"BASE_RATE_CURRENCY" => array("FIELD" => "AP.BASE_RATE_CURRENCY", "TYPE" => "string"),
				"MIN_PAY" => array("FIELD" => "AP.MIN_PAY", "TYPE" => "double"),
				"MIN_PLAN_VALUE" => array("FIELD" => "AP.MIN_PLAN_VALUE", "TYPE" => "double"),
				"MIN_PLAN_SUM" => array("FIELD" => "AP.MIN_PLAN_VALUE", "TYPE" => "double", "WHERE" => array("CSaleAffiliatePlan", "PrepareCurrency4Where")),

				"SECTION_ID" => array("FIELD" => "APS.ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_affiliate_plan_section APS ON (AP.ID = APS.PLAN_ID)"),
				"SECTION_MODULE_ID" => array("FIELD" => "APS.MODULE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_affiliate_plan_section APS ON (AP.ID = APS.PLAN_ID)"),
				"SECTION_SECTION_ID" => array("FIELD" => "APS.SECTION_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_affiliate_plan_section APS ON (AP.ID = APS.PLAN_ID)"),
				"SECTION_RATE" => array("FIELD" => "APS.RATE", "TYPE" => "double", "FROM" => "LEFT JOIN b_sale_affiliate_plan_section APS ON (AP.ID = APS.PLAN_ID)"),
				"SECTION_RATE_TYPE" => array("FIELD" => "APS.RATE_TYPE", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_affiliate_plan_section APS ON (AP.ID = APS.PLAN_ID)"),
				"SECTION_RATE_CURRENCY" => array("FIELD" => "APS.RATE_CURRENCY", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_affiliate_plan_section APS ON (AP.ID = APS.PLAN_ID)"),
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_affiliate_plan AP ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sale_affiliate_plan AP ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		$topCount = 0;
		$useNavParams = is_array($arNavStartParams);
		if ($useNavParams && isset($arNavStartParams['nTopCount']))
		{
			$topCount = (int)$arNavStartParams['nTopCount'];
		}

		if ($useNavParams && $topCount <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_affiliate_plan AP ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

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

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if ($useNavParams && $topCount > 0)
			{
				$strSql .= 'LIMIT ' . $topCount;
			}

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

		if (!CSaleAffiliatePlan::CheckFields("ADD", $arFields, 0))
		{
			return false;
		}

		foreach (GetModuleEvents('sale', 'OnBeforeAffiliatePlanAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				return false;
			}
		}

		if (!isset($arFields1['TIMESTAMP_X']))
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();
			unset($arFields['TIMESTAMP_X']);
			$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
			unset($helper, $connection);
		}

		$arInsert = $DB->PrepareInsert("b_sale_affiliate_plan", $arFields);

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
			"INSERT INTO b_sale_affiliate_plan(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql);

		$ID = intval($DB->LastID());

		foreach (GetModuleEvents('sale', 'OnAfterAffiliatePlanAdd', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

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

		if (!CSaleAffiliatePlan::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		foreach (GetModuleEvents('sale', 'OnBeforeAffiliatePlanUpdate', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields)) === false)
			{
				return false;
			}
		}

		if (!isset($arFields1['TIMESTAMP_X']))
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();
			unset($arFields['TIMESTAMP_X']);
			$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
			unset($helper, $connection);
		}

		$strUpdate = $DB->PrepareUpdate("b_sale_affiliate_plan", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '') $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		$strSql = "UPDATE b_sale_affiliate_plan SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		unset($GLOBALS["SALE_AFFILIATE_PLAN"]["SALE_AFFILIATE_PLAN_CACHE_".$ID]);

		foreach (GetModuleEvents('sale', 'OnAfterAffiliatePlanUpdate', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

		return $ID;
	}
}
