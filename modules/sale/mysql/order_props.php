<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/order_props.php");

class CSaleOrderProps extends CAllSaleOrderProps
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

			$arSelectFields = array("ID", "PERSON_TYPE_ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "SORT", "USER_PROPS", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "IS_ZIP", "CODE", "IS_FILTERED", "ACTIVE", "UTIL", "INPUT_FIELD_LOCATION", "MULTIPLE", "PAYSYSTEM_ID", "DELIVERY_ID");
		}

		if (empty($arSelectFields))
			$arSelectFields = array("ID", "PERSON_TYPE_ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "SORT", "USER_PROPS", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "IS_ZIP",	"CODE", "IS_FILTERED", "ACTIVE", "UTIL", "INPUT_FIELD_LOCATION", "MULTIPLE", "PAYSYSTEM_ID", "DELIVERY_ID");

		// filter by relation to delivery and payment systems
		if (isset($arFilter["RELATED"]) && !is_array($arFilter["RELATED"]) && intval($arFilter["RELATED"]) == 0) // filter all not related to anything
		{
			if (($key = array_search("PAYSYSTEM_ID", $arSelectFields)) !== false)
				unset($arSelectFields[$key]);

			if (($key = array_search("DELIVERY_ID", $arSelectFields)) !== false)
				unset($arSelectFields[$key]);
		}
		else if (isset($arFilter["RELATED"]) && is_array($arFilter["RELATED"]))
		{
			if (isset($arFilter["RELATED"]["PAYSYSTEM_ID"]))
			{
				$arFilter["PAYSYSTEM_ID"] = $arFilter["RELATED"]["PAYSYSTEM_ID"];
				unset($arFilter["RELATED"]["PAYSYSTEM_ID"]);
			}

			if (isset($arFilter["RELATED"]["DELIVERY_ID"]))
			{
				$arFilter["DELIVERY_ID"] = $arFilter["RELATED"]["DELIVERY_ID"];
				unset($arFilter["RELATED"]["DELIVERY_ID"]);
			}
		}

		// FIELDS -->
		$arFields = array(
			"ID" => array("FIELD" => "P.ID", "TYPE" => "int"),
			"PERSON_TYPE_ID" => array("FIELD" => "P.PERSON_TYPE_ID", "TYPE" => "int"),
			"NAME" => array("FIELD" => "P.NAME", "TYPE" => "string"),
			"TYPE" => array("FIELD" => "P.TYPE", "TYPE" => "string"),
			"REQUIED" => array("FIELD" => "P.REQUIED", "TYPE" => "char"),
			"REQUIRED" => array("FIELD" => "P.REQUIED", "TYPE" => "char"),
			//"DEFAULT_VALUE" => array("FIELD" => "P.DEFAULT_VALUE", "TYPE" => "string"),
			"SORT" => array("FIELD" => "P.SORT", "TYPE" => "int"),
			"USER_PROPS" => array("FIELD" => "P.USER_PROPS", "TYPE" => "char"),
			"IS_LOCATION" => array("FIELD" => "P.IS_LOCATION", "TYPE" => "char"),
			"PROPS_GROUP_ID" => array("FIELD" => "P.PROPS_GROUP_ID", "TYPE" => "int"),
			"SIZE1" => array("FIELD" => "P.SIZE1", "TYPE" => "int"),
			"SIZE2" => array("FIELD" => "P.SIZE2", "TYPE" => "int"),
			"DESCRIPTION" => array("FIELD" => "P.DESCRIPTION", "TYPE" => "string"),
			"IS_EMAIL" => array("FIELD" => "P.IS_EMAIL", "TYPE" => "char"),
			"IS_PROFILE_NAME" => array("FIELD" => "P.IS_PROFILE_NAME", "TYPE" => "char"),
			"IS_PAYER" => array("FIELD" => "P.IS_PAYER", "TYPE" => "char"),
			"IS_LOCATION4TAX" => array("FIELD" => "P.IS_LOCATION4TAX", "TYPE" => "char"),
			"IS_FILTERED" => array("FIELD" => "P.IS_FILTERED", "TYPE" => "char"),
			"IS_ZIP" => array("FIELD" => "P.IS_ZIP", "TYPE" => "char"),
			"CODE" => array("FIELD" => "P.CODE", "TYPE" => "string"),
			"ACTIVE" => array("FIELD" => "P.ACTIVE", "TYPE" => "char"),
			"UTIL" => array("FIELD" => "P.UTIL", "TYPE" => "char"),
			"INPUT_FIELD_LOCATION" => array("FIELD" => "P.INPUT_FIELD_LOCATION", "TYPE" => "int"),
			"MULTIPLE" => array("FIELD" => "P.MULTIPLE", "TYPE" => "char"),

			"GROUP_ID" => array("FIELD" => "PG.ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props_group PG ON (P.PROPS_GROUP_ID = PG.ID)"),
			"GROUP_PERSON_TYPE_ID" => array("FIELD" => "PG.PERSON_TYPE_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props_group PG ON (P.PROPS_GROUP_ID = PG.ID)"),
			"GROUP_NAME" => array("FIELD" => "PG.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props_group PG ON (P.PROPS_GROUP_ID = PG.ID)"),
			"GROUP_SORT" => array("FIELD" => "PG.SORT", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props_group PG ON (P.PROPS_GROUP_ID = PG.ID)"),

			"PERSON_TYPE_LID" => array("FIELD" => "SPT.LID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_person_type SPT ON (P.PERSON_TYPE_ID = SPT.ID)"),
			"PERSON_TYPE_NAME" => array("FIELD" => "SPT.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_person_type SPT ON (P.PERSON_TYPE_ID = SPT.ID)"),
			"PERSON_TYPE_SORT" => array("FIELD" => "SPT.SORT", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_person_type SPT ON (P.PERSON_TYPE_ID = SPT.ID)"),
			"PERSON_TYPE_ACTIVE" => array("FIELD" => "SPT.ACTIVE", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_person_type SPT ON (P.PERSON_TYPE_ID = SPT.ID)"),

			"PAYSYSTEM_ID" => array("FIELD" => "SOP.PROPERTY_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_order_props_relation SOP ON P.ID = SOP.PROPERTY_ID", "WHERE" => array("CSaleOrderProps", "PrepareRelation4Where")),
			"DELIVERY_ID" => array("FIELD" => "SOD.PROPERTY_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order_props_relation SOD ON P.ID = SOD.PROPERTY_ID", "WHERE" => array("CSaleOrderProps", "PrepareRelation4Where"))
		);
		// <-- FIELDS

		self::addPropertyDefaultValueField('P', $arFields);

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		//filter order properties by relation to delivery and payment systems
		if (isset($arFilter["RELATED"]))
		{
			if (!is_array($arFilter["RELATED"]) && intval($arFilter["RELATED"]) == 0)
			{
				if (strlen($arSqls["WHERE"]) > 0)
					$arSqls["WHERE"] .= " AND ";
				$arSqls["WHERE"] .= "(P.ID NOT IN (SELECT DISTINCT SOR.PROPERTY_ID FROM b_sale_order_props_relation SOR))";
			}
			elseif (is_array($arFilter["RELATED"]))
			{
				$strSqlRelatedWhere = "";

				// payment
				if (isset($arFilter["PAYSYSTEM_ID"]) && intval($arFilter["PAYSYSTEM_ID"]) > 0)
					$strSqlRelatedWhere .= "(SOP.ENTITY_TYPE = 'P' AND SOP.ENTITY_ID = ".$DB->ForSql($arFilter["PAYSYSTEM_ID"]).")";

				// delivery
				if (isset($arFilter["DELIVERY_ID"]) && strlen($arFilter["DELIVERY_ID"]) > 0)
				{
					if (strlen($strSqlRelatedWhere) > 0)
					{
						$logic = "OR";
						if (isset($arFilter["RELATED"]["LOGIC"]) && $arFilter["RELATED"]["LOGIC"] == "AND")
							$logic = "AND";

						$strSqlRelatedWhere .= " ".$logic." ";
					}

					$strSqlRelatedWhere .= "(SOD.ENTITY_TYPE = 'D' AND SOD.ENTITY_ID = '".$DB->ForSql($arFilter["DELIVERY_ID"])."')";
				}

				// all other
				if (isset($arFilter["RELATED"]["TYPE"]))
				{
					if ($arFilter["RELATED"]["TYPE"] == "WITH_NOT_RELATED")
					{
						if (strlen($strSqlRelatedWhere) > 0)
							$strSqlRelatedWhere .= " OR (P.ID NOT IN (SELECT DISTINCT SOR.PROPERTY_ID FROM b_sale_order_props_relation SOR))";
					}
				}

				if (strlen($strSqlRelatedWhere) > 0)
				{
					if (strlen($arSqls["WHERE"]) > 0)
						$arSqls["WHERE"] .= " AND ";

					$arSqls["WHERE"] .= "(".$strSqlRelatedWhere.")";
				}
			}
		}

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_order_props P ".
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
			"FROM b_sale_order_props P ".
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
				"FROM b_sale_order_props P ".
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
				// FOR MYSQL!!! ANOTHER CODE FOR ORACLE
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

		foreach(GetModuleEvents("sale", "OnBeforeOrderPropsAdd", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;
		}

		if (!CSaleOrderProps::CheckFields("ADD", $arFields))
			return false;

		// translate here
		$arFields['DEFAULT_VALUE'] = self::translateLocationIDToCode($arFields);

		$arInsert = $DB->PrepareInsert("b_sale_order_props", $arFields);

		$strSql = "INSERT INTO b_sale_order_props(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		foreach(GetModuleEvents("sale", "OnOrderPropsAdd", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

		return $ID;
	}
}
?>