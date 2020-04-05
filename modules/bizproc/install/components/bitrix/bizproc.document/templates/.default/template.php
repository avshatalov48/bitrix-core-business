<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;

CBPDocument::AddShowParameterInit($arParams["MODULE_ID"], "only_users", $arParams["DOCUMENT_TYPE"]);

$bizProcIndex = 0;
$bEmpty = true;
$bShowButtons = false;

$arDocumentStates = $arResult["DOCUMENT_STATES"];
$postFormUri = isset($arParams["POST_FORM_URI"]) ? $arParams["POST_FORM_URI"] : "";

$actionUrl = CHTTP::urlAddParams(
	CHTTP::urlDeleteParams(
		$postFormUri !== "" ? $postFormUri : $APPLICATION->GetCurPage(), array("id", "action", "sessid", "back_url")),
	array("sessid" => bitrix_sessid())
);

if(!empty($arParams["~back_url"]))
{
	$actionUrl = CHTTP::urlAddParams(
		$actionUrl,
		array("back_url" => urlencode($arParams["~back_url"]))
	);
}

?>
<div class="bizproc-page-document">

<form action="<?=htmlspecialcharsbx($postFormUri !== '' ? $postFormUri : POST_FORM_ACTION_URI)?>" method="POST" class="bizproc-form" name="start_workflow_form1" id="start_workflow_form1">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($arParams["~back_url"])?>" />

<ul class="bizproc-list bizproc-document-states">
<?
$iCount = 0;
if ($arParams["StartWorkflowPermission"] == "Y"):
	$bEmpty = false;
	$iCount++;
	$url = CComponentEngine::MakePathFromTemplate($arParams["~WORKFLOW_START_URL"],
					array("MODULE_ID" => $arParams["DOCUMENT_ID"][0], "ENTITY" => $arParams["DOCUMENT_ID"][1],
						"DOCUMENT_ID" => $arParams["DOCUMENT_ID"][2], "DOCUMENT_TYPE" => $arParams["DOCUMENT_TYPE"][2],
						"ID" => $arParams["DOCUMENT_ID"][2]));
	$url .= (strpos($url, "?") === false ? "?" : "&").bitrix_sessid_get()."&back_url=".
		urlencode(!empty($arParams["~back_url"]) ? $arParams["~back_url"] : $APPLICATION->GetCurPageParam("", array("back_url")));
?>
	<li class="bizproc-list-item bizproc-document-start bizproc-list-item-first">
		<table class="bizproc-table-main" cellpadding="0" border="0">
			<tr>
				<td class="bizproc-field-name">
					<?=GetMessage("IBEL_BIZPROC_NEW")?>:
				</td>
				<td class="bizproc-field-value">
					<span><a href="<?=$url?>"><?=GetMessage("IBEL_BIZPROC_START")?></a></span>
				</td>
			</tr>
		</table>
	</li>
<?
endif;

foreach ($arDocumentStates as $arDocumentState)
{
	$bizProcIndex++;

	if (intVal($arDocumentState["WORKFLOW_STATUS"]) < 0):
		continue;
	elseif (!CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::ViewWorkflow,
		$GLOBALS["USER"]->GetID(),
		$arParams["DOCUMENT_ID"],
		array(
			"DocumentStates" => $arDocumentStates,
			"WorkflowId" => $arDocumentState["ID"] > 0 ? $arDocumentState["ID"] : $arDocumentState["TEMPLATE_ID"]))):
		continue;
	endif;
	$bEmpty = false;
	$arTasks = array();
	if ($arDocumentState["ID"] > 0)
		$arTasks = CBPDocument::GetUserTasksForWorkflow($USER->GetID(), $arDocumentState["ID"]);
	$arEvents = CBPDocument::GetAllowableEvents($GLOBALS["USER"]->GetID(), $arParams["USER_GROUPS"], $arDocumentState);
$iCount++;
$iCountRow = 0;
?>
	<li class="bizproc-list-item bizproc-document-process <?=(strlen($arDocumentState["ID"]) < 0 ?
				"bizproc-document-notstarted" : (strlen($arDocumentState["WORKFLOW_STATUS"]) > 0 ?
				"bizproc-document-inprogress" :
				"bizproc-document-finished"))?> <?=(empty($arTasks) ? "" :
				"bizproc-document-hastasks")
				?> <?
				?><?=($iCount == 1 ? "bizproc-list-item-first" : "")?> <?
				?><?=($iCount%2 == 1 ? "bizproc-list-item-odd " : "bizproc-list-item-even ")?>">
<table class="bizproc-table-main" cellpadding="0" border="0">
	<thead>
		<tr>
			<th colspan="2">
				<div class="bizproc-document-controls">
				<?if (strlen($arDocumentState["ID"]) > 0):
					$tmp = false;
					if (strlen($arDocumentState["WORKFLOW_STATUS"]) > 0):
						$tmp = true;?>
					<span class="bizproc-document-control-first">
						<a href="<?=CHTTP::urlAddParams($actionUrl, array("id" => urlencode($arDocumentState["ID"]), "action" => "stop_bizproc"))?>"><?=GetMessage("IBEL_BIZPROC_STOP")?></a></span>
					<?elseif ($arParams["DropWorkflowPermission"] == "Y"):
						$tmp = true;?>
					<span class="bizproc-document-control-first">
						<a href="<?=CHTTP::urlAddParams($actionUrl, array("id" => $arDocumentState["ID"], "action" => "del_bizproc"))?>"><?=GetMessage("IBEL_BIZPROC_DEL")?></a></span>
					<?endif;?>
					<span class="<?=($tmp ? "bizproc-document-control-second" : "bizproc-document-control-single")?>">
						<a href="<?=CComponentEngine::MakePathFromTemplate($arParams["~WORKFLOW_LOG_URL"],
						array("MODULE_ID" => $arParams["DOCUMENT_ID"][0], "ENTITY" => $arParams["DOCUMENT_ID"][1],
							"DOCUMENT_ID" => $arParams["DOCUMENT_ID"][2], "DOCUMENT_TYPE" => $arParams["DOCUMENT_TYPE"][2],
							"ID" => $arDocumentState["ID"], "STATE_ID" => $arDocumentState["ID"]))?>"><?=GetMessage("IBEL_BIZPROC_LOG")?></a></span>
				<?endif;?>
				</div>
				<?=$arDocumentState["TEMPLATE_NAME"]?>
			</th>
		</tr>
	</thead>
	<tbody>

		<?if (strlen($arDocumentState["STATE_MODIFIED"]) > 0):
		$iCountRow++;
		?>
		<tr class="<?=($iCountRow == 1 ? "bizproc-item-row-first" : "")?> <?
			?><?=(empty($arTasks) && empty($arEvents) && empty($arDocumentState["TEMPLATE_PARAMETERS"]) && strlen($arDocumentState["STATE_NAME"]) <= 0 ?
				"bizproc-item-row-last" : "")?>">
			<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_DATE")?>:</td>
			<td class="bizproc-field-value"><?= FormatDateFromDB($arDocumentState["STATE_MODIFIED"]) ?></td>
		</tr>
		<?endif;?>
		<?if (strlen($arDocumentState["STATE_NAME"]) > 0):
		$iCountRow++;
		?>
		<tr class="<?=($iCountRow == 1 ? "bizproc-item-row-first" : "")?> <?
			?><?=(empty($arTasks) && empty($arEvents) && empty($arDocumentState["TEMPLATE_PARAMETERS"])? "bizproc-item-row-last" : "")?>">
			<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_STATE")?>:</td>
			<td class="bizproc-field-value">
				<?=(strlen($arDocumentState["STATE_TITLE"]) > 0 ? $arDocumentState["STATE_TITLE"] : $arDocumentState["STATE_NAME"])?>
			</td>
		</tr>
		<?endif;?>

		<?if (strlen($arDocumentState["ID"]) <= 0)
		{
			$iCountRow++;
			CBPDocument::StartWorkflowParametersShow(
				$arDocumentState["TEMPLATE_ID"],
				$arDocumentState["TEMPLATE_PARAMETERS"],
				"start_workflow_form1",
				(!empty($arResult["ERROR_MESSAGE"]))
			);
		}
		?>
		<?

		if (count($arEvents) > 0)
		{
			$bShowButtons = true;
			$iCountRow++;
		?>
		<tr class="<?=($iCountRow == 1 ? "bizproc-item-row-first" : "")?> <?
			?><?=(empty($arTasks) ? "bizproc-item-row-last" : "")?>">
			<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_RUN_CMD")?>:</td>
			<td class="bizproc-field-value">
				<input type="hidden" name="bizproc_id_<?= $bizProcIndex ?>" value="<?= $arDocumentState["ID"] ?>">
				<input type="hidden" name="bizproc_template_id_<?= $bizProcIndex ?>" value="<?= $arDocumentState["TEMPLATE_ID"] ?>">
				<select name="bizproc_event_<?= $bizProcIndex ?>">
					<option value=""><?=GetMessage("IBEL_BIZPROC_RUN_CMD_NO")?></option>
					<?
					foreach ($arEvents as $e)
					{
					?><option value="<?= htmlspecialcharsbx($e["NAME"]) ?>"<?= ($_REQUEST["bizproc_event_".$bizProcIndex] == $e["NAME"]) ? " selected" : ""?>><?
						?><?= htmlspecialcharsbx($e["TITLE"]) ?></option><?
					}
					?>
				</select>
			</td>
		</tr>
		<?
		}
		if (strlen($arDocumentState["ID"]) > 0)
		{
			if (count($arTasks) > 0)
			{
				$iCountRow++;
		?>
		<tr class="<?=($iCountRow == 1 ? "bizproc-item-row-first" : "")?> bizproc-item-row-last">
			<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_TASKS")?>:</td>
			<td class="bizproc-field-value">
				<?
				foreach ($arTasks as $arTask)
				{
					$url = CComponentEngine::MakePathFromTemplate($arParams["~TASK_EDIT_URL"], array("ID" => $arTask["ID"]));
					$url .= (strpos($url, "?") === false ? "?" : "&")."back_url=".urlencode(!empty($arParams["~back_url"]) ? $arParams["~back_url"] : $APPLICATION->GetCurPageParam("", array()));
					?><a href="<?=$url?>" title="<?= strip_tags($arTask["DESCRIPTION"]) ?>"><?= $arTask["NAME"] ?></a><br /><?
				}
				?>
			</td>
		</tr>
		<?
			}
		}
?>
	</tbody>
</table>
	</li>
<?
}
if ($bEmpty):
?><?
elseif ($bShowButtons):
?>

	<li class="bizproc-item-buttons">
		<div class="bizproc-item-buttons">
			<table class="bizproc-table-main" cellpadding="0" border="0">
				<tr>
					<td style="text-align:center;">
						<input type="hidden" name="bizproc_index" value="<?= $bizProcIndex ?>" />
						<input type="submit" name="save" value="<?=GetMessage("IBEL_BIZPROC_SAVE")?>" />
						<input type="submit" name="update" value="<?=GetMessage("IBEL_BIZPROC_APPLY")?>" />
						<?
						if (!empty($arParams["~back_url"])):
						?>
						<input type="submit" name="cancel" value="<?=GetMessage("IBEL_BIZPROC_CANCEL")?>" />
						<?
						endif;
						?>
					</td>
				</tr>
			</table>
		</div>
	</li>
<?
endif;
?>
</ul>
</form>
</div>