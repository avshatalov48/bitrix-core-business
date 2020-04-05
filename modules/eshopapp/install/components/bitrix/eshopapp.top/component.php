<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

if (empty($arParams['IBLOCK_ID']))
{
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arParams["IBLOCK_TYPE_ID"], "ACTIVE"=>"Y", "IBLOCK_LID"=>SITE_ID));
	if($arr=$rsIBlock->Fetch())
		$arParams['IBLOCK_ID'] = $arr["ID"];
}

if(strlen($arParams["ELEMENT_SORT_FIELD"])<=0)
	$arParams["ELEMENT_SORT_FIELD"]="sort";
if($arParams["ELEMENT_SORT_ORDER"]!="desc")
	$arParams["ELEMENT_SORT_ORDER"]="asc";

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);
$arParams["BASKET_URL"]=trim($arParams["BASKET_URL"]);
if(strlen($arParams["BASKET_URL"])<=0)
	$arParams["BASKET_URL"] = "/personal/basket.php";

if(strlen($arParams["DISPLAY_IMG_WIDTH"])<=0)
	$arParams["DISPLAY_IMG_WIDTH"] = "75";
if(strlen($arParams["DISPLAY_IMG_WIDTH"])<=0)
	$arParams["DISPLAY_IMG_WIDTH"] = "225";
if(strlen($arParams["SHARPEN"])<=0)
	$arParams["SHARPEN"] = "30";

$arParams["ACTION_VARIABLE"]=trim($arParams["ACTION_VARIABLE"]);
if(strlen($arParams["ACTION_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";

$arParams["PRODUCT_ID_VARIABLE"]=trim($arParams["PRODUCT_ID_VARIABLE"]);
if(strlen($arParams["PRODUCT_ID_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_ID_VARIABLE"]))
	$arParams["PRODUCT_ID_VARIABLE"] = "id";

$arParams["PRODUCT_QUANTITY_VARIABLE"]=trim($arParams["PRODUCT_QUANTITY_VARIABLE"]);
if(strlen($arParams["PRODUCT_QUANTITY_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_QUANTITY_VARIABLE"]))
	$arParams["PRODUCT_QUANTITY_VARIABLE"] = "quantity";

$arParams["PRODUCT_PROPS_VARIABLE"]=trim($arParams["PRODUCT_PROPS_VARIABLE"]);
if(strlen($arParams["PRODUCT_PROPS_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_PROPS_VARIABLE"]))
	$arParams["PRODUCT_PROPS_VARIABLE"] = "prop";

/*$arParams["SECTION_ID_VARIABLE"]=trim($arParams["SECTION_ID_VARIABLE"]);
if(strlen($arParams["SECTION_ID_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["SECTION_ID_VARIABLE"]))
	$arParams["SECTION_ID_VARIABLE"] = "SECTION_ID"; */

$arParams["CATALOG_FOLDER"] = trim($arParams["CATALOG_FOLDER"]);
if(strlen($arParams["CATALOG_FOLDER"])<=0)
	$arParams["CATALOG_FOLDER"] = "/eshop_app/catalog/";

$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N";
$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y";
$arParams["DISPLAY_COMPARE"] = $arParams["DISPLAY_COMPARE"]=="Y";

$arParams["ELEMENT_COUNT"] = intval($arParams["ELEMENT_COUNT"]);
if($arParams["ELEMENT_COUNT"]<=0)
	$arParams["ELEMENT_COUNT"]=9;

/*if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]); */
if(!is_array($arParams["PRICE_CODE"]))
	$arParams["PRICE_CODE"] = array();

//$arParams["USE_PRICE_COUNT"] = $arParams["USE_PRICE_COUNT"]=="Y";
$arParams["SHOW_PRICE_COUNT"] = intval($arParams["SHOW_PRICE_COUNT"]);
if($arParams["SHOW_PRICE_COUNT"]<=0)
	$arParams["SHOW_PRICE_COUNT"]=1;
$arParams["USE_PRODUCT_QUANTITY"] = $arParams["USE_PRODUCT_QUANTITY"]==="Y";

if(!is_array($arParams["PRODUCT_PROPERTIES"]))
	$arParams["PRODUCT_PROPERTIES"] = array();
foreach($arParams["PRODUCT_PROPERTIES"] as $k=>$v)
	if($v==="")
		unset($arParams["PRODUCT_PROPERTIES"][$k]);

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

$arParams['CONVERT_CURRENCY'] = (isset($arParams['CONVERT_CURRENCY']) && 'Y' == $arParams['CONVERT_CURRENCY'] ? 'Y' : 'N');
$arParams['CURRENCY_ID'] = trim(strval($arParams['CURRENCY_ID']));
if ('' == $arParams['CURRENCY_ID'])
{
	$arParams['CONVERT_CURRENCY'] = 'N';
}
elseif ('N' == $arParams['CONVERT_CURRENCY'])
{
	$arParams['CURRENCY_ID'] = '';
}

if(strlen($arParams["FILTER_NAME"])>0)
{
	global ${$arParams["FILTER_NAME"]};
	$arrFilter = ${$arParams["FILTER_NAME"]};
}
if(!is_array($arrFilter))
	$arrFilter=array();

$arParams["CACHE_FILTER"]=$arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;


/*************************************************************************
			Processing of the Buy link
*************************************************************************/
$strError = "";
if(array_key_exists($arParams["ACTION_VARIABLE"], $_REQUEST) && array_key_exists($arParams["PRODUCT_ID_VARIABLE"], $_REQUEST))
{
	if(array_key_exists($arParams["ACTION_VARIABLE"]."BUY", $_REQUEST))
		$action = "BUY";
	elseif(array_key_exists($arParams["ACTION_VARIABLE"]."ADD2BASKET", $_REQUEST))
		$action = "ADD2BASKET";
	else
		$action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);

	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if (($action == "ADD2BASKET" || $action == "BUY" || $action == "SUBSCRIBE_PRODUCT") && $productID > 0)
	{
		if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			if($arParams["USE_PRODUCT_QUANTITY"])
				$QUANTITY = intval($_POST[$arParams["PRODUCT_QUANTITY_VARIABLE"]]);
			if($QUANTITY <= 1)
				$QUANTITY = 1;

			$product_properties = array();
			if(count($arParams["PRODUCT_PROPERTIES"]))
			{
				if(is_array($_POST[$arParams["PRODUCT_PROPS_VARIABLE"]]))
				{
					$product_properties = CIBlockPriceTools::CheckProductProperties(
						$arParams["IBLOCK_ID"],
						$productID,
						$arParams["PRODUCT_PROPERTIES"],
						$_POST[$arParams["PRODUCT_PROPS_VARIABLE"]]
					);
					if(!is_array($product_properties))
						$strError = GetMessage("CATALOG_ERROR2BASKET").".";
				}
				else
				{
					$strError = GetMessage("CATALOG_ERROR2BASKET").".";
				}
			}
			
			if(is_array($arParams["OFFERS_CART_PROPERTIES"]))
			{
				foreach($arParams["OFFERS_CART_PROPERTIES"] as $i => $pid)
					if($pid === "")
						unset($arParams["OFFERS_CART_PROPERTIES"][$i]);

				if(!empty($arParams["OFFERS_CART_PROPERTIES"]))
				{
					$product_properties = CIBlockPriceTools::GetOfferProperties(
						$productID,
						$arParams["IBLOCK_ID"],
						$arParams["OFFERS_CART_PROPERTIES"]
					);
				}
			}

			$arRewriteFields = array();
			$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
			$arNotify = unserialize($notifyOption);

			if ($action == "SUBSCRIBE_PRODUCT" && $arNotify[SITE_ID]['use'] == 'Y')
			{
				$arRewriteFields["SUBSCRIBE"] = "Y";
				$arRewriteFields["CAN_BUY"] = "N";
			}

			if(!$strError && Add2BasketByProductID($productID, $QUANTITY, $arRewriteFields, $product_properties))
			{
				$APPLICATION->RestartBuffer();
				return;
				/*if ($action == "BUY")
					LocalRedirect($arParams["BASKET_URL"]);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));  */
			}
			else
			{
				if ($ex = $GLOBALS["APPLICATION"]->GetException())
					$strError = $ex->GetString();
				else
					$strError = GetMessage("CATALOG_ERROR2BASKET").".";
			}
		}
	}
}
if(strlen($strError)>0)
{
	ShowError($strError);
	return;
}

if($arParams['IBLOCK_ID'] > 0)
{
	$arrFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	//if(defined("BX_COMP_MANAGED_CACHE"))
	//	$GLOBALS["CACHE_MANAGER"]->RegisterTag("iblock_id_".$arParams["IBLOCK_ID"]);

	$arFilter = Array("ID" => $arParams['IBLOCK_ID'],"TYPE"=>"catalog", "SITE_ID"=>SITE_ID);
	$obCache = new CPHPCache;
	if($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog/top"))
	{
		$arIblock = $obCache->GetVars();
	}
	else
	{
		$arIBlock = array();
		$dbRes = CIBlock::GetList(Array(), $arFilter);
		$dbRes = new CIBlockResult($dbRes);

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache("/iblock/catalog/top");

			if ($arIBlock = $dbRes->GetNext())
			{
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arParams["IBLOCK_ID"]);
			}
			$CACHE_MANAGER->EndTagCache();
		}
		else
		{
			if(!$arIBlock = $dbRes->GetNext())
				$arIBlock = array();
		}

		$obCache->EndDataCache($arIBlock);
	}
}
else
{
	ShowError("IBLOCK_ERROR");
	return;
}

/*if($arParams["DISPLAY_COMPARE"]=="Y"):
	$APPLICATION->IncludeComponent(
		"bitrix:catalog.compare.list",
		"eshop",
		Array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"NAME" => $arParams["COMPARE_NAME"],
			"COMPARE_URL" => $arIBlock["LIST_PAGE_URL"]."compare/"
		),
		$component
	);
endif; */
/*************************************************************************
			Work with cache
*************************************************************************/
if($this->StartResultCache(false, array($arrFilter, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()))))
{
	//This function returns array with prices description and access rights
	//in case catalog module n/a prices get values from element properties
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);

	$arConvertParams = array();
	if ('Y' == $arParams['CONVERT_CURRENCY'])
	{
		if (!CModule::IncludeModule('currency'))
		{
			$arParams['CONVERT_CURRENCY'] = 'N';
			$arParams['CURRENCY_ID'] = '';
		}
		else
		{
			$arCurrencyInfo = CCurrency::GetByID($arParams['CURRENCY_ID']);
			if (!(is_array($arCurrencyInfo) && !empty($arCurrencyInfo)))
			{
				$arParams['CONVERT_CURRENCY'] = 'N';
				$arParams['CURRENCY_ID'] = '';
			}
			else
			{
				$arParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
				$arConvertParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
			}
		}
	}
	/************************************
			Elements
	************************************/
	//SELECT
	$arSelect = array(
		"ID",
		"NAME",
		"CODE",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"DETAIL_PAGE_URL",
		//"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
		"PREVIEW_TEXT",
		"PREVIEW_TEXT_TYPE",
		"PROPERTY_*",
	);
	//WHERE
	$arrFilter["ACTIVE"] = "Y";

	$arrFilter["IBLOCK_LID"] = SITE_ID;
	$arrFilter["IBLOCK_ACTIVE"] = "Y";
	$arrFilter["ACTIVE_DATE"] = "Y";
	$arrFilter["ACTIVE"] = "Y";
	$arrFilter["CHECK_PERMISSIONS"] = "Y";

	//ORDER BY
	$arSort = array(
		$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
		"ID" => "DESC",
	);
	//PRICES
	//if(!$arParams["USE_PRICE_COUNT"])
	//{
		foreach($arResult["PRICES"] as $key => $value)
		{
			$arSelect[] = $value["SELECT"];
			$arrFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
		}
	//}
	$arResult['CONVERT_CURRENCY'] = $arConvertParams;

	if ($arParams['FLAG_PROPERTY_CODE'])
	{
		$arrFilter['!PROPERTY_'.$arParams['FLAG_PROPERTY_CODE']] = false;
	}

	$arResult["ITEMS"] = array();
	$arResult["IDS"] = array();

	$arDefaultVariableAliases404 = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$rsElements = CIBlockElement::GetList($arSort, $arrFilter, false, array("nTopCount" => $arParams["ELEMENT_COUNT"]), $arSelect);
	$rsElements->SetUrlTemplates(htmlspecialcharsbx($arParams["CATALOG_FOLDER"])."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"."&".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#"/*$arParams["DETAIL_URL"]*/);

	while($obElement = $rsElements->GetNextElement())
	{
		$arItem = $obElement->GetFields();

		$arItem["DETAIL_PICTURE"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);

		$arItem["PROPERTIES"] = $obElement->GetProperties();

		$arItem["DISPLAY_PROPERTIES"] = array();
		/*foreach($arParams["PROPERTY_CODE"] as $pid)
		{
			$prop = &$arItem["PROPERTIES"][$pid];
			if((is_array($prop["VALUE"]) && count($prop["VALUE"])>0) ||
				(!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0))
			{
				$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "catalog_out");
			}
		} */

		$arItem["PRODUCT_PROPERTIES"] = CIBlockPriceTools::GetProductProperties(
			$arParams["IBLOCK_ID"],
			$arItem["ID"],
			$arParams["PRODUCT_PROPERTIES"],
			$arItem["PROPERTIES"]
		);

		/*if($arParams["USE_PRICE_COUNT"])
		{
			if(CModule::IncludeModule("catalog"))
			{
				$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arItem["ID"]);
				foreach($arItem["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
					$arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialcharsbx($arColumn["NAME_LANG"]);
			}
			else
			{
				$arItem["PRICE_MATRIX"] = false;
			}
			$arItem["PRICES"] = array();
		}
		else
		{ */
			$arItem["PRICE_MATRIX"] = false;
			$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem, $arParams['PRICE_VAT_INCLUDE'], $arConvertParams);
		//}
		$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem);
		$arItem["BUY_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItem["ADD_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItem["COMPARE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("action=ADD_TO_COMPARE_LIST&id=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItem["DELETE_COMPARE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("action=DELETE_FROM_COMPARE_RESULT&IBLOCK_ID=".$arParams['IBLOCK_ID']."&ID[]=".$arItem['ID'],array("action", "IBLOCK_ID", "ID")));
		$arItem["SUBSCRIBE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=SUBSCRIBE_PRODUCT&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));

		$arResult["ITEMS"][]=$arItem;
		$arResult['IDS'][] = $arItem['ID'];
	}
	$arResult["RESULT"] = $rsElements;
	
	if(!isset($arParams["OFFERS_FIELD_CODE"]))
		$arParams["OFFERS_FIELD_CODE"] = array();
	foreach($arParams["OFFERS_FIELD_CODE"] as $key => $value)
		if($value === "")
			unset($arParams["OFFERS_FIELD_CODE"][$key]);

	if(!isset($arParams["OFFERS_PROPERTY_CODE"]))
		$arParams["OFFERS_PROPERTY_CODE"] = array();
	foreach($arParams["OFFERS_PROPERTY_CODE"] as $key => $value)
		if($value === "")
			unset($arParams["OFFERS_PROPERTY_CODE"][$key]);

	$baseCurrency = CCurrency::GetBaseCurrency();
	if(
		!empty($arResult["IDS"])
		&& (
			!empty($arParams["OFFERS_FIELD_CODE"])
			|| !empty($arParams["OFFERS_PROPERTY_CODE"])
		)
	)
	{
		$arOffers = array();
		//foreach($arParams['IBLOCK_ID'] as $key => $iblockID)
		//{
			$arOffersIblock = CIBlockPriceTools::GetOffersIBlock($arParams['IBLOCK_ID']);
			$OFFERS_IBLOCK_ID = is_array($arOffersIblock)? $arOffersIblock["OFFERS_IBLOCK_ID"]: 0;
			//if(!$OFFERS_IBLOCK_ID)
			//	continue;
			$arElementsOffer = array();	
			foreach($arResult["ITEMS"] as $key2=>$arElement)
				if ($arElement["IBLOCK_ID"] == $arParams['IBLOCK_ID'])
					$arElementsOffer[$key2] = $arElement["ID"];
			//if (empty($arElementsOffer)) continue;


			$arOffers = CIBlockPriceTools::GetOffersArray(
				$arParams['IBLOCK_ID']
				,$arElementsOffer
				,array(
					$arParams["OFFERS_SORT_FIELD"] => $arParams["OFFERS_SORT_ORDER"],
					"ID" => "DESC",
				)
				,$arParams["OFFERS_FIELD_CODE"]
				,$arParams["OFFERS_PROPERTY_CODE"]
				,$arParams["OFFERS_LIMIT"]
				,$arResult["PRICES"]
				,$arParams['PRICE_VAT_INCLUDE']
				,$arConvertParams
			);

			if(!empty($arOffers))
			{
				$arElementOffer = array();
				foreach($arElementsOffer as $i => $id)
				{
					$arResult["ITEMS"][$i]["OFFERS"] = array();
					$arElementOffer[$id] = &$arResult["ITEMS"][$i]["OFFERS"];
				}
				foreach($arOffers as $key=>$arOffer)
				{
					if(array_key_exists($arOffer["LINK_ELEMENT_ID"], $arElementOffer))
					{
						$arOffer["BUY_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arOffer["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
						$arOffer["ADD_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arOffer["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
						$arOffer["COMPARE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("action=ADD_TO_COMPARE_LIST&id=".$arOffer["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
						$arOffer["DELETE_COMPARE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("action=DELETE_FROM_COMPARE_RESULT&IBLOCK_ID=".$arParams['IBLOCK_ID']."&ID[]=".$arOffer["ID"],array("action", "IBLOCK_ID", "ID")));
						$arOffer["SUBSCRIBE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=SUBSCRIBE_PRODUCT&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arOffer["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));

						$arElementOffer[$arOffer["LINK_ELEMENT_ID"]][] = $arOffer;
					}
				}
			}
		//}

		/*foreach($arResult["ITEMS"] as $key => $arItem)
		{
			if (is_array($arItem["OFFERS"]))
			{
				$minProductPrice = "741";
				foreach ($arItem["OFFERS"] as $key2 => $arOffer)
				{
					foreach ($arOffer["PRICES"] as $key3 => $arPrice)
					{
						if ((intval($minProductPrice) > 0) && (intval($minOfferPrice)  < intval($minProductPrice))) {
							$minProductPrice = $minOfferPrice;            }
						else
							$minProductPrice = $minOfferPrice;

					}
				}
				if ($minProductPrice > 0)
				{
					$arResult["ITEMS"][$key]["MIN_OFFER_PRICE"] = $minProductPrice;
					$arResult["ITEMS"][$key]["PRINT_MIN_OFFER_PRICE"] = FormatCurrency($minProductPrice, $baseCurrency);
				}
			}
		}        */
	}

	$this->SetResultCacheKeys(array("IDS"));
	$this->IncludeComponentTemplate();
}
?>