<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$elementId = $dialog->getMap()['ElementId'];
$docType = $dialog->getMap()['DocumentType'];
$fields = $dialog->getMap()['Fields'];
?>

<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($elementId['Name'])?>: </span>
	<?=$dialog->renderFieldControl($elementId)?>
</div>

<div class="bizproc-automation-popup-settings" id="doctype_container">
	<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($docType['Name'])?>: </span>
	<?=$dialog->renderFieldControl($docType)?>
</div>

<div class="bizproc-automation-popup-settings" id="fields_container">
	<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($fields['Name'])?>: </span>
	<?=$dialog->renderFieldControl($fields)?>
</div>

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