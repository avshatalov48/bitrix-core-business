<?php

/** @deprecated */
class CSaleDelivery extends CAllSaleDelivery
{
	/** @deprecated  */
	public static function PrepareCurrency4Where($val, $key, $operation, $negative, $field, &$arField, &$arFilter)
	{
		$val = DoubleVal($val);

		$baseSiteCurrency = "";
		if (isset($arFilter["LID"]) && $arFilter["LID"] <> '')
			$baseSiteCurrency = CSaleLang::GetLangCurrency($arFilter["LID"]);
		elseif (isset($arFilter["CURRENCY"]) && $arFilter["CURRENCY"] <> '')
			$baseSiteCurrency = $arFilter["CURRENCY"];

		if ($baseSiteCurrency == '')
			return False;

		$strSqlSearch = "";

		$dbCurrency = CCurrency::GetList("sort", "asc");
		while ($arCurrency = $dbCurrency->Fetch())
		{
			$val1 = roundEx(CCurrencyRates::ConvertCurrency($val, $baseSiteCurrency, $arCurrency["CURRENCY"]), SALE_VALUE_PRECISION);
			if ($strSqlSearch <> '')
				$strSqlSearch .= " OR ";

			$strSqlSearch .= "(D.ORDER_CURRENCY = '".$arCurrency["CURRENCY"]."' AND ";
			if ($negative == "Y")
				$strSqlSearch .= "NOT";
			$strSqlSearch .= "(".$field." ".$operation." ".$val1." OR ".$field." IS NULL OR ".$field." = 0)";
			$strSqlSearch .= ")";
		}

		return "(".$strSqlSearch.")";
	}

	/** @deprecated */
	public static function PrepareLocation4Where($val, $key, $operation, $negative, $field, &$arField, &$arFilter)
	{
		return "(D2L.LOCATION_ID = ".intval($val)." AND D2L.LOCATION_TYPE = 'L' ".
			" OR L2LG.LOCATION_ID = ".intval($val)." AND D2L.LOCATION_TYPE = 'G') ";
	}
}
