<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arThemes = array();
if (IsModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('CP_BCT_TPL_THEME_SITE');
}

$arThemesMessages = array(
	"blue" => GetMessage("CP_BCT_TPL_THEME_BLUE"),
	"wood" => GetMessage("CP_BCT_TPL_THEME_WOOD"),
	"yellow" => GetMessage("CP_BCT_TPL_THEME_YELLOW"),
	"green" => GetMessage("CP_BCT_TPL_THEME_GREEN"),
	"red" => GetMessage("CP_BCT_TPL_THEME_RED"),
	"black" => GetMessage("CP_BCT_TPL_THEME_BLACK")
);
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
if (is_dir($dir) && $directory = opendir($dir))
{
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : mb_strtoupper(mb_substr($file, 0, 1)).mb_strtolower(mb_substr($file, 1)));
	}
	closedir($directory);
}

$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage("CP_BCT_TPL_TEMPLATE_THEME"),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);

$arViews = array(
	"vertical" => GetMessage("CP_BCT_TPL_FILTER_VIEW_V"),
	"horizontal" => GetMessage("CP_BCT_TPL_FILTER_VIEW_H")
);
$arTemplateParameters['FILTER_VIEW_MODE'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage("CP_BCT_TPL_FILTER_VIEW"),
	'TYPE' => 'LIST',
	'VALUES' => $arViews,
	'DEFAULT' => 'vertical',
	'ADDITIONAL_VALUES' => 'Y',
	'REFRESH' => "Y"
);

if ($arCurrentValues["FILTER_VIEW_MODE"] == "vertical")
{
	$arPopupPosition = array(
		"left" => GetMessage("CP_BCT_TPL_POPUP_POSITION_LEFT"),
		"right" => GetMessage("CP_BCT_TPL_POPUP_POSITION_RIGHT")
	);
	$arTemplateParameters['POPUP_POSITION'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage("CP_BCT_TPL_POPUP_POSITION"),
		'TYPE' => 'LIST',
		'VALUES' => $arPopupPosition,
		'DEFAULT' => 'left',
		'ADDITIONAL_VALUES' => 'Y'
	);
}

$arTemplateParameters['DISPLAY_ELEMENT_COUNT'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage('TP_BCSF_DISPLAY_ELEMENT_COUNT'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y',
);
?>