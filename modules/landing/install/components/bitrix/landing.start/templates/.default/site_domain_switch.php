<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:landing.site_domain_switch',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'SITE_ID' => $arResult['VARS']['site_edit'],
			'MODE' => 'DELETE_GIFT'
		],
		'USE_PADDING' => false,
		'PAGE_MODE' => false
	]
);