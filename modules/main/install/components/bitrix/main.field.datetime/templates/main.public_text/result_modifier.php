<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UserField\Types\DateTimeType;

/** @var array $arResult */

$value = implode(', ', array_map(static function($value) use($arResult)
{
	if ($value === null)
	{
		return '';
	}

	return \CDatabase::formatDate(
		$value,
		\CLang::getDateFormat(DateTimeType::FORMAT_TYPE_FULL),
		DateTimeType::getFormat($value, $arResult['userField'])
	);
}, $arResult['value']));

$arResult['value'] = $value;
