<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

/** @var \CBitrixComponentTemplate $this  */
/** @var \CatalogCatalogControllerComponent $component */
/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

if ($component->isUiCatalog())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:catalog.productcard.controller',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '.default',
			'POPUP_COMPONENT_PARAMS' => [
				'SEF_MODE' => 'Y',
				'SEF_FOLDER' => $arParams['SEF_FOLDER'],
			],
			'USE_PADDING' => false,
			'USE_UI_TOOLBAR' => 'Y',
		],
		$component
	);
}
else
{
	$arResult['PAGE_DESCRIPTION']['SEF_FOLDER'] = $this->GetFolder().'/';
	$arResult['PAGE_DESCRIPTION']['PAGE_PATH'] = 'include/product_detail.php';

	$APPLICATION->IncludeComponent(
		"bitrix:crm.admin.page.include",
		"",
		$arResult['PAGE_DESCRIPTION'],
		$component,
		['HIDE_ICONS' => 'Y']
	);
}
