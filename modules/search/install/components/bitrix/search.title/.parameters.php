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
	],
	'PARAMETERS' => [
		'PAGE' => [
			'PARENT' => 'URL_TEMPLATES',
			'NAME' => GetMessage('CP_BST_FORM_PAGE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '#SITE_DIR#search/index.php',
		],
		'NUM_CATEGORIES' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BST_NUM_CATEGORIES'),
			'TYPE' => 'STRING',
			'DEFAULT' => '1',
			'REFRESH' => 'Y',
		],
		'TOP_COUNT' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BST_TOP_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '5',
			'REFRESH' => 'Y',
		],
		'ORDER' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BST_ORDER'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'date',
			'VALUES' => [
				'date' => GetMessage('CP_BST_ORDER_BY_DATE'),
				'rank' => GetMessage('CP_BST_ORDER_BY_RANK'),
			],
		],
		'USE_LANGUAGE_GUESS' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BST_USE_LANGUAGE_GUESS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'CHECK_DATES' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BST_CHECK_DATES'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'SHOW_OTHERS' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BST_SHOW_OTHERS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		],
	],
];

if ($arCurrentValues['SHOW_OTHERS'] === 'Y')
{
	$arComponentParameters['GROUPS']['OTHERS_CATEGORY'] = [
		'NAME' => GetMessage('CP_BST_OTHERS_CATEGORY')
	];
	$arComponentParameters['PARAMETERS']['CATEGORY_OTHERS_TITLE'] = [
		'PARENT' => 'OTHERS_CATEGORY',
		'NAME' => GetMessage('CP_BST_CATEGORY_TITLE'),
		'TYPE' => 'STRING',
	];
}

$NUM_CATEGORIES = intval($arCurrentValues['NUM_CATEGORIES']);
if ($NUM_CATEGORIES <= 0)
{
	$NUM_CATEGORIES = 1;
}

for ($i = 0; $i < $NUM_CATEGORIES; $i++)
{
	$arComponentParameters['GROUPS']['CATEGORY_' . $i] = [
		'NAME' => GetMessage('CP_BST_NUM_CATEGORY', ['#NUM#' => $i + 1])
	];
	$arComponentParameters['PARAMETERS']['CATEGORY_' . $i . '_TITLE'] = [
		'PARENT' => 'CATEGORY_' . $i,
		'NAME' => GetMessage('CP_BST_CATEGORY_TITLE'),
		'TYPE' => 'STRING',
	];

	CSearchParameters::AddFilterParams($arComponentParameters, $arCurrentValues, 'CATEGORY_' . $i, 'CATEGORY_' . $i);
}
