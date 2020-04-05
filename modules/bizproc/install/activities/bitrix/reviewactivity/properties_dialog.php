<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPAR_PD_REVIEWERS") ?>:</span></td>
	<td width="60%"><?=CBPDocument::ShowParameterField("user", 'review_users', $arCurrentValues['review_users'], Array('rows'=>'2'))?></td>
</tr>
<tr>
	<td align="right"><span class="adm-required-field"><?= GetMessage("BPAR_PD_NAME") ?>:</span></td>
	<td><?=CBPDocument::ShowParameterField("string", 'review_name', $arCurrentValues['review_name'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPAR_PD_DESCR") ?>:</td>
	<td valign="top"><?=CBPDocument::ShowParameterField("text", 'review_description', $arCurrentValues['review_description'], Array('rows'=>'7'))?></td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPAR_PD_APPROVE_TYPE") ?>:</td>
	<td width="60%">
		<select name="approve_type">
			<option value="all"<?= $arCurrentValues["approve_type"] == "all" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_APPROVE_TYPE_ALL") ?></option>
			<option value="any"<?= $arCurrentValues["approve_type"] == "any" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_APPROVE_TYPE_ANY") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_SET_STATUS_MESSAGE") ?>:</td>
	<td>
		<select name="set_status_message">
			<option value="Y"<?= $arCurrentValues["set_status_message"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_YES") ?></option>
			<option value="N"<?= $arCurrentValues["set_status_message"] == "N" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_NO") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_STATUS_MESSAGE") ?>:<br/><?= GetMessage("BPAR_PD_STATUS_MESSAGE_HINT1") ?></td>
	<td><?=CBPDocument::ShowParameterField("string", 'status_message', $arCurrentValues['status_message'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_TASK_BUTTON_MESSAGE") ?>:</td>
	<td><?=CBPDocument::ShowParameterField("string", 'task_button_message', $arCurrentValues['task_button_message'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_SHOW_COMMENT") ?>:</td>
	<td>
		<select name="show_comment">
			<option value="Y"<?= $arCurrentValues["show_comment"] != "N" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_YES") ?></option>
			<option value="N"<?= $arCurrentValues["show_comment"] == "N" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_NO") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_COMMENT_REQUIRED") ?>:</td>
	<td>
		<select name="comment_required">
			<option value="N"><?= GetMessage("BPAR_PD_NO") ?></option>
			<option value="Y"<?= $arCurrentValues["comment_required"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_YES") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_COMMENT_LABEL_MESSAGE") ?>:</td>
	<td><?=CBPDocument::ShowParameterField("string", 'comment_label_message', $arCurrentValues['comment_label_message'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_TIMEOUT_DURATION") ?>:<br/><?= GetMessage("BPAR_PD_TIMEOUT_DURATION_HINT") ?></td>
	<td>
		<?=CBPDocument::ShowParameterField('int', 'timeout_duration', $arCurrentValues["timeout_duration"], array('size' => 20))?>
		<select name="timeout_duration_type">
			<option value="s"<?= ($arCurrentValues["timeout_duration_type"] == "s") ? " selected" : "" ?>><?= GetMessage("BPAR_PD_TIME_S") ?></option>
			<option value="m"<?= ($arCurrentValues["timeout_duration_type"] == "m") ? " selected" : "" ?>><?= GetMessage("BPAR_PD_TIME_M") ?></option>
			<option value="h"<?= ($arCurrentValues["timeout_duration_type"] == "h") ? " selected" : "" ?>><?= GetMessage("BPAR_PD_TIME_H") ?></option>
			<option value="d"<?= ($arCurrentValues["timeout_duration_type"] == "d") ? " selected" : "" ?>><?= GetMessage("BPAR_PD_TIME_D") ?></option>
		</select>
		<?
		$delayMinLimit = CBPSchedulerService::getDelayMinLimit();
		if ($delayMinLimit):
			?>
			<p style="color: red;">* <?= GetMessage("BPAR_PD_TIMEOUT_LIMIT") ?>: <?=CBPHelper::FormatTimePeriod($delayMinLimit)?></p>
			<?
		endif;
		?>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_ACCESS_CONTROL") ?>:</td>
	<td>
		<select name="access_control">
			<option value="Y"<?= $arCurrentValues["access_control"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_YES") ?></option>
			<option value="N"<?= $arCurrentValues["access_control"] != "Y" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_NO") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_DELEGATION_TYPE") ?>:</td>
	<td>
		<select name="delegation_type">
			<?foreach (CBPTaskDelegationType::getSelectList() as $key => $label):?>
				<option value="<?=htmlspecialcharsbx($key)?>>"<?= $arCurrentValues["delegation_type"] == $key ? " selected" : "" ?>><?=htmlspecialcharsbx($label)?></option>
			<?endforeach;?>
		</select>
	</td>
</tr>