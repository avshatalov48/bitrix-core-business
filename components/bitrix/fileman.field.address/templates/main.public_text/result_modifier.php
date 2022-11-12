<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Fileman\UserField\Types\AddressType;

$text = '';
$first = true;
$hideCoordinates = !empty($arParams['additionalParameters']['hideCoordinates']);

foreach($arResult['value'] as $value)
{
	if($value == '')
	{
		continue;
	}

	list($descr, $coords) = AddressType::parseValue($value);

	if($descr == '')
	{
		continue;
	}

	if(!$first)
	{
		$text .= ', ';
	}

	$first = false;

	$text .= (
	!$hideCoordinates && $coords != ''
		? sprintf('%s (%s)', $descr, join(', ', $coords))
		: $descr
	);
}

$arResult['value'] = $text;