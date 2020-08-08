<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/auxiliary.php");

class CSaleAuxiliary extends CAllSaleAuxiliary
{
	//********** SELECT **************//
	function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		$strSql = 
			"SELECT A.ID, A.USER_ID, A.ITEM, A.ITEM_MD5, ".
			"	".$DB->DateToCharFunction("A.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
			"	".$DB->DateToCharFunction("A.DATE_INSERT", "FULL")." as DATE_INSERT ".
			"FROM b_sale_auxiliary A ".
			"WHERE A.ID = ".$ID." ";

		$dbAuxiliary = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arAuxiliary = $dbAuxiliary->Fetch())
			return $arAuxiliary;

		return false;
	}

	function GetByParams($userID, $itemMD5)
	{
		global $DB;

		$userID = intval($userID);
		if ($userID <= 0)
			return false;

		$itemMD5 = Trim($itemMD5);
		if ($itemMD5 == '')
			return false;

		$itemMD5 = md5($itemMD5);

		$strSql = 
			"SELECT A.ID, A.USER_ID, A.ITEM, A.ITEM_MD5, ".
			"	".$DB->DateToCharFunction("A.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
			"	".$DB->DateToCharFunction("A.DATE_INSERT", "FULL")." as DATE_INSERT ".
			"FROM b_sale_auxiliary A ".
			"WHERE A.USER_ID = ".$userID." ".
			"	AND A.ITEM_MD5 = '".$DB->ForSql($itemMD5)."' ";

		$dbAuxiliary = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arAuxiliary = $dbAuxiliary->Fetch())
			return $arAuxiliary;

		return false;
	}

	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "USER_ID", "TIMESTAMP_X", "ITEM", "ITEM_MD5", "DATE_INSERT");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "A.ID", "TYPE" => "int"),
				"USER_ID" => array("FIELD" => "A.USER_ID", "TYPE" => "int"),
				"TIMESTAMP_X" => array("FIELD" => "A.TIMESTAMP_X", "TYPE" => "datetime"),
				"ITEM" => array("FIELD" => "A.ITEM", "TYPE" => "string"),
				"ITEM_MD5" => array("FIELD" => "A.ITEM_MD5", "TYPE" => "string", "WHERE" => array("CSaleAuxiliary", "PrepareItemMD54Where")),
				"DATE_INSERT" => array("FIELD" => "A.DATE_INSERT", "TYPE" => "datetime")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_auxiliary A ".
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
			"FROM b_sale_auxiliary A ".
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
				"FROM b_sale_auxiliary A ".
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

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br><br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}


	function DeleteByTime($periodLength, $periodType)
	{
		global $DB;

		$periodLength = intval($periodLength);
		if ($periodLength <= 0)
			return False;

		$periodType = Trim($periodType);
		$periodType = ToUpper($periodType);
		if ($periodType == '')
			return False;

		$deleteVal = 0;
		if ($periodType == "I")
			$deleteVal = mktime(date("H"), date("i") - $periodLength, date("s"), date("m"), date("d"), date("Y"));
		elseif ($periodType == "H")
			$deleteVal = mktime(date("H") - $periodLength, date("i"), date("s"), date("m"), date("d"), date("Y"));
		elseif ($periodType == "D")
			$deleteVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") - $periodLength, date("Y"));
		elseif ($periodType == "W")
			$deleteVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") - 7 * $periodLength, date("Y"));
		elseif ($periodType == "M")
			$deleteVal = mktime(date("H"), date("i"), date("s"), date("m") - $periodLength, date("d"), date("Y"));
		elseif ($periodType == "Q")
			$deleteVal = mktime(date("H"), date("i"), date("s"), date("m") - 3 * $periodLength, date("d"), date("Y"));
		elseif ($periodType == "S")
			$deleteVal = mktime(date("H"), date("i"), date("s"), date("m") - 6 * $periodLength, date("d"), date("Y"));
		elseif ($periodType == "Y")
			$deleteVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") - $periodLength);

		if ($deleteVal <= 0)
			return False;

		return $DB->Query("DELETE FROM b_sale_auxiliary WHERE DATE_INSERT < '".Date("Y-m-d H:i:s", $deleteVal)."' ", true);
	}

	function Add($arFields)
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

		if (!CSaleAuxiliary::CheckFields("ADD", $arFields, 0))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_auxiliary", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($arInsert[0] <> '') $arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if ($arInsert[1] <> '') $arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$strSql =
			"INSERT INTO b_sale_auxiliary(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleAuxiliary::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_auxiliary", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '') $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		$strSql = "UPDATE b_sale_auxiliary SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}
}
?>