<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$request = \bitrix\Main\HttpContext::getCurrent()->getRequest();

$arParams['PAGE_URL_SITE_SHOW'] = str_replace(
	'#site_show#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_SITE_SHOW']
);

$arParams['PAGE_URL_LANDING_VIEW'] = str_replace(
	'#site_show#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_LANDING_VIEW']
);

$arParams['PAGE_URL_SITE_EDIT'] = str_replace(
	'#site_edit#',
	$arResult['VARS']['site_show'],
	$arParams['PAGE_URL_SITE_EDIT']
);
?>

<?if ($arResult['VARS']['landing_edit']):?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.landing_edit',
		'.default',
		array(
			'SITE_ID' => $arResult['VARS']['site_show'],
			'LANDING_ID' => $arResult['VARS']['landing_edit'],
			'PAGE_URL_LANDINGS' => $arParams['PAGE_URL_SITE_SHOW'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
			'PAGE_URL_SITE_EDIT' => $arParams['PAGE_URL_SITE_EDIT']
		),
		$component
	);?>

<?elseif ($template = $request->get('tpl')):?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.demo_preview',
		'.default',
		array(
			'CODE' => $template,
			'TYPE' => 'PAGE',//$arParams['TYPE'],
			'PAGE_URL_BACK' => $arParams['PAGE_URL_SITE_SHOW'],
			'SITE_ID' => $arResult['VARS']['site_show'],
		),
		$component
	);?>

<?else:?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.demo',
		'.default',
		array(
			'TYPE' => 'PAGE',//$arParams['TYPE'],
			'ACTION_FOLDER' => $arParams['ACTION_FOLDER'],
			'SITE_ID' => $arResult['VARS']['site_show'],
			'PAGE_URL_SITES' => $arParams['PAGE_URL_SITES'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW']
		),
		$component
	);?>

<?endif;?>
