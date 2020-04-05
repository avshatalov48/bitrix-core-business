<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNAA2_PD_CUSER") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField('user', 'absence_user', $arCurrentValues['absence_user'], Array('rows' => 1))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNAA2_PD_CNAME") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'absence_name', $arCurrentValues['absence_name'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"> <?= GetMessage("BPSNAA2_PD_CDESCR") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'absence_desrc', $arCurrentValues['absence_desrc'], Array('rows'=> 7))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNAA2_PD_CFROM") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("datetime", 'absence_from', $arCurrentValues['absence_from'])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNAA2_PD_CTO") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("datetime", 'absence_to', $arCurrentValues['absence_to'])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNAA2_PD_CTYPES") ?>:</span></td>
	<td width="60%">
		<select name="absence_type" id="id_absence_type">
			<?
			foreach ($arAbsenceTypes as $key => $value)
			{
				?><option value="<?= $key ?>"<?= ($key == $arCurrentValues["absence_type"]) ? " selected" : "" ?>><?= $value ?></option><?
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSNAA2_PD_CSTATE") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'absence_state', $arCurrentValues['absence_state'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSNAA2_PD_CFSTATE") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'absence_finish_state', $arCurrentValues['absence_finish_state'], Array('size'=> 50))?>
		<input type="hidden" name="absence_site_id" value="<?= $absenceSiteId ?>">
	</td>
</tr>
