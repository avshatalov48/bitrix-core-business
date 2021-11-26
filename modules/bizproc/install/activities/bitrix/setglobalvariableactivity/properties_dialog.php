<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var $javascriptFunctions */
/** @var $arVariables */
/** @var $arCurrentValues */
/** @var $isAdmin */
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

if ($isAdmin): ?>
	<?= $javascriptFunctions ?>

	<tr>
		<td colspan="2">
			<table width="100%" border="0" cellspacing="2" cellpadding="2" id="bwfvc_addrow_table">
				<?php
				$defaultFieldCode = array_key_first($arVariables) ?? '';
				$index = -1;
				foreach ($arCurrentValues as $varId => $varValue):
					if (array_key_exists($varId, $arVariables)):
						$index++ ?>
						<tr id="delete_row_<?= $index ?>">
							<td>
								<select
									name="global_variable_field_<?= $index ?>"
									onchange="BPWFSCAChangeFieldType(<?= $index ?>, this.options[this.selectedIndex].value, null)"
								><?php foreach ($arVariables as $key => $value):?>
										<option
											value="<?= htmlspecialcharsbx($key) ?>"
											<?= ($key === $varId) ? ' selected' : ''?>
										><?= htmlspecialcharsbx($value['Name']) ?></option>
									<?php endforeach ?>
								</select>
							</td>
							<td>=</td>
							<td id="id_td_variable_value_<?= $index ?>">
								<input type="text" name="<?= htmlspecialcharsbx($varId) ?>" value="...">
							</td>
							<td align="right">
								<a
									href="#"
									onclick="BPWFSCADeleteCondition(<?= $index ?>); return false;"
								><?= GetMessage('BPSGVA_PD_DELETE') ?></a>
							</td>
						</tr>
					<?php endif;
				endforeach ?>
			</table>

			<?php CAdminCalendar::ShowScript() ?>

			<script>
				BX.namespace('BX.Bizproc');

				function BPWFSCAChangeFieldType(ind, field, value)
				{
					BX.showWait();
					var valueTd = document.getElementById('id_td_variable_value_' + ind);

					objFieldsGlobalVar.GetFieldInputControl(
						objFieldsGlobalVar.arDocumentFields[field],
						value,
						{'Field': field, 'Form': '<?= $dialog->getFormName() ?>'},
						function(v) {
							if (v === undefined)
							{
								valueTd.innerHTML = '';
							}
							else
							{
								valueTd.innerHTML = v;
								if (typeof BX.Bizproc.Selector !== 'undefined')
								{
									BX.Bizproc.Selector.initSelectors(valueTd);
								}
							}
							BX.closeWait();
						},
						true
					);
				}

				var bwfvc_counter = <?= $index ?>;
				function BPWFSCAAddCondition()
				{
					var addrowTable = document.getElementById('bwfvc_addrow_table');

					bwfvc_counter++;

					var newRow = addrowTable.insertRow(-1);
					newRow.id = 'delete_row_' + bwfvc_counter;

					var cellSelect = newRow.insertCell(-1);

					var newSelect = document.createElement('select');
					newSelect.setAttribute('bwfvc_counter', bwfvc_counter);
					newSelect.name = 'global_variable_field_' + bwfvc_counter;
					newSelect.onchange = function() {
						BPWFSCAChangeFieldType(
							this.getAttribute('bwfvc_counter'),
							this.options[this.selectedIndex].value,
							null
						)
					};

					<?php
					$optionIndex = -1;
					foreach ($arVariables as $key => $value):
						$optionIndex++; ?>
						newSelect.options[<?= $optionIndex ?>] = new Option(
							'<?= CUtil::JSEscape($value['Name']) ?>',
							'<?= CUtil::JSEscape($key) ?>'
						);
					<?php endforeach?>
					cellSelect.appendChild(newSelect);

					var cellSymbolEquals = newRow.insertCell(-1);
					cellSymbolEquals.innerHTML = '=';

					var cellValue = newRow.insertCell(-1);
					cellValue.id = 'id_td_variable_value_' + bwfvc_counter;
					cellValue.innerHTML = '';

					var cellDeleteRow = newRow.insertCell(-1);
					cellDeleteRow.aligh = 'right';
					cellDeleteRow.innerHTML =
						'<a href="#" onclick="BPWFSCADeleteCondition('
						+ bwfvc_counter
						+ '); return false;"><?= GetMessage("BPSGVA_PD_DELETE") ?></a>'
					;

					BPWFSCAChangeFieldType(bwfvc_counter, '<?= CUtil::JSEscape($defaultFieldCode) ?>', null);
				}

				function BPWFSCADeleteCondition(ind)
				{
					var addrowTable = document.getElementById('bwfvc_addrow_table');
					var count = addrowTable.rows.length;
					for (var i = 0; i < count; i++)
					{
						if (addrowTable.rows[i].id !== 'delete_row_' + ind)
						{
							continue;
						}

						addrowTable.deleteRow(i);
						break;
					}
				}
			</script>

			<script>
				<?php
				$i = -1;
				foreach ($arCurrentValues as $varId => $varValue):
					if (array_key_exists($varId, $arVariables)):
					$i++;?>
					BPWFSCAChangeFieldType(
						<?= $i ?>,
						'<?= CUtil::JSEscape($varId) ?>',
						<?= CUtil::PhpToJSObject($varValue) ?>
					);
					<?php endif;
				endforeach?>

				<?php
				if ($i < 0):?>
					BPWFSCAAddCondition();
				<?php endif ?>
			</script>

			<a href="#" onclick="BPWFSCAAddCondition(); return false;"><?= GetMessage('BPSGVA_PD_ADD') ?></a>
		</td>
	</tr>
<?php else: ?>
	<tr>
		<td
			align="right"
			width="40%"
			valign="top"
			colspan="2"
			style="color: red"
		><?= GetMessage('BPSGVA_PD_ACCESS_DENIED') ?></td>
	</tr>

<?php endif ?>
