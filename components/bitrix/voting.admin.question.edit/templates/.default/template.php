<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
$gridSnippet = new Bitrix\Main\Grid\Panel\Snippet();
\Bitrix\Main\UI\Extension::load(array("popup", "ajax", "ui.buttons", "ui.forms", "main.polyfill.core", "color_picker"));
$tabControl = new CAdminTabControl("tabControl", array(
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("VOTE_QUESTION"),
		"ICON" => "vote_question_edit",
		"TITLE" => GetMessage("VOTE_QUESTION")),
	array(
		"DIV" => "edit3",
		"TAB" => GetMessage("VOTE_PROPERTIES"),
		"ICON" => "vote_question_edit",
		"TITLE" => GetMessage("VOTE_PROPERTIES_TITLE")
	)
));

$controlPanel = [
	"GROUPS"   => [[
		"ITEMS" => [
			$gridSnippet->getEditButton(),
			$gridSnippet->getRemoveButton(),
			[
				"TYPE" => "DROPDOWN",
				"ID" => "base_action_select_" . $arParams["ANSWER_PARAMS"]["GRID_ID"],
				"NAME" => "action_button_" . $arParams["ANSWER_PARAMS"]["GRID_ID"],
				"ITEMS" => [
					[
						"NAME" => Loc::getMessage("VOTE_GRID_ACTIONS"),
						"VALUE" => "default",
						"ONCHANGE" => [["ACTION" => "RESET_CONTROLS"]]
					],
					[
						"NAME" => Loc::getMessage("VOTE_GRID_ACTIONS_ACTIVATE"),
						"VALUE" => "activate",
						"ONCHANGE" => [
							["ACTION" => "RESET_CONTROLS"],
							[
								"ACTION" => "CREATE",
								"DATA" => [$gridSnippet->getApplyButton([
								"ONCHANGE" => [[
										"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
										"DATA" => [["JS" => "Grid.sendSelected()"]]
									]]
								])]
							]
						]
					],
					[
						"NAME" => Loc::getMessage("VOTE_GRID_ACTIONS_DEACTIVATE"),
						"VALUE" => "deactivate",
						"ONCHANGE" => [
							["ACTION" => "RESET_CONTROLS"],
							[
								"ACTION" => "CREATE",
								"DATA" => [$gridSnippet->getApplyButton([
										"ONCHANGE" => [[
											"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
											"DATA" => [["JS" => "Grid.sendSelected()"]]
										]]
									]
								)]
							]
						]
					],
					[
						"NAME" => Loc::getMessage("VOTE_GRID_ACTIONS_UNDELETE"),
						"VALUE" => "undelete",
						"ONCHANGE" => [
							["ACTION" => "RESET_CONTROLS"],
							[
								"ACTION" => "CREATE",
								"DATA" => [$gridSnippet->getApplyButton([
										"ONCHANGE" => [[
											"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
											"DATA" => [["JS" => "Grid.sendSelected()"]]
										]]
									]
								)]
							]
						]
					],
					[
						"NAME" => Loc::getMessage("VOTE_GRID_ACTIONS_RESTORE"),
						"VALUE" => "cancel",
						"ONCHANGE" => [
							["ACTION" => "RESET_CONTROLS"],
							[
								"ACTION" => "CREATE",
								"DATA" => [$gridSnippet->getApplyButton([
										"ONCHANGE" => [[
											"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
											"DATA" => [["JS" => "Grid.sendSelected()"]]
										]]
									]
								)]
							]
						]
					]
				]
			]
		]
	]
	], "ITEMS" => []
];
$gridInstanceID = CUtil::JSEscape($arParams["ANSWER_PARAMS"]["INSTANCE_ID"]);
$gridId = CUtil::JSEscape($arParams["ANSWER_PARAMS"]["GRID_ID"]);
$vote = $arResult["VOTE"];
$questionTypes = \Bitrix\Vote\QuestionTypes::getList();
$rows = array();
while (($row = $arResult["ANSWERS"]->fetch()) && $row)
{
	$gridRow = array(
		"id" => $row["ID"],
		"actions" => array(
			array(
				"text" => Loc::getMessage("VOTE_EDIT"),
				"className" => "edit",
				"default" => true,
				"onclick" => "BX.Vote.editAnswer('{$gridInstanceID}', '{$row["ID"]}');",
			),
			array(
				"text" => Loc::getMessage("VOTE_DELETE"),
				"className" => "remove",
				"onclick" => "BX.Main.gridManager.getInstanceById('{$gridId}').removeRow('{$row["ID"]}');"
			)
		),
		"default_action" => array(
			"title" => Loc::getMessage("VOTE_EDIT"),
			"onclick" => "BX.Vote.editAnswer('{$gridInstanceID}', '{$row["ID"]}');",
		),
		"columnClasses" => array(),
		"columns" => array(),
		"data" => array(),
		"attrs" => array(
			"data-vote-item" => "answer-" . $gridInstanceID,
			"data-item" => array(
				"active" => $row["ACTIVE"],
				"c_sort" => $row["C_SORT"],
				"IMAGE_ID" => $row["IMAGE_ID"],
				"message" => $row["MESSAGE"],
				"message_type" => $row["MESSAGE_TYPE"],
				"field_type" => $row["FIELD_TYPE"],
				"field_width" => $row["FIELD_WIDTH"],
				"field_height" => $row["FIELD_HEIGHT"],
				"field_param" => $row["FIELD_PARAM"],
				"color" => $row["COLOR"],
				"deleted" => $row["DELETED"],
				"saved" => $row["SAVED"]
			)
		)
	);
	foreach ($row as $fieldId => $field)
	{
		$gridRow["columnClasses"][$fieldId] = array();
		if ($row["SAVED"] === "N")
		{
			$gridRow["columnClasses"][$fieldId][] = "main-grid-cell-unsaved";
		}
		if ($row["DELETED"] === "Y")
		{
			$gridRow["columnClasses"][$fieldId][] = "main-grid-cell-deleted";
		}
		$gridRow["columnClasses"][$fieldId] = implode(" ", $gridRow["columnClasses"][$fieldId]);
		$gridRow["columns"][$fieldId] = is_string($field) ? htmlspecialcharsbx($field) : $field;
		$gridRow["data"][$fieldId] = $field;
	}

	$field = htmlspecialcharsbx($gridRow["columns"]["COLOR"]);
	$gridRow["columns"]["COLOR"] = $field <> '' ? "<span class=\"vote-edit-color\" style='border-color:$field;'>$field</span>" : "";
	$gridRow["data"]["COLOR"] = "<input name=\"COLOR\" class=\"main-grid-editor main-grid-editor-text\" id=\"COLOR_control\" value=\"$field\" onclick=\"BX.Vote.showColorPicker(this)\">";

	$gridRow["columns"]["ACTIVE"] = ($gridRow["data"]["ACTIVE"] == "Y" ? GetMessage("admin_lib_list_yes") : GetMessage("admin_lib_list_no"));
	$gridRow["columns"]["FIELD_TYPE"] = \Bitrix\Vote\AnswerTypes::getTitleById($gridRow["columns"]["FIELD_TYPE"]);

	if (empty($row["IMAGE_ID"]))
	{
		$gridRow["columns"]["IMAGE_ID"] = "";
	}
	else
	{
		if (!is_array($row["IMAGE_ID"]))
		{
			$gridRow["columns"]["IMAGE_ID"] = \CFileInput::Show(
				"fileInput_".$row["ID"],
				$row["IMAGE_ID"], array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y"
			));
			$gridRow["data"]["IMAGE_ID"] = CFile::GetFileSRC(CFile::GetFileArray($row["IMAGE_ID"]));
		}
		else if (array_key_exists("relative_tmp_name", $row["IMAGE_ID"]))
		{
			$p = htmlspecialcharsbx($row["IMAGE_ID"]["relative_tmp_name"]);
			$gridRow["columns"]["IMAGE_ID"] = "<img src=\"{$p}\" style=\"max-width: 150px;max-height:150px;\">";
			$gridRow["data"]["IMAGE_ID"] = $row["IMAGE_ID"]["relative_tmp_name"];
		}
		else
		{
			$p = htmlspecialcharsbx($row["IMAGE_ID"]["tmp_name"]);
			$gridRow["columns"]["IMAGE_ID"] = "<img src=\"{$p}\" style=\"max-width: 150px;max-height:150px;\">";
			$gridRow["data"]["IMAGE_ID"] = $row["IMAGE_ID"]["tmp_name"];
		}
	}
	$rows[] = $gridRow;
}
$aMenu = array(
	array(
		"TEXT"	=> GetMessage("VOTE_QUESTIONS"),
		"TITLE"	=> GetMessage("VOTE_QUESTIONS_LIST"),
		"LINK"	=> "/bitrix/admin/vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=".$arResult["VOTE_ID"],
		"ICON" => "btn_list"));

if ($arResult["QUESTION_ID"] > 0)
{
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_CREATE"),
		"TITLE"	=> GetMessage("VOTE_CREATE_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/vote_question_edit.php?VOTE_ID=".$arParams["VOTE_ID"]."&lang=".LANGUAGE_ID,
		"ICON" => "btn_new");
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_COPY"),
		"TITLE"	=> GetMessage("VOTE_CREATE_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/vote_question_edit.php?VOTE_ID=".$arParams["VOTE_ID"]."&COPY_ID=".$arResult["QUESTION_ID"]."&lang=".LANGUAGE_ID,
		"ICON" => "btn_new");
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_DELETE"),
		"TITLE"	=> GetMessage("VOTE_DELETE_RECORD"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("VOTE_DELETE_RECORD_CONFIRM")."')) window.location='/bitrix/admin/vote_question_list.php?action=delete&ID=".$arParams["QUESTION_ID"]."&VOTE_ID=".$arParams["VOTE_ID"]."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
		"ICON" => "btn_delete");
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
if (!empty($arResult["ERROR"]))
{
	echo (new CAdminMessage(implode($arResult["ERROR"], "<br />")))->Show();
}
$arQuestion = $arResult["QUESTION"];
?>

	<form id="form_<?=htmlspecialcharsbx($arParams["ANSWER_PARAMS"]["GRID_ID"])?>" method="POST" action="" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="VOTE_ID" value="<?=$arResult["VOTE_ID"]?>" />
		<input type="hidden" name="QUESTION_ID" value="<?=$arResult["QUESTION_ID"]?>" />
		<input type="hidden" name="ACTION" value="UPDATE" />
		<input type="hidden" name="gridInstanceId" value="<?=htmlspecialcharsbx($arParams["ANSWER_PARAMS"]["INSTANCE_ID"])?>" />
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
		<?

		//region VoteParams
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		if(\COption::GetOptionString("vote", "USE_HTML_EDIT") == "Y" && CModule::IncludeModule("fileman")):?>
			<tr>
				<td align="center" colspan="2"><?
					CFileMan::AddHTMLEditorFrame("QUESTION",
						htmlspecialcharsbx($arQuestion["QUESTION"]),
						"QUESTION_TYPE",
						$arQuestion["QUESTION_TYPE"],
						array(
							"height" => "100",
							"width" => "100%",
							"placeholder" => "Question text inside"));
					?></td>
			</tr>
		<?else:?>
			<tr>
				<td align="center" colspan="2"><?=InputType("radio","QUESTION_TYPE","text",$arQuestion["QUESTION_TYPE"],false)?>Text &nbsp;/&nbsp;<?=InputType("radio","QUESTION_TYPE","html",$arQuestion["QUESTION_TYPE"],false)?>HTML</td>
			</tr>
			<tr>
				<td align="center" colspan="2"><textarea name="QUESTION" style="width:100%" rows="23"><?=$arQuestion["QUESTION"]?></textarea></td>
			</tr>
		<?endif;?>
		<tr>
			<td width="30%"><?=GetMessage("VOTE_IMAGE")?></td>
			<td><?=CFile::InputFile("IMAGE_ID", 20, $arQuestion["IMAGE_ID"]);?><?
				if (is_array($arQuestion["IMAGE"])):
					?><br /><?=CFile::ShowImage($arQuestion["IMAGE"], 200, 200, "border=0", "", true)?><?
				endif;?>
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage("VOTE_FIELD_TYPE")?></td>
			<td>
				<?=SelectBoxFromArray("FIELD_TYPE", array("reference_id" => array_keys($questionTypes), "reference" => array_values($questionTypes)), $arQuestion["FIELD_TYPE"])?>
				<br/> <i><?= Loc::getMessage("VOTE_TYPE_NOTIFY") ?></i>
			</td>
		</tr>
		<tr>
			<td> </td>
			<td><?=InputType("checkbox", "REQUIRED", "Y", $arQuestion["REQUIRED"], false)?> <label for="REQUIRED"><?=Loc::getMessage("VOTE_REQUIRED")?></label></td>
		</tr>
		<?
		/************** Answers Tab ****************************************/
		$tabControl->BeginNextTab();
		?>
		<tr>
			<td width="40%"><?=GetMessage("VOTE_VOTE")?></td>
			<td>[<a href="vote_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$vote["ID"]?>" title="<?=GetMessage("VOTE_CONF")?>"><?=$vote["ID"]?></a>]&nbsp;
				<?=htmlspecialcharsbx($vote["TITLE"])?></td>
		</tr>
		<?if ($arQuestion["TIMESTAMP_X"] <> ''):?>
			<tr><td><?=GetMessage("VOTE_TIMESTAMP")?></td>
				<td><?=$arQuestion["TIMESTAMP_X"]?></td>
			</tr>
			<tr><td><?=GetMessage("VOTE_COUNTER_QUESTION")?></td>
				<td><?=$arQuestion["COUNTER"]?></td>
			</tr>
		<?endif;?>
		<tr>
			<td><?=GetMessage("VOTE_ACTIVE")?></td>
			<td><?=InputType("checkbox", "ACTIVE", "Y", $arQuestion["ACTIVE"], false)?> <label for="ACTIVE"><?= Loc::getMessage("VOTE_ACTIVE_LABEL") ?></label></td>
		</tr>
		<tr><td><?=GetMessage("VOTE_C_SORT")?></td>
			<td><input type="text" id="C_SORT" name="C_SORT" size="5" maxlength="18" value="<?=intval($arQuestion["C_SORT"])?>" /></td>
		</tr>
		<tr>
			<td><?=GetMessage("VOTE_DIAGRAM")?></td>
			<td><input type="hidden" name="DIAGRAM" value="N"/><input type="checkbox" name="DIAGRAM" id="DIAGRAM" value="Y" <?=($arQuestion["DIAGRAM"] == "Y" ? "checked='checked'" : "")?> /> <label for="DIAGRAM"><?=GetMessage("VOTE_DIAGRAM_LABEL")?></label></td>
		</tr>
		<tr>
			<td><?=GetMessage("VOTE_DIAGRAM_TYPE")?>:</td>
			<td><?echo SelectBoxFromArray("DIAGRAM_TYPE", GetVoteDiagramList(), $arQuestion["DIAGRAM_TYPE"]);?></td>
		</tr>
		<?
		$tabControl->EndTab();
		//endregion
		?></form><?
		//region Answers1
		?>
		<div id="answer_grid_container" style="padding:12px 18px 12px 12px;">
			<div class="adm-detail-title"><?=Loc::getMessage("VOTE_ANSWERS")?></div>
			<?$APPLICATION->includeComponent(
				"bitrix:main.ui.grid",
				"",
				array(
					"GRID_ID" => $arParams["ANSWER_PARAMS"]["GRID_ID"],
					"COLUMNS" => array(
						array(
							"id" => "ID",
							"name" => "ID",
							"sort" => "ID",
							"default" => false),
						array(
							"id" => "IMAGE_ID",
							"name" => GetMessage("VOTE_IMAGE_ID"),
							"default" => true,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::IMAGE
							)),
						array(
							"id" => "MESSAGE",
							"name" => Loc::getMessage("VOTE_ANSWER_MESSAGE"),
							"sticked_default" => true,
							"sticked" => true,
							"sort" => "MESSAGE",
							"default" => true,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::TEXTAREA
							)),
						array(
							"id" => "MESSAGE_TYPE",
							"name" => Loc::getMessage("VOTE_ANSWER_MESSAGE_TYPE"),
							"default" => false,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::DROPDOWN,
								"items" => array(
									"html" => "html",
									"text" => "text"
								)
							)
						),
						array(
							"id" => "FIELD_TYPE",
							"name" => Loc::getMessage("VOTE_ANSWER_FIELD_TYPE"),
							"default" => true,
							"sort" => "FIELD_TYPE",
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::DROPDOWN,
								"items" => \Bitrix\Vote\AnswerTypes::getTitledList()
							)
						),
						array(
							"id" => "FIELD_WIDTH",
							"name" => Loc::getMessage("VOTE_FIELD_WIDTH"),
							"default" => false,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::TEXT,
							)
						),
						array(
							"id" => "FIELD_HIGHT",
							"name" => Loc::getMessage("VOTE_FIELD_HEIGHT"),
							"default" => false,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::TEXT,
							)
						),
						array(
							"id" => "FIELD_PARAM",
							"name" => Loc::getMessage("VOTE_FIELD_PARAM"),
							"default" => false,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::TEXT,
							)
						),
						array(
							"id" => "ACTIVE",
							"name" => GetMessage("VOTE_ACTIVE"),
							"sort" => "ACTIVE",
							"default" => false,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::DROPDOWN,
								"items" => array(
									"Y" => GetMessage("admin_lib_list_yes"),
									"N" => GetMessage("admin_lib_list_no")
								)
							)
						),
						array(
							"id" => "C_SORT",
							"name" => Loc::getMessage("VOTE_SORT"),
							"sort" => "C_SORT",
							"default" => true,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::NUMBER
							)
						),
						array(
							"id" => "COLOR",
							"name" => Loc::getMessage("VOTE_COLOR"),
							"sort" => "COLOR",
							"default" => true,
							"editable" => array(
								"TYPE" => \Bitrix\Main\Grid\Editor\Types::CUSTOM
							)
						),
						array(
							"id" => "COUNTER",
							"name" => Loc::getMessage("VOTE_COUNTER"),
							"sort" => "COUNTER",
							"default" => false,
						)
					),
					"ROWS" => $rows,
					"NAV_STRING" => "", //$navString,
					"NAV_PARAM_NAME" => "", //$navParamName,
					"TOTAL_ROWS_COUNT" => count($rows),
					"CURRENT_PAGE" => $currentPage,
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
			);
			?>
			<button id="add_regular_type_answer" class="ui-btn ui-btn-primary ui-btn-icon-add" onclick="BX.Vote.addAnswer('<?=htmlspecialcharsbx($gridInstanceID)?>'); return false;">+</button>
			<button id="add_text_type_answer" class="ui-btn ui-btn-primary ui-btn-light-border" onclick="BX.Vote.addTextAnswer('<?=htmlspecialcharsbx($gridInstanceID)?>'); return false;"><?= Loc::getMessage("VOTE_GRID_BUTTON_ADD_TEXT") ?></button>
		</div><?
		//endregion
		$tabControl->Buttons(
			array(
				"back_url"=>"vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=".$arResult["VOTE_ID"]
			)
		);
		$tabControl->End();
		?>
<script>
BX.ready(function() {
	BX.message({
		VOTE_ANSWER_PLACEHOLDER : '<?=GetMessageJS("VOTE_ANSWER_PLACEHOLDER")?>',
		VOTE_ANSWER_PLACEHOLDER1 : '<?=GetMessageJS("VOTE_ANSWER_PLACEHOLDER1")?>',
		VOTE_ANSWER_FIELD_TYPE : '<?=GetMessageJS("VOTE_ANSWER_FIELD_TYPE")?>',
		VOTE_ANSWER_TEXT_OTHER : '<?=GetMessageJS("VOTE_ANSWER_TEXT_OTHER")?>',
		VOTE_ANSWER_MESSAGE : '<?=GetMessageJS("VOTE_ANSWER_MESSAGE")?>',
		VOTE_SAVE : '<?=GetMessageJS("VOTE_SAVE")?>',
		VOTE_CANCEL : '<?=GetMessageJS("VOTE_CANCEL")?>'
	});
	BX.Vote.setTypes({
		questionTypes : <?=\CUtil::PhpToJSObject(\Bitrix\Vote\QuestionTypes::getFullList())?>,
		answerTypes : <?=\CUtil::PhpToJSObject(\Bitrix\Vote\AnswerTypes::getFullList())?>
	});
	BX.Vote.setParams('<?=$gridInstanceID?>',
		{
			formId : 'form_<?=$gridId?>',
			gridId: '<?=$gridId?>',
			maxSort : <?=intval($arParams["ANSWER_PARAMS"]["MAX_SORT"])?>,
			voteId : <?=intval($arParams["VOTE_ID"])?>,
			questionId : <?=intval($arParams["QUESTION_ID"])?>
		});
});
</script>
<?
$tabControl->ShowWarnings("form1", $message);
$sDocTitle = ($arResult["QUESTION_ID"] > 0 ? str_replace("#ID#", $arResult["QUESTION_ID"], GetMessage("VOTE_EDIT_RECORD")) : GetMessage("VOTE_NEW_RECORD"));
$APPLICATION->SetTitle($sDocTitle);
