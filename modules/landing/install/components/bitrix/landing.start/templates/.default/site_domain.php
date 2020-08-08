<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:landing.site_domain',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'TYPE' => $arParams['TYPE'],
			'SITE_ID' => $arResult['VARS']['site_edit']
		],
		'USE_PADDING' => true,
		'PAGE_MODE' => false
	]
);