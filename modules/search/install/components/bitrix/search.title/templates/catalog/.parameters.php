<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arPrice = [];
if (CModule::IncludeModule('catalog'))
{
	$rsPrice = CCatalogGroup::GetList($v1 = 'sort', $v2 = 'asc');
	while ($arr = $rsPrice->Fetch())
	{
		$arPrice[$arr['NAME']] = '[' . $arr['NAME'] . '] ' . $arr['NAME_LANG'];
	}
}

$arTemplateParameters = [
	'SHOW_INPUT' => [
		'NAME' => GetMessage('TP_BST_SHOW_INPUT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
		'REFRESH' => 'Y',
	],
	'INPUT_ID' => [
		'NAME' => GetMessage('TP_BST_INPUT_ID'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'title-search-input',
	],
	'CONTAINER_ID' => [
		'NAME' => GetMessage('TP_BST_CONTAINER_ID'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'title-search',
	],
	'PRICE_CODE' => [
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('TP_BST_PRICE_CODE'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'VALUES' => $arPrice,
	],
	'PRICE_VAT_INCLUDE' => [
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('TP_BST_PRICE_VAT_INCLUDE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	],
	'PREVIEW_TRUNCATE_LEN' => [
		'PARENT' => 'ADDITIONAL_SETTINGS',
		'NAME' => GetMessage('TP_BST_PREVIEW_TRUNCATE_LEN'),
		'TYPE' => 'STRING',
		'DEFAULT' => '',
	],
	'SHOW_PREVIEW' => [
		'NAME' => GetMessage('TP_BST_SHOW_PREVIEW'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
		'REFRESH' => 'Y',
	],
];

if (isset($arCurrentValues['SHOW_PREVIEW']) && 'Y' == $arCurrentValues['SHOW_PREVIEW'])
{
	$arTemplateParameters['PREVIEW_WIDTH'] = [
		'NAME' => GetMessage('TP_BST_PREVIEW_WIDTH'),
		'TYPE' => 'STRING',
		'DEFAULT' => 75,
	];
	$arTemplateParameters['PREVIEW_HEIGHT'] = [
		'NAME' => GetMessage('TP_BST_PREVIEW_HEIGHT'),
		'TYPE' => 'STRING',
		'DEFAULT' => 75,
	];
}

if (CModule::IncludeModule('catalog') && CModule::IncludeModule('currency'))
{
	$arTemplateParameters['CONVERT_CURRENCY'] = [
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('TP_BST_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	];

	if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
	{
		$arCurrencyList = [];
		$rsCurrencies = CCurrency::GetList('SORT', 'ASC');
		while ($arCurrency = $rsCurrencies->Fetch())
		{
			$arCurrencyList[$arCurrency['CURRENCY']] = $arCurrency['CURRENCY'];
		}
		$arTemplateParameters['CURRENCY_ID'] = [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('TP_BST_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arCurrencyList,
			'DEFAULT' => CCurrency::GetBaseCurrency(),
			'ADDITIONAL_VALUES' => 'Y',
		];
	}
}
