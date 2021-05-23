<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
use Bitrix\Main\Localization\Loc;
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
$rows = [];
foreach ($arResult["ELEMENTS_ROWS"] as $row)
{
	$actions = [[
			"text" => Loc::getMessage('REST_HOOK_EDIT'),
			"className" => "edit",
			"default" => true,
			"onclick" => "BX.SidePanel.Instance.emulateAnchorClick('".CUtil::JSEscape(str_replace("#id#", $row['ID'], $arParams['EDIT_URL_TPL']))."');"],
		[
			"text" => Loc::getMessage('REST_HOOK_DELETE'),
			"className" => "remove",
			"onclick" => "BX.Main.gridManager.getInstanceById('{$arResult["GRID_ID"]}').removeRow('{$row["ID"]}');"
		]];
	$gridRow = [
		"id" => $row["ID"],
		"actions" => $actions,
		"columnClasses" => [],
		"columns" => [],
	];
	foreach (["ID", "TITLE", "COMMENT", "DATE_CREATE", "EVENT_NAME", "EVENT_HANDLER"] as $fieldId)
	{
		$gridRow["columns"][$fieldId] = htmlspecialcharsbx($row[$fieldId]);
	}
	$rows[] = $gridRow;
}
$APPLICATION->includeComponent(
	"bitrix:main.ui.grid",
	"",
	[
		"GRID_ID" => $arResult["GRID_ID"],
		"COLUMNS" => [
			[
				"id" => "ID",
				"name" => "ID",
				"default" => false
			],
			[
				"id" => "TITLE",
				"name" => Loc::getMessage("REST_HOOK_TITLE"),
				"default" => true
			],
			[
				"id" => "COMMENT",
				"name" => Loc::getMessage("REST_HOOK_COMMENT"),
				"default" => true
			],
			[
				"id" => "DATE_CREATE",
				"name" => Loc::getMessage("REST_HOOK_DATE_CREATE"),
				"default" => true
			],
			[
				"id" => "EVENT_NAME",
				"name" => Loc::getMessage("REST_HOOK_EVENT_NAME"),
				"default" => true
			],
			[
				"id" => "EVENT_HANDLER",
				"name" => Loc::getMessage("REST_HOOK_EVENT_HANDLER"),
				"default" => true
			]
		],
		"ROWS" => $rows,
		"NAV_OBJECT" => $arResult["NAV_OBJECT"], //$navString,
		"TOTAL_ROWS_COUNT" => $arResult['ROWS_COUNT'],
		"CURRENT_PAGE" => $currentPage,
		"MESSAGES" => $arResult["MESSAGES"],

		"SORT" => [],

		"AJAX_MODE" => "Y",
		//	"AJAX_ID" => $arParams["ANSWER_PARAMS"]["AJAX_ID"],
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_HISTORY" => "N",

		"ENABLE_NEXT_PAGE" => false,
		"PAGE_SIZES" => [],
		"ACTION_PANEL" => false,
		"SHOW_CHECK_ALL_CHECKBOXES" => false,
		"SHOW_ROW_CHECKBOXES" => false,
		"SHOW_ROW_ACTIONS_MENU" => true,
		"SHOW_GRID_SETTINGS_MENU" => true,
		"SHOW_MORE_BUTTON" => true,
		"SHOW_NAVIGATION_PANEL" => true,
		"SHOW_PAGINATION" => true,
		"SHOW_SELECTED_COUNTER" => false,
		"SHOW_TOTAL_COUNTER" => true,
		"SHOW_PAGESIZE" => true,
		"ALLOW_COLUMNS_SORT" => false,
		"ALLOW_ROWS_SORT" => false,
		"ALLOW_COLUMNS_RESIZE" => true,
		"ALLOW_HORIZONTAL_SCROLL" => true,
		"ALLOW_SORT" => false,
		"ALLOW_PIN_HEADER" => true,
		"SHOW_ACTION_PANEL" => true,
		"ALLOW_VALIDATE" => false
	],
	false,
	array("HIDE_ICONS" => "Y")
);
?>