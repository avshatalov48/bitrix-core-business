<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPCRU_PD_TYPE") ?>:</span></td>
	<td width="60%">
		<script type="text/javascript">
		function __BPCRUUserTypeChange(v)
		{
			if (v == 'boss')
			{
				document.getElementById("bpcrUserParameterTitle").innerHTML = "<?= GetMessage("BPCRU_PD_USER_BOSS") ?>";
				try{
					document.getElementById("tr_max_level").style.display = 'table-row';
				}catch(e){
					document.getElementById("tr_max_level").style.display = 'block';
				}
			}
			else
			{
				document.getElementById("bpcrUserParameterTitle").innerHTML = "<?= GetMessage("BPCRU_PD_USER_RANDOM") ?>";
				document.getElementById("tr_max_level").style.display = 'none';
			}
		}
		</script>
		<select name="user_type" onchange="__BPCRUUserTypeChange(this.value)">
			<option value="random"<?= ($arCurrentValues['user_type'] == "random") ? " selected" : "" ?>><?= GetMessage("BPCRU_PD_TYPE_RANDOM") ?></option>
			<option value="boss"<?= ($arCurrentValues['user_type'] == "boss") ? " selected" : "" ?>><?= GetMessage("BPCRU_PD_TYPE_BOSS") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field" id="bpcrUserParameterTitle"><?= GetMessage("BPCRU_PD_USER_RANDOM") ?></span>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'user_parameter', $arCurrentValues['user_parameter'], Array('rows'=>'2'))?>
	</td>
</tr>
<tr id="tr_max_level">
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPCRU_PD_MAX_LEVEL") ?>:</span></td>
	<td width="60%">
		<select name="max_level">
			<?for ($i = 1; $i < 11; $i++):?>
			<option value="<?= $i ?>"<?= ($arCurrentValues['max_level'] == $i) ? " selected" : "" ?>><?= ($i == 1) ? GetMessage("BPCRU_PD_MAX_LEVEL_1") : $i ?></option>
			<?endfor;?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPCRU_PD_USER1") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'reserve_user_parameter', $arCurrentValues['reserve_user_parameter'], Array('rows'=>'2'))?>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPCRU_PD_SKIP_ABSENT") ?>:</td>
	<td>
		<select name="skip_absent">
			<option value="Y"<?= $arCurrentValues["skip_absent"] != "N" ? " selected" : "" ?>><?= GetMessage("BPCRU_PD_YES") ?></option>
			<option value="N"<?= $arCurrentValues["skip_absent"] == "N" ? " selected" : "" ?>><?= GetMessage("BPCRU_PD_NO") ?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPCRU_PD_SKIP_TIMEMAN") ?>:</td>
	<td>
		<select name="skip_timeman">
			<option value="N"<?= $arCurrentValues["skip_timeman"] != "Y" ? " selected" : "" ?>><?= GetMessage("BPCRU_PD_NO") ?></option>
			<option value="Y"<?= $arCurrentValues["skip_timeman"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPCRU_PD_YES") ?></option>
		</select>
	</td>
</tr>
<script type="text/javascript">
__BPCRUUserTypeChange('<?= $arCurrentValues['user_type'] ?>');
</script>