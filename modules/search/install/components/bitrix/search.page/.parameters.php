<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule('search'))
{
	return;
}

$arComponentParameters = [
	'GROUPS' => [
		'PAGER_SETTINGS' => [
			'NAME' => GetMessage('SEARCH_PAGER_SETTINGS'),
		],
	],
	'PARAMETERS' => [
		'AJAX_MODE' => [],
		'RESTART' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('SEARCH_RESTART'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'NO_WORD_LOGIC' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BSP_NO_WORD_LOGIC'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'USE_LANGUAGE_GUESS' => [
			'NAME' => GetMessage('CP_BSP_USE_LANGUAGE_GUESS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'CHECK_DATES' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('SEARCH_CHECK_DATES'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'USE_TITLE_RANK' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('SEARCH_USE_TITLE_RANK'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'DEFAULT_SORT' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_SP_DEFAULT_SORT'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'DEFAULT' => 'rank',
			'VALUES' => [
				'rank' => GetMessage('CP_SP_DEFAULT_SORT_RANK'),
				'date' => GetMessage('CP_SP_DEFAULT_SORT_DATE'),
			],
		],
		'FILTER_NAME' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BSP_FILTER_NAME'),
			'TYPE' => 'STRING',
		],
		'SHOW_WHERE' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SEARCH_SHOW_DROPDOWN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y',
		],
		'arrWHERE' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SEARCH_WHERE_DROPDOWN'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => CSearchParameters::GetFilterDropDown(),
		],
		'SHOW_WHEN' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BSP_SHOW_WHEN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N'
		],
		'PAGE_RESULT_COUNT' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SEARCH_PAGE_RESULT_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '50',
		],
		'CACHE_TIME'  => ['DEFAULT' => 3600],
		'DISPLAY_TOP_PAGER' => [
			'PARENT' => 'PAGER_SETTINGS',
			'NAME' => GetMessage('CP_BSP_DISPLAY_TOP_PAGER'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'DISPLAY_BOTTOM_PAGER' => [
			'PARENT' => 'PAGER_SETTINGS',
			'NAME' => GetMessage('CP_BSP_DISPLAY_BOTTOM_PAGER'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'PAGER_TITLE' => [
			'PARENT' => 'PAGER_SETTINGS',
			'NAME' => GetMessage('SEARCH_PAGER_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('SEARCH_RESULTS'),
		],
		'PAGER_SHOW_ALWAYS' => [
			'PARENT' => 'PAGER_SETTINGS',
			'NAME' => GetMessage('SEARCH_PAGER_SHOW_ALWAYS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'PAGER_TEMPLATE' => [
			'PARENT' => 'PAGER_SETTINGS',
			'NAME' => GetMessage('SEARCH_PAGER_TEMPLATE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
	],
];

if ($arCurrentValues['SHOW_WHERE'] == 'N')
{
	unset($arComponentParameters['PARAMETERS']['arrWHERE']);
}

CSearchParameters::AddFilterParams($arComponentParameters, $arCurrentValues, 'arrFILTER', 'DATA_SOURCE');
