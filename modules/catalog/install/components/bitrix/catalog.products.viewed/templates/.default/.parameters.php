<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 */

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog'))
	return;

CBitrixComponent::includeComponentClass($componentName);

$arThemes = array();
if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('CP_CPV_TPL_THEME_SITE');
}

$arThemesList = array(
	'blue' => GetMessage('CP_CPV_TPL_THEME_BLUE'),
	'green' => GetMessage('CP_CPV_TPL_THEME_GREEN'),
	'red' => GetMessage('CP_CPV_TPL_THEME_RED'),
	'yellow' => GetMessage('CP_CPV_TPL_THEME_YELLOW')
);
$dir = trim(preg_replace("'[\\\\/]+'", '/', dirname(__FILE__).'/themes/'));
if (is_dir($dir))
{
	foreach ($arThemesList as $themeID => $themeName)
	{
		if (!is_file($dir.$themeID.'/style.css'))
			continue;
		$arThemes[$themeID] = $themeName;
	}
}

$documentRoot = Loader::getDocumentRoot();

$singleIblockMode = !isset($arCurrentValues['IBLOCK_MODE']) || $arCurrentValues['IBLOCK_MODE'] === 'single';

if (
	$singleIblockMode && isset($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0
	|| !$singleIblockMode
)
{
	$catalogs = array();
	$iblockMap = array();
	$iblockFilter = array('ACTIVE' => 'Y');

	if ($singleIblockMode)
	{
		$catalogInfo = CCatalogSku::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
		if (!empty($catalogInfo))
		{
			$iblockFilter['ID'] = array($catalogInfo['IBLOCK_ID'], $catalogInfo['PRODUCT_IBLOCK_ID']);
		}
	}

	$iblockIterator = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
	while ($iblock = $iblockIterator->fetch())
	{
		$iblockMap[$iblock['ID']] = $iblock;
	}

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

	foreach ($catalogs as $catalog)
	{
		$catalogs[$catalog['IBLOCK_ID']] = $catalog;
		$iblock = $iblockMap[$catalog['IBLOCK_ID']];
		$groupId = 'CATALOG_PARAMS_'.$iblock['ID'];

		// Params in group
		// 1. Display Properties
		$listProperties = array();
		$allProperties = array();

		$propertyIterator = CIBlockProperty::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			array('IBLOCK_ID' => $iblock['ID'], 'ACTIVE' => 'Y')
		);
		while ($property = $propertyIterator->fetch())
		{
			$property['ID'] = (int)$property['ID'];
			$propertyName = '['.$property['ID'].']'.('' != $property['CODE'] ? '['.$property['CODE'].']' : '').' '.$property['NAME'];

			$allProperties[$property['CODE']] = $propertyName;

			if ($property['PROPERTY_TYPE'] === 'L')
			{
				$listProperties[$property['CODE']] = $propertyName;
			}
		}

		if ((int)$catalog['SKU_PROPERTY_ID'] <= 0)
		{
			if (!empty($arCurrentValues['PROPERTY_CODE_'.$iblock['ID']]))
			{
				$selected = array();

				foreach ($arCurrentValues['PROPERTY_CODE_'.$iblock['ID']] as $code)
				{
					if (isset($allProperties[$code]))
					{
						$selected[$code] = $allProperties[$code];
					}
				}

				$arTemplateParameters['PROPERTY_CODE_MOBILE_'.$iblock['ID']] = array(
					'PARENT' => $groupId,
					'NAME' => GetMessage('CP_CPV_TPL_PROPERTY_CODE_MOBILE'),
					'TYPE' => 'LIST',
					'MULTIPLE' => 'Y',
					'VALUES' => $selected,
					'HIDDEN' => !$catalog['VISIBLE'] ? 'Y' : 'N'
				);
			}

			if (isset($arCurrentValues['ENLARGE_PRODUCT']) && $arCurrentValues['ENLARGE_PRODUCT'] === 'PROP')
			{
				$arTemplateParameters['ENLARGE_PROP_'.$iblock['ID']] = array(
					'PARENT' => $groupId,
					'NAME' => GetMessage('CP_CPV_TPL_ENLARGE_PROP'),
					'TYPE' => 'LIST',
					'MULTIPLE' => 'N',
					'ADDITIONAL_VALUES' => 'N',
					'REFRESH' => 'N',
					'DEFAULT' => '-',
					'VALUES' => array('-' => GetMessage('CP_CPV_TPL_PROP_EMPTY')) + $listProperties,
					'HIDDEN' => !$catalog['VISIBLE'] ? 'Y' : 'N'
				);
			}

			if (isset($arCurrentValues['LABEL_PROP_'.$iblock['ID']]) && !empty($arCurrentValues['LABEL_PROP_'.$iblock['ID']]))
			{
				if (!is_array($arCurrentValues['LABEL_PROP_'.$iblock['ID']]))
				{
					$arCurrentValues['LABEL_PROP_'.$iblock['ID']] = array($arCurrentValues['LABEL_PROP_'.$iblock['ID']]);
				}

				$selected = array();
				foreach ($arCurrentValues['LABEL_PROP_'.$iblock['ID']] as $name)
				{
					if (isset($listProperties[$name]))
					{
						$selected[$name] = $listProperties[$name];
					}
				}

				$arTemplateParameters['LABEL_PROP_MOBILE_'.$iblock['ID']] = array(
					'PARENT' => $groupId,
					'NAME' => GetMessage('CP_CPV_TPL_LABEL_PROP_MOBILE'),
					'TYPE' => 'LIST',
					'MULTIPLE' => 'Y',
					'ADDITIONAL_VALUES' => 'N',
					'REFRESH' => 'N',
					'VALUES' => $selected,
					'HIDDEN' => !$catalog['VISIBLE'] ? 'Y' : 'N'
				);
				unset($selected);
			}
		}
	}
}


$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_TEMPLATE_THEME'),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);

$arTemplateParameters['PRODUCT_BLOCKS_ORDER'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_PRODUCT_BLOCKS_ORDER'),
	'TYPE' => 'CUSTOM',
	'JS_FILE' => CatalogProductsViewedComponent::getSettingsScript($componentPath, 'dragdrop_order'),
	'JS_EVENT' => 'initDraggableOrderControl',
	'JS_DATA' => Json::encode(array(
		'price' => GetMessage('CP_CPV_TPL_PRODUCT_BLOCK_PRICE'),
		'quantityLimit' => GetMessage('CP_CPV_TPL_PRODUCT_BLOCK_QUANTITY_LIMIT'),
		'quantity' => GetMessage('CP_CPV_TPL_PRODUCT_BLOCK_QUANTITY'),
		'buttons' => GetMessage('CP_CPV_TPL_PRODUCT_BLOCK_BUTTONS'),
		'props' => GetMessage('CP_CPV_TPL_PRODUCT_BLOCK_PROPS'),
		'sku' => GetMessage('CP_CPV_TPL_PRODUCT_BLOCK_SKU')
	)),
	'DEFAULT' => 'price,props,sku,quantityLimit,quantity,buttons'
);

$lineElementCount = 3;
$pageElementCount = (int)$arCurrentValues['PAGE_ELEMENT_COUNT'] ?: 9;

$arTemplateParameters['PRODUCT_ROW_VARIANTS'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_PRODUCT_ROW_VARIANTS'),
	'TYPE' => 'CUSTOM',
	'BIG_DATA' => 'N',
	'COUNT_PARAM_NAME' => 'PAGE_ELEMENT_COUNT',
	'JS_FILE' => CatalogProductsViewedComponent::getSettingsScript($componentPath, 'dragdrop_add'),
	'JS_EVENT' => 'initDraggableAddControl',
	'JS_MESSAGES' => Json::encode(array(
		'variant' => GetMessage('CP_CPV_TPL_SETTINGS_VARIANT'),
		'delete' => GetMessage('CP_CPV_TPL_SETTINGS_DELETE'),
		'quantity' => GetMessage('CP_CPV_TPL_SETTINGS_QUANTITY'),
		'quantityBigData' => GetMessage('CP_CPV_TPL_SETTINGS_QUANTITY_BIG_DATA')
	)),
	'JS_DATA' => Json::encode(CatalogProductsViewedComponent::getTemplateVariantsMap()),
	'DEFAULT' => Json::encode(CatalogProductsViewedComponent::predictRowVariants($lineElementCount, $pageElementCount))
);

$arTemplateParameters['ENLARGE_PRODUCT'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_ENLARGE_PRODUCT'),
	'TYPE' => 'LIST',
	'MULTIPLE' => 'N',
	'ADDITIONAL_VALUES' => 'N',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N',
	'VALUES' => array(
		'STRICT' => GetMessage('CP_CPV_TPL_ENLARGE_PRODUCT_STRICT'),
		'PROP' => GetMessage('CP_CPV_TPL_ENLARGE_PRODUCT_PROP')
	)
);
$arTemplateParameters['SHOW_SLIDER'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_SHOW_SLIDER'),
	'TYPE' => 'CHECKBOX',
	'MULTIPLE' => 'N',
	'REFRESH' => 'Y',
	'DEFAULT' => 'Y'
);

if (isset($arCurrentValues['SHOW_SLIDER']) && $arCurrentValues['SHOW_SLIDER'] === 'Y')
{
	$arTemplateParameters['SLIDER_INTERVAL'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_CPV_TPL_SLIDER_INTERVAL'),
		'TYPE' => 'TEXT',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '3000'
	);
	$arTemplateParameters['SLIDER_PROGRESS'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_CPV_TPL_SLIDER_PROGRESS'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => 'N'
	);
}

$arTemplateParameters['LABEL_PROP_POSITION'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_LABEL_PROP_POSITION'),
	'TYPE' => 'CUSTOM',
	'JS_FILE' => CatalogProductsViewedComponent::getSettingsScript($componentPath, 'position'),
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
$arTemplateParameters['PRODUCT_SUBSCRIPTION'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_PRODUCT_SUBSCRIPTION'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y'
);
$arTemplateParameters['SHOW_DISCOUNT_PERCENT'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_SHOW_DISCOUNT_PERCENT'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['SHOW_DISCOUNT_PERCENT']) && $arCurrentValues['SHOW_DISCOUNT_PERCENT'] === 'Y')
{
	$arTemplateParameters['DISCOUNT_PERCENT_POSITION'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_CPV_TPL_DISCOUNT_PERCENT_POSITION'),
		'TYPE' => 'CUSTOM',
		'JS_FILE' => CatalogProductsViewedComponent::getSettingsScript($componentPath, 'position'),
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

$arTemplateParameters['SHOW_OLD_PRICE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_SHOW_OLD_PRICE'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N'
);
$arTemplateParameters['SHOW_MAX_QUANTITY'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_SHOW_MAX_QUANTITY'),
	'TYPE' => 'LIST',
	'REFRESH' => 'Y',
	'MULTIPLE' => 'N',
	'VALUES' => array(
		'N' => GetMessage('CP_CPV_TPL_SHOW_MAX_QUANTITY_N'),
		'Y' => GetMessage('CP_CPV_TPL_SHOW_MAX_QUANTITY_Y'),
		'M' => GetMessage('CP_CPV_TPL_SHOW_MAX_QUANTITY_M')
	),
	'DEFAULT' => array('N')
);

if (isset($arCurrentValues['SHOW_MAX_QUANTITY']))
{
	if ($arCurrentValues['SHOW_MAX_QUANTITY'] !== 'N')
	{
		$arTemplateParameters['MESS_SHOW_MAX_QUANTITY'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_CPV_TPL_MESS_SHOW_MAX_QUANTITY'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_SHOW_MAX_QUANTITY_DEFAULT')
		);
	}

	if ($arCurrentValues['SHOW_MAX_QUANTITY'] === 'M')
	{
		$arTemplateParameters['RELATIVE_QUANTITY_FACTOR'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_CPV_TPL_RELATIVE_QUANTITY_FACTOR'),
			'TYPE' => 'STRING',
			'DEFAULT' => '5'
		);
		$arTemplateParameters['MESS_RELATIVE_QUANTITY_MANY'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_CPV_TPL_MESS_RELATIVE_QUANTITY_MANY'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_RELATIVE_QUANTITY_MANY_DEFAULT')
		);
		$arTemplateParameters['MESS_RELATIVE_QUANTITY_FEW'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_CPV_TPL_MESS_RELATIVE_QUANTITY_FEW'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_RELATIVE_QUANTITY_FEW_DEFAULT')
		);
	}
}

$arTemplateParameters['ADD_TO_BASKET_ACTION'] = array(
	'PARENT' => 'BASKET',
	'NAME' => GetMessage('CP_CPV_TPL_ADD_TO_BASKET_ACTION'),
	'TYPE' => 'LIST',
	'VALUES' => array(
		'ADD' => GetMessage('ADD_TO_BASKET_ACTION_ADD'),
		'BUY' => GetMessage('ADD_TO_BASKET_ACTION_BUY')
	),
	'DEFAULT' => 'ADD',
	'REFRESH' => 'N'
);
$arTemplateParameters['SHOW_CLOSE_POPUP'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_SHOW_CLOSE_POPUP'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
);
$arTemplateParameters['MESS_BTN_BUY'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_MESS_BTN_BUY'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_BTN_BUY_DEFAULT')
);
$arTemplateParameters['MESS_BTN_ADD_TO_BASKET'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_MESS_BTN_ADD_TO_BASKET'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_BTN_ADD_TO_BASKET_DEFAULT')
);
$arTemplateParameters['MESS_BTN_SUBSCRIBE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_MESS_BTN_SUBSCRIBE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_BTN_SUBSCRIBE_DEFAULT')
);

if (isset($arCurrentValues['DISPLAY_COMPARE']) && $arCurrentValues['DISPLAY_COMPARE'] === 'Y')
{
	$arTemplateParameters['MESS_BTN_COMPARE'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => GetMessage('CP_CPV_TPL_MESS_BTN_COMPARE'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_BTN_COMPARE_DEFAULT')
	);
	$arTemplateParameters['COMPARE_NAME'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => GetMessage('CP_CPV_TPL_COMPARE_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'CATALOG_COMPARE_LIST'
	);
}

$arTemplateParameters['MESS_BTN_DETAIL'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_MESS_BTN_DETAIL'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_BTN_DETAIL_DEFAULT')
);
$arTemplateParameters['MESS_NOT_AVAILABLE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_CPV_TPL_MESS_NOT_AVAILABLE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_CPV_TPL_MESS_NOT_AVAILABLE_DEFAULT')
);
$arTemplateParameters['USE_ENHANCED_ECOMMERCE'] = array(
	'PARENT' => 'ANALYTICS_SETTINGS',
	'NAME' => GetMessage('CP_CPV_TPL_USE_ENHANCED_ECOMMERCE'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['USE_ENHANCED_ECOMMERCE']) && $arCurrentValues['USE_ENHANCED_ECOMMERCE'] === 'Y')
{
	$arTemplateParameters['DATA_LAYER_NAME'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => GetMessage('CP_CPV_TPL_DATA_LAYER_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'dataLayer'
	);
}