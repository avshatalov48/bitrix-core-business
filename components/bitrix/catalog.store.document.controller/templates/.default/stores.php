<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogStoreDocumentControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

global $APPLICATION;

$componentParams = [
	'PATH_TO' => $arResult['PATH_TO'],
];

\Bitrix\Main\UI\Extension::load([
	'admin_interface',
	'sidepanel'
]);

if ($component->isIframeMode())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:catalog.store.admin_list',
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
		'bitrix:catalog.store.admin_list',
		'',
		$componentParams
	);
}
