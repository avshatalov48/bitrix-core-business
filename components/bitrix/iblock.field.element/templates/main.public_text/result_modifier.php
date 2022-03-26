<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */

use Bitrix\Iblock\UserField\Types\ElementType;
use Bitrix\Main\Text\HtmlFilter;

$userField = $arResult['userField'];

ElementType::getEnumList($userField);

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
	HtmlFilter::encode(implode(', ', $result)) : ElementType::getEmptyCaption($userField)
);
