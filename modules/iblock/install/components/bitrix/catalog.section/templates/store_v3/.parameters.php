<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 * @var array $arCurrentValues
 * @var array $arTemplateParameters
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;

if (!Loader::includeModule('iblock'))
	return;

$boolCatalog = Loader::includeModule('catalog');
CBitrixComponent::includeComponentClass($componentName);

$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$defaultValue = array('-' => Loc::getMessage('CP_BCS_TPL_PROP_EMPTY'));
$arSKU = false;
$boolSKU = false;
$filterDataValues = array();
if ($boolCatalog && (isset($arCurrentValues['IBLOCK_ID']) && 0 < intval($arCurrentValues['IBLOCK_ID'])))
{
	$arSKU = CCatalogSku::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
	$boolSKU = !empty($arSKU) && is_array($arSKU);
	$filterDataValues['iblockId'] = (int)$arCurrentValues['IBLOCK_ID'];
	if ($boolSKU)
	{
		$filterDataValues['offersIblockId'] = $arSKU['IBLOCK_ID'];
	}
}


$arThemes = array(
	'blue' => Loc::getMessage('CP_BCS_TPL_THEME_BLUE'),
	'green' => Loc::getMessage('CP_BCS_TPL_THEME_GREEN'),
	'red' => Loc::getMessage('CP_BCS_TPL_THEME_RED'),
	'yellow' => Loc::getMessage('CP_BCS_TPL_THEME_YELLOW')
);

if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = Loc::getMessage('CP_BCS_TPL_THEME_SITE');
}


$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCS_TPL_TEMPLATE_THEME'),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);

$arTemplateParameters['SHOW_SLIDER'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCS_TPL_SHOW_SLIDER'),
	'TYPE' => 'CHECKBOX',
	'MULTIPLE' => 'N',
	'REFRESH' => 'Y',
	'DEFAULT' => 'Y'
);

if (isset($arCurrentValues['SHOW_SLIDER']) && $arCurrentValues['SHOW_SLIDER'] === 'Y')
{
	$arTemplateParameters['SLIDER_INTERVAL'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_SLIDER_INTERVAL'),
		'TYPE' => 'TEXT',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '3000'
	);
	$arTemplateParameters['SLIDER_PROGRESS'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_SLIDER_PROGRESS'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => 'N'
	);
}

$arAllPropList = array();
$arFilePropList = $defaultValue;
$arListPropList = array();

if ($iblockExists)
{
	$rsProps = CIBlockProperty::GetList(
		array('SORT' => 'ASC', 'ID' => 'ASC'),
		array('IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'], 'ACTIVE' => 'Y')
	);
	while ($arProp = $rsProps->Fetch())
	{
		$strPropName = '['.$arProp['ID'].']'.('' != $arProp['CODE'] ? '['.$arProp['CODE'].']' : '').' '.$arProp['NAME'];

		if ($arProp['CODE'] == '')
		{
			$arProp['CODE'] = $arProp['ID'];
		}

		$arAllPropList[$arProp['CODE']] = $strPropName;

		if ($arProp['PROPERTY_TYPE'] === 'F')
		{
			$arFilePropList[$arProp['CODE']] = $strPropName;
		}

		if ($arProp['PROPERTY_TYPE'] === 'L')
		{
			$arListPropList[$arProp['CODE']] = $strPropName;
		}
	}

	$showedProperties = [];
	if ($usePropertyFeatures)
	{
		if ($iblockExists)
		{
			$showedProperties = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
				$arCurrentValues['IBLOCK_ID'],
				['CODE' => 'Y']
			);
			if ($showedProperties === null)
				$showedProperties = [];
		}
	}
	else
	{
		if (!empty($arCurrentValues['PROPERTY_CODE']) && is_array($arCurrentValues['PROPERTY_CODE']))
		{
			$showedProperties = $arCurrentValues['PROPERTY_CODE'];
		}
	}
	if (!empty($showedProperties))
	{
		$selected = array();

		foreach ($showedProperties as $code)
		{
			if (isset($arAllPropList[$code]))
			{
				$selected[$code] = $arAllPropList[$code];
			}
		}

		$arTemplateParameters['PROPERTY_CODE_MOBILE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('CP_BCS_TPL_PROPERTY_CODE_MOBILE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $selected
		);
	}
	unset($showedProperties);

	if ($boolSKU)
	{
		$arTemplateParameters['PRODUCT_DISPLAY_MODE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('CP_BCS_TPL_PRODUCT_DISPLAY_MODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'Y',
			'DEFAULT' => 'N',
			'VALUES' => array(
				'N' => Loc::getMessage('CP_BCS_TPL_DML_SIMPLE'),
				'Y' => Loc::getMessage('CP_BCS_TPL_DML_EXT')
			)
		);
	}

	$arTemplateParameters['ADD_PICT_PROP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_ADD_PICT_PROP'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '-',
		'VALUES' => $arFilePropList
	);

	$arTemplateParameters['LABEL_PROP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_LABEL_PROP'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'Y',
		'VALUES' => $arListPropList
	);

	if (isset($arCurrentValues['LABEL_PROP']) && !empty($arCurrentValues['LABEL_PROP']))
	{
		if (!is_array($arCurrentValues['LABEL_PROP']))
		{
			$arCurrentValues['LABEL_PROP'] = array($arCurrentValues['LABEL_PROP']);
		}

		$selected = array();
		foreach ($arCurrentValues['LABEL_PROP'] as $name)
		{
			if (isset($arListPropList[$name]))
			{
				$selected[$name] = $arListPropList[$name];
			}
		}

		$arTemplateParameters['LABEL_PROP_MOBILE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('CP_BCS_TPL_LABEL_PROP_MOBILE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'VALUES' => $selected
		);
		unset($selected);

		$arTemplateParameters['LABEL_PROP_POSITION'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('CP_BCS_TPL_LABEL_PROP_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogSectionComponent::getSettingsScript($componentPath, 'position'),
			'JS_EVENT' => 'initPositionControl',
			'JS_DATA' => Json::encode(
				array(
					'positions' => array(
						'top-left', 'top-center', 'top-right',
						'middle-left', 'middle-center', 'middle-right',
						'bottom-left', 'bottom-center', 'bottom-right'
					),
					'className' => ''
				)
			),
			'DEFAULT' => 'top-left'
		);
	}

	if ($boolSKU && isset($arCurrentValues['PRODUCT_DISPLAY_MODE']) && 'Y' == $arCurrentValues['PRODUCT_DISPLAY_MODE'])
	{
		$arAllOfferPropList = array();
		$arFileOfferPropList = $arTreeOfferPropList = $defaultValue;
		$rsProps = CIBlockProperty::GetList(
			array('SORT' => 'ASC', 'ID' => 'ASC'),
			array('IBLOCK_ID' => $arSKU['IBLOCK_ID'], 'ACTIVE' => 'Y')
		);
		while ($arProp = $rsProps->Fetch())
		{
			if ($arProp['ID'] == $arSKU['SKU_PROPERTY_ID'])
				continue;
			$arProp['USER_TYPE'] = (string)$arProp['USER_TYPE'];
			$strPropName = '['.$arProp['ID'].']'.('' != $arProp['CODE'] ? '['.$arProp['CODE'].']' : '').' '.$arProp['NAME'];
			if ('' == $arProp['CODE'])
				$arProp['CODE'] = $arProp['ID'];
			$arAllOfferPropList[$arProp['CODE']] = $strPropName;
			if ('F' == $arProp['PROPERTY_TYPE'])
				$arFileOfferPropList[$arProp['CODE']] = $strPropName;
			if ('N' != $arProp['MULTIPLE'])
				continue;
			if (
				'L' == $arProp['PROPERTY_TYPE']
				|| 'E' == $arProp['PROPERTY_TYPE']
				|| ('S' == $arProp['PROPERTY_TYPE'] && 'directory' == $arProp['USER_TYPE'] && CIBlockPriceTools::checkPropDirectory($arProp))
			)
				$arTreeOfferPropList[$arProp['CODE']] = $strPropName;
		}
		$arTemplateParameters['OFFER_ADD_PICT_PROP'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('CP_BCS_TPL_OFFER_ADD_PICT_PROP'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arFileOfferPropList
		);
		if (!$usePropertyFeatures)
		{
			$arTemplateParameters['OFFER_TREE_PROPS'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => Loc::getMessage('CP_BCS_TPL_OFFER_TREE_PROPS'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'ADDITIONAL_VALUES' => 'N',
				'REFRESH' => 'N',
				'DEFAULT' => '-',
				'VALUES' => $arTreeOfferPropList
			);
		}
	}
}

if ($boolCatalog)
{
	$arTemplateParameters['USE_OFFER_NAME'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_USE_OFFER_NAME'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);

	$arTemplateParameters['PRODUCT_SUBSCRIPTION'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_PRODUCT_SUBSCRIPTION'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y'
	);

	$arTemplateParameters['SHOW_OLD_PRICE'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_SHOW_OLD_PRICE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);
	$arTemplateParameters['SHOW_MAX_QUANTITY'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_SHOW_MAX_QUANTITY'),
		'TYPE' => 'LIST',
		'REFRESH' => 'Y',
		'MULTIPLE' => 'N',
		'VALUES' => array(
			'N' => Loc::getMessage('CP_BCS_TPL_SHOW_MAX_QUANTITY_N'),
			'Y' => Loc::getMessage('CP_BCS_TPL_SHOW_MAX_QUANTITY_Y'),
			'M' => Loc::getMessage('CP_BCS_TPL_SHOW_MAX_QUANTITY_M')
		),
		'DEFAULT' => array('N'),
	);

	if (isset($arCurrentValues['SHOW_MAX_QUANTITY']))
	{
		if ($arCurrentValues['SHOW_MAX_QUANTITY'] !== 'N')
		{
			$arTemplateParameters['MESS_SHOW_MAX_QUANTITY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_SHOW_MAX_QUANTITY'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_SHOW_MAX_QUANTITY_DEFAULT')
			);
		}

		if ($arCurrentValues['SHOW_MAX_QUANTITY'] === 'M')
		{
			$arTemplateParameters['RELATIVE_QUANTITY_FACTOR'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => Loc::getMessage('CP_BCS_TPL_RELATIVE_QUANTITY_FACTOR'),
				'TYPE' => 'STRING',
				'DEFAULT' => '5'
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_MANY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_RELATIVE_QUANTITY_MANY'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_RELATIVE_QUANTITY_MANY_DEFAULT')
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_FEW'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_RELATIVE_QUANTITY_FEW'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_RELATIVE_QUANTITY_FEW_DEFAULT')
			);
		}
	}

	$arTemplateParameters['ADD_TO_BASKET_ACTION'] = array(
		'PARENT' => 'BASKET',
		'NAME' => Loc::getMessage('CP_BCS_TPL_ADD_TO_BASKET_ACTION'),
		'TYPE' => 'LIST',
		'VALUES' => array(
			'ADD' => Loc::getMessage('ADD_TO_BASKET_ACTION_ADD'),
			'BUY' => Loc::getMessage('ADD_TO_BASKET_ACTION_BUY')
		),
		'DEFAULT' => 'ADD',
		'REFRESH' => 'N'
	);
	$arTemplateParameters['SHOW_CLOSE_POPUP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => Loc::getMessage('CP_BCS_TPL_SHOW_CLOSE_POPUP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
}

$arTemplateParameters['MESS_BTN_BUY'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_BUY'),
	'TYPE' => 'STRING',
	'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_BUY_DEFAULT')
);

$arTemplateParameters['LAZY_LOAD'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_LAZY_LOAD'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

$arTemplateParameters['MESS_BTN_LAZY_LOAD'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_LAZY_LOAD'),
	'TYPE' => 'TEXT',
	'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_LAZY_LOAD_DEFAULT'),
	'HIDDEN' => (isset($arCurrentValues['LAZY_LOAD']) && $arCurrentValues['LAZY_LOAD'] === 'Y' ? 'N' : 'Y')
);

$showLoadOnScroll = (isset($arCurrentValues['LAZY_LOAD']) && $arCurrentValues['LAZY_LOAD'] === 'Y')
	|| (isset($arCurrentValues['DISPLAY_TOP_PAGER']) && $arCurrentValues['DISPLAY_TOP_PAGER'] === 'Y')
	|| (!isset($arCurrentValues['DISPLAY_BOTTOM_PAGER']) || $arCurrentValues['DISPLAY_BOTTOM_PAGER'] !== 'N');

$arTemplateParameters['LOAD_ON_SCROLL'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_LOAD_ON_SCROLL'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y',
	'HIDDEN' => ($showLoadOnScroll ? 'N' : 'Y')
);

$showScrollParameters = (
	$showLoadOnScroll
	&& isset($arCurrentValues['LOAD_ON_SCROLL']) && $arCurrentValues['LOAD_ON_SCROLL'] === 'Y'
);

$arTemplateParameters['DEFERRED_LOAD'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_DEFERRED_LOAD'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'N',
	'HIDDEN' => ($showScrollParameters ? 'Y' : 'N')
);

$arTemplateParameters['CYCLIC_LOADING'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_CYCLIC_LOADING'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y',
	'HIDDEN' => ($showScrollParameters ? 'Y' : 'N')
);

$arTemplateParameters['CYCLIC_LOADING_COUNTER_NAME'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_CYCLIC_LOADING_COUNTER_NAME'),
	'TYPE' => 'STRING',
	'DEFAULT' => 'cycleCount',
	'HIDDEN' => ($showScrollParameters ? 'Y' : 'N')
);

$arTemplateParameters['MESS_BTN_ADD_TO_BASKET'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_ADD_TO_BASKET'),
	'TYPE' => 'STRING',
	'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_ADD_TO_BASKET_DEFAULT')
);
$arTemplateParameters['MESS_BTN_SUBSCRIBE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_SUBSCRIBE'),
	'TYPE' => 'STRING',
	'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_SUBSCRIBE_DEFAULT')
);

if (isset($arCurrentValues['DISPLAY_COMPARE']) && $arCurrentValues['DISPLAY_COMPARE'] === 'Y')
{
	$arTemplateParameters['MESS_BTN_COMPARE'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_COMPARE'),
		'TYPE' => 'STRING',
		'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_COMPARE_DEFAULT')
	);
	$arTemplateParameters['COMPARE_NAME'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => Loc::getMessage('CP_BCS_TPL_COMPARE_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'CATALOG_COMPARE_LIST'
	);
}

$arTemplateParameters['MESS_BTN_DETAIL'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_DETAIL'),
	'TYPE' => 'STRING',
	'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_BTN_DETAIL_DEFAULT')
);
$arTemplateParameters['MESS_NOT_AVAILABLE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCS_TPL_MESS_NOT_AVAILABLE'),
	'TYPE' => 'STRING',
	'DEFAULT' => Loc::getMessage('CP_BCS_TPL_MESS_NOT_AVAILABLE_DEFAULT')
);
$arTemplateParameters['RCM_TYPE'] = array(
	'PARENT' => 'BIG_DATA_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_TYPE_TITLE'),
	'TYPE' => 'LIST',
	'MULTIPLE' => 'N',
	'VALUES' => array(
		// personal
		'personal' => Loc::getMessage('CP_BCS_TPL_PERSONAL'),
		// general
		'bestsell' => Loc::getMessage('CP_BCS_TPL_BESTSELLERS'),
		// item2item
		'similar_sell' => Loc::getMessage('CP_BCS_TPL_SOLD_WITH'),
		'similar_view' => Loc::getMessage('CP_BCS_TPL_VIEWED_WITH'),
		'similar' => Loc::getMessage('CP_BCS_TPL_SIMILAR'),
		// randomly distributed
		'any_similar' => Loc::getMessage('CP_BCS_TPL_SIMILAR_ANY'),
		'any_personal' => Loc::getMessage('CP_BCS_TPL_PERSONAL_WBEST'),
		'any' => Loc::getMessage('CP_BCS_TPL_RAND')
	),
	'DEFAULT' => 'personal'
);
$arTemplateParameters['RCM_PROD_ID'] = array(
	'PARENT' => 'BIG_DATA_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_PRODUCT_ID_PARAM'),
	'TYPE' => 'STRING',
	'DEFAULT' => '={$_REQUEST["PRODUCT_ID"]}'
);
$arTemplateParameters['SHOW_FROM_SECTION'] = array(
	'PARENT' => 'BIG_DATA_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_SHOW_FROM_SECTION'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N'
);
$arTemplateParameters['USE_ENHANCED_ECOMMERCE'] = array(
	'PARENT' => 'ANALYTICS_SETTINGS',
	'NAME' => Loc::getMessage('CP_BCS_TPL_USE_ENHANCED_ECOMMERCE'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['USE_ENHANCED_ECOMMERCE']) && $arCurrentValues['USE_ENHANCED_ECOMMERCE'] === 'Y')
{
	$arTemplateParameters['DATA_LAYER_NAME'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => Loc::getMessage('CP_BCS_TPL_DATA_LAYER_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'dataLayer'
	);
	$arTemplateParameters['BRAND_PROPERTY'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => Loc::getMessage('CP_BCS_TPL_BRAND_PROPERTY'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'DEFAULT' => '',
		'VALUES' => $defaultValue + $arAllPropList
	);
}

$offsetModeList = [
	'N' => Loc::getMessage('CPT_BCS_SECTIONS_OFFSET_MODE_NO'),
	'F' => Loc::getMessage('CPT_BCS_SECTIONS_OFFSET_MODE_FIXED'),
	'D' => Loc::getMessage('CPT_BCS_SECTIONS_OFFSET_MODE_DYNAMIC')
];

$arTemplateParameters['SECTIONS_OFFSET_MODE'] = [
	'NAME' => Loc::getMessage('CPT_BCS_SECTIONS_OFFSET_MODE'),
	'PARENT' => 'VISUAL',
	'TYPE' => 'LIST',
	'VALUES' => $offsetModeList,
	'MULTIPLE' => 'N',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y'
];

if (isset($arCurrentValues['SECTIONS_OFFSET_MODE']))
{
	$arTemplateParameters['SECTIONS_OFFSET_VARIABLE'] = [
		'NAME' => Loc::getMessage('CPT_BCS_SECTIONS_OFFSET_VARIABLE'),
		'PARENT' => 'VISUAL',
		'TYPE' => 'STRING',
		'DEFAULT' => '',
		'HIDDEN' => ($arCurrentValues['OFFSET_MODE'] === 'D' ? 'N' : 'Y')
	];
}

$arTemplateParameters['SECTIONS_SECTION_ID'] = [
	'NAME' => Loc::getMessage('CP_BCS_TPL_SECTIONS_SECTION_ID'),
	'PARENT' => 'VISUAL',
	'TYPE' => 'STRING',
	'DEFAULT' => ''
];
$arTemplateParameters['SECTIONS_SECTION_CODE'] = [
	'NAME' => Loc::getMessage('CP_BCS_TPL_SECTIONS_SECTION_CODE'),
	'PARENT' => 'VISUAL',
	'TYPE' => 'STRING',
	'DEFAULT' => ''
];
$arTemplateParameters['SECTIONS_TOP_DEPTH'] = [
	'NAME' => Loc::getMessage('CP_BCS_TPL_SECTIONS_TOP_DEPTH'),
	'PARENT' => 'VISUAL',
	'TYPE' => 'STRING',
	'DEFAULT' => '2'
];
