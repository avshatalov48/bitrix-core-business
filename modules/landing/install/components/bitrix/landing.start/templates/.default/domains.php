<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:landing.domains",
	".default",
	array(
		'PAGE_URL_DOMAIN_EDIT' => $arParams['PAGE_URL_DOMAIN_EDIT']
	),
	$component
);?>