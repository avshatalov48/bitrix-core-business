<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\DateType;

if (isset($arResult['additionalParameters']['bVarsFromForm']) && $arResult['additionalParameters']['bVarsFromForm'])
{
	$type = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']['TYPE'] ?? '';
	$value = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']['VALUE'] ?? '';
}
elseif (
	isset($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
	&& is_array($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
)
{
	$type = $arResult['userField']['SETTINGS']['DEFAULT_VALUE']['TYPE'] ?? '';
	$value = CDatabase::FormatDate(
		$arResult['userField']['SETTINGS']['DEFAULT_VALUE']['VALUE'] ?? '',
		'YYYY-MM-DD',
		CLang::GetDateFormat(DateType::FORMAT_TYPE_SHORT)
	);
}
else
{
	$type = DateType::TYPE_NONE;
	$value = '';
}

$arResult['default_value_type'] = $type;
$arResult['default_value'] = $value;