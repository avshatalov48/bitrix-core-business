<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var \CBitrixComponent $component */
/** @var \CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.site_master',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'SITE_ID' => $arResult['VARS']['site_edit'],
		'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
		'PAGE_URL_SITE_MASTER' => $arParams['PAGE_URL_SITE_MASTER']
	),
	$component
);?>