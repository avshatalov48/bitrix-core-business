<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPTA1A_TASK_TYPE") ?>:</span></td>
	<td width="60%">
		<select name="task_type" onchange="document.getElementById('id_task_owner_id').disabled = (this.selectedIndex == 0);">
			<option value="user"<?= $arCurrentValues["task_type"] == "user" ? " selected" : "" ?>><?= GetMessage("BPTA1A_TASK_TYPE_U") ?></option>
			<option value="group"<?= $arCurrentValues["task_type"] != "user" ? " selected" : "" ?>><?= GetMessage("BPTA1A_TASK_TYPE_G") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPTA1A_TASKOWNERID") ?>:</span></td>
	<td width="60%">
		<select name="task_owner_id" id="id_task_owner_id" style="width:400px">
			<?
			foreach ($arGroups as $key => $value)
			{
				?><option value="<?= $key ?>"<?= $arCurrentValues["task_owner_id"] == $key ? " selected" : "" ?>><?= $value ?></option><?
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPTA1A_TASKCREATEDBY") ?>:</span></td>
	<td width="60%">
		<input type="text" name="task_created_by" id="id_task_created_by" value="<?= htmlspecialcharsbx($arCurrentValues["task_created_by"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_task_created_by', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPTA1A_TASKASSIGNEDTO") ?>:</span></td>
	<td width="60%">
		<input type="text" name="task_assigned_to" id="id_task_assigned_to" value="<?= htmlspecialcharsbx($arCurrentValues["task_assigned_to"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_task_assigned_to', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPTA1A_TASKACTIVEFROM") ?>:</td>
	<td width="60%">
		<span style="white-space:nowrap;"><input type="text" name="task_active_from" id="id_task_active_from" size="30" value="<?= htmlspecialcharsbx($arCurrentValues["task_active_from"]) ?>"><?= CAdminCalendar::Calendar("task_active_from", "", "", true) ?></span>
		<input type="button" value="..." onclick="BPAShowSelector('id_task_active_from', 'datetime');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPTA1A_TASKACTIVETO") ?>:</td>
	<td width="60%">
		<span style="white-space:nowrap;"><input type="text" name="task_active_to" id="id_task_active_to" size="30" value="<?= htmlspecialcharsbx($arCurrentValues["task_active_to"]) ?>"><?= CAdminCalendar::Calendar("task_active_to", "", "", true) ?></span>
		<input type="button" value="..." onclick="BPAShowSelector('id_task_active_to', 'datetime');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPTA1A_TASKNAME") ?>:</span></td>
	<td width="60%">
		<input type="text" name="task_name" id="id_task_name" value="<?= htmlspecialcharsbx($arCurrentValues["task_name"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_task_name', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPTA1A_TASKDETAILTEXT") ?>:</td>
	<td width="60%">
		<textarea name="task_detail_text" id="id_task_detail_text" rows="7" cols="40"><?= htmlspecialcharsbx($arCurrentValues["task_detail_text"]) ?></textarea>
		<input type="button" value="..." onclick="BPAShowSelector('id_task_detail_text', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPTA1A_TASKPRIORITY") ?>:</td>
	<td width="60%">
		<select name="task_priority">
			<?
			foreach ($arTaskPriority as $key => $value)
			{
				?><option value="<?= $key ?>"<?= $arCurrentValues["task_priority"] == $key ? " selected" : "" ?>><?= $value ?></option><?
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPTA1A_TASKTRACKERS") ?>:</td>
	<td width="60%">
		<input type="text" name="task_trackers" id="id_task_trackers" value="<?= htmlspecialcharsbx($arCurrentValues["task_trackers"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_task_trackers', 'user');">
	</td>
</tr>
<?
if (count($arForums) > 0)
{
	?>
	<tr>
		<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPTA1A_TASKFORUM") ?>:</span></td>
		<td width="60%">
			<select name="task_forum_id" id="id_task_forum_id" style="width:400px">
				<?
				foreach ($arForums as $key => $value)
				{
					?><option value="<?= $key ?>"<?= $arCurrentValues["task_forum_id"] == $key ? " selected" : "" ?>><?= $value ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<?
}
?>