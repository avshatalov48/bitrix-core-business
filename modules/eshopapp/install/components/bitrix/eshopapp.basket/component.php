<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

$arParams["PATH_TO_ORDER"] = Trim($arParams["PATH_TO_ORDER"]);
if (strlen($arParams["PATH_TO_ORDER"]) <= 0)
	$arParams["PATH_TO_ORDER"] = "order.php";

if($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("SBB_TITLE"));

if (!isset($arParams["COLUMNS_LIST"]) || !is_array($arParams["COLUMNS_LIST"]) || count($arParams["COLUMNS_LIST"]) <= 0)
	$arParams["COLUMNS_LIST"] = array("NAME", "PRICE", "TYPE", "QUANTITY", "DELETE", "DELAY", "WEIGHT");

$arParams["HIDE_COUPON"] = (($arParams["HIDE_COUPON"] == "Y") ? "Y" : "N");
if (!CModule::IncludeModule("catalog"))
	$arParams["HIDE_COUPON"] = "Y";

if (!isset($arParams['QUANTITY_FLOAT']))
	$arParams['QUANTITY_FLOAT'] = 'N';
$arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] = (($arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] == "Y") ? "Y" : "N");


//$arParams['PRICE_VAT_INCLUDE'] = $arParams['PRICE_VAT_INCLUDE'] == 'N' ? 'N' : 'Y';
$arParams['PRICE_VAT_SHOW_VALUE'] = $arParams['PRICE_VAT_SHOW_VALUE'] == 'N' ? 'N' : 'Y';

$arParams["WEIGHT_UNIT"] = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", SITE_ID));
$arParams["WEIGHT_KOEF"] = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, SITE_ID));

if (strlen($_REQUEST["BasketRefresh"]) > 0 || strlen($_REQUEST["BasketOrder"]) > 0 || strlen($_REQUEST["action"]) > 0)
{
	if(strlen($_REQUEST["action"]) > 0)
	{
		$id = IntVal($_REQUEST["id"]);
		if($id > 0)
		{
			$dbBasketItems = CSaleBasket::GetList(
					array(),
					array(
							"FUSER_ID" => CSaleBasket::GetBasketUserID(),
							"LID" => SITE_ID,
							"ORDER_ID" => "NULL",
							"ID" => $id,
						),
					false,
					false,
					array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "CURRENCY")
				);
			if($arBasket = $dbBasketItems->Fetch())
			{
				if($_REQUEST["action"] == "delete" && in_array("DELETE", $arParams["COLUMNS_LIST"]))
				{
					CSaleBasket::Delete($arBasket["ID"]);
				}
				elseif($_REQUEST["action"] == "shelve" && in_array("DELAY", $arParams["COLUMNS_LIST"]))
				{
					if ($arBasket["DELAY"] == "N" && $arBasket["CAN_BUY"] == "Y")
						CSaleBasket::Update($arBasket["ID"], Array("DELAY" => "Y"));
				}
				elseif($_REQUEST["action"] == "add" && in_array("DELAY", $arParams["COLUMNS_LIST"]))
				{
					if ($arBasket["DELAY"] == "Y" && $arBasket["CAN_BUY"] == "Y")
						CSaleBasket::Update($arBasket["ID"], Array("DELAY" => "N"));
				}
				unset($_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID]);
			}
		}
	}
	else
	{
		if ($arParams["HIDE_COUPON"] != "Y")
		{
			$COUPON = Trim($_REQUEST["COUPON"]);
			if (strlen($COUPON) > 0)
				CCatalogDiscountCoupon::SetCoupon($COUPON);
			else
				CCatalogDiscountCoupon::ClearCoupon();
		}

		$dbBasketItems = CSaleBasket::GetList(
				array("NAME" => "ASC"),
				array(
						"FUSER_ID" => CSaleBasket::GetBasketUserID(),
						"LID" => SITE_ID,
						"ORDER_ID" => "NULL"
					),
				false,
				false,
				array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "PRICE", "QUANTITY", "CURRENCY", "SUBSCRIBE", "PRODUCT_PROVIDER_CLASS")
			);
		while ($arBasketItems = $dbBasketItems->Fetch())
		{
			$arBasketItems['QUANTITY'] = $arParams['QUANTITY_FLOAT'] == 'Y' ? DoubleVal($arBasketItems['QUANTITY']) : IntVal($arBasketItems['QUANTITY']);

			$quantityTmp = $arParams['QUANTITY_FLOAT'] == 'Y' ? DoubleVal($_REQUEST["QUANTITY_".$arBasketItems["ID"]]) : IntVal($_REQUEST["QUANTITY_".$arBasketItems["ID"]]);

			if ($arBasketItems["DELAY"] == "N" && $arBasketItems["CAN_BUY"] == "Y")
			{
				$arFields = array();
				if (in_array("QUANTITY", $arParams["COLUMNS_LIST"]))
					$arFields["QUANTITY"] = $quantityTmp;
				if (count($arFields) > 0 &&	($arBasketItems["QUANTITY"] != $arFields["QUANTITY"] && in_array("QUANTITY", $arParams["COLUMNS_LIST"])))
					CSaleBasket::Update($arBasketItems["ID"], $arFields);
			}
		}
	}
}

CSaleBasket::UpdateBasketPrices(CSaleBasket::GetBasketUserID(), SITE_ID);

$bShowReady = False;
$bShowDelay = False;
$bShowSubscribe = False;
$bShowNotAvail = False;
$allSum = 0;
$allWeight = 0;
$allCurrency = CSaleLang::GetLangCurrency(SITE_ID);
$allVATSum = 0;

$arResult["ITEMS"]["AnDelCanBuy"] = Array();
$arResult["ITEMS"]["DelDelCanBuy"] = Array();
$arResult["ITEMS"]["nAnCanBuy"] = Array();
$arResult["ITEMS"]["ProdSubscribe"] = Array();
$DISCOUNT_PRICE_ALL = 0;

$arVariableAliases = CComponentEngine::MakeComponentVariableAliases(array(), $arParams["VARIABLE_ALIASES"]);
$arBasketItems = array();
$arBasketItemsTmp = array();
$dbBasketItems = CSaleBasket::GetList(
		array(
				"NAME" => "ASC",
				"ID" => "ASC"
			),
		array(
				"FUSER_ID" => CSaleBasket::GetBasketUserID(),
				"LID" => SITE_ID,
				"ORDER_ID" => "NULL"
			),
		false,
		false,
		array("ID", "NAME", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "PRICE", "WEIGHT", "DETAIL_PAGE_URL", "NOTES", "CURRENCY", "VAT_RATE", "CATALOG_XML_ID", "PRODUCT_XML_ID", "SUBSCRIBE", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS")
	);

$arItemsIDs = array();
$arItemsInBasketIDs = array();

while ($arItems = $dbBasketItems->GetNext())
{
	$arItemsIDs[] = $arItems["PRODUCT_ID"];
	$arItemsInBasketIDs[$arItems["PRODUCT_ID"]] = $arItems["ID"] ;

	$arItems['QUANTITY'] = $arParams['QUANTITY_FLOAT'] == 'Y' ? number_format(DoubleVal($arItems['QUANTITY']), 2, '.', '') : IntVal($arItems['QUANTITY']);

	$arItems["PROPS"] = Array();
	if(in_array("PROPS", $arParams["COLUMNS_LIST"]))
	{
		$dbProp = CSaleBasket::GetPropsList(Array("SORT" => "ASC", "ID" => "ASC"), Array("BASKET_ID" => $arItems["ID"], "!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID")));
		while($arProp = $dbProp -> GetNext())
			$arItems["PROPS"][] = $arProp;
	}

	$arItems["PRICE_VAT_VALUE"] = (($arItems["PRICE"] / ($arItems["VAT_RATE"] +1)) * $arItems["VAT_RATE"]);
	$arItems["PRICE_FORMATED"] = SaleFormatCurrency($arItems["PRICE"], $arItems["CURRENCY"]);
	$arItems["WEIGHT"] = DoubleVal($arItems["WEIGHT"]);
	$arItems["WEIGHT_FORMATED"] = roundEx(DoubleVal($arItems["WEIGHT"]/$arParams["WEIGHT_KOEF"]), SALE_VALUE_PRECISION)." ".$arParams["WEIGHT_UNIT"];

	$arBasketItemsTmp[$arItems["PRODUCT_ID"]] = $arItems;
}

//--DETAIL_PHOTOS
$arBasketItemImgs = array();
$arSkuItemsIDsForPhoto = array();
$arBind = array();

$dbItemUrl = CIBlockElement::GetList(array(), array("ID"=>$arItemsIDs), false, false, array("DETAIL_PAGE_URL", "ID", "DETAIL_PICTURE", "PROPERTY_CML2_LINK"));
$dbItemUrl->SetUrlTemplates(htmlspecialcharsbx($arParams["CATALOG_FOLDER"])."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"."&".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#");
while ($arItem = $dbItemUrl->GetNext())
{
	if (isset($arBasketItemsTmp[$arItem["ID"]]))
		$arBasketItemsTmp[$arItem["ID"]]["DETAIL_PAGE_URL"] = $arItem["DETAIL_PAGE_URL"];

	$photo = "";
	$photo = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
	if (!$photo  && $arItem["PROPERTY_CML2_LINK_VALUE"])
	{
		$arSkuItemsIDsForPhoto[] = $arItem["PROPERTY_CML2_LINK_VALUE"];
		$arBind[$arItem["PROPERTY_CML2_LINK_VALUE"]][] = $arItemsInBasketIDs[$arItem["ID"]];
	}
	if ($photo)
	{
		$arBasketItemImgs[$arItemsInBasketIDs[$arItem["ID"]]] = $photo;
	}
}
$arBasketItems = array_values($arBasketItemsTmp);

//--DETAIL_PHOTOS
if (is_array($arSkuItemsIDsForPhoto) && !empty($arSkuItemsIDsForPhoto))
{
	$arSkuItemsIDsForPhoto = array_unique($arSkuItemsIDsForPhoto);
	$dbAddProps = CIBlockElement::GetList(array(), array("ID"=>$arSkuItemsIDsForPhoto), false, false, array("ID", "DETAIL_PICTURE"));
	while ($arAddProps = $dbAddProps->GetNext())
	{
		$photo = "";
		$photo = CFile::GetFileArray($arAddProps["DETAIL_PICTURE"]);
		if ($photo)
		{
			foreach ($arBind[$arAddProps["ID"]] as $val)
				$arBasketItemImgs[$val] = $photo;
		}
	}
}
$arResult["ITEMS_IMG"] = $arBasketItemImgs;
//--

foreach($arBasketItems as $arItems)
{
	if ($arItems["DELAY"] == "N" && $arItems["CAN_BUY"] == "Y")
	{
		$allSum += ($arItems["PRICE"] * $arItems["QUANTITY"]);
		$allWeight += ($arItems["WEIGHT"] * $arItems["QUANTITY"]);
		$allVATSum += roundEx($arItems["PRICE_VAT_VALUE"] * $arItems["QUANTITY"], SALE_VALUE_PRECISION);
	}

	if ($arItems["DELAY"] == "N" && $arItems["CAN_BUY"] == "Y")
	{
		$bShowReady = True;
		if(DoubleVal($arItems["DISCOUNT_PRICE"]) > 0)
		{
			$arItems["DISCOUNT_PRICE_PERCENT"] = $arItems["DISCOUNT_PRICE"]*100 / ($arItems["DISCOUNT_PRICE"] + $arItems["PRICE"]);
			$arItems["DISCOUNT_PRICE_PERCENT_FORMATED"] = roundEx($arItems["DISCOUNT_PRICE_PERCENT"], SALE_VALUE_PRECISION)."%";
			$DISCOUNT_PRICE_ALL += $arItems["DISCOUNT_PRICE"] * $arItems["QUANTITY"];
			$arItems["FULL_PRICE"] = $arItems["DISCOUNT_PRICE"] + $arItems["PRICE"];
			$arItems["FULL_PRICE_FORMATED"] = SaleFormatCurrency($arItems["FULL_PRICE"], $arItems["CURRENCY"]);

		}
		$arResult["ITEMS"]["AnDelCanBuy"][] = $arItems;
	}
	elseif ($arItems["DELAY"] == "Y" && $arItems["CAN_BUY"] == "Y")
	{
		$bShowDelay = True;
		if(DoubleVal($arItems["DISCOUNT_PRICE"]) > 0)
		{
			$arItems["DISCOUNT_PRICE_PERCENT"] = $arItems["DISCOUNT_PRICE"]*100 / ($arItems["DISCOUNT_PRICE"] + $arItems["PRICE"]);
			$arItems["DISCOUNT_PRICE_PERCENT_FORMATED"] = roundEx($arItems["DISCOUNT_PRICE_PERCENT"], SALE_VALUE_PRECISION)."%";
			$DISCOUNT_PRICE_ALL += $arItems["DISCOUNT_PRICE"] * $arItems["QUANTITY"];
			$arItems["FULL_PRICE"] = $arItems["DISCOUNT_PRICE"] + $arItems["PRICE"];
			$arItems["FULL_PRICE_FORMATED"] = SaleFormatCurrency($arItems["FULL_PRICE"], $arItems["CURRENCY"]);
		}
		$arResult["ITEMS"]["DelDelCanBuy"][] = $arItems;
	}
	elseif ($arItems["CAN_BUY"] == "N" && $arItems["SUBSCRIBE"] == "Y")
	{
		$bShowSubscribe = True;
		if(DoubleVal($arItems["DISCOUNT_PRICE"]) > 0)
		{
			$arItems["DISCOUNT_PRICE_PERCENT"] = $arItems["DISCOUNT_PRICE"]*100 / ($arItems["DISCOUNT_PRICE"] + $arItems["PRICE"]);
			$arItems["DISCOUNT_PRICE_PERCENT_FORMATED"] = roundEx($arItems["DISCOUNT_PRICE_PERCENT"], SALE_VALUE_PRECISION)."%";
			$DISCOUNT_PRICE_ALL += $arItems["DISCOUNT_PRICE"] * $arItems["QUANTITY"];
			$arItems["FULL_PRICE"] = $arItems["DISCOUNT_PRICE"] + $arItems["PRICE"];
			$arItems["FULL_PRICE_FORMATED"] = SaleFormatCurrency($arItems["FULL_PRICE"], $arItems["CURRENCY"]);
		}
		$arResult["ITEMS"]["ProdSubscribe"][] = $arItems;
	}
	else
	{
		$bShowNotAvail = True;
		if(DoubleVal($arItems["DISCOUNT_PRICE"]) > 0)
		{
			$arItems["DISCOUNT_PRICE_PERCENT"] = $arItems["DISCOUNT_PRICE"]*100 / ($arItems["DISCOUNT_PRICE"] + $arItems["PRICE"]);
			$arItems["DISCOUNT_PRICE_PERCENT_FORMATED"] = roundEx($arItems["DISCOUNT_PRICE_PERCENT"], SALE_VALUE_PRECISION)."%";
			$DISCOUNT_PRICE_ALL += $arItems["DISCOUNT_PRICE"] * $arItems["QUANTITY"];
			$arItems["FULL_PRICE"] = $arItems["DISCOUNT_PRICE"] + $arItems["PRICE"];
			$arItems["FULL_PRICE_FORMATED"] = SaleFormatCurrency($arItems["FULL_PRICE"], $arItems["CURRENCY"]);
		}
		$arResult["ITEMS"]["nAnCanBuy"][] = $arItems;
	}
}

$arResult["ShowReady"] = (($bShowReady)?"Y":"N");
$arResult["ShowDelay"] = (($bShowDelay)?"Y":"N");
$arResult["ShowNotAvail"] = (($bShowNotAvail)?"Y":"N");
$arResult["ShowSubscribe"] = (($bShowSubscribe)?"Y":"N");

/*
$dbDiscount = CSaleDiscount::GetList(
		array("SORT" => "ASC"),
		array(
				"LID" => SITE_ID,
				"ACTIVE" => "Y",
				"!>ACTIVE_FROM" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
				"!<ACTIVE_TO" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
				"<=PRICE_FROM" => $allSum,
				">=PRICE_TO" => $allSum,
				"USER_GROUPS" => $USER->GetUserGroupArray(),
			),
		false,
		false,
		array("*")
	);
$arMinDiscount = array();
$dblMinPrice = $allSum;
$arResult["DISCOUNT_PRICE"] = 0;
$arResult["DISCOUNT_PERCENT"] = 0;
while ($arDiscount = $dbDiscount->Fetch())
{
	$dblDiscount = 0;
	$allSum_tmp = $allSum;
	if ($arDiscount["DISCOUNT_TYPE"] == "P")
	{
		if($arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] == "Y")
		{
			foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $arResultItem)
			{
				$curDiscount = roundEx(DoubleVal($arResultItem["PRICE"]) * DoubleVal($arResultItem["QUANTITY"]) * $arDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
				$dblDiscount += $curDiscount;
			}
		}
		else
		{
			foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $arResultItem)
			{
				$curDiscount = roundEx(DoubleVal($arResultItem["PRICE"]) * $arDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
				$dblDiscount += $curDiscount * DoubleVal($arResultItem["QUANTITY"]);
			}
		}
	}
	else
	{
		$dblDiscount = roundEx(CCurrencyRates::ConvertCurrency($arDiscount["DISCOUNT_VALUE"], $arDiscount["CURRENCY"], $allCurrency), SALE_VALUE_PRECISION);
	}
	$allSum = $allSum - $dblDiscount;
	if ($dblMinPrice > $allSum)
	{
		$dblMinPrice = $allSum;
		$arMinDiscount = $arDiscount;
	}
	$allSum = $allSum_tmp;
}

if (!empty($arMinDiscount))
{
	if ($arMinDiscount["DISCOUNT_TYPE"] == "P")
	{
		$arResult["DISCOUNT_PERCENT"] = $arMinDiscount["DISCOUNT_VALUE"];
		$countItems = count($arResult["ITEMS"]["AnDelCanBuy"]);
		for ($bi = 0; $bi < $countItems; $bi++)
		{
			if($arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] == "Y")
			{
				$curDiscount = roundEx(DoubleVal($arResult["ITEMS"]["AnDelCanBuy"][$bi]["PRICE"]) * DoubleVal($arResult["ITEMS"]["AnDelCanBuy"][$bi]["QUANTITY"]) * $arMinDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
				$arResult["DISCOUNT_PRICE"] += $curDiscount;
			}
			else
			{
				$curDiscount = roundEx(DoubleVal($arResult["ITEMS"]["AnDelCanBuy"][$bi]["PRICE"]) * $arMinDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
				$arResult["DISCOUNT_PRICE"] += $curDiscount * DoubleVal($arResult["ITEMS"]["AnDelCanBuy"][$bi]["QUANTITY"]);
			}
		}
		$arResult["DISCOUNT_PERCENT_FORMATED"] = DoubleVal($arResult["DISCOUNT_PERCENT"])."%";
	}
	else
	{
		$arResult["DISCOUNT_PRICE"] = CCurrencyRates::ConvertCurrency($arMinDiscount["DISCOUNT_VALUE"], $arMinDiscount["CURRENCY"], $allCurrency);
		$arResult["DISCOUNT_PRICE"] = roundEx($arResult["DISCOUNT_PRICE"], SALE_VALUE_PRECISION);
	}
	$allSum = $allSum - $arResult["DISCOUNT_PRICE"];
}
*/
$arOrder = array(
	'SITE_ID' => SITE_ID,
	'USER_ID' => $USER->GetID(),
	'ORDER_PRICE' => $allSum,
	'ORDER_WEIGHT' => $allWeight,
	'BASKET_ITEMS' => $arResult["ITEMS"]["AnDelCanBuy"]
);

$arOptions = array(
	'COUNT_DISCOUNT_4_ALL_QUANTITY' => $arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"],
);

$arErrors = array();

CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

$allSum = 0;
$allWeight = 0;
$allVATSum = 0;

$DISCOUNT_PRICE_ALL = 0;
foreach ($arOrder['BASKET_ITEMS'] as &$arOneItem)
{
	$allSum += ($arOneItem["PRICE"] * $arOneItem["QUANTITY"]);
	$allWeight += ($arOneItem["WEIGHT"] * $arOneItem["QUANTITY"]);
	$allVATSum += roundEx($arOneItem["PRICE_VAT_VALUE"] * $arOneItem["QUANTITY"], SALE_VALUE_PRECISION);
	$arOneItem["PRICE_FORMATED"] = SaleFormatCurrency($arOneItem["PRICE"], $arOneItem["CURRENCY"]);
	$arOneItem["DISCOUNT_PRICE_PERCENT"] = $arOneItem["DISCOUNT_PRICE"]*100 / ($arOneItem["DISCOUNT_PRICE"] + $arOneItem["PRICE"]);
	$arOneItem["DISCOUNT_PRICE_PERCENT_FORMATED"] = roundEx($arOneItem["DISCOUNT_PRICE_PERCENT"], SALE_VALUE_PRECISION)."%";
	$DISCOUNT_PRICE_ALL += $arOneItem["DISCOUNT_PRICE"] * $arOneItem["QUANTITY"];
}
if (isset($arOneItem))
	unset($arOneItem);

$arResult["ITEMS"]["AnDelCanBuy"] = $arOrder['BASKET_ITEMS'];

//$DISCOUNT_PRICE_ALL += $arResult["DISCOUNT_PRICE"];
$arResult["allSum"] = $allSum;
$arResult["allWeight"] = $allWeight;
$arResult["allWeight_FORMATED"] = roundEx(DoubleVal($allWeight/$arParams["WEIGHT_KOEF"]), SALE_VALUE_PRECISION)." ".$arParams["WEIGHT_UNIT"];
$arResult["allSum_FORMATED"] = SaleFormatCurrency($allSum, $allCurrency);
$arResult["DISCOUNT_PRICE_FORMATED"] = SaleFormatCurrency($arResult["DISCOUNT_PRICE"], $allCurrency);

if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y')
{
	$arResult["allVATSum"] = $allVATSum;
	$arResult["allVATSum_FORMATED"] = SaleFormatCurrency($allVATSum, $allCurrency);
	$arResult["allNOVATSum_FORMATED"] = SaleFormatCurrency(DoubleVal($arResult["allSum"]-$allVATSum), $allCurrency);
}

if ($arParams["HIDE_COUPON"] != "Y")
	$arCoupons = CCatalogDiscountCoupon::GetCoupons();

if (count($arCoupons) > 0)
	$arResult["COUPON"] = htmlspecialcharsbx($arCoupons[0]);
if(count($arBasketItems)<=0)
	$arResult["ERROR_MESSAGE"] = GetMessage("SALE_EMPTY_BASKET");

$arResult["DISCOUNT_PRICE_ALL"] = $DISCOUNT_PRICE_ALL;
$arResult["DISCOUNT_PRICE_ALL_FORMATED"] = SaleFormatCurrency($DISCOUNT_PRICE_ALL, $allCurrency);

if (strlen($_REQUEST["BasketRefresh"]) > 0 || strlen($_REQUEST["action"]) > 0)
{
	unset($_REQUEST["BasketRefresh"]);
	unset($_REQUEST["BasketOrder"]);
	$APPLICATION->RestartBuffer();
	$data = array();
	$data["price"] = $arResult["allSum_FORMATED"];
	$data["is_discount"] = $arResult["DISCOUNT_PRICE"] ? true : false;
	$data["discount"] = $arResult["DISCOUNT_PRICE_FORMATED"];
	$data["weight"] = $arResult["allWeight_FORMATED"];
	$data["vat_excluded"] = $arResult["allNOVATSum_FORMATED"];
	$data["vat_included"] = $arResult["allVATSum_FORMATED"];
	$data["num_cart_items"] = count($arResult["ITEMS"]["AnDelCanBuy"]);
	$data["num_delay_items"] = count($arResult["ITEMS"]["DelDelCanBuy"]);
	$data["items"]["AnDelCanBuy"] = $arResult["ITEMS"]["AnDelCanBuy"];

	if (SITE_CHARSET != "utf-8")
		$data = $APPLICATION->ConvertCharsetArray($data, SITE_CHARSET, "utf-8");
	echo json_encode($data);
	die();
}

$this->IncludeComponentTemplate();
?>