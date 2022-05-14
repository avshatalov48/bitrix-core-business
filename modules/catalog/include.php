<?php
/** @global \CMain $APPLICATION */
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Sale,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);

const CATALOG_CONTAINER_PATH = 'modules/catalog/.container.php';

const CATALOG_PATH2EXPORTS = "/bitrix/php_interface/include/catalog_export/";
const CATALOG_PATH2EXPORTS_DEF = "/bitrix/modules/catalog/load/";
const CATALOG_DEFAULT_EXPORT_PATH = '/bitrix/catalog_export/';

const CATALOG_PATH2IMPORTS = "/bitrix/php_interface/include/catalog_import/";
const CATALOG_PATH2IMPORTS_DEF = "/bitrix/modules/catalog/load_import/";

const YANDEX_SKU_EXPORT_ALL = 1;
const YANDEX_SKU_EXPORT_MIN_PRICE = 2;
const YANDEX_SKU_EXPORT_PROP = 3;
const YANDEX_SKU_TEMPLATE_PRODUCT = 1;
const YANDEX_SKU_TEMPLATE_OFFERS = 2;
const YANDEX_SKU_TEMPLATE_CUSTOM = 3;

const EXPORT_VERSION_OLD = 1;
const EXPORT_VERSION_NEW = 2;

/*
* @deprecated deprecated since catalog 14.5.3
* @see CCatalogDiscount::ENTITY_ID
*/
const DISCOUNT_TYPE_STANDART = 0;
/*
* @deprecated deprecated since catalog 14.5.3
* @see CCatalogDiscountSave::ENTITY_ID
*/
const DISCOUNT_TYPE_SAVE = 1;

/*
* @deprecated deprecated since catalog 14.5.3
* @see CCatalogDiscount::OLD_FORMAT
*/
const CATALOG_DISCOUNT_OLD_VERSION = 1;
/*
* @deprecated deprecated since catalog 14.5.3
* @see CCatalogDiscount::CURRENT_FORMAT
*/
const CATALOG_DISCOUNT_NEW_VERSION = 2;

const BX_CATALOG_FILENAME_REG = '/[^a-zA-Z0-9\s!#\$%&\(\)\[\]\{\}+\.;=@\^_\~\/\\\\\-]/i';

// Constants for the store control: //
const CONTRACTOR_INDIVIDUAL = 1;
const CONTRACTOR_JURIDICAL = 2;
const DOC_ARRIVAL = 'A';
const DOC_MOVING = 'M';
const DOC_RETURNS = 'R';
const DOC_DEDUCT = 'D';
const DOC_INVENTORY = 'I';

//**********************************//

global $APPLICATION;

if (!Loader::includeModule("iblock"))
{
	$APPLICATION->ThrowException(Loc::getMessage('CAT_ERROR_IBLOCK_NOT_INSTALLED'));
	return false;
}

if (!Loader::includeModule("currency"))
{
	$APPLICATION->ThrowException(Loc::getMessage('CAT_ERROR_CURRENCY_NOT_INSTALLED'));
	return false;
}

$arTreeDescr = array(
	'js' => '/bitrix/js/catalog/core_tree.js',
	'css' => '/bitrix/panel/catalog/catalog_cond.css',
	'lang' => '/bitrix/modules/catalog/lang/'.LANGUAGE_ID.'/js_core_tree.php',
	'rel' => array('core', 'date', 'window')
);
CJSCore::RegisterExt('core_condtree', $arTreeDescr);

const CATALOG_VALUE_EPSILON = 1e-6;
const CATALOG_VALUE_PRECISION = 2;
const CATALOG_CACHE_DEFAULT_TIME = 10800;

require_once __DIR__.'/autoload.php';

if (defined('CATALOG_GLOBAL_VARS') && CATALOG_GLOBAL_VARS == 'Y')
{
	global $CATALOG_CATALOG_CACHE;
	$CATALOG_CATALOG_CACHE = null;

	global $CATALOG_ONETIME_COUPONS_ORDER;
	$CATALOG_ONETIME_COUPONS_ORDER = null;

	global $CATALOG_PRODUCT_CACHE;
	$CATALOG_PRODUCT_CACHE = null;

	global $MAIN_EXTRA_LIST_CACHE;
	$MAIN_EXTRA_LIST_CACHE = null;

	global $CATALOG_BASE_GROUP;
	$CATALOG_BASE_GROUP = array();

	global $CATALOG_TIME_PERIOD_TYPES;
	/** @noinspection PhpDeprecationInspection */
	$CATALOG_TIME_PERIOD_TYPES = CCatalogProduct::GetTimePeriodTypes(true);

	global $arCatalogAvailProdFields;
	$arCatalogAvailProdFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_ELEMENT);
	global $arCatalogAvailPriceFields;
	$arCatalogAvailPriceFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_CATALOG);
	global $arCatalogAvailValueFields;
	$arCatalogAvailValueFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE);
	global $arCatalogAvailQuantityFields;
	$arCatalogAvailQuantityFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE_EXT);
	global $arCatalogAvailGroupFields;
	$arCatalogAvailGroupFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_SECTION);

	global $defCatalogAvailProdFields;
	$defCatalogAvailProdFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_ELEMENT);
	global $defCatalogAvailPriceFields;
	$defCatalogAvailPriceFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CATALOG);
	global $defCatalogAvailValueFields;
	$defCatalogAvailValueFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE);
	global $defCatalogAvailQuantityFields;
	$defCatalogAvailQuantityFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE_EXT);
	global $defCatalogAvailGroupFields;
	$defCatalogAvailGroupFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_SECTION);
	global $defCatalogAvailCurrencies;
	$defCatalogAvailCurrencies = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CURRENCY);
}

/*************************************************************/
/**
 * @deprecated deprecated since catalog 17.0.0
 * @see \Bitrix\Catalog\GroupTable::getList()
 *
 * @param string $by
 * @param string $order
 * @return bool|CDBResult
 */
function GetCatalogGroups($by = "SORT", $order = "ASC")
{
	return CCatalogGroup::GetList(array($by => $order));
}

/**
 * @deprecated deprecated since catalog 17.0.0
 * @see \Bitrix\Catalog\GroupTable::getList()
 *
 * @param int $CATALOG_GROUP_ID
 * @return array|false
 */
function GetCatalogGroup($CATALOG_GROUP_ID)
{
	$CATALOG_GROUP_ID = intval($CATALOG_GROUP_ID);
	return CCatalogGroup::GetByID($CATALOG_GROUP_ID);
}

/**
 * @deprecated deprecated since catalog 17.0.0
 * @see \Bitrix\Catalog\GroupLangTable::getList()
 *
 * @param int $CATALOG_GROUP_ID
 * @return string|null
 */
function GetCatalogGroupName($CATALOG_GROUP_ID)
{
	/** @noinspection PhpDeprecationInspection */
	$rn = GetCatalogGroup($CATALOG_GROUP_ID);
	return $rn["NAME_LANG"];
}

/**
 * @deprecated deprecated since catalog 17.0.0
 * @see \Bitrix\Catalog\ProductTable::getList()
 *
 * @param int $PRODUCT_ID
 * @return array|false
 */
function GetCatalogProduct($PRODUCT_ID)
{
	$PRODUCT_ID = intval($PRODUCT_ID);
	return CCatalogProduct::GetByID($PRODUCT_ID);
}

/**
 * @deprecated deprecated since catalog 17.0.0
 * @see CCatalogProduct::GetByIDEx or write optimal code
 *
 * @param int $PRODUCT_ID
 * @param bool $boolAllValues
 * @return array|bool
 */
function GetCatalogProductEx($PRODUCT_ID, $boolAllValues = false)
{
	$PRODUCT_ID = intval($PRODUCT_ID);
	return CCatalogProduct::GetByIDEx($PRODUCT_ID, $boolAllValues);
}

/**
 * @deprecated deprecated since catalog 17.0.0
 * @see \Bitrix\Catalog\PriceTable::getList()
 *
 * @param int $PRODUCT_ID
 * @param int $CATALOG_GROUP_ID
 * @return array|false
 */
function GetCatalogProductPrice($PRODUCT_ID, $CATALOG_GROUP_ID)
{
	$PRODUCT_ID = intval($PRODUCT_ID);
	$CATALOG_GROUP_ID = intval($CATALOG_GROUP_ID);

	$db_res = CPrice::GetList(($by="CATALOG_GROUP_ID"), ($order="ASC"), array("PRODUCT_ID"=>$PRODUCT_ID, "CATALOG_GROUP_ID"=>$CATALOG_GROUP_ID));

	if ($res = $db_res->Fetch())
		return $res;

	return false;
}

/**
 * @deprecated deprecated since catalog 17.0.0
 * @see \Bitrix\Catalog\PriceTable::getList()
 *
 * @param int $PRODUCT_ID
 * @param string $by
 * @param string $order
 * @return array
 */
function GetCatalogProductPriceList($PRODUCT_ID, $by = "SORT", $order = "ASC")
{
	$PRODUCT_ID = intval($PRODUCT_ID);

	$db_res = CPrice::GetList(
		array($by => $order),
		array("PRODUCT_ID" => $PRODUCT_ID)
	);

	$arPrice = array();
	while ($res = $db_res->Fetch())
	{
		$arPrice[] = $res;
	}

	return $arPrice;
}

/**
 * @deprecated
 *
 * @param int $IBLOCK
 * @param bool $SECT_ID
 * @param array $arOrder
 * @param int $cnt
 * @return false
 */
function GetCatalogProductTable($IBLOCK, $SECT_ID=false, $arOrder=array("sort"=>"asc"), $cnt=0)
{
	return false;
}

/**
 * @deprecated deprecated since catalog 9.0.0
 * @see CCurrencyLang::CurrencyFormat()
 *
 * @param int|float|string $fSum
 * @param string $strCurrency
 * @return string
 */
function FormatCurrency($fSum, $strCurrency)
{
	return CCurrencyLang::CurrencyFormat($fSum, $strCurrency, true);
}

/**
 * @deprecated deprecated since catalog 12.5.0
 * @see CCatalogProductProvider::GetProductData()
 *
 * @param int $productID
 * @param int|float $quantity
 * @param string $renewal
 * @param int $intUserID
 * @param bool|string $strSiteID
 * @return array|false
 */
function CatalogBasketCallback($productID, $quantity = 0, $renewal = "N", $intUserID = 0, $strSiteID = false)
{
	$arParams = array(
		'PRODUCT_ID' => $productID,
		'QUANTITY' => $quantity,
		'RENEWAL' => $renewal,
		'USER_ID' => $intUserID,
		'SITE_ID' => $strSiteID,
		'CHECK_QUANTITY' => 'Y',
		'AVAILABLE_QUANTITY' => 'Y'
	);

	return CCatalogProductProvider::GetProductData($arParams);
}

/**
 * @deprecated deprecated since catalog 12.5.0
 * @see CCatalogProductProvider::OrderProduct()
 *
 * @param int $productID
 * @param int|float $quantity
 * @param string $renewal
 * @param int $intUserID
 * @param bool|string $strSiteID
 * @return array|false
 */
function CatalogBasketOrderCallback($productID, $quantity, $renewal = "N", $intUserID = 0, $strSiteID = false)
{
	$arParams = array(
		'PRODUCT_ID' => $productID,
		'QUANTITY' => $quantity,
		'RENEWAL' => $renewal,
		'USER_ID' => $intUserID,
		'SITE_ID' => $strSiteID
	);

	$arResult = CCatalogProductProvider::OrderProduct($arParams);
	if (!empty($arResult) && is_array($arResult) && isset($arResult['QUANTITY']))
	{
		CCatalogProduct::QuantityTracer($productID, $arResult['QUANTITY']);
	}
	return $arResult;
}

/**
 * @deprecated
 *
 * @param int $productID
 * @param int $UserID
 * @param bool|mixed|string $strSiteID
 * @return array|bool
 */
function CatalogViewedProductCallback($productID, $UserID, $strSiteID = SITE_ID)
{
	global $USER;

	$productID = intval($productID);
	$UserID = intval($UserID);

	if ($productID <= 0)
		return false;

	static $arUserCache = array();
	if ($UserID > 0)
	{
		if (!isset($arUserCache[$UserID]))
		{
			$rsUsers = CUser::GetList('ID',	'ASC', array("ID_EQUAL_EXACT"=>$UserID), array('FIELDS' => array('ID')));
			if ($arUser = $rsUsers->Fetch())
				$arUserCache[$arUser['ID']] = CUser::GetUserGroup($arUser['ID']);
			else
				return false;
		}

		CCatalogDiscountSave::SetDiscountUserID($UserID);

		$dbIBlockElement = CIBlockElement::GetList(
			array(),
			array(
				"ID" => $productID,
				"ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
				"CHECK_PERMISSIONS" => "N",
			),
			false,
			false,
			array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'TIMESTAMP_X', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
		);
		if(!($arProduct = $dbIBlockElement->GetNext()))
			return false;

		if (CIBlock::GetArrayByID($arProduct['IBLOCK_ID'], "RIGHTS_MODE") == 'E')
		{
			$arUserRights = CIBlockElementRights::GetUserOperations($productID,$UserID);
			if (empty($arUserRights))
				return false;
			elseif (!is_array($arUserRights) || !array_key_exists('element_read',$arUserRights))
				return false;
		}
		else
		{
			if (CIBlock::GetPermission($arProduct['IBLOCK_ID'], $UserID) < 'R')
				return false;
		}
	}
	else
	{
		$dbIBlockElement = CIBlockElement::GetList(
			array(),
			array(
				"ID" => $productID,
				"ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
				"MIN_PERMISSION" => "R",
			),
			false,
			false,
			array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'TIMESTAMP_X', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
		);
		if(!($arProduct = $dbIBlockElement->GetNext()))
			return false;
	}

	$bTrace = true;
	if ($arCatalogProduct = CCatalogProduct::GetByID($productID))
	{
		if ($arCatalogProduct["CAN_BUY_ZERO"] != "Y" && ($arCatalogProduct["QUANTITY_TRACE"] == "Y" && doubleval($arCatalogProduct["QUANTITY"]) <= 0))
		{
			$currentPrice = 0.0;
			$currentDiscount = 0.0;
			$bTrace = false;
		}
	}

	if ($bTrace)
	{
		$arPrice = CCatalogProduct::GetOptimalPrice($productID, 1, ($UserID > 0 ? $arUserCache[$UserID] : $USER->GetUserGroupArray()), "N", array(), ($UserID > 0 ? $strSiteID : false), array());

		if (count($arPrice) > 0)
		{
			$currentPrice = $arPrice["PRICE"]["PRICE"];
			$currentDiscount = 0.0;

			if ($arPrice['PRICE']['VAT_INCLUDED'] == 'N')
			{
				if(doubleval($arPrice['PRICE']['VAT_RATE']) > 0)
				{
					$currentPrice *= (1 + $arPrice['PRICE']['VAT_RATE']);
					$arPrice['PRICE']['VAT_INCLUDED'] = 'Y';
				}
			}

			if (!empty($arPrice["DISCOUNT"]))
			{
				if ($arPrice["DISCOUNT"]["VALUE_TYPE"]=="F")
				{
					if ($arPrice["DISCOUNT"]["CURRENCY"] == $arPrice["PRICE"]["CURRENCY"])
						$currentDiscount = $arPrice["DISCOUNT"]["VALUE"];
					else
						$currentDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["VALUE"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
				}
				elseif ($arPrice["DISCOUNT"]["VALUE_TYPE"]=="S")
				{
					if ($arPrice["DISCOUNT"]["CURRENCY"] == $arPrice["PRICE"]["CURRENCY"])
						$currentDiscount = $arPrice["DISCOUNT"]["VALUE"];
					else
						$currentDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["VALUE"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
				}
				else
				{
					$currentDiscount = $currentPrice * $arPrice["DISCOUNT"]["VALUE"] / 100.0;

					if (doubleval($arPrice["DISCOUNT"]["MAX_DISCOUNT"]) > 0)
					{
						if ($arPrice["DISCOUNT"]["CURRENCY"] == $arPrice["PRICE"]["CURRENCY"])
							$maxDiscount = $arPrice["DISCOUNT"]["MAX_DISCOUNT"];
						else
							$maxDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["MAX_DISCOUNT"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);

						if ($currentDiscount > $maxDiscount)
							$currentDiscount = $maxDiscount;
					}
				}

				if ($arPrice["DISCOUNT"]["VALUE_TYPE"] == "S")
				{
					$currentDiscount_tmp = $currentPrice - $currentDiscount;
					$currentPrice = $currentDiscount;
					$currentDiscount = $currentDiscount_tmp;
					unset($currentDiscount_tmp);
				}
				else
				{
					$currentPrice = $currentPrice - $currentDiscount;
				}
			}

			if (empty($arPrice["PRICE"]["CATALOG_GROUP_NAME"]))
			{
				if (!empty($arPrice["PRICE"]["CATALOG_GROUP_ID"]))
				{
					$rsCatGroups = CCatalogGroup::GetList(array(),array('ID' => $arPrice["PRICE"]["CATALOG_GROUP_ID"]),false,array('nTopCount' => 1),array('ID','NAME','NAME_LANG'));
					if ($arCatGroup = $rsCatGroups->Fetch())
					{
						$arPrice["PRICE"]["CATALOG_GROUP_NAME"] = (!empty($arCatGroup['NAME_LANG']) ? $arCatGroup['NAME_LANG'] : $arCatGroup['NAME']);
					}
				}
			}
		}
		else
		{
			$currentPrice = 0.0;
			$currentDiscount = 0.0;
		}
	}

	$arResult = array(
		"PREVIEW_PICTURE" => $arProduct['PREVIEW_PICTURE'],
		"DETAIL_PICTURE" => $arProduct['DETAIL_PICTURE'],
		"PRODUCT_PRICE_ID" => $arPrice["PRICE"]["ID"],
		"PRICE" => $currentPrice,
		"VAT_RATE" => $arPrice['PRICE']['VAT_RATE'],
		"CURRENCY" => $arPrice["PRICE"]["CURRENCY"],
		"DISCOUNT_PRICE" => $currentDiscount,
		"NAME" => $arProduct["~NAME"],
		"DETAIL_PAGE_URL" => $arProduct['~DETAIL_PAGE_URL'],
		"NOTES" => $arPrice["PRICE"]["CATALOG_GROUP_NAME"]
	);

	if ($UserID > 0)
		CCatalogDiscountSave::ClearDiscountUserID();

	return $arResult;
}

/**
 * @deprecated deprecated since catalog 12.5.6
 * @see CCatalogDiscountCoupon::CouponOneOrderDisable()
 *
 * @param int $intOrderID
 * @return void
 */
function CatalogDeactivateOneTimeCoupons($intOrderID = 0)
{
	CCatalogDiscountCoupon::CouponOneOrderDisable($intOrderID);
}

function CatalogPayOrderCallback($productID, $userID, $bPaid, $orderID)
{
	global $DB;
	global $USER;

	$productID = intval($productID);
	$userID = intval($userID);
	$bPaid = (bool)$bPaid;
	$orderID = intval($orderID);

	if ($userID <= 0)
		return false;

	$dbIBlockElement = CIBlockElement::GetList(
		array(),
		array(
			"ID" => $productID,
			"ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"CHECK_PERMISSIONS" => "N",
		),
		false,
		false,
		array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL')
	);
	if ($arIBlockElement = $dbIBlockElement->GetNext())
	{
		$arCatalog = CCatalog::GetByID($arIBlockElement["IBLOCK_ID"]);
		if ($arCatalog["SUBSCRIPTION"] == "Y")
		{
			$arProduct = CCatalogProduct::GetByID($productID);

			if ($bPaid)
			{
				if ('E' == CIBlock::GetArrayByID($arIBlockElement['IBLOCK_ID'], "RIGHTS_MODE"))
				{
					$arUserRights = CIBlockElementRights::GetUserOperations($productID, $userID);
					if (empty($arUserRights))
					{
						return false;
					}
					elseif (!is_array($arUserRights) || !array_key_exists('element_read', $arUserRights))
					{
						return false;
					}
				}
				else
				{
					if ('R' > CIBlock::GetPermission($arIBlockElement['IBLOCK_ID'], $userID))
					{
						return false;
					}
				}

				$arUserGroups = array();
				$arTmp = array();
				$ind = -1;
				$curTime = time();
				$dbProductGroups = CCatalogProductGroups::GetList(
					array(),
					array("PRODUCT_ID" => $productID),
					false,
					false,
					array("GROUP_ID", "ACCESS_LENGTH", "ACCESS_LENGTH_TYPE")
				);
				while ($arProductGroups = $dbProductGroups->Fetch())
				{
					$ind++;

					$arProductGroups['GROUP_ID'] = intval($arProductGroups['GROUP_ID']);
					$accessType = $arProductGroups["ACCESS_LENGTH_TYPE"];
					$accessLength = intval($arProductGroups["ACCESS_LENGTH"]);

					$accessVal = 0;
					if (0 < $accessLength)
					{
						if ($accessType == CCatalogProduct::TIME_PERIOD_HOUR)
							$accessVal = mktime(date("H") + $accessLength, date("i"), date("s"), date("m"), date("d"), date("Y"));
						elseif ($accessType == CCatalogProduct::TIME_PERIOD_DAY)
							$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $accessLength, date("Y"));
						elseif ($accessType == CCatalogProduct::TIME_PERIOD_WEEK)
							$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7 * $accessLength, date("Y"));
						elseif ($accessType == CCatalogProduct::TIME_PERIOD_MONTH)
							$accessVal = mktime(date("H"), date("i"), date("s"), date("m") + $accessLength, date("d"), date("Y"));
						elseif ($accessType == CCatalogProduct::TIME_PERIOD_QUART)
							$accessVal = mktime(date("H"), date("i"), date("s"), date("m") + 3 * $accessLength, date("d"), date("Y"));
						elseif ($accessType == CCatalogProduct::TIME_PERIOD_SEMIYEAR)
							$accessVal = mktime(date("H"), date("i"), date("s"), date("m") + 6 * $accessLength, date("d"), date("Y"));
						elseif ($accessType == CCatalogProduct::TIME_PERIOD_YEAR)
							$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + $accessLength);
						elseif ($accessType == CCatalogProduct::TIME_PERIOD_DOUBLE_YEAR)
							$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + 2 * $accessLength);
					}

					$arUserGroups[$ind] = array(
						"GROUP_ID" => $arProductGroups["GROUP_ID"],
						"DATE_ACTIVE_FROM" => date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)), $curTime),
						"DATE_ACTIVE_TO" => (0 < $accessLength ? date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)), $accessVal) : false)
					);

					$arTmp[$arProductGroups["GROUP_ID"]] = $ind;
				}

				if (!empty($arUserGroups))
				{
					$dbOldGroups = CUser::GetUserGroupEx($userID);
					while ($arOldGroups = $dbOldGroups->Fetch())
					{
						$arOldGroups["GROUP_ID"] = intval($arOldGroups["GROUP_ID"]);
						if (array_key_exists($arOldGroups["GROUP_ID"], $arTmp))
						{
							if ($arOldGroups["DATE_ACTIVE_FROM"] == '')
							{
								$arUserGroups[$arTmp[$arOldGroups["GROUP_ID"]]]["DATE_ACTIVE_FROM"] = false;
							}
							else
							{
								$oldDate = CDatabase::FormatDate($arOldGroups["DATE_ACTIVE_FROM"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								$newDate = CDatabase::FormatDate($arUserGroups[$arTmp[$arOldGroups["GROUP_ID"]]]["DATE_ACTIVE_FROM"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								if ($oldDate > $newDate)
									$arUserGroups[$arTmp[$arOldGroups["GROUP_ID"]]]["DATE_ACTIVE_FROM"] = $arOldGroups["DATE_ACTIVE_FROM"];
							}

							if ($arOldGroups["DATE_ACTIVE_TO"] == '')
							{
								$arUserGroups[$arTmp[$arOldGroups["GROUP_ID"]]]["DATE_ACTIVE_TO"] = false;
							}
							elseif (false !== $arUserGroups[$arTmp[$arOldGroups["GROUP_ID"]]]["DATE_ACTIVE_TO"])
							{
								$oldDate = CDatabase::FormatDate($arOldGroups["DATE_ACTIVE_TO"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								$newDate = CDatabase::FormatDate($arUserGroups[$arTmp[$arOldGroups["GROUP_ID"]]]["DATE_ACTIVE_TO"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								if ($oldDate > $newDate)
									$arUserGroups[$arTmp[$arOldGroups["GROUP_ID"]]]["DATE_ACTIVE_TO"] = $arOldGroups["DATE_ACTIVE_TO"];
							}
						}
						else
						{
							$ind++;

							$arUserGroups[$ind] = array(
								"GROUP_ID" => $arOldGroups["GROUP_ID"],
								"DATE_ACTIVE_FROM" => $arOldGroups["DATE_ACTIVE_FROM"],
								"DATE_ACTIVE_TO" => $arOldGroups["DATE_ACTIVE_TO"]
							);
						}
					}

					CUser::SetUserGroup($userID, $arUserGroups);
					if (CCatalog::IsUserExists())
					{
						if (intval($USER->GetID()) == $userID)
						{
							$arUserGroupsTmp = array();
							foreach ($arUserGroups as &$arOneGroup)
							{
								$arUserGroupsTmp[] = $arOneGroup["GROUP_ID"];
							}
							if (isset($arOneGroup))
								unset($arOneGroup);

							$USER->SetUserGroupArray($arUserGroupsTmp);
						}
					}
				}
			}
			else
			{
				$arUserGroups = array();
				$ind = -1;
				$arTmp = array();

				$dbOldGroups = CUser::GetUserGroupEx($userID);
				while ($arOldGroups = $dbOldGroups->Fetch())
				{
					$ind++;
					$arOldGroups["GROUP_ID"] = intval($arOldGroups["GROUP_ID"]);
					$arUserGroups[$ind] = array(
						"GROUP_ID" => $arOldGroups["GROUP_ID"],
						"DATE_ACTIVE_FROM" => $arOldGroups["DATE_ACTIVE_FROM"],
						"DATE_ACTIVE_TO" => $arOldGroups["DATE_ACTIVE_FROM"]
					);

					$arTmp[$arOldGroups["GROUP_ID"]] = $ind;
				}

				$bNeedUpdate = false;
				$dbProductGroups = CCatalogProductGroups::GetList(
					array(),
					array("PRODUCT_ID" => $productID),
					false,
					false,
					array("GROUP_ID")
				);
				while ($arProductGroups = $dbProductGroups->Fetch())
				{
					$arProductGroups["GROUP_ID"] = intval($arProductGroups["GROUP_ID"]);
					if (array_key_exists($arProductGroups["GROUP_ID"], $arTmp))
					{
						unset($arUserGroups[$arProductGroups["GROUP_ID"]]);
						$bNeedUpdate = true;
					}
				}

				if ($bNeedUpdate)
				{
					CUser::SetUserGroup($userID, $arUserGroups);

					if (CCatalog::IsUserExists())
					{
						if (intval($USER->GetID()) == $userID)
						{
							$arUserGroupsTmp = array();
							foreach ($arUserGroups as &$arOneGroup)
							{
								$arUserGroupsTmp[] = $arOneGroup["GROUP_ID"];
							}
							if (isset($arOneGroup))
								unset($arOneGroup);

							$USER->SetUserGroupArray($arUserGroupsTmp);
						}
					}
				}
			}

			if ($arProduct["PRICE_TYPE"] != "S")
			{
				if ($bPaid)
				{
					$recurType = $arProduct["RECUR_SCHEME_TYPE"];
					$recurLength = intval($arProduct["RECUR_SCHEME_LENGTH"]);

					$recurSchemeVal = 0;
					if ($recurType == CCatalogProduct::TIME_PERIOD_HOUR)
						$recurSchemeVal = mktime(date("H") + $recurLength, date("i"), date("s"), date("m"), date("d"), date("Y"));
					elseif ($recurType == CCatalogProduct::TIME_PERIOD_DAY)
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $recurLength, date("Y"));
					elseif ($recurType == CCatalogProduct::TIME_PERIOD_WEEK)
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7 * $recurLength, date("Y"));
					elseif ($recurType == CCatalogProduct::TIME_PERIOD_MONTH)
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + $recurLength, date("d"), date("Y"));
					elseif ($recurType == CCatalogProduct::TIME_PERIOD_QUART)
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 3 * $recurLength, date("d"), date("Y"));
					elseif ($recurType == CCatalogProduct::TIME_PERIOD_SEMIYEAR)
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 6 * $recurLength, date("d"), date("Y"));
					elseif ($recurType == CCatalogProduct::TIME_PERIOD_YEAR)
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + $recurLength);
					elseif ($recurType == CCatalogProduct::TIME_PERIOD_DOUBLE_YEAR)
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + 2 * $recurLength);

					$arFields = array(
						"USER_ID" => $userID,
						"MODULE" => "catalog",
						"PRODUCT_ID" => $productID,
						"PRODUCT_NAME" => $arIBlockElement["~NAME"],
						"PRODUCT_URL" => $arIBlockElement["~DETAIL_PAGE_URL"],
						"PRODUCT_PRICE_ID" => false,
						"PRICE_TYPE" => $arProduct["PRICE_TYPE"],
						"RECUR_SCHEME_TYPE" => $recurType,
						"RECUR_SCHEME_LENGTH" => $recurLength,
						"WITHOUT_ORDER" => $arProduct["WITHOUT_ORDER"],
						"PRICE" => false,
						"CURRENCY" => false,
						"CANCELED" => "N",
						"CANCELED_REASON" => false,
						"PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
						"DESCRIPTION" => false,
						"PRIOR_DATE" => false,
						"NEXT_DATE" => Date(
							$DB->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)),
							$recurSchemeVal
						)
					);
					return $arFields;
				}
			}
		}
		return true;
	}

	return false;
}

function CatalogRecurringCallback($productID, $userID)
{
	global $APPLICATION;
	global $DB;

	$productID = intval($productID);
	if ($productID <= 0)
		return false;

	$userID = intval($userID);
	if ($userID <= 0)
		return false;

	$arProduct = CCatalogProduct::GetByID($productID);
	if (!$arProduct)
	{
		$APPLICATION->ThrowException(str_replace("#ID#", $productID, Loc::getMessage("I_NO_PRODUCT")), "NO_PRODUCT");
		return false;
	}

	if ($arProduct["PRICE_TYPE"] == "T")
	{
		$arProduct = CCatalogProduct::GetByID($arProduct["TRIAL_PRICE_ID"]);
		if (!$arProduct)
		{
			$APPLICATION->ThrowException(str_replace("#TRIAL_ID#", $productID, str_replace("#ID#", $arProduct["TRIAL_PRICE_ID"], Loc::getMessage("I_NO_TRIAL_PRODUCT"))), "NO_PRODUCT_TRIAL");
			return false;
		}
	}
	$productID = intval($arProduct["ID"]);

	if ($arProduct["PRICE_TYPE"] != "R")
	{
		$APPLICATION->ThrowException(str_replace("#ID#", $productID, Loc::getMessage("I_PRODUCT_NOT_SUBSCR")), "NO_IBLOCK_SUBSCR");
		return false;
	}

	$dbIBlockElement = CIBlockElement::GetList(
		array(),
		array(
			"ID" => $productID,
			"ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"CHECK_PERMISSIONS" => "N",
		),
		false,
		false,
		array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL')
	);
	if(!($arIBlockElement = $dbIBlockElement->GetNext()))
	{
		$APPLICATION->ThrowException(str_replace("#ID#", $productID, Loc::getMessage("I_NO_IBLOCK_ELEM")), "NO_IBLOCK_ELEMENT");
		return false;
	}
	if ('E' == CIBlock::GetArrayByID($arIBlockElement['IBLOCK_ID'], "RIGHTS_MODE"))
	{
		$arUserRights = CIBlockElementRights::GetUserOperations($productID, $userID);
		if (empty($arUserRights))
		{
			$APPLICATION->ThrowException(str_replace("#ID#", $productID, Loc::getMessage("I_NO_IBLOCK_ELEM")), "NO_IBLOCK_ELEMENT");
			return false;
		}
		elseif (!is_array($arUserRights) || !array_key_exists('element_read', $arUserRights))
		{
			$APPLICATION->ThrowException(str_replace("#ID#", $productID, Loc::getMessage("I_NO_IBLOCK_ELEM")), "NO_IBLOCK_ELEMENT");
			return false;
		}
	}
	else
	{
		if ('R' > CIBlock::GetPermission($arIBlockElement['IBLOCK_ID'], $userID))
		{
			$APPLICATION->ThrowException(str_replace("#ID#", $productID, Loc::getMessage("I_NO_IBLOCK_ELEM")), "NO_IBLOCK_ELEMENT");
			return false;
		}
	}

	$arCatalog = CCatalog::GetByID($arIBlockElement["IBLOCK_ID"]);
	if ($arCatalog["SUBSCRIPTION"] != "Y")
	{
		$APPLICATION->ThrowException(str_replace("#ID#", $arIBlockElement["IBLOCK_ID"], Loc::getMessage("I_CATALOG_NOT_SUBSCR")), "NOT_SUBSCRIPTION");
		return false;
	}

	if ($arProduct["CAN_BUY_ZERO"]!="Y" && ($arProduct["QUANTITY_TRACE"] == "Y" && doubleval($arProduct["QUANTITY"]) <= 0))
	{
		$APPLICATION->ThrowException(str_replace("#ID#", $productID, Loc::getMessage("I_PRODUCT_SOLD")), "PRODUCT_END");
		return false;
	}

	$arUserGroups = CUser::GetUserGroup($userID);
	$arUserGroups = array_values(array_unique($arUserGroups));

	CCatalogDiscountSave::Disable();

	$arPrice = CCatalogProduct::GetOptimalPrice($productID, 1, $arUserGroups, "Y");
	if (empty($arPrice))
	{
		if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, 1, $arUserGroups))
		{
			$quantity = $nearestQuantity;
			$arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $arUserGroups, "Y");
		}
	}

	CCatalogDiscountSave::Enable();

	if (empty($arPrice))
	{
		return false;
	}

	$currentPrice = $arPrice["PRICE"]["PRICE"];

	//SIGURD: logic change. see mantiss 5036.
	// discount applied to a final price with VAT already included.
	if (doubleval($arPrice['PRICE']['VAT_RATE']) > 0 && $arPrice['PRICE']['VAT_INCLUDED'] != 'Y')
		$currentPrice *= (1 + $arPrice['PRICE']['VAT_RATE']);

	$arDiscountList = array();

	if (!empty($arPrice["DISCOUNT_LIST"]))
	{
		foreach ($arPrice["DISCOUNT_LIST"] as &$arOneDiscount)
		{
			switch ($arOneDiscount['VALUE_TYPE'])
			{
				case CCatalogDiscount::TYPE_FIX:
					if ($arOneDiscount['CURRENCY'] == $arPrice["PRICE"]["CURRENCY"])
						$currentDiscount = $arOneDiscount['VALUE'];
					else
						$currentDiscount = CCurrencyRates::ConvertCurrency($arOneDiscount["VALUE"], $arOneDiscount["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
					$currentPrice = $currentPrice - $currentDiscount;
					unset($currentDiscount);
					break;
				case CCatalogDiscount::TYPE_PERCENT:
					$currentDiscount = $currentPrice*$arOneDiscount["VALUE"]/100.0;
					if (0 < $arOneDiscount['MAX_DISCOUNT'])
					{
						if ($arOneDiscount['CURRENCY'] == $arPrice["PRICE"]["CURRENCY"])
							$dblMaxDiscount = $arOneDiscount['MAX_DISCOUNT'];
						else
							$dblMaxDiscount = CCurrencyRates::ConvertCurrency($arOneDiscount['MAX_DISCOUNT'], $arOneDiscount["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
						if ($currentDiscount > $dblMaxDiscount)
							$currentDiscount = $dblMaxDiscount;
					}
					$currentPrice = $currentPrice - $currentDiscount;
					unset($currentDiscount);
					break;
				case CCatalogDiscount::TYPE_SALE:
					if ($arOneDiscount['CURRENCY'] == $arPrice["PRICE"]["CURRENCY"])
						$currentPrice = $arOneDiscount['VALUE'];
					else
						$currentPrice = CCurrencyRates::ConvertCurrency($arOneDiscount['VALUE'], $arOneDiscount["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
					break;
			}

			$arOneList = array(
				'ID' => $arOneDiscount['ID'],
				'NAME' => $arOneDiscount['NAME'],
				'COUPON' => '',
				'MODULE_ID' => 'catalog',
			);

			if ($arOneDiscount['COUPON'])
			{
				$arOneList['COUPON'] = $arOneDiscount['COUPON'];
			}
			$arDiscountList[] = $arOneList;
		}
		unset($arOneDiscount);
	}

	$recurType = $arProduct["RECUR_SCHEME_TYPE"];
	$recurLength = intval($arProduct["RECUR_SCHEME_LENGTH"]);

	$recurSchemeVal = 0;
	if ($recurType == CCatalogProduct::TIME_PERIOD_HOUR)
		$recurSchemeVal = mktime(date("H") + $recurLength, date("i"), date("s"), date("m"), date("d"), date("Y"));
	elseif ($recurType == CCatalogProduct::TIME_PERIOD_DAY)
		$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $recurLength, date("Y"));
	elseif ($recurType == CCatalogProduct::TIME_PERIOD_WEEK)
		$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7 * $recurLength, date("Y"));
	elseif ($recurType == CCatalogProduct::TIME_PERIOD_MONTH)
		$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + $recurLength, date("d"), date("Y"));
	elseif ($recurType == CCatalogProduct::TIME_PERIOD_QUART)
		$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 3 * $recurLength, date("d"), date("Y"));
	elseif ($recurType == CCatalogProduct::TIME_PERIOD_SEMIYEAR)
		$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 6 * $recurLength, date("d"), date("Y"));
	elseif ($recurType == CCatalogProduct::TIME_PERIOD_YEAR)
		$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + $recurLength);
	elseif ($recurType == CCatalogProduct::TIME_PERIOD_DOUBLE_YEAR)
		$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + 2 * $recurLength);

	$arResult = array(
		"WEIGHT" => floatval($arProduct["WEIGHT"]),
		"DIMENSIONS" => serialize(array(
			"WIDTH" => $arProduct["WIDTH"],
			"HEIGHT" => $arProduct["HEIGHT"],
			"LENGTH" => $arProduct["LENGTH"]
		)),
		"VAT_RATE" => $arPrice["PRICE"]["VAT_RATE"],
		"QUANTITY" => 1,
		"PRICE" => $currentPrice,
		"WITHOUT_ORDER" => $arProduct["WITHOUT_ORDER"],
		"PRODUCT_ID" => $productID,
		"PRODUCT_NAME" => $arIBlockElement["~NAME"],
		"PRODUCT_URL" => $arIBlockElement["~DETAIL_PAGE_URL"],
		"PRODUCT_PRICE_ID" => $arPrice["PRICE"]["ID"],
		"CURRENCY" => $arPrice["PRICE"]["CURRENCY"],
		"NAME" => $arIBlockElement["NAME"],
		"MODULE" => "catalog",
		"PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
		"CATALOG_GROUP_NAME" => $arPrice["PRICE"]["CATALOG_GROUP_NAME"],
		"DETAIL_PAGE_URL" => $arIBlockElement["~DETAIL_PAGE_URL"],
		"PRICE_TYPE" => $arProduct["PRICE_TYPE"],
		"RECUR_SCHEME_TYPE" => $arProduct["RECUR_SCHEME_TYPE"],
		"RECUR_SCHEME_LENGTH" => $arProduct["RECUR_SCHEME_LENGTH"],
		"PRODUCT_XML_ID" => $arIBlockElement["~XML_ID"],
		"TYPE" => ($arProduct["TYPE"] == CCatalogProduct::TYPE_SET) ? CCatalogProductSet::TYPE_SET : NULL,
		"NEXT_DATE" => date(
			$DB->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)),
			$recurSchemeVal
		)
	);
	if (!empty($arPrice["DISCOUNT_LIST"]))
	{
		$arResult['DISCOUNT_LIST'] = $arDiscountList;
	}

	return $arResult;
}

function CatalogBasketCancelCallback($PRODUCT_ID, $QUANTITY, $bCancel)
{
	$PRODUCT_ID = intval($PRODUCT_ID);
	$QUANTITY = doubleval($QUANTITY);
	$bCancel = (bool)$bCancel;

	if ($bCancel)
		CCatalogProduct::QuantityTracer($PRODUCT_ID, -$QUANTITY);
	else
	{
		CCatalogProduct::QuantityTracer($PRODUCT_ID, $QUANTITY);
	}
}

/**
 * @param int $PRICE_ID
 * @param float|int $QUANTITY
 * @param array $arRewriteFields
 * @param array $arProductParams
 * @return bool|int
 */
function Add2Basket($PRICE_ID, $QUANTITY = 1, $arRewriteFields = array(), $arProductParams = array())
{
	global $APPLICATION;

	$PRICE_ID = (int)$PRICE_ID;
	if ($PRICE_ID <= 0)
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_PRODUCT_PRICE_NOT_FOUND'), "NO_PRODUCT_PRICE");
		return false;
	}
	$QUANTITY = (float)$QUANTITY;
	if ($QUANTITY <= 0)
		$QUANTITY = 1;

	if (!Loader::includeModule("sale"))
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_SALE_MODULE'), "NO_SALE_MODULE");
		return false;
	}
	if (Loader::includeModule("statistic") && isset($_SESSION['SESS_SEARCHER_ID']) && (int)$_SESSION["SESS_SEARCHER_ID"] > 0)
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_SESS_SEARCHER'), "SESS_SEARCHER");
		return false;
	}

	$rsPrices = CPrice::GetListEx(
		array(),
		array('ID' => $PRICE_ID),
		false,
		false,
		array(
			'ID',
			'PRODUCT_ID',
			'PRICE',
			'CURRENCY',
			'CATALOG_GROUP_ID'
		)
	);
	if (!($arPrice = $rsPrices->Fetch()))
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_PRODUCT_PRICE_NOT_FOUND'), "NO_PRODUCT_PRICE");
		return false;
	}
	$arPrice['CATALOG_GROUP_NAME'] = '';
	$rsCatGroups = CCatalogGroup::GetListEx(
		array(),
		array('ID' => $arPrice['CATALOG_GROUP_ID']),
		false,
		false,
		array(
			'ID',
			'NAME',
			'NAME_LANG'
		)
	);
	if ($arCatGroup = $rsCatGroups->Fetch())
	{
		$arPrice['CATALOG_GROUP_NAME'] = (!empty($arCatGroup['NAME_LANG']) ? $arCatGroup['NAME_LANG'] : $arCatGroup['NAME']);
	}
	$rsProducts = CCatalogProduct::GetList(
		array(),
		array('ID' => $arPrice["PRODUCT_ID"]),
		false,
		false,
		array(
			'ID',
			'CAN_BUY_ZERO',
			'QUANTITY_TRACE',
			'QUANTITY',
			'WEIGHT',
			'WIDTH',
			'HEIGHT',
			'LENGTH',
			'TYPE',
			'MEASURE'
		)
	);
	if (!($arCatalogProduct = $rsProducts->Fetch()))
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_PRODUCT'), "NO_PRODUCT");
		return false;
	}
	if (
		($arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_SKU || $arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
		&& Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
	)
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_CANNOT_ADD_SKU'), "NO_PRODUCT");
		return false;
	}
	if ($arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_SET)
	{
		$allSets = CCatalogProductSet::getAllSetsByProduct($arPrice['PRODUCT_ID'], CCatalogProductSet::TYPE_SET);
		if (empty($allSets))
		{
			$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_PRODUCT_SET'), "NO_PRODUCT");
			return false;
		}
		$set = current($allSets);
		unset($allSets);
		$itemIds = array();
		foreach ($set['ITEMS'] as $item)
		{
			if ($item['ITEM_ID'] != $item['OWNER_ID'])
				$itemIds[$item['ITEM_ID']] = $item['ITEM_ID'];
		}
		if (empty($itemIds))
		{
			$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_PRODUCT_SET'), "NO_PRODUCT");
			return false;
		}
		$iterator = CIBlockElement::GetList(
			array(),
			array(
				'ID' => $itemIds,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'N'
			),
			false,
			false,
			array('ID', 'IBLOCK_ID')
		);
		while ($row = $iterator->Fetch())
		{
			if (isset($itemIds[$row['ID']]))
				unset($itemIds[$row['ID']]);
		}
		unset($row, $iterator);
		if (!empty($itemIds))
		{
			$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_PRODUCT_SET_ITEM'), "NO_PRODUCT");
			return false;
		}
	}

	$arCatalogProduct['MEASURE'] = (int)$arCatalogProduct['MEASURE'];
	$arCatalogProduct['MEASURE_NAME'] = '';
	$arCatalogProduct['MEASURE_CODE'] = 0;
	if ($arCatalogProduct['MEASURE'] <= 0)
	{
		$arMeasure = CCatalogMeasure::getDefaultMeasure(true, true);
		$arCatalogProduct['MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
		$arCatalogProduct['MEASURE_CODE'] = $arMeasure['CODE'];
	}
	else
	{
		$rsMeasures = CCatalogMeasure::getList(
			array(),
			array('ID' => $arCatalogProduct['MEASURE']),
			false,
			false,
			array('ID', 'SYMBOL_RUS', 'CODE')
		);
		if ($arMeasure = $rsMeasures->GetNext())
		{
			$arCatalogProduct['MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
			$arCatalogProduct['MEASURE_CODE'] = $arMeasure['CODE'];
		}
	}

	$dblQuantity = (float)$arCatalogProduct["QUANTITY"];
	$boolQuantity = ($arCatalogProduct["CAN_BUY_ZERO"] != 'Y' && $arCatalogProduct["QUANTITY_TRACE"] == 'Y');
	if ($boolQuantity && $dblQuantity <= 0)
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_PRODUCT_RUN_OUT'), "PRODUCT_RUN_OUT");
		return false;
	}

	$rsItems = CIBlockElement::GetList(
		array(),
		array(
			"ID" => $arPrice["PRODUCT_ID"],
			"ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => "R"
		),
		false,
		false,
		array(
			'ID',
			'IBLOCK_ID',
			'NAME',
			'XML_ID',
			'DETAIL_PAGE_URL'
		)
	);
	if (!($arProduct = $rsItems->GetNext()))
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_PRODUCT'), "NO_PRODUCT");
		return false;
	}

	$arProps = array();

	$strIBlockXmlID = (string)CIBlock::GetArrayByID($arProduct['IBLOCK_ID'], 'XML_ID');
	if ($strIBlockXmlID !== '')
	{
		$arProps[] = array(
			"NAME" => "Catalog XML_ID",
			"CODE" => "CATALOG.XML_ID",
			"VALUE" => $strIBlockXmlID
		);
	}

	// add sku props
	$arParentSku = CCatalogSku::GetProductInfo($arProduct['ID'], $arProduct['IBLOCK_ID']);
	if (!empty($arParentSku))
	{
		if (mb_strpos($arProduct["~XML_ID"], '#') === false)
		{
			$parentIterator = Iblock\ElementTable::getList(array(
				'select' => array('ID', 'XML_ID'),
				'filter' => array('ID' => $arParentSku['ID'])
			));
			if ($parent = $parentIterator->fetch())
			{
				$arProduct["~XML_ID"] = $parent['XML_ID'].'#'.$arProduct["~XML_ID"];
			}
			unset($parent, $parentIterator);
		}
	}

	if (!empty($arProductParams) && is_array($arProductParams))
	{
		foreach ($arProductParams as &$arOneProductParams)
		{
			$arProps[] = array(
				"NAME" => $arOneProductParams["NAME"],
				"CODE" => $arOneProductParams["CODE"],
				"VALUE" => $arOneProductParams["VALUE"],
				"SORT" => $arOneProductParams["SORT"],
			);
		}
		unset($arOneProductParams);
	}

	$arProps[] = array(
		"NAME" => "Product XML_ID",
		"CODE" => "PRODUCT.XML_ID",
		"VALUE" => $arProduct["~XML_ID"]
	);

	$arFields = array(
		"PRODUCT_ID" => $arPrice["PRODUCT_ID"],
		"PRODUCT_PRICE_ID" => $PRICE_ID,
		"BASE_PRICE" => $arPrice["PRICE"],
		"PRICE" => $arPrice["PRICE"],
		"DISCOUNT_PRICE" => 0,
		"CURRENCY" => $arPrice["CURRENCY"],
		"WEIGHT" => $arCatalogProduct["WEIGHT"],
		"DIMENSIONS" => serialize(array(
			"WIDTH" => $arCatalogProduct["WIDTH"],
			"HEIGHT" => $arCatalogProduct["HEIGHT"],
			"LENGTH" => $arCatalogProduct["LENGTH"]
		)),
		"QUANTITY" => ($boolQuantity && $dblQuantity < $QUANTITY ? $dblQuantity : $QUANTITY),
		"LID" => SITE_ID,
		"DELAY" => "N",
		"CAN_BUY" => "Y",
		"NAME" => $arProduct["~NAME"],
		"MODULE" => "catalog",
		"PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
		"NOTES" => $arPrice["CATALOG_GROUP_NAME"],
		"DETAIL_PAGE_URL" => $arProduct["~DETAIL_PAGE_URL"],
		"CATALOG_XML_ID" => $strIBlockXmlID,
		"PRODUCT_XML_ID" => $arProduct["~XML_ID"],
		"PROPS" => $arProps,
		"TYPE" => ($arCatalogProduct["TYPE"] == CCatalogProduct::TYPE_SET) ? CCatalogProductSet::TYPE_SET : NULL,
		"MEASURE_NAME" => $arCatalogProduct['MEASURE_NAME'],
		"MEASURE_CODE" => $arCatalogProduct['MEASURE_CODE'],
		'MEASURE' => array(
			'ID' => $arCatalogProduct['MEASURE'],
			'NAME' => $arCatalogProduct['MEASURE_NAME'],
			'CODE' => $arCatalogProduct['MEASURE_CODE']
		)
	);

	if (!empty($arRewriteFields) && is_array($arRewriteFields))
	{
		$arFields = array_merge($arFields, $arRewriteFields);
	}
	$result = CSaleBasket::Add($arFields);

	if ($result)
	{
		if (Loader::includeModule("statistic"))
			CStatistic::Set_Event("eStore", "add2basket", $arFields["PRODUCT_ID"]);
	}

	return $result;
}

/**
 * @deprecated deprecated since catalog 17.5.9
 * @see \Bitrix\Catalog\Product\Basket::addProduct
 *
 * @param int $productId
 * @param float|int $quantity
 * @param array $rewriteFields
 * @param bool|array $productParams
 * @return false|int
 */
function Add2BasketByProductID($productId, $quantity = 1, $rewriteFields = array(), $productParams = false)
{
	global $APPLICATION;

	$result = false;

	if (!Loader::includeModule('sale'))
	{
		return $result;
	}

	$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

	/* for old use */
	if ($productParams === false)
	{
		$productParams = $rewriteFields;
		$rewriteFields = array();
	}

	$rewrite = (!empty($rewriteFields) && is_array($rewriteFields));
	if ($rewrite && isset($rewriteFields['SUBSCRIBE']) && $rewriteFields['SUBSCRIBE'] == 'Y')
		return SubscribeProduct($productId, $rewriteFields, $productParams);

	$quantity = (empty($quantity) ? 1 : (float)$quantity);
	if ($quantity <= 0)
		$quantity = 1;

	$product = array(
		'PRODUCT_ID' => $productId,
		'QUANTITY' => $quantity
	);
	if (!empty($productParams))
		$product['PROPS'] = $productParams;

	$basketResult = Catalog\Product\Basket::addProduct($product, ($rewrite ? $rewriteFields : array()));
	if ($basketResult->isSuccess())
	{
		$data = $basketResult->getData();
		$result = $data['ID'];
		unset($data);

		if (!empty($rewriteFields['ORDER_ID']) && intval($rewriteFields['ORDER_ID']) > 0)
		{
			trigger_error("Wrong API usage of adding a product in order", E_USER_WARNING);

			$productId = (int)$productId;
			if ($productId <= 0)
			{
				$APPLICATION->ThrowException(
					Main\Localization\Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT')
				);
				return $result;
			}

			$module = 'catalog';
			if (array_key_exists('MODULE', $rewriteFields))
			{
				$module = $rewriteFields['MODULE'];
			}

			$siteId = SITE_ID;
			if (!empty($rewriteFields['LID']))
			{
				$siteId = $rewriteFields['LID'];
			}
			/** @var Sale\Basket $basketClassName */
			$basketClassName = $registry->getBasketClassName();

			$basket = $basketClassName::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), $siteId);

			$propertyList = array();
			if (!empty($product['PROPS']) && is_array($product['PROPS']))
			{
				$propertyList = $product['PROPS'];
			}

			$basketItems = $basket->getExistsItems($module, $productId, $propertyList);
			foreach ($basketItems as $basketItem)
			{
				$basketItem->setFieldNoDemand('ORDER_ID', intval($rewriteFields['ORDER_ID']));
			}
			
			if ($basketItems)
			{
				$r = $basket->save();

				/** @var Sale\Order $orderClass */
				$orderClass = $registry->getOrderClassName();

				$orderId = intval($rewriteFields['ORDER_ID']);
				$order = $orderClass::load($orderId);
				if ($order)
				{
					$basket = $order->getBasket();
					$basket->refresh();
					$r = $order->save();
					if (!$r->isSuccess())
					{
						$APPLICATION->ThrowException(
							implode('; ', $r->getErrorMessages())
						);
					}
				}
			}
		}

	}
	else
	{
		$APPLICATION->ThrowException(
			implode('; ', $basketResult->getErrorMessages())
		);
	}
	unset($product);
	unset($basketResult);

	return $result;
}

/**
 * @param int $intProductID
 * @param array $arRewriteFields
 * @param array $arProductParams
 * @return bool|int
 */
function SubscribeProduct($intProductID, $arRewriteFields = array(), $arProductParams = array())
{
	global $USER, $APPLICATION;

	if (!CCatalog::IsUserExists())
		return false;
	if (!$USER->IsAuthorized())
		return false;
	$intUserID = (int)$USER->GetID();

	$intProductID = (int)$intProductID;
	if ($intProductID <= 0)
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_EMPTY_PRODUCT_ID'), "EMPTY_PRODUCT_ID");
		return false;
	}

	if (!Loader::includeModule("sale"))
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_SALE_MODULE'), "NO_SALE_MODULE");
		return false;
	}

	if (!Catalog\Product\Basket::isNotCrawler())
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_SESS_SEARCHER'));
		return false;
	}

	$rsProducts = CCatalogProduct::GetList(
		array(),
		array('ID' => $intProductID),
		false,
		false,
		array(
			'ID',
			'WEIGHT',
			'WIDTH',
			'HEIGHT',
			'LENGTH',
			'TYPE',
			'MEASURE',
			'SUBSCRIBE'
		)
	);
	if (!($arCatalogProduct = $rsProducts->Fetch()))
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_PRODUCT'), "NO_PRODUCT");
		return false;
	}

	if ($arCatalogProduct['SUBSCRIBE'] == 'N')
	{
		$APPLICATION->ThrowException(Loc::getMessage('CATALOG_ERR_NO_SUBSCRIBE'), 'SUBSCRIBE');
		return false;
	}
	$arCatalogProduct['MEASURE'] = (int)$arCatalogProduct['MEASURE'];
	$arCatalogProduct['MEASURE_NAME'] = '';
	$arCatalogProduct['MEASURE_CODE'] = 0;
	if ($arCatalogProduct['MEASURE'] <= 0)
	{
		$arMeasure = CCatalogMeasure::getDefaultMeasure(true, true);
		$arCatalogProduct['MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
		$arCatalogProduct['MEASURE_CODE'] = $arMeasure['CODE'];
	}
	else
	{
		$rsMeasures = CCatalogMeasure::getList(
			array(),
			array('ID' => $arCatalogProduct['MEASURE']),
			false,
			false,
			array('ID', 'SYMBOL_RUS', 'CODE')
		);
		if ($arMeasure = $rsMeasures->GetNext())
		{
			$arCatalogProduct['MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
			$arCatalogProduct['MEASURE_CODE'] = $arMeasure['CODE'];
		}
	}

	$rsItems = CIBlockElement::GetList(
		array(),
		array(
			"ID" => $intProductID,
			"ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => "R"
		),
		false,
		false,
		array(
			'ID',
			'IBLOCK_ID',
			'NAME',
			'XML_ID',
			'DETAIL_PAGE_URL'
		)
	);
	if (!($arProduct = $rsItems->GetNext()))
		return false;

	$arParentSku = CCatalogSku::GetProductInfo($intProductID, $arProduct['IBLOCK_ID']);
	if (!empty($arParentSku))
	{
		if (mb_strpos($arProduct["~XML_ID"], '#') === false)
		{
			$parentIterator = Iblock\ElementTable::getList(array(
				'select' => array('ID', 'XML_ID'),
				'filter' => array('ID' => $arParentSku['ID'])
			));
			if ($parent = $parentIterator->fetch())
			{
				$arProduct["~XML_ID"] = $parent['XML_ID'].'#'.$arProduct["~XML_ID"];
			}
			unset($parent, $parentIterator);
		}
	}

	$arPrice = array(
		'BASE_PRICE' => 0,
		'PRICE' => 0.0,
		'DISCOUNT_PRICE' => 0,
		'CURRENCY' => CSaleLang::GetLangCurrency(SITE_ID),
		'VAT_RATE' => 0,
		'PRODUCT_PRICE_ID' => 0,
		'CATALOG_GROUP_NAME' => '',
	);
	$arBuyerGroups = $USER->GetUserGroupArray();
	$arSubscrPrice = CCatalogProduct::GetOptimalPrice($intProductID, 1, $arBuyerGroups, "N", array(), SITE_ID, array());
	if (!empty($arSubscrPrice) && is_array($arSubscrPrice))
	{
		$arPrice['BASE_PRICE'] = $arSubscrPrice['RESULT_PRICE']['BASE_PRICE'];
		$arPrice['PRICE'] = $arSubscrPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
		$arPrice['DISCOUNT_PRICE'] = $arSubscrPrice['RESULT_PRICE']['DISCOUNT'];
		$arPrice['CURRENCY'] = $arSubscrPrice['RESULT_PRICE']['CURRENCY'];
		$arPrice['VAT_RATE'] = $arSubscrPrice['RESULT_PRICE']['VAT_RATE'];
		$arPrice['PRODUCT_PRICE_ID'] = $arSubscrPrice['PRICE']['ID'];
		$arPrice['CATALOG_GROUP_NAME'] = $arSubscrPrice['PRICE']['CATALOG_GROUP_NAME'];
	}

	$arProps = array();

	$strIBlockXmlID = (string)CIBlock::GetArrayByID($arProduct['IBLOCK_ID'], 'XML_ID');
	if ($strIBlockXmlID !== '')
	{
		$arProps[] = array(
			"NAME" => "Catalog XML_ID",
			"CODE" => "CATALOG.XML_ID",
			"VALUE" => $strIBlockXmlID
		);
	}

	if (!empty($arProductParams) && is_array($arProductParams))
	{
		foreach ($arProductParams as &$arOneProductParams)
		{
			$arProps[] = array(
				"NAME" => $arOneProductParams["NAME"],
				"CODE" => $arOneProductParams["CODE"],
				"VALUE" => $arOneProductParams["VALUE"],
				"SORT" => $arOneProductParams["SORT"],
			);
		}
		unset($arOneProductParams);
	}

	$arProps[] = array(
		"NAME" => "Product XML_ID",
		"CODE" => "PRODUCT.XML_ID",
		"VALUE" => $arProduct["XML_ID"]
	);

	$arFields = array(
		"PRODUCT_ID" => $intProductID,
		"PRODUCT_PRICE_ID" => $arPrice['PRODUCT_PRICE_ID'],
		"BASE_PRICE" => $arPrice['BASE_PRICE'],
		"PRICE" => $arPrice['PRICE'],
		"DISCOUNT_PRICE" => $arPrice['DISCOUNT_PRICE'],
		"CURRENCY" => $arPrice['CURRENCY'],
		"VAT_RATE" => $arPrice['VAT_RATE'],
		"WEIGHT" => $arCatalogProduct["WEIGHT"],
		"DIMENSIONS" => serialize(array(
			"WIDTH" => $arCatalogProduct["WIDTH"],
			"HEIGHT" => $arCatalogProduct["HEIGHT"],
			"LENGTH" => $arCatalogProduct["LENGTH"]
		)),
		"QUANTITY" => 1,
		"LID" => SITE_ID,
		"DELAY" => "N",
		"CAN_BUY" => "N",
		"SUBSCRIBE" => "Y",
		"NAME" => $arProduct["~NAME"],
		"MODULE" => "catalog",
		"PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
		"NOTES" => $arPrice["CATALOG_GROUP_NAME"],
		"DETAIL_PAGE_URL" => $arProduct["~DETAIL_PAGE_URL"],
		"CATALOG_XML_ID" => $strIBlockXmlID,
		"PRODUCT_XML_ID" => $arProduct["~XML_ID"],
		"PROPS" => $arProps,
		"TYPE" => ($arCatalogProduct["TYPE"] == CCatalogProduct::TYPE_SET) ? CCatalogProductSet::TYPE_SET : NULL,
		"MEASURE_NAME" => $arCatalogProduct['MEASURE_NAME'],
		"MEASURE_CODE" => $arCatalogProduct['MEASURE_CODE'],
		'IGNORE_CALLBACK_FUNC' => 'Y'
	);

	if (!empty($arRewriteFields) && is_array($arRewriteFields))
	{
		if (array_key_exists('SUBSCRIBE', $arRewriteFields))
			unset($arRewriteFields['SUBSCRIBE']);
		if (array_key_exists('CAN_BUY', $arRewriteFields))
			unset($arRewriteFields['CAN_BUY']);
		if (array_key_exists('DELAY', $arRewriteFields))
			unset($arRewriteFields['DELAY']);
		if (!empty($arRewriteFields))
			$arFields = array_merge($arFields, $arRewriteFields);
	}

	$mxBasketID = CSaleBasket::Add($arFields);
	if ($mxBasketID)
	{
		if (!isset($_SESSION['NOTIFY_PRODUCT']))
		{
			$_SESSION['NOTIFY_PRODUCT'] = array(
				$intUserID = array(),
			);
		}
		elseif (!isset($_SESSION['NOTIFY_PRODUCT'][$intUserID]))
		{
			$_SESSION['NOTIFY_PRODUCT'][$intUserID] = array();
		}
		$_SESSION["NOTIFY_PRODUCT"][$intUserID][$intProductID] = $intProductID;

		if (Loader::includeModule("statistic"))
			CStatistic::Set_Event("sale2basket", "subscribe", $intProductID);
	}
	return $mxBasketID;
}

/**
 * @deprecated deprecated since catalog 17.0.0
 *
 * @param $ID
 * @param int|float $filterQauntity
 * @param array $arFilterType
 * @param string $VAT_INCLUDE
 * @param array $arCurrencyParams
 * @return array|bool
 */
function CatalogGetPriceTableEx($ID, $filterQauntity = 0, $arFilterType = array(), $VAT_INCLUDE = 'Y', $arCurrencyParams = array())
{
	global $USER;

	static $arPriceTypes = array();

	$ID = (int)$ID;
	if ($ID <= 0)
		return false;

	$filterQauntity = (int)$filterQauntity;

	if (!is_array($arFilterType))
		$arFilterType = array($arFilterType);

	$boolConvert = false;
	$strCurrencyID = '';
	$arCurrencyList = array();
	if (!empty($arCurrencyParams) && is_array($arCurrencyParams) && !empty($arCurrencyParams['CURRENCY_ID']))
	{
		$boolConvert = true;
		$strCurrencyID = $arCurrencyParams['CURRENCY_ID'];
	}

	$arResult = array();
	$arResult["ROWS"] = array();
	$arResult["COLS"] = array();
	$arResult["MATRIX"] = array();
	$arResult["CAN_BUY"] = array();
	$arResult["AVAILABLE"] = "N";

	$arUserGroups = $USER->GetUserGroupArray();
	Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups, true);
	$strCacheID = 'UG_'.implode('_', $arUserGroups);

	if (isset($arPriceTypes[$strCacheID]))
	{
		$arPriceGroups = $arPriceTypes[$strCacheID];
	}
	else
	{
		$arPriceGroups = CCatalogGroup::GetGroupsPerms($arUserGroups, array());
		$arPriceTypes[$strCacheID] = $arPriceGroups;
	}

	if (empty($arPriceGroups["view"]))
		return $arResult;

	$currentQuantity = -1;
	$rowsCnt = -1;

	$arFilter = array("PRODUCT_ID" => $ID);
	if ($filterQauntity > 0)
	{
		$arFilter["+<=QUANTITY_FROM"] = $filterQauntity;
		$arFilter["+>=QUANTITY_TO"] = $filterQauntity;
	}
	if (!empty($arFilterType))
	{
		$arTmp = array();
		foreach ($arPriceGroups["view"] as &$intOneGroup)
		{
			if (in_array($intOneGroup, $arFilterType))
				$arTmp[] = $intOneGroup;
		}
		if (isset($intOneGroup))
			unset($intOneGroup);

		if (empty($arTmp))
			return $arResult;

		$arFilter["CATALOG_GROUP_ID"] = $arTmp;
	}
	else
	{
		$arFilter["CATALOG_GROUP_ID"] = $arPriceGroups["view"];
	}

	$dbRes = CCatalogProduct::GetVATInfo($ID);
	if ($arVatInfo = $dbRes->Fetch())
	{
		$fVatRate = floatval($arVatInfo['RATE'] * 0.01);
		$bVatIncluded = $arVatInfo['VAT_INCLUDED'] == 'Y';
	}
	else
	{
		$fVatRate = 0.00;
		$bVatIncluded = false;
	}

	$rsProducts = CCatalogProduct::GetList(
		array(),
		array('ID' => $ID),
		false,
		false,
		array(
			'ID',
			'CAN_BUY_ZERO',
			'QUANTITY_TRACE',
			'QUANTITY'
		)
	);
	if ($arProduct = $rsProducts->Fetch())
	{
		$intIBlockID = CIBlockElement::GetIBlockByID($arProduct['ID']);
		if (!$intIBlockID)
			return false;
		$arProduct['IBLOCK_ID'] = $intIBlockID;
	}
	else
	{
		return false;
	}

	$dbPrice = CPrice::GetListEx(
		array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
		$arFilter,
		false,
		false,
		array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO")
	);

	while ($arPrice = $dbPrice->Fetch())
	{
		if ($VAT_INCLUDE == 'N')
		{
			if ($bVatIncluded)
				$arPrice['PRICE'] /= (1 + $fVatRate);
		}
		else
		{
			if (!$bVatIncluded)
				$arPrice['PRICE'] *= (1 + $fVatRate);
		}
		$arPrice['CATALOG_GROUP_ID'] = (int)$arPrice['CATALOG_GROUP_ID'];

		$arPrice['VAT_RATE'] = $fVatRate;

		CCatalogDiscountSave::Disable();
		$arDiscounts = CCatalogDiscount::GetDiscount($ID, $arProduct["IBLOCK_ID"], array($arPrice["CATALOG_GROUP_ID"]), $arUserGroups, "N", SITE_ID, array());
		CCatalogDiscountSave::Enable();

		$discountPrice = CCatalogProduct::CountPriceWithDiscount($arPrice["PRICE"], $arPrice["CURRENCY"], $arDiscounts);
		$arPrice["DISCOUNT_PRICE"] = $discountPrice;

		$arPrice["QUANTITY_FROM"] = doubleval($arPrice["QUANTITY_FROM"]);
		if ($currentQuantity != $arPrice["QUANTITY_FROM"])
		{
			$rowsCnt++;
			$arResult["ROWS"][$rowsCnt]["QUANTITY_FROM"] = $arPrice["QUANTITY_FROM"];
			$arResult["ROWS"][$rowsCnt]["QUANTITY_TO"] = doubleval($arPrice["QUANTITY_TO"]);
			$currentQuantity = $arPrice["QUANTITY_FROM"];
		}

		if ($boolConvert && $strCurrencyID != $arPrice["CURRENCY"])
		{
			$price = CCurrencyRates::ConvertCurrency($arPrice["PRICE"], $arPrice["CURRENCY"], $strCurrencyID);
			$unroundDiscountPrice = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT_PRICE"], $arPrice["CURRENCY"], $strCurrencyID);
			$discountPrice = Catalog\Product\Price::roundPrice(
				$arPrice["CATALOG_GROUP_ID"],
				$unroundDiscountPrice,
				$strCurrencyID
			);
			if ($discountPrice > $price)
				$price = $discountPrice;
			$arResult["MATRIX"][$arPrice["CATALOG_GROUP_ID"]][$rowsCnt] = array(
				"ID" => $arPrice["ID"],
				"ORIG_PRICE" => $arPrice["PRICE"],
				"ORIG_DISCOUNT_PRICE" => $arPrice["DISCOUNT_PRICE"],
				"ORIG_CURRENCY" => $arPrice["CURRENCY"],
				"ORIG_VAT_RATE" => $arPrice["VAT_RATE"],
				'PRICE' => $price,
				'DISCOUNT_PRICE' => $discountPrice,
				'UNROUND_DISCOUNT_PRICE' => $unroundDiscountPrice,
				'CURRENCY' => $strCurrencyID,
				'VAT_RATE' => $arPrice["VAT_RATE"],
			);
			$arCurrencyList[$arPrice['CURRENCY']] = $arPrice['CURRENCY'];
		}
		else
		{
			$arPrice['UNROUND_DISCOUNT_PRICE'] = $arPrice['DISCOUNT_PRICE'];
			$arPrice['DISCOUNT_PRICE'] = Catalog\Product\Price::roundPrice(
				$arPrice["CATALOG_GROUP_ID"],
				$arPrice['DISCOUNT_PRICE'],
				$arPrice["CURRENCY"]
			);
			if ($arPrice["DISCOUNT_PRICE"] > $arPrice["PRICE"])
				$arPrice["PRICE"] = $arPrice["DISCOUNT_PRICE"];
			$arResult["MATRIX"][$arPrice["CATALOG_GROUP_ID"]][$rowsCnt] = array(
				"ID" => $arPrice["ID"],
				"PRICE" => $arPrice["PRICE"],
				"DISCOUNT_PRICE" => $arPrice["DISCOUNT_PRICE"],
				"UNROUND_DISCOUNT_PRICE" => $arPrice['UNROUND_DISCOUNT_PRICE'],
				"CURRENCY" => $arPrice["CURRENCY"],
				"VAT_RATE" => $arPrice["VAT_RATE"]
			);
		}
	}

	$arCatalogGroups = CCatalogGroup::GetListArray();
	foreach ($arCatalogGroups as $key => $value)
	{
		if (isset($arResult["MATRIX"][$key]))
			$arResult["COLS"][$value["ID"]] = $value;
	}

	$arResult["CAN_BUY"] = $arPriceGroups["buy"];
	$arResult["AVAILABLE"] = (0 >= $arProduct['QUANTITY'] && 'Y' == $arProduct['QUANTITY_TRACE'] && 'N' == $arProduct['CAN_BUY_ZERO'] ? 'N' : 'Y');

	if ($boolConvert)
	{
		if (!empty($arCurrencyList))
			$arCurrencyList[$strCurrencyID] = $strCurrencyID;
		$arResult['CURRENCY_LIST'] = $arCurrencyList;
	}

	return $arResult;
}

function CatalogGetPriceTable($ID)
{
	global $USER;

	$ID = (int)$ID;
	if ($ID <= 0)
		return false;

	$arResult = array();

	$arPriceGroups = array();
	$cacheKey = LANGUAGE_ID."_".($USER->GetGroups());
	if (isset($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"])
		&& is_array($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"])
		&& isset($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey])
		&& is_array($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey]))
	{
		$arPriceGroups = $GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey];
	}
	else
	{
		$dbPriceGroupsList = CCatalogGroup::GetList(
			array("SORT" => "ASC"),
			array(
				"CAN_ACCESS" => "Y",
				"LID" => LANGUAGE_ID
			),
			array("ID", "NAME_LANG", "SORT"),
			false,
			array("ID", "NAME_LANG", "CAN_BUY", "SORT")
		);
		while ($arPriceGroupsList = $dbPriceGroupsList->Fetch())
		{
			$arPriceGroups[] = $arPriceGroupsList;
			$GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey][] = $arPriceGroupsList;
		}
	}

	if (empty($arPriceGroups))
		return false;

	$arBorderMap = array();
	$bMultiQuantity = False;

	$dbPrice = CPrice::GetList(
		array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC", "SORT" => "ASC"),
		array("PRODUCT_ID" => $ID),
		false,
		false,
		array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "ELEMENT_IBLOCK_ID", "SORT")
	);
	while ($arPrice = $dbPrice->Fetch())
	{
		CCatalogDiscountSave::Disable();
		$arDiscounts = CCatalogDiscount::GetDiscount($ID, $arPrice["ELEMENT_IBLOCK_ID"], array($arPrice["CATALOG_GROUP_ID"]), $USER->GetUserGroupArray(), "N", SITE_ID, array());
		CCatalogDiscountSave::Enable();

		$discountPrice = CCatalogProduct::CountPriceWithDiscount($arPrice["PRICE"], $arPrice["CURRENCY"], $arDiscounts);
		$arPrice["DISCOUNT_PRICE"] = $discountPrice;

		if (array_key_exists($arPrice["QUANTITY_FROM"]."-".$arPrice["QUANTITY_TO"], $arBorderMap))
			$jnd = $arBorderMap[$arPrice["QUANTITY_FROM"]."-".$arPrice["QUANTITY_TO"]];
		else
		{
			$jnd = count($arBorderMap);
			$arBorderMap[$arPrice["QUANTITY_FROM"]."-".$arPrice["QUANTITY_TO"]] = $jnd;
		}

		$arResult[$jnd]["QUANTITY_FROM"] = (float)$arPrice["QUANTITY_FROM"];
		$arResult[$jnd]["QUANTITY_TO"] = (float)$arPrice["QUANTITY_TO"];
		if ((float)$arPrice["QUANTITY_FROM"] > 0 || (float)$arPrice["QUANTITY_TO"] > 0)
			$bMultiQuantity = true;

		$arResult[$jnd]["PRICE"][$arPrice["CATALOG_GROUP_ID"]] = $arPrice;
	}

	$numGroups = count($arPriceGroups);
	for ($i = 0; $i < $numGroups; $i++)
	{
		$bNeedKill = true;
		for ($j = 0, $intCount = count($arResult); $j < $intCount; $j++)
		{
			if (!array_key_exists($arPriceGroups[$i]["ID"], $arResult[$j]["PRICE"]))
				$arResult[$j]["PRICE"][$arPriceGroups[$i]["ID"]] = False;

			if ($arResult[$j]["PRICE"][$arPriceGroups[$i]["ID"]] != false)
				$bNeedKill = False;
		}

		if ($bNeedKill)
		{
			for ($j = 0, $intCount = count($arResult); $j < $intCount; $j++)
				unset($arResult[$j]["PRICE"][$arPriceGroups[$i]["ID"]]);

			unset($arPriceGroups[$i]);
		}
	}

	return array(
		"COLS" => $arPriceGroups,
		"MATRIX" => $arResult,
		"MULTI_QUANTITY" => ($bMultiQuantity ? "Y" : "N")
	);
}

function __CatalogGetMicroTime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function __CatalogSetTimeMark($text, $startStop = "")
{
	global $__catalogTimeMarkTo, $__catalogTimeMarkFrom, $__catalogTimeMarkGlobalFrom;

	if (mb_strtoupper($startStop) == "START")
	{
		$hFile = fopen($_SERVER["DOCUMENT_ROOT"]."/__catalog_debug.txt", "a");
		fwrite($hFile, date("H:i:s")." - ".$text."\n");
		fclose($hFile);

		$__catalogTimeMarkGlobalFrom = __CatalogGetMicroTime();
		$__catalogTimeMarkFrom = __CatalogGetMicroTime();
	}
	elseif (mb_strtoupper($startStop) == "STOP")
	{
		$__catalogTimeMarkTo = __CatalogGetMicroTime();

		$hFile = fopen($_SERVER["DOCUMENT_ROOT"]."/__catalog_debug.txt", "a");
		fwrite($hFile, date("H:i:s")." - ".round($__catalogTimeMarkTo - $__catalogTimeMarkFrom, 3)." s - ".$text."\n");
		fwrite($hFile, date("H:i:s")." - ".round($__catalogTimeMarkTo - $__catalogTimeMarkGlobalFrom, 3)." s\n\n");
		fclose($hFile);
	}
	else
	{
		$__catalogTimeMarkTo = __CatalogGetMicroTime();

		$hFile = fopen($_SERVER["DOCUMENT_ROOT"]."/__catalog_debug.txt", "a");
		fwrite($hFile, date("H:i:s")." - ".round($__catalogTimeMarkTo - $__catalogTimeMarkFrom, 3)." s - ".$text."\n");
		fclose($hFile);

		$__catalogTimeMarkFrom = __CatalogGetMicroTime();
	}
}

function CatalogGetVATArray($arFilter = array(), $bInsertEmptyLine = false)
{
	$bInsertEmptyLine = ($bInsertEmptyLine === true);

	if (!is_array($arFilter))
		$arFilter = array();

	$arFilter['ACTIVE'] = 'Y';
	$dbResult = CCatalogVat::GetListEx(array(), $arFilter, false, false, array('ID', 'NAME'));

	if ($bInsertEmptyLine)
		$arList = array('REFERENCE' => array(0 => Loc::getMessage('CAT_VAT_REF_NOT_SELECTED')), 'REFERENCE_ID' => array(0 => ''));
	else
		$arList = array('REFERENCE' => array(), 'REFERENCE_ID' => array());

	$bEmpty = true;
	while ($arRes = $dbResult->Fetch())
	{
		$bEmpty = false;
		$arList['REFERENCE'][] = $arRes['NAME'];
		$arList['REFERENCE_ID'][] = $arRes['ID'];
	}

	if ($bEmpty && !$bInsertEmptyLine)
		return false;
	else
		return $arList;
}

function CurrencyModuleUnInstallCatalog()
{
	global $APPLICATION;
	$APPLICATION->ThrowException(Loc::getMessage("CAT_INCLUDE_CURRENCY"), "CAT_DEPENDS_CURRENCY");
	return false;
}

function CatalogGenerateCoupon()
{
	foreach (GetModuleEvents("catalog", "OnGenerateCoupon", true) as $arEvent)
	{
		return ExecuteModuleEventEx($arEvent);
	}

	$allchars = 'ABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789';
	$charsLen = mb_strlen($allchars) - 1;
	$string1 = '';
	$string2 = '';
	for ($i = 0; $i < 5; $i++)
		$string1 .= mb_substr($allchars, rand(0, $charsLen), 1);

	for ($i = 0; $i < 7; $i++)
		$string2 .= mb_substr($allchars, rand(0, $charsLen), 1);

	return 'CP-'.$string1.'-'.$string2;
}

function __GetCatLangMessages($strBefore, $strAfter, $MessID, $strDefMess = false, $arLangList = array())
{
	$arResult = false;

	if (empty($MessID))
		return $arResult;
	if (!is_array($MessID))
		$MessID = array($MessID);
	if (!is_array($arLangList))
		$arLangList = array($arLangList);

	if (empty($arLangList))
	{
		$rsLangs = CLanguage::GetList("lid", "asc", array("ACTIVE" => "Y"));
		while ($arLang = $rsLangs->Fetch())
		{
			$arLangList[] = $arLang['LID'];
		}
	}
	foreach ($arLangList as &$strLID)
	{
		$arMess = IncludeModuleLangFile(str_replace('//', '/', $strBefore.$strAfter), $strLID, true);
		if (!empty($arMess))
		{
			foreach ($MessID as &$strMessID)
			{
				if (empty($strMessID))
					continue;
				$arResult[$strMessID][$strLID] = $arMess[$strMessID] ?? $strDefMess;
			}
			if (isset($strMessID))
				unset($strMessID);
		}
	}
	if (isset($strLID))
		unset($strLID);
	return $arResult;
}

/**
 * @deprecated deprecated since catalog 16.0.0
 * @see \Bitrix\Main\Type\Collection::normalizeArrayValuesByInt
 *
 * @param array &$arMap
 * @param bool $boolSort
 * @return void
 */
function CatalogClearArray(&$arMap, $boolSort = true)
{
	Main\Type\Collection::normalizeArrayValuesByInt($arMap, $boolSort);
}
