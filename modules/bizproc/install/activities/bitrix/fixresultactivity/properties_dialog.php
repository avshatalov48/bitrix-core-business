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
Main\UI\Extension::load(['bizproc.mixed-selector']);

Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/fixresultactivity/script.js'));

/** @var PropertiesDialog $dialog */
$chosenResultType = (int)$dialog->getCurrentValue('result_type', 0);
$chosenAccessType = (int)$dialog->getCurrentValue('access_type', 0);
$chosenResultValue = $dialog->getCurrentValue('result_fields_value');
$chosenAccessValue = $dialog->getCurrentValue('access_fields_value');

$resultTypeField = $dialog->getMap()['ResultType'];
$accessTypeField = $dialog->getMap()['AccessType'];
$resultFieldsMap = [];
$accessFieldsMap = [];

foreach ($dialog->getMap()['ResultFields']['Map'] as $resultType => $fieldsMap)
{
	$resultFieldsMap[$resultType] = [
		'documentType' => $dialog->getDocumentType(),
		'fieldsMap' => $fieldsMap,
	];
}

foreach ($dialog->getMap()['AccessFields']['Map'] as $accessType => $fieldsMap)
{
	$accessFieldsMap[$accessType] = [
		'documentType' => $dialog->getDocumentType(),
		'fieldsMap' => $fieldsMap,
	];
}

$activityFilter = [
		'createdocumentactivity',
		'createcrmcompanydocumentactivity',
		'createcrmleaddocumentactivity',
		'createcrmdealdocumentactivity',
		'createcrmcontactdocumentactivity',
		'createlistsdocumentactivity',
		'crmcreatedynamicactivity',
		'task2activity',
	];

?>
<tr>
	<td align="right" width="25%"><?=htmlspecialcharsbx($resultTypeField['Name'])?></td>
	<td width="75%">
		<?=
		$dialog->getFieldTypeObject($resultTypeField)->renderControl(
			[
				'Form' => $dialog->getFormName(),
				'Field' => $resultTypeField['FieldName']
			],
			$dialog->getCurrentValue($resultTypeField['FieldName']),
			false,
			0
		)
		?>
	</td>
</tr>
<tbody id="result-fields-container"></tbody>
<tr>
	<td align="right" width="25%"><?=htmlspecialcharsbx($accessTypeField['Name'])?></td>
	<td width="75%">
		<?=
		$dialog->getFieldTypeObject($accessTypeField)->renderControl(
			[
				'Form' => $dialog->getFormName(),
				'Field' => $accessTypeField['FieldName']
			],
			$dialog->getCurrentValue($accessTypeField['FieldName']),
			false,
			0
		)
		?>
	</td>
</tr>
<tbody id="access-fields-container"></tbody>
<script>
	BX.Event.ready(() => {
		new BX.Bizproc.Activity.FixResultActivity({
			formName: '<?= CUtil::JSEscape($dialog->getFormName()) ?>',
			resultFieldsMap: <?= \Bitrix\Main\Web\Json::encode($resultFieldsMap) ?>,
			accessFieldsMap: <?= \Bitrix\Main\Web\Json::encode($accessFieldsMap) ?>,
			currentResultValues: <?= \Bitrix\Main\Web\Json::encode($chosenResultValue) ?>,
			currentAccessValues: <?= \Bitrix\Main\Web\Json::encode($chosenAccessValue) ?>,
			objectTabs: {
				Document: window.arDocumentFields ?? [],
				Activity: arAllActivities ?? []
			},
			template: [rootActivity.Serialize()],
			activityFilter: <?= \Bitrix\Main\Web\Json::encode($activityFilter) ?>,
		}).init();
	})
</script>