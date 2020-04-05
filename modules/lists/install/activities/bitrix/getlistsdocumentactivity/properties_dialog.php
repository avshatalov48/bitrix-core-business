<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$elementId = $dialog->getMap()['ElementId'];
$docType = $dialog->getMap()['DocumentType'];
$fields = $dialog->getMap()['Fields'];
?>
<tr>
	<td align="right" width="40%" valign="top">
		<span class="adm-required-field"><?=htmlspecialcharsbx($elementId['Name'])?>:</span>
	</td>
	<td width="60%">
		<?=$dialog->renderFieldControl($elementId, null, true, \Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER)?>
	</td>
</tr>

<tr>
	<td align="right" width="40%" valign="top">
		<span class="adm-required-field"><?=htmlspecialcharsbx($docType['Name'])?>:</span>
	</td>
	<td width="60%" id="doctype_container">
		<?=$dialog->renderFieldControl($docType, null, false, \Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER)?>
	</td>
</tr>

<tr id="fields_container">
	<td align="right" width="40%" valign="top">
		<span class="adm-required-field"><?=htmlspecialcharsbx($fields['Name'])?>:</span>
	</td>
	<td width="60%">
		<?=$dialog->renderFieldControl($fields, null, false, \Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER)?>
	</td>
</tr>

<script>
	BX.ready(function()
	{
		var container = BX('doctype_container');
		var select = container ? container.querySelector('[name="lists_document_type"]') : null;
		var fieldsSelect = BX('fields_container').querySelector('select');

		BX.bind(select, 'change', function()
			{
				var documentType = this.value;
				BX.cleanNode(fieldsSelect);

				if (!documentType)
				{
					return;
				}

				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: '/bitrix/tools/bizproc_activity_ajax.php',
					data:  {
						'site_id': BX.message('SITE_ID'),
						'sessid' : BX.bitrix_sessid(),
						'document_type' : <?=Cutil::PhpToJSObject($dialog->getDocumentType())?>,
						'activity': 'GetListsDocumentActivity',
						'lists_document_type': documentType,
						'form_name': <?=Cutil::PhpToJSObject($formName)?>
					},
					onsuccess: function(response)
					{
						if (response)
						{
							response.options.forEach(function(opt)
							{
								fieldsSelect.appendChild(BX.create('option', {
									props: {value: opt.value},
									text: opt.text
								}))
							});
						}
					}
				});
			}
		);
	});
</script>