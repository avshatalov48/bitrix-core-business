<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

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
	$params['sef_url'][$code] = $arParams['SEF_FOLDER'] . $url;
}
?>
<?$APPLICATION->IncludeComponent(
	'bitrix:landing.landing_view',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'SITE_ID' => $arResult['VARS']['site_show'],
		'LANDING_ID' => $arResult['VARS']['landing_edit'],
		'PAGE_URL_URL_SITES' => $arParams['PAGE_URL_SITES'],
		'PAGE_URL_LANDINGS' => $arParams['PAGE_URL_SITE_SHOW'],
		'PAGE_URL_LANDING_EDIT' => $arParams['PAGE_URL_LANDING_EDIT'],
		'PAGE_URL_SITE_EDIT' => $arParams['PAGE_URL_SITE_EDIT'],
		'PARAMS' => $params,
		'AGREEMENT' => $arResult['AGREEMENT']
	),
	$component
);?>