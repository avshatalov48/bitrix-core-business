<?php

use Bitrix\Main\Application;

IncludeModuleLangFile(__FILE__);

class CAllSaleAffiliateTransact
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "AFFILIATE_ID") || $ACTION=="ADD") && intval($arFields["AFFILIATE_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAT2_NO_AFF"), "EMPTY_AFFILIATE_ID");
			return false;
		}
		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && $arFields["CURRENCY"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAT2_NO_CURRENCY"), "EMPTY_CURRENCY");
			return false;
		}
		if ((is_set($arFields, "TRANSACT_DATE") || $ACTION=="ADD") && $arFields["TRANSACT_DATE"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAT2_NO_DATE"), "EMPTY_TRANSACT_DATE");
			return false;
		}

		if (is_set($arFields, "AMOUNT") || $ACTION=="ADD")
		{
			$arFields["AMOUNT"] = str_replace(",", ".", $arFields["AMOUNT"]);
			$arFields["AMOUNT"] = DoubleVal($arFields["AMOUNT"]);
		}

		if ((is_set($arFields, "DEBIT") || $ACTION=="ADD") && $arFields["DEBIT"] != "Y")
			$arFields["DEBIT"] = "N";

		if (is_set($arFields, "AFFILIATE_ID"))
		{
			$dbAddiliate = CSaleAffiliate::GetList(array(), array("ID" => $arFields["AFFILIATE_ID"]), false, false, array("ID"));
			if (!$dbAddiliate->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["AFFILIATE_ID"], GetMessage("SCGAT2_NO_AFF1")), "ERROR_NO_AFFILIATE_ID");
				return false;
			}
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		return $DB->Query("DELETE FROM b_sale_affiliate_transact WHERE ID = ".$ID." ", true);
	}

	public static function OnAffiliateDelete($affiliateID)
	{
		global $DB;
		$affiliateID = intval($affiliateID);

		return $DB->Query("DELETE FROM b_sale_affiliate_transact WHERE AFFILIATE_ID = ".$affiliateID." ", true);
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		$strSql =
			"SELECT AT.ID, AT.AFFILIATE_ID, AT.AMOUNT, AT.CURRENCY, AT.DEBIT, AT.DESCRIPTION, ".
			"	AT.EMPLOYEE_ID, ".
			"	".$DB->DateToCharFunction("AT.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
			"	".$DB->DateToCharFunction("AT.TRANSACT_DATE", "FULL")." as TRANSACT_DATE ".
			"FROM b_sale_affiliate_transact AT ".
			"WHERE AT.ID = ".$ID." ";

		$db_res = $DB->Query($strSql);
		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			return false;
		}

		$arFields1 = [];
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleAffiliateTransact::CheckFields("UPDATE", $arFields, $ID))
		{
			return false;
		}

		if (!isset($arFields1['TIMESTAMP_X']))
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();
			unset($arFields['TIMESTAMP_X']);
			$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
			unset($helper, $connection);
		}

		$strUpdate = $DB->PrepareUpdate("b_sale_affiliate_transact", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '') $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		$strSql = "UPDATE b_sale_affiliate_transact SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		return $ID;
	}
}
