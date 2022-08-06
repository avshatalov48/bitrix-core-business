<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Landing\Update\Stepper;

/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CBitrixComponent $component */

$arParams['PAGE_URL_LANDING_SETTINGS'] = str_replace(
	'#site_show#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_LANDING_SETTINGS']
);
$arParams['PAGE_URL_LANDING_EDIT'] = str_replace(
	'#site_show#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_LANDING_EDIT']
);
$arParams['PAGE_URL_LANDING_VIEW'] = str_replace(
	'#site_show#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_LANDING_VIEW']
);
$arParams['PAGE_URL_LANDING_DESIGN'] = str_replace(
	'#site_show#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_LANDING_DESIGN']
);

$sef = [];

foreach ($arParams['SEF_URL_TEMPLATES'] as $code => $url)
{
	$sef[$code] = $arParams['SEF_FOLDER'] . $url;
}

Stepper::show();
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.landings',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'SITE_ID' => $arResult['VARS']['site_show'],
		'ACTION_FOLDER' => $arParams['ACTION_FOLDER'],
		'PAGE_URL_LANDING_SETTINGS' => $arParams['PAGE_URL_LANDING_SETTINGS'],
		'PAGE_URL_LANDING_EDIT' => $arParams['PAGE_URL_LANDING_EDIT'],
		'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
		'PAGE_URL_LANDING_DESIGN' => $arParams['PAGE_URL_LANDING_DESIGN'],
		'PAGE_URL_FOLDER_EDIT' => $arParams['PAGE_URL_FOLDER_EDIT'],
		'TILE_MODE' => $arParams['TILE_LANDING_MODE'],
		'DRAFT_MODE' => $arParams['DRAFT_MODE'],
		'SEF' => $sef,
		'AGREEMENT' => $arResult['AGREEMENT']
	),
	$component
);?>