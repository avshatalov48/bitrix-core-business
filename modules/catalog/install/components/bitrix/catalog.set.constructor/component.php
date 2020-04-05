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

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Application,
	Bitrix\Catalog;

if (!CBXFeatures::IsFeatureEnabled('CatCompleteSet'))
	return;

$arParams['IBLOCK_ID'] = isset($arParams['IBLOCK_ID']) ? (int)$arParams['IBLOCK_ID'] : 0;
if ($arParams['IBLOCK_ID'] <= 0)
	return;

if (!isset($arParams["BASKET_URL"]))
	$arParams["BASKET_URL"] = '/personal/cart/';
if ('' == trim($arParams["BASKET_URL"]))
	$arParams["BASKET_URL"] = '/personal/cart/';

if (!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams['CACHE_GROUPS'] = trim($arParams['CACHE_GROUPS']);
if ('N' != $arParams['CACHE_GROUPS'])
	$arParams['CACHE_GROUPS'] = 'Y';

$elementID = intval($arParams["ELEMENT_ID"]);
if (!$elementID)
{
	ShowError(GetMessage("EMPTY_ELEMENT_ERROR"));
	return;
}

if (!is_array($arParams["OFFERS_CART_PROPERTIES"]))
	$arParams["OFFERS_CART_PROPERTIES"] = array();
foreach($arParams["OFFERS_CART_PROPERTIES"] as $i => $pid)
	if ($pid === "")
		unset($arParams["OFFERS_CART_PROPERTIES"][$i]);

$arParams['BUNDLE_ITEMS_COUNT'] = (isset($arParams['BUNDLE_ITEMS_COUNT']) ? (int)$arParams['BUNDLE_ITEMS_COUNT'] : 3);
if ($arParams['BUNDLE_ITEMS_COUNT'] < 1)
	$arParams['BUNDLE_ITEMS_COUNT'] = 3;

if($this->startResultCache(false, array($elementID, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()))))
{
	if (!Loader::includeModule('catalog'))
	{
		ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALLED"));
		$this->abortResultCache();
		return;
	}
	$isProductHaveSet = CCatalogProductSet::isProductHaveSet($elementID, CCatalogProductSet::TYPE_GROUP);
	$product = false;
	if (!$isProductHaveSet)
	{
		$product = CCatalogSku::GetProductInfo($elementID, $arParams['IBLOCK_ID']);
		if (!empty($product))
		{
			$isProductHaveSet = CCatalogProductSet::isProductHaveSet($product['ID'], CCatalogProductSet::TYPE_GROUP);
			if (!$isProductHaveSet)
				$product = false;
		}
	}
	if (!$isProductHaveSet)
	{
		$this->abortResultCache();
		return;
	}

	if (!empty($product))
	{
		$arResult['PRODUCT_ID'] = $product['ID'];
		$arResult['PRODUCT_IBLOCK_ID'] = $product['IBLOCK_ID'];
		$arResult['ELEMENT_ID'] = $elementID;
		$arResult['ELEMENT_IBLOCK_ID'] = $arParams['IBLOCK_ID'];
	}
	else
	{
		$arResult['PRODUCT_ID'] = $elementID;
		$arResult['PRODUCT_IBLOCK_ID'] = $arParams['IBLOCK_ID'];
		$arResult['ELEMENT_ID'] = $elementID;
		$arResult['ELEMENT_IBLOCK_ID'] = $arParams['IBLOCK_ID'];
	}

	$arParams['CONVERT_CURRENCY'] = (isset($arParams['CONVERT_CURRENCY']) && 'Y' == $arParams['CONVERT_CURRENCY'] ? 'Y' : 'N');
	$arParams['CURRENCY_ID'] = trim(strval($arParams['CURRENCY_ID']));
	if ($arParams['CURRENCY_ID'] == '')
		$arParams['CONVERT_CURRENCY'] = 'N';
	elseif ($arParams['CONVERT_CURRENCY'] == 'N')
		$arParams['CURRENCY_ID'] = '';

	$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

	$arConvertParams = array();
	if ($arParams['CONVERT_CURRENCY'] == 'Y')
	{
		if (!Loader::includeModule('currency'))
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

	$currentSet = false;
	$productLink = array();
	$allSets = CCatalogProductSet::getAllSetsByProduct($arResult['PRODUCT_ID'], CCatalogProductSet::TYPE_GROUP);
	foreach ($allSets as &$oneSet)
	{
		if ($oneSet['ACTIVE'] == 'Y')
		{
			$currentSet = $oneSet;
			break;
		}
	}
	unset($oneSet, $allSets);
	if (empty($currentSet))
	{
		$this->abortResultCache();
		return;
	}

	Main\Type\Collection::sortByColumn($currentSet['ITEMS'], array('SORT' => SORT_ASC), '', null, true);

	$arSetItemsID = array($arResult['ELEMENT_ID']);
	$productQuantity = array(
		$arResult['ELEMENT_ID'] => 1
	);
	foreach ($currentSet['ITEMS'] as $index => $item)
	{
		$id = $item['ITEM_ID'];
		$arSetItemsID[] = $id;
		$productLink[$id] = $index;
		$productQuantity[$id] = $item['QUANTITY'];
		unset($id);
	}
	unset($index, $item);

	$countSetDefaultItems = 0;

	$select = array(
		'ID',
		'NAME',
		'CODE',
		'IBLOCK_ID',
		'IBLOCK_SECTION_ID',
		'DETAIL_PAGE_URL',
		'PREVIEW_PICTURE',
		'DETAIL_PICTURE',
		'PREVIEW_TEXT',
		'CATALOG_AVAILABLE',
		'CATALOG_MEASURE'
	);
	$filter = array(
		'ID' => $arSetItemsID,
		'IBLOCK_LID' => SITE_ID,
		'ACTIVE_DATE' => 'Y',
		'ACTIVE' => 'Y',
		'CHECK_PERMISSIONS' => 'Y',
		'MIN_PERMISSION' => 'R'
	);
	$arResult['PRICES'] = \CIBlockPriceTools::GetCatalogPrices($arResult['PRODUCT_IBLOCK_ID'], $arParams['PRICE_CODE']);
	$allowPriceTypes = \CIBlockPriceTools::GetAllowCatalogPrices($arResult['PRICES']);

	$arResult["SET_ITEMS"]["DEFAULT"] = array();
	$arResult["SET_ITEMS"]["OTHER"] = array();
	$arResult["SET_ITEMS"]["PRICE"] = 0;
	$arResult["SET_ITEMS"]["OLD_PRICE"] = 0;
	$arResult["SET_ITEMS"]["PRICE_DISCOUNT_DIFFERENCE"] = 0;

	$arResult['ITEMS_RATIO'] = array_fill_keys($arSetItemsID, 1);
	$ratioResult = Catalog\ProductTable::getCurrentRatioWithMeasure($arSetItemsID);
	foreach ($ratioResult as $ratioProduct => $ratioData)
	{
		$arResult['ITEMS_RATIO'][$ratioProduct] = $ratioData['RATIO'];
		$productQuantity[$ratioProduct] *= $ratioData['RATIO'];
	}
	unset($ratioProduct, $ratioData);

	$tagIblockList = array();
	$tagIblockList[$arResult['PRODUCT_IBLOCK_ID']] = $arResult['PRODUCT_IBLOCK_ID'];
	$tagIblockList[$arResult['ELEMENT_IBLOCK_ID']] = $arResult['ELEMENT_IBLOCK_ID'];
	$tagCurrencyList = array();

	$foundMain = false;
	$itemsList = array();
	$offerList = array();
	$itemsIterator = CIBlockElement::GetList(
		array(),
		$filter,
		false,
		false,
		$select
	);
	while ($item = $itemsIterator->GetNext())
	{
		if (
			$item['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_PRODUCT
			&& $item['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SET
			&& $item['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_OFFER
		)
			continue;

		$item['ID'] = (int)$item['ID'];
		$item['IBLOCK_ID'] = (int)$item['IBLOCK_ID'];
		$itemsList[$item['ID']] = $item;
		if ($item['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_OFFER)
			$offerList[$item['ID']] = $item['ID'];
		if ($item['ID'] == $arResult['ELEMENT_ID'])
			$foundMain = true;
	}
	unset($select, $item, $itemsIterator);
	if (!$foundMain || count($itemsList) < 2)
	{
		$this->abortResultCache();
		return;
	}

	if (!empty($offerList))
	{
		$parents = CCatalogSku::getProductList($offerList);
		if (!empty($parents) && is_array($parents))
		{
			$offersMap = array();
			foreach ($parents as $offerId => $parentData)
			{
				$parentId = $parentData['ID'];
				if (!isset($offersMap[$parentId]))
					$offersMap[$parentId] = array();
				$offersMap[$parentId][$offerId] = $offerId;
			}
			unset($offerId, $parentData);
			$iterator = CIBlockElement::GetList(
				array(),
				array(
					'ID' => array_keys($offersMap),
					'IBLOCK_LID' => SITE_ID,
					'ACTIVE_DATE' => 'Y',
					'ACTIVE' => 'Y',
					'CHECK_PERMISSIONS' => 'Y',
					'MIN_PERMISSION' => 'R'
				),
				false,
				false,
				array('ID', 'IBLOCK_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
			);
			while ($row = $iterator->Fetch())
			{
				$row['ID'] = (int)$row['ID'];
				foreach ($offersMap[$row['ID']] as $itemId)
				{
					unset($offerList[$itemId]);
					if ($itemsList[$itemId]['PREVIEW_PICTURE'] === null)
						$itemsList[$itemId]['PREVIEW_PICTURE'] = $row['PREVIEW_PICTURE'];
					if ($itemsList[$itemId]['DETAIL_PICTURE'] === null)
						$itemsList[$itemId]['DETAIL_PICTURE'] = $row['DETAIL_PICTURE'];
				}
				unset($itemId);
			}
			unset($row, $iterator);
			unset($offersMap);
		}

		if (!empty($offerList))
		{
			foreach ($offerList as $clearId)
				unset($itemsList[$clearId]);
			unset($clearId);
		}
	}
	if (empty($itemsList))
	{
		$this->abortResultCache();
		return;
	}

	foreach ($itemsList as $item)
		$tagIblockList[$item['IBLOCK_ID']] = $item['IBLOCK_ID'];
	unset($item);

	if (!empty($allowPriceTypes))
	{
		$prices = array();
		$iterator = Catalog\PriceTable::getList(array(
			'select' => array(
				'ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY',
				'QUANTITY_FROM', 'QUANTITY_TO',
				'EXTRA_ID'
			),
			'filter' => array('@PRODUCT_ID' => array_keys($itemsList), '@CATALOG_GROUP_ID' => $allowPriceTypes),
			'order' => array('PRODUCT_ID' => 'ASC', 'CATALOG_GROUP_ID' => 'ASC')
		));
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['PRODUCT_ID'];
			$rawPrice = array();
			if ($row['QUANTITY_FROM'] !== null || $row['QUANTITY_TO'] !== null)
			{
				if (
					($row['QUANTITY_FROM'] === null || (int)$row['QUANTITY_FROM'] <= $productQuantity[$id])
					&& ($row['QUANTITY_TO'] === null || (int)$row['QUANTITY_TO'] >= $productQuantity[$id])
				)
					$rawPrice = $row;
			}
			else
			{
				$rawPrice = $row;
			}
			if (!empty($rawPrice))
			{
				$priceType = $rawPrice['CATALOG_GROUP_ID'];
				$itemsList[$id]['CATALOG_PRICE_ID_'.$priceType] = $rawPrice['ID'];
				$itemsList[$id]['~CATALOG_PRICE_ID_'.$priceType] = $rawPrice['ID'];
				$itemsList[$id]['CATALOG_PRICE_'.$priceType] = $rawPrice['PRICE'];
				$itemsList[$id]['~CATALOG_PRICE_'.$priceType] = $rawPrice['PRICE'];
				$itemsList[$id]['CATALOG_CURRENCY_'.$priceType] = $rawPrice['CURRENCY'];
				$itemsList[$id]['~CATALOG_CURRENCY_'.$priceType] = $rawPrice['CURRENCY'];
				$itemsList[$id]['CATALOG_QUANTITY_FROM_'.$priceType] = $rawPrice['QUANTITY_FROM'];
				$itemsList[$id]['~CATALOG_QUANTITY_FROM_'.$priceType] = $rawPrice['QUANTITY_FROM'];
				$itemsList[$id]['CATALOG_QUANTITY_TO_'.$priceType] = $rawPrice['QUANTITY_TO'];
				$itemsList[$id]['~CATALOG_QUANTITY_TO_'.$priceType] = $rawPrice['QUANTITY_TO'];
				$itemsList[$id]['CATALOG_EXTRA_ID_'.$priceType] = $rawPrice['EXTRA_ID'];
				$itemsList[$id]['~CATALOG_EXTRA_ID_'.$priceType] = $rawPrice['EXTRA_ID'];

				$tagCurrencyList[$rawPrice['CURRENCY']] = $rawPrice['CURRENCY'];
				unset($priceType);
			}
			unset($rawPrice, $id);
		}
		unset($row, $iterator);
	}

	$item = $itemsList[$arResult['ELEMENT_ID']];
	$priceList = \CIBlockPriceTools::GetItemPrices(
		$item['IBLOCK_ID'],
		$arResult['PRICES'],
		$item,
		$arParams['PRICE_VAT_INCLUDE'],
		$arConvertParams
	);
	if (empty($priceList))
	{
		$this->abortResultCache();
		return;
	}
	$minimalPrice = \CIBlockPriceTools::getMinPriceFromList($priceList);
	if (empty($minimalPrice))
	{
		$this->abortResultCache();
		return;
	}
	else
	{
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_CURRENCY'] = $minimalPrice['CURRENCY'];
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_DISCOUNT_VALUE'] = $minimalPrice['DISCOUNT_VALUE'];
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_PRINT_DISCOUNT_VALUE'] = $minimalPrice['PRINT_DISCOUNT_VALUE'];
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_VALUE'] = $minimalPrice['VALUE'];
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_PRINT_VALUE'] = $minimalPrice['PRINT_VALUE'];
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_DISCOUNT_DIFFERENCE_VALUE'] = $minimalPrice['DISCOUNT_DIFF'];
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_DISCOUNT_DIFFERENCE'] = $minimalPrice['PRINT_DISCOUNT_DIFF'];
		$itemsList[$arResult['ELEMENT_ID']]['PRICE_DISCOUNT_PERCENT'] = $minimalPrice['DISCOUNT_DIFF_PERCENT'];

		if ($arParams['CONVERT_CURRENCY'] == 'N')
		{
			$arConvertParams['CONVERT_CURRENCY'] = 'Y';
			$arConvertParams['CURRENCY_ID'] = $minimalPrice['CURRENCY'];
		}
	}
	unset($minimalPrice, $priceList, $item);

	if ($arConvertParams['CURRENCY_ID'] !== '')
		$tagCurrencyList[$arConvertParams['CURRENCY_ID']] = $arConvertParams['CURRENCY_ID'];

	foreach ($itemsList as $item)
	{
		if ($item['ID'] != $arResult['ELEMENT_ID'])
		{
			$priceList = \CIBlockPriceTools::GetItemPrices(
				$item['IBLOCK_ID'],
				$arResult['PRICES'],
				$item,
				$arParams['PRICE_VAT_INCLUDE'],
				$arConvertParams
			);
			if (empty($priceList))
				continue;

			$minimalPrice = \CIBlockPriceTools::getMinPriceFromList($priceList);
			if (!empty($minimalPrice))
			{
				$item['PRICE_CURRENCY'] = $minimalPrice['CURRENCY'];
				$item['PRICE_DISCOUNT_VALUE'] = $minimalPrice['DISCOUNT_VALUE'];
				$item['PRICE_PRINT_DISCOUNT_VALUE'] = $minimalPrice['PRINT_DISCOUNT_VALUE'];
				$item['PRICE_VALUE'] = $minimalPrice['VALUE'];
				$item['PRICE_PRINT_VALUE'] = $minimalPrice['PRINT_VALUE'];
				$item['PRICE_DISCOUNT_DIFFERENCE_VALUE'] = $minimalPrice['DISCOUNT_DIFF'];
				$item['PRICE_DISCOUNT_DIFFERENCE'] = $minimalPrice['PRINT_DISCOUNT_DIFF'];
				$item['PRICE_DISCOUNT_PERCENT'] = $minimalPrice['DISCOUNT_DIFF_PERCENT'];
			}
			unset($minimalPrice);
		}

		$item['CAN_BUY'] = CIBlockPriceTools::CanBuy(
			$item['IBLOCK_ID'],
			$arResult['PRICES'],
			$item
		);

		if (isset($productLink[$item['ID']]))
		{
			$index = $productLink[$item['ID']];
			$currentSet['ITEMS'][$index]['ITEM_DATA'] = $item;
			unset($index);
		}
		elseif ($item['ID'] == $arResult['ELEMENT_ID'])
		{
			$currentSet['ITEM_DATA'] = $item;
		}
	}
	unset($item, $itemsList);
	if (empty($currentSet['ITEM_DATA']))
	{
		$this->abortResultCache();
		return;
	}
	$defaultMeasure = CCatalogMeasure::getDefaultMeasure(true, true);
	$arResult['ELEMENT'] = $currentSet['ITEM_DATA'];
	$arResult['ELEMENT']['SET_QUANTITY'] = 1;
	$arResult['ELEMENT']['MEASURE_RATIO'] = $arResult['ITEMS_RATIO'][$arResult['ELEMENT']['ID']];
	$arResult['ELEMENT']['MEASURE'] = (
		!empty($ratioResult[$arResult['ELEMENT']['ID']]['MEASURE'])
		? $ratioResult[$arResult['ELEMENT']['ID']]['MEASURE']
		: $defaultMeasure
	);
	$arResult['ELEMENT']['BASKET_QUANTITY'] = $arResult['ELEMENT']['MEASURE_RATIO'];
	$arResult['SET_ITEMS']['PRICE'] = $currentSet['ITEM_DATA']['PRICE_DISCOUNT_VALUE'];
	$arResult['SET_ITEMS']['OLD_PRICE'] = $currentSet['ITEM_DATA']['PRICE_VALUE'];
	$arResult['SET_ITEMS']['PRICE_DISCOUNT_DIFFERENCE'] = $currentSet['ITEM_DATA']['PRICE_DISCOUNT_DIFFERENCE_VALUE'];
	$arResult['BASKET_QUANTITY'] = array(
		$arResult['ELEMENT']['ID'] => $arResult['ELEMENT']['BASKET_QUANTITY']
	);

	$defaultCurrency = $arResult['ELEMENT']['PRICE_CURRENCY'];
	$compareCurrency = empty($arConvertParams) || $arConvertParams['CONVERT_CURRENCY'] == 'N';
	$found = false;
	$resort = false;
	foreach ($currentSet['ITEMS'] as &$setItem)
	{
		if (!isset($setItem['ITEM_DATA']))
			continue;

		$setItem['ITEM_DATA']['SET_QUANTITY'] = (empty($setItem['QUANTITY']) ? 1 : $setItem['QUANTITY']);
		$setItem['ITEM_DATA']['MEASURE_RATIO'] = $arResult['ITEMS_RATIO'][$setItem['ITEM_DATA']['ID']];
		$setItem['ITEM_DATA']['MEASURE'] = (
			!empty($ratioResult[$setItem['ITEM_DATA']['ID']]['MEASURE'])
			? $ratioResult[$setItem['ITEM_DATA']['ID']]['MEASURE']
			: $defaultMeasure
		);
		$setItem['ITEM_DATA']['BASKET_QUANTITY'] = $setItem['ITEM_DATA']['SET_QUANTITY']*$setItem['ITEM_DATA']['MEASURE_RATIO'];
		$arResult['BASKET_QUANTITY'][$setItem['ITEM_DATA']['ID']] = $setItem['ITEM_DATA']['BASKET_QUANTITY'];
		$setItem['ITEM_DATA']['SET_SORT'] = $setItem['SORT'];
		if ($compareCurrency && $setItem['ITEM_DATA']['PRICE_CURRENCY'] != $defaultCurrency)
		{
			$setItem['ITEM_DATA']['PRICE_CONVERT_DISCOUNT_VALUE'] = CCurrencyRates::ConvertCurrency($setItem['ITEM_DATA']['PRICE_DISCOUNT_VALUE'], $setItem['ITEM_DATA']['PRICE_CURRENCY'], $defaultCurrency);
			$setItem['ITEM_DATA']['PRICE_CONVERT_VALUE'] = CCurrencyRates::ConvertCurrency($setItem['ITEM_DATA']["PRICE_VALUE"], $setItem['ITEM_DATA']['PRICE_CURRENCY'], $defaultCurrency);
			$setItem['ITEM_DATA']['PRICE_CONVERT_DISCOUNT_DIFFERENCE_VALUE'] = CCurrencyRates::ConvertCurrency($setItem['ITEM_DATA']['PRICE_DISCOUNT_DIFFERENCE_VALUE'], $setItem['ITEM_DATA']['PRICE_CURRENCY'], $defaultCurrency);
			$setItem['ITEM_DATA']['PRICE_CURRENCY'] = $defaultCurrency;
		}
		if ($setItem['ITEM_DATA']['CAN_BUY'] && $countSetDefaultItems < $arParams['BUNDLE_ITEMS_COUNT'])
		{
			$arResult['SET_ITEMS']['DEFAULT'][] = $setItem['ITEM_DATA'];
			$arResult['SET_ITEMS']['PRICE'] += $setItem['ITEM_DATA']['PRICE_DISCOUNT_VALUE']*$setItem['ITEM_DATA']['BASKET_QUANTITY'];
			$arResult['SET_ITEMS']['OLD_PRICE'] += $setItem['ITEM_DATA']['PRICE_VALUE']*$setItem['ITEM_DATA']['BASKET_QUANTITY'];
			$arResult['SET_ITEMS']['PRICE_DISCOUNT_DIFFERENCE'] += $setItem['ITEM_DATA']['PRICE_DISCOUNT_DIFFERENCE_VALUE']*$setItem['ITEM_DATA']['BASKET_QUANTITY'];
			$countSetDefaultItems++;
		}
		else
		{
			if (!$setItem['ITEM_DATA']['CAN_BUY'])
				$resort = true;
			$arResult['SET_ITEMS']['OTHER'][] = $setItem['ITEM_DATA'];
		}
		$found = true;
	}
	unset($setItem, $currentSet);
	if (!$found || empty($arResult['SET_ITEMS']['DEFAULT']))
	{
		$this->abortResultCache();
		return;
	}
	unset($found);
	if ($resort)
		Main\Type\Collection::sortByColumn($arResult['SET_ITEMS']['OTHER'], array('CAN_BUY' => SORT_DESC, 'SET_SORT' => SORT_ASC));
	unset($resort);

	if (defined('BX_COMP_MANAGED_CACHE') && (!empty($tagIblockList) || !empty($tagCurrencyList)))
	{
		$taggedCache = Application::getInstance()->getTaggedCache();
		if (!empty($tagIblockList))
		{
			foreach ($tagIblockList as $iblock)
				$taggedCache->registerTag('iblock_id_'.$iblock);
			unset($iblock);
		}
		if (!empty($tagCurrencyList))
		{
			foreach ($tagCurrencyList as $currency)
				$taggedCache->registerTag('currency_id_'.$currency);
			unset($currency);
		}
	}

	$arResult['SHOW_DEFAULT_SET_DISCOUNT'] = true;
	if ($arResult["SET_ITEMS"]["OLD_PRICE"] && $arResult["SET_ITEMS"]["OLD_PRICE"] != $arResult["SET_ITEMS"]["PRICE"])
	{
		$arResult["SET_ITEMS"]["OLD_PRICE"] = CCurrencyLang::CurrencyFormat($arResult["SET_ITEMS"]["OLD_PRICE"], $defaultCurrency, true);
	}
	else
	{
		$arResult["SET_ITEMS"]["OLD_PRICE"] = 0;
		$arResult['SHOW_DEFAULT_SET_DISCOUNT'] = false;
	}
	if ($arResult["SET_ITEMS"]["PRICE"])
		$arResult["SET_ITEMS"]["PRICE"] = CCurrencyLang::CurrencyFormat($arResult["SET_ITEMS"]["PRICE"], $defaultCurrency, true);
	if ($arResult["SET_ITEMS"]["PRICE_DISCOUNT_DIFFERENCE"])
		$arResult["SET_ITEMS"]["PRICE_DISCOUNT_DIFFERENCE"] = CCurrencyLang::CurrencyFormat($arResult["SET_ITEMS"]["PRICE_DISCOUNT_DIFFERENCE"], $defaultCurrency, true);

	$currencyFormat = CCurrencyLang::GetFormatDescription($defaultCurrency);
	$arResult['CURRENCIES'] = array(
		array(
			'CURRENCY' => $defaultCurrency,
			'FORMAT' => array(
				'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
				'DEC_POINT' => $currencyFormat['DEC_POINT'],
				'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
				'DECIMALS' => $currencyFormat['DECIMALS'],
				'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
				'HIDE_ZERO' => $currencyFormat['HIDE_ZERO']
			)
		)
	);
	unset($currencyFormat);
	$arResult['CONVERT_CURRENCY'] = $arConvertParams;

	$this->setResultCacheKeys(array());
	$this->includeComponentTemplate();
}