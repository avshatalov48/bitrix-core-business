<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arResult
 */

use Bitrix\Main\UserField\Types\IntegerType;

$arResult['additionalParameters']['VALIGN'] = (
$arResult['userField']['MULTIPLE'] === 'Y' ? 'top' : 'middle'
);

foreach($arResult['value'] as $key => $value)
{
	if(
		$arResult['userField']['ENTITY_VALUE_ID'] < 1
		&&
		mb_strlen($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
	)
	{
		$value = HtmlFilter::encode(
			$arResult['additionalParameters']['SETTINGS']['DEFAULT_VALUE']
		);
	}

	$attrList = [
		'type' => 'text',
		'size' => $arResult['additionalParameters']['SETTINGS']['SIZE'],
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





