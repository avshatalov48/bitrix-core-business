<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @var \CBPDocumentService $documentService */
$elementId = $dialog->getMap()['ElementId'];
$docType = $dialog->getMap()['DocumentType'];
?>
<?= $javascriptFunctions ?>
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

<tr id="ulda_pd_add_field_container" style="display:none">
	<td colspan="2">
		<a href="#" id="ulda_pd_add_field_btn"><?= GetMessage("BPULDA_PD_ADD_FIELD") ?></a>
		<table width="100%" border="0" cellpadding="2" cellspacing="2" id="ulda_lists_document_fields">
			<?
			$fields = $dialog->getCurrentValue('fields');
			foreach ($fields as $fieldKey => $fieldValue):

				$property = isset($documentFields[$fieldKey]) ? $documentFields[$fieldKey] : null;

				if (!$property)
				{
					continue;
				}
				?>
				<tr data-field="<?=htmlspecialcharsbx($fieldKey)?>">
					<td align="right" width="30%" class="adm-detail-content-cell-l">
						<?if ($property["Required"]):?><span class="adm-required-field"><?endif;?>
							<?=htmlspecialcharsbx($property["Name"])?>:
			<?if ($property["Required"]):?></span><?endif;?>
					</td>
					<td width="60%" class="adm-detail-content-cell-r"><?=$documentService->GetFieldInputControl(
							$listsDocumentType,
							$property,
							array($dialog->getFormName(), 'fields__'.$fieldKey),
							$fieldValue,
							true
						)?>
					</td>
					<td align="right"><a onclick="BX.remove(BX.findParent(this, {tag: 'tr'})); return false"><?=GetMessage("BPULDA_PD_DELETE")?></a></td>
				</tr>
			<?endforeach;?>
		</table>
	</td>
</tr>
<script>
	BX.ready(function()
	{
		BX.namespace('BX.Bizproc');

		var container = BX('doctype_container');
		var select = container ? container.querySelector('[name="lists_document_type"]') : null;
		var fieldsContainer = BX('ulda_lists_document_fields');
		var addFieldButton = BX('ulda_pd_add_field_btn');
		var addFieldContainer = BX('ulda_pd_add_field_container');

		var fields = <?=\Bitrix\Main\Web\Json::encode($documentFieldsJs)?>;

		if (fields.length)
		{
			BX.show(addFieldContainer, 'table-row');
		}

		BX.bind(select, 'change', function()
			{
				var documentType = this.value;
				BX.cleanNode(fieldsContainer);

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
						'activity': 'UpdateListsDocumentActivity',
						'lists_document_type': documentType,
						'form_name': <?=Cutil::PhpToJSObject($formName)?>
					},
					onsuccess: function(response)
					{
						if (response)
						{
							fields = response.fields;
							BX.show(addFieldContainer, 'table-row');
						}
					}
				});
			}
		);

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
						BWFVCAddCondition(item.field);
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
					maxHeight: 500
				}
			);
		});

		var BWFVCChangeFieldType = function(container, field, value)
		{
			BX.showWait();

			objFieldsULDA.GetFieldInputControl(
				field,
				(value || ''),
				{'Field':field['FieldName'], 'Form':'<?= $formName ?>'},
				function(v){
					if (v)
					{
						container.innerHTML = v;
						if (typeof BX.Bizproc.Selector !== 'undefined')
						{
							BX.Bizproc.Selector.initSelectors(container);
						}
					}
					BX.closeWait();
				},
				true
			);
		};

		var BWFVCAddCondition = function(field, val)
		{
			if (fieldsContainer.querySelector('[data-field="'+field['Id']+'"]'))
			{
				return;
			}

			var newCell, newRow = fieldsContainer.insertRow(-1);

			newRow.setAttribute('data-field', field['Id']);

			newCell = newRow.insertCell(-1);
			BX.adjust(newCell, {
				style: {textAlign: 'right', width: '30%'},
				attrs: {className: 'adm-detail-content-cell-l'}
			});
			newCell.appendChild(BX.create('span', {
				attrs: {
					className: field['Required'] ? 'adm-required-field' : ''
				},
				text: field['Name']
			}));

			var controlCell = newRow.insertCell(-1);
			BX.adjust(controlCell, {width: 60, className: 'adm-detail-content-cell-r'});
			controlCell.textContent = '...';

			newCell = newRow.insertCell(-1);
			newCell.align="right";

			newCell.appendChild(BX.create('a', {
				text: '<?=GetMessageJs("BPULDA_PD_DELETE")?>',
				events: {
					click: BWFVCDeleteCondition
				}
			}));

			BWFVCChangeFieldType(controlCell, field, val);
		};

		var BWFVCDeleteCondition = function(event)
		{
			event.preventDefault();
			var row = BX.findParent(this, {tag: 'tr'});
			BX.remove(row);
		}
	});
</script>