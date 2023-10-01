<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @var bool $canSetModifiedBy */
/** @var mixed $modifiedBy */
$mergeMultipleFields = $dialog->getMap()['MergeMultipleFields'];

$extendedType = $dialog->getDocumentType();
if ($dialog->getContext()['DOCUMENT_CATEGORY_ID'] ?? null)
{
	$extendedType[] = $dialog->getContext()['DOCUMENT_CATEGORY_ID'];
}

?>
<div class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text">
	<a class="bizproc-automation-popup-settings-link" data-role="bp-sfa-fields-list">
		<?=GetMessage('BIZPROC_AUTOMATION_SFA_FIELDS_LIST')?>
	</a>
</div>
<script>
	BX.ready(function()
	{
		var documentType = <?=\Bitrix\Main\Web\Json::encode($extendedType)?>;
		var documentFields = <?=\Bitrix\Main\Web\Json::encode($arDocumentFields)?>;
		var documentFieldsSort = <?=\Bitrix\Main\Web\Json::encode(array_keys($arDocumentFields))?>;

		var i, menuItems = [];

		for (i = 0; i < documentFieldsSort.length; ++i)
		{
			var fieldId = documentFieldsSort[i], propertyType;
			if (!documentFields.hasOwnProperty(fieldId))
				continue;

			if (fieldId === 'STATUS_ID' ||
				fieldId === 'STAGE_ID' ||
				fieldId === 'CATEGORY_ID' ||
				fieldId.indexOf('EVENT_') === 0)
			{
				continue;
			}

			propertyType = documentFields[fieldId]['Type'];

			menuItems.push({
				text: BX.util.htmlspecialchars(documentFields[fieldId]['Name']),
				fieldId: fieldId,
				onclick: function(e, item)
				{
					this.popupWindow.close();
					BWFVCAddCondition(item.fieldId, '');
				}
			});
		}

		var onFieldsListSelectClick = function(e)
		{
			var menuId = 'bp-sfa-' + Math.random();

			BX.PopupMenu.show(
				menuId,
				this,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(this)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					className: 'bizproc-automation-inline-selector-menu',
					overlay: { backgroundColor: 'transparent' }
				}
			);

			return BX.PreventDefault(e);
		};

		var fieldsListSelect = document.querySelector('[data-role="bp-sfa-fields-list"]');
		if (fieldsListSelect)
		{
			BX.bind(fieldsListSelect, 'click', onFieldsListSelectClick);
		}

		function BWFVCChangeFieldType(controlWrapper, field, value)
		{
			var property = documentFields[field];
			if (!property)
			{
				return;
			}

			var node = BX.Bizproc.FieldType.renderControl(documentType, property, field, value);

			if (node)
			{
				controlWrapper.appendChild(node);
			}

			return node;
		}

		var bwfvc_counter = -1;
		var addedFields = {};

		function BWFVCAddCondition(fieldId, val)
		{
			var field = documentFields[fieldId];

			if (addedFields[fieldId])
			{
				return;
			}
			addedFields[fieldId] = true;

			var addrowTable = document.getElementById('bwfvc_addrow_table');

			bwfvc_counter++;
			var newRow = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'}});

			newRow.appendChild(BX.create('span', {
				text: field.Name,
				attrs: {
					className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete'
				}
			}));

			var inputHidden = BX.create("input", {props: {type: 'hidden'}});
			inputHidden.name = "document_field_" + bwfvc_counter;
			inputHidden.value = fieldId;

			newRow.appendChild(inputHidden);

			var controlWrapper = BX.create('div');
			newRow.appendChild(controlWrapper);

			var deleteButton = BX.create('a', {
				attrs: {
					className: 'bizproc-automation-popup-settings-delete bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-light'
				},
				props: {href: '#'},
				events: {
					click: BWFVCDeleteCondition.bind(newRow, fieldId)
				},
				text: '<?=GetMessageJS('BIZPROC_AUTOMATION_SFA_DELETE')?>'
			});
			newRow.appendChild(deleteButton);

			BWFVCChangeFieldType(controlWrapper, fieldId, val);

			addrowTable.appendChild(newRow);
		}

		function BWFVCDeleteCondition(fieldId, e)
		{
			BX.remove(this);
			e.preventDefault();
			delete addedFields[fieldId]
		}
<?php
		foreach ($arCurrentValues as $fieldKey => $documentFieldValue)
		{
			if (!array_key_exists($fieldKey, $arDocumentFields))
			{
				continue;
			}

			if ($arDocumentFields[$fieldKey]['Type'] === 'user')
			{
				$documentFieldValue = \CBPHelper::UsersArrayToString(
						$documentFieldValue,
						null,
						$dialog->getDocumentType()
				);
			}
			?>BWFVCAddCondition('<?= CUtil::JSEscape($fieldKey) ?>', <?= \Bitrix\Main\Web\Json::encode($documentFieldValue) ?>);<?
		}
		if (count($arCurrentValues) <= 0)
		{
			$fieldIds = array_keys($arDocumentFields);
			?>BWFVCAddCondition("<?=CUtil::JSEscape($fieldIds[0])?>", "");<?
		}
?>});
</script>
<div id="bwfvc_addrow_table"></div>
	<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($mergeMultipleFields['Name'])?>:
	</span>
		<?=$dialog->renderFieldControl($mergeMultipleFields)?>
	</div>
<?if ($canSetModifiedBy):?>
	<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?=GetMessage('BIZPROC_AUTOMATION_SFA_MODIFIED_BY')?>:
	</span>
		<?=$dialog->renderFieldControl(['Type' => 'user', 'FieldName' => 'modified_by'], $modifiedBy)?>
	</div>
<?endif;?>