<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}
/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

unset($arParams["IBLOCK_TYPE"]); //was used only for IBLOCK_ID setup with Editor
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);

if (empty($arParams["ELEMENT_SORT_FIELD"]))
	$arParams["ELEMENT_SORT_FIELD"] = "sort";
if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["ELEMENT_SORT_ORDER"]))
	$arParams["ELEMENT_SORT_ORDER"]="asc";
if (empty($arParams["ELEMENT_SORT_FIELD2"]))
	$arParams["ELEMENT_SORT_FIELD2"] = "id";
if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["ELEMENT_SORT_ORDER2"]))
	$arParams["ELEMENT_SORT_ORDER2"] = "desc";

if(strlen($arParams["SECTION_SORT_FIELD"])<=0)
	$arParams["SECTION_SORT_FIELD"]="sort";
$arParams["SECTION_SORT_ORDER"] = strtolower($arParams["SECTION_SORT_ORDER"]);
if($arParams["SECTION_SORT_ORDER"]!="desc")
	$arParams["SECTION_SORT_ORDER"]="asc";

$arrFilter=array();
if(strlen($arParams["FILTER_NAME"])>0)
{
	global ${$arParams["FILTER_NAME"]};
	if (is_array(${$arParams["FILTER_NAME"]}))
		$arrFilter = ${$arParams["FILTER_NAME"]};
}

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);
$arParams["BASKET_URL"]=trim($arParams["BASKET_URL"]);
if(strlen($arParams["BASKET_URL"])<=0)
	$arParams["BASKET_URL"] = "/personal/basket.php";

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

$arParams["SECTION_ID_VARIABLE"]=trim($arParams["SECTION_ID_VARIABLE"]);
if(strlen($arParams["SECTION_ID_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["SECTION_ID_VARIABLE"]))
	$arParams["SECTION_ID_VARIABLE"] = "SECTION_ID";

$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N";
$arParams["DISPLAY_COMPARE"] = $arParams["DISPLAY_COMPARE"]=="Y";

$arParams["SECTION_COUNT"] = intval($arParams["SECTION_COUNT"]);
if($arParams["SECTION_COUNT"]<=0)
	$arParams["SECTION_COUNT"]=20;
$arParams["ELEMENT_COUNT"] = intval($arParams["ELEMENT_COUNT"]);
if($arParams["ELEMENT_COUNT"]<=0)
	$arParams["ELEMENT_COUNT"]=20;
$arParams["LINE_ELEMENT_COUNT"] = intval($arParams["LINE_ELEMENT_COUNT"]);
if($arParams["LINE_ELEMENT_COUNT"]<=0)
	$arParams["LINE_ELEMENT_COUNT"]=3;

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]);
if(!is_array($arParams["PRICE_CODE"]))
	$arParams["PRICE_CODE"] = array();

if (empty($arParams['HIDE_NOT_AVAILABLE']))
	$arParams['HIDE_NOT_AVAILABLE'] = 'N';
elseif ('Y' != $arParams['HIDE_NOT_AVAILABLE'])
	$arParams['HIDE_NOT_AVAILABLE'] = 'N';

$arParams["USE_PRICE_COUNT"] = $arParams["USE_PRICE_COUNT"]=="Y";
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

$arParams["CACHE_FILTER"]=$arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;

$arParams['CACHE_GROUPS'] = trim($arParams['CACHE_GROUPS']);
if ('N' != $arParams['CACHE_GROUPS'])
	$arParams['CACHE_GROUPS'] = 'Y';

$arParams["USE_MAIN_ELEMENT_SECTION"] = $arParams["USE_MAIN_ELEMENT_SECTION"]==="Y";
/*************************************************************************
			Processing of the Buy link
*************************************************************************/
$strError = "";
if (array_key_exists($arParams["ACTION_VARIABLE"], $_REQUEST) && array_key_exists($arParams["PRODUCT_ID_VARIABLE"], $_REQUEST))
{
	if(array_key_exists($arParams["ACTION_VARIABLE"]."BUY", $_REQUEST))
		$action = "BUY";
	elseif(array_key_exists($arParams["ACTION_VARIABLE"]."ADD2BASKET", $_REQUEST))
		$action = "ADD2BASKET";
	else
		$action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);

	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if (($action == "ADD2BASKET" || $action == "BUY") && $productID > 0)
	{
		if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			$QUANTITY = 0;
			$product_properties = array();
			if(!empty($arParams["PRODUCT_PROPERTIES"]))
			{
				if(is_array($_REQUEST[$arParams["PRODUCT_PROPS_VARIABLE"]]))
				{
					$product_properties = CIBlockPriceTools::CheckProductProperties(
						$arParams["IBLOCK_ID"],
						$productID,
						$arParams["PRODUCT_PROPERTIES"],
						$_REQUEST[$arParams["PRODUCT_PROPS_VARIABLE"]]
					);
					if(!is_array($product_properties))
						$strError = GetMessage("CATALOG_ERROR2BASKET").".";
				}
				else
				{
					$strError = GetMessage("CATALOG_ERROR2BASKET").".";
				}
			}
			if ($arParams["USE_PRODUCT_QUANTITY"])
			{
				if (isset($_REQUEST[$arParams["PRODUCT_QUANTITY_VARIABLE"]]))
				{
					$QUANTITY = doubleval($_REQUEST[$arParams["PRODUCT_QUANTITY_VARIABLE"]]);
				}
			}
			if (0 >= $QUANTITY)
			{
				$rsRatios = CCatalogMeasureRatio::getList(
					array(),
					array('PRODUCT_ID' => $productID),
					false,
					false,
					array('PRODUCT_ID', 'RATIO')
				);
				if ($arRatio = $rsRatios->Fetch())
				{
					$intRatio = intval($arRatio['RATIO']);
					$dblRatio = doubleval($arRatio['RATIO']);
					$QUANTITY = ($dblRatio > $intRatio ? $dblRatio : $intRatio);
				}
			}
			if (0 >= $QUANTITY)
				$QUANTITY = 1;

			if(!$strError && Add2BasketByProductID($productID, $QUANTITY, $product_properties))
			{
				if ($action == "BUY")
					LocalRedirect($arParams["BASKET_URL"]);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			}
			else
			{
				if ($ex = $APPLICATION->GetException())
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

$arResult["SECTIONS"]=array();

/*************************************************************************
			Work with cache
*************************************************************************/
if($this->StartResultCache(false, array($arrFilter, CDBResult::NavStringForCache($arParams["PAGE_ELEMENT_COUNT"]), ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()))))
{
	global $CACHE_MANAGER;
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
	$arResult['CONVERT_CURRENCY'] = $arConvertParams;

	$bIBlockCatalog = false;
	$arCatalog = false;
	$bCatalog = CModule::IncludeModule('catalog');
	if ($bCatalog)
	{
		$arCatalog = CCatalog::GetByID($arParams["IBLOCK_ID"]);
		if (!empty($arCatalog) && is_array($arCatalog))
			$bIBlockCatalog = true;
	}
	$arResult['CATALOG'] = $arCatalog;
	//This function returns array with prices description and access rights
	//in case catalog module n/a prices get values from element properties
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);

	$arFilter = array(
		"ACTIVE"=>"Y",
		"GLOBAL_ACTIVE"=>"Y",
		"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE"=>"Y",
	);
	//ORDER BY
	$arSort = array(
		$arParams["SECTION_SORT_FIELD"] => $arParams["SECTION_SORT_ORDER"],
		"ID" => "ASC",
	);
	//SELECT
	$arSelect = array();
	if(isset($arParams["SECTION_FIELDS"]) && is_array($arParams["SECTION_FIELDS"]))
	{
		foreach($arParams["SECTION_FIELDS"] as $field)
			if(is_string($field) && !empty($field))
				$arSelect[] = $field;
	}

	if(!empty($arSelect))
	{
		$arSelect[] = "ID";
		$arSelect[] = "NAME";
		$arSelect[] = "LIST_PAGE_URL";
		$arSelect[] = "SECTION_PAGE_URL";
	}

	if(isset($arParams["SECTION_USER_FIELDS"]) && is_array($arParams["SECTION_USER_FIELDS"]))
	{
		foreach($arParams["SECTION_USER_FIELDS"] as $field)
			if(is_string($field) && preg_match("/^UF_/", $field))
				$arSelect[] = $field;
	}

	$currencyList = array();

	$bGetPropertyCodes = !empty($arParams["PROPERTY_CODE"]);
	$bGetProductProperties = !empty($arParams["PRODUCT_PROPERTIES"]);
	$bGetProperties = $bGetPropertyCodes || $bGetProductProperties;

	//EXECUTE
	$rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
	$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);
	while($arSection = $rsSections->GetNext())
	{
		\Bitrix\Iblock\InheritedProperty\SectionValues::queue($arSection["IBLOCK_ID"], $arSection["ID"]);

		// list of the element fields that will be used in selection
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"CODE",
			"XML_ID",
			"NAME",
			"ACTIVE",
			"DATE_ACTIVE_FROM",
			"DATE_ACTIVE_TO",
			"SORT",
			"PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"DATE_CREATE",
			"CREATED_BY",
			"TIMESTAMP_X",
			"MODIFIED_BY",
			"TAGS",
			"IBLOCK_SECTION_ID",
			"DETAIL_PAGE_URL",
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"PROPERTY_*",
		);
		if($arParams["SHOW_DESCRIPTION"])
		{
			$arSelect[] = "PREVIEW_TEXT";
			$arSelect[] = "PREVIEW_TEXT_TYPE";
		}
		$arrFilter["ACTIVE"] = "Y";
		$arrFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
		$arrFilter["SECTION_ID"] = $arSection["ID"];
		$arrFilter["ACTIVE_DATE"] = "Y";
		$arrFilter["CHECK_PERMISSIONS"] = "Y";
		if ($bIBlockCatalog && 'Y' == $arParams['HIDE_NOT_AVAILABLE'])
			$arrFilter['CATALOG_AVAILABLE'] = 'Y';
		//PRICES
		$arPriceTypeID = array();
		if (!$arParams["USE_PRICE_COUNT"])
		{
			foreach($arResult["PRICES"] as &$value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;
				$arSelect[] = $value["SELECT"];
				$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
			}
			if (isset($value))
				unset($value);
		}
		else
		{
			foreach($arResult["PRICES"] as &$value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;
				$arPriceTypeID[] = $value["ID"];
			}
			if (isset($value))
				unset($value);
		}
		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
			$arParams["ELEMENT_SORT_FIELD2"] => $arParams["ELEMENT_SORT_ORDER2"],
		);

		//EXECUTE
		$rsElements = CIBlockElement::GetList($arSort, $arrFilter, false, array("nTopCount"=>$arParams["ELEMENT_COUNT"]), $arSelect);
		$rsElements->SetUrlTemplates($arParams["DETAIL_URL"]);
		if(!$arParams["USE_MAIN_ELEMENT_SECTION"])
			$rsElements->SetSectionContext($arSection);

		$arSection["ITEMS"] = array();
		while($obElement = $rsElements->GetNextElement())
		{
			$arItem = $obElement->GetFields();

			$arItem['ACTIVE_FROM'] = $arItem['DATE_ACTIVE_FROM'];
			$arItem['ACTIVE_TO'] = $arItem['DATE_ACTIVE_TO'];

			$arButtons = CIBlock::GetPanelButtons(
				$arItem["IBLOCK_ID"],
				$arItem["ID"],
				$arSection["ID"],
				array("SECTION_BUTTONS"=>false, "SESSID"=>false, "CATALOG"=>true)
			);
			$arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
			$arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

			\Bitrix\Iblock\InheritedProperty\ElementValues::queue($arItem["IBLOCK_ID"], $arItem["ID"]);

			$arItem["PROPERTIES"] = array();
			if ($bGetProperties)
				$arItem["PROPERTIES"] = $obElement->GetProperties();

			$arItem["DISPLAY_PROPERTIES"] = array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if((is_array($prop["VALUE"]) && count($prop["VALUE"])>0) ||
				(!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0))
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "catalog_out");
				}
			}

			if ($bGetProductProperties)
			{
				$arItem["PRODUCT_PROPERTIES"] = CIBlockPriceTools::GetProductProperties(
					$arParams["IBLOCK_ID"],
					$arItem["ID"],
					$arParams["PRODUCT_PROPERTIES"],
					$arItem["PROPERTIES"]
				);
			}

			if($arParams["USE_PRICE_COUNT"])
			{
				if($bCatalog)
				{
					$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arItem["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);
					if (isset($arItem["PRICE_MATRIX"]["COLS"]) && is_array($arItem["PRICE_MATRIX"]["COLS"]))
					{
						foreach($arItem["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
							$arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialcharsbx($arColumn["NAME_LANG"]);
					}
				}
				else
				{
					$arItem["PRICE_MATRIX"] = false;
				}
				$arItem["PRICES"] = array();
			}
			else
			{
				$arItem["PRICE_MATRIX"] = false;
				$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem, $arParams['PRICE_VAT_INCLUDE'], $arConvertParams);
			}
			$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem);

			$arItem["BUY_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			$arItem["ADD_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			$arItem["COMPARE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("action=ADD_TO_COMPARE_LIST&id=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));

			if ('Y' == $arParams['CONVERT_CURRENCY'])
			{
				if ($arParams["USE_PRICE_COUNT"])
				{
					if (is_array($arItem["PRICE_MATRIX"]) && !empty($arItem["PRICE_MATRIX"]))
					{
						if (isset($arItem["PRICE_MATRIX"]['CURRENCY_LIST']) && is_array($arItem["PRICE_MATRIX"]['CURRENCY_LIST']))
						{
							//TODO: replace this code after catalog 15.5.4
							foreach ($arItem['PRICE_MATRIX']['CURRENCY_LIST'] as $oneCurrency)
								$currencyList[$oneCurrency] = $oneCurrency;
							unset($oneCurrency);
						}
					}
				}
				else
				{
					if (!empty($arItem["PRICES"]))
					{
						foreach ($arItem["PRICES"] as &$arOnePrices)
						{
							if (isset($arOnePrices['ORIG_CURRENCY']))
								$currencyList[$arOnePrices['ORIG_CURRENCY']] = $arOnePrices['ORIG_CURRENCY'];
						}
						unset($arOnePrices);
					}
				}
			}

			$arSection["ITEMS"][]=$arItem;
		}
		$arResult["SECTIONS"][]=$arSection;
		if(count($arResult["SECTIONS"])>=$arParams["SECTION_COUNT"])
			break;
	}

	foreach ($arResult["SECTIONS"] as &$arSection)
	{
		$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arSection["IBLOCK_ID"], $arSection["ID"]);
		$arSection["IPROPERTY_VALUES"] = $ipropValues->getValues();

		\Bitrix\Iblock\Component\Tools::getFieldImageData(
			$arSection,
			array('PICTURE', 'DETAIL_PICTURE'),
			\Bitrix\Iblock\Component\Tools::IPROPERTY_ENTITY_SECTION,
			'IPROPERTY_VALUES'
		);

		foreach($arSection["ITEMS"] as &$arItem)
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
			$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();
			\Bitrix\Iblock\Component\Tools::getFieldImageData(
				$arItem,
				array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
				\Bitrix\Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
				'IPROPERTY_VALUES'
			);
		}
		unset($arItem);
	}
	unset($arSection);

	if (
		'Y' == $arParams['CONVERT_CURRENCY']
		&& !empty($currencyList)
		&& defined("BX_COMP_MANAGED_CACHE")
	)
	{
		$currencyList[$arConvertParams['CURRENCY_ID']] = $arConvertParams['CURRENCY_ID'];
		foreach ($currencyList as &$oneCurrency)
			$CACHE_MANAGER->RegisterTag('currency_id_'.$oneCurrency);
		unset($oneCurrency);
	}
	unset($currencyList);

	$this->SetResultCacheKeys(array(
	));
	$this->IncludeComponentTemplate();
}
