<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}
	elseif(!empty($arResult["arTask"]))
	{
	?>
		<form method="post" name="task_form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="doTask">
			<input type="hidden" name="id" value="<?= intval($arResult["arTask"]["ID"]) ?>">
			<input type="hidden" name="workflow_id" value="<?= htmlspecialcharsbx($arResult["arTask"]["WORKFLOW_ID"]) ?>">
			<?= bitrix_sessid_post() ?>
			<table class="data-table">
				<tr>
					<td align="right" valign="top" width="40%"><?= GetMessage("BPAT_NAME") ?>:</td>
					<td width="60%" valign="top"><?= $arResult["arTask"]["NAME"] ?></td>
				</tr>
				<tr>
					<td align="right" valign="top" width="40%"><?= GetMessage("BPAT_DESCR") ?>:</td>
					<td width="60%" valign="top"><?= nl2br($arResult["arTask"]["DESCRIPTION"]) ?></td>
				</tr>
				<?if (strlen($arResult["arTask"]["PARAMETERS"]["DOCUMENT_URL"]) > 0):?>
				<tr>
					<td align="right" valign="top" width="40%">&nbsp;</td>
					<td width="60%" valign="top"><a href="<?= $arResult["arTask"]["PARAMETERS"]["DOCUMENT_URL"] ?>" target="_blank"><?= GetMessage("BPAT_GOTO_DOC") ?></a></td>
				</tr>
				<?endif;?>
				<?= $arResult["taskForm"]; ?>
				<tr>
					<td colspan="2" >
						<?= $arResult["taskFormButtons"] ?>
					</td>
				</tr>
			</table>
			
		</form>
	<?
	}
	else
		echo ShowError(GetMessage("BPAT_TASK_LOST"));
	
}
?>