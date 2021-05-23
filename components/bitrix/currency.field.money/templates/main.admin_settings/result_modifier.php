<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Currency\Helpers\Editor;
use Bitrix\Currency\UserField\Types\MoneyType;

CJSCore::init(['core_uf_money']);

$currencyList = Editor::getListCurrency();

$result = '';
if($arResult['additionalParameters']['bVarsFromForm'])
{
	$value = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'];
}
elseif(is_array($arResult['userField']))
{
	$value = $arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
}
else
{
	$defaultValue = '';
	$defaultCurrency = '';
	foreach($currencyList as $currencyInfo)
	{
		if($currencyInfo['BASE'] === 'Y')
		{
			$defaultCurrency = $currencyInfo['CURRENCY'];
		}
	}

	$value = MoneyType::formatToDB($defaultValue, $defaultCurrency);
}

$arResult['value'] = $value;