<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Rest\PlacementTable;

$appId = 0;
$placementId = 0;
$placementCode = '';

if ($arResult["VARIABLES"]["PLACEMENT_ID"] > 0)
{
	$res = PlacementTable::getById((int) $arResult["VARIABLES"]["PLACEMENT_ID"]);
	if ($placement = $res->fetch())
	{
		$placementCode = $placement['PLACEMENT'];
		$appId = $placement['APP_ID'];
		$placementId = $placement['ID'];
	}
}

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$params = $request->getQuery('params') ?: [];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	array(
		"POPUP_COMPONENT_NAME" => "bitrix:app.layout",
		"POPUP_COMPONENT_TEMPLATE_NAME" => ".default",
		"POPUP_COMPONENT_PARAMS" => array(
			'ID' => $appId,
			'PLACEMENT' => $placementCode,
			'PLACEMENT_ID' => $placementId,
			'SHOW_LOADER' => 'Y',
			'SET_TITLE' => 'Y',
			'IS_SLIDER' => 'Y',
			'PARAM' => [],
			'PLACEMENT_OPTIONS' => $params,
		),
		"USE_PADDING" => false,
		"PAGE_MODE" => false,
		"USE_UI_TOOLBAR" => "N",
		"PLAIN_VIEW" => (\CRestUtil::isSlider() ? "Y" : "N")
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);