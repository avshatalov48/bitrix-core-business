<?

use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}

$filterParams = [
	'GRID_ID' => $arParams['GRID_ID'],
	'FILTER_ID' => $arParams['FILTER_ID'],
	'FILTER' => $arResult['FILTERS'],
	'DISABLE_SEARCH' => true,
	'ENABLE_LABEL' => true,
];

if ($arParams['USE_TOOLBAR'])
{
	if ($arParams['ADMIN_MODE'])
	{
		$APPLICATION->IncludeComponent('bitrix:ui.toolbar', 'admin', []);
	}

	Toolbar::addFilter($filterParams);
}
else
{
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		$filterParams
	);
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	array(
		'GRID_ID' => $arParams['GRID_ID'],
		'COLUMNS' => $arResult['COLUMNS'],
		'ROWS' => $arResult['ROWS'],
		'NAV_OBJECT' => $arResult['NAV_OBJECT'],
		'~NAV_PARAMS' => ['SHOW_ALWAYS' => false],
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'TOTAL_ROWS_COUNT' => $arResult['TOTAL_ROWS_COUNT'],
		'ALLOW_COLUMNS_SORT' => false,
		'ALLOW_COLUMNS_RESIZE' => false,
		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'AJAX_OPTION_HISTORY' => 'N'
	)
);