<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;

if (!Loader::includeModule('iblock'))
	return;

$boolCatalog = Loader::includeModule('catalog');
CBitrixComponent::includeComponentClass('bitrix:catalog.section');
CBitrixComponent::includeComponentClass('bitrix:catalog.top');
CBitrixComponent::includeComponentClass('bitrix:catalog.element');

$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arSKU = false;
$boolSKU = false;
if ($boolCatalog && $iblockExists)
{
	$arSKU = CCatalogSKU::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
	$boolSKU = !empty($arSKU) && is_array($arSKU);
}

$defaultValue = array('-' => GetMessage('CP_BC_TPL_PROP_EMPTY'));

$arThemes = array();
if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('CPT_BC_TPL_THEME_SITE');
}

$arThemes['blue'] = GetMessage('CPT_BC_TPL_THEME_BLUE');
$arThemes['green'] = GetMessage('CPT_BC_TPL_THEME_GREEN');
$arThemes['red'] = GetMessage('CPT_BC_TPL_THEME_RED');
$arThemes['wood'] = GetMessage('CPT_BC_TPL_THEME_WOOD');
$arThemes['yellow'] = GetMessage('CPT_BC_TPL_THEME_YELLOW');
$arThemes['black'] = GetMessage('CP_BC_TPL_THEME_BLACK');

$documentRoot = Loader::getDocumentRoot();

$arViewModeList = array(
	'LIST' => GetMessage('CPT_BC_SECTIONS_VIEW_MODE_LIST'),
	'LINE' => GetMessage('CPT_BC_SECTIONS_VIEW_MODE_LINE'),
	'TEXT' => GetMessage('CPT_BC_SECTIONS_VIEW_MODE_TEXT'),
	'TILE' => GetMessage('CPT_BC_SECTIONS_VIEW_MODE_TILE')
);

$arFilterViewModeList = array(
	"VERTICAL" => GetMessage("CPT_BC_FILTER_VIEW_MODE_VERTICAL"),
	"HORIZONTAL" => GetMessage("CPT_BC_FILTER_VIEW_MODE_HORIZONTAL")
);

$arTemplateParameters = array(
	"SECTIONS_VIEW_MODE" => array(
		"PARENT" => "SECTIONS_SETTINGS",
		"NAME" => GetMessage('CPT_BC_SECTIONS_VIEW_MODE'),
		"TYPE" => "LIST",
		"VALUES" => $arViewModeList,
		"MULTIPLE" => "N",
		"DEFAULT" => "LIST",
		"REFRESH" => "Y"
	),
	"SECTIONS_SHOW_PARENT_NAME" => array(
		"PARENT" => "SECTIONS_SETTINGS",
		"NAME" => GetMessage('CPT_BC_SECTIONS_SHOW_PARENT_NAME'),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"
	)
);

if (isset($arCurrentValues['SECTIONS_VIEW_MODE']) && 'TILE' == $arCurrentValues['SECTIONS_VIEW_MODE'])
{
	$arTemplateParameters['SECTIONS_HIDE_SECTION_NAME'] = array(
		'PARENT' => 'SECTIONS_SETTINGS',
		'NAME' => GetMessage('CPT_BC_SECTIONS_HIDE_SECTION_NAME'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);
}

$arTemplateParameters["FILTER_VIEW_MODE"] = array(
	"PARENT" => "FILTER_SETTINGS",
	"NAME" => GetMessage('CPT_BC_FILTER_VIEW_MODE'),
	"TYPE" => "LIST",
	"VALUES" => $arFilterViewModeList,
	"DEFAULT" => "VERTICAL",
	"HIDDEN" => (!isset($arCurrentValues['USE_FILTER']) || 'N' == $arCurrentValues['USE_FILTER'] ? 'Y' : 'N')
);
$arTemplateParameters["FILTER_HIDE_ON_MOBILE"] = array(
	"PARENT" => "FILTER_SETTINGS",
	"NAME" => GetMessage("CPT_BC_FILTER_HIDE_ON_MOBILE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"HIDDEN" => (!isset($arCurrentValues['USE_FILTER']) || 'N' == $arCurrentValues['USE_FILTER'] ? 'Y' : 'N')
);
$arTemplateParameters["INSTANT_RELOAD"] = array(
	"PARENT" => "FILTER_SETTINGS",
	"NAME" => GetMessage("CPT_BC_INSTANT_RELOAD"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"HIDDEN" => (!isset($arCurrentValues['USE_FILTER']) || 'N' == $arCurrentValues['USE_FILTER'] ? 'Y' : 'N')
);
$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage("CP_BC_TPL_TEMPLATE_THEME"),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);
$arTemplateParameters['SEARCH_PAGE_RESULT_COUNT'] = array(
	'PARENT' => 'SEARCH_SETTINGS',
	'NAME' => GetMessage("CP_BC_TPL_SEARCH_PAGE_RESULT_COUNT_MSGVER_1"),
	"TYPE" => "STRING",
	"DEFAULT" => "50",
);
$arTemplateParameters['SEARCH_RESTART'] = array(
	'PARENT' => 'SEARCH_SETTINGS',
	'NAME' => GetMessage("CP_BC_TPL_SEARCH_RESTART"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
);
$arTemplateParameters['SEARCH_NO_WORD_LOGIC'] = array(
	'PARENT' => 'SEARCH_SETTINGS',
	'NAME' => GetMessage("CP_BC_TPL_SEARCH_NO_WORD_LOGIC"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
);
$arTemplateParameters['SEARCH_USE_LANGUAGE_GUESS'] = array(
	'PARENT' => 'SEARCH_SETTINGS',
	'NAME' => GetMessage("CP_BC_TPL_SEARCH_USE_LANGUAGE_GUESS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
);
$arTemplateParameters['SEARCH_CHECK_DATES'] = array(
	'PARENT' => 'SEARCH_SETTINGS',
	'NAME' => GetMessage("CP_BC_TPL_SEARCH_CHECK_DATES"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
);
$arTemplateParameters['SEARCH_USE_SEARCH_RESULT_ORDER'] = array(
	'PARENT' => 'SEARCH_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_SEARCH_USE_SEARCH_RESULT_ORDER'),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);

$arAllPropList = array();
$arListPropList = array();
$arHighloadPropList = array();
$arFilePropList = $defaultValue;

if ($iblockExists)
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

		if ('S' == $arProp['PROPERTY_TYPE'] && 'directory' == $arProp['USER_TYPE'] && CIBlockPriceTools::checkPropDirectory($arProp))
		{
			$arHighloadPropList[$arProp['CODE']] = $strPropName;
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
		if (!empty($arCurrentValues['LIST_PROPERTY_CODE']) && is_array($arCurrentValues['LIST_PROPERTY_CODE']))
		{
			$showedProperties = $arCurrentValues['LIST_PROPERTY_CODE'];
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

		$arTemplateParameters['LIST_PROPERTY_CODE_MOBILE'] = array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_PROPERTY_CODE_MOBILE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $selected
		);
	}
	unset($showedProperties);

	$arTemplateParameters['LIST_PRODUCT_BLOCKS_ORDER'] = array(
		'PARENT' => 'LIST_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_PRODUCT_BLOCKS_ORDER'),
		'TYPE' => 'CUSTOM',
		'JS_FILE' => CatalogSectionComponent::getSettingsScript('/bitrix/components/bitrix/catalog.section', 'dragdrop_order'),
		'JS_EVENT' => 'initDraggableOrderControl',
		'JS_DATA' => Json::encode(array(
			'price' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_PRICE'),
			'quantityLimit' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_QUANTITY_LIMIT'),
			'quantity' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_QUANTITY'),
			'buttons' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_BUTTONS'),
			'props' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_PROPS'),
			'sku' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_SKU')
		)),
		'DEFAULT' => 'price,props,sku,quantityLimit,quantity,buttons'
	);

	$lineElementCount = (int)$arCurrentValues['LINE_ELEMENT_COUNT'] ?: 3;
	$pageElementCount = (int)$arCurrentValues['PAGE_ELEMENT_COUNT'] ?: 30;

	$arTemplateParameters['LIST_PRODUCT_ROW_VARIANTS'] = array(
		'PARENT' => 'LIST_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_PRODUCT_ROW_VARIANTS'),
		'TYPE' => 'CUSTOM',
		'BIG_DATA' => 'Y',
		'COUNT_PARAM_NAME' => 'PAGE_ELEMENT_COUNT',
		'JS_FILE' => CatalogSectionComponent::getSettingsScript('/bitrix/components/bitrix/catalog.section', 'dragdrop_add'),
		'JS_EVENT' => 'initDraggableAddControl',
		'JS_MESSAGES' => Json::encode(array(
			'variant' => GetMessage('CP_BC_TPL_SETTINGS_VARIANT'),
			'delete' => GetMessage('CP_BC_TPL_SETTINGS_DELETE'),
			'quantity' => GetMessage('CP_BC_TPL_SETTINGS_QUANTITY'),
			'quantityBigData' => GetMessage('CP_BC_TPL_SETTINGS_QUANTITY_BIG_DATA')
		)),
		'JS_DATA' => Json::encode(CatalogSectionComponent::getTemplateVariantsMap()),
		'DEFAULT' => Json::encode(CatalogSectionComponent::predictRowVariants($lineElementCount, $pageElementCount))
	);

	$arTemplateParameters['LIST_ENLARGE_PRODUCT'] = array(
		'PARENT' => 'LIST_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_ENLARGE_PRODUCT'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'Y',
		'DEFAULT' => 'N',
		'VALUES' => array(
			'STRICT' => GetMessage('CP_BC_TPL_ENLARGE_PRODUCT_STRICT'),
			'PROP' => GetMessage('CP_BC_TPL_ENLARGE_PRODUCT_PROP')
		)
	);

	if (isset($arCurrentValues['LIST_ENLARGE_PRODUCT']) && $arCurrentValues['LIST_ENLARGE_PRODUCT'] === 'PROP')
	{
		$arTemplateParameters['LIST_ENLARGE_PROP'] = array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_ENLARGE_PROP'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $defaultValue + $arListPropList
		);
	}
	$arTemplateParameters['LIST_SHOW_SLIDER'] = array(
		'PARENT' => 'LIST_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_SHOW_SLIDER'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'REFRESH' => 'Y',
		'DEFAULT' => 'Y'
	);

	if (!isset($arCurrentValues['LIST_SHOW_SLIDER']) || $arCurrentValues['LIST_SHOW_SLIDER'] === 'Y')
	{
		$arTemplateParameters['LIST_SLIDER_INTERVAL'] = array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_SLIDER_INTERVAL'),
			'TYPE' => 'TEXT',
			'MULTIPLE' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '3000'
		);
		$arTemplateParameters['LIST_SLIDER_PROGRESS'] = array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_SLIDER_PROGRESS'),
			'TYPE' => 'CHECKBOX',
			'MULTIPLE' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => 'N'
		);
	}

	$arTemplateParameters['ADD_PICT_PROP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BC_TPL_ADD_PICT_PROP'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'ADDITIONAL_VALUES' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '-',
		'VALUES' => $arFilePropList
	);
	$arTemplateParameters['LABEL_PROP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BC_TPL_LABEL_PROP'),
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
			'NAME' => GetMessage('CP_BC_TPL_LABEL_PROP_MOBILE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'VALUES' => $selected
		);
		unset($selected);

		$arTemplateParameters['LABEL_PROP_POSITION'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BC_TPL_LABEL_PROP_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogSectionComponent::getSettingsScript('/bitrix/components/bitrix/catalog.section', 'position'),
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
		$arTemplateParameters['PRODUCT_DISPLAY_MODE'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BC_TPL_PRODUCT_DISPLAY_MODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'Y',
			'DEFAULT' => 'N',
			'VALUES' => array(
				'N' => GetMessage('CP_BC_TPL_DML_SIMPLE'),
				'Y' => GetMessage('CP_BC_TPL_DML_EXT')
			)
		);
		$arAllOfferPropList = array();
		$arFileOfferPropList = array(
			'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
		);
		$arTreeOfferPropList = array(
			'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
		);
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
			'NAME' => GetMessage('CP_BC_TPL_OFFER_ADD_PICT_PROP'),
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
				'NAME' => GetMessage('CP_BC_TPL_OFFER_TREE_PROPS'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'ADDITIONAL_VALUES' => 'N',
				'REFRESH' => 'N',
				'DEFAULT' => '-',
				'VALUES' => $arTreeOfferPropList
			);
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
		if (!empty($arCurrentValues['DETAIL_PROPERTY_CODE']) && is_array($arCurrentValues['DETAIL_PROPERTY_CODE']))
		{
			$showedProperties = $arCurrentValues['DETAIL_PROPERTY_CODE'];
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

		$arTemplateParameters['DETAIL_MAIN_BLOCK_PROPERTY_CODE'] = array(
			'PARENT' => 'DETAIL_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_MAIN_BLOCK_PROPERTY_CODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'SIZE' => (count($selected) > 5 ? 8 : 3),
			'VALUES' => $selected
		);
	}
	unset($showedProperties);
}

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
		if (!empty($arCurrentValues['DETAIL_OFFERS_PROPERTY_CODE']) && is_array($arCurrentValues['DETAIL_OFFERS_PROPERTY_CODE']))
		{
			$showedProperties = $arCurrentValues['DETAIL_OFFERS_PROPERTY_CODE'];
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

		$arTemplateParameters['DETAIL_MAIN_BLOCK_OFFERS_PROPERTY_CODE'] = array(
			'PARENT' => 'DETAIL_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_MAIN_BLOCK_OFFERS_PROPERTY_CODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'SIZE' => (count($selected) > 5 ? 8 : 3),
			'VALUES' => $selected
		);
	}
	unset($showedProperties);
}

$arTemplateParameters['DETAIL_USE_VOTE_RATING'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_USE_VOTE_RATING'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y'
);

if (isset($arCurrentValues['DETAIL_USE_VOTE_RATING']) && 'Y' == $arCurrentValues['DETAIL_USE_VOTE_RATING'])
{
	$arTemplateParameters['DETAIL_VOTE_DISPLAY_AS_RATING'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_DETAIL_VOTE_DISPLAY_AS_RATING'),
		'TYPE' => 'LIST',
		'VALUES' => array(
			'rating' => GetMessage('CP_BC_TPL_DVDAR_RATING'),
			'vote_avg' => GetMessage('CP_BC_TPL_DVDAR_AVERAGE'),
		),
		'DEFAULT' => 'rating'
	);
}

$arTemplateParameters['DETAIL_USE_COMMENTS'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_USE_COMMENTS'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y'
);

if (isset($arCurrentValues['DETAIL_USE_COMMENTS']) && 'Y' == $arCurrentValues['DETAIL_USE_COMMENTS'])
{
	if (ModuleManager::isModuleInstalled("blog"))
	{
		$arTemplateParameters['DETAIL_BLOG_USE'] = array(
			'PARENT' => 'DETAIL_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_DETAIL_BLOG_USE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y'
		);
		if (isset($arCurrentValues['DETAIL_BLOG_USE']) && $arCurrentValues['DETAIL_BLOG_USE'] == 'Y')
		{
			$arTemplateParameters['DETAIL_BLOG_URL'] = array(
				'PARENT' => 'DETAIL_SETTINGS',
				'NAME' => GetMessage('CP_BC_DETAIL_TPL_BLOG_URL'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'catalog_comments'
			);
			$arTemplateParameters['DETAIL_BLOG_EMAIL_NOTIFY'] = array(
				'PARENT' => 'DETAIL_SETTINGS',
				'NAME' => GetMessage('CP_BC_TPL_DETAIL_BLOG_EMAIL_NOTIFY'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'N'
			);
		}
	}

	$boolRus = false;
	$rsLangs = CLanguage::GetList('id', 'asc', array('ID' => 'ru',"ACTIVE" => "Y"));
	if ($arLang = $rsLangs->Fetch())
	{
		$boolRus = true;
	}

	if ($boolRus)
	{
		$arTemplateParameters['DETAIL_VK_USE'] = array(
			'PARENT' => 'DETAIL_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_DETAIL_VK_USE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y'
		);

		if (isset($arCurrentValues['DETAIL_VK_USE']) && 'Y' == $arCurrentValues['DETAIL_VK_USE'])
		{
			$arTemplateParameters['DETAIL_VK_API_ID'] = array(
				'PARENT' => 'DETAIL_SETTINGS',
				'NAME' => GetMessage('CP_BC_TPL_DETAIL_VK_API_ID'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'API_ID'
			);
		}
	}

	$arTemplateParameters['DETAIL_FB_USE'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_DETAIL_FB_USE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y'
	);

	if (isset($arCurrentValues['DETAIL_FB_USE']) && 'Y' == $arCurrentValues['DETAIL_FB_USE'])
	{
		$arTemplateParameters['DETAIL_FB_APP_ID'] = array(
			'PARENT' => 'DETAIL_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_DETAIL_FB_APP_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => ''
		);
	}
}

if (ModuleManager::isModuleInstalled("highloadblock"))
{
	$arTemplateParameters['DETAIL_BRAND_USE'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_DETAIL_BRAND_USE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y'
	);

	if (isset($arCurrentValues['DETAIL_BRAND_USE']) && 'Y' == $arCurrentValues['DETAIL_BRAND_USE'])
	{
		$arTemplateParameters['DETAIL_BRAND_PROP_CODE'] = array(
			'PARENT' => 'DETAIL_SETTINGS',
			"NAME" => GetMessage("CP_BC_TPL_DETAIL_PROP_CODE"),
			"TYPE" => "LIST",
			"VALUES" => $arHighloadPropList,
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y"
		);
	}
}

$arTemplateParameters['DETAIL_DISPLAY_NAME'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_DISPLAY_NAME'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y'
);
$arTemplateParameters['DETAIL_IMAGE_RESOLUTION'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_IMAGE_RESOLUTION'),
	'TYPE' => 'LIST',
	'VALUES' => array(
		'16by9' => GetMessage('CP_BC_TPL_DETAIL_IMAGE_RESOLUTION_16_BY_9'),
		'1by1' => GetMessage('CP_BC_TPL_DETAIL_IMAGE_RESOLUTION_1_BY_1')
	),
	'DEFAULT' => '16by9'
);
$arTemplateParameters['DETAIL_PRODUCT_INFO_BLOCK_ORDER'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_PRODUCT_INFO_BLOCK_ORDER'),
	'TYPE' => 'CUSTOM',
	'JS_FILE' => CatalogElementComponent::getSettingsScript('/bitrix/components/bitrix/catalog.element', 'dragdrop_order'),
	'JS_EVENT' => 'initDraggableOrderControl',
	'JS_DATA' => Json::encode(array(
		'sku' => GetMessage('CP_BC_TPL_DETAIL_PRODUCT_BLOCK_SKU'),
		'props' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_PROPS')
	)),
	'DEFAULT' => 'sku,props'
);
$arTemplateParameters['DETAIL_PRODUCT_PAY_BLOCK_ORDER'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_PRODUCT_PAY_BLOCK_ORDER'),
	'TYPE' => 'CUSTOM',
	'JS_FILE' => CatalogElementComponent::getSettingsScript('/bitrix/components/bitrix/catalog.element', 'dragdrop_order'),
	'JS_EVENT' => 'initDraggableOrderControl',
	'JS_DATA' => Json::encode(array(
		'rating' => GetMessage('CP_BC_TPL_DETAIL_PRODUCT_BLOCK_RATING'),
		'price' => GetMessage('CP_BC_TPL_DETAIL_PRODUCT_BLOCK_PRICE'),
		'priceRanges' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_PRICE_RANGES'),
		'quantityLimit' => GetMessage('CP_BC_TPL_DETAIL_PRODUCT_BLOCK_QUANTITY_LIMIT'),
		'quantity' => GetMessage('CP_BC_TPL_DETAIL_PRODUCT_BLOCK_QUANTITY'),
		'buttons' => GetMessage('CP_BC_TPL_DETAIL_PRODUCT_BLOCK_BUTTONS')
	)),
	'DEFAULT' => 'rating,price,priceRanges,quantityLimit,quantity,buttons'
);
$arTemplateParameters['DETAIL_SHOW_SLIDER'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_SHOW_SLIDER'),
	'TYPE' => 'CHECKBOX',
	'MULTIPLE' => 'N',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['DETAIL_SHOW_SLIDER']) && $arCurrentValues['DETAIL_SHOW_SLIDER'] === 'Y')
{
	$arTemplateParameters['DETAIL_SLIDER_INTERVAL'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_DETAIL_SLIDER_INTERVAL'),
		'TYPE' => 'TEXT',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => '5000'
	);
	$arTemplateParameters['DETAIL_SLIDER_PROGRESS'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_DETAIL_SLIDER_PROGRESS'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'REFRESH' => 'N',
		'DEFAULT' => 'N'
	);
}

$arTemplateParameters['DETAIL_DETAIL_PICTURE_MODE'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_DETAIL_PICTURE_MODE'),
	'TYPE' => 'LIST',
	'MULTIPLE' => 'Y',
	'DEFAULT' => array('POPUP', 'MAGNIFIER'),
	'VALUES' => array(
		'POPUP' => GetMessage('DETAIL_DETAIL_PICTURE_MODE_POPUP'),
		'MAGNIFIER' => GetMessage('DETAIL_DETAIL_PICTURE_MODE_MAGNIFIER'),
	)
);

$arTemplateParameters['DETAIL_ADD_DETAIL_TO_SLIDER'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_ADD_DETAIL_TO_SLIDER'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N'
);
$arTemplateParameters['DETAIL_DISPLAY_PREVIEW_TEXT_MODE'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_DISPLAY_PREVIEW_TEXT_MODE'),
	'TYPE' => 'LIST',
	'VALUES' => array(
		'H' => GetMessage('CP_BC_TPL_DETAIL_DISPLAY_PREVIEW_TEXT_MODE_HIDE'),
		'E' => GetMessage('CP_BC_TPL_DETAIL_DISPLAY_PREVIEW_TEXT_MODE_EMPTY_DETAIL'),
		'S' => GetMessage('CP_BC_TPL_DETAIL_DISPLAY_PREVIEW_TEXT_MODE_SHOW')
	),
	'DEFAULT' => 'E'
);

if ($boolCatalog)
{
	$arTemplateParameters['USE_COMMON_SETTINGS_BASKET_POPUP'] = array(
		'PARENT' => 'BASKET',
		'NAME' => GetMessage('CP_BC_TPL_USE_COMMON_SETTINGS_BASKET_POPUP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y'
	);
	$useCommonSettingsBasketPopup = (
		isset($arCurrentValues['USE_COMMON_SETTINGS_BASKET_POPUP'])
		&& $arCurrentValues['USE_COMMON_SETTINGS_BASKET_POPUP'] == 'Y'
	);
	$addToBasketActions = array(
		'BUY' => GetMessage('ADD_TO_BASKET_ACTION_BUY'),
		'ADD' => GetMessage('ADD_TO_BASKET_ACTION_ADD')
	);
	$arTemplateParameters['COMMON_ADD_TO_BASKET_ACTION'] = array(
		'PARENT' => 'BASKET',
		'NAME' => GetMessage('CP_BC_TPL_COMMON_ADD_TO_BASKET_ACTION'),
		'TYPE' => 'LIST',
		'VALUES' => $addToBasketActions,
		'DEFAULT' => 'ADD',
		'REFRESH' => 'N',
		'HIDDEN' => ($useCommonSettingsBasketPopup ? 'N' : 'Y')
	);
	$arTemplateParameters['COMMON_SHOW_CLOSE_POPUP'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BC_TPL_COMMON_SHOW_CLOSE_POPUP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
	$arTemplateParameters['MESS_PRICE_RANGES_TITLE'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_MESS_PRICE_RANGES_TITLE'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('CP_BC_TPL_MESS_PRICE_RANGES_TITLE_DEFAULT')
	);
	$arTemplateParameters['MESS_DESCRIPTION_TAB'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_MESS_DESCRIPTION_TAB'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('CP_BC_TPL_MESS_DESCRIPTION_TAB_DEFAULT')
	);
	$arTemplateParameters['MESS_PROPERTIES_TAB'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_MESS_PROPERTIES_TAB'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('CP_BC_TPL_MESS_PROPERTIES_TAB_DEFAULT')
	);
	$arTemplateParameters['MESS_COMMENTS_TAB'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_MESS_COMMENTS_TAB'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('CP_BC_TPL_MESS_COMMENTS_TAB_DEFAULT')
	);
	$arTemplateParameters['TOP_ADD_TO_BASKET_ACTION'] = array(
		'PARENT' => 'BASKET',
		'NAME' => GetMessage('CP_BC_TPL_TOP_ADD_TO_BASKET_ACTION'),
		'TYPE' => 'LIST',
		'VALUES' => $addToBasketActions,
		'DEFAULT' => 'ADD',
		'REFRESH' => 'N',
		'HIDDEN' => (!$useCommonSettingsBasketPopup ? 'N' : 'Y')
	);
	$arTemplateParameters['SECTION_ADD_TO_BASKET_ACTION'] = array(
		'PARENT' => 'BASKET',
		'NAME' => GetMessage('CP_BC_TPL_SECTION_ADD_TO_BASKET_ACTION'),
		'TYPE' => 'LIST',
		'VALUES' => $addToBasketActions,
		'DEFAULT' => 'ADD',
		'REFRESH' => 'N',
		'HIDDEN' => (!$useCommonSettingsBasketPopup ? 'N' : 'Y')
	);
	$arTemplateParameters['DETAIL_ADD_TO_BASKET_ACTION'] = array(
		'PARENT' => 'BASKET',
		'NAME' => GetMessage('CP_BC_TPL_DETAIL_ADD_TO_BASKET_ACTION'),
		'TYPE' => 'LIST',
		'VALUES' => $addToBasketActions,
		'DEFAULT' => 'BUY',
		'REFRESH' => 'Y',
		'MULTIPLE' => 'Y',
		'HIDDEN' => (!$useCommonSettingsBasketPopup ? 'N' : 'Y')
	);

	if (!$useCommonSettingsBasketPopup && !empty($arCurrentValues['DETAIL_ADD_TO_BASKET_ACTION']))
	{
		$selected = array();

		if (!is_array($arCurrentValues['DETAIL_ADD_TO_BASKET_ACTION']))
		{
			$arCurrentValues['DETAIL_ADD_TO_BASKET_ACTION'] = array($arCurrentValues['DETAIL_ADD_TO_BASKET_ACTION']);
		}

		foreach ($arCurrentValues['DETAIL_ADD_TO_BASKET_ACTION'] as $action)
		{
			if (isset($addToBasketActions[$action]))
			{
				$selected[$action] = $addToBasketActions[$action];
			}
		}

		$arTemplateParameters['DETAIL_ADD_TO_BASKET_ACTION_PRIMARY'] = array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BC_TPL_DETAIL_ADD_TO_BASKET_ACTION_PRIMARY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $selected,
			'DEFAULT' => 'BUY',
			'REFRESH' => 'N'
		);
		unset($selected);
	}

	$arTemplateParameters['PRODUCT_SUBSCRIPTION'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BC_TPL_PRODUCT_SUBSCRIPTION'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	);
	$arTemplateParameters['SHOW_DISCOUNT_PERCENT'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BC_TPL_SHOW_DISCOUNT_PERCENT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['SHOW_DISCOUNT_PERCENT']) && $arCurrentValues['SHOW_DISCOUNT_PERCENT'] === 'Y')
	{
		$arTemplateParameters['DISCOUNT_PERCENT_POSITION'] = array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BC_TPL_DISCOUNT_PERCENT_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogSectionComponent::getSettingsScript('/bitrix/components/bitrix/catalog.section', 'position'),
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
		'NAME' => GetMessage('CP_BC_TPL_SHOW_OLD_PRICE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
	$arTemplateParameters['SHOW_MAX_QUANTITY'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CP_BC_TPL_SHOW_MAX_QUANTITY'),
		'TYPE' => 'LIST',
		'REFRESH' => 'Y',
		'MULTIPLE' => 'N',
		'VALUES' => array(
			'N' => GetMessage('CP_BC_TPL_SHOW_MAX_QUANTITY_N'),
			'Y' => GetMessage('CP_BC_TPL_SHOW_MAX_QUANTITY_Y'),
			'M' => GetMessage('CP_BC_TPL_SHOW_MAX_QUANTITY_M')
		),
		'DEFAULT' => array('N')
	);

	if (isset($arCurrentValues['SHOW_MAX_QUANTITY']))
	{
		if ($arCurrentValues['SHOW_MAX_QUANTITY'] !== 'N')
		{
			$arTemplateParameters['MESS_SHOW_MAX_QUANTITY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BC_TPL_MESS_SHOW_MAX_QUANTITY'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BC_TPL_MESS_SHOW_MAX_QUANTITY_DEFAULT')
			);
		}

		if ($arCurrentValues['SHOW_MAX_QUANTITY'] === 'M')
		{
			$arTemplateParameters['RELATIVE_QUANTITY_FACTOR'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BC_TPL_RELATIVE_QUANTITY_FACTOR'),
				'TYPE' => 'STRING',
				'DEFAULT' => '5'
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_MANY'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BC_TPL_MESS_RELATIVE_QUANTITY_MANY'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BC_TPL_MESS_RELATIVE_QUANTITY_MANY_DEFAULT')
			);
			$arTemplateParameters['MESS_RELATIVE_QUANTITY_FEW'] = array(
				'PARENT' => 'VISUAL',
				'NAME' => GetMessage('CP_BC_TPL_MESS_RELATIVE_QUANTITY_FEW'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CP_BC_TPL_MESS_RELATIVE_QUANTITY_FEW_DEFAULT')
			);
		}
	}
}

$arTemplateParameters['LAZY_LOAD'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_LAZY_LOAD'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

$arTemplateParameters['MESS_BTN_LAZY_LOAD'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_MESS_BTN_LAZY_LOAD'),
	'TYPE' => 'TEXT',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_BTN_LAZY_LOAD_DEFAULT'),
	'HIDDEN' => (
		(isset($arCurrentValues['LAZY_LOAD']) && $arCurrentValues['LAZY_LOAD'] === 'Y')
		|| (isset($arCurrentValues['DISPLAY_TOP_PAGER']) && $arCurrentValues['DISPLAY_TOP_PAGER'] === 'Y')
		|| (!isset($arCurrentValues['DISPLAY_BOTTOM_PAGER']) || $arCurrentValues['DISPLAY_BOTTOM_PAGER'] !== 'N')
		? 'N'
		: 'Y'
	)
);

$arTemplateParameters['LOAD_ON_SCROLL'] = array(
	'PARENT' => 'PAGER_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_LOAD_ON_SCROLL'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'HIDDEN' => (isset($arCurrentValues['LAZY_LOAD']) && $arCurrentValues['LAZY_LOAD'] === 'Y' ? 'N' : 'Y')
);

$arTemplateParameters['MESS_BTN_BUY'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BC_TPL_MESS_BTN_BUY'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_BTN_BUY_DEFAULT')
);
$arTemplateParameters['MESS_BTN_ADD_TO_BASKET'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BC_TPL_MESS_BTN_ADD_TO_BASKET'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_BTN_ADD_TO_BASKET_DEFAULT')
);
$arTemplateParameters['MESS_BTN_COMPARE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BC_TPL_MESS_BTN_COMPARE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_BTN_COMPARE_DEFAULT')
);
$arTemplateParameters['MESS_BTN_DETAIL'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BC_TPL_MESS_BTN_DETAIL'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_BTN_DETAIL_DEFAULT')
);
$arTemplateParameters['MESS_NOT_AVAILABLE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BC_TPL_MESS_NOT_AVAILABLE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_NOT_AVAILABLE_DEFAULT')
);
$arTemplateParameters['MESS_NOT_AVAILABLE_SERVICE'] = [
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BC_TPL_MESS_NOT_AVAILABLE_SERVICE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_NOT_AVAILABLE_SERVICE_DEFAULT'),
];

$arTemplateParameters['MESS_BTN_SUBSCRIBE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CP_BC_TPL_MESS_BTN_SUBSCRIBE'),
	'TYPE' => 'STRING',
	'DEFAULT' => GetMessage('CP_BC_TPL_MESS_BTN_SUBSCRIBE_DEFAULT')
);

if (ModuleManager::isModuleInstalled("sale"))
{
	$arTemplateParameters['USE_SALE_BESTSELLERS'] = array(
		'NAME' => GetMessage('CP_BC_TPL_USE_SALE_BESTSELLERS'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y'
	);

	$arTemplateParameters['USE_BIG_DATA'] = array(
		'PARENT' => 'BIG_DATA_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_USE_BIG_DATA'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
		'REFRESH' => 'Y'
	);
	if (!isset($arCurrentValues['USE_BIG_DATA']) || $arCurrentValues['USE_BIG_DATA'] == 'Y')
	{
		$rcmTypeList = array(
			'personal' => GetMessage('CP_BC_TPL_RCM_PERSONAL'),
			'bestsell' => GetMessage('CP_BC_TPL_RCM_BESTSELLERS'),
			'similar_sell' => GetMessage('CP_BC_TPL_RCM_SOLD_WITH'),
			'similar_view' => GetMessage('CP_BC_TPL_RCM_VIEWED_WITH'),
			'similar' => GetMessage('CP_BC_TPL_RCM_SIMILAR'),
			'any_similar' => GetMessage('CP_BC_TPL_RCM_SIMILAR_ANY'),
			'any_personal' => GetMessage('CP_BC_TPL_RCM_PERSONAL_WBEST'),
			'any' => GetMessage('CP_BC_TPL_RCM_RAND')
		);
		$arTemplateParameters['BIG_DATA_RCM_TYPE'] = array(
			'PARENT' => 'BIG_DATA_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_BIG_DATA_RCM_TYPE'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'personal',
			'VALUES' => $rcmTypeList
		);
		unset($rcmTypeList);
	}
}

if (isset($arCurrentValues['SHOW_TOP_ELEMENTS']) && 'Y' == $arCurrentValues['SHOW_TOP_ELEMENTS'])
{
	$arTemplateParameters['TOP_VIEW_MODE'] = array(
		'PARENT' => 'TOP_SETTINGS',
		'NAME' => GetMessage('CPT_BC_TPL_TOP_VIEW_MODE'),
		'TYPE' => 'LIST',
		'VALUES' => array(
			'BANNER' => GetMessage('CPT_BC_TPL_VIEW_MODE_BANNER'),
			'SLIDER' => GetMessage('CPT_BC_TPL_VIEW_MODE_SLIDER'),
			'SECTION' => GetMessage('CPT_BC_TPL_VIEW_MODE_SECTION')
		),
		'MULTIPLE' => 'N',
		'DEFAULT' => 'SECTION',
		'REFRESH' => 'Y'
	);

	if (isset($arCurrentValues['TOP_VIEW_MODE']) && ('SLIDER' == $arCurrentValues['TOP_VIEW_MODE'] || 'BANNER' == $arCurrentValues['TOP_VIEW_MODE']))
	{
		$arTemplateParameters['TOP_ROTATE_TIMER'] = array(
			'PARENT' => 'TOP_SETTINGS',
			'NAME' => GetMessage('CPT_BC_TPL_TOP_ROTATE_TIMER'),
			'TYPE' => 'STRING',
			'DEFAULT' => '30'
		);
	}

	if (isset($arCurrentValues['TOP_VIEW_MODE']) && $arCurrentValues['TOP_VIEW_MODE'] === 'SECTION')
	{
		if (!empty($arCurrentValues['TOP_PROPERTY_CODE']))
		{
			$selected = array();

			foreach ($arCurrentValues['TOP_PROPERTY_CODE'] as $code)
			{
				if (isset($arAllPropList[$code]))
				{
					$selected[$code] = $arAllPropList[$code];
				}
			}

			$arTemplateParameters['TOP_PROPERTY_CODE_MOBILE'] = array(
				'PARENT' => 'TOP_SETTINGS',
				'NAME' => GetMessage('CP_BC_TPL_PROPERTY_CODE_MOBILE'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'VALUES' => $selected
			);
		}

		$arTemplateParameters['TOP_PRODUCT_BLOCKS_ORDER'] = array(
			'PARENT' => 'TOP_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_PRODUCT_BLOCKS_ORDER'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogTopComponent::getSettingsScript('/bitrix/components/bitrix/catalog.top', 'dragdrop_order'),
			'JS_EVENT' => 'initDraggableOrderControl',
			'JS_DATA' => Json::encode(array(
				'price' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_PRICE'),
				'quantityLimit' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_QUANTITY_LIMIT'),
				'quantity' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_QUANTITY'),
				'buttons' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_BUTTONS'),
				'props' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_PROPS'),
				'sku' => GetMessage('CP_BC_TPL_PRODUCT_BLOCK_SKU')
			)),
			'DEFAULT' => 'price,props,sku,quantityLimit,quantity,buttons'
		);

		$lineElementCount = (int)$arCurrentValues['TOP_LINE_ELEMENT_COUNT'] ?: 3;
		$pageElementCount = (int)$arCurrentValues['TOP_ELEMENT_COUNT'] ?: 9;

		$arTemplateParameters['TOP_PRODUCT_ROW_VARIANTS'] = array(
			'PARENT' => 'TOP_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_PRODUCT_ROW_VARIANTS'),
			'TYPE' => 'CUSTOM',
			'BIG_DATA' => 'N',
			'COUNT_PARAM_NAME' => 'TOP_ELEMENT_COUNT',
			'JS_FILE' => CatalogTopComponent::getSettingsScript('/bitrix/components/bitrix/catalog.top', 'dragdrop_add'),
			'JS_EVENT' => 'initDraggableAddControl',
			'JS_MESSAGES' => Json::encode(array(
				'variant' => GetMessage('CP_BC_TPL_SETTINGS_VARIANT'),
				'delete' => GetMessage('CP_BC_TPL_SETTINGS_DELETE'),
				'quantity' => GetMessage('CP_BC_TPL_SETTINGS_QUANTITY'),
				'quantityBigData' => GetMessage('CP_BC_TPL_SETTINGS_QUANTITY_BIG_DATA')
			)),
			'JS_DATA' => Json::encode(CatalogTopComponent::getTemplateVariantsMap()),
			'DEFAULT' => Json::encode(CatalogTopComponent::predictRowVariants($lineElementCount, $pageElementCount))
		);

		$arTemplateParameters['TOP_ENLARGE_PRODUCT'] = array(
			'PARENT' => 'TOP_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_ENLARGE_PRODUCT'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'Y',
			'DEFAULT' => 'N',
			'VALUES' => array(
				'STRICT' => GetMessage('CP_BC_TPL_ENLARGE_PRODUCT_STRICT'),
				'PROP' => GetMessage('CP_BC_TPL_ENLARGE_PRODUCT_PROP')
			)
		);

		if (isset($arCurrentValues['TOP_ENLARGE_PRODUCT']) && $arCurrentValues['TOP_ENLARGE_PRODUCT'] === 'PROP')
		{
			$arTemplateParameters['TOP_ENLARGE_PROP'] = array(
				'PARENT' => 'TOP_SETTINGS',
				'NAME' => GetMessage('CP_BC_TPL_ENLARGE_PROP'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'N',
				'ADDITIONAL_VALUES' => 'N',
				'REFRESH' => 'N',
				'DEFAULT' => '-',
				'VALUES' => $defaultValue + $arListPropList
			);
		}
		$arTemplateParameters['TOP_SHOW_SLIDER'] = array(
			'PARENT' => 'TOP_SETTINGS',
			'NAME' => GetMessage('CP_BC_TPL_SHOW_SLIDER'),
			'TYPE' => 'CHECKBOX',
			'MULTIPLE' => 'N',
			'REFRESH' => 'Y',
			'DEFAULT' => 'Y'
		);

		if (!isset($arCurrentValues['TOP_SHOW_SLIDER']) || $arCurrentValues['TOP_SHOW_SLIDER'] === 'Y')
		{
			$arTemplateParameters['TOP_SLIDER_INTERVAL'] = array(
				'PARENT' => 'TOP_SETTINGS',
				'NAME' => GetMessage('CP_BC_TPL_SLIDER_INTERVAL'),
				'TYPE' => 'TEXT',
				'MULTIPLE' => 'N',
				'REFRESH' => 'N',
				'DEFAULT' => '3000'
			);
			$arTemplateParameters['TOP_SLIDER_PROGRESS'] = array(
				'PARENT' => 'TOP_SETTINGS',
				'NAME' => GetMessage('CP_BC_TPL_SLIDER_PROGRESS'),
				'TYPE' => 'CHECKBOX',
				'MULTIPLE' => 'N',
				'REFRESH' => 'N',
				'DEFAULT' => 'N'
			);
		}
	}
}

if (isset($arCurrentValues['USE_COMPARE']) && $arCurrentValues['USE_COMPARE'] == 'Y')
{
	$arTemplateParameters['COMPARE_POSITION_FIXED'] = array(
		'PARENT' => 'COMPARE_SETTINGS',
		'NAME' => GetMessage('CPT_BC_TPL_COMPARE_POSITION_FIXED'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
		'REFRESH' => 'Y'
	);
	if (!isset($arCurrentValues['COMPARE_POSITION_FIXED']) || $arCurrentValues['COMPARE_POSITION_FIXED'] == 'Y')
	{
		$positionList = array(
			'top left' => GetMessage('CPT_BC_TPL_PARAM_COMPARE_POSITION_TOP_LEFT'),
			'top right' => GetMessage('CPT_BC_TPL_PARAM_COMPARE_POSITION_TOP_RIGHT'),
			'bottom left' => GetMessage('CPT_BC_TPL_PARAM_COMPARE_POSITION_BOTTOM_LEFT'),
			'bottom right' => GetMessage('CPT_BC_TPL_PARAM_COMPARE_POSITION_BOTTOM_RIGHT')
		);
		$arTemplateParameters['COMPARE_POSITION'] = array(
			'PARENT' => 'COMPARE_SETTINGS',
			'NAME' => GetMessage('CPT_BC_TPL_COMPARE_POSITION'),
			'TYPE' => 'LIST',
			'VALUES' => $positionList,
			'DEFAULT' => 'top left'
		);
		unset($positionList);
	}
}

$arTemplateParameters['SIDEBAR_SECTION_SHOW'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CPT_SIDEBAR_SECTION_SHOW'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y',
	'SORT' => 800
);
$arTemplateParameters['SIDEBAR_DETAIL_SHOW'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CPT_SIDEBAR_DETAIL_SHOW'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'SORT' => 800
);
$arTemplateParameters['SIDEBAR_PATH'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('CPT_SIDEBAR_PATH'),
	'TYPE' => 'STRING',
	'SORT' => 800
);

$arTemplateParameters['USE_ENHANCED_ECOMMERCE'] = array(
	'PARENT' => 'ANALYTICS_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_USE_ENHANCED_ECOMMERCE'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['USE_ENHANCED_ECOMMERCE']) && $arCurrentValues['USE_ENHANCED_ECOMMERCE'] === 'Y')
{
	$arTemplateParameters['DATA_LAYER_NAME'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_DATA_LAYER_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'dataLayer'
	);
	$arTemplateParameters['BRAND_PROPERTY'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => GetMessage('CP_BC_TPL_BRAND_PROPERTY'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'DEFAULT' => '',
		'VALUES' => $defaultValue + $arAllPropList
	);
}

$arTemplateParameters['DETAIL_SHOW_POPULAR'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_SHOW_POPULAR'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y'
);
$arTemplateParameters['DETAIL_SHOW_VIEWED'] = array(
	'PARENT' => 'DETAIL_SETTINGS',
	'NAME' => GetMessage('CP_BC_TPL_DETAIL_SHOW_VIEWED'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y'
);

// hack to hide component parameters by templates
$arTemplateParameters['HIDE_USE_ALSO_BUY'] = array();
