<?php

use bitrix\Main\HttpContext;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var CBitrixComponent $component */
/** @var CMain $APPLICATION */

$request = HttpContext::getCurrent()->getRequest();
$designBlockId = $request->get('design_block');

$arParams['PAGE_URL_SITE_SHOW'] = str_replace(
	'#site_show#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_SITE_SHOW']
);

$arParams['PAGE_URL_LANDING_EDIT'] = str_replace(
	array('#site_show#', '#landing_edit#'),
	array($arResult['VARS']['site_show'], $arResult['VARS']['landing_edit']),
	$arParams['PAGE_URL_LANDING_EDIT']
);

$arParams['PAGE_URL_LANDING_DESIGN'] = str_replace(
	array('#site_show#', '#landing_edit#'),
	array($arResult['VARS']['site_show'], $arResult['VARS']['landing_edit']),
	$arParams['PAGE_URL_LANDING_DESIGN']
);

$arParams['PAGE_URL_SITE_EDIT'] = str_replace(
	array('#site_edit#'),
	array($arResult['VARS']['site_show']),
	$arParams['PAGE_URL_SITE_EDIT']
);

$params = array(
	'sef_url' => array()
);

foreach ($arParams['SEF_URL_TEMPLATES'] as $code => $url)
{
	if ($url)
	{
		$params['sef_url'][$code] = $arParams['SEF_FOLDER'] . $url;
	}
}
?>

<?if ($designBlockId):?>

<?$APPLICATION->includeComponent(
	'bitrix:landing.landing_designblock',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'SITE_ID' => $arResult['VARS']['site_show'],
		'LANDING_ID' => $arResult['VARS']['landing_edit'],
		'BLOCK_ID' => $designBlockId
	),
	$component
);?>

<?php else:?>

<?$APPLICATION->includeComponent(
	'bitrix:landing.landing_view',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'SITE_ID' => $arResult['VARS']['site_show'],
		'LANDING_ID' => $arResult['VARS']['landing_edit'],
		'FULL_PUBLICATION' => $arParams['EDIT_FULL_PUBLICATION'],
		'DONT_LEAVE_AFTER_PUBLICATION' => $arParams['EDIT_DONT_LEAVE_FRAME'],
		'PANEL_LIGHT_MODE' => $arParams['EDIT_PANEL_LIGHT_MODE'],
		'PAGE_URL_URL_SITES' => $arParams['PAGE_URL_SITES'],
		'PAGE_URL_LANDINGS' => $arParams['PAGE_URL_SITE_SHOW'],
		'PAGE_URL_LANDING_EDIT' => $arParams['PAGE_URL_LANDING_EDIT'],
		'PAGE_URL_LANDING_DESIGN' => $arParams['PAGE_URL_LANDING_DESIGN'],
		'PAGE_URL_SITE_EDIT' => $arParams['PAGE_URL_SITE_EDIT'],
		'DRAFT_MODE' => $arParams['DRAFT_MODE'],
		'PARAMS' => $params,
		'SEF' => $params['sef_url'],
		'AGREEMENT' => $arResult['AGREEMENT']
	),
	$component
);?>

<?php endif; ?>
