<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

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

\Bitrix\Landing\Update\Stepper::show();
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.landings',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'SITE_ID' => $arResult['VARS']['site_show'],
		'ACTION_FOLDER' => $arParams['ACTION_FOLDER'],
		'PAGE_URL_LANDING_EDIT' => $arParams['PAGE_URL_LANDING_EDIT'],
		'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW']
	),
	$component
);?>