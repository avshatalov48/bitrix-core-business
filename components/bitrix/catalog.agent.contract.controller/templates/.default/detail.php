<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CatalogAgentContractControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

$agentContractId = (int)($arResult['VARIABLES']['AGENT_CONTRACT_ID'] ?? 0);

global $APPLICATION;

Main\UI\Extension::load('ui.notification');

$iblockId = 0;
if (Main\Loader::includeModule('crm'))
{
	$iblockId = (int)\Bitrix\Crm\Product\Catalog::getDefaultId();
}
elseif (Main\Loader::includeModule('catalog'))
{
	$catalogIblock = Bitrix\Catalog\CatalogIblockTable::getRow([
		'select' => ['IBLOCK_ID'],
		'filter' => ['=PRODUCT_IBLOCK_ID' => 0],
		'order' => ['IBLOCK_ID' => 'DESC'],
		'cache' => ['ttl' => 86400],
	]);
	$iblockId = (int)($catalogIblock['IBLOCK_ID'] ?? 0);
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.agent.contract.detail',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'ID' => $agentContractId,
			'IBLOCK_ID' => $iblockId,
			'PATH_TO' => $arResult['PATH_TO'],
		],
		'USE_BACKGROUND_CONTENT' => false,
		'USE_UI_TOOLBAR' => 'Y',
		'USE_TOP_MENU' => false,
		'USE_PADDING' => false,
		'PAGE_MODE' => false,
		'RELOAD_GRID_AFTER_SAVE' => true,
		'CLOSE_AFTER_SAVE' => true,
		'PAGE_MODE_OFF_BACK_URL' => $arParams['BACK_URL'],
	]
);
