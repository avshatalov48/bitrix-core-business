<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["FatalErrorMessage"] <> '')
{
	?>
	<span class='errortext'><?= $arResult["FatalErrorMessage"] ?></span><br /><br />
	<?
}
else
{
	if ($arResult["ErrorMessage"] <> '')
	{
		?>
		<span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br /><br />
		<?
	}
	$arButtons = array(
		array(
			"TEXT"=>GetMessage("BPWC_WVCT_2LIST"),
			"TITLE"=>GetMessage("BPWC_WVCT_2LIST"),
			"LINK"=>$arResult["PATH_TO_LIST"],
			"ICON"=>"btn-list",
		),
	);
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arButtons
		),
		$component
	);
	?>
	<br>

	<form name="bizprocform" method="post" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data">
		<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($arResult["BackUrl"]) ?>">
		<?=bitrix_sessid_post()?>

		<table class="bpwiz1-view-form data-table" cellpadding="0" cellspacing="0">
		<tr>
			<th colspan="2"><?= GetMessage("BPWC_WVCT_SUBTITLE") ?></th>
		</tr>
		<?
		foreach ($arResult["WorkflowVariables"] as $parameterKey => $arParameter)
		{
			?>
			<tr>
				<td align="right" width="40%" valign="top"><?= $arParameter["Required"] ? "<span style=\"color:red\">*</span> " : ""?><?= htmlspecialcharsbx($arParameter["Name"]) ?>:<?if ($arParameter["Description"] <> '') echo "<br /><small>".htmlspecialcharsbx($arParameter["Description"])."</small><br />";?></td>
				<td width="60%" valign="top"><?
					echo $arResult["DocumentService"]->GetFieldInputControl(
						array("bizproc", "CBPVirtualDocument", "type_".$arResult["Block"]["ID"]),
						$arParameter,
						array("Form" => "bizprocform", "Field" => $parameterKey),
						$arParameter["Default"],
						false,
						true
					);
				?></td>
			</tr>
			<?
		}
		if (count($arResult["WorkflowVariables"]) <= 0)
		{
			?>
			<tr><td><?= GetMessage("BPWC_WVCT_EMPTY") ?></td></tr>
			<?
		}
		?>
		</table>
		<br><br>

		<input type="submit" name="save_variables" value="<?= GetMessage("BPWC_WVCT_SAVE") ?>">
		<input type="submit" name="apply_variables" value="<?= GetMessage("BPWC_WVCT_APPLY") ?>">
		<input type="submit" name="cancel_variables"  value="<?= GetMessage("BPWC_WVCT_CANCEL") ?>">
	</form>
	<?
}
?>