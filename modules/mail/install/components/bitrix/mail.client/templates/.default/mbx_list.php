<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \CMain $APPLICATION */
/** @var array $arResult */

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:mail.mailbox.list',
		'POPUP_COMPONENT_PARAMS' => $arResult,
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'DEFAULT_THEME_ID' => 'light:mail',
		'USE_UI_TOOLBAR' => 'Y',
		'USE_PADDING' => false,
		'PLAIN_VIEW' => false,
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => "/stream/",
	],
);
