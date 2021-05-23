<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$value = implode(', ', array_map(function($v)
{
	return (is_null($v) || is_scalar($v) ? (string)$v : '');
}, $arResult['value']));

$arResult['value'] = $value;