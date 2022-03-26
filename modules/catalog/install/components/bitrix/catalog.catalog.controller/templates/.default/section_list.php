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

if ($component->isIframeMode())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:crm.admin.page.include',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $arResult['PAGE_DESCRIPTION'],
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'USE_PADDING' => true,
			'USE_UI_TOOLBAR' => 'Y',
		]
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:crm.admin.page.include",
		"",
		$arResult['PAGE_DESCRIPTION'],
		$component,
		['HIDE_ICONS' => 'Y']
	);
}
