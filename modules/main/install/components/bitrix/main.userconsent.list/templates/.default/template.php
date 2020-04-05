<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}

foreach ($arResult['ROWS'] as $index => $data)
{
	$pathToEdit = str_replace('#id#', $data['ID'], $arParams['PATH_TO_EDIT']);
	$data['NAME'] = '<a data-bx-slider-href="" href="' . htmlspecialcharsbx($pathToEdit) . '">' . htmlspecialcharsbx($data['NAME']) . '</a>';

	$actions = array();
	$path = $pathToEdit;
	$actions[] = array(
		'text' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_ROW_ACTION_VIEW'),
		'onclick' => 'BX.UserConsentListManager.open("' . \CUtil::JSEscape($path). '")',
		'default' => true
	);
	$path = str_replace('#id#', $data['ID'], $arParams['PATH_TO_CONSENT_LIST']);
	$actions[] = array(
		'text' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_ROW_ACTION_VIEW_CONSENTS'),
		'onclick' => 'BX.UserConsentListManager.open("' . \CUtil::JSEscape($path). '")',
	);
	$actions[] = array(
		'text' => Loc::getMessage('MAIN_USER_CONSENT_LIST_COMP_UI_ROW_ACTION_REMOVE'),
		'onclick' => 'BX.UserConsentListManager.remove(' . \CUtil::JSEscape($data['ID']). ', "' . \CUtil::JSEscape($arParams['GRID_ID']). '")',
	);

	$arResult['ROWS'][$index] = array(
		'id' => $data['ID'],
		'columns' => $data,
		'actions' => $actions
	);
}

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.filter",
	"",
	array(
		"FILTER_ID" => $arParams['FILTER_ID'],
		"GRID_ID" => $arParams['GRID_ID'],
		"RENDER_FILTER_INTO_VIEW" => $arParams['RENDER_FILTER_INTO_VIEW'],
		"RENDER_FILTER_INTO_VIEW_SORT" => $arParams['RENDER_FILTER_INTO_VIEW_SORT'],
		"FILTER" => $arResult['FILTERS'],
		"DISABLE_SEARCH" => true,
		"ENABLE_LABEL" => true,
	)
);

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $arParams['GRID_ID'],
		"COLUMNS" => $arResult['COLUMNS'],
		"ROWS" => $arResult['ROWS'],
		"NAV_OBJECT" => $arResult['NAV_OBJECT'],
		"~NAV_PARAMS" => array('SHOW_ALWAYS' => false),
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_GRID_SETTINGS_MENU' =>true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		"TOTAL_ROWS_COUNT" => $arResult['TOTAL_ROWS_COUNT'],
		'ALLOW_COLUMNS_SORT' => false,
		'ALLOW_COLUMNS_RESIZE' => false,
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_HISTORY" => "N"
	)
);