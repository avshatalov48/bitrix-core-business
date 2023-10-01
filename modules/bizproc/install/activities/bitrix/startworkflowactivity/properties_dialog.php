<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load(['ui.entity-selector']);
\Bitrix\Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/startworkflowactivity/script.js'));

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
$runtimeData = $dialog->getRuntimeData();
$isAdmin = $runtimeData['isAdmin'] ?? false;

if ($isAdmin): ?>
	<tr>
		<td align="right" width="40%" valign="top">
			<span class="adm-required-field"><?= Loc::getMessage('BPSWFA_PD_DOCUMENT_ID') ?>:</span>
		</td>
		<td width="60%">
			<?= $dialog->renderFieldControl(
				$map['DOCUMENT_ID'],
				$runtimeData['documentId'],
				true,
				\Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER
			) ?>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%">
			<span class="adm-required-field"><?= Loc::getMessage('BPSWFA_PD_TEMPLATE') ?>:</span>
		</td>
		<td width="60%">
			<div data-role="start-workflow-activity-template-selector"></div>
			<input
				type="hidden"
				name="template_id"
				value="<?= (int)$runtimeData['currentTemplateId'] ?>"
			/>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%"></td>
		<td width="60%">
			<label>
				<input
					type="checkbox"
					value="Y"
					name="use_subscription"
					<?php if ($runtimeData['useSubscription'] == 'Y') echo 'checked' ?>
				/>
				<?= Loc::getMessage('BPSWFA_PD_USE_SUBSCRIPTION') ?>
			</label>
		</td>
	</tr>
	<tbody id="bpswfa_template">
		<?= $runtimeData['templateParametersRender'] ?>
	</tbody>
	<script>
		BX.ready(() => {
			new BX.Bizproc.Activity.StartWorkflowActivity({
				templateNode: document.querySelector('[data-role="start-workflow-activity-template-selector"]'),
				templateInput: document.getElementsByName('template_id')[0],
				templateId: '<?= CUtil::JSEscape($runtimeData['currentTemplateId']) ?>',
				parametersNode: document.getElementById('bpswfa_template'),
				documentType: <?= CUtil::PhpToJSObject($runtimeData['documentType']) ?>,
				formName: '<?= CUtil::JSEscape($runtimeData['formName']) ?>',
				isRobot: false,
			}).init();
		});
	</script>
<?php else:?>
<tr>
	<td align="right" width="40%" valign="top" colspan="2" style="color: red">
		<?= Loc::getMessage('BPSWFA_PD_ACCESS_DENIED_1') ?>
	</td>
</tr>
<?php endif;
