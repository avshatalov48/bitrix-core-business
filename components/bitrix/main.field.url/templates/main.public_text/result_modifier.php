<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

$result = [];

foreach($arResult['value'] as $res)
{
	if(is_string($res) && $res !== '')
	{
		$result[] = $res;
	}
}
$arResult['value'] = HtmlFilter::encode(implode(', ', $result));