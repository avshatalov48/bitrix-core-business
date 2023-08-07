<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<tr>
	<td align="right" width="40%"><?= GetMessage("BPHEEA_PD_USERS") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'permission', $arCurrentValues["permission"], Array())?>
	</td>
</tr>

<?if ($allowSetStatus):?>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPHEEA_PD_SET_STATE") ?>:</td>
	<td width="60%">
		<select name="setstate">
		<option value="">(<?= GetMessage("BPHEEA_PD_NOT_SET") ?>)</option>
		<?foreach ($arStates as $id => $val):?>
			<option value="<?= htmlspecialcharsbx($id) ?>"<?= ($arCurrentValues["setstate"] == $id ? " selected" : "")?>><?= htmlspecialcharsbx($val) ?></option>
		<?endforeach?>
		</select>
	</td>
</tr>
<?endif?>
