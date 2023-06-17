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

use Bitrix\Main\Localization\Loc;

$APPLICATION->IncludeComponent('bitrix:ui.info.error', '', [
	'TITLE' => Loc::getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_ERROR_NEW_PROPERTY_TITLE'),
	'DESCRIPTION' => '',
]);
