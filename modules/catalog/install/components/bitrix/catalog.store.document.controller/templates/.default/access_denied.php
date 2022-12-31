<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var \CMain $APPLICATION
 * @var \CatalogStoreDocumentControllerComponent $component
 */

Loc::loadMessages(__FILE__);

$componentParams = [
	'TITLE' => Loc::getMessage('CATALOG_STORE_DOCUMENT_CONTROLLER_ACCESS_DENIED_ERROR_TITLE'),
	'HELPER_CODE' => 15955386,
	'LESSON_ID' => 25010,
	'COURSE_ID' => 48,
];

$APPLICATION->SetTitle(Loc::getMessage('CATALOG_STORE_DOCUMENT_CONTROLLER_ACCESS_DENIED_TITLE'));
if ($component->isIframeMode())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:ui.info.error',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $componentParams,
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'USE_PADDING' => false,
			'USE_UI_TOOLBAR' => 'N',
		]
	);
}
else
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.info.error',
		'',
		$componentParams
	);
}
