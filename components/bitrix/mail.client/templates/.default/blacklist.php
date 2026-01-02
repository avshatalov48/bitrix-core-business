<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \CMain $APPLICATION */
/** @var array $arResult */

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:mail.blacklist.list',
		'POPUP_COMPONENT_PARAMS' => $arResult,
		'USE_UI_TOOLBAR' => 'Y',
	]
);
