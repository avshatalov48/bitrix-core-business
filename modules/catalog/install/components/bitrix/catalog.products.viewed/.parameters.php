<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arCurrentValues */

use Bitrix\Main\Loader;
use Bitrix\Currency;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog'))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();
$arIBlock = array();
$iblockFilter = !empty($arCurrentValues['IBLOCK_TYPE'])
	? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
	: array('ACTIVE' => 'Y');

$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
{
	$arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
}
unset($arr, $rsIBlock, $iblockFilter);

$arPrice = CCatalogIBlockParameters::getPriceTypesList();
$arAscDesc = array(
	'asc' => GetMessage('IBLOCK_SORT_ASC'),
	'desc' => GetMessage('IBLOCK_SORT_DESC'),
);

$singleIblockMode = !isset($arCurrentValues['IBLOCK_MODE']) || $arCurrentValues['IBLOCK_MODE'] === 'single';
$showFromSection = $singleIblockMode && isset($arCurrentValues['SHOW_FROM_SECTION']) && $arCurrentValues['SHOW_FROM_SECTION'] === 'Y';

$arComponentParameters = array(
	'GROUPS' => array(
		'ACTION_SETTINGS' => array(
			'NAME' => GetMessage('IBLOCK_ACTIONS')
		),
		'PRICES' => array(
			'NAME' => GetMessage('IBLOCK_PRICES'),
		),
		'BASKET' => array(
			'NAME' => GetMessage('IBLOCK_BASKET'),
		),
		'COMPARE' => array(
			'NAME' => GetMessage('IBLOCK_COMPARE')
		),
		'ANALYTICS_SETTINGS' => array(
			'NAME' => GetMessage('ANALYTICS_SETTINGS'),
			'SORT' => 11000
		)
	),
	'PARAMETERS' => array(
		'IBLOCK_MODE' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_IBLOCK_MODE'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'single' => GetMessage('CP_CPV_IBLOCK_MODE_SINGLE'),
				'multi' => GetMessage('CP_CPV_IBLOCK_MODE_MULTI')
			),
			'DEFAULT' => 'single',
			'REFRESH' => 'Y',
		),
		'IBLOCK_TYPE' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IBLOCK_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlockType,
			'REFRESH' => 'Y',
			'HIDDEN' => $singleIblockMode ? 'N' : 'Y'
		),
		'IBLOCK_ID' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IBLOCK_IBLOCK'),
			'TYPE' => 'LIST',
			'ADDITIONAL_VALUES' => 'Y',
			'VALUES' => $arIBlock,
			'REFRESH' => 'Y',
			'HIDDEN' => $singleIblockMode ? 'N' : 'Y'
		),
		'SHOW_FROM_SECTION' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_SHOW_FROM_SECTION'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
			'HIDDEN' => $singleIblockMode ? 'N' : 'Y'
		),
		'SECTION_ID' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_SECTION_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '={$GLOBALS["CATALOG_CURRENT_SECTION_ID"]}',
			'HIDDEN' => $showFromSection ? 'N' : 'Y'
		),
		'SECTION_CODE' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_SECTION_CODE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
			'HIDDEN' => $showFromSection ? 'N' : 'Y'
		),
		'SECTION_ELEMENT_ID' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_SECTION_ELEMENT_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '={$GLOBALS["CATALOG_CURRENT_ELEMENT_ID"]}',
			'HIDDEN' => $showFromSection ? 'N' : 'Y'
		),
		'SECTION_ELEMENT_CODE' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_SECTION_ELEMENT_CODE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
			'HIDDEN' => $showFromSection ? 'N' : 'Y'
		),
		'DEPTH' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_DEPTH'),
			'TYPE' => 'STRING',
			'DEFAULT' => '2',
			'HIDDEN' => $showFromSection ? 'N' : 'Y'
		),
		'HIDE_NOT_AVAILABLE' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'N',
			'VALUES' => array(
				'Y' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE_HIDE'),
				'L' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE_LAST'),
				'N' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE_SHOW')
			)
		),
		'HIDE_NOT_AVAILABLE_OFFERS' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE_OFFERS'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'N',
			'VALUES' => array(
				'Y' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE_OFFERS_HIDE'),
				'L' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE_OFFERS_SUBSCRIBE'),
				'N' => GetMessage('CP_CPV_HIDE_NOT_AVAILABLE_OFFERS_SHOW')
			)
		),
		'PAGE_ELEMENT_COUNT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('IBLOCK_PAGE_ELEMENT_COUNT'),
			'TYPE' => 'STRING',
			'HIDDEN' => 'Y',
			'DEFAULT' => '9'
		),
		'PRICE_CODE' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_PRICE_CODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arPrice,
		),
		'USE_PRICE_COUNT' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_USE_PRICE_COUNT'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'SHOW_PRICE_COUNT' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_SHOW_PRICE_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '1',
		),
		'PRICE_VAT_INCLUDE' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_VAT_INCLUDE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'CONVERT_CURRENCY' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_CPV_CONVERT_CURRENCY'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
		'BASKET_URL' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('IBLOCK_BASKET_URL'),
			'TYPE' => 'STRING',
			'DEFAULT' => '/personal/basket.php',
		),
		'ACTION_VARIABLE' => array(
			'PARENT' => 'ACTION_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ACTION_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'action_cpv',
		),
		'PRODUCT_ID_VARIABLE' => array(
			'PARENT' => 'ACTION_SETTINGS',
			'NAME' => GetMessage('IBLOCK_PRODUCT_ID_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'id',
		),
		'USE_PRODUCT_QUANTITY' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_CPV_USE_PRODUCT_QUANTITY'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
		'PRODUCT_QUANTITY_VARIABLE' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_CPV_PRODUCT_QUANTITY_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'quantity',
			'HIDDEN' => (isset($arCurrentValues['USE_PRODUCT_QUANTITY']) && $arCurrentValues['USE_PRODUCT_QUANTITY'] === 'Y' ? 'N' : 'Y')
		),
		'ADD_PROPERTIES_TO_BASKET' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_CPV_ADD_PROPERTIES_TO_BASKET'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y'
		),
		'PRODUCT_PROPS_VARIABLE' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_CPV_PRODUCT_PROPS_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'prop',
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] === 'N' ? 'Y' : 'N')
		),
		'PARTIAL_PRODUCT_PROPERTIES' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_CPV_PARTIAL_PRODUCT_PROPERTIES'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] === 'N' ? 'Y' : 'N')
		),
		'CACHE_TIME' => array('DEFAULT' => 3600),
		'CACHE_GROUPS' => array(
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('CP_CPV_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		)
	),
);

if (isset($arCurrentValues['CONVERT_CURRENCY']) && $arCurrentValues['CONVERT_CURRENCY'] === 'Y')
{
	$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_CPV_CURRENCY_ID'),
		'TYPE' => 'LIST',
		'VALUES' => Currency\CurrencyManager::getCurrencyList(),
		'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
		'ADDITIONAL_VALUES' => 'Y',
	);
}

if (
	$singleIblockMode && isset($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0
	|| !$singleIblockMode
)
{
	$iblockMap = array();
	$iblockFilter = array('ACTIVE' => 'Y');
	if ($singleIblockMode)
	{
		$catalogInfo = CCatalogSku::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
		$iblockFilter['ID'] = array($catalogInfo['IBLOCK_ID'], $catalogInfo['PRODUCT_IBLOCK_ID']);
	}

	$iblockIterator = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
	while ($iblock = $iblockIterator->fetch())
	{
		$iblockMap[$iblock['ID']] = $iblock;
	}

	$catalogs = array();
	$productsCatalogs = array();
	$skuCatalogs = array();
	$catalogIterator = CCatalog::GetList(
		array('IBLOCK_ID' => 'ASC'),
		array('@IBLOCK_ID' => array_keys($iblockMap)),
		false,
		false,
		array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID')
	);
	while ($catalog = $catalogIterator->fetch())
	{
		$isOffersCatalog = (int)$catalog['PRODUCT_IBLOCK_ID'] > 0;
		if ($isOffersCatalog)
		{
			$skuCatalogs[$catalog['PRODUCT_IBLOCK_ID']] = $catalog;

			if (!isset($productsCatalogs[$catalog['PRODUCT_IBLOCK_ID']]))
			{
				$productsCatalogs[$catalog['PRODUCT_IBLOCK_ID']] = $catalog;
			}
		}
		else
		{
			$productsCatalogs[$catalog['IBLOCK_ID']] = $catalog;
		}
	}

	foreach ($productsCatalogs as $catalog)
	{
		if ($singleIblockMode)
		{
			$catalog['VISIBLE'] = !empty($catalogInfo);
		}
		else
		{
			$catalog['VISIBLE'] = isset($arCurrentValues['SHOW_PRODUCTS_'.$catalog['IBLOCK_ID']])
				&& $arCurrentValues['SHOW_PRODUCTS_'.$catalog['IBLOCK_ID']] === 'Y';
		}

		$catalogs[] = $catalog;

		if (isset($skuCatalogs[$catalog['IBLOCK_ID']]))
		{
			$skuCatalogs[$catalog['IBLOCK_ID']]['VISIBLE'] = $catalog['VISIBLE'];
			$catalogs[] = $skuCatalogs[$catalog['IBLOCK_ID']];
		}
	}

	$defaultListValues = array('-' => GetMessage('CP_CPV_UNDEFINED'));
	foreach ($catalogs as $catalog)
	{
		$catalogs[$catalog['IBLOCK_ID']] = $catalog;
		$iblock = $iblockMap[$catalog['IBLOCK_ID']];

		// sku
		if ((int)$catalog['SKU_PROPERTY_ID'] > 0)
		{
			$groupName = sprintf(GetMessage('CP_CPV_GROUP_OFFERS_CATALOG_PARAMS'), $iblock['NAME']);
		}
		else
		{
			$groupName = sprintf(GetMessage('CP_CPV_GROUP_PRODUCT_CATALOG_PARAMS'), $iblock['NAME']);
		}

		$groupId = 'CATALOG_PARAMS_'.$iblock['ID'];
		$arComponentParameters['GROUPS'][$groupId] = array(
			'NAME' => $groupName
		);

		// Params in group
		// 1. Display Properties
		$listProperties = array();
		$allProperties = array();
		$fileProperties = array();
		$treeProperties = array();

		$propertyIterator = CIBlockProperty::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			array('IBLOCK_ID' => $iblock['ID'], 'ACTIVE' => 'Y')
		);
		while ($property = $propertyIterator->fetch())
		{
			$property['ID'] = (int)$property['ID'];
			$propertyName = '['.$property['ID'].']'.('' != $property['CODE'] ? '['.$property['CODE'].']' : '').' '.$property['NAME'];

			if ($property['CODE'] == '')
			{
				$property['CODE'] = $property['ID'];
			}

			$allProperties[$property['CODE']] = $propertyName;

			if ($property['PROPERTY_TYPE'] === 'F')
			{
				$fileProperties[$property['CODE']] = $propertyName;
			}

			if ($property['PROPERTY_TYPE'] === 'L')
			{
				$listProperties[$property['CODE']] = $propertyName;
			}

			// skip property id
			if ($property['ID'] == $catalog['SKU_PROPERTY_ID'])
				continue;

			if (
				$property['PROPERTY_TYPE'] === 'L'
				|| $property['PROPERTY_TYPE'] === 'E'
				|| ($property['PROPERTY_TYPE'] === 'S' && $property['USER_TYPE'] === 'directory')
			)
			{
				$treeProperties[$property['CODE']] = $propertyName;
			}
		}

		// Properties
		// Common Catalog options
		if (!$singleIblockMode && (int)$catalog['SKU_PROPERTY_ID'] <= 0)
		{
			$arComponentParameters['PARAMETERS']['SHOW_PRODUCTS_'.$iblock['ID']] = array(
				'PARENT' => $groupId,
				'NAME' => GetMessage('CP_CPV_SHOW_PRODUCTS'),
				'TYPE' => 'CHECKBOX',
				'REFRESH' => 'Y',
				'DEFAULT' => 'N'
			);
		}

		$arComponentParameters['PARAMETERS']['PROPERTY_CODE_'.$iblock['ID']] = array(
			'PARENT' => $groupId,
			'NAME' => GetMessage('CP_CPV_PROPERTY_DISPLAY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'REFRESH' => 'Y',
			'VALUES' => $allProperties,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => '',
			'HIDDEN' => !$catalog['VISIBLE'] ? 'Y' : 'N'
		);

		// hack for correct sort
		if ((int)$catalog['SKU_PROPERTY_ID'] <= 0 && isset($templateProperties['PROPERTY_CODE_MOBILE_'.$iblock['ID']]))
		{
			$arComponentParameters['PARAMETERS']['PROPERTY_CODE_MOBILE_'.$iblock['ID']] = $templateProperties['PROPERTY_CODE_MOBILE_'.$iblock['ID']];
			unset($templateProperties['PROPERTY_CODE_MOBILE_'.$iblock['ID']]);
		}

		// 3. Cart properties
		$arComponentParameters['PARAMETERS']['CART_PROPERTIES_'.$iblock['ID']] = array(
			'PARENT' => $groupId,
			'NAME' => GetMessage('CP_CPV_PROPERTY_ADD_TO_BASKET'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $treeProperties,
			'ADDITIONAL_VALUES' => 'Y',
			'HIDDEN' => ((isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) &&
				$arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N') || !$catalog['VISIBLE'] ? 'Y' : 'N')
		);

		// 2. Additional Image
		$arComponentParameters['PARAMETERS']['ADDITIONAL_PICT_PROP_'.$iblock['ID']] = array(
			'PARENT' => $groupId,
			'NAME' => GetMessage('CP_CPV_ADDITIONAL_IMAGE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => array_merge($defaultListValues, $fileProperties),
			'ADDITIONAL_VALUES' => 'N',
			'DEFAULT' => '-',
			'HIDDEN' => !$catalog['VISIBLE'] ? 'Y' : 'N'
		);

		if ((int)$catalog['SKU_PROPERTY_ID'] > 0)
		{
			$arComponentParameters['PARAMETERS']['OFFER_TREE_PROPS_'.$iblock['ID']] = array(
				'PARENT' => $groupId,
				'NAME' => GetMessage('CP_CPV_PROPERTY_GROUP'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'VALUES' => array_merge($defaultListValues, $treeProperties),
				'ADDITIONAL_VALUES' => 'N',
				'DEFAULT' => '-',
				'HIDDEN' => !$catalog['VISIBLE'] ? 'Y' : 'N'
			);
		}
		else
		{
			$arComponentParameters['PARAMETERS']['LABEL_PROP_'.$iblock['ID']] = array(
				'PARENT' => $groupId,
				'NAME' => GetMessage('CP_CPV_PROPERTY_LABEL'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'ADDITIONAL_VALUES' => 'N',
				'REFRESH' => 'Y',
				'VALUES' => array_merge($defaultListValues, $listProperties),
				'HIDDEN' => !$catalog['VISIBLE'] ? 'Y' : 'N'
			);

			// hack for correct sort
			if (isset($templateProperties['LABEL_PROP_MOBILE_'.$iblock['ID']]))
			{
				$arComponentParameters['PARAMETERS']['LABEL_PROP_MOBILE_'.$iblock['ID']] = $templateProperties['LABEL_PROP_MOBILE_'.$iblock['ID']];
				unset($templateProperties['LABEL_PROP_MOBILE_'.$iblock['ID']]);
			}
		}
	}
}

$arComponentParameters['PARAMETERS']['DISPLAY_COMPARE'] = array(
	'PARENT' => 'COMPARE',
	'NAME' => GetMessage('CP_CPV_DISPLAY_COMPARE'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['DISPLAY_COMPARE']) && $arCurrentValues['DISPLAY_COMPARE'] === 'Y')
{
	$arComponentParameters['PARAMETERS']['COMPARE_PATH'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => GetMessage('CP_CPV_COMPARE_PATH'),
		'TYPE' => 'STRING',
		'DEFAULT' => ''
	);
}