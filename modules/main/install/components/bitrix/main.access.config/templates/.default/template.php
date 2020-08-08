<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('socialnetwork');
$componentParams = $arParams['POPUP_COMPONENT_PARAMS'] ?? [];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => $arParams['ACCESS_COMPONENT_NAME'],
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParams,
		'USE_UI_TOOLBAR' => 'Y',
		'USE_PADDING' => false,
		'PLAIN_VIEW' => false,
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => $arParams['BACK_URL']
	]
);

?>