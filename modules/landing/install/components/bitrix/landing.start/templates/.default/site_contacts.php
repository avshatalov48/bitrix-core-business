<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */
/** @var \CBitrixComponent $component */
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.site_contacts',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'SITE_ID' => $arResult['VARS']['site_edit']
	),
	$component
);?>