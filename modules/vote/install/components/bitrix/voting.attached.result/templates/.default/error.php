<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

$APPLICATION->IncludeComponent('bitrix:ui.info.error', '', [
	'TITLE' => $arResult['ERROR_TITLE'] ?? null,
	'DESCRIPTION' => $arResult['ERROR_DESCRIPTION'] ?? null,
]);