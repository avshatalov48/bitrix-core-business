<?
IncludeModuleLangFile(__FILE__);

class CAllSaleUserTransact
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty user field", "EMPTY_USER_ID");
			return false;
		}
		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && $arFields["CURRENCY"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty currency field", "EMPTY_CURRENCY");
			return false;
		}
		if ((is_set($arFields, "TRANSACT_DATE") || $ACTION=="ADD") && $arFields["TRANSACT_DATE"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty transaction date field", "EMPTY_TRANSACT_DATE");
			return false;
		}

		if (is_set($arFields, "AMOUNT") || $ACTION=="ADD")
		{
			$arFields["AMOUNT"] = str_replace(",", ".", $arFields["AMOUNT"]);
			$arFields["AMOUNT"] = DoubleVal($arFields["AMOUNT"]);
		}

		if ((is_set($arFields, "DEBIT") || $ACTION=="ADD") && $arFields["DEBIT"] != "Y")
			$arFields["DEBIT"] = "N";

		if (is_set($arFields, "USER_ID"))
		{
			$dbUser = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbUser->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["USER_ID"], GetMessage("SKGUT_NO_USER")), "ERROR_NO_USER_ID");
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

		return $DB->Query("DELETE FROM b_sale_user_transact WHERE ID = ".$ID." ", true);
	}

	public static function OnUserDelete($UserID)
	{
		global $DB;
		$UserID = intval($UserID);

		return $DB->Query("DELETE FROM b_sale_user_transact WHERE USER_ID = ".$UserID." ", true);
	}

	public static function DeleteByOrder($OrderID)
	{
		global $DB;
		$OrderID = intval($OrderID);
		$DB->Query("Update b_sale_user_transact SET NOTES='ORDER ".$OrderID."' WHERE ORDER_ID = ".$OrderID." ", true);
		return $DB->Query("Update b_sale_user_transact SET ORDER_ID = NULL WHERE ORDER_ID = ".$OrderID." ", true);
	}
}