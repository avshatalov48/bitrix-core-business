<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


$arThemes = array();
if (\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('SB_TPL_THEME_SITE');
}
$arThemesList = array(
	'blue' => GetMessage('SB_TPL_THEME_BLUE'),
	'green' => GetMessage('SB_TPL_THEME_GREEN'),
	'red' => GetMessage('SB_TPL_THEME_RED'),
	'wood' => GetMessage('SB_TPL_THEME_WOOD'),
	'yellow' => GetMessage('SB_TPL_THEME_YELLOW'),
	'black' => GetMessage('SB_TPL_THEME_BLACK')
);
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
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
	'NAME' => GetMessage("SB_TPL_TEMPLATE_THEME"),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);

?>
