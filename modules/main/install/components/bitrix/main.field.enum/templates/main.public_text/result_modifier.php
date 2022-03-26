<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\EnumType;

$userField = $arResult['userField'];

EnumType::getEnumList($userField);

$value = $arResult['value'];
$result = [];

foreach($value as $res)
{
	if(isset($userField['USER_TYPE']['FIELDS'][$res]))
	{
		$result[] = $userField['USER_TYPE']['FIELDS'][$res];
	}
}

$arResult['value'] = (count($result) ?
	implode(', ', $result) : EnumType::getEmptyCaption($userField)
);
