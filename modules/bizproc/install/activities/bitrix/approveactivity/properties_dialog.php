<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPAA_PD_APPROVERS") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'approve_users', $arCurrentValues['approve_users'], Array('rows'=>'2'))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPAA_PD_TYPE") ?>:</span></td>
	<td width="60%">
		<script>
		function __BPAppTCh(v)
		{
			if(v=='vote')
			{
				try{
					document.getElementById("appvprc1").style.display = 'table-row';
				}catch(e){
					document.getElementById("appvprc1").style.display = 'block';
				}
				try{
					document.getElementById("appvprc2").style.display = 'table-row';
				}catch(e){
					document.getElementById("appvprc2").style.display = 'block';
				}
			}
			else
			{
				document.getElementById("appvprc1").style.display = 'none';
				document.getElementById("appvprc2").style.display = 'none';
			}
		}
		</script>
		<select name="approve_type" onchange="__BPAppTCh(this.value)">
			<option value="all"<?= $arCurrentValues["approve_type"] == "all" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_TYPE_ALL") ?></option>
			<option value="any"<?= $arCurrentValues["approve_type"] == "any" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_TYPE_ANY") ?></option>
			<option value="vote"<?= $arCurrentValues["approve_type"] == "vote" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_TYPE_VOTE") ?></option>
		</select>
	</td>
</tr>
<tr id="appvprc1" <?=($arCurrentValues["approve_type"]!="vote"?" style='display:none'":"")?>>
	<td align="right" width="40%"><?= GetMessage("BPAA_PD_PERCENT") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("int", 'approve_percent', $arCurrentValues['approve_percent'], array("size"=>"5"))?>
	</td>
</tr>
<tr id="appvprc2" <?=($arCurrentValues["approve_type"]!="vote"?" style='display:none'":"")?>>
	<td align="right" width="40%"><?= GetMessage("BPAA_PD_WAIT") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("bool", 'approve_wait', $arCurrentValues['approve_wait'])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPAA_PD_NAME") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'approve_name', $arCurrentValues['approve_name'], Array('size'=>'50'))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPAA_PD_DESCR") ?>:</td>
	<td width="60%" valign="top">
		<?=CBPDocument::ShowParameterField("text", 'approve_description', $arCurrentValues['approve_description'], Array('rows'=>'7'))?>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAA_PD_SET_STATUS_MESSAGE") ?>:</td>
	<td>
		<select name="set_status_message">
			<option value="Y"<?= $arCurrentValues["set_status_message"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_YES") ?></option>
			<option value="N"<?= $arCurrentValues["set_status_message"] == "N" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_NO") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAA_PD_STATUS_MESSAGE") ?>:<br/><?= GetMessage("BPAA_PD_STATUS_MESSAGE_HINT1") ?></td>
	<td valign="top"><?=CBPDocument::ShowParameterField("string", 'status_message', $arCurrentValues['status_message'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_TASK_BUTTON1_MESSAGE") ?>:</td>
	<td><?=CBPDocument::ShowParameterField("string", 'task_button1_message', $arCurrentValues['task_button1_message'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_TASK_BUTTON2_MESSAGE") ?>:</td>
	<td><?=CBPDocument::ShowParameterField("string", 'task_button2_message', $arCurrentValues['task_button2_message'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_SHOW_COMMENT") ?>:</td>
	<td>
		<select name="show_comment">
			<option value="Y"<?= $arCurrentValues["show_comment"] != "N" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_YES") ?></option>
			<option value="N"<?= $arCurrentValues["show_comment"] == "N" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_NO") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_COMMENT_REQUIRED") ?>:</td>
	<td>
		<select name="comment_required">
			<option value="N"><?= GetMessage("BPAA_PD_NO") ?></option>
			<option value="Y"<?= $arCurrentValues["comment_required"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_YES") ?></option>
			<option value="YA"<?= $arCurrentValues["comment_required"] == "YA" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_COMMENT_REQUIRED_YA") ?></option>
			<option value="YR"<?= $arCurrentValues["comment_required"] == "YR" ? " selected" : "" ?>><?= GetMessage("BPAR_PD_COMMENT_REQUIRED_YR") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_COMMENT_LABEL_MESSAGE") ?>:</td>
	<td><?=CBPDocument::ShowParameterField("string", 'comment_label_message', $arCurrentValues['comment_label_message'], Array('size'=>'50'))?></td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAA_PD_TIMEOUT_DURATION") ?>:<br/><?= GetMessage("BPAA_PD_TIMEOUT_DURATION_HINT") ?></td>
	<td valign="top">
		<?=CBPDocument::ShowParameterField('int', 'timeout_duration', $arCurrentValues["timeout_duration"], array('size' => 20))?>
		<select name="timeout_duration_type">
			<option value="s"<?= ($arCurrentValues["timeout_duration_type"] == "s") ? " selected" : "" ?>><?= GetMessage("BPAA_PD_TIME_S") ?></option>
			<option value="m"<?= ($arCurrentValues["timeout_duration_type"] == "m") ? " selected" : "" ?>><?= GetMessage("BPAA_PD_TIME_M") ?></option>
			<option value="h"<?= ($arCurrentValues["timeout_duration_type"] == "h") ? " selected" : "" ?>><?= GetMessage("BPAA_PD_TIME_H") ?></option>
			<option value="d"<?= ($arCurrentValues["timeout_duration_type"] == "d") ? " selected" : "" ?>><?= GetMessage("BPAA_PD_TIME_D") ?></option>
		</select>
		<?
			$delayMinLimit = CBPSchedulerService::getDelayMinLimit();
			if ($delayMinLimit):
		?>
		<p style="color: red;">* <?= GetMessage("BPAA_PD_TIMEOUT_LIMIT") ?>: <?=CBPHelper::FormatTimePeriod($delayMinLimit)?></p>
		<?
			endif;
		?>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_ACCESS_CONTROL") ?>:</td>
	<td>
		<select name="access_control">
			<option value="Y"<?= $arCurrentValues["access_control"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_YES") ?></option>
			<option value="N"<?= $arCurrentValues["access_control"] != "Y" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_NO") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_DELEGATION_TYPE") ?>:</td>
	<td>
		<select name="delegation_type">
			<?foreach (CBPTaskDelegationType::getSelectList() as $key => $label):?>
			<option value="<?=htmlspecialcharsbx($key)?>"<?= $arCurrentValues["delegation_type"] == $key ? " selected" : "" ?>><?=htmlspecialcharsbx($label)?></option>
			<?endforeach;?>
		</select>
	</td>
</tr>
