<?php

use Bitrix\Main;
use Bitrix\Sale;

IncludeModuleLangFile(__FILE__);

class CSaleLang
{
	public static function Add($arFields)
	{
		try
		{
			return Bitrix\Sale\Internals\SiteCurrencyTable::add($arFields)->isSuccess();
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	public static function Update($siteId, $arFields)
	{
		if ($siteId == $arFields["LID"])
		{
			unset($arFields["LID"]);
			return Bitrix\Sale\Internals\SiteCurrencyTable::update($siteId, $arFields)->isSuccess();
		}
		else
			die("h3jg53jh2g3jh6g");
	}

	public static function Delete($siteId)
	{
		return Bitrix\Sale\Internals\SiteCurrencyTable::delete($siteId)->isSuccess();
	}

	public static function GetByID($siteId)
	{
		return Bitrix\Sale\Internals\SiteCurrencyTable::getCurrency($siteId);
	}

	/**
	 * Return site currency.
	 *
	 * @deprecated deprecated since sale 15.0.0
	 * @see \Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency
	 *
	 * @param string $siteId        Site identifier.
	 * @return string
	 */
	public static function GetLangCurrency($siteId)
	{
		return Sale\Internals\SiteCurrencyTable::getSiteCurrency($siteId);
	}

	public static function OnBeforeCurrencyDelete($currency)
	{
		global $APPLICATION;

		$currency = (string)$currency;
		if ($currency === '')
			return true;

		if (Bitrix\Sale\Internals\SiteCurrencyTable::getList(array(
			'select' => array('*'),
			'filter' => array('=CURRENCY' => $currency),
			'limit'  => 1
		))->fetch())
		{
			$APPLICATION->ThrowException(str_replace("#CURRENCY#", $currency, GetMessage("SKGO_ERROR_CURRENCY")), "ERROR_CURRENCY");
			return false;
		}

		//TODO: change this call Option::get after remove RUB from default_option
		$saleCurrency = (string)Main\Config\Option::get('sale', 'default_currency', '-');
		if ($saleCurrency == $currency)
		{
			$APPLICATION->ThrowException(
				GetMessage(
					"SKGO_ERROR_DEFAULT_CURRENCY",
					array("#CURRENCY#" => $currency)
				),
				"ERROR_CURRENCY"
			);
			return false;
		}

		return true;
	}

	public static function OnLangDelete($langId)
	{
		Sale\Internals\SiteCurrencyTable::delete($langId);

		return true;
	}
}

class CAllSaleGroupAccessToSite
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "GROUP_ID") || $ACTION=="ADD") && intval($arFields["GROUP_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty group field", "EMPTY_GROUP_ID");
			return false;
		}

		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && $arFields["SITE_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty site field", "EMPTY_SITE_ID");
			return false;
		}

		return True;
	}

	public static function Update($ID, &$arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_ID"), "NO_ID");
			return false;
		}

		if (!CSaleGroupAccessToSite::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_site2group", $arFields);
		$strSql = "UPDATE b_sale_site2group SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		return True;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);

		$strSql =
			"SELECT * ".
			"FROM b_sale_site2group ".
			"WHERE ID = ".$ID."";
		$dbGroupSite = $DB->Query($strSql);

		if ($arGroupSite = $dbGroupSite->Fetch())
			return $arGroupSite;

		return False;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_DEL_ID"), "NO_ID");
			return false;
		}

		return $DB->Query("DELETE FROM b_sale_site2group WHERE ID = ".$ID." ", true);
	}

	public static function DeleteBySite($SITE_ID)
	{
		global $DB;

		$SITE_ID = Trim($SITE_ID);
		if ($SITE_ID == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_DEL_SITE"), "NO_SITE_ID");
			return false;
		}

		return $DB->Query("DELETE FROM b_sale_site2group WHERE SITE_ID = '".$DB->ForSql($SITE_ID, 2)."' ", true);
	}

	public static function DeleteByGroup($GROUP_ID)
	{
		global $DB;

		$GROUP_ID = intval($GROUP_ID);
		if ($GROUP_ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_DEL_GROUP"), "NO_GROUP_ID");
			return false;
		}

		return $DB->Query("DELETE FROM b_sale_site2group WHERE GROUP_ID = ".$GROUP_ID." ", true);
	}
}

class CAllSaleGroupAccessToFlag
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "GROUP_ID") || $ACTION=="ADD") && intval($arFields["GROUP_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty group field", "EMPTY_GROUP_ID");
			return false;
		}

		if ((is_set($arFields, "ORDER_FLAG") || $ACTION=="ADD") && $arFields["ORDER_FLAG"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty flag field", "EMPTY_ORDER_FLAG");
			return false;
		}

		return True;
	}

	public static function Update($ID, &$arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_ID"), "NO_ID");
			return false;
		}

		if (!CSaleGroupAccessToFlag::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_order_flags2group", $arFields);
		$strSql = "UPDATE b_sale_order_flags2group SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		return True;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);

		$strSql =
			"SELECT * ".
			"FROM b_sale_order_flags2group ".
			"WHERE ID = ".$ID."";
		$dbGroupSite = $DB->Query($strSql);

		if ($arGroupSite = $dbGroupSite->Fetch())
			return $arGroupSite;

		return False;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_DEL_ID"), "NO_ID");
			return false;
		}

		return $DB->Query("DELETE FROM b_sale_order_flags2group WHERE ID = ".$ID." ", true);
	}

	public static function DeleteByGroup($GROUP_ID)
	{
		global $DB;

		$GROUP_ID = intval($GROUP_ID);
		if ($GROUP_ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_DEL_GROUP"), "NO_GROUP_ID");
			return false;
		}

		return $DB->Query("DELETE FROM b_sale_order_flags2group WHERE GROUP_ID = ".$GROUP_ID." ", true);
	}

	public static function DeleteByFlag($ORDER_FLAG)
	{
		global $DB;

		$ORDER_FLAG = Trim($ORDER_FLAG);
		if ($ORDER_FLAG == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGS_NO_DEL_FLAG"), "NO_ORDER_FLAG");
			return false;
		}

		return $DB->Query("DELETE FROM b_sale_order_flags2group WHERE ORDER_FLAG = '".$DB->ForSql($ORDER_FLAG, 1)."' ", true);
	}
}
