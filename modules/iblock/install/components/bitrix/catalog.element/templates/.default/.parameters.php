<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 */

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;

if (!Loader::includeModule('iblock'))
	return;

$boolCatalog = Loader::includeModule('catalog');
CBitrixComponent::includeComponentClass($componentName);

$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$defaultValue = array('-' => GetMessage('CP_BCE_TPL_PROP_EMPTY'));
$arSKU = false;
$boolSKU = false;

if ($boolCatalog && $iblockExists)
{
	$arSKU = CCatalogSku::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
	$boolSKU = !empty($arSKU) && is_array($arSKU);
}

$arThemes = array();
if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('CP_BCE_TPL_THEME_SITE');
}

$arThemesList = array(
	'blue' => GetMessage('CP_BCE_TPL_THEME_BLUE'),
	'green' => GetMessage('CP_BCE_TPL_THEME_GREEN'),
	'red' => GetMessage('CP_BCE_TPL_THEME_RED'),
	'wood' => GetMessage('CP_BCE_TPL_THEME_WOOD'),
	'yellow' => GetMessage('CP_BCE_TPL_THEME_YELLOW'),
	'black' => GetMessage('CP_BCE_TPL_THEME_BLACK')
);
$dir = trim(preg_replace("'[\\\\/]+'", "/", __DIR__.'/themes/'));
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

$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_TEMPLATE_THEME'),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);
$arTemplateParameters['PRODUCT_INFO_BLOCK_ORDER'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_PRODUCT_INFO_BLOCK_ORDER'),
	'TYPE' => 'CUSTOM',
	'JS_FILE' => CatalogElementComponent::getSettingsScript($componentPath, 'dragdrop_order'),
	'JS_EVENT' => 'initDraggableOrderControl',
	'JS_DATA' => Json::encode(array(
		'sku' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_SKU'),
		'props' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_PROPS')
	)),
	'DEFAULT' => 'sku,props'
);
$arTemplateParameters['PRODUCT_PAY_BLOCK_ORDER'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_PRODUCT_PAY_BLOCK_ORDER'),
	'TYPE' => 'CUSTOM',
	'JS_FILE' => CatalogElementComponent::getSettingsScript($componentPath, 'dragdrop_order'),
	'JS_EVENT' => 'initDraggableOrderControl',
	'JS_DATA' => Json::encode(array(
		'rating' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_RATING'),
		'price' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_PRICE'),
		'priceRanges' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_PRICE_RANGES'),
		'quantityLimit' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_QUANTITY_LIMIT'),
		'quantity' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_QUANTITY'),
		'buttons' => GetMessage('CP_BCE_TPL_PRODUCT_BLOCK_BUTTONS')
	)),
	'DEFAULT' => 'rating,price,priceRanges,quantityLimit,quantity,buttons'
);

$arAllPropList = array();
$arFilePropList = $defaultValue;
$arListPropList = array();
$arHighloadPropList = array();

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

		if ($arProp['PROPERTY_TYPE'] === 'S' && $arProp['USER_TYPE'] === 'directory' && CIBlockPriceTools::checkPropDirectory($arProp))
		{
			$arHighloadPropList[$arProp['CODE']] = $strPropName;
		}
	}

	$arAllOfferPropList = array();
	$arTreeOfferPropList = $arFileOfferPropList = $defaultValue;

	if ($boolSKU)
	{
		$rsProps = CIBlockProperty::GetList(
			array('SORT' => 'ASC', 'ID' => 'ASC'),
			array('IBLOCK_ID' => $arSKU['IBLOCK_ID'], 'ACTIVE' => 'Y')
		);
		while ($arProp = $rsProps->Fetch())
		{
			if ($arProp['ID'] == $arSKU['SKU_PROPERTY_ID'])
			{
				continue;
			}

			$arProp['USER_TYPE'] = (string)$arProp['USER_TYPE'];
			$strPropName = '['.$arProp['ID'].']'.('' != $arProp['CODE'] ? '['.$arProp['CODE'].']' : '').' '.$arProp['NAME'];

			if ($arProp['CODE'] == '')
			{
				$arProp['CODE'] = $arProp['ID'];
			}

			$arAllOfferPropList[$arProp['CODE']] = $strPropName;

			if ($arProp['PROPERTY_TYPE'] === 'F')
			{
				$arFileOfferPropList[$arProp['CODE']] = $strPropName;
			}

			if ($arProp['MULTIPLE'] != 'N')
			{
				continue;
			}

			if (
				$arProp['PROPERTY_TYPE'] === 'L'
				|| $arProp['PROPERTY_TYPE'] === 'E'
				|| (
					$arProp['PROPERTY_TYPE'] === 'S'
					&& $arProp['USER_TYPE'] === 'directory'
					&& CIBlockPriceTools::checkPropDirectory($arProp)
				)
			)
			{
				$arTreeOfferPropList[$arProp['CODE']] = $strPropName;
			}
		}
	}

	$showedProperties = [];
	if ($usePropertyFeatures)
	{
		if ($iblockExists)
		{
			$showedProperties = Iblock\Model\PropertyFeature::getDetailPageShowProperties(
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

		$arTemplateParameters['MAIN_BLOCK_PROPERTY_CODE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_MAIN_BLOCK_PROPERTY_CODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'SIZE' => (count($selected) > 5 ? 8 : 3),
			'VALUES' => $selected
		);
	}
	unset($showedProperties);

	if ($boolSKU)
	{
		$showedProperties = [];
		if ($usePropertyFeatures)
		{
			$showedProperties = Iblock\Model\PropertyFeature::getDetailPageShowProperties(
				$arSKU['IBLOCK_ID'],
				['CODE' => 'Y']
			);
			if ($showedProperties === null)
				$showedProperties = [];
		}
		else
		{
			if (!empty($arCurrentValues['OFFERS_PROPERTY_CODE']) && is_array($arCurrentValues['OFFERS_PROPERTY_CODE']))
			{
				$showedProperties = $arCurrentValues['OFFERS_PROPERTY_CODE'];
			}
		}

		if (!empty($showedProperties))
		{
			$selected = array();

			foreach ($showedProperties as $code)
			{
				if (isset($arAllOfferPropList[$code]))
				{
					$selected[$code] = $arAllOfferPropList[$code];
				}
			}

			$arTemplateParameters['MAIN_BLOCK_OFFERS_PROPERTY_CODE'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_MAIN_BLOCK_OFFERS_PROPERTY_CODE'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'SIZE' => (count($selected) > 5 ? 8 : 3),
				'VALUES' => $selected
			);
		}
		unset($showedProperties);
	}

	$arTemplateParameters['ADD_PICT_PROP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_ADD_PICT_PROP'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '-',
		'VALUES' => $arFilePropList
	);

	$arTemplateParameters['LABEL_PROP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_LABEL_PROP'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'Y',
		'VALUES' => $arListPropList
	);

	if (!empty($arCurrentValues['LABEL_PROP']))
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
			'NAME' => GetMessage('CP_BCE_TPL_LABEL_PROP_MOBILE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'SIZE' => (count($selected) > 5 ? 8 : 3),
			'VALUES' => $selected
		);
		unset($selected);

		$arTemplateParameters['LABEL_PROP_POSITION'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_LABEL_PROP_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogElementComponent::getSettingsScript($componentPath, 'position'),
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

	if ($boolSKU)
	{
		$arTemplateParameters['OFFER_ADD_PICT_PROP'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_OFFER_ADD_PICT_PROP'),
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
				'NAME' => GetMessage('CP_BCE_TPL_OFFER_TREE_PROPS'),
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

$arTemplateParameters['DISPLAY_NAME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_DISPLAY_NAME'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y'
);
$arTemplateParameters['IMAGE_RESOLUTION'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_IMAGE_RESOLUTION'),
	'TYPE' => 'LIST',
	'VALUES' => array(
		'16by9' => GetMessage('CP_BCE_TPL_IMAGE_RESOLUTION_16_BY_9'),
		'1by1' => GetMessage('CP_BCE_TPL_IMAGE_RESOLUTION_1_BY_1')
	),
	'DEFAULT' => '16by9'
);
$arTemplateParameters['SHOW_SLIDER'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_SHOW_SLIDER'),
	'TYPE' => 'CHECKBOX',
	'MULTIPLE' => 'N',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['SHOW_SLIDER']) && $arCurrentValues['SHOW_SLIDER'] === 'Y')
{
	$arTemplateParameters['SLIDER_INTERVAL'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_SLIDER_INTERVAL'),
		'TYPE' => 'TEXT',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '5000'
	);
	$arTemplateParameters['SLIDER_PROGRESS'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_SLIDER_PROGRESS'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => 'N'
	);
}

$arTemplateParameters['DETAIL_PICTURE_MODE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_DETAIL_PICTURE_MODE'),
	'TYPE' => 'LIST',
	'MULTIPLE' => 'Y',
	'DEFAULT' => array('POPUP', 'MAGNIFIER'),
	'VALUES' => array(
		'POPUP' => GetMessage('DETAIL_PICTURE_MODE_POPUP'),
		'MAGNIFIER' => GetMessage('DETAIL_PICTURE_MODE_MAGNIFIER')
	)
);
$arTemplateParameters['ADD_DETAIL_TO_SLIDER'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_ADD_DETAIL_TO_SLIDER'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N'
);
$arTemplateParameters['DISPLAY_PREVIEW_TEXT_MODE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_DISPLAY_PREVIEW_TEXT_MODE'),
	'TYPE' => 'LIST',
	'VALUES' => array(
		'H' => GetMessage('CP_BCE_TPL_DISPLAY_PREVIEW_TEXT_MODE_HIDE'),
		'E' => GetMessage('CP_BCE_TPL_DISPLAY_PREVIEW_TEXT_MODE_EMPTY_DETAIL'),
		'S' => GetMessage('CP_BCE_TPL_DISPLAY_PREVIEW_TEXT_MODE_SHOW')
	),
	'DEFAULT' => 'E'
);

if ($boolCatalog)
{
	$arTemplateParameters['PRODUCT_SUBSCRIPTION'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_PRODUCT_SUBSCRIPTION'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y'
	);
	$arTemplateParameters['SHOW_DISCOUNT_PERCENT'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_SHOW_DISCOUNT_PERCENT'),
		'TYPE' => 'CHECKBOX',
		'REFRESH' => 'Y',
		'DEFAULT' => 'N'
	);

	if (isset($arCurrentValues['SHOW_DISCOUNT_PERCENT']) && $arCurrentValues['SHOW_DISCOUNT_PERCENT'] === 'Y')
	{
		$arTemplateParameters['DISCOUNT_PERCENT_POSITION'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_DISCOUNT_PERCENT_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogElementComponent::getSettingsScript($componentPath, 'position'),
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
		'NAME' => GetMessage('CP_BCE_TPL_SHOW_OLD_PRICE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);
	$arTemplateParameters['SHOW_MAX_QUANTITY'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_SHOW_MAX_QUANTITY'),
		'TYPE' => 'LIST',
		'REFRESH' => 'Y',
		'MULTIPLE' => 'N',
		'VALUES' => array(
			'N' => GetMessage('CP_BCE_TPL_SHOW_MAX_QUANTITY_N'),
			'Y' => GetMessage('CP_BCE_TPL_SHOW_MAX_QUANTITY_Y'),
			'M' => GetMessage('CP_BCE_TPL_SHOW_MAX_QUANTITY_M')
		),
		'DEFAULT' => array('N'),
	);

	if (isset($arCurrentValues['SHOW_MAX_QUANTITY']))
	{
		if ($arCurrentValues['SHOW_MAX_QUANTITY'] !== 'N')
		{
			$arTemplateParameters['MESS_SHOW_MAX_QUANTITY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_MESS_SHOW_MAX_QUANTITY'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_SHOW_MAX_QUANTITY_DEFAULT')
			);
		}

		if ($arCurrentValues['SHOW_MAX_QUANTITY'] === 'M')
		{
			$arTemplateParameters['RELATIVE_QUANTITY_FACTOR'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_RELATIVE_QUANTITY_FACTOR'),
				'TYPE' => 'STRING',
				'DEFAULT' => '5'
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_MANY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_MESS_RELATIVE_QUANTITY_MANY'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_RELATIVE_QUANTITY_MANY_DEFAULT')
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_FEW'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_MESS_RELATIVE_QUANTITY_FEW'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_RELATIVE_QUANTITY_FEW_DEFAULT')
			);
		}
	}

	$basketActions = array(
		'BUY' => GetMessage('ADD_TO_BASKET_ACTION_BUY'),
		'ADD' => GetMessage('ADD_TO_BASKET_ACTION_ADD')
	);
	$arTemplateParameters['ADD_TO_BASKET_ACTION'] = array(
		'PARENT' => 'BASKET',
		'NAME' => GetMessage('CP_BCE_TPL_ADD_TO_BASKET_ACTION'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'VALUES' => $basketActions,
		'DEFAULT' => array('BUY'),
		'REFRESH' => 'Y'
	);

	if (!empty($arCurrentValues['ADD_TO_BASKET_ACTION']))
	{
		$selected = array();

		if (!is_array($arCurrentValues['ADD_TO_BASKET_ACTION']))
		{
			$arCurrentValues['ADD_TO_BASKET_ACTION'] = array($arCurrentValues['ADD_TO_BASKET_ACTION']);
		}

		foreach ($arCurrentValues['ADD_TO_BASKET_ACTION'] as $action)
		{
			if (isset($basketActions[$action]))
			{
				$selected[$action] = $basketActions[$action];
			}
		}

		$arTemplateParameters['ADD_TO_BASKET_ACTION_PRIMARY'] = array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BCE_TPL_ADD_TO_BASKET_ACTION_PRIMARY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $selected,
			'DEFAULT' => 'BUY',
			'REFRESH' => 'N'
		);
		unset($selected);
	}

	$arTemplateParameters['SHOW_CLOSE_POPUP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_SHOW_CLOSE_POPUP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);
}

$arTemplateParameters['MESS_BTN_BUY'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_BTN_BUY'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_BTN_BUY_DEFAULT')
);
$arTemplateParameters['MESS_BTN_ADD_TO_BASKET'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_BTN_ADD_TO_BASKET'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_BTN_ADD_TO_BASKET_DEFAULT')
);
$arTemplateParameters['MESS_BTN_SUBSCRIBE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_BTN_SUBSCRIBE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_BTN_SUBSCRIBE_DEFAULT')
);

if (isset($arCurrentValues['DISPLAY_COMPARE']) && $arCurrentValues['DISPLAY_COMPARE'] === 'Y')
{
	$arTemplateParameters['MESS_BTN_COMPARE'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => GetMessage('CP_BCE_TPL_MESS_BTN_COMPARE'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_BTN_COMPARE_DEFAULT')
	);
}

$arTemplateParameters['MESS_NOT_AVAILABLE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_NOT_AVAILABLE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_NOT_AVAILABLE_DEFAULT')
);
$arTemplateParameters['MESS_NOT_AVAILABLE_SERVICE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_NOT_AVAILABLE_SERVICE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_NOT_AVAILABLE_SERVICE_DEFAULT')
);

$arTemplateParameters['USE_VOTE_RATING'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_USE_VOTE_RATING'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y'
);
if (isset($arCurrentValues['USE_VOTE_RATING']) && $arCurrentValues['USE_VOTE_RATING'] === 'Y')
{
	$arTemplateParameters['VOTE_DISPLAY_AS_RATING'] = array(
		'NAME' => GetMessage('CP_BCE_TPL_VOTE_DISPLAY_AS_RATING'),
		'TYPE' => 'LIST',
		'VALUES' => array(
			'rating' => GetMessage('CP_BCE_TPL_VDAR_RATING'),
			'vote_avg' => GetMessage('CP_BCE_TPL_VDAR_AVERAGE'),
		),
		'DEFAULT' => 'rating'
	);
}

$arTemplateParameters['USE_COMMENTS'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_USE_COMMENTS'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y'
);

if (isset($arCurrentValues['USE_COMMENTS']) && $arCurrentValues['USE_COMMENTS'] === 'Y')
{
	if (ModuleManager::isModuleInstalled('blog'))
	{
		$arTemplateParameters['BLOG_USE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_BLOG_USE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y'
		);

		if (isset($arCurrentValues['BLOG_USE']) && $arCurrentValues['BLOG_USE'] === 'Y')
		{
			$arTemplateParameters['BLOG_URL'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_BLOG_URL'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'catalog_comments'
			);
			$arTemplateParameters['BLOG_EMAIL_NOTIFY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_BLOG_EMAIL_NOTIFY'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'N'
			);
		}
	}

	$boolRus = false;
	$rsLangs = CLanguage::GetList('id', 'asc', array('ID' => 'ru','ACTIVE' => 'Y'));
	if ($arLang = $rsLangs->Fetch())
	{
		$boolRus = true;
	}

	if ($boolRus)
	{
		$arTemplateParameters['VK_USE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_VK_USE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y'
		);

		if (isset($arCurrentValues['VK_USE']) && $arCurrentValues['VK_USE'] === 'Y')
		{
			$arTemplateParameters['VK_API_ID'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BCE_TPL_VK_API_ID'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'API_ID'
			);
		}
	}

	$arTemplateParameters['FB_USE'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_FB_USE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y'
	);

	if (isset($arCurrentValues['FB_USE']) && $arCurrentValues['FB_USE'] === 'Y')
	{
		$arTemplateParameters['FB_APP_ID'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_FB_APP_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => ''
		);
	}
}

if (ModuleManager::isModuleInstalled('highloadblock'))
{
	$arTemplateParameters['BRAND_USE'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BCE_TPL_BRAND_USE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y'
	);

	if (isset($arCurrentValues['BRAND_USE']) && $arCurrentValues['BRAND_USE'] === 'Y')
	{
		$arTemplateParameters['BRAND_PROP_CODE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCE_TPL_BRAND_PROP_CODE'),
			'TYPE' => 'LIST',
			'VALUES' => $arHighloadPropList,
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'Y'
		);
	}
}

$arTemplateParameters['MESS_PRICE_RANGES_TITLE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_PRICE_RANGES_TITLE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_PRICE_RANGES_TITLE_DEFAULT')
);
$arTemplateParameters['MESS_DESCRIPTION_TAB'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_DESCRIPTION_TAB'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_DESCRIPTION_TAB_DEFAULT')
);
$arTemplateParameters['MESS_PROPERTIES_TAB'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_PROPERTIES_TAB'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_PROPERTIES_TAB_DEFAULT')
);
$arTemplateParameters['MESS_COMMENTS_TAB'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BCE_TPL_MESS_COMMENTS_TAB'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BCE_TPL_MESS_COMMENTS_TAB_DEFAULT')
);
$arTemplateParameters['USE_ENHANCED_ECOMMERCE'] = array(
	'PARENT' => 'ANALYTICS_SETTINGS',
	'NAME' => GetMessage('CP_BCE_TPL_USE_ENHANCED_ECOMMERCE'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['USE_ENHANCED_ECOMMERCE']) && $arCurrentValues['USE_ENHANCED_ECOMMERCE'] === 'Y')
{
	$arTemplateParameters['DATA_LAYER_NAME'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => GetMessage('CP_BCE_TPL_DATA_LAYER_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'dataLayer'
	);
	$arTemplateParameters['BRAND_PROPERTY'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => GetMessage('CP_BCE_TPL_BRAND_PROPERTY'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'DEFAULT' => '',
		'VALUES' => $defaultValue + $arAllPropList
	);
}

$arTemplateParameters['USE_RATIO_IN_RANGES'] = array(
	'PARENT' => 'PRICES',
	'NAME' => GetMessage('CP_BCE_TPL_USE_RATIO_IN_RANGES'),
	'TYPE' => 'CHECKBOX',
	'HIDDEN' => isset($arCurrentValues['USE_PRICE_COUNT']) && $arCurrentValues['USE_PRICE_COUNT'] === 'Y' ? 'N' : 'Y',
	'DEFAULT' => 'Y'
);
