<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
foreach ($arAllowableOperations as $operationKey => $operationValue)
{
	?>
	<tr>
		<td align="right" width="40%"><?= str_replace("#OP#", $operationValue, GetMessage("BPSA_PD_PERM")) ?>:</td>
		<td width="60%">
			<?= CBPDocument::ShowParameterField(
				"user", 'permission_' . $operationKey, $arCurrentValues["permission_" . $operationKey] ?? null
			) ?>
		</td>
	</tr>
	<?
}
?>
<tr>
	<td align="right" width="40%" valign="top"><?=GetMessage("BPSA_PD_PERM_CURRENT_LABEL")?>:</td>
	<td width="60%">
		<label><input type="radio" name="set_mode" value="<?=CBPSetPermissionsMode::Hold?>"<?= ($arCurrentValues["set_mode"] == CBPSetPermissionsMode::Hold) ? " checked" : "" ?>> <?= GetMessage("BPSA_PD_PERM_HOLD") ?></label><br/>
		<?if ($arCurrentValues['is_extended_mode']):?>
		<label><input type="radio" name="set_mode" value="<?=CBPSetPermissionsMode::Rewrite?>"<?= ($arCurrentValues["set_mode"] == CBPSetPermissionsMode::Rewrite) ? " checked" : "" ?>> <?= GetMessage("BPSA_PD_PERM_REWRITE") ?></label><br/>
		<?endif?>
		<label><input type="radio" name="set_mode" value="<?=CBPSetPermissionsMode::Clear?>"<?= ($arCurrentValues["set_mode"] == CBPSetPermissionsMode::Clear) ? " checked" : "" ?>> <?= GetMessage("BPSA_PD_PERM_CLEAR") ?></label>
	</td>
</tr>
<?if ($arCurrentValues['is_extended_mode']):?>
<tr>
	<td align="right" width="40%" valign="top"><?=GetMessage("BPSA_PD_PERM_SCOPE_LABEL")?>:</td>
	<td width="60%">
		<label><input type="radio" name="set_scope" value="<?=CBPSetPermissionsMode::ScopeWorkflow?>"<?= ($arCurrentValues["set_scope"] == CBPSetPermissionsMode::ScopeWorkflow) ? " checked" : "" ?>> <?= GetMessage("BPSA_PD_PERM_SCOPE_WORFLOW") ?></label><br/>
		<label><input type="radio" name="set_scope" value="<?=CBPSetPermissionsMode::ScopeDocument?>"<?= ($arCurrentValues["set_scope"] == CBPSetPermissionsMode::ScopeDocument) ? " checked" : "" ?>> <?= GetMessage("BPSA_PD_PERM_SCOPE_DOCUMENT") ?></label>
	</td>
</tr>
<?endif?>