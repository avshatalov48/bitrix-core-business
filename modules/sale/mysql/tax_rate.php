<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/tax_rate.php");

class CSaleTaxRate extends CAllSaleTaxRate
{
	function Add($arFields, $arOptions = array())
	{
		global $DB;
		if (!CSaleTaxRate::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_tax_rate", $arFields);
		$strSql =
			"INSERT INTO b_sale_tax_rate(".$arInsert[0].", TIMESTAMP_X) ".
			"VALUES(".$arInsert[1].", ".$DB->GetNowFunction().")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		if (is_set($arFields, "TAX_LOCATION"))
		{
			CSaleTaxRate::SetTaxRateLocation($ID, $arFields["TAX_LOCATION"], $arOptions);
		}

		return $ID;
	}

	function GetList($arOrder = array("APPLY_ORDER"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$arSqlSearchFrom = Array();

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilteKey = count($filter_keys);
		for ($i=0; $i < $countFilteKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);

			if (strval($val) == "")
				$val = 0;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch (ToUpper($key))
			{
				case "ID":
					$arSqlSearch[] = "TR.ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
					break;
				case "LID":
					$arSqlSearch[] = "T.LID ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "CODE":
					$arSqlSearch[] = "T.CODE ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "TAX_ID":
					$arSqlSearch[] = "TR.TAX_ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
					break;
				case "PERSON_TYPE_ID":
					$arSqlSearch[] = " (TR.PERSON_TYPE_ID ".($bInvert?"<>":"=")." ".IntVal($val)." OR TR.PERSON_TYPE_ID = 0 OR TR.PERSON_TYPE_ID IS NULL) ";
					break;
				case "CURRENCY":
					$arSqlSearch[] = "TR.CURRENCY ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "IS_PERCENT":
					$arSqlSearch[] = "TR.IS_PERCENT ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "IS_IN_PRICE":
					$arSqlSearch[] = "TR.IS_IN_PRICE ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "ACTIVE":
					$arSqlSearch[] = "TR.ACTIVE ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "APPLY_ORDER":
					$arSqlSearch[] = "TR.APPLY_ORDER ".($bInvert?"<>":"=")." ".IntVal($val)." ";
					break;
				case "LOCATION":

					if(CSaleLocation::isLocationProMigrated())
					{
						try
						{
							$class = self::CONN_ENTITY_NAME.'Table';
							$arSqlSearch[] = "	TR.ID in (".$class::getConnectedEntitiesQuery(intval($val), 'id', array('select' => array('ID'))).") ";
						}
						catch(Exception $e)
						{
						}
					}
					else
					{
						$arSqlSearch[] = 
							"	TR.ID = TR2L.TAX_RATE_ID ".
							"	AND (TR2L.LOCATION_CODE = ".IntVal($val)." AND TR2L.LOCATION_TYPE = 'L' ".
							"		OR L2LG.LOCATION_ID = ".IntVal($val)." AND TR2L.LOCATION_TYPE = 'G') ";
						$arSqlSearchFrom[] = 
							", b_sale_tax2location TR2L ".
							"	LEFT JOIN b_sale_location2location_group L2LG ON (TR2L.LOCATION_TYPE = 'G' AND TR2L.LOCATION_CODE = L2LG.LOCATION_GROUP_ID) ";
					}

					break;
				case "LOCATION_CODE":
						try
						{
							$class = self::CONN_ENTITY_NAME.'Table';
							$arSqlSearch[] = "	TR.ID in (".$class::getConnectedEntitiesQuery($val, 'code', array('select' => array('ID'))).") ";
						}
						catch(Exception $e)
						{
						}
					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSqlSearchFrom = "";
		$countSqlSearchForm = count($arSqlSearchFrom);
		for($i=0; $i < $countSqlSearchForm; $i++)
		{
			$strSqlSearchFrom .= " ".$arSqlSearchFrom[$i]." ";
		}

		$strSql = 
			"SELECT DISTINCT TR.ID, TR.TAX_ID, TR.PERSON_TYPE_ID, TR.VALUE, TR.CURRENCY, ".
			"	TR.IS_PERCENT, TR.IS_IN_PRICE, TR.APPLY_ORDER, ".$DB->DateToCharFunction("TR.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
			"	T.LID, T.NAME, T.DESCRIPTION, TR.ACTIVE, T.CODE ".
			"FROM b_sale_tax_rate TR, b_sale_tax T ".
			"	".$strSqlSearchFrom." ".
			"WHERE TR.TAX_ID = T.ID ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC")
				$order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " TR.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " T.LID ".$order." ";
			elseif ($by == "CODE") $arSqlOrder[] = " T.CODE ".$order." ";
			elseif ($by == "TIMESTAMP_X") $arSqlOrder[] = " TR.TIMESTAMP_X ".$order." ";
			elseif ($by == "ACTIVE") $arSqlOrder[] = " TR.ACTIVE ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " T.NAME ".$order." ";
			elseif ($by == "PERSON_TYPE_ID") $arSqlOrder[] = " TR.PERSON_TYPE_ID ".$order." ";
			elseif ($by == "IS_IN_PRICE") $arSqlOrder[] = " TR.IS_IN_PRICE ".$order." ";
			else
			{
				$arSqlOrder[] = " TR.APPLY_ORDER ".$order." ";
				$by = "APPLY_ORDER";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;
		//echo "<br>".htmlspecialcharsbx($strSql)."<br>";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

}
?>