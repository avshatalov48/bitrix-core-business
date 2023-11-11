<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

/** @var \CBitrixComponentTemplate $this  */
/** @var \CatalogCatalogControllerComponent $component */
/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

$this->setViewTarget('above_pagetitle');
$component->showCatalogControlPanel();
$this->endViewTarget();

$componentParams = [
	'IBLOCK_ID' => $arResult['IBLOCK_ID'],
	'SECTION_ID' => (int)($arResult['VARIABLES']['SECTION_ID'] ?? 0),
	'URL_BUILDER' => $arResult['URL_BUILDER'],
];

if ($component->isIframeMode())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:catalog.product.grid',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $componentParams,
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'USE_PADDING' => true,
			'USE_UI_TOOLBAR' => 'Y',
		]
	);
}
else
{
	$APPLICATION->IncludeComponent(
		'bitrix:catalog.product.grid',
		'',
		$componentParams,
		$component,
		['HIDE_ICONS' => 'Y']
	);
}
