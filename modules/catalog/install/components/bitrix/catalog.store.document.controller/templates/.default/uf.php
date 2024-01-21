<?php

use Bitrix\Catalog\Document\StoreDocumentTableManager;
use Bitrix\Main\Context;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

$entityId = Context::getCurrent()->getRequest()->get('entityId');
$entityId = in_array($entityId, StoreDocumentTableManager::getUfEntityIds(), true) ? $entityId : '';

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.store.field.config.list',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'HELPDESK_ARTICLE_ID' => 17109518,
			'ENTITY_ID' => $entityId,
		],
		'USE_PADDING' => false,
		'USE_UI_TOOLBAR' => 'Y',
	]
);
