<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (
	empty($arCurrentValues['condition_type'])
	|| !array_key_exists($arCurrentValues['condition_type'], $arActivities)
)
{
	$arCurrentValues['condition_type'] = $firstConditionType;
}
?>
<tbody>
	<tr>
		<td align="right" width="40%"><?= GetMessage('BPWA_PD_TYPE') ?>:</td>
		<td width="60%">
			<select name="condition_type" onchange="WhileActivity.changeConditionTypeHandler(this)">
				<?php foreach ($arActivities as $key => $value): ?>
					<option
							data-id="id_bwfiba_type_<?= $key ?>"
							value="<?= $key ?>"
							<?= ($arCurrentValues['condition_type'] == $key) ? ' selected' : '' ?>
					><?= $value["NAME"] ?></option>
				<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?foreach ($arActivities as $key => $value):?>
				<table id="id_bwfiba_type_<?= $key ?>" style="display:<?= ($arCurrentValues['condition_type'] == $key) ? '' : 'none' ?>" width="100%">
					<?=$arActivities[$key]['PROPERTIES_DIALOG']?>
				</table>
			<?endforeach;?>
		</td>
	</tr>
</tbody>
