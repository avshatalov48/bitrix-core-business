<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('socialnetwork');
CUtil::InitJSCore(array('socnetlogdest'));
\Bitrix\Main\Localization\Loc::loadMessages(__DIR__.DIRECTORY_SEPARATOR.'script.js.php');
?>
<div class="bizproc-page-workflow-start">
<?
if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;

if ($arResult["SHOW_MODE"] == "StartWorkflowSuccess")
{
	ShowNote(str_replace("#TEMPLATE#", $arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["NAME"], GetMessage("BPABS_MESSAGE_SUCCESS")));
}
elseif ($arResult["SHOW_MODE"] == "StartWorkflowError")
{
	ShowNote(str_replace("#TEMPLATE#", $arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["NAME"], GetMessage("BPABS_MESSAGE_ERROR")));
}
elseif ($arResult["SHOW_MODE"] == "WorkflowParameters")
{
	/** @var CBPDocumentService $documentService */
	$documentService = $arResult["DocumentService"];

?>
<form method="post" name="start_workflow_form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" data-role="bizproc-start-form">
	<input type="hidden" name="workflow_template_id" value="<?=intval($arParams["TEMPLATE_ID"]) ?>" />
	<input type="hidden" name="document_type" value="<?= htmlspecialcharsbx($arParams["DOCUMENT_TYPE"][2]) ?>" />
	<input type="hidden" name="document_id" value="<?= htmlspecialcharsbx($arParams["DOCUMENT_ID"][2]) ?>" />
	<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($arResult["back_url"]) ?>" />
	<?= bitrix_sessid_post() ?>
<fieldset class="bizproc-item bizproc-workflow-template">
	<legend class="bizproc-item-legend bizproc-workflow-template-title">
		<?=$arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["NAME"]?>
	</legend>
	<?if($arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["DESCRIPTION"]!=''):?>
	<div class="bizproc-item-description bizproc-workflow-template-description">
		<?= $arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["DESCRIPTION"] ?>
	</div>
	<?endif;

	if (!empty($arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["PARAMETERS"]))
	{
		foreach ($arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["PARAMETERS"] as $parameterKey => $arParameter)
		{
			if ($parameterKey == "TargetUser")
			{
				continue;
			}
?>
			<div class="bizproc-modern-type-control-container">
				<span class="bizproc-modern-type-control-container-title bizproc-modern-type-control-container-title-top"
				<? if ($arParameter["Description"]):?> title="<?=htmlspecialcharsbx($arParameter["Description"])?>"<?endif;?>>
					<?=htmlspecialcharsbx($arParameter['Name'])?><?=($arParameter["Required"] ? "<span class=\"required\">*</span> " : "")?>:
				</span>
				<div class="bizproc-modern-type-control-wrapper">
				<?
				echo $documentService->GetFieldInputControl(
					$arParams["DOCUMENT_TYPE"],
					$arParameter,
					array(
						"Form" => "start_workflow_form1",
						"Field" => $parameterKey,
					),
					$arResult["PARAMETERS_VALUES"][$parameterKey] ?? null,
					false,
					true
				);
				?>
				</div>
			</div>
<?
		}
	}
?>
	<div class="bizproc-item-buttons bizproc-workflow-start-buttons" data-role="bizproc-form-buttons">
		<input type="submit" name="DoStartParamWorkflow" value="<?= GetMessage("BPABS_DO_START") ?>" />
		<input type="submit" name="CancelStartParamWorkflow" value="<?= GetMessage("BPABS_DO_CANCEL") ?>" />
	</div>
</fieldset>
</form>
<script>
	BX.ready(function()
	{
		BX.message({
			BP_WS_DESTINATION_CHOOSE: '<?=GetMessageJS('BP_WS_DESTINATION_CHOOSE')?>',
			BP_WS_DESTINATION_EDIT: '<?=GetMessageJS('BP_WS_DESTINATION_EDIT')?>',
			BP_WS_FILE_CHOOSE: '<?=GetMessageJS('BP_WS_FILE_CHOOSE')?>',
			BP_WS_CONTROL_CLONE: '<?=GetMessageJS('BP_WS_CONTROL_CLONE')?>'
		});
	});
</script>
<?
}
elseif ($arResult["SHOW_MODE"] == "SelectWorkflow" && count($arResult["TEMPLATES"]) > 0)
{
	foreach($_GET as $key => $val):
		if (in_array(mb_strtolower($key), array("sessid", "workflow_template_id")))
			continue;
	endforeach;
	$bFirst = true;
?>
	<ul class="bizproc-list bizproc-workflow-templates">
		<?foreach ($arResult["TEMPLATES"] as $workflowTemplateId => $arWorkflowTemplate):?>
			<li class="bizproc-list-item bizproc-workflow-template">
				<div class="bizproc-item-title">
					<a href="<?=$arResult["TEMPLATES"][$arWorkflowTemplate["ID"]]["URL"]?>"><?=$arWorkflowTemplate["NAME"]?></a>
				</div>
				<?if ($arWorkflowTemplate["DESCRIPTION"] <> ''):?>
				<div class="bizproc-item-description">
					<?= $arWorkflowTemplate["DESCRIPTION"] ?>
				</div>
				<?endif;?>
			</li>
		<?endforeach;?>
	</ul>
<?
}
elseif ($arResult["SHOW_MODE"] == "SelectWorkflow")
{
	ShowNote(GetMessage("BPABS_NO_TEMPLATES"));
}
?>
</div>
