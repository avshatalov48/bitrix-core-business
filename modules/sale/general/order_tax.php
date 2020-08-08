<?
IncludeModuleLangFile(__FILE__);

class CAllSaleOrderTax
{
	public static function CheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "ORDER_ID") || $ACTION=="ADD") && intval($arFields["ORDER_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOT_EMPTY_ORDER_ID"), "ERROR_NO_ORDER_ID");
			return false;
		}
		if ((is_set($arFields, "TAX_NAME") || $ACTION=="ADD") && $arFields["TAX_NAME"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOT_EMPTY_TAX_NAME"), "ERROR_NO_TAX_NAME");
			return false;
		}
		if ((is_set($arFields, "IS_PERCENT") || $ACTION=="ADD") && $arFields["IS_PERCENT"]!="Y" && $arFields["IS_PERCENT"]!="N")
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOT_EMPTY_TAX_VALUE"), "ERROR_NO_IS_PERCENT");
			return false;
		}
		if ((is_set($arFields, "IS_IN_PRICE") || $ACTION=="ADD") && $arFields["IS_IN_PRICE"]!="Y" && $arFields["IS_IN_PRICE"]!="N")
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOT_EMPTY_IN_PRICE"), "ERROR_NO_IS_IN_PRICE");
			return false;
		}

		if (is_set($arFields, "VALUE") || $ACTION=="ADD")
		{
			$arFields["VALUE"] = str_replace(",", ".", $arFields["VALUE"]);
			$arFields["VALUE"] = DoubleVal($arFields["VALUE"]);
			if ($arFields["VALUE"] <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOT_EMPTY_SUM"), "ERROR_NO_VALUE");
				return false;
			}
		}
		if (is_set($arFields, "VALUE_MONEY") || $ACTION=="ADD")
		{
			$arFields["VALUE_MONEY"] = str_replace(",", ".", $arFields["VALUE_MONEY"]);
			$arFields["VALUE_MONEY"] = DoubleVal($arFields["VALUE_MONEY"]);
		}
		if ((is_set($arFields, "VALUE_MONEY") || $ACTION=="ADD") && $arFields["VALUE_MONEY"]<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOT_EMPTY_SUM_MONEY"), "ERROR_NO_VALUE_MONEY");
			return false;
		}

		if (is_set($arFields, "ORDER_ID"))
		{
			if (!static::isOrderExists($arFields["ORDER_ID"]))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["ORDER_ID"], GetMessage("SKGOT_NO_ORDER")), "ERROR_NO_ORDER");
				return false;
			}
		}

		if ((is_set($arFields, "CODE") || $ACTION=="ADD") && $arFields["CODE"] == '')
			$arFields["CODE"] = false;

		return true;
	}

	protected static function isOrderExists($id)
	{
		return !empty(CSaleOrder::GetByID($id));
	}

	public static function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if (!static::CheckFields("UPDATE", $arFields)) return false;

		$strUpdate = $DB->PrepareUpdate(static::getTableName(), $arFields);
		$strSql = "UPDATE ".static::getTableName()." SET ".
			"	".$strUpdate." ".
			"WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		return $DB->Query("DELETE FROM ".static::getTableName()." WHERE ID = ".$ID."", true);
	}

	public static function DeleteEx($ORDER_ID)
	{
		global $DB;
		$ORDER_ID = intval($ORDER_ID);
		return $DB->Query("DELETE FROM ".static::getTableName()." WHERE ORDER_ID = ".$ORDER_ID."", true);
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT ID, ORDER_ID, TAX_NAME, VALUE, VALUE_MONEY, APPLY_ORDER, CODE, IS_PERCENT, IS_IN_PRICE ".
			"FROM ".static::getTableName()." ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	// The function does not handle fixed-rate taxes. Only with interest!
	// any tax returns for the price
	// the second argument ($ arTaxList [] ["TAX_VAL"]) returns the value of the tax for that price
	function CountTaxes($Price, &$arTaxList, $DefCurrency)
	{
		//1. Untwist stack tax included in the price for the determination of the initial price
		$part_sum = 0.00;
		$tax_koeff = 1.00;
		$minus = 0.00;
		$cnt = count($arTaxList);
		for ($i = 0; $i < $cnt; $i++)
		{
			if ($i == 0)
				$prevOrder = intval($arTaxList[$i]["APPLY_ORDER"]);

			if ($prevOrder != intval($arTaxList[$i]["APPLY_ORDER"]))
			{
				$tax_koeff += $part_sum;
				$part_sum = 0.00;
				$prevOrder = intval($arTaxList[$i]["APPLY_ORDER"]);
			}

			$val = $tax_koeff * DoubleVal($arTaxList[$i]["VALUE"]) / 100.00;
			$part_sum += $val;

			if ($arTaxList[$i]["IS_IN_PRICE"] != "Y")
				$minus += $val;
		}
		$tax_koeff += $part_sum;
		$item_price = $Price/($tax_koeff-$minus);

		//2. collect taxes
		$part_sum = 0.00;
		$tax_koeff = 1.00;
		$plus = 0.00;
		$total_tax = 0.00;
		$cnt = count($arTaxList);
		for ($i = 0; $i < $cnt; $i++)
		{
			if ($i==0)
				$prevOrder = intval($arTaxList[$i]["APPLY_ORDER"]);

			if ($prevOrder <> intval($arTaxList[$i]["APPLY_ORDER"]))
			{
				$tax_koeff += $part_sum;
				$part_sum = 0.00;
				$prevOrder = intval($arTaxList[$i]["APPLY_ORDER"]);
			}

			$val = $tax_koeff * DoubleVal($arTaxList[$i]["VALUE"]) / 100.00;
			$tax_val = $val*$item_price;
			$part_sum += $val;
			$total_tax += $tax_val;

			$arTaxList[$i]["TAX_VAL"] = $tax_val;
		}
		return $total_tax;
	}

	/**
	 * @return string
	 */
	protected static function getTableName()
	{
		return 'b_sale_order_tax';
	}
}