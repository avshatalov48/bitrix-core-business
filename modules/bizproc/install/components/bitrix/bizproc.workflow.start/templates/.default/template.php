<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');
CBPDocument::AddShowParameterInit($arParams["DOCUMENT_TYPE"][0], "only_users", $arParams["DOCUMENT_TYPE"][2], $arParams["DOCUMENT_TYPE"][1]);
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
?>
	<div class="bizproc-item-text">
		<ul class="bizproc-list bizproc-workflow-template-params">
<?
	foreach ($arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["PARAMETERS"] as $parameterKey => $arParameter)
	{
		if ($parameterKey == "TargetUser")
			continue;
?>
		<li class="bizproc-list-item bizproc-workflow-template-param">
			<div class="bizproc-field bizproc-field-<?=$arParameter["Type"]?>">
				<label class="bizproc-field-name">
					<?=($arParameter["Required"] ? "<span class=\"required\">*</span> " : "")?>
						<span class="bizproc-field-title"><?=htmlspecialcharsbx($arParameter["Name"])?></span><?
					if (strlen($arParameter["Description"]) > 0):
						?><span class="bizproc-field-description"> (<?=htmlspecialcharsbx($arParameter["Description"])?>)</span><?
					endif;
					?>:
				</label>
				<span class="bizproc-field-value"><?
					echo $documentService->GetFieldInputControl(
						$arParams["DOCUMENT_TYPE"],
						$arParameter,
						array("Form" => "start_workflow_form1", "Field" => $parameterKey),
						$arResult["PARAMETERS_VALUES"][$parameterKey],
						false,
						true
					);
				?></span>
			</div>
		</li>
<?
	}
?>
		</ul>
	</div>
<?
	}
?>
	<div class="bizproc-item-buttons bizproc-workflow-start-buttons">
		<input type="submit" name="DoStartParamWorkflow" value="<?= GetMessage("BPABS_DO_START") ?>" />
		<input type="submit" name="CancelStartParamWorkflow" value="<?= GetMessage("BPABS_DO_CANCEL") ?>" />
	</div>
</fieldset>
</form>
<?
}
elseif ($arResult["SHOW_MODE"] == "SelectWorkflow" && count($arResult["TEMPLATES"]) > 0)
{
	foreach($_GET as $key => $val):
		if (in_array(strtolower($key), array("sessid", "workflow_template_id")))
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
				<?if (strlen($arWorkflowTemplate["DESCRIPTION"]) > 0):?>
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