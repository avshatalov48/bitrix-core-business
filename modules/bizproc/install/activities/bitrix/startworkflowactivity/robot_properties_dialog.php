<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load(['ui.entity-selector']);
\Bitrix\Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/startworkflowactivity/script.js'));

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

function propertiesDialogToJsObject(\Bitrix\Bizproc\Activity\PropertiesDialog $dialog)
{
	return CUtil::PhpToJSObject(array(
		'documentType' => $dialog->getDocumentType(),
		'activityName' => $dialog->getActivityName(),
		'formName' => $dialog->getFormName(),
		'siteId' => $dialog->getSiteId()
	));
}

$map = $dialog->getMap();
$runtimeData = $dialog->getRuntimeData();
$isAdmin = $runtimeData['isAdmin'] ?? false;
?>

<?php if($isAdmin): ?>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title"><?= Loc::getMessage('BPSWFA_RPD_DOCUMENT_ID') ?>:</span>
		<?= $dialog->renderFieldControl($map['DOCUMENT_ID'], $runtimeData['documentId'])?>
	</div>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title"><?= Loc::getMessage('BPSWFA_RPD_TEMPLATE')?>: </span>
		<div data-role="start-workflow-activity-template-selector"></div>
		<input
			type="hidden"
			name="template_id"
			value="<?= htmlspecialcharsbx($runtimeData['currentTemplateId']) ?>"
		/>
	</div>
	<div class="bizproc-automation-popup-settings">
		<input
			type="checkbox"
			value="Y"
			name="use_subscription"
			<?php if ($runtimeData['useSubscription'] == 'Y') echo 'checked'?>
		/>
		<?= Loc::getMessage('BPSWFA_RPD_USE_SUBSCRIPTION') ?>
	</div>
	<div id="bpswfa_template">
		<?= $dialog-> getRuntimeData()['templateParametersRender'] ?>
	</div>
	<script>
		BX.ready(() => {
			new BX.Bizproc.Activity.StartWorkflowActivity({
				templateNode: document.querySelector('[data-role="start-workflow-activity-template-selector"]'),
				templateInput: document.getElementsByName('template_id')[0],
				templateId: '<?= CUtil::JSEscape($runtimeData['currentTemplateId']) ?>',
				parametersNode: document.getElementById('bpswfa_template'),
				documentType: <?= CUtil::PhpToJSObject($runtimeData['documentType']) ?>,
				formName: '<?= CUtil::JSEscape($runtimeData['formName']) ?>',
				propertiesDialog: <?= propertiesDialogToJsObject($dialog) ?>,
				isRobot: true,
			}).init();
		});
	</script>
<?php else: ?>
	<div class="bizproc-automation-popup-settings-alert">
		<?= Loc::getMessage('BPSWFA_RPD_ACCESS_DENIED_1') ?>
	</div>
<?php endif;
