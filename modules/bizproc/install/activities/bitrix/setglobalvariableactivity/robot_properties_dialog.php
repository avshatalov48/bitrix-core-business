<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @var $javascriptFunctions*/
/** @var $arVariables */
/** @var $arCurrentValues */
/** @var $isAdmin */

if ($isAdmin): ?>

	<div class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text">
		<a class="bizproc-automation-popup-settings-link" data-role="bp-sgva-variables-list">
			<?= GetMessage('BIZPROC_AUTOMATION_SGVA_VARIABLE_LIST') ?>
		</a>
	</div>
	<div id="bwfvc_addrow_table"></div>
	<?= $javascriptFunctions ?>
	<script>
		BX.ready(function()
		{
			var documentType = <?= \Bitrix\Main\Web\Json::encode($dialog->getDocumentType()) ?>;
			var globalVariables = <?= \Bitrix\Main\Web\Json::encode($arVariables) ?>;
			var menuItems = [];

			for (var fieldId in globalVariables)
			{
				menuItems.push({
					text: BX.util.htmlspecialchars(globalVariables[fieldId]['Name']),
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
				var menuId = 'bp-sgva-' + Math.random();
				BX.Main.MenuManager.show(
					menuId,
					this,
					menuItems,
					{
						autoHide: true,
						offsetLeft: (BX.pos(this)['width'] / 2),
						angle: { position: 'top', offset: 0 },
						className: 'bizproc-automation-inline-selector-menu',
						overlay: { backgroundColor: 'transparent' },
						minHeight: 50,
						minWidth: 100,
						events:
							{
								onPopupClose: function()
								{
									this.destroy();
								}
							}
					}
				);

				return BX.PreventDefault(e);
			}
			var variablesListSelect = document.querySelector('[data-role="bp-sgva-variables-list"]');
			if (variablesListSelect)
			{
				BX.bind(variablesListSelect, 'click', onFieldsListSelectClick);
			}

			var bwfvc_counter = -1;
			var addedFields = {};

			function BWFVCAddCondition(fieldId, val)
			{
				if (fieldId === '')
				{
					return;
				}

				var field = globalVariables[fieldId];

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

				var inputHidden = BX.create('input', {props: {type: 'hidden'}});
				inputHidden.name = 'global_variable_field_' + bwfvc_counter;
				inputHidden.value = fieldId;
				newRow.appendChild(inputHidden);

				var controlWrapper = BX.create('div');
				newRow.appendChild(controlWrapper);
				BWFVCChangeFieldType(controlWrapper, fieldId, val);

				var deleteButton = BX.create('a', {
					attrs: {
						className: 'bizproc-automation-popup-settings-delete bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-light'
					},
					props: {href: '#'},
					events: {
						click: BWFVCDeleteCondition.bind(newRow, fieldId)
					},
					text: '<?= GetMessageJS('BIZPROC_AUTOMATION_SGVA_DELETE') ?>'
				});
				newRow.appendChild(deleteButton);

				addrowTable.appendChild(newRow);
			}

			function BWFVCChangeFieldType(controlWrapper, field, value)
			{
				var property = globalVariables[field];
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

			function BWFVCDeleteCondition(fieldId, e)
			{
				BX.remove(this);
				e.preventDefault();
				delete addedFields[fieldId];
			}

			<?php
			foreach ($arCurrentValues as $id => $value)
			{
				if (!array_key_exists($id, $arVariables))
				{
					continue;
				}

				if ($arVariables[$id]['Type'] === 'user')
				{
					$value = CBPHelper::UsersArrayToString($value, null, $dialog->getDocumentType());
				}
				?>
				BWFVCAddCondition('<?= CUtil::JSEscape($id) ?>', <?= CUtil::PhpToJSObject($value) ?>);
				<?php
			}
			if (count($arCurrentValues) <= 0)
			{
				$variableIds = array_keys($arVariables);
				?>BWFVCAddCondition('<?= CUtil::JSEscape($variableIds[0]) ?>', '');
			<?php
			} ?>
		});
	</script>

<?php else: ?>
	<div class="bizproc-automation-popup-settings-alert">
		<?=GetMessage('BIZPROC_AUTOMATION_SGVA_ACCESS_DENIED')?>
	</div>
<?php endif ?>
