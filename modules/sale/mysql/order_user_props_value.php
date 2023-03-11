<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/order_user_props_value.php");

class CSaleOrderUserPropsValue extends CAllSaleOrderUserPropsValue
{
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = strval($arOrder);
			$arFilter = strval($arFilter);
			if ($arOrder <> '' && $arFilter <> '')
				$arOrder = array($arOrder => $arFilter);
			else
				$arOrder = array();
			if (is_array($arGroupBy))
				$arFilter = $arGroupBy;
			else
				$arFilter = array();
			$arGroupBy = false;

			if (count($arSelectFields) <= 0)
				$arSelectFields = array("ID", "USER_PROPS_ID", "ORDER_PROPS_ID", "USER_VALUE_NAME", "VALUE", "TYPE", "SORT", "VARIANT_NAME", "CODE");
		}

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "USER_PROPS_ID", "ORDER_PROPS_ID", "NAME", "VALUE", "PROP_ID", "PROP_PERSON_TYPE_ID", "PROP_NAME", "PROP_TYPE", "PROP_REQUIED", "PROP_DEFAULT_VALUE", "PROP_SORT", "PROP_USER_PROPS", "PROP_IS_LOCATION", "PROP_PROPS_GROUP_ID", "PROP_SIZE1", "PROP_SIZE2", "PROP_DESCRIPTION", "PROP_IS_EMAIL", "PROP_IS_PROFILE_NAME", "PROP_IS_PAYER", "PROP_IS_LOCATION4TAX", "PROP_IS_ZIP", "PROP_CODE", "VARIANT_ID", "VARIANT_ORDER_PROPS_ID", "VARIANT_NAME", "VARIANT_VALUE", "VARIANT_SORT", "VARIANT_DESCRIPTION");

		// TODO proper compatibility CAllSaleOrderUserPropsValue::getList15
		$sale15converted = \Bitrix\Main\Config\Option::get('main', '~sale_converted_15', 'N') == 'Y';


		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "UP.ID", "TYPE" => "int"),
				"USER_PROPS_ID" => array("FIELD" => "UP.USER_PROPS_ID", "TYPE" => "int"),
				"ORDER_PROPS_ID" => array("FIELD" => "UP.ORDER_PROPS_ID", "TYPE" => "int"),
				"NAME" => array("FIELD" => "UP.NAME", "TYPE" => "string"),

				"PROP_ID" => array("FIELD" => "P.ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_PERSON_TYPE_ID" => array("FIELD" => "P.PERSON_TYPE_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_NAME" => array("FIELD" => "P.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_TYPE" => array("FIELD" => "P.TYPE", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_REQUIED" => array("FIELD" => "P.REQUI".($sale15converted ? 'R' : '')."ED", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_DEFAULT_VALUE" => array("FIELD" => "P.DEFAULT_VALUE", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_SORT" => array("FIELD" => "P.SORT", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_USER_PROPS" => array("FIELD" => "P.USER_PROPS", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_LOCATION" => array("FIELD" => "P.IS_LOCATION", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_PROPS_GROUP_ID" => array("FIELD" => "P.PROPS_GROUP_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_SIZE1" => array("FIELD" => "P.SIZE1", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_SIZE2" => array("FIELD" => "P.SIZE2", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_DESCRIPTION" => array("FIELD" => "P.DESCRIPTION", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_EMAIL" => array("FIELD" => "P.IS_EMAIL", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_PROFILE_NAME" => array("FIELD" => "P.IS_PROFILE_NAME", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_PAYER" => array("FIELD" => "P.IS_PAYER", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_LOCATION4TAX" => array("FIELD" => "P.IS_LOCATION4TAX", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_ZIP" => array("FIELD" => "P.IS_ZIP", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_MULTIPLE" => array("FIELD" => "P.MULTIPLE", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_CODE" => array("FIELD" => "P.CODE", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_ACTIVE" => array("FIELD" => "P.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"PROP_UTIL" => array("FIELD" => "P.UTIL", "TYPE" => "char", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),

				"VARIANT_ID" => array("FIELD" => "PV.ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props_variant PV ON (UP.ORDER_PROPS_ID = PV.ORDER_PROPS_ID AND UP.VALUE = PV.VALUE)"),
				"VARIANT_ORDER_PROPS_ID" => array("FIELD" => "PV.ORDER_PROPS_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props_variant PV ON (UP.ORDER_PROPS_ID = PV.ORDER_PROPS_ID AND UP.VALUE = PV.VALUE)"),
				"VARIANT_NAME" => array("FIELD" => "PV.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props_variant PV ON (UP.ORDER_PROPS_ID = PV.ORDER_PROPS_ID AND UP.VALUE = PV.VALUE)"),
				"VARIANT_VALUE" => array("FIELD" => "PV.VALUE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props_variant PV ON (UP.ORDER_PROPS_ID = PV.ORDER_PROPS_ID AND UP.VALUE = PV.VALUE)"),
				"VARIANT_SORT" => array("FIELD" => "PV.SORT", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props_variant PV ON (UP.ORDER_PROPS_ID = PV.ORDER_PROPS_ID AND UP.VALUE = PV.VALUE)"),
				"VARIANT_DESCRIPTION" => array("FIELD" => "PV.DESCRIPTION", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props_variant PV ON (UP.ORDER_PROPS_ID = PV.ORDER_PROPS_ID AND UP.VALUE = PV.VALUE)"),

				"USER_VALUE_NAME" => array("FIELD" => "PV.NAME", "TYPE" => "string"),
				"TYPE" => array("FIELD" => "P.TYPE", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"SORT" => array("FIELD" => "P.SORT", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)"),
				"CODE" => array("FIELD" => "P.CODE", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_order_props P ON (UP.ORDER_PROPS_ID = P.ID)")
			);
		// <-- FIELDS


		if ($sale15converted && is_array($arSelectFields) && $arSelectFields)
		{
			if (($i = array_search('PROP_SIZE1', $arSelectFields)) !== false)
				unset($arSelectFields[$i]);
			if (($i = array_search('PROP_SIZE2', $arSelectFields)) !== false)
				unset($arSelectFields[$i]);

			if (($i = array_search('*', $arSelectFields)) !== false)
			{
				unset($arFields['PROP_SIZE1'], $arFields['PROP_SIZE2']);
			}
		}

		self::addPropertyValueField('UP', $arFields, $arSelectFields);

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_user_props_value UP ".
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
			"FROM b_sale_user_props_value UP ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (!empty($arNavStartParams) && is_array($arNavStartParams) && (int)($arNavStartParams["nTopCount"] ?? 0) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_user_props_value UP ".
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
			if (!empty($arNavStartParams) && is_array($arNavStartParams) && (int)($arNavStartParams["nTopCount"] ?? 0) > 0)
			{
				$strSql .= "LIMIT " . (int)($arNavStartParams["nTopCount"] ?? 0);
			}

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	public static function Add($arFields)
	{
		global $DB;

		// translate here
		$arFields['VALUE'] = self::translateLocationIDToCode($arFields['VALUE'], $arFields['ORDER_PROPS_ID']);

		$arInsert = $DB->PrepareInsert("b_sale_user_props_value", $arFields);

		$strSql = 
			"INSERT INTO b_sale_user_props_value(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		return $ID;
	}
}
