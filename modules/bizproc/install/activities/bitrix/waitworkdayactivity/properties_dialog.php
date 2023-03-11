<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();

$userParam = $map['TargetUser'];

?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= $userParam['Name'] ?></span>:</td>
	<td width="60%">
		<?= $dialog->renderFieldControl(
			$userParam,
			null,
			true,
			\Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER
		); ?>
	</td>
</tr>