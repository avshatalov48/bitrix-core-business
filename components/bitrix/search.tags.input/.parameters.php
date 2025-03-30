<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule('search'))
{
	return;
}

$arComponentParameters = [
	'PARAMETERS' => [
		'NAME' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'TAG',
		],
		'VALUE' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_VALUE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
		'SITE_ID' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_SITE_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => SITE_ID,
		]
	],
];

CSearchParameters::AddFilterParams($arComponentParameters, $arCurrentValues, 'arrFILTER', 'DATA_SOURCE', 'N');
