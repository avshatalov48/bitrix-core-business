<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */
/** @var \CBitrixComponent $component */

$request = \Bitrix\Main\HttpContext::getCurrent()->getRequest();

$arParams['PAGE_URL_SITE_EDIT'] = str_replace(
	'#site_edit#',
	0,
	$arParams['PAGE_URL_SITE_EDIT']
);

$template = $request->get('tpl');
$notRedirectToEdit = ($request->get('no_redirect') == 'Y') ? 'Y' : 'N';
if ($arParams['TYPE'] != 'STORE')
{
	$template = '';
}
?>

<?if ($arResult['VARS']['site_edit']):?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.site_edit',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'SITE_ID' => $arResult['VARS']['site_edit'],
			'PAGE_URL_SITES' => $arParams['PAGE_URL_SITES'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
			'PAGE_URL_SITE_DOMAIN' => $arParams['PAGE_URL_SITE_DOMAIN'],
			'PAGE_URL_SITE_COOKIES' => $arParams['PAGE_URL_SITE_COOKIES'],
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
			'PAGE_URL_BACK' => $arParams['PAGE_URL_SITE_EDIT'],
			'DISABLE_REDIRECT' => $notRedirectToEdit,
			'DONT_LEAVE_FRAME' => $arParams['EDIT_DONT_LEAVE_FRAME']
		),
		$component
	);?>

<?elseif ($request->get('super') == 'Y'):?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.site_master',
		'teaser',
		array(
			'TYPE' => $arParams['TYPE'],
			'GET_DATA' => 'N',
			'PAGE_URL_SITE_MASTER' => $arParams['PAGE_URL_SITE_MASTER']
		),
		$component
	);?>

<?else:?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.demo',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'DISABLE_REDIRECT' => $notRedirectToEdit,
			'PAGE_URL_SITES' => $arParams['PAGE_URL_SITES'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
			'DONT_LEAVE_FRAME' => $arParams['EDIT_DONT_LEAVE_FRAME']
		),
		$component
	);?>

<?endif;?>
