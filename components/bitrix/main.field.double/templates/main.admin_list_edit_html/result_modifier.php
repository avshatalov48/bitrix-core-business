<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$attrList = [
	'size' => (int)$arResult['userField']['SETTINGS']['SIZE'],
	'value' => round(
		(double)$arResult['additionalParameters']['VALUE'],
		$arResult['userField']['SETTINGS']['PRECISION']
	),
	'name' => $arResult['additionalParameters']['NAME'],
	'type' => 'text'
];

$arResult['value'] = [
	'attrList' => $attrList,
	'value' => round(
		(double)$arResult['additionalParameters']['VALUE'],
		$arResult['userField']['SETTINGS']['PRECISION']
	)
];