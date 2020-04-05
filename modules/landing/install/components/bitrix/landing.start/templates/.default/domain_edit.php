<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.domain_edit',
	'.default',
	array(
		'DOMAIN_ID' => $arResult['VARS']['domain_edit'],
		'PAGE_URL_DOMAINS' => $arParams['PAGE_URL_DOMAINS']
	),
	$component
);?>