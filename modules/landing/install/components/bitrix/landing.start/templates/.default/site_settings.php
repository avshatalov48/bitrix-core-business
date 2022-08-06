<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CBitrixComponent $component */

$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:landing.settings',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'SITE_ID' => $arResult['VARS']['site_edit'],
			'TYPE' => $arParams['TYPE'],
			'PAGES' => [
				'PAGE_URL_SITE_EDIT' => $arParams['PAGE_URL_SITE_EDIT'],
				'PAGE_URL_SITE_DESIGN' => $arParams['PAGE_URL_SITE_DESIGN'],
			],
		],
		'USE_PADDING' => false,
		'PAGE_MODE' => false,
		'CLOSE_AFTER_SAVE' => false,
		'RELOAD_GRID_AFTER_SAVE' => false,
		'RELOAD_PAGE_AFTER_SAVE' => true,
	]
);