<?php

/**
 * @var array $arResult
 * @var CMain $APPLICATION
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

$APPLICATION->IncludeComponent(
	'bitrix:ui.info.error',
	'',
	[
		'TITLE' => $arResult['ERROR']['TITLE'] ?? null,
		'DESCRIPTION' => $arResult['ERROR']['DESCRIPTION'] ?? null,
	]
);
