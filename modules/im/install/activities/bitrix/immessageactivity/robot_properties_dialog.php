<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;

Main\UI\Extension::load([
	'ui.entity-selector',
	'main.popup',
	'ui.design-tokens',
	'ui.forms',
	'im.robot.message-template-selector',
]);

CJSCore::Init('bp_field_type');
Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/immessageactivity/script.js'));

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
$messageTemplate = $map['MessageTemplate'];
unset($map['MessageTemplate']);

foreach ($map as $property): ?>
	<?php if (isset($property['Type'])): ?>
		<div class="bizproc-automation-popup-settings">
			<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
				<?= htmlspecialcharsbx($property['Name']) ?>:
			</span>
			<?= $dialog->renderFieldControl($property) ?>
		</div>
	<?php endif; ?>
<?php
endforeach;
?>
<div class="bizproc-automation-popup-settings bizproc-automation-popup-settings-flex">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-left">
		<?= htmlspecialcharsbx($messageTemplate['Name']) ?>:
	</span>
	<div class="bizproc-automation-popup-settings-link" data-role="message-type" ></div>
	<input
		type="hidden"
		name="<?= htmlspecialcharsbx($messageTemplate['FieldName']) ?>"
		value="<?= htmlspecialcharsbx($dialog->getCurrentValue($messageTemplate)) ?>"
	>
</div>

<div id="id_<?= htmlspecialcharsbx($map['MessageFields']['FieldName']) ?>"></div>

<script>
	BX.ready(() => {
		const formName = '<?= CUtil::JSEscape($dialog->getFormName()) ?>';

		const activity = new BX.Im.Activity.ImMessageActivity({
			form: document.forms[formName],
			isRobot: true,
			documentType: <?= Main\Web\Json::encode($dialog->getDocumentType()) ?>,
			currentValues: <?= Main\Web\Json::encode($dialog->getCurrentValues()) ?>,
			chatFieldName: '<?= CUtil::JSEscape($map['ChatId']['FieldName']) ?>',
			messageTemplateFields: <?= Main\Web\Json::encode($map['MessageFields']['Map']) ?>,
			messageTemplateList: <?= Main\Web\Json::encode($messageTemplate['Options']) ?>,
		});
		activity.init();
	});
</script>