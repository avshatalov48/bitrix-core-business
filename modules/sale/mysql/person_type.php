<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/person_type.php");

class CSalePersonType extends CAllSalePersonType
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
		}
		if(empty($arSelectFields))
			$arSelectFields = Array("ID", "LID", "NAME", "SORT", "ACTIVE", "CODE");
			
		if(is_set($arFilter, "LID") && !empty($arFilter["LID"]))
		{
			$arFilter["LIDS"] = $arFilter["LID"];
			unset($arFilter["LID"]);
		}

		$arFilter['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "PT.ID", "TYPE" => "int"),
				"LID" => array("FIELD" => "PT.LID", "TYPE" => "string"),
				"LIDS" => array("FIELD" => "PTS.SITE_ID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_person_type_site PTS ON (PT.ID = PTS.PERSON_TYPE_ID)"),
				"NAME" => array("FIELD" => "PT.NAME", "TYPE" => "string"),
				"CODE" => array("FIELD" => "PT.CODE", "TYPE" => "string"),
				"SORT" => array("FIELD" => "PT.SORT", "TYPE" => "int"),
				"ACTIVE" => array("FIELD" => "PT.ACTIVE", "TYPE" => "char"),
				"ENTITY_REGISTRY_TYPE" => array("FIELD" => "ENTITY_REGISTRY_TYPE", "TYPE" => "string"),
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_person_type PT ".
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
			"FROM b_sale_person_type PT ".
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
				"FROM b_sale_person_type PT ".
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
		
		
		$arPT = array();
		$arResTmp = array();
		while ($arRes = $dbRes->Fetch())
		{
			if(intval($arRes["ID"]) > 0)
			{
				if(!in_array($arRes["ID"], $arPT))
					$arPT[] = $arRes["ID"];
				$arResTmp[] = $arRes;
			}
		}
		
		if(!empty($arPT) && is_array($arPT))
		{
			$strSql = "SELECT * from b_sale_person_type_site WHERE PERSON_TYPE_ID IN (".implode(",", $arPT).")";
			$dbRes1 = $DB->Query($strSql);
			while ($arRes1 = $dbRes1->Fetch())
			{
				$arRes2[$arRes1["PERSON_TYPE_ID"]][] = $arRes1["SITE_ID"];
			}
		}

		foreach($arResTmp as $k => $v)
			$arResTmp[$k]["LIDS"] = $arRes2[$v["ID"]];
			
		$dbRes = new CDBResult();
		$dbRes->InitFromArray($arResTmp);

		return $dbRes;
	}

	public static function Add($arFields)
	{
		global $DB;

		if (!CSalePersonType::CheckFields("ADD", $arFields))
			return false;

		$db_events = GetModuleEvents("sale", "OnBeforePersonTypeAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array(&$arFields))===false)
				return false;

		$arLID = Array();
		if(is_set($arFields, "LID"))
		{
			if(is_array($arFields["LID"]))
				$arLID = $arFields["LID"];
			else
				$arLID[] = $arFields["LID"];

			$str_LID = "''";
			$arFields["LID"] = false;
			foreach($arLID as $k => $v)
			{
				if($v <> '')
				{
					$str_LID .= ", '".$DB->ForSql($v)."'";
					if(empty($arFields["LID"]))
						$arFields["LID"] = $v;
				}
				else
					unset($arLID[$k]);
			}
		}

		$arFields['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;

		$arInsert = $DB->PrepareInsert("b_sale_person_type", $arFields);

		$strSql =
			"INSERT INTO b_sale_person_type(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql);

		$ID = intval($DB->LastID());
		
		if(count($arLID)>0)
		{
			$strSql = "DELETE FROM b_sale_person_type_site WHERE PERSON_TYPE_ID=".$ID;
			$DB->Query($strSql);

			$strSql =
				"INSERT INTO b_sale_person_type_site(PERSON_TYPE_ID, SITE_ID) ".
				"SELECT ".$ID.", LID ".
				"FROM b_lang ".
				"WHERE LID IN (".$str_LID.") ";

			$DB->Query($strSql);
		}

		unset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]);

		$events = GetModuleEvents("sale", "OnPersonTypeAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $arFields));

		return $ID;
	}
}
