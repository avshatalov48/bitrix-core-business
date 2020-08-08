<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
use Bitrix\Main\Localization\Loc;
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$rows = [];
foreach ($arResult["ELEMENTS_ROWS"] as $row)
{
	$actions = [
		"edit" => [
			"text" => Loc::getMessage('APP_EDIT'),
			"className" => "edit",
			"default" => true,
			"onclick" => "BX.SidePanel.Instance.emulateAnchorClick('".CUtil::JSEscape(str_replace("#id#", $row['ID'], $arParams['EDIT_URL_TPL']))."');"
		]
	];

	$onlyApi = empty($row["MENU_NAME"]) && empty($row["MENU_NAME_DEFAULT"]) && empty($row["MENU_NAME_LICENSE"]) && (empty($row['MENU_NAME_ALL']) || !implode('', $row['MENU_NAME_ALL']));

	if(!$onlyApi)
	{
		$actions["view"] = array(
			'className' => 'view',
			'text' => Loc::getMessage('APP_OPEN'),
			"onclick" => "BX.SidePanel.Instance.emulateAnchorClick('".CUtil::JSEscape(str_replace("#id#", $row['ID'], $arParams['APPLICATION_URL']))."');"
		);
	}
	$actions["delete"] = [
		"text" => Loc::getMessage('APP_DELETE'),
		"className" => "remove",
		"onclick" => "BX.Main.gridManager.getInstanceById('{$arResult["GRID_ID"]}').removeRow('{$row["ID"]}');"
	];
	$actions["rights"] = array(
		'text' => Loc::getMessage('APP_RIGHTS'),
		'className' => 'view',
		'onclick' => "BX.rest.Marketplace.setRights('".$row["ID"]."');",
	);

	if(strlen($row['URL_INSTALL']) > 0)
	{
		$actions["reinstall"] = array(
			'text' => Loc::getMessage('APP_REINSTALL'),
			'className' => 'view',
			'onclick' => "BX.rest.Marketplace.reinstall('".$row["ID"]."')"
		);
	}

	$gridRow = array(
		"id" => $row["ID"],
		"actions" => array_values($actions),
		"columnClasses" => array(),
		"columns" => array(),
	);
	foreach (["ID", "APP_NAME", "CLIENT_ID", "CLIENT_SECRET"] as $fieldId)
	{
		$gridRow["columns"][$fieldId] = htmlspecialcharsbx($row[$fieldId]);
	}
	if ($onlyApi)
	{
		$gridRow["columns"]["ONLY_API"] = Loc::getMessage("APP_YES");
	}
	else
	{
		$gridRow["columns"]["ONLY_API"] = Loc::getMessage("APP_NO");
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
				"id" => "APP_NAME",
				"name" => Loc::getMessage("APP_HEADER_NAME"),
				"sticked_default" => true,
				"sticked" => true,
				"default" => true
			],
			[
				"id" => "ONLY_API",
				"name" => Loc::getMessage("APP_HEADER_ONLY_API"),
				"default" => true
			],
			[
				"id" => "CLIENT_ID",
				"name" => Loc::getMessage("APP_HEADER_CLIENT_ID"),
				"default" => true
			],
			[
				"id" => "CLIENT_SECRET",
				"name" => Loc::getMessage("APP_HEADER_SECRET_ID"),
				"default" => true
			]
		],
		"ROWS" => $rows,
		"NAV_OBJECT" => $arResult["NAV_OBJECT"], //$navString,
		"TOTAL_ROWS_COUNT" => $arResult['ROWS_COUNT'],
		"CURRENT_PAGE" => $currentPage,
		"MESSAGES" => $arParams["ANSWER_PARAMS"]["MESSAGES"],

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

<script>
	BX.message({
		"APPLIST_DELETE_CONFIRM" : "<?=GetMessageJS("APPLIST_DELETE_CONFIRM")?>",
		"APPLIST_DELETE_ERROR" : "<?=GetMessageJS("APPLIST_DELETE_ERROR")?>"
	});
</script>