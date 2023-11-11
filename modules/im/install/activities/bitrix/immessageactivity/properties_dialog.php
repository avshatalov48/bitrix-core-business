<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;

Main\UI\Extension::load(['ui.entity-selector', 'im.robot.message-template-selector']);
CJSCore::Init('bp_field_type');
Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/immessageactivity/script.js'));

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();

foreach ($map as $property): ?>
	<?php
	$allowSelection = true;
	if (is_array($property['Settings'] ?? null))
	{
		$allowSelection = $property['Settings']['AllowSelection'] ?? true;
	}
	?>
	<?php if (isset($property['Type'])): ?>
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
					$allowSelection,
					\Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER
				) ?>
			</td>
		</tr>
	<?php endif; ?>
<?php endforeach; ?>

<tr>
	<td colspan="2">
		<table id="id_<?= htmlspecialcharsbx($map['MessageFields']['FieldName']) ?>">
		</table>
	</td>
</tr>

<script>
	BX.ready(() => {
		const formName = '<?= CUtil::JSEscape($dialog->getFormName()) ?>';

		const activity = new BX.Im.Activity.ImMessageActivity({
			form: document.forms[formName],
			isRobot: false,
			documentType: <?= Main\Web\Json::encode($dialog->getDocumentType()) ?>,
			currentValues: <?= Main\Web\Json::encode($dialog->getCurrentValues()) ?>,
			chatFieldName: '<?= CUtil::JSEscape($map['ChatId']['FieldName']) ?>',
			messageTemplateFields: <?= Main\Web\Json::encode($map['MessageFields']['Map']) ?>,
		});
		activity.init();
	});
</script>
