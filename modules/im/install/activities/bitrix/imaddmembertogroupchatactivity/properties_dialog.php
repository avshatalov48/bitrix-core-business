<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
foreach ($dialog->getMap() as $property): ?>
	<tr>
		<td align="right" width="40%">
			<?php if ($property['Required']): ?><span class="adm-required-field"><?php endif ?>
				<?= htmlspecialcharsbx($property['Name']) ?>:
			<?php if ($property['Required']): ?></span><?php endif ?>
		</td>
		<td width="60%">
			<?= $dialog->renderFieldControl(
				$property,
				null,
				true,
				\Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER
			) ?>
		</td>
	</tr>
<?php
endforeach;
?>
