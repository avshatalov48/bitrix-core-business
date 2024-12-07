<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Currency\Helpers\Editor;
use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\UI\Extension;

Extension::load([
	'core_uf_money',
]);

$value = '';

/** @var $arResult [] */
if (isset($arResult['additionalParameters']['bVarsFromForm']) && $arResult['additionalParameters']['bVarsFromForm'])
{
	$value = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'];
}
elseif (isset($arResult['userField']) && is_array($arResult['userField']))
{
	$value = $arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
}
else
{
	$defaultValue = '';
	$defaultCurrency = '';
	$currencyList = Editor::getListCurrency();
	foreach ($currencyList as $currencyInfo)
	{
		if ($currencyInfo['BASE'] === 'Y')
		{
			$defaultCurrency = $currencyInfo['CURRENCY'];
		}
	}

	$value = MoneyType::formatToDB($defaultValue, $defaultCurrency);
}

$arResult['value'] = $value;
