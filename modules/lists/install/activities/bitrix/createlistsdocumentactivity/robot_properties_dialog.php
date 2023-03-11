<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog
 * @var array $documentFields
 * @var array $listsDocumentType
 * @var \CBPDocumentService $documentService
 * @var string $formName
 */
$docType = $dialog->getMap()['DocumentType'];
?>

<div class="bizproc-automation-popup-settings" id="doctype_container">
	<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($docType['Name'])?>: </span>
	<?=$dialog->renderFieldControl($docType)?>
</div>

<div id="lists_document_fields">
	<?
	$baseTypes = \Bitrix\Bizproc\FieldType::getBaseTypesMap();
	foreach ($documentFields as $fieldKey => $fieldValue):?>
		<div class="bizproc-automation-popup-settings">
			<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
				<?=htmlspecialcharsbx($fieldValue["Name"])?>:
			</span>
			<?
			echo $documentService->GetFieldInputControl(
				$listsDocumentType,
				$fieldValue,
				array($dialog->getFormName(), $fieldKey),
				$dialog->getCurrentValue($fieldKey),

				(isset($baseTypes[$fieldValue['Type']])),true
			)
			?>
		</div>
	<?endforeach;?>
</div>
<script>
	BX.ready(function()
	{
		var container = BX('doctype_container');
		var fieldsContainer = BX('lists_document_fields');
		var select = container ? container.querySelector('[name="lists_document_type"]') : null;
		if (select)
		{
			BX.bind(select, 'change', function()
				{
					var documentType = this.value;
					BX.cleanNode(fieldsContainer);

					if (!documentType)
					{
						return;
					}

					BX.ajax.runAction(
						'bizproc.activity.request',
						{
							data: {
								documentType: <?= Cutil::PhpToJSObject($dialog->getDocumentType()) ?>,
								activity: 'CreateListsDocumentActivity',
								params: {
									lists_document_type: documentType,
									form_name: <?= Cutil::PhpToJSObject($formName) ?>,
									public_mode: 'Y',
								}
							}
						}
					).then(
						(response) => {
							response.data.forEach(function(field)
							{
								renderer(field, documentType.split('@'));
							});
						},
						(response) => {
							BX.UI.Dialogs.MessageBox.alert(response.errors[0].message);
						}
					);
				}
			);

			var renderer = function(field, documentType)
			{
				var newRow = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'}});

				newRow.appendChild(BX.create('span', {
					text: field.Name,
					attrs: {
						className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete'
					}
				}));

				var controlWrapper = BX.create('div');
				newRow.appendChild(controlWrapper);

				var node = BX.Bizproc.FieldType.renderControl(documentType, field, field['Id']);

				if (node)
				{
					controlWrapper.appendChild(node);
				}

				fieldsContainer.appendChild(newRow);
			}
		}
	});
</script>