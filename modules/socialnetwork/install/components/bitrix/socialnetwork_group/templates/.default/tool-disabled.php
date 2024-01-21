<?php

/** @var array $arResult */

global $APPLICATION;

$componentParameters = [
	'LIMIT_CODE' => $arResult['LIMIT_CODE'],
	'MODULE' => 'socialnetwork',
	'SOURCE' => 'group',
];

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:intranet.settings.tool.stub',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParameters,
	]
);