<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$request = \bitrix\Main\HttpContext::getCurrent()->getRequest();

$arParams['PAGE_URL_SITE_EDIT'] = str_replace(
	'#site_edit#',
	0,
	$arParams['PAGE_URL_SITE_EDIT']
);
?>

<?if ($arResult['VARS']['site_edit']):?>

	<?
	$template = $request->get('tpl');
	if ($arParams['TYPE'] != 'STORE')
	{
		$template = '';
	}
	?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.site_edit',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'SITE_ID' => $arResult['VARS']['site_edit'],
			'PAGE_URL_SITES' => $arParams['PAGE_URL_SITES'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
			'TEMPLATE' => $template
		),
		$component
	);?>

<?elseif ($template = $request->get('tpl')):?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.demo_preview',
		'.default',
		array(
			'CODE' => $template,
			'TYPE' => $arParams['TYPE'],
			'PAGE_URL_BACK' => $arParams['PAGE_URL_SITE_EDIT']
		),
		$component
	);?>

<?else:?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.demo',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'PAGE_URL_SITES' => $arParams['PAGE_URL_SITES'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW']
		),
		$component
	);?>

<?endif;?>
