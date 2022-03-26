<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/** @var $arResult array */

$value = implode(', ', array_map(static function($v)
{
	return (is_null($v) || is_scalar($v) ? (string)$v : '');
}, $arResult['value']));

$arResult['value'] = HtmlFilter::encode($value);
