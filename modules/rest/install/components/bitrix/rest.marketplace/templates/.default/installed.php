<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

$APPLICATION->IncludeComponent(
	'bitrix:rest.marketplace.installed',
	'',
	[
		'DETAIL_URL_TPL' => $arParams['DETAIL_URL_TPL'],
		'CATEGORY_URL_TPL' => $arParams['CATEGORY_URL_TPL'],
	],
	$component
);
