<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

global $APPLICATION;
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/main.ui.grid/templates/.default/style.css');

$APPLICATION->SetTitle(
	Loc::getMessage(
		'MAIN_NUMERATOR_EDIT_SEQUENCE_PAGE_TITLE',
		['#NUMERATOR_NAME#' => $arResult['NUMERATOR_NAME'] ?? '']
	)
);

$arResult['GRID_DATA'] = [];

foreach ($arResult['ITEMS'] as &$item)
{
	$gridActions = [];
	$arResult['GRID_DATA'][] = [
		'id'       => $item['ID'],
		'actions'  => [],
		'data'     => $item,
		'editable' => true,
		"columns"  => [
			"TEXT_KEY"    => $item["TEXT_KEY"],
			"NEXT_NUMBER" => $item["NEXT_NUMBER"],
		],
	];
}

unset($item);
$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID'             => $arResult['GRID_ID'],
		'MESSAGES'            => $arResult['MESSAGES'] ?? [],
		'AJAX_MODE'           => 'Y',
		'AJAX_OPTION_JUMP'    => 'N',
		'AJAX_OPTION_HISTORY' => 'N',
		'HEADERS'             => $arResult['HEADERS'],
		'SORT'                => $arResult['SORT'],
		'SORT_VARS'           => $arResult['SORT_VARS'],
		'ROWS'                => $arResult['GRID_DATA'],
		'EDITABLE'            => true,
		'ACTIONS'             => [],
		'ACTION_ALL_ROWS'     => false,
		'NAV_OBJECT'          => $arResult['ITEMS'],
		'FORM_ID'             => $arResult['FORM_ID'],
		'TAB_ID'              => $arResult['TAB_ID'],
		'ALLOW_COLUMNS_SORT'  => false,
		'ACTION_PANEL'        => [
			'GROUPS' => [
				[
					'ITEMS' =>
						[
							$snippet->getEditButton(),
						],
				],
			],
		],
		'TOTAL_ROWS_COUNT'    => $arResult['ROWS_COUNT'],
	],
	$component
);
