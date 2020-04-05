<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists("__bp_sort_in_template_by_modified"))
{
	function __bp_sort_in_template_by_modified($arr1, $arr2)
	{
		if ($arr1["STATE_MODIFIED"] == $arr2["STATE_MODIFIED"])
			return 0;
		elseif (strlen($arr1["STATE_MODIFIED"]) <= 0 && strlen($arr1["STATE_MODIFIED"]) > 0)
			return -1;
		elseif (strlen($arr1["STATE_MODIFIED"]) > 0 && strlen($arr1["STATE_MODIFIED"]) <= 0)
			return 1;
		$res1 = MakeTimeStamp($arr1["STATE_MODIFIED"]);
		$res2 = MakeTimeStamp($arr2["STATE_MODIFIED"]);

		return ($res1 < $res2) ? 1 : -1;
	}
}

if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;

CBPDocument::AddShowParameterInit($arParams["MODULE_ID"], "only_users", $arParams["DOCUMENT_TYPE"]);

$bizProcIndex = 0;
$bEmpty = true;
$bShowButtons = false;

$arDocumentStates = CBPDocument::GetDocumentStates(
	$arParams["DOCUMENT_TYPE"],
	$arParams["DOCUMENT_ID"]);
$arGroups = CBPDocument::GetAllowableUserGroups($arParams["DOCUMENT_TYPE"]);
$arUsers = array();
uasort($arDocumentStates, "__bp_sort_in_template_by_modified");
?>
<div class="bizproc-page-document">

<form action="<?=POST_FORM_ACTION_URI?>" method="POST" class="bizproc-form" name="start_workflow_form1" id="start_workflow_form1">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($arParams["back_url"]);?>" />

<ul class="bizproc-list bizproc-document-states">
<?
$iCount = 0;
if ($arParams["StartWorkflowPermission"] == "Y"):
	$bEmpty = false;
	$iCount++;
	$url = CComponentEngine::MakePathFromTemplate($arParams["WORKFLOW_START_URL"],
					array("MODULE_ID" => $arParams["DOCUMENT_ID"][0], "ENTITY" => $arParams["DOCUMENT_ID"][1],
						"DOCUMENT_ID" => $arParams["DOCUMENT_ID"][2], "DOCUMENT_TYPE" => $arParams["DOCUMENT_TYPE"][2],
						"ID" => $arParams["DOCUMENT_ID"][2]));
	$url .= (strpos($url, "?") === false ? "?" : "&")."back_url=".
		urlencode(!empty($arParams["back_url"]) ? $arParams["back_url"] : $APPLICATION->GetCurPageParam("", array("back_url")));


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

	if (intVal($arDocumentState["WORKFLOW_STATUS"]) < 0 || $arDocumentState["ID"] <= 0):
		continue;
	elseif (!CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::ViewWorkflow,
		$GLOBALS["USER"]->GetID(),
		$arParams["DOCUMENT_ID"],
		array(
			"DocumentStates" => $arDocumentStates,
			"WorkflowId" => $arDocumentState["ID"]))):
		continue;
	endif;
	$arTasks = array();
	$arDumpWorkflow = array();
	$arTasks = CBPDocument::GetUserTasksForWorkflow($USER->GetID(), $arDocumentState["ID"]);
	if (strlen($arDocumentState["WORKFLOW_STATUS"]) > 0)
	{
		$dbDmpWorkflow = CBPTrackingService::GetList(
			array("ID" => "DESC"),
			array("WORKFLOW_ID" => $arDocumentState["ID"], "TYPE" => array(CBPTrackingType::Report, CBPTrackingType::Custom, CBPTrackingType::FaultActivity)),
			false,
			array("nTopCount" => 5),
			array("ID", "TYPE", "MODIFIED", "ACTION_NOTE", "ACTION_TITLE", "ACTION_NAME", "EXECUTION_STATUS", "EXECUTION_RESULT")
		);
		while ($track = $dbDmpWorkflow->GetNext())
		{
			$strMessageTemplate = "";
			switch ($track["TYPE"])
			{
				case 1:
					$strMessageTemplate = GetMessage("BPABL_TYPE_1");
					break;
				case 2:
					$strMessageTemplate = GetMessage("BPABL_TYPE_2");
					break;
				case 3:
					$strMessageTemplate = GetMessage("BPABL_TYPE_3");
					break;
				case 4:
					$strMessageTemplate = GetMessage("BPABL_TYPE_4");
					break;
				case 5:
					$strMessageTemplate = GetMessage("BPABL_TYPE_5");
					break;
				default:
					$strMessageTemplate = GetMessage("BPABL_TYPE_6");
			}

			$name = (strlen($track["ACTION_TITLE"]) > 0 ? $track["ACTION_TITLE"] : $track["ACTION_NAME"]);

			switch ($track["EXECUTION_STATUS"])
			{
				case CBPActivityExecutionStatus::Initialized:
					$status = GetMessage("BPABL_STATUS_1");
					break;
				case CBPActivityExecutionStatus::Executing:
					$status = GetMessage("BPABL_STATUS_2");
					break;
				case CBPActivityExecutionStatus::Canceling:
					$status = GetMessage("BPABL_STATUS_3");
					break;
				case CBPActivityExecutionStatus::Closed:
					$status = GetMessage("BPABL_STATUS_4");
					break;
				case CBPActivityExecutionStatus::Faulting:
					$status = GetMessage("BPABL_STATUS_5");
					break;
				default:
					$status = GetMessage("BPABL_STATUS_6");
			}

			switch ($track["EXECUTION_RESULT"])
			{
				case CBPActivityExecutionResult::None:
					$result = GetMessage("BPABL_RES_1");
					break;
				case CBPActivityExecutionResult::Succeeded:
					$result = GetMessage("BPABL_RES_2");
					break;
				case CBPActivityExecutionResult::Canceled:
					$result = GetMessage("BPABL_RES_3");
					break;
				case CBPActivityExecutionResult::Faulted:
					$result = GetMessage("BPABL_RES_4");
					break;
				case CBPActivityExecutionResult::Uninitialized:
					$result = GetMessage("BPABL_RES_5");
					break;
				default:
					$status = GetMessage("BPABL_RES_6");
			}

			$note = ((strlen($track["ACTION_NOTE"]) > 0) ? ": ".$track["ACTION_NOTE"] : "");
			$arPattern = array("#ACTIVITY#", "#STATUS#", "#RESULT#", "#NOTE#");
			$arReplace = array($name, $status, $result, $note);
			if (!empty($track["ACTION_NAME"]) && !empty($track["ACTION_TITLE"])):
				$arPattern[] = $track["ACTION_NAME"];
				$arReplace[] = $track["ACTION_TITLE"];
			endif;
			$strMessageTemplate = str_replace(
					$arPattern,
					$arReplace,
					$strMessageTemplate);

			if (preg_match_all("/(?<=\{\=user\:)([^\}]+)(?=\})/is", $strMessageTemplate, $arMatches))
			{
				$arPattern = array(); $arReplacement = array();
				foreach ($arMatches[0] as $user)
				{
					if (in_array("{=user:".$user."}", $arPattern))
						continue;
					$replace = "";
					if (array_key_exists(strtolower($user), $arGroups))
						$replace = $arGroups[strtolower($user)];
					elseif (array_key_exists(strtoupper($user), $arGroups))
						$replace = $arGroups[strtoupper($user)];
					else
					{
						$id = intVal(str_replace("user_", "", $user));
						if (!array_key_exists($id, $arUsers)):
							$db_res = CUser::GetByID($id);
							$arUsers[$id] = false;
							if ($db_res && $arUser = $db_res->GetNext()):
								$name = trim($arUser["NAME"]." ".$arUser["LAST_NAME"]);
								$arUser["FULL_NAME"] = (empty($name) ? $arUser["LOGIN"] : $name);
								$arUsers[$id] = $arUser;
							endif;
						endif;
						if (!empty($arUsers[$id]))
							$replace = "<a href=\"".
								CComponentEngine::MakePathFromTemplate($arParams["~USER_VIEW_URL"], array("USER_ID" => $id))."\">".
								$arUsers[$id]["FULL_NAME"]."</a>";
					}

					if (!empty($replace))
					{
						$arPattern[] = "{=user:".$user."}";
						$arPattern[] = "{=user:user_".$user."}";
						$arReplacement[] = $replace;
						$arReplacement[] = $replace;
					}
				}
				$strMessageTemplate = str_replace($arPattern, $arReplacement, $strMessageTemplate);
			}

			$arDumpWorkflow[] = $strMessageTemplate;
		}
	}
	$arEvents = CBPDocument::GetAllowableEvents($GLOBALS["USER"]->GetID(), $arParams["USER_GROUPS"], $arDocumentState);

	$bEmpty = false;
	$iCount++;
	$iCountRow = 0;

?>
	<li class="bizproc-list-item bizproc-document-process <?=(strlen($arDocumentState["WORKFLOW_STATUS"]) > 0 ?
				"bizproc-document-inprogress" :
				"bizproc-document-finished")?> <?=(empty($arTasks) ? "" :
				"bizproc-document-hastasks")
				?> <?
				?><?=($iCount == 1 ? "bizproc-list-item-first" : "")?> <?
				?><?=($iCount%2 == 1 ? "bizproc-list-item-odd " : "bizproc-list-item-even ")?>">
<table class="bizproc-table-main" cellpadding="0" border="0">
	<thead>
		<tr>
			<th colspan="2">
				<div class="bizproc-document-controls">
				<?
					$tmp = false;
					if (strlen($arDocumentState["WORKFLOW_STATUS"]) > 0):
						$tmp = true;?>
					<span class="bizproc-document-control-first">
						<a href="<?=$APPLICATION->GetCurPageParam("id=".$arDocumentState["ID"]."&action=stop_bizproc&".bitrix_sessid_get().
						(!empty($arParams["back_url"]) ? "&back_url=".urlencode($arParams["back_url"]) : ""),
						array("id", "action", "sessid", "back_url"))?>"><?=GetMessage("IBEL_BIZPROC_STOP")?></a></span>
					<?elseif ($arParams["DropWorkflowPermission"] == "Y"):
						$tmp = true;?>
					<span class="bizproc-document-control-first">
						<a href="<?=$APPLICATION->GetCurPageParam("id=".$arDocumentState["ID"]."&action=del_bizproc&".bitrix_sessid_get().
						(!empty($arParams["back_url"]) ? "&back_url=".urlencode($arParams["back_url"]) : ""),
						array("id", "action", "sessid", "back_url"))?>"><?=GetMessage("IBEL_BIZPROC_DEL")?></a></span>
					<?endif;?>
					<span class="<?=($tmp ? "bizproc-document-control-second" : "bizproc-document-control-single")?>">
						<a href="<?=CComponentEngine::MakePathFromTemplate($arParams["WORKFLOW_LOG_URL"],
						array("MODULE_ID" => $arParams["DOCUMENT_ID"][0], "ENTITY" => $arParams["DOCUMENT_ID"][1],
							"DOCUMENT_ID" => $arParams["DOCUMENT_ID"][2], "DOCUMENT_TYPE" => $arParams["DOCUMENT_TYPE"][2],
							"ID" => $arDocumentState["ID"], "STATE_ID" => $arDocumentState["ID"]))?>"><?=GetMessage("IBEL_BIZPROC_LOG")?></a></span>
				</div>
				<?=$arDocumentState["TEMPLATE_NAME"]?>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr class="bizproc-item-row-first">
			<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_DATE")?>:</td>
			<td class="bizproc-field-value"><?= $arDocumentState["STATE_MODIFIED"] ?></td>
		</tr>
		<tr class="<?=(empty($arTasks) && empty($arEvents)&& empty($arDumpWorkflow)? "bizproc-item-row-last" : "")?>">
			<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_STATE")?>:</td>
			<td class="bizproc-field-value">
				<?=(strlen($arDocumentState["STATE_TITLE"]) > 0 ? $arDocumentState["STATE_TITLE"] : $arDocumentState["STATE_NAME"])?>
			</td>
		</tr>
		<?

		if (!empty($arDumpWorkflow)):
		?><tr class="<?=(empty($arTasks) && empty($arEvents)? "bizproc-item-row-last" : "")?>">
			<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_MODIFICATION")?>:</td>
			<td class="bizproc-field-value">
				<?=implode("<br />", $arDumpWorkflow)?></pre><??>
			</td>
		</tr>
		<?endif;

		if (count($arEvents) > 0)
		{
			$bShowButtons = true;
		?>
		<tr class="<?=(empty($arTasks) ? "bizproc-item-row-last" : "")?>">
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
					$url = CComponentEngine::MakePathFromTemplate($arParams["TASK_EDIT_URL"], array("ID" => $arTask["ID"]));
					$url .= (strpos($url, "?") === false ? "?" : "&")."back_url=".urlencode($APPLICATION->GetCurPageParam("", array()));
					?><a href="<?=$url?>" title="<?= htmlspecialcharsbx($arTask["DESCRIPTION"]) ?>"><?= $arTask["NAME"] ?></a><br /><?
				}
				?>
			</td>
		</tr>
		<?
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
						if (!empty($arParams["back_url"])):
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