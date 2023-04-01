<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.seo.detail',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'IBLOCK_ID' => $arResult['VARIABLES']['IBLOCK_ID'],
			'MODE' => 'MODE_SECTION',
			'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
		]
	]
);
