<?
use Bitrix\Main\ModuleManager;
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arThemes = array();
if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('CP_BCC_TPL_THEME_SITE');
}

$arThemesList = array(
	'blue' => GetMessage('CP_BCC_TPL_THEME_BLUE'),
	'green' => GetMessage('CP_BCC_TPL_THEME_GREEN'),
	'red' => GetMessage('CP_BCC_TPL_THEME_RED'),
	'wood' => GetMessage('CP_BCC_TPL_THEME_WOOD'),
	'yellow' => GetMessage('CP_BCC_TPL_THEME_YELLOW'),
	'black' => GetMessage('CP_BCC_TPL_THEME_BLACK')
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

$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage("CP_BCC_TPL_TEMPLATE_THEME"),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);
?>