<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
$arTemplateParameters = [
	'TAGS_SORT' => [
		'NAME' => GetMessage('SEARCH_SORT'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'VALUES' => ['NAME' => GetMessage('SEARCH_NAME'), 'CNT' => GetMessage('SEARCH_CNT')],
		'DEFAULT' => 'NAME',
	],
	'TAGS_PAGE_ELEMENTS' => [
		'NAME' => GetMessage('SEARCH_PAGE_ELEMENTS'),
		'TYPE' => 'STRING',
		'DEFAULT' => '150',
	],
	'TAGS_PERIOD' => [
		'NAME' => GetMessage('SEARCH_PERIOD'),
		'TYPE' => 'STRING',
		'DEFAULT' => '',
	],
	'TAGS_URL_SEARCH' => [
		'NAME' => GetMessage('SEARCH_URL_SEARCH'),
		'TYPE' => 'STRING',
		'DEFAULT' => '',
	],
	'TAGS_INHERIT' => [
		'NAME' => GetMessage('SEARCH_TAGS_INHERIT'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'Y',
	],
	'FONT_MAX' => [
		'NAME' => GetMessage('SEARCH_FONT_MAX'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '50',
	],
	'FONT_MIN' => [
		'NAME' => GetMessage('SEARCH_FONT_MIN'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '10',
	],
	'COLOR_NEW' => [
		'NAME' => GetMessage('SEARCH_COLOR_NEW'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '000000',
	],
	'COLOR_OLD' => [
		'NAME' => GetMessage('SEARCH_COLOR_OLD'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'C8C8C8',
	],
	'PERIOD_NEW_TAGS' => [
		'NAME' => GetMessage('SEARCH_PERIOD_NEW_TAGS'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '',
	],
	'SHOW_CHAIN' => [
		'NAME' => GetMessage('SEARCH_SHOW_CHAIN'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'Y',
	],
	'COLOR_TYPE' => [
		'NAME' => GetMessage('SEARCH_COLOR_TYPE'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'Y',
	],
	'WIDTH' => [
		'NAME' => GetMessage('SEARCH_WIDTH'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '100%',
	],
	'USE_SUGGEST' => [
		'NAME' => GetMessage('TP_BSP_USE_SUGGEST'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	],
];

if (COption::GetOptionString('search', 'use_social_rating') == 'Y')
{
	$arTemplateParameters['SHOW_RATING'] = [
		'NAME' => GetMessage('TP_BSP_SHOW_RATING'),
		'TYPE' => 'LIST',
		'VALUES' => [
			'' => GetMessage('TP_BSP_SHOW_RATING_CONFIG'),
			'Y' => GetMessage('MAIN_YES'),
			'N' => GetMessage('MAIN_NO'),
		],
		'MULTIPLE' => 'N',
		'DEFAULT' => '',
	];
	$arTemplateParameters['RATING_TYPE'] = [
		'NAME' => GetMessage('TP_BSP_RATING_TYPE'),
		'TYPE' => 'LIST',
		'VALUES' => [
			'' => GetMessage('TP_BSP_RATING_TYPE_CONFIG'),
			'like' => GetMessage('TP_BSP_RATING_TYPE_LIKE_TEXT'),
			'like_graphic' => GetMessage('TP_BSP_RATING_TYPE_LIKE_GRAPHIC'),
			'standart_text' => GetMessage('TP_BSP_RATING_TYPE_STANDART_TEXT'),
			'standart' => GetMessage('TP_BSP_RATING_TYPE_STANDART_GRAPHIC'),
		],
		'MULTIPLE' => 'N',
		'DEFAULT' => '',
	];
	$arTemplateParameters['PATH_TO_USER_PROFILE'] = [
		'NAME' => GetMessage('TP_BSP_PATH_TO_USER_PROFILE'),
		'TYPE' => 'STRING',
		'DEFAULT' => '',
	];
}
