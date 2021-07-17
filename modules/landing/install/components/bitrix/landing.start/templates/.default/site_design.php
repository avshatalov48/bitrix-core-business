<?php

use bitrix\Main\HttpContext;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CBitrixComponent $component */

$request = HttpContext::getCurrent()->getRequest();

$arParams['PAGE_URL_SITE_DESIGN'] = str_replace(
	'#site_edit#',
	0,
	$arParams['PAGE_URL_SITE_DESIGN']
);

$template = $request->get('tpl');
$notRedirectToEdit = ($request->get('no_redirect') === 'Y') ? 'Y' : 'N';
if ($arParams['TYPE'] !== 'STORE')
{
	$template = '';
}
?>

<?if ($arResult['VARS']['site_edit']):?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.site_edit',
		'design',
		[
			'TYPE' => $arParams['TYPE'],
			'SITE_ID' => $arResult['VARS']['site_edit'],
		],
		$component
	);?>

<?php endif; ?>
