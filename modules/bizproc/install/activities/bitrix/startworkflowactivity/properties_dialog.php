<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

if ($isAdmin):
?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPSWFA_PD_DOCUMENT_ID") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField('string', 'document_id', $documentId, array('size' => 20))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSWFA_PD_ENTITY") ?>:</td>
	<td width="60%">
		<select name="" onchange="BPSWFA_getDocumentTypes(this.value)">
			<option value=""><?= GetMessage("BPSWFA_PD_ENTITY") ?>:</option>
			<? foreach ($entities as $id => $name):?>
			<option value="<?= htmlspecialcharsbx($id) ?>" <?if ($id == $currentEntity) echo 'selected'?>><?= htmlspecialcharsbx($name) ?></option>
			<? endforeach ?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSWFA_PD_DOCUMENT_TYPE") ?>:</td>
	<td width="60%">
		<select name="" id="bpswfa_types" <?if (empty($currentType)) echo 'disabled'?> onchange="BPSWFA_getTemplates(this.value)">
			<option value=""><?= GetMessage("BPSWFA_PD_DOCUMENT_TYPE") ?>:</option>
			<? foreach ($types as $type):?>
				<option value="<?= htmlspecialcharsbx($type['id']) ?>" <?if ($type['id'] == $currentType) echo 'selected'?>>
					<?= htmlspecialcharsbx($type['name']) ?></option>
			<? endforeach ?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPSWFA_PD_TEMPLATE") ?>:</span></td>
	<td width="60%">
		<select name="template_id" id="bpswfa_templates" <?if (empty($currentTemplateId)) echo 'disabled'?>  onchange="BPSWFA_getTemplateParameters(this.value)">
			<option value=""><?= GetMessage("BPSWFA_PD_TEMPLATE") ?>:</option>
			<? foreach ($templates as $template):?>
				<option value="<?= htmlspecialcharsbx($template['id']) ?>" <?if ($template['id'] == $currentTemplateId) echo 'selected'?>>
					<?= htmlspecialcharsbx($template['name']) ?></option>
			<? endforeach ?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"></td>
	<td width="60%">
		<label>
			<input type="checkbox" value="Y" name="use_subscription" <?if ($useSubscription == 'Y') echo 'checked'?>/>
			<?= GetMessage("BPSWFA_PD_USE_SUBSCRIPTION") ?>
		</label>
	</td>
</tr>
<tbody id="bpswfa_template">
<?=$templateParametersRender?>
</tbody>
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
				'entity': entity
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
				'document': document
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
				'content_type': 'html'
			},
			function(response)
			{
				if (response)
					container.innerHTML = response;
			}
		);
	};
</script>
<?else:?>
<tr>
	<td align="right" width="40%" valign="top" colspan="2" style="color: red"><?=GetMessage('BPSWFA_PD_ACCESS_DENIED_1')?></td>
</tr>
<?endif?>