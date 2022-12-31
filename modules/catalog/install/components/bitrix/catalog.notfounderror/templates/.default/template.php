<?php
/**
 * @global \CMain $APPLICATION
 * @var $component \CatalogNotFoundError
 * @var $this \CBitrixComponentTemplate
 * @var array $arResult
 * @var array $arParams
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.info.error',
	'',
	[
		'TITLE' => $arResult['TITLE'],
		'DESCRIPTION' => '',
		'IS_HTML' => 'Y',
	],
	$component,
	[
		'HIDE_ICONS' => 'Y',
	]
);
