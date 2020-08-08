<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$iblockId = (int)($arResult['VARIABLES']['IBLOCK_ID'] ?? 0);

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.property.creation.form',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'PROPERTY_ID' => $arResult['VARIABLES']['PROPERTY_ID'],
			'IBLOCK_ID' => $iblockId,
		]
	]
);