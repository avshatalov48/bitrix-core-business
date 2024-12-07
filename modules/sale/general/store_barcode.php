<?php

IncludeModuleLangFile(__FILE__);

class CAllSaleStoreBarcode
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if (defined("SALE_DEBUG") && SALE_DEBUG)
			CSaleHelper::WriteToLog("CSaleStoreBarcode checking fields", array("ACTION" => $ACTION, "arFields" => $arFields), "SSBA1");

		if ((is_set($arFields, "BASKET_ID") || $ACTION=="ADD") && $arFields["BASKET_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SSB_EMPTY_BASKET_ID"), "BARCODE_ADD_EMPTY_BASKET_ID");
			return false;
		}

		if ((is_set($arFields, "BASKET_ID") || $ACTION=="ADD") && $arFields["BASKET_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SSB_EMPTY_STORE_ID"), "BARCODE_ADD_EMPTY_STORE_ID");
			return false;
		}
			
		if ((is_set($arFields, "QUANTITY") || $ACTION=="ADD") && $arFields["QUANTITY"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SSB_EMPTY_QUANTITY"), "BARCODE_ADD_EMPTY_QUANTITY");
			return false;
		}

		return true;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);

		$strSql =
			"SELECT O.*, ".
			"	".$DB->DateToCharFunction("O.DATE_CREATE", "FULL")." as DATE_CREATE, ".
			"	".$DB->DateToCharFunction("O.DATE_MODIFY", "FULL")." as DATE_MODIFY ".
			"FROM b_sale_store_barcode O ".
			"WHERE O.ID = ".$ID."";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}

		return False;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		return $DB->Query("DELETE FROM b_sale_store_barcode WHERE ID = ".$ID." ", true);
	}
}
