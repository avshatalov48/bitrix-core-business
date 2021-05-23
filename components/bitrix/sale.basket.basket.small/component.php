<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
$arParams["PATH_TO_BASKET"] = trim($arParams["PATH_TO_BASKET"]);
$arParams["PATH_TO_ORDER"] = trim($arParams["PATH_TO_ORDER"]);
$arParams['SHOW_DELAY'] = (isset($arParams['SHOW_DELAY']) && $arParams['SHOW_DELAY'] == 'N' ? 'N' : 'Y');
$arParams['SHOW_NOTAVAIL'] = (isset($arParams['SHOW_NOTAVAIL']) && $arParams['SHOW_NOTAVAIL'] == 'N' ? 'N' : 'Y');
$arParams['SHOW_SUBSCRIBE'] = (isset($arParams['SHOW_SUBSCRIBE']) && $arParams['SHOW_SUBSCRIBE'] == 'N' ? 'N' : 'Y');

$bReady = false;
$bDelay = false;
$bNotAvail = false;
$bSubscribe = false;
$arItems = array();
$arReadyItems = array();
$allSum = 0.0;
$allWeight = 0.0;
$arBasketItems = array();
$arSetParentWeight = array();

$fuserId = (int)CSaleBasket::GetBasketUserID(true);
if ($fuserId > 0)
{
	$rsBaskets = CSaleBasket::GetList(
		array("ID" => "ASC"),
		array("FUSER_ID" => $fuserId, "LID" => SITE_ID, "ORDER_ID" => "NULL"),
		false,
		false,
		array(
			"ID", "NAME", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY",
			"PRICE", "WEIGHT", "DETAIL_PAGE_URL", "NOTES", "CURRENCY", "VAT_RATE", "CATALOG_XML_ID",
			"PRODUCT_XML_ID", "SUBSCRIBE", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS", "TYPE", "SET_PARENT_ID",
			"PRODUCT_PRICE_ID", 'CUSTOM_PRICE', 'BASE_PRICE'
		)
	);
	while ($arItem = $rsBaskets->GetNext())
	{
		$arBasketItems[] = $arItem;

		if (CSaleBasketHelper::isSetItem($arItem))
			$arSetParentWeight[$arItem["SET_PARENT_ID"]] += $arItem["WEIGHT"] * $arItem['QUANTITY'];
	}
}
if (!empty($arBasketItems))
{
	// count weight for set parent products
	foreach ($arBasketItems as &$arItem)
	{
		if (CSaleBasketHelper::isSetParent($arItem))
			$arItem["WEIGHT"] = $arSetParentWeight[$arItem["ID"]] / $arItem["QUANTITY"];
	}
	unset($arItem);

	foreach ($arBasketItems as &$arItem)
	{
		if (CSaleBasketHelper::isSetItem($arItem))
			continue;

		$boolOneReady = false;
		if ($arItem["DELAY"]=="N" && $arItem["CAN_BUY"]=="Y")
		{
			$boolOneReady = true;
			$bReady = true;
			$allSum += ($arItem["PRICE"] * $arItem["QUANTITY"]);
			$allWeight += ($arItem["WEIGHT"] * $arItem["QUANTITY"]);
		}
		elseif ($arItem["DELAY"]=="Y" && $arItem["CAN_BUY"]=="Y")
		{
			if ('N' == $arParams['SHOW_DELAY'])
				continue;
			$bDelay = true;
		}
		elseif ($arItem["CAN_BUY"]=="N" && $arItem["SUBSCRIBE"]=="N")
		{
			if ('N' == $arParams['SHOW_NOTAVAIL'])
				continue;
			$bNotAvail = true;
		}
		elseif ($arItem["CAN_BUY"]=="N" && $arItem["SUBSCRIBE"]=="Y")
		{
			if ('N' == $arParams['SHOW_SUBSCRIBE'])
				continue;
			$bSubscribe = true;
		}

		if (!$boolOneReady)
		{
			$arItem["PRICE_FORMATED"] = SaleFormatCurrency($arItem["PRICE"], $arItem["CURRENCY"]);
			$arItems[] = $arItem;
		}
		else
		{
			$arReadyItems[] = $arItem;
		}
	}
}

if (!empty($arReadyItems))
{
	$arOrder = array(
		'SITE_ID' => SITE_ID,
		'USER_ID' => $USER->GetID(),
		'ORDER_PRICE' => $allSum,
		'ORDER_WEIGHT' => $allWeight,
		'BASKET_ITEMS' => $arReadyItems
	);

	$arOptions = array();

	$arErrors = array();

	CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

	foreach ($arOrder['BASKET_ITEMS'] as &$arOneItem)
	{
		$arOneItem["PRICE_FORMATED"] = SaleFormatCurrency($arOneItem["PRICE"], $arOneItem["CURRENCY"]);
	}
	if (isset($arOneItem))
		unset($arOneItem);

	$arItems = array_merge($arOrder['BASKET_ITEMS'], $arItems);
}

$arResult = array(
	'READY' => ($bReady ? "Y" : "N"),
	'DELAY' => ($bDelay ? "Y" : "N"),
	'NOTAVAIL' => ($bNotAvail ? "Y" : "N"),
	'SUBSCRIBE' => ($bSubscribe ? "Y" : "N"),
	'ITEMS' => $arItems
);

$this->IncludeComponentTemplate();