<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\UI\Toolbar\Facade\Toolbar;

Toolbar::deleteFavoriteStar();
?>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:rest.marketplace.booklet',
	'',
	array(
		"CODE" => $arResult["VARIABLES"]["CODE"]
	),
	$component
);
