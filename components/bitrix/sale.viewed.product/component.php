<?
/** @global CMain $APPLICATION
 * @global CUser $USER
 * @global array $arParams */
use Bitrix\Main\Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["VIEWED_COUNT"] = IntVal($arParams["VIEWED_COUNT"]);
if ($arParams["VIEWED_COUNT"] <= 0)
	$arParams["VIEWED_COUNT"] = 5;
$arParams["VIEWED_IMG_HEIGHT"] = IntVal($arParams["VIEWED_IMG_HEIGHT"]);
if($arParams["VIEWED_IMG_HEIGHT"] <= 0)
	$arParams["VIEWED_IMG_HEIGHT"] = 150;
$arParams["VIEWED_IMG_WIDTH"] = IntVal($arParams["VIEWED_IMG_WIDTH"]);
if ($arParams["VIEWED_IMG_WIDTH"] <= 0)
	$arParams["VIEWED_IMG_WIDTH"] = 150;

if($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("VIEW_TITLE"));

$arParams["VIEWED_NAME"] = (($arParams["VIEWED_NAME"] == "Y") ? "Y" : "N");
$arParams["VIEWED_IMAGE"] = (($arParams["VIEWED_IMAGE"] == "Y") ? "Y" : "N");
$arParams["VIEWED_PRICE"] = (($arParams["VIEWED_PRICE"] == "Y") ? "Y" : "N");

if (!isset($arParams["VIEWED_CURRENCY"]) || strlen($arParams["VIEWED_CURRENCY"]) <= 0)
	$arParams["VIEWED_CURRENCY"] = "default";

$arResult = array();
$arFilter = array();

if (!Loader::includeModule('sale'))
{
	ShowError(GetMessage("VIEWE_NOT_INSTALL"));
	return;
}

if (!Loader::includeModule('catalog'))
{
	ShowError(GetMessage("VIEWCATALOG_NOT_INSTALL"));
	return;
}

$fuserId = (int)CSaleBasket::GetBasketUserID(true);
$newUser = $fuserId <= 0;

if (!$newUser)
{
	$arFilter = array(
		'LID' => SITE_ID,
		'FUSER_ID' => $fuserId
	);
}
unset($fuserId);

//add to basket
if (isset($_REQUEST[$arParams["ACTION_VARIABLE"]]) && isset($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]))
{
	if("BUY" ==  $_REQUEST[$arParams["ACTION_VARIABLE"]])
		$action = "BUY";
	elseif("ADD2BASKET" == $_REQUEST[$arParams["ACTION_VARIABLE"]])
		$action = "ADD2BASKET";
	else
		$action = ToUpper($_REQUEST[$arParams["ACTION_VARIABLE"]]);

	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if ($productID > 0)
	{
		//get props sku
		$product_properties = array();
		$arPropsSku = array();

		$arParentSku = CCatalogSku::GetProductInfo($productID);
		if (!empty($arParentSku) && is_array($arParentSku))
		{
			$dbProduct = CIBlockElement::GetList(array(), array("ID" => $productID), false, false, array('IBLOCK_ID', 'IBLOCK_SECTION_ID'));
			$arProduct = $dbProduct->Fetch();

			$dbOfferProperties = CIBlock::GetProperties($arProduct["IBLOCK_ID"], array(), array("!XML_ID" => "CML2_LINK"));
			while($arOfferProperties = $dbOfferProperties->Fetch())
				$arPropsSku[] = $arOfferProperties["CODE"];

			$product_properties = CIBlockPriceTools::GetOfferProperties(
							$productID,
							$arParentSku["IBLOCK_ID"],
							$arPropsSku
						);
		}

		if (($action == "ADD2BASKET" || $action == "BUY") && $productID > 0)
		{
			Add2BasketByProductID($productID, 1, array(), $product_properties);

			if ($action == "BUY")
				LocalRedirect($arParams["BASKET_URL"]);
			else
				LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		}
	}
}//end add to basket

$arViewed = array();
$arViewedId = array();
$arElementSort = array();
if (!$newUser)
{
	$db_res = CSaleViewedProduct::GetList(
			array(
				"DATE_VISIT" => "DESC"
			),
			$arFilter,
			false,
			array(
				"nTopCount" => $arParams["VIEWED_COUNT"]
			),
			array('ID', 'IBLOCK_ID', 'PRICE', 'CURRENCY', 'CAN_BUY', 'PRODUCT_ID', 'DATE_VISIT', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PREVIEW_PICTURE', 'NAME', 'NOTES')
	);
	while ($arItems = $db_res->Fetch())
	{
		$arViewedId[] = $arItems["PRODUCT_ID"];
		$arViewed[$arItems["PRODUCT_ID"]] = $arItems;
	}
}
//check catalog
if (!empty($arViewedId))
{
	$arIBlockSectionID = array();

	$res = CIBlockElement::GetList(
		array(),
		array("ID" => $arViewedId),
		false,
		false,
		array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_TYPE_ID",
			"IBLOCK_CODE",
			"IBLOCK_EXTERNAL_ID",
			"IBLOCK_SECTION_ID",
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"DETAIL_PAGE_URL",
			"CODE",
			"XML_ID",
			"SECTION_CODE",
			"EXTERNAL_ID",
			"SITE_DIR"
		)
	);
	while ($arElement = $res->GetNext())
	{
		$arElement["DATE_VISIT"] = $arViewed[$arElement["ID"]]["DATE_VISIT"];
		$arElement["~DATE_VISIT"] = MakeTimeStamp($arElement["DATE_VISIT"]);
		$arElement["ELEMENT_CODE"] = $arElement["CODE"];
		$arElement["ELEMENT_ID"] = $arElement["ID"];
		$arElement["SECTION_ID"] = $arElement["IBLOCK_SECTION_ID"];

		$arElementSort[] = $arElement;
		$arIBlockSectionID[] = $arElement["IBLOCK_SECTION_ID"];
	}

	// get additional info for updated detail URLs
	$dbSectionRes = CIBlockSection::GetList(array(), array("ID" => array_unique($arIBlockSectionID)), false, array("ID", "CODE"));
	while ($arSectionRes = $dbSectionRes->GetNext())
	{
		foreach ($arElementSort as &$arElement)
		{
			if ($arElement["IBLOCK_SECTION_ID"] == $arSectionRes["ID"])
				$arElement["SECTION_CODE"] = $arSectionRes["CODE"];
		}
		unset($arElement);
	}

	sortByColumn($arElementSort, array("~DATE_VISIT" => SORT_DESC));

	$currency = CSaleLang::GetLangCurrency(SITE_ID);

	foreach ($arElementSort as $arElements)
	{
		static $arCacheOffersIblock = array();
		$priceMin = 0;
		$arItems = $arViewed[$arElements["ID"]];
		$arItems["IBLOCK_ID"] = $arElements["IBLOCK_ID"];
		$arItems["DETAIL_PICTURE"] = $arElements["DETAIL_PICTURE"];
		$arItems["PREVIEW_PICTURE"] = $arElements["PREVIEW_PICTURE"];

		$arElements["DETAIL_PAGE_URL"] = CIBlock::ReplaceDetailUrl($arElements["DETAIL_PAGE_URL"], $arElements, false);

		$arItems["DETAIL_PAGE_URL"] = $arElements["DETAIL_PAGE_URL"];
		$arItems["BUY_URL"] = htmlspecialcharsex($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItems["PRODUCT_ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItems["ADD_URL"] = htmlspecialcharsex($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItems["PRODUCT_ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));

		if (!is_set($arCacheOffersIblock[$arElements["IBLOCK_ID"]]))
		{
			$mxResult = CCatalogSKU::GetInfoByProductIBlock($arElements["IBLOCK_ID"]);
			if (is_array($mxResult))
			{
				$arOffersIblock["OFFERS_IBLOCK_ID"] = $mxResult["IBLOCK_ID"];
				$arCacheOffersIblock[$arElements["IBLOCK_ID"]] = $arOffersIblock;
			}
		}
		else
			$arOffersIblock = $arCacheOffersIblock[$arElements["IBLOCK_ID"]];

		if (isset($arOffersIblock["OFFERS_IBLOCK_ID"]) && $arOffersIblock["OFFERS_IBLOCK_ID"] > 0)
		{
			$arItems["OFFERS"] = array();

			static $arCacheOfferProperties = array();
			if (!isset($arCacheOfferProperties[$arOffersIblock["OFFERS_IBLOCK_ID"]]))
			{
				$dbOfferProperties = CIBlock::GetProperties($arOffersIblock["OFFERS_IBLOCK_ID"], array(), array("!XML_ID" => "CML2_LINK"));
				while($arOfferProperties = $dbOfferProperties->Fetch())
					$arCacheOfferProperties[$arOffersIblock["OFFERS_IBLOCK_ID"]][] = $arOfferProperties["CODE"];
			}
			$arIblockOfferPropsFilter = $arCacheOfferProperties[$arOffersIblock["OFFERS_IBLOCK_ID"]];

			static $arCacheResultPrices = array();
			if (empty($arCacheResultPrices))
			{
				$arPriceTypeList = array();
				$dbPriceType = CCatalogGroup::GetList(array(),array('CAN_BUY' => 'Y'),false,false,array('NAME', 'ID'));
				while ($arPriceType = $dbPriceType->Fetch())
				{
					$arPriceTypeList[] = $arPriceType["NAME"];
				}
				$arResultPrices = CIBlockPriceTools::GetCatalogPrices($arElements["IBLOCK_ID"], $arPriceTypeList);
				$arCacheResultPrices = $arResultPrices;
			}
			else
				$arResultPrices = $arCacheResultPrices;

			$arOffers = CIBlockPriceTools::GetOffersArray(
						$arElements["IBLOCK_ID"],
						$arItems["PRODUCT_ID"],
						array("ID" => "DESC"),
						array("NAME"),
						$arIblockOfferPropsFilter,
						0,
						$arResultPrices,
						true
			);
			if (!empty($arOffers) && is_array($arOffers))
			{
				$result = false;
				$minPrice = 0;
				$urlAdd2Basket = '';
				$urlBuy = '';
				foreach ($arOffers as $oneOffer)
				{
					if ($oneOffer['LINK_ELEMENT_ID'] != $arItems['PRODUCT_ID'])
						continue;

					if (!$oneOffer['CAN_BUY'])
						continue;

					$oneOffer["BUY_URL"] = htmlspecialcharsBX($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$oneOffer["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
					$oneOffer["ADD_URL"] = htmlspecialcharsBX($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$oneOffer["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));

					if (empty($result))
					{
						$minPrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
							? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
							: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
						);
						$result = $oneOffer['MIN_PRICE'];
						$urlAdd2Basket = $oneOffer["ADD_URL"];
						$urlBuy = $oneOffer["BUY_URL"];
					}
					else
					{
						$comparePrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
							? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
							: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
						);
						if ($minPrice > $comparePrice)
						{
							$minPrice = $comparePrice;
							$result = $oneOffer['MIN_PRICE'];
							$urlAdd2Basket = $oneOffer["ADD_URL"];
							$urlBuy = $oneOffer["BUY_URL"];
						}
					}

					$arItems["OFFERS"][] = $oneOffer;
				}

				if (!empty($result))
				{
					$arItems["PRICE"] = $result['DISCOUNT_VALUE'];
					$arItems["CURRENCY"] = $result['CURRENCY'];
					$arItems["MIN_PRICE_SET"] = true;
					$arItems["BUY_URL"] = $urlBuy;
					$arItems["ADD_URL"] = $urlAdd2Basket;
				}
			}
		}

		if (floatval($arItems["PRICE"]) > 0)
		{
			if ($arParams["VIEWED_CURRENCY"] != "default" && $arItems["CURRENCY"] != $arParams["VIEWED_CURRENCY"])
			{
				$arItems["PRICE"] = CCurrencyRates::ConvertCurrency($arItems["PRICE"], $arItems["CURRENCY"], $arParams["VIEWED_CURRENCY"]);
				$arItems["CURRENCY"] = $arParams["VIEWED_CURRENCY"];
			}

			$arItems["PRICE_FORMATED"] = SaleFormatCurrency($arItems["PRICE"], $arItems["CURRENCY"]);
			if (isset($arItems["MIN_PRICE_SET"]))
				$arItems["PRICE_FORMATED"] = GetMessage("VIEW_PRICE_FROM")." ".$arItems["PRICE_FORMATED"];

			$arItems["CAN_BUY"] = "Y";
		}
		else
			$arItems["CAN_BUY"] = "N";

		$arResult[] = $arItems;
	}
}

$this->IncludeComponentTemplate();