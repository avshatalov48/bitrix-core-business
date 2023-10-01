<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogAgentContractControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

global $APPLICATION;

$componentParams = [
	'PATH_TO' => $arResult['PATH_TO'],
];

if ($component->isIframeMode())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:catalog.agent.contract.list',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $componentParams,
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'N',
			'USE_PADDING' => false,
			'USE_UI_TOOLBAR' => 'Y',
			'USE_BACKGROUND_CONTENT' => true,
		]
	);
}
else
{
	$APPLICATION->IncludeComponent(
		'bitrix:catalog.agent.contract.list',
		'',
		$componentParams
	);
}
