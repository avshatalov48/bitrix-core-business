<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

?>

<? if($isAdmin): ?>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title"><?=GetMessage('BPSWFA_RPD_DOCUMENT_ID')?>:</span>
	<?=$dialog->renderFieldControl($map['DOCUMENT_ID'], $documentId)?>
</div>

<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title"><?=GetMessage("BPSWFA_RPD_ENTITY")?>: </span>
	<select name="" class="bizproc-automation-popup-settings-dropdown" onchange="BPSWFA_getDocumentTypes(this.value)">
		<option value=""><?= GetMessage("BPSWFA_RPD_ENTITY") ?>:</option>
		<? foreach ($entities as $id => $name):?>
			<option value="<?= htmlspecialcharsbx($id) ?>"
				<?if ($id == $currentEntity) echo 'selected';?>
			><?= htmlspecialcharsbx($name) ?></option>
		<? endforeach ?>
	</select>
</div>

<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title"><?=GetMessage("BPSWFA_RPD_DOCUMENT_TYPE_1")?>: </span>
	<select class="bizproc-automation-popup-settings-dropdown" name="" id="bpswfa_types" <?if (empty($currentType)) echo 'disabled'?> onchange="BPSWFA_getTemplates(this.value)">
		<option value=""><?=GetMessage("BPSWFA_RPD_DOCUMENT_TYPE_1")?>:</option>
		<? foreach ($types as $type):?>
			<option value="<?= htmlspecialcharsbx($type['id']) ?>" <?if ($type['id'] == $currentType) echo 'selected'?>>
				<?= htmlspecialcharsbx($type['name']) ?></option>
		<? endforeach ?>
	</select>
</div>

<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title"><?=GetMessage("BPSWFA_RPD_TEMPLATE")?>: </span>
	<select class="bizproc-automation-popup-settings-dropdown" name="template_id" id="bpswfa_templates" <?if (empty($currentTemplateId)) echo 'disabled'?>  onchange="BPSWFA_getTemplateParameters(this.value)">
		<option value=""><?=GetMessage("BPSWFA_RPD_TEMPLATE")?>:</option>
		<? foreach ($templates as $template):?>
			<option value="<?= htmlspecialcharsbx($template['id']) ?>" <?if ($template['id'] == $currentTemplateId) echo 'selected'?>>
				<?= htmlspecialcharsbx($template['name']) ?></option>
		<? endforeach ?>
	</select>
</div>

<div class="bizproc-automation-popup-settings">
	<input type="checkbox" value="Y" name="use_subscription" <?if ($useSubscription == 'Y') echo 'checked'?>/>
	<?= GetMessage("BPSWFA_RPD_USE_SUBSCRIPTION") ?>
</div>
<div id="bpswfa_template">
<?=$templateParametersRender?>
</div>

<script>
	var BPSWFA_getDocumentTypes = function(entity)
	{
		var select = BX('bpswfa_types'),
			select2 = BX('bpswfa_templates');
		BX('bpswfa_template').innerHTML = '';

		if (!entity)
		{
			select.setAttribute('disabled', 'disabled');
			select2.setAttribute('disabled', 'disabled');
		}

		BX.ajax.post(
			'/bitrix/tools/bizproc_activity_ajax.php',
			{
				'site_id': BX.message('SITE_ID'),
				'sessid' : BX.bitrix_sessid(),
				'document_type' : <?=Cutil::PhpToJSObject($documentType)?>,
				'activity': 'StartWorkflowActivity',
				'entity': entity,
			},
			function(response)
			{
				response = BX.parseJSON(response);

				var first = select.options[0];
				select.innerHTML = '';
				select.options[0] = first;

				for (var i = 0, s = response.types.length; i < s; ++i)
				{
					select.options[i+1] = new Option(response.types[i].name, response.types[i].id);
				}
				select.removeAttribute('disabled');
				select2.setAttribute('disabled', 'disabled');
			}
		);
	};

	var BPSWFA_getTemplates = function(document)
	{
		var select = BX('bpswfa_templates');
		BX('bpswfa_template').innerHTML = '';
		if (!document)
		{
			select.setAttribute('disabled', 'disabled');
		}

		BX.ajax.post(
			'/bitrix/tools/bizproc_activity_ajax.php',
			{
				'site_id': BX.message('SITE_ID'),
				'sessid' : BX.bitrix_sessid(),
				'document_type' : <?=Cutil::PhpToJSObject($documentType)?>,
				'activity': 'StartWorkflowActivity',
				'document': document,
			},
			function(response)
			{
				response = BX.parseJSON(response);

				var first = select.options[0];
				select.innerHTML = '';
				select.options[0] = first;

				for (var i = 0, s = response.templates.length; i < s; ++i)
				{
					select.options[i+1] = new Option(response.templates[i].name, response.templates[i].id);
				}
				select.removeAttribute('disabled');
			}
		);
	};

	var BPSWFA_getTemplateParameters = function(templateId)
	{
		var container = BX('bpswfa_template');
		container.innerHTML = '';

		if (!templateId)
			return;

		BX.ajax.post(
			'/bitrix/tools/bizproc_activity_ajax.php',
			{
				'site_id': BX.message('SITE_ID'),
				'sessid' : BX.bitrix_sessid(),
				'document_type' : <?=Cutil::PhpToJSObject($documentType)?>,
				'activity': 'StartWorkflowActivity',
				'template_id': templateId,
				'form_name': <?=Cutil::PhpToJSObject($formName)?>,
				'content_type': 'html',
				'properties_dialog': <?=propertiesDialogToJsObject($dialog)?>,
				'isRobot': 'y'
			},
			function(response)
			{
				if (response)
					container.innerHTML = response;

				if (BX.getClass('BX.Bizproc.Automation.Designer'))
				{
					var dlg = BX.Bizproc.Automation.Designer.getInstance().getRobotSettingsDialog();
					if (dlg)
					{
						dlg.template.initRobotSettingsControls(dlg.robot, container);
					}
				}
			}
		);
	};
</script>
<? else: ?>
<div class="bizproc-automation-popup-settings-alert">
	<?=GetMessage('BPSWFA_RPD_ACCESS_DENIED_1')?>
</div>
<? endif ?>