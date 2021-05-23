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

	/** @var CBPDocumentService $documentService */
	$documentService = $arResult["DocumentService"];
?>
	<form method="post" name="start_workflow_form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" data-role="bizproc-start-form">
		<?= bitrix_sessid_post() ?>
		<input type="hidden" name="site" value="<?= SITE_ID ?>">
		<input type="hidden" name="document_type" value="<?= htmlspecialcharsbx($arParams["DOCUMENT_TYPE"][2]) ?>">
		<input type="hidden" name="auto_execute_type" value="<?= htmlspecialcharsbx($arResult["EXEC_TYPE"]) ?>">
		<?foreach ($arResult['TEMPLATES'] as $template):?>
		<fieldset class="bizproc-item bizproc-workflow-template bizproc-workflow-template-parameters">
			<legend class="bizproc-item-legend bizproc-workflow-template-title">
				<?= htmlspecialcharsbx($template["NAME"])?>
			</legend>
			<?if($template["DESCRIPTION"]!=''):?>
				<div class="bizproc-item-description bizproc-workflow-template-description">
					<?= htmlspecialcharsbx($template["DESCRIPTION"]) ?>
				</div>
			<?endif;
			if (!empty($template["PARAMETERS"]))
			{
				foreach ($template["PARAMETERS"] as $parameterKey => $arParameter)
				{
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
									$arParameter['Default'],
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
		</fieldset>
		<?endforeach;?>
	</form>
</div>
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