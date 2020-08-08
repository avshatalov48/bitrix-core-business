<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\BooleanType;

$label = BooleanType::getLabels($arResult['userField']);

$text = '';
$first = true;

foreach ($arResult['value'] as $value)
{
	if (!$first)
	{
		$text .= ', ';
	}
	$first = false;

	$text .= ($value ? $label[1] : $label[0]);
}

$arResult['value'] = $text;