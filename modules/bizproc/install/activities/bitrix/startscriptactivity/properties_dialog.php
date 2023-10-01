<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Activity\PropertiesDialog;

Main\Loader::includeModule('ui');
Main\UI\Extension::load(['ui.entity-selector']);
Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/startscriptactivity/script.js'));

/** @var PropertiesDialog $dialog */
$map = $dialog->getMap();
$templateProperty = $map['TemplateId'];
unset($map['TemplateId']);

foreach ($map as $key => $property): ?>
	<tr>
		<td align="right" width="40%" valign="top">
			<span class="adm-required-field"><?= $property['Name'] ?>:</span>
		</td>
		<td width="60%">
			<?= $dialog->renderFieldControl($property, null, true, FieldType::RENDER_MODE_DESIGNER) ?>
		</td>
	</tr>
<?php endforeach; ?>
<tr>
	<td align="right" width="40%">
		<span class="adm-required-field"><?= $templateProperty['Name'] ?>:</span>
	</td>
	<td width="60%">
		<div data-role="start-script-activity-template-selector"></div>
		<input
			type="hidden"
			name="template_id"
			value="<?= (int)$dialog->getCurrentValue('template_id') ?: '' ?>"
		/>
	</td>
</tr>
<tbody id="bp_start_script_activity_template_parameters">
	<?= $dialog->getRuntimeData()['parametersForm'] ?>
</tbody>
<script>
	BX.Event.ready(() => {
		new BX.Bizproc.Activity.StartScriptActivity({
			templateNode: document.querySelector('[data-role="start-script-activity-template-selector"]'),
			templateInput: document.getElementsByName('template_id')[0],
			parametersNode: document.getElementById('bp_start_script_activity_template_parameters'),
			templateId: '<?= (int)$dialog->getCurrentValue('template_id') ?>',
			documentType: <?= CUtil::PhpToJSObject($dialog->getDocumentType()) ?>,
			formName: '<?= CUtil::JSEscape($dialog->getFormName()) ?>',
			isRobot: false,
		}).init();
	});
</script>