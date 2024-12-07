<?php

use Bitrix\Main\Application;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/affiliate.php");

class CSaleAffiliate extends CAllSaleAffiliate
{
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (empty($arSelectFields) || !is_array($arSelectFields))
		{
			$arSelectFields = [
				'ID',
				'SITE_ID',
				'USER_ID',
				'AFFILIATE_ID',
				'PLAN_ID',
				'ACTIVE',
				'TIMESTAMP_X',
				'DATE_CREATE',
				'PAID_SUM',
				'APPROVED_SUM',
				'PENDING_SUM',
				'ITEMS_NUMBER',
				'ITEMS_SUM',
				'LAST_CALCULATE',
				'AFF_SITE',
				'AFF_DESCRIPTION',
				'FIX_PLAN',
			];
		}

		// FIELDS -->
		$arFields = array(
			"ID" => array("FIELD" => "A.ID", "TYPE" => "int"),
			"SITE_ID" => array("FIELD" => "A.SITE_ID", "TYPE" => "string"),
			"USER_ID" => array("FIELD" => "A.USER_ID", "TYPE" => "int"),
			"AFFILIATE_ID" => array("FIELD" => "A.AFFILIATE_ID", "TYPE" => "int"),
			"PLAN_ID" => array("FIELD" => "A.PLAN_ID", "TYPE" => "int"),
			"ACTIVE" => array("FIELD" => "A.ACTIVE", "TYPE" => "char"),
			"TIMESTAMP_X" => array("FIELD" => "A.TIMESTAMP_X", "TYPE" => "datetime"),
			"DATE_CREATE" => array("FIELD" => "A.DATE_CREATE", "TYPE" => "datetime"),
			"PAID_SUM" => array("FIELD" => "A.PAID_SUM", "TYPE" => "double"),
			"APPROVED_SUM" => array("FIELD" => "A.APPROVED_SUM", "TYPE" => "double"),
			"PENDING_SUM" => array("FIELD" => "A.PENDING_SUM", "TYPE" => "double"),
			"ITEMS_NUMBER" => array("FIELD" => "A.ITEMS_NUMBER", "TYPE" => "int"),
			"ITEMS_SUM" => array("FIELD" => "A.ITEMS_SUM", "TYPE" => "double"),
			"LAST_CALCULATE" => array("FIELD" => "A.LAST_CALCULATE", "TYPE" => "datetime"),
			"AFF_SITE" => array("FIELD" => "A.AFF_SITE", "TYPE" => "string"),
			"AFF_DESCRIPTION" => array("FIELD" => "A.AFF_DESCRIPTION", "TYPE" => "string"),
			"FIX_PLAN" => array("FIELD" => "A.FIX_PLAN", "TYPE" => "char"),

			"PLAN_SITE_ID" => array("FIELD" => "AP.SITE_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_NAME" => array("FIELD" => "AP.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_DESCRIPTION" => array("FIELD" => "AP.DESCRIPTION", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_TIMESTAMP_X" => array("FIELD" => "AP.TIMESTAMP_X", "TYPE" => "datetime", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_ACTIVE" => array("FIELD" => "AP.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_BASE_RATE" => array("FIELD" => "AP.BASE_RATE", "TYPE" => "double", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_BASE_RATE_TYPE" => array("FIELD" => "AP.BASE_RATE_TYPE", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_BASE_RATE_CURRENCY" => array("FIELD" => "AP.BASE_RATE_CURRENCY", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_MIN_PAY" => array("FIELD" => "AP.MIN_PAY", "TYPE" => "double", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),
			"PLAN_MIN_PLAN_VALUE" => array("FIELD" => "AP.MIN_PLAN_VALUE", "TYPE" => "double", "FROM" => "INNER JOIN b_sale_affiliate_plan AP ON (A.PLAN_ID = AP.ID)"),

			"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
			"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
			"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
			"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),
			"USER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (A.USER_ID = U.ID)"),

			"ORDER_ID" => array("FIELD" => "O.ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order O ON (A.ID = O.AFFILIATE_ID)"),
			"ORDER_DATE_ALLOW_DELIVERY" => array("FIELD" => "O.DATE_ALLOW_DELIVERY", "TYPE" => "datetime", "FROM" => "INNER JOIN b_sale_order O ON (A.ID = O.AFFILIATE_ID)"),
			"ORDER_ALLOW_DELIVERY" => array("FIELD" => "O.ALLOW_DELIVERY", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order O ON (A.ID = O.AFFILIATE_ID)"),
		);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_affiliate A ".
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
			"FROM b_sale_affiliate A ".
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
				"FROM b_sale_affiliate A ".
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

		if (!CSaleAffiliate::CheckFields("ADD", $arFields, 0))
		{
			return false;
		}

		foreach (GetModuleEvents('sale', 'OnBeforeBAffiliateAdd', true) as $arEvent)
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

		$arInsert = $DB->PrepareInsert("b_sale_affiliate", $arFields);

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
			"INSERT INTO b_sale_affiliate(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql);

		$ID = intval($DB->LastID());

		foreach (GetModuleEvents('sale', 'OnAfterBAffiliateAdd', true) as $arEvent)
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

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleAffiliate::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		foreach (GetModuleEvents('sale', 'OnBeforeAffiliateUpdate', true) as $arEvent)
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

		$strUpdate = $DB->PrepareUpdate("b_sale_affiliate", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '')
				$strUpdate = ", ".$strUpdate;
			$strUpdate = $key."=".$value.$strUpdate;
		}

		$strSql = "UPDATE b_sale_affiliate SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		unset($GLOBALS["SALE_AFFILIATE"]["SALE_AFFILIATE_CACHE_".$ID]);

		foreach (GetModuleEvents('sale', 'OnAfterAffiliateUpdate', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

		return $ID;
	}
}
