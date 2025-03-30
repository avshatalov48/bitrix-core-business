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
		'SORT' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_SORT'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => [
				'NAME' => GetMessage('SEARCH_NAME'),
				'CNT' => GetMessage('SEARCH_CNT'),
			],
			'DEFAULT' => 'NAME',
		],
		'PAGE_ELEMENTS' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_PAGE_ELEMENTS'),
			'TYPE' => 'STRING',
			'DEFAULT' => '150',
		],
		'PERIOD' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_PERIOD'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
		'URL_SEARCH' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_URL_SEARCH'),
			'TYPE' => 'STRING',
			'DEFAULT' => '/search/index.php',
		],
		'TAGS_INHERIT' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('SEARCH_TAGS_INHERIT'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'CHECK_DATES' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('SEARCH_CHECK_DATES'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'FILTER_NAME' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BSTC_FILTER_NAME'),
			'TYPE' => 'STRING',
		],
		'CACHE_TIME'  => ['DEFAULT' => 3600],
	],
];

CSearchParameters::AddFilterParams($arComponentParameters, $arCurrentValues, 'arrFILTER', 'DATA_SOURCE');
