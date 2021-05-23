<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
?><?$APPLICATION->IncludeComponent(
	'bitrix:rest.marketplace.category',
	'',
	[
		"CATEGORY" => $arResult["VARIABLES"]["category"],
		"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
		"CATEGORY_URL_TPL" => $arParams["CATEGORY_URL_TPL"],
		"SHOW_FILTER" => "Y"
	],
	$component);
