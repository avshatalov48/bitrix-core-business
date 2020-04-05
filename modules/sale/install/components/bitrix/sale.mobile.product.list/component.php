<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage("SMPL_SALE_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError("SMPL_MOBILEAPP_NOT_INSTALLED");
	return;
}

if (!CModule::IncludeModule('iblock'))
{
	ShowError("SMPL_IBLOCK_NOT_INSTALLED");
	return;
}


$bXmlId = COption::GetOptionString("sale", "show_order_product_xml_id", "N");

$dbBasket = CSaleBasket::GetList(
	array("NAME" => "ASC"),
	array("ORDER_ID" => $arParams["ORDER_ID"]),
	false,
	false,
	array("ID", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE", "CURRENCY", "WEIGHT",
		"QUANTITY", "NAME", "MODULE", "CALLBACK_FUNC", "NOTES", "DETAIL_PAGE_URL",
		"DISCOUNT_PRICE", "DISCOUNT_VALUE", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC",
		"PAY_CALLBACK_FUNC", "CATALOG_XML_ID", "PRODUCT_XML_ID", "VAT_RATE")
);

$weight = 0;
$price =0;
$price_total = 0;
$arProdIds = array(); //http://jabber.bx/view.php?id=37744
$arProdIdsPrIds = array();

while ($arBasket = $dbBasket->GetNext())
{
	$arProdIds[] = $arBasket["PRODUCT_ID"];
	$arProdIdsPrIds[$arBasket["PRODUCT_ID"]] = $arBasket["ID"];

	if ($bXmlId == "N")
		$arPropsFilter["!CODE"] = array("PRODUCT.XML_ID", "CATALOG.XML_ID");

	$arBasket["PROPS"] = Array();
	$dbBasketProps = CSaleBasket::GetPropsList(
			array("BASKET_ID" => "ASC", "SORT" => "ASC", "NAME" => "ASC"),
			array("BASKET_ID" => $arBasket["ID"]),
			false,
			false,
			array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
		);
	while ($arBasketProps = $dbBasketProps->GetNext())
		$arBasket["PROPS"][$arBasketProps["ID"]] = $arBasketProps;

	$arResult["BASKET"][$arBasket["ID"]] = $arBasket;
	$arResult["BASKET"][$arBasket["ID"]]["BALANCE"] = "0";

	$arCurFormat = CCurrencyLang::GetCurrencyFormat($arBasket["CURRENCY"]);
	$CURRENCY_FORMAT = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));

	$priceDiscount = $priceBase = ($arBasket["DISCOUNT_PRICE"] + $arBasket["PRICE"]);
	if(DoubleVal($priceBase) > 0)
		$priceDiscount = roundEx(($arBasket["DISCOUNT_PRICE"] * 100) / $priceBase, SALE_VALUE_PRECISION);

	$arResult["BASKET"][$arBasket["ID"]]["PRICE_STRING"] = CurrencyFormatNumber($arBasket["PRICE"], $arBasket["CURRENCY"])." ".$CURRENCY_FORMAT;

	if($arBasket["DISCOUNT_PRICE"] > 0)
	{
		$arResult["BASKET"][$arBasket["ID"]]["OLD_PRICE_STRING"] = CurrencyFormatNumber($priceBase, $arBasket["CURRENCY"])." ".$CURRENCY_FORMAT;
		$arResult["BASKET"][$arBasket["ID"]]["DISCOUNT_STRING"] = $priceDiscount."%";
	}

	$weight += $arBasket["WEIGHT"]*$arBasket["QUANTITY"];
	$price += $arBasket["PRICE"]*$arBasket["QUANTITY"];
	$price_total += ($arBasket["PRICE"] + $arBasket["DISCOUNT_PRICE"]) * $arBasket["QUANTITY"];
}

$arResult["WEIGHT"] = $weight;
$arResult["PRICE"] = $price;
$arResult["PRICE_TOTAL"] = $price_total;

$rsProductsInfo = CIBlockElement::GetList(
									array(),
									array("ID" => $arProdIds),
									false,
									false,
									array("ID", "PREVIEW_PICTURE", "DETAIL_PICTURE", "NAME")
);

while($arProductInfo = $rsProductsInfo->GetNext())
	$arResult["BASKET"][$arProdIdsPrIds[$arProductInfo["ID"]]]["INFO"] = $arProductInfo;

if(CModule::IncludeModule('catalog'))
{
	$rsCatProd = CCatalogProduct::GetList(
									array(),
									array("ID" => $arProdIds),
									false,
									false,
									array("ID", "QUANTITY")
	);

	while($arCatProd = $rsCatProd->Fetch())
		if ($arResult["BASKET"][$arProdIdsPrIds[$arCatProd["ID"]]]["MODULE"] == "catalog")
			$arResult["BASKET"][$arProdIdsPrIds[$arCatProd["ID"]]]["BALANCE"] = FloatVal($arCatProd["QUANTITY"]);
}

$this->IncludeComponentTemplate();
return $arResult;
?>
