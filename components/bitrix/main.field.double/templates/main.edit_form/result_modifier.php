<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arResult['additionalParameters']['VALIGN'] = (
	$arResult['userField']['MULTIPLE'] === 'Y' ? 'top' : 'middle'
);

$entityValueId = (int)($arResult['userField']['ENTITY_VALUE_ID'] ?? 0);

foreach($arResult['value'] as $key => $value)
{
	if(
		$entityValueId < 1
		&& (string)$arResult['userField']['SETTINGS']['DEFAULT_VALUE'] !== ''
	)
	{
		$value = htmlspecialcharsbx(
			$arResult['userField']['SETTINGS']['DEFAULT_VALUE'] ?? null
		);
	}

	if (!empty($value))
	{
		$value = round(
			(double)$value,
			(int)($arResult['userField']['SETTINGS']['PRECISION'] ?? 0)
		);
	}

	$attrList = [
		'type' => 'text',
		'size' => $arResult['userField']['SETTINGS']['SIZE'] ?? '',
		'value' => $value,
		'name' => str_replace('[]', '[' . $key . ']', $arResult['fieldName'])
	];

	if($arResult['userField']['EDIT_IN_LIST'] !== 'Y')
	{
		$attrList['disabled'] = 'disabled';
	}

	$arResult['value'][$key] = [
		'attrList' => $attrList,
		'value' => $value,
	];
}



