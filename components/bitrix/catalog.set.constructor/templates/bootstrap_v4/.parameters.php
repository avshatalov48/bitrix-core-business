<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

if (!Loader::includeModule('catalog'))
	return;

$arThemes = [
	'blue' => GetMessage('CP_CSC_TPL_THEME_BLUE'),
	'green' => GetMessage('CP_CSC_TPL_THEME_GREEN'),
	'red' => GetMessage('CP_CSC_TPL_THEME_RED'),
	'yellow' => GetMessage('CP_CSC_TPL_THEME_YELLOW'),
];

if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('CP_CSC_TPL_THEME_SITE');
}


$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage("CP_CSC_TPL_TEMPLATE_THEME"),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);
?>