<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

foreach ($arAllowableOperations as $operationKey => $operationValue)
{
	?>
	<tr>
		<td align="right" width="40%"><?= str_replace("#OP#", $operationValue, GetMessage("BPSA_PD_PERM")) ?>:</td>
		<td width="60%">
			<?= CBPDocument::ShowParameterField("user", 'permission_'.$operationKey, $arCurrentValues["permission_".$operationKey] ?? null) ?>
		</td>
	</tr>
	<?
}
if (!empty($isExtendedPermsSupported)):

	$permMode = (int)($arCurrentValues["perm_mode"] ?? CBPSetPermissionsMode::Clear);
	$permScope = (int)($arCurrentValues["perm_scope"] ?? CBPSetPermissionsMode::ScopeWorkflow);

?>
<tr>
	<td align="right" width="40%" valign="top"><?=GetMessage("BPSA_PD_PERM_CURRENT_LABEL")?>:</td>
	<td width="60%">
		<label><input type="radio" name="perm_mode" value="<?= CBPSetPermissionsMode::Hold ?>"<?= ($permMode === CBPSetPermissionsMode::Hold) ? " checked" : "" ?>><?= GetMessage("BPSA_PD_PERM_HOLD") ?></label><br/>
		<label><input type="radio" name="perm_mode" value="<?= CBPSetPermissionsMode::Rewrite ?>"<?= ($permMode === CBPSetPermissionsMode::Rewrite) ? " checked" : "" ?>><?= GetMessage("BPSA_PD_PERM_REWRITE") ?></label><br/>
		<label><input type="radio" name="perm_mode" value="<?= CBPSetPermissionsMode::Clear ?>"<?= ($permMode === CBPSetPermissionsMode::Clear) ? " checked" : "" ?>><?= GetMessage("BPSA_PD_PERM_CLEAR") ?></label>
	</td>
</tr>

<tr>
	<td align="right" width="40%" valign="top"><?=GetMessage("BPSA_PD_PERM_SCOPE_LABEL")?>:</td>
	<td width="60%">
		<label><input type="radio" name="perm_scope" value="<?=CBPSetPermissionsMode::ScopeWorkflow?>"<?= ($permScope === CBPSetPermissionsMode::ScopeWorkflow) ? " checked" : "" ?>><?= GetMessage("BPSA_PD_PERM_SCOPE_WORFLOW") ?></label><br/>
		<label><input type="radio" name="perm_scope" value="<?=CBPSetPermissionsMode::ScopeDocument?>"<?= ($permScope === CBPSetPermissionsMode::ScopeDocument) ? " checked" : "" ?>><?= GetMessage("BPSA_PD_PERM_SCOPE_DOCUMENT") ?></label>
	</td>
</tr>
<?php
endif;
