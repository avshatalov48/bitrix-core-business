<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/order_tax.php");

class CSaleOrderTax extends CAllSaleOrderTax
{
	public static function GetList($arOrder = array("TAX_NAME" => "ASC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "T.ID", "TYPE" => "int"),
				"ORDER_ID" => array("FIELD" => "T.ORDER_ID", "TYPE" => "int"),
				"TAX_NAME" => array("FIELD" => "T.TAX_NAME", "TYPE" => "string"),
				"VALUE" => array("FIELD" => "T.VALUE", "TYPE" => "double"),
				"VALUE_MONEY" => array("FIELD" => "T.VALUE_MONEY", "TYPE" => "double"),
				"APPLY_ORDER" => array("FIELD" => "T.APPLY_ORDER", "TYPE" => "int"),
				"CODE" => array("FIELD" => "T.CODE", "TYPE" => "string"),
				"IS_PERCENT" => array("FIELD" => "T.IS_PERCENT", "TYPE" => "char"),
				"IS_IN_PRICE" => array("FIELD" => "T.IS_IN_PRICE", "TYPE" => "char")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM ".static::getTableName()." T ".
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
			"FROM ".static::getTableName()." T ".
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
				"FROM ".static::getTableName()." T ".
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

	public static function Add($arFields)
	{
		global $DB;

		if (!static::CheckFields("ADD", $arFields))
			return false;

		$dbResult = static::GetList(
			array(),
			array(
				"ORDER_ID" => $arFields['ORDER_ID'],
				"TAX_NAME" => $arFields['TAX_NAME'],
				"CODE" => $arFields['CODE'],
			),
			false,
			false,
			array("ID")
		);
		if ($dbResult->Fetch())
		{
			return false;
		}

		$arInsert = $DB->PrepareInsert(static::getTableName(), $arFields);
		$strSql =
			"INSERT INTO ".static::getTableName()."(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		return $ID;
	}
}
?>