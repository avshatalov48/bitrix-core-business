<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/order_props_values.php");

class CSaleOrderPropsValue extends CAllSaleOrderPropsValue
{
	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = strval($arOrder);
			$arFilter = strval($arFilter);
			if (strlen($arOrder) > 0 && strlen($arFilter) > 0)
				$arOrder = array($arOrder => $arFilter);
			else
				$arOrder = array();
			if (is_array($arGroupBy))
				$arFilter = $arGroupBy;
			else
				$arFilter = array();
			$arGroupBy = false;

			$arSelectFields = array("ID", "ORDER_ID", "ORDER_PROPS_ID", "NAME", "VALUE", "CODE");
		}

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "ORDER_ID", "ORDER_PROPS_ID", "NAME", "VALUE", "CODE");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "V.ID", "TYPE" => "int"),
				"ORDER_ID" => array("FIELD" => "V.ORDER_ID", "TYPE" => "int"),
				"ORDER_PROPS_ID" => array("FIELD" => "V.ORDER_PROPS_ID", "TYPE" => "int"),
				"NAME" => array("FIELD" => "V.NAME", "TYPE" => "string"),
				"CODE" => array("FIELD" => "V.CODE", "TYPE" => "string"),
				"PROP_ID" => array("FIELD" => "P.ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_PERSON_TYPE_ID" => array("FIELD" => "P.PERSON_TYPE_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_NAME" => array("FIELD" => "P.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_TYPE" => array("FIELD" => "P.TYPE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_REQUIED" => array("FIELD" => "P.REQUIED", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_DEFAULT_VALUE" => array("FIELD" => "P.DEFAULT_VALUE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_SORT" => array("FIELD" => "P.SORT", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_USER_PROPS" => array("FIELD" => "P.USER_PROPS", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_LOCATION" => array("FIELD" => "P.IS_LOCATION", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_PROPS_GROUP_ID" => array("FIELD" => "P.PROPS_GROUP_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_SIZE1" => array("FIELD" => "P.SIZE1", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_SIZE2" => array("FIELD" => "P.SIZE2", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_DESCRIPTION" => array("FIELD" => "P.DESCRIPTION", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_EMAIL" => array("FIELD" => "P.IS_EMAIL", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_PROFILE_NAME" => array("FIELD" => "P.IS_PROFILE_NAME", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_PAYER" => array("FIELD" => "P.IS_PAYER", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_LOCATION4TAX" => array("FIELD" => "P.IS_LOCATION4TAX", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_IS_ZIP" => array("FIELD" => "P.IS_ZIP", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_CODE" => array("FIELD" => "P.CODE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_ACTIVE" => array("FIELD" => "P.ACTIVE", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
				"PROP_UTIL" => array("FIELD" => "P.UTIL", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_order_props P ON (V.ORDER_PROPS_ID = P.ID)"),
			);
		// <-- FIELDS

		CSaleOrderPropsValue::addPropertyValueField('V', $arFields, $arSelectFields);

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_order_props_value V ".
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
			"FROM b_sale_order_props_value V ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_order_props_value V ".
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
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])>0)
				$strSql .= "LIMIT ".IntVal($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	function Add($arFields)
	{
		global $DB;

		if (!CSaleOrderPropsValue::CheckFields("ADD", $arFields, 0))
			return false;

		// translate here
		$arFields['VALUE'] = self::translateLocationIDToCode($arFields['VALUE'], $arFields['ORDER_PROPS_ID']);

		$arInsert = $DB->PrepareInsert("b_sale_order_props_value", $arFields);

		$strSql =
			"INSERT INTO b_sale_order_props_value(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		return $ID;
	}


	function GetOrderProps($ORDER_ID)
	{
		global $DB;
		$ORDER_ID = IntVal($ORDER_ID);

		$strSql =
			"SELECT PV.ID, PV.ORDER_ID, PV.ORDER_PROPS_ID, PV.NAME, ".self::getPropertyValueFieldSelectSql().", PV.CODE, ".
			"	P.NAME as PROPERTY_NAME, P.TYPE, P.PROPS_GROUP_ID, P.INPUT_FIELD_LOCATION, PG.NAME as GROUP_NAME, ".
			"	P.IS_LOCATION, P.IS_EMAIL, P.IS_PROFILE_NAME, P.IS_PAYER, PG.SORT as GROUP_SORT, P.ACTIVE, P.UTIL ".
			"FROM b_sale_order_props_value PV ".
			"	LEFT JOIN b_sale_order_props P ON (PV.ORDER_PROPS_ID = P.ID) ".
			"	LEFT JOIN b_sale_order_props_group PG ON (P.PROPS_GROUP_ID = PG.ID) ".
			self::getLocationTableJoinSql().
			"WHERE PV.ORDER_ID = ".$ORDER_ID." ".
			"ORDER BY PG.SORT, PG.NAME, P.SORT, P.NAME, P.ID ";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetOrderRelatedProps($ORDER_ID, $arFilter = array())
	{
		global $DB;
		$ORDER_ID = IntVal($ORDER_ID);

		$strJoin = "";
		$strWhere = "";

		if (isset($arFilter["PAYSYSTEM_ID"]) && intval($arFilter["PAYSYSTEM_ID"]) > 0)
		{
			$strJoin = "	LEFT JOIN b_sale_order_props_relation SOP ON P.ID = SOP.PROPERTY_ID ";
			$strWhere = " (SOP.ENTITY_TYPE = 'P' AND SOP.ENTITY_ID = ".(int)($arFilter["PAYSYSTEM_ID"]).")";
		}

		if (isset($arFilter["DELIVERY_ID"]) && strlen($arFilter["DELIVERY_ID"]) > 0)
		{
			$strJoin .= "	LEFT JOIN b_sale_order_props_relation SOD ON P.ID = SOD.PROPERTY_ID ";
			if (strlen($strWhere) > 0)
				$strWhere .= " OR";

			$strWhere .= " (SOD.ENTITY_TYPE = 'D' AND SOD.ENTITY_ID = '".$DB->ForSql($arFilter["DELIVERY_ID"])."')";
		}

		if (strlen($strWhere) > 0)
			$strWhere = " AND (".$strWhere.") ";

		// locations kept in CODEs, but must be shown as IDs
		$lMig = CSaleLocation::isLocationProMigrated();

		$strSql =
			"SELECT DISTINCT PV.ID, PV.ORDER_ID, PV.ORDER_PROPS_ID, PV.NAME, ".self::getPropertyValueFieldSelectSql().", PV.CODE, ".
			"	P.NAME as PROPERTY_NAME, P.TYPE, P.PROPS_GROUP_ID, P.INPUT_FIELD_LOCATION, PG.NAME as GROUP_NAME, ".
			"	P.IS_LOCATION, P.IS_EMAIL, P.IS_PROFILE_NAME, P.IS_PAYER, PG.SORT as GROUP_SORT, P.ACTIVE, P.UTIL ".
			"FROM b_sale_order_props_value PV ".
			"	LEFT JOIN b_sale_order_props P ON (PV.ORDER_PROPS_ID = P.ID) ".
			"	LEFT JOIN b_sale_order_props_group PG ON (P.PROPS_GROUP_ID = PG.ID) ".
			self::getLocationTableJoinSql().
			$strJoin.
			"WHERE PV.ORDER_ID = ".$ORDER_ID." ".
			$strWhere.
			"ORDER BY PG.SORT, PG.NAME, P.SORT, P.NAME, P.ID ";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}
}

?>