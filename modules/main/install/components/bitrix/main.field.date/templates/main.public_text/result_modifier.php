<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

$value = implode(', ', array_map(static function($value){
	if(
		$value instanceof \Bitrix\Main\Type\Date
		|| is_scalar($value)
		|| $value === null
	)
	{
		return (string)$value;
	}

	return  '';
}, $arResult['value']));

$arResult['value'] = $value;
