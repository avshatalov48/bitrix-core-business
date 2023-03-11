<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CAllMain $APPLICATION */
/** @var \CBitrixComponentTemplate $this */
/** @var array $arResult */
/** @var array $arParams */

global $APPLICATION;

use \Bitrix\UI\Toolbar\Facade\Toolbar;

$APPLICATION->SetTitle($arResult['TITLE']);
if (isset($arResult['TOOLBAR']) && $arResult['TOOLBAR'])
{
	Toolbar::addButton($arResult['TOOLBAR']);
}
Toolbar::deleteFavoriteStar();

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	array(
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.integration.list',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'CACHE_TIME' => '3600',
			'CACHE_TYPE' => 'Y',
			'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'] ?? '',
			'TYPE' => 'INDEX',
			'SET_TITLE' => 'N',
			'CODE' => '',
			'SHOW_MENU' => 'Y',
			'PATH_TO_SECTION' => $arResult['PATH_TO_SECTION']
		],
		"USE_UI_TOOLBAR" => "Y",
		'USE_PADDING' => false,
	)
);