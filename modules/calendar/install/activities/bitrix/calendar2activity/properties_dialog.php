<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%"><span><?= GetMessage("BPSNMA_PD_CALENDAR_TYPE") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'calendar_type', $arCurrentValues['calendar_type'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span><?= GetMessage("BPSNMA_PD_CALENDAR_OWNER") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'calendar_owner_id', $arCurrentValues['calendar_owner_id'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span><?= GetMessage("BPSNMA_PD_CALENDAR_SECTION") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'calendar_section', $arCurrentValues['calendar_section'], Array('size'=> 50))?>
	</td>
</tr>

<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_CUSER") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField('user', 'calendar_user', $arCurrentValues['calendar_user'], Array('rows' => 1))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_CNAME") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'calendar_name', $arCurrentValues['calendar_name'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"> <?= GetMessage("BPSNMA_PD_CDESCR") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'calendar_desrc', $arCurrentValues['calendar_desrc'], ['rows'=> 7, 'cols' => 40])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSNMA_PD_CFROM") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("datetime", 'calendar_from', $arCurrentValues['calendar_from'])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span><?= GetMessage("BPSNMA_PD_CTO") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("datetime", 'calendar_to', $arCurrentValues['calendar_to'])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span><?= GetMessage("BPSNMA_PD_TIMEZONE") ?>:</span></td>
	<td width="60%">
		<select name="calendar_timezone" id="id_calendar_timezone">
			<option value=""> - </option>
			<?
			if (is_array($timezoneList))
			{
				foreach($timezoneList as $tz)
				{?>
					<option value="<?= $tz['timezone_id'] ?>"
						<? if($tz['timezone_id'] == $arCurrentValues["calendar_timezone"])
						{
							echo 'selected';
						} ?>><?= htmlspecialcharsEx($tz['title']) ?></option>
				<?}
			}
			?>
		</select>
	</td>
</tr>