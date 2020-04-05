<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 */

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;

if (!Loader::includeModule('iblock'))
	return;

$boolCatalog = Loader::includeModule('catalog');
CBitrixComponent::includeComponentClass($componentName);

$defaultValue = array('-' => GetMessage('CP_BCT_TPL_PROP_EMPTY'));
$viewModeList = array(
	'BANNER' => GetMessage('CPT_BCT_TPL_VIEW_MODE_BANNER'),
	'SLIDER' => GetMessage('CPT_BCT_TPL_VIEW_MODE_SLIDER'),
	'SECTION' => GetMessage('CPT_BCT_TPL_VIEW_MODE_SECTION')
);
$viewModeValue = isset($arCurrentValues['VIEW_MODE']) && isset($viewModeList[$arCurrentValues['VIEW_MODE']])
	? $arCurrentValues['VIEW_MODE']
	: 'SECTION';

$arTemplateParameters = array(
	'VIEW_MODE' => array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CPT_BCT_TPL_VIEW_MODE'),
		'TYPE' => 'LIST',
		'VALUES' => $viewModeList,
		'MULTIPLE' => 'N',
		'DEFAULT' => 'SECTION',
		'REFRESH' => 'Y'
	)
);

if (!empty($viewModeValue))
{
	$arThemes = array();
	if (ModuleManager::isModuleInstalled('bitrix.eshop'))
	{
		$arThemes['site'] = GetMessage('CP_BCT_TPL_THEME_SITE');
	}

	$arThemesList = array(
		'blue' => GetMessage('CP_BCT_TPL_THEME_BLUE'),
		'green' => GetMessage('CP_BCT_TPL_THEME_GREEN'),
		'red' => GetMessage('CP_BCT_TPL_THEME_RED'),
		'wood' => GetMessage('CP_BCT_TPL_THEME_WOOD'),
		'yellow' => GetMessage('CP_BCT_TPL_THEME_YELLOW'),
		'black' => GetMessage('CP_BCT_TPL_THEME_BLACK')
	);

	$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__).'/'.ToLower($arCurrentValues['VIEW_MODE']).'/themes/'));
	if (is_dir($dir))
	{
		foreach ($arThemesList as $themeID => $themeName)
		{
			if (!is_file($dir.$themeID.'/style.css'))
				continue;

			$arThemes[$themeID] = $themeName;
		}
	}

	$arTemplateParameters['TEMPLATE_THEME'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_TEMPLATE_THEME'),
		'TYPE' => 'LIST',
		'VALUES' => $arThemes,
		'DEFAULT' => 'blue',
		'ADDITIONAL_VALUES' => 'Y'
	);
}

$arSKU = false;
$boolSKU = false;
if ($boolCatalog && isset($arCurrentValues['IBLOCK_ID']) && intval($arCurrentValues['IBLOCK_ID']) > 0)
{
	$arSKU = CCatalogSku::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
	$boolSKU = !empty($arSKU) && is_array($arSKU);
}

if ($boolSKU)
{
	$arTemplateParameters['PRODUCT_DISPLAY_MODE'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_PRODUCT_DISPLAY_MODE'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'Y',
		'DEFAULT' => 'N',
		'VALUES' => array(
			'N' => GetMessage('CP_BCT_TPL_DML_SIMPLE'),
			'Y' => GetMessage('CP_BCT_TPL_DML_EXT')
		)
	);
}

$arAllPropList = array();
$arFilePropList = array();
$arListPropList = array();

if (isset($arCurrentValues['IBLOCK_ID']) && 0 < intval($arCurrentValues['IBLOCK_ID']))
{
	$rsProps = CIBlockProperty::GetList(
		array('SORT' => 'ASC', 'ID' => 'ASC'),
		array('IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'], 'ACTIVE' => 'Y')
	);
	while ($arProp = $rsProps->Fetch())
	{
		$strPropName = '['.$arProp['ID'].']'.('' != $arProp['CODE'] ? '['.$arProp['CODE'].']' : '').' '.$arProp['NAME'];
		if ('' == $arProp['CODE'])
		{
			$arProp['CODE'] = $arProp['ID'];
		}

		$arAllPropList[$arProp['CODE']] = $strPropName;

		if ('F' == $arProp['PROPERTY_TYPE'])
		{
			$arFilePropList[$arProp['CODE']] = $strPropName;
		}

		if ('L' == $arProp['PROPERTY_TYPE'])
		{
			$arListPropList[$arProp['CODE']] = $strPropName;
		}

		if (!empty($arCurrentValues['PROPERTY_CODE']))
		{
			$selected = array();

			foreach ($arCurrentValues['PROPERTY_CODE'] as $code)
			{
				if (isset($arAllPropList[$code]))
				{
					$selected[$code] = $arAllPropList[$code];
				}
			}

			$arTemplateParameters['PROPERTY_CODE_MOBILE'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCT_TPL_PROPERTY_CODE_MOBILE'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'VALUES' => $selected
			);
		}
	}

	$arTemplateParameters['ADD_PICT_PROP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_ADD_PICT_PROP'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '-',
		'VALUES' => $defaultValue + $arFilePropList
	);

	if ($viewModeValue === 'SECTION')
	{
		$arTemplateParameters['LABEL_PROP'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_TPL_LABEL_PROP'),
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
				'NAME' => GetMessage('CP_BCT_TPL_LABEL_PROP_MOBILE'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'ADDITIONAL_VALUES' => 'N',
				'REFRESH' => 'N',
				'VALUES' => $selected
			);
			unset($selected);

			$arTemplateParameters['LABEL_PROP_POSITION'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCT_TPL_LABEL_PROP_POSITION'),
				'TYPE' => 'CUSTOM',
				'JS_FILE' => CatalogTopComponent::getSettingsScript($componentPath, 'position'),
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
	}
	else
	{
		$arTemplateParameters['LABEL_PROP'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_TPL_LABEL_PROP'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arListPropList
		);
	}

	if ($boolSKU && isset($arCurrentValues['PRODUCT_DISPLAY_MODE']) && $arCurrentValues['PRODUCT_DISPLAY_MODE'] === 'Y')
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
			'NAME' => GetMessage('CP_BCT_TPL_OFFER_ADD_PICT_PROP'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arFileOfferPropList
		);
		$arTemplateParameters['OFFER_TREE_PROPS'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_TPL_OFFER_TREE_PROPS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arTreeOfferPropList
		);
	}
}

if ($boolCatalog)
{
	$arTemplateParameters['SHOW_DISCOUNT_PERCENT'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_SHOW_DISCOUNT_PERCENT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);
	$arTemplateParameters['SHOW_OLD_PRICE'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_SHOW_OLD_PRICE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);
	$arTemplateParameters['SHOW_MAX_QUANTITY'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_SHOW_MAX_QUANTITY'),
		'TYPE' => 'LIST',
		'REFRESH' => 'Y',
		'MULTIPLE' => 'N',
		'VALUES' => array(
			'N' => GetMessage('CP_BCT_TPL_SHOW_MAX_QUANTITY_N'),
			'Y' => GetMessage('CP_BCT_TPL_SHOW_MAX_QUANTITY_Y'),
			'M' => GetMessage('CP_BCT_TPL_SHOW_MAX_QUANTITY_M')
		),
		'DEFAULT' => array('N')
	);

	if (isset($arCurrentValues['SHOW_MAX_QUANTITY']))
	{
		if ($arCurrentValues['SHOW_MAX_QUANTITY'] !== 'N')
		{
			$arTemplateParameters['MESS_SHOW_MAX_QUANTITY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCT_TPL_MESS_SHOW_MAX_QUANTITY'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_SHOW_MAX_QUANTITY_DEFAULT')
			);
		}

		if ($arCurrentValues['SHOW_MAX_QUANTITY'] === 'M')
		{
			$arTemplateParameters['RELATIVE_QUANTITY_FACTOR'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCT_TPL_RELATIVE_QUANTITY_FACTOR'),
				'TYPE' => 'STRING',
				'DEFAULT' => '5'
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_MANY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCT_TPL_MESS_RELATIVE_QUANTITY_MANY'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_RELATIVE_QUANTITY_MANY_DEFAULT')
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_FEW'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCT_TPL_MESS_RELATIVE_QUANTITY_FEW'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_RELATIVE_QUANTITY_FEW_DEFAULT')
			);
		}
	}
	
	$arTemplateParameters['ADD_TO_BASKET_ACTION'] = array(
		'PARENT' => 'BASKET',
		'NAME' => GetMessage('CP_BCT_TPL_ADD_TO_BASKET_ACTION'),
		'TYPE' => 'LIST',
		'VALUES' => array(
			'BUY' => GetMessage('ADD_TO_BASKET_ACTION_BUY'),
			'ADD' => GetMessage('ADD_TO_BASKET_ACTION_ADD')
		),
		'DEFAULT' => 'ADD',
		'REFRESH' => 'N'
	);
	$arTemplateParameters['SHOW_CLOSE_POPUP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_SHOW_CLOSE_POPUP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
}

if ($viewModeValue === 'SLIDER' || $viewModeValue === 'BANNER')
{
	$arTemplateParameters['ROTATE_TIMER'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_ROTATE_TIMER'),
		'TYPE' => 'STRING',
		'DEFAULT' => '30'
	);
	$arTemplateParameters['SHOW_PAGINATION'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_SHOW_PAGINATION'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y'
	);
}

if ($viewModeValue === 'SECTION')
{
	$arTemplateParameters['PRODUCT_SUBSCRIPTION'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_PRODUCT_SUBSCRIPTION'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y'
	);

	if (isset($arCurrentValues['SHOW_DISCOUNT_PERCENT']) && $arCurrentValues['SHOW_DISCOUNT_PERCENT'] === 'Y')
	{
		$arTemplateParameters['DISCOUNT_PERCENT_POSITION'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_TPL_DISCOUNT_PERCENT_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogTopComponent::getSettingsScript($componentPath, 'position'),
			'JS_EVENT' => 'initPositionControl',
			'JS_DATA' => Json::encode(
				array(
					'positions' => array(
						'top-left', 'top-center', 'top-right',
						'middle-left', 'middle-center', 'middle-right',
						'bottom-left', 'bottom-center', 'bottom-right'
					),
					'className' => 'bx-pos-parameter-block-circle'
				)
			),
			'DEFAULT' => 'bottom-right'
		);
	}

	$lineElementCount = (int)$arCurrentValues['LINE_ELEMENT_COUNT'] ?: 3;
	$pageElementCount = (int)$arCurrentValues['ELEMENT_COUNT'] ?: 9;

	$arTemplateParameters['PRODUCT_ROW_VARIANTS'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_PRODUCT_ROW_VARIANTS'),
		'TYPE' => 'CUSTOM',
		'BIG_DATA' => 'N',
		'COUNT_PARAM_NAME' => 'ELEMENT_COUNT',
		'JS_FILE' => CatalogTopComponent::getSettingsScript($componentPath, 'dragdrop_add'),
		'JS_EVENT' => 'initDraggableAddControl',
		'JS_MESSAGES' => Json::encode(array(
			'variant' => GetMessage('CP_BCT_TPL_SETTINGS_VARIANT'),
			'delete' => GetMessage('CP_BCT_TPL_SETTINGS_DELETE'),
			'quantity' => GetMessage('CP_BCT_TPL_SETTINGS_QUANTITY'),
			'quantityBigData' => GetMessage('CP_BCT_TPL_SETTINGS_QUANTITY_BIG_DATA')
		)),
		'JS_DATA' => Json::encode(CatalogTopComponent::getTemplateVariantsMap()),
		'DEFAULT' => Json::encode(CatalogTopComponent::predictRowVariants($lineElementCount, $pageElementCount))
	);
	$arTemplateParameters['ENLARGE_PRODUCT'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_ENLARGE_PRODUCT'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'Y',
		'DEFAULT' => 'N',
		'VALUES' => array(
			'STRICT' => GetMessage('CP_BCT_TPL_ENLARGE_PRODUCT_STRICT'),
			'PROP' => GetMessage('CP_BCT_TPL_ENLARGE_PRODUCT_PROP')
		)
	);

	if (isset($arCurrentValues['ENLARGE_PRODUCT']) && $arCurrentValues['ENLARGE_PRODUCT'] === 'PROP')
	{
		$arTemplateParameters['ENLARGE_PROP'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_TPL_ENLARGE_PROP'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $defaultValue + $arListPropList
		);
	}

	$arTemplateParameters['PRODUCT_BLOCKS_ORDER'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_PRODUCT_BLOCKS_ORDER'),
		'TYPE' => 'CUSTOM',
		'JS_FILE' => CatalogTopComponent::getSettingsScript($componentPath, 'dragdrop_order'),
		'JS_EVENT' => 'initDraggableOrderControl',
		'JS_DATA' => Json::encode(array(
			'price' => GetMessage('CP_BCT_TPL_PRODUCT_BLOCK_PRICE'),
			'quantityLimit' => GetMessage('CP_BCT_TPL_PRODUCT_BLOCK_QUANTITY_LIMIT'),
			'quantity' => GetMessage('CP_BCT_TPL_PRODUCT_BLOCK_QUANTITY'),
			'buttons' => GetMessage('CP_BCT_TPL_PRODUCT_BLOCK_BUTTONS'),
			'props' => GetMessage('CP_BCT_TPL_PRODUCT_BLOCK_PROPS'),
			'sku' => GetMessage('CP_BCT_TPL_PRODUCT_BLOCK_SKU')
		)),
		'DEFAULT' => 'price,props,sku,quantityLimit,quantity,buttons'
	);

	$arTemplateParameters['SHOW_SLIDER'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCT_TPL_SHOW_SLIDER'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'REFRESH' => 'Y',
		'DEFAULT' => 'Y'
	);

	if (isset($arCurrentValues['SHOW_SLIDER']) && $arCurrentValues['SHOW_SLIDER'] === 'Y')
	{
		$arTemplateParameters['SLIDER_INTERVAL'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_TPL_SLIDER_INTERVAL'),
			'TYPE' => 'TEXT',
			'MULTIPLE' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '3000'
		);
		$arTemplateParameters['SLIDER_PROGRESS'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_TPL_SLIDER_PROGRESS'),
			'TYPE' => 'CHECKBOX',
			'MULTIPLE' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => 'N'
		);
	}

	$arTemplateParameters['USE_ENHANCED_ECOMMERCE'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => GetMessage('CP_BCT_TPL_USE_ENHANCED_ECOMMERCE'),
		'TYPE' => 'CHECKBOX',
		'REFRESH' => 'Y',
		'DEFAULT' => 'N'
	);

	if (isset($arCurrentValues['USE_ENHANCED_ECOMMERCE']) && $arCurrentValues['USE_ENHANCED_ECOMMERCE'] === 'Y')
	{
		$arTemplateParameters['DATA_LAYER_NAME'] = array(
			'PARENT' => 'ANALYTICS_SETTINGS',
			'NAME' => GetMessage('CP_BCT_TPL_DATA_LAYER_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'dataLayer'
		);
		$arTemplateParameters['BRAND_PROPERTY'] = array(
			'PARENT' => 'ANALYTICS_SETTINGS',
			'NAME' => GetMessage('CP_BCT_TPL_BRAND_PROPERTY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'VALUES' => $defaultValue + $arAllPropList
		);
	}
}

$arTemplateParameters['MESS_BTN_BUY'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCT_TPL_MESS_BTN_BUY'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_BTN_BUY_DEFAULT')
);
$arTemplateParameters['MESS_BTN_ADD_TO_BASKET'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCT_TPL_MESS_BTN_ADD_TO_BASKET'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_BTN_ADD_TO_BASKET_DEFAULT')
);
if (isset($arCurrentValues['DISPLAY_COMPARE']) && isset($arCurrentValues['DISPLAY_COMPARE']) == 'Y')
{
	$arTemplateParameters['MESS_BTN_COMPARE'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => GetMessage('CP_BCT_TPL_MESS_BTN_COMPARE'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_BTN_COMPARE_DEFAULT')
	);
	$arTemplateParameters['COMPARE_NAME'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => GetMessage('CP_BCT_TPL_COMPARE_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'CATALOG_COMPARE_LIST'
	);
}
$arTemplateParameters['MESS_BTN_DETAIL'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCT_TPL_MESS_BTN_DETAIL'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_BTN_DETAIL_DEFAULT')
);
$arTemplateParameters['MESS_NOT_AVAILABLE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCT_TPL_MESS_NOT_AVAILABLE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCT_TPL_MESS_NOT_AVAILABLE_DEFAULT')
);