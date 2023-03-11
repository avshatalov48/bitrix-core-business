<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$elementId = $dialog->getMap()['ElementId'];
$docType = $dialog->getMap()['DocumentType'];
?>
<?= $javascriptFunctions ?>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($elementId['Name'])?>: </span>
	<?=$dialog->renderFieldControl($elementId)?>
</div>

<div class="bizproc-automation-popup-settings" id="doctype_container">
	<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($docType['Name'])?>: </span>
	<?=$dialog->renderFieldControl($docType)?>
</div>

<div id="ulda_pd_add_field_btn" style="display: none;" class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text">
	<a class="bizproc-automation-popup-settings-link" data-role="bp-sfa-fields-list">
		<?=GetMessage('BPULDA_PD_ADD_FIELD')?>
	</a>
</div>

<div id="ulda_lists_document_fields" style="padding-top: 15px">
	<?
	$fields = $dialog->getCurrentValue('fields');
	$baseTypes = \Bitrix\Bizproc\FieldType::getBaseTypesMap();
	foreach ($fields as $fieldKey => $fieldValue):

		$property = isset($documentFields[$fieldKey]) ? $documentFields[$fieldKey] : null;

		if (!$property)
		{
			continue;
		}
		?>
		<div class="bizproc-automation-popup-settings" data-field="<?=htmlspecialcharsbx($fieldKey)?>">
			<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
				<?=htmlspecialcharsbx($property["Name"])?>:
			</span>
			<?
			echo $documentService->GetFieldInputControl(
				$listsDocumentType,
				$property,
				array($dialog->getFormName(), 'fields__'.$fieldKey),
				$fieldValue,
				(isset($baseTypes[$property['Type']])),
				true
			)
			?>
			<a onclick="BX.remove(this.parentNode);return false;" class="bizproc-automation-popup-settings-delete bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-light" href="#">
				<?=GetMessage('BPULDA_PD_DELETE')?>
			</a>
		</div>
	<?endforeach;?>
</div>
<script>
	BX.ready(function()
	{
		var container = BX('doctype_container');
		var select = container ? container.querySelector('[name="lists_document_type"]') : null;
		var fieldsContainer = BX('ulda_lists_document_fields');
		var addFieldButton = BX('ulda_pd_add_field_btn');

		var fields = <?=\Bitrix\Main\Web\Json::encode($documentFieldsJs)?>;
		var documentType = '<?=$listsDocumentType ? implode('@', $listsDocumentType) : ''?>';

		if (fields.length)
		{
			BX.show(addFieldButton, 'table-row');
		}

		BX.bind(select, 'change', function()
			{
				documentType = this.value;
				BX.cleanNode(fieldsContainer);

				if (!documentType)
				{
					BX.hide(addFieldButton, 'table-row');
					return;
				}

				BX.ajax.runAction(
					'bizproc.activity.request',
					{
						data: {
							documentType: <?= Cutil::PhpToJSObject($dialog->getDocumentType()) ?>,
							activity: 'UpdateListsDocumentActivity',
							params: {
								lists_document_type: documentType,
								form_name: <?= Cutil::PhpToJSObject($dialog->getFormName()) ?>,
							}
						}
					}
				).then(
					(response) => {
						fields = response.data.fields;
						BX.show(addFieldButton, 'table-row');
					},
					(response) => {
						BX.UI.Dialogs.MessageBox.alert(response.errors[0].message);
					}
				);
			}
		);

		var renderer = function(field)
		{
			var newRow = BX.create('div', {attrs: {
				className: 'bizproc-automation-popup-settings',
				'data-field': field['Id']
			}});

			newRow.appendChild(BX.create('span', {
				text: field.Name,
				attrs: {
					className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete'
				}
			}));

			var controlWrapper = BX.create('div');
			newRow.appendChild(controlWrapper);

			var node = BX.Bizproc.FieldType.renderControl(documentType, field, field['FieldName']);

			if (node)
			{
				controlWrapper.appendChild(node);
			}

			var deleteButton = BX.create('a', {
				attrs: {
					className: 'bizproc-automation-popup-settings-delete bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-light'
				},
				props: {href: '#'},
				events: {
					click: function(event)
					{
						event.preventDefault();
						BX.remove(this);
					}.bind(newRow)
				},
				text: '<?=GetMessageJS('BPULDA_PD_DELETE')?>'
			});
			newRow.appendChild(deleteButton);

			return newRow;
		};

		BX.bind(addFieldButton, 'click', function(event)
		{
			event.preventDefault();

			if (!fields.length)
			{
				return false;
			}

			var menuId = 'bp-ulda-' + Math.random();

			var menuItems = [];

			fields.forEach(function(field)
			{
				menuItems.push({
					text: field['Name'],
					field: field,
					onclick: function(e, item)
					{
						this.popupWindow.close();
						if (!fieldsContainer.querySelector('[data-field="'+field['Id']+'"]'))
						{
							fieldsContainer.appendChild(renderer(field));
						}
					}
				});
			});

			BX.PopupMenu.show(
				menuId,
				this,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(this)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					maxHeight: 500,
					overlay: { backgroundColor: 'transparent' },
				}
			);
		});
	});
</script>