<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/order_user_props.php");

class CSaleOrderUserProps extends CAllSaleOrderUserProps
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

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "NAME", "USER_ID", "PERSON_TYPE_ID", "DATE_UPDATE");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "P.ID", "TYPE" => "int"),
				"NAME" => array("FIELD" => "P.NAME", "TYPE" => "string"),
				"USER_ID" => array("FIELD" => "P.USER_ID", "TYPE" => "int"),
				"PERSON_TYPE_ID" => array("FIELD" => "P.PERSON_TYPE_ID", "TYPE" => "int"),
				"DATE_UPDATE" => array("FIELD" => "P.DATE_UPDATE", "TYPE" => "datetime"),
				"FORMAT_DATE_UPDATE" => array("FIELD" => "P.DATE_UPDATE", "TYPE" => "datetime"),
				"DATE_UPDATE_FORMAT" => array("FIELD" => "P.DATE_UPDATE", "TYPE" => "datetime"),
				"XML_ID" => array("FIELD" => "P.XML_ID", "TYPE" => "string"),
				"VERSION_1C" => array("FIELD" => "P.VERSION_1C", "TYPE" => "string"),
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		$dateTimeFields = array("DATE_UPDATE", "FORMAT_DATE_UPDATE", "DATE_UPDATE_FORMAT");
		foreach ($arOrder as $orderValue => $by)
		{
			if (in_array($orderValue, $dateTimeFields))
				$arSqls["SELECT"] .= ", P.".$orderValue." ".$orderValue."_RAW";
		}

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_user_props P ".
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
			"FROM b_sale_user_props P ".
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
				"FROM b_sale_user_props P ".
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

	function Add($arFields)
	{
		global $DB;

		if (!CSaleOrderUserProps::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_user_props", $arFields);

		$strSql =
			"INSERT INTO b_sale_user_props(".$arInsert[0].", DATE_UPDATE) ".
			"VALUES(".$arInsert[1].", ".$DB->GetNowFunction().")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		return $ID;
	}
}
?>