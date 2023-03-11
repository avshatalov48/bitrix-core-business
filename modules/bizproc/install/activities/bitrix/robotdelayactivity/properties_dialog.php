<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$parentDialog = $dialog->getRuntimeData()['parentDialog'];

echo $parentDialog;

foreach ($dialog->getMap() as $fieldId => $field): ?>
	<tr>
		<td align="right" width="40%"><?= htmlspecialcharsbx($field['Name']) ?>:</td>
		<td width="60%">
			<?= $dialog->renderFieldControl(
				$field,
				null,
				!empty($field['AllowSelection']),
				\Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER
			) ?>
		</td>
	</tr>
<?php endforeach;
