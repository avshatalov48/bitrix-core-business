<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\UserField\Types\SectionType;
use Bitrix\Main\Text\HtmlFilter;

$userField = $arResult['userField'];

SectionType::getEnumList($userField);

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
	HtmlFilter::encode(implode(', ', $result)) : SectionType::getEmptyCaption($userField)
);