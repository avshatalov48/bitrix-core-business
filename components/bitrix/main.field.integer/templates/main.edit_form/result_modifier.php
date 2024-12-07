<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arResult
 */

$arResult['additionalParameters']['VALIGN'] = (
$arResult['userField']['MULTIPLE'] === 'Y' ? 'top' : 'middle'
);

foreach($arResult['value'] as $key => $value)
{
	$entityValueId = (int)($arResult['userField']['ENTITY_VALUE_ID'] ?? 0);

	if(
		$entityValueId < 1
		&& (string)$arResult['userField']['SETTINGS']['DEFAULT_VALUE'] != ''
	)
	{
		$value = HtmlFilter::encode(
			$arResult['userField']['SETTINGS']['DEFAULT_VALUE']
		);
	}

	$attrList = [
		'type' => 'text',
		'size' => $arResult['userField']['SETTINGS']['SIZE'] ?? 0,
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





