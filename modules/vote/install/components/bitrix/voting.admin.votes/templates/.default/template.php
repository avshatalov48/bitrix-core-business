<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}


/**
 * @global  CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.filter",
	"",
	[
		"FILTER_ID" => $arParams["FILTER_ID"],
		"GRID_ID" => $arParams["GRID_ID"],
		"FILTER" => $arResult["FILTERS"],
		"ENABLE_LABEL" => true,
	]
);

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	[
		"GRID_ID" => $arParams["GRID_ID"],
		"COLUMNS" => $arResult["COLUMNS"],
		"ROWS" => $arResult["ROWS"],
		"ACTION_PANEL" => $arResult["ACTION_PANEL"],
		"NAV_OBJECT" => $arResult["NAV_OBJECT"],
		"~NAV_PARAMS" => ["SHOW_ALWAYS" => false],
		"SHOW_ROW_CHECKBOXES" => true,
		"SHOW_GRID_SETTINGS_MENU" => true,
		"SHOW_PAGINATION" => true,
		"SHOW_SELECTED_COUNTER" => true,
		"SHOW_TOTAL_COUNTER" => false,
		"ALLOW_COLUMNS_SORT" => true,
		"ALLOW_COLUMNS_RESIZE" => true,
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_HISTORY" => "N"
	]
);
