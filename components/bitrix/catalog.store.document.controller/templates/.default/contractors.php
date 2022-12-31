<?php

use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogStoreDocumentControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

global $APPLICATION;

if ($arResult['IS_CRM_CONTRACTORS_PROVIDER'])
{
	$APPLICATION->IncludeComponent(
		'bitrix:crm.store.contractor.list',
		'',
		[
			'PATH_TO' => $arResult['PATH_TO'],
		]
	);
}
else
{
	echo $arResult['CONTRACTORS_MIGRATION_PROGRESS'];

	$componentParams = [
		'PATH_TO' => $arResult['PATH_TO'],
	];

	Extension::load([
		'admin_interface',
		'sidepanel',
	]);

	if ($component->isIframeMode())
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'POPUP_COMPONENT_NAME' => 'bitrix:catalog.contractor.list',
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
			'bitrix:catalog.contractor.list',
			'',
			$componentParams
		);
	}
}
