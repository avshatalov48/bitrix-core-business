<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$attrList = [];

if($arResult['userField']['SETTINGS']['ROWS'] < 2)
{
	$tag = 'input';
	$attrList = [
		'size' => (int)$arResult['userField']['SETTINGS']['SIZE'],
	];
}
else
{
	$tag = 'textarea';
	$attrList = [
		'cols' => (int)$arResult['userField']['SETTINGS']['SIZE'],
		'rows' => (int)$arResult['userField']['SETTINGS']['ROWS']
	];
}

$attrList['name'] = $arResult['additionalParameters']['NAME'];

if($arResult['userField']['SETTINGS']['MAX_LENGTH'] > 0)
{
	$attrList['maxlength'] = (int)$arResult['userField']['SETTINGS']['MAX_LENGTH'];
}

$arResult['fieldValues'] = [
	'attrList' => $attrList,
	'tag' => $tag
];