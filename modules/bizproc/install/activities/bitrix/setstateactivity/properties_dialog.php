<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arCurrentValues = $arCurrentValues ?? [];

?>

<tr>
	<td align="right" width="40%"><?= GetMessage('BPSFA_PD_STATE') ?>:</td>
	<td width="60%">
		<select name="target_state_name_1">
			<option value=""><?= GetMessage('BPSFA_PD_OTHER') ?></option>
			<?php
			$fl = false;
			foreach ($arStates as $key => $value)
			{
				if (isset($arCurrentValues['target_state_name']) && $key == $arCurrentValues['target_state_name'])
				{
					$fl = true;
				}
				?><option
					value="<?= htmlspecialcharsbx($key) ?>"
					<?= (isset($arCurrentValues['target_state_name']) && $key == $arCurrentValues['target_state_name']) ? ' selected' : '' ?>
				><?= $value ?></option><?php
			}
			?>
		</select><br />
		<?= CBPDocument::ShowParameterField(
				'string',
				'target_state_name',
				!$fl ? ($arCurrentValues['target_state_name'] ?? null) : '',
				['size' => 30]
		) ?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"></td>
	<td width="60%">
		<label>
			<input type="checkbox" value="Y" name="cancel_current_state" <?php
				if (isset($arCurrentValues['cancel_current_state']) && $arCurrentValues['cancel_current_state'] == 'Y') echo 'checked'?>/>
			<?= GetMessage('BPSSA_CANCEL_CURRENT_STATE') ?>
		</label>
	</td>
</tr>