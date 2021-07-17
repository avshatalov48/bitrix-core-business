<?php

use bitrix\Main\HttpContext;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CBitrixComponent $component */

$request = HttpContext::getCurrent()->getRequest();

$arParams['PAGE_URL_LANDING_DESIGN'] = str_replace(
	'#site_edit#',
	0,
	$arParams['PAGE_URL_LANDING_DESIGN']
);

if ($arResult['VARS']['landing_edit'] > 0)
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.landing_edit',
		'design',
		[
			'SITE_ID' => $arResult['VARS']['site_show'],
			'LANDING_ID' => $arResult['VARS']['landing_edit'],
			'TYPE' => $arParams['TYPE'],
		],
		$component
	);
}


