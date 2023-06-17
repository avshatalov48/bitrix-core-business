<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

$APPLICATION->IncludeComponent(
	'bitrix:ui.info.error',
	'',
	[
		'TITLE' => $arResult['ERROR'],
	]
);
