<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$dialog = $arResult['dialog'];

$data = $dialog->getRuntimeData();
$map = $dialog->getMap();
$activityData = $data['ACTIVITY_DATA'];

$documentService = CBPRuntime::GetRuntime()->getDocumentService();
$activityDocumentType = is_array($activityData['DOCUMENT_TYPE']) ? $activityData['DOCUMENT_TYPE'] : $dialog->getDocumentType();
$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
$currentValues = $dialog->getCurrentValues();

$appPlacement = $data['APP_PLACEMENT'];
$placementSid = null;
$appCurrentValues = [];

if ($appPlacement):

	foreach ($properties as $key => $property)
	{
		$appCurrentValues[$key] = $dialog->getCurrentValue('property_'.mb_strtolower($key));
	}

	echo '<div style="padding: 0 0 10px">';
	$placementSid = $APPLICATION->includeComponent(
		'bitrix:app.layout',
		'',
		array(
			'ID' => $appPlacement['APP_ID'],
			'PLACEMENT' => \Bitrix\Bizproc\RestService::PLACEMENT_ACTIVITY_PROPERTIES_DIALOG,
			'PLACEMENT_ID' => $appPlacement['ID'],
			"PLACEMENT_OPTIONS" => [
				'code' => $activityData['CODE'],
				'activity_name' => $dialog->getActivityName(),
				'properties' => $properties,
				'current_values' => $appCurrentValues,
				'document_type' => $dialog->getDocumentType(),
				'document_fields' => $documentService->GetDocumentFields($dialog->getDocumentType()),
				'template' => $dialog->getTemplateExpressions(),
			],
			'PARAM' => array(
				'FRAME_WIDTH' => '100%',
				'FRAME_HEIGHT' => '350px'
			),
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
	echo '</div>';
else:
	foreach ($properties as $id => $property):
		$name = $map[$id]['FieldName'];
		$title = \Bitrix\Bizproc\RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID);
	?>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"><?=htmlspecialcharsbx($title)?>: </span>
		<?
		echo $dialog->renderFieldControl($map[$id], $currentValues[$name]);
		?>
	</div>
	<?
	endforeach;
endif;

if ($data['IS_ADMIN']):?>

	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"><?=htmlspecialcharsbx($map['AuthUserId']['Name'])?>: </span>
		<?
		echo $dialog->renderFieldControl($map['AuthUserId']);
		?>
	</div>

<?php endif;

if ($placementSid):?>
<script>
	BX.ready(function()
	{
		var appLayout = BX.rest.AppLayout.get('<?=CUtil::JSEscape($placementSid)?>');
		var properties = <?=\Bitrix\Main\Web\Json::encode($properties)?>;
		var values = <?=\Bitrix\Main\Web\Json::encode($appCurrentValues)?>;
		var form = document.forms['<?=CUtil::JSEscape($dialog->getFormName())?>'];

		function setValueToForm(name, value)
		{
			name = 'property_' + name.toLowerCase();
			if (BX.type.isArray(value))
			{
				name += '[]';
			}
			else
			{
				value = [value];
			}

			Array.from(form.querySelectorAll('[name="'+name+'"]')).forEach(function(element)
			{
				BX.remove(element);
			});

			value.forEach(function(val)
			{
				form.appendChild(BX.create('input', {
					props: {
						type: 'hidden',
						name: name,
						value: val
					}
				}));
			});
		}

		var placementInterface = appLayout.messageInterface;
		placementInterface.setPropertyValue = function(param, callback)
		{
			for (var key in param)
			{
				if (properties[key])
				{
					setValueToForm(key, param[key]);
				}
			}
		}

		for(var k in values)
		{
			setValueToForm(k, values[k]);
		}
	});
</script>
<?php endif;?>
