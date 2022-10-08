<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager,
	Bitrix\Catalog;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog'))
	return;

$arTemplateParameters['LINE_ELEMENT_COUNT'] = array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("CATALOG_RECOMMENDED_PRODUCTS_LINE_ELEMENT_COUNT"),
	"TYPE" => "STRING",
	"DEFAULT" => "3",
	"SORT" => 1000

);

$arThemes = array();
if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('CATALOG_RECOMMENDED_PRODUCTS_TPL_THEME_SITE');
}
$arThemesList = array(
	'blue' => GetMessage('CATALOG_RECOMMENDED_PRODUCTS_TPL_THEME_BLUE'),
	'green' => GetMessage('CATALOG_RECOMMENDED_PRODUCTS_TPL_THEME_GREEN'),
	'red' => GetMessage('CATALOG_RECOMMENDED_PRODUCTS_TPL_THEME_RED'),
	'wood' => GetMessage('CATALOG_RECOMMENDED_PRODUCTS_TPL_THEME_WOOD'),
	'yellow' => GetMessage('CATALOG_RECOMMENDED_PRODUCTS_TPL_THEME_YELLOW'),
	'black' => GetMessage('CATALOG_RECOMMENDED_PRODUCTS_TPL_THEME_BLACK')
);
$dir = trim(preg_replace("'[\\\\/]+'", "/", __DIR__."/themes/"));
if (is_dir($dir))
{
	foreach ($arThemesList as $themeID => $themeName)
	{
		if (!is_file($dir.$themeID.'/style.css'))
			continue;
		$arThemes[$themeID] = $themeName;
	}
}

$sortFields = $arSort = \CIBlockParameters::GetElementSortFields(
	array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO')
);
$sortOrder = array(
	'ASC' => GetMessage('BX_CRP_SORT_ORDER_ASC'),
	'DESC' => GetMessage('BX_CRP_SORT_ORDER_DESC'),
);

$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage("CATALOG_RECOMMENDED_PRODUCTS_TPL_TEMPLATE_THEME"),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);

$arTemplateParameters['ELEMENT_SORT_FIELD'] = array(
	'PARENT' => 'SORT_SETTINGS',
	'NAME' => GetMessage('BX_CRP_IBLOCK_ELEMENT_SORT_FIELD'),
	'TYPE' => 'LIST',
	'VALUES' => $sortFields,
	'ADDITIONAL_VALUES' => 'Y',
	'DEFAULT' => 'SORT',
);
$arTemplateParameters['ELEMENT_SORT_ORDER'] = array(
	'PARENT' => 'SORT_SETTINGS',
	'NAME' => GetMessage('BX_CRP_IBLOCK_ELEMENT_SORT_ORDER'),
	'TYPE' => 'LIST',
	'VALUES' => $sortOrder,
	'DEFAULT' => 'ASC',
	'ADDITIONAL_VALUES' => 'Y',
);
$arTemplateParameters['ELEMENT_SORT_FIELD2'] = array(
	'PARENT' => 'SORT_SETTINGS',
	'NAME' => GetMessage('BX_CRP_IBLOCK_ELEMENT_SORT_FIELD2'),
	'TYPE' => 'LIST',
	'VALUES' => $sortFields,
	'ADDITIONAL_VALUES' => 'Y',
	'DEFAULT' => 'ID',
);
$arTemplateParameters['ELEMENT_SORT_ORDER2'] = array(
	'PARENT' => 'SORT_SETTINGS',
	'NAME' => GetMessage('BX_CRP_IBLOCK_ELEMENT_SORT_ORDER2'),
	'TYPE' => 'LIST',
	'VALUES' => $sortOrder,
	'DEFAULT' => 'DESC',
	'ADDITIONAL_VALUES' => 'Y',
);

$arTemplateParameters['PRODUCT_DISPLAY_MODE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('BX_CRP_TPL_PRODUCT_DISPLAY_MODE'),
	'TYPE' => 'LIST',
	'MULTIPLE' => 'N',
	'ADDITIONAL_VALUES' => 'N',
	'DEFAULT' => 'N',
	'VALUES' => array(
		'N' => GetMessage('BX_CRP_TPL_DML_SIMPLE'),
		'Y' => GetMessage('BX_CRP_TPL_DML_EXT')
	)
);