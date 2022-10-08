<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\UI\Toolbar\Facade\Toolbar;

Toolbar::deleteFavoriteStar();

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

$APPLICATION->IncludeComponent(
	'bitrix:rest.marketplace.booklet',
	'',
	array(
		"CODE" => $arResult["VARIABLES"]["CODE"]
	),
	$component
);
