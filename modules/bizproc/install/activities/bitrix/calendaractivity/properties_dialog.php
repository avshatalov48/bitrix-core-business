<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_CUSER") ?>:</span></td>
	<td width="60%">
		<input type="text" name="calendar_user" id="id_calendar_user" value="<?= htmlspecialcharsbx($arCurrentValues["calendar_user"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_calendar_user', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_CNAME") ?>:</span></td>
	<td width="60%">
		<input type="text" name="calendar_name" id="id_calendar_name" value="<?= htmlspecialcharsbx($arCurrentValues["calendar_name"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_calendar_name', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"> <?= GetMessage("BPSNMA_PD_CDESCR") ?>:</td>
	<td width="60%">
		<textarea name="calendar_desrc" id="id_calendar_desrc" rows="7" cols="40"><?= htmlspecialcharsbx($arCurrentValues["calendar_desrc"]) ?></textarea>
		<input style="vertical-align: top" type="button" value="..." onclick="BPAShowSelector('id_calendar_desrc', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_CFROM") ?>:</span></td>
	<td width="60%">
		<span style="white-space:nowrap;"><input type="text" name="calendar_from" id="id_calendar_from" size="30" value="<?= htmlspecialcharsbx($arCurrentValues["calendar_from"]) ?>"><?= CAdminCalendar::Calendar("calendar_from", "", "", true) ?></span>
		<input type="button" value="..." onclick="BPAShowSelector('id_calendar_from', 'datetime');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_CTO") ?>:</span></td>
	<td width="60%">
		<span style="white-space:nowrap;"><input type="text" name="calendar_to" id="id_calendar_to" size="30" value="<?= htmlspecialcharsbx($arCurrentValues["calendar_to"]) ?>"><?= CAdminCalendar::Calendar("calendar_to", "", "", true) ?></span>
		<input type="button" value="..." onclick="BPAShowSelector('id_calendar_to', 'datetime');">
	</td>
</tr>