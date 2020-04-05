<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
\Bitrix\Main\UI\Extension::load(array("popup", "ajax", "ui.buttons", "ui.forms"));
$gridSnippet = new Bitrix\Main\Grid\Panel\Snippet();
/** @var CDBResult $data */
$data = $arResult["DATA"];
/** @var string $gridId */
$gridId = $arParams["GRID_ID"];
/** @var string $voteId */
$voteId = $arParams["VOTE_ID"];
$controlPanel = array(
	"GROUPS" => [["ITEMS" => [
		$gridSnippet->getEditButton(),
		$gridSnippet->getRemoveButton(),
		[
			"TYPE" => "DROPDOWN",
			"ID" => "base_action_select_".$gridId,
			"NAME" => "action_button_".$gridId,
			"ITEMS" => [
				[
					"NAME" => GetMessage("admin_lib_list_actions"),
					"VALUE" => "default",
					"ONCHANGE" => [ ["ACTION" => "RESET_CONTROLS",]]
				],
				[
					"NAME" => Loc::getMessage("VOTE_ACTIVATE"),
					"VALUE" => "activate",
					"ONCHANGE" => [
						["ACTION" => "RESET_CONTROLS" ],
						[
							"ACTION" => "CREATE",
							"DATA" => [$gridSnippet->getApplyButton([
								"ONCHANGE" => [[
										"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
										"DATA" => [["JS" => "BX.Main.gridManager.getInstanceById('".$gridId."').sendSelected()"]]
									]]
								]
							)]
						]
					]
				],
				[
					"NAME" => Loc::getMessage("VOTE_DEACTIVATE"),
					"VALUE" => "deactivate",
					"ONCHANGE" => [
						["ACTION" => "RESET_CONTROLS"],
						[
							"ACTION" => "CREATE",
							"DATA" => [$gridSnippet->getApplyButton([
								"ONCHANGE" => [[
										"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
										"DATA" => [["JS" => "BX.Main.gridManager.getInstanceById('".$gridId."').sendSelected()"]]
									]]
								]
							)]
						]
					]
				]
			]
		]
	]]],
	"ITEMS" => array()
);
$rows = [];
while($row = $data->fetch())
{
	$gridRow = array(
		"id" => $row["ID"],
		"actions" => array(
			array(
				"text" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
				"className" => "edit",
				"href" => "/bitrix/admin/vote_question_edit.php?ID={$row['ID']}&VOTE_ID={$row["VOTE_ID"]}"
			),
			$gridSnippet->getRemoveAction(),
			array(
				"text" => Loc::getMessage("MAIN_ADMIN_MENU_DELETE"),
				"className" => "remove",
				"onclick" => "if(confirm('" . GetMessage("VOTE_CONFIRM_DEL_QUESTION") . "')) {BX.Main.gridManager.getInstanceById('{$gridId}').removeRow({$row['ID']})}"
			)
		),
		"default_action" => array(
			"title" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"href" => "/bitrix/admin/vote_question_edit.php?ID={$row['ID']}&VOTE_ID={$row["VOTE_ID"]}"
		),
		"columnClasses" => array(),
		"columns" => array(),
		"data" => array()
	);
	foreach ($row as $fieldId => $field)
	{
		$gridRow["columns"][$fieldId] = htmlspecialcharsbx($field);
		$gridRow["data"][$fieldId] = $field;
	}
	$gridRow["columns"]["QUESTION"] = ($row["QUESTION_TYPE"]=="text" ? htmlspecialcharsex($row["QUESTION"]) : HTMLToTxt($row["QUESTION"]));
	$gridRow["columns"]["QUESTION_TYPE"] = strtolower($row["QUESTION_TYPE"]);
	$gridRow["columns"]["ACTIVE"] = ($gridRow["data"]["ACTIVE"] == "Y" ? GetMessage("admin_lib_list_yes") : GetMessage("admin_lib_list_no"));
	$gridRow["columns"]["REQUIRED"] = ($gridRow["data"]["REQUIRED"] == "Y" ? GetMessage("admin_lib_list_yes") : GetMessage("admin_lib_list_no"));
	$gridRow["columns"]["DIAGRAM"] = ($gridRow["data"]["DIAGRAM"] == "Y" ? GetMessage("admin_lib_list_yes") : GetMessage("admin_lib_list_no"));

	if ($row["IMAGE_ID"] > 0)
	{
		$gridRow["columns"]["IMAGE_ID"] = CFileInput::Show(
			"fileInput_".$row["IMAGE_ID"],
			$row["IMAGE_ID"], array(
			"IMAGE" => "Y",
			"PATH" => "Y",
			"FILE_SIZE" => "Y",
			"DIMENSIONS" => "Y",
			"IMAGE_POPUP" => "Y"
		));
		$gridRow["data"]["IMAGE_ID"] = CFile::GetFileSRC(CFile::GetFileArray($row["IMAGE_ID"]));
	}
	$rows[] = $gridRow;
}
?>
	<div class="adm-toolbar-panel-container">
		<div class="adm-toolbar-panel-flexible-space">
			<?
			if ($arParams["SHOW_FILTER"] === "Y")
			{
				?><?$APPLICATION->includeComponent(
					"bitrix:main.ui.filter",
					"",
					[
						"FILTER_ID" => $arResult["FILTER_ID"],
						"GRID_ID" => $gridId,
						"FILTER" => $arResult["FILTER_FIELDS"],
//						"FILTER_PRESETS" => $this->filterPresets,
						"ENABLE_LABEL" => true,
						"ENABLE_LIVE_SEARCH" => true
					],
					false,
					["HIDE_ICONS" => true]
				);?><?
			}
			?>

		</div>
		<a class="ui-btn ui-btn-primary ui-btn-icon-add" href="/bitrix/admin/vote_question_edit.php?lang=<?=LANG?>&VOTE_ID=<?=$voteId?>"><?=GetMessage("VOTE_ADD_QUESTION")?></a>
	</div>
<?
?><?$APPLICATION->includeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $gridId,
		"COLUMNS" => [
			["id" => "ID", "name" => "ID", "sort" => "ID", "default" => false],
			[
				"id" => "IMAGE_ID",
				"name" => Loc::getMessage("VOTE_IMAGE_ID"),
				"default" => true,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::IMAGE,
				]
			],
			[
				"id" => "QUESTION",
				"name" => Loc::getMessage("VOTE_QUESTION"),
				"sticked_default" => true,
				"sticked" => true,
				"sort" => "QUESTION",
				"default" => true,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::TEXTAREA
				]
			],
			[
				"id" => "QUESTION_TYPE",
				"name" => Loc::getMessage("VOTE_QUESTION_TYPE"),
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::DROPDOWN,
					"items" => [
						"html" => "html",
						"text" => "text"
					]
				]
			],
			[
				"id" => "ACTIVE",
				"name" => GetMessage("VOTE_ACTIVE"),
				"sort" => "ACTIVE",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::CHECKBOX,
					"VALUE" => "Y"
				]
			],
			[
				"id" => "REQUIRED",
				"name" => GetMessage("VOTE_REQUIRED"),
				"sort" => "REQUIRED",
				"default" => true,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::CHECKBOX,
					"VALUE" => "Y"
				]
			],
			[
				"id" => "C_SORT",
				"name" => GetMessage("VOTE_C_SORT"),
				"sort" => "C_SORT",
				"default" => true,
				"editable" => array("TYPE" => \Bitrix\Main\Grid\Editor\Types::NUMBER)
			],
			[
				"id" => "DIAGRAM",
				"name" => GetMessage("VOTE_DIAGRAM"),
				"sort" => "DIAGRAM",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::CHECKBOX,
					"VALUE" => "Y"
				]
			],
			["id" => "TIMESTAMP_X", "name" => GetMessage("VOTE_TIMESTAMP_X"), "sort" => "TIMESTAMP_X", "default" => false],
		],
		"ROWS" => $rows,
		"NAV_STRING" => "", //$navString,
		"NAV_PARAM_NAME" => "", //$navParamName,
		"TOTAL_ROWS_COUNT" => count($rows),
		"CURRENT_PAGE" => $APPLICATION->GetCurPageParam(),
		"MESSAGES" => $arParams["ANSWER_PARAMS"]["MESSAGES"],

		"SORT" => array(
			"sort" => array("C_SORT" => "ASC")
		),

		"AJAX_MODE" => "Y",
	//	"AJAX_ID" => $arParams["ANSWER_PARAMS"]["AJAX_ID"],
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_HISTORY" => "N",

		"ENABLE_NEXT_PAGE" => false,
		"PAGE_SIZES" => array(),
		"ACTION_PANEL" => $controlPanel,
		"SHOW_CHECK_ALL_CHECKBOXES" => true,
		"SHOW_ROW_CHECKBOXES" => true,
		"SHOW_ROW_ACTIONS_MENU" => true,
		"SHOW_GRID_SETTINGS_MENU" => true,
		"SHOW_MORE_BUTTON" => true,
		"SHOW_NAVIGATION_PANEL" => false,
		"SHOW_PAGINATION" => false,
		"SHOW_SELECTED_COUNTER" => true,
		"SHOW_TOTAL_COUNTER" => false,
		"SHOW_PAGESIZE" => false,
		"ALLOW_COLUMNS_SORT" => true,
		"ALLOW_ROWS_SORT" => false,
		"ALLOW_COLUMNS_RESIZE" => true,
		"ALLOW_HORIZONTAL_SCROLL" => true,
		"ALLOW_SORT" => true,
		"ALLOW_PIN_HEADER" => true,
		"SHOW_ACTION_PANEL" => true,
		"ALLOW_VALIDATE" => false
	),
	false,
	array("HIDE_ICONS" => "Y")
);?>