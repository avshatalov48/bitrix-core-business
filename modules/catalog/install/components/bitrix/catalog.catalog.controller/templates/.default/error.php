<?php

use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CBitrixComponentTemplate $this  */
/** @var \CrmCatalogControllerComponent $component */

$this->setViewTarget('above_pagetitle');
$component->showCatalogControlPanel();
$this->endViewTarget();

Loc::loadMessages(__FILE__);

$componentParams = [
	'TITLE' => Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ACCESS_DENIED_ERROR_TITLE'),
];

if ($component->isIframeMode())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:ui.info.error',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'POPUP_COMPONENT_PARAMS' => $componentParams,
			'USE_PADDING' => true,
			'USE_UI_TOOLBAR' => 'Y',
		]
	);
}
else
{
	$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('CATALOG_CATALOG_TITLE'));
	\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
	$APPLICATION->IncludeComponent(
		"bitrix:ui.info.error",
		"",
		$componentParams
	);
}

