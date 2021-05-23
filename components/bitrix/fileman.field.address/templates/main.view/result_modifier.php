<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Page\Asset;
use Bitrix\Fileman\UserField\Types\AddressType;

CJSCore::init(['uf', 'userfield_address', 'google_map']);

foreach($arResult['value'] as $key => $value)
{
	if($value)
	{
		list($text, $coords) = AddressType::parseValue($value);
		$arResult['value'][$key] = [
			'text' => $text,
			'coords' => $coords
		];
	}
}
