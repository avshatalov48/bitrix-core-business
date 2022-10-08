<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.buttons',
]);

?>
<div class="bp-setparameters">
<?
if ($arResult["FatalErrorMessage"] <> '')
{
	?>
	<span class="bp-question"><span>!</span><?= htmlspecialcharsbx($arResult["FatalErrorMessage"]) ?></span>
	<?
}
else
{
	if ($arResult["ErrorMessage"] <> '')
	{
		?>
		<span class="bp-question"><span>!</span><?= htmlspecialcharsbx($arResult["ErrorMessage"]) ?></span>
		<?
	}
	if ($arResult['DESCRIPTION'])
	{
		?>
		<p><?= nl2br(htmlspecialcharsbx($arResult['DESCRIPTION'])) ?></p>
		<?
	}
	?>

	<form name="bizprocform" method="post" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($arResult["BackUrl"]) ?>">
		<input type="hidden" name="ID" value="<?= $arParams["ID"] ?>">
		<input type="hidden" name="save_action" value="Y">
		<?
		foreach ($arResult["PARAMETERS"] as $parameterKey => $arParameter)
		{
			?>
			<span class="bp-question-title"><?= htmlspecialcharsbx($arParameter["Name"]) ?>:</span>
			<?if ($arParameter["Description"] <> ''):?>
			<p class="hint"><?=htmlspecialcharsbx($arParameter["Description"])?></p>
			<?endif?>
			<div class="bp-question-item"><?
				echo $arResult["DocumentService"]->GetFieldInputControl(
					$arResult["DOCUMENT_TYPE"],
					$arParameter,
					array("Form" => "bizprocform", "Field" => $parameterKey),
					$arParameter["Default"],
					false,
					true
				);
				?>
			</div>
			<div class="bp-question-divider"></div>
			<?
		}
		if (count($arResult["PARAMETERS"]) <= 0)
		{
			?>
			<span class="bp-question"><span>!</span><?= GetMessage("BPWFSPT_EMPTY") ?></span>
			<?
		}
		?>
		<?if ($arResult["PARAMETERS"]):?>
		<div class="bp-question-item">
			<input type="submit" value="<?= GetMessage("BPWFSPT_SAVE") ?>" class="ui-btn ui-btn-success">
		</div>
		<?endif?>
	</form>
	<?
}
?>
</div>