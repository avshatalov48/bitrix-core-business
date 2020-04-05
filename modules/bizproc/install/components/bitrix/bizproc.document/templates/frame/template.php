<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
CJSCore::Init('bp_starter');

if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;

$arDocumentStates = $arResult["DOCUMENT_STATES"];

?>
<div class="bizproc-page-document" data-role="bizproc-document-base">
<?if ($arParams["StartWorkflowPermission"] == "Y"):?>
	<div>
		<span class="webform-small-button webform-small-button-blue" data-role="start-button">
			<span class="webform-small-button-text">
				<?=GetMessage("IBEL_BIZPROC_START")?>
			</span>
		</span>
	</div>
<?endif;?>
<form action="" method="POST" data-role="form">
	<?=bitrix_sessid_post()?>
<ul class="bizproc-document-list bizproc-document-workflow-list-item" data-role="workflows-list">
<?
$workflows = array();

foreach ($arDocumentStates as $arDocumentState)
{
	if (intVal($arDocumentState["WORKFLOW_STATUS"]) < 0):
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

	$arTasks = CBPDocument::GetUserTasksForWorkflow($USER->GetID(), $arDocumentState["ID"]);
	$arEvents = CBPDocument::GetAllowableEvents($USER->GetID(), $arParams["USER_GROUPS"], $arDocumentState);

	$arDocumentState['EVENTS'] = $arEvents;
	$arDocumentState['TASKS'] = $arTasks;
	$arDocumentState['STATE_MODIFIED_FORMATTED'] = FormatDateFromDB($arDocumentState["STATE_MODIFIED"]);

	$workflows[] = $arDocumentState;
}
?>
</ul>
	<div class="bizproc-document-toolbar-bottom" data-role="events-apply-container">
		<span class="webform-small-button webform-small-button-accept" data-role="events-apply-button">
			<span class="webform-small-button-text">
				<?=GetMessage("IBEL_BIZPROC_APPLY")?>
			</span>
		</span>
	</div>
</form>
	<div hidden data-role="templates">
		<li data-template="workflow" class="bizproc-list-item bizproc-document-process"
			data-class-finished="bizproc-document-finished"
			data-class-tasks="bizproc-document-hastasks"
			data-role="workflow-node" data-workflow-id=""
		>
			<table class="bizproc-table-main" cellpadding="0" border="0">
				<thead>
				<tr>
					<th colspan="2">
						<div class="bizproc-document-workflow-toolbar">
							<span class="bizproc-document-control" data-role="terminate-container">
								<a href="#" data-role="terminate"><?=GetMessage("IBEL_BIZPROC_STOP")?></a>
							</span>
							<span class="bizproc-document-control" data-role="kill-container">
								<a href="#" data-role="kill"><?=GetMessage("IBEL_BIZPROC_DEL")?></a>
							</span>
							<span class="bizproc-document-control">
								<a href="#" data-role="log"><?=GetMessage("IBEL_BIZPROC_LOG")?></a>
							</span>
						</div>
						<span data-role="workflow-name"></span>
					</th>
				</tr>
				</thead>
				<tbody>
					<tr>
						<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_DATE")?>:</td>
						<td class="bizproc-field-value" data-role="workflow-modified"></td>
					</tr>
					<tr>
						<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_STATE")?>:</td>
						<td class="bizproc-field-value" data-role="workflow-state"></td>
					</tr>
					<tr data-role="events-row">
						<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_RUN_CMD")?>:</td>
						<td class="bizproc-field-value">
							<select data-role="events-select">
								<option value=""><?=GetMessage("IBEL_BIZPROC_RUN_CMD_NO")?></option>
							</select>
						</td>
					</tr>
					<tr data-role="tasks-row">
						<td class="bizproc-field-name"><?=GetMessage("IBEL_BIZPROC_TASKS")?>:</td>
						<td class="bizproc-field-value">
							<ul class="bizproc-field-value-tasks" data-role="tasks-container">

							</ul>
						</td>
					</tr>
				</tbody>
			</table>
		</li>
	</div>
</div>
<script>
	BX.ready(function() {
		BX.message({
			IBEL_BIZPROC_LOG_TITLE: '<?=GetMessageJS('IBEL_BIZPROC_LOG_TITLE')?>'
		});

		var baseNode = document.querySelector('[data-role="bizproc-document-base"]');
		var config = <?=\Bitrix\Main\Web\Json::encode(array(
			'serviceUrl' => '/bitrix/components/bitrix/bizproc.document/ajax.php',
			'moduleId' => $arParams["DOCUMENT_ID"][0],
			'entity' => $arParams["DOCUMENT_ID"][1],
			'documentId' => $arParams["DOCUMENT_ID"][2],
			'documentType' => $arParams["DOCUMENT_TYPE"][2],
			'canTerminate' => $arParams["StartWorkflowPermission"] == "Y",
			'canKill' => $arParams["DropWorkflowPermission"] == "Y",
		))?>;
		var workflows = <?=\Bitrix\Main\Web\Json::encode($workflows)?>;

		if (baseNode)
		{
			var component = new BX.Bizproc.DocumentComponent(baseNode, config);
			component.init();
			component.renderWorkflows(workflows);
		}
	});
</script>