<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBPDocumentService $documentService */

$arC = \Bitrix\Bizproc\Activity\Condition::getOperatorList();

$conditions = (array) $arCurrentValues['conditions'];
if (!$conditions)
{
	$condition[] = ['operator' => '!empty'];
}
foreach ($conditions as $i => $condition)
{
	?><tbody data-index="<?=$i?>" data-object="<?=htmlspecialcharsbx($condition['object'])?>" data-field="<?=htmlspecialcharsbx($condition['field'])?>"><?
	if ($i > 0)
	{
		?>
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l">
				<select name="mixed_condition[<?=$i?>][joiner]">
					<option value="0"><?= GetMessage("BPMC_PD_AND") ?></option>
					<option value="1" <?if($condition['joiner']==1) echo 'selected'?>><?= GetMessage("BPMC_PD_OR") ?></option>
				</select>
			</td>
			<td align="right" width="60%" class="adm-detail-content-cell-r"><a href="#" onclick="BPMixedConditionDelete(this); return false;"><?= GetMessage("BPMC_PD_DELETE") ?></a></td>
		</tr>
		<?
	}
	?>
	<tr>
		<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPMC_PD_FIELD") ?>:</td>
		<td width="60%" class="adm-detail-content-cell-r">
			<a href="#" onclick="BPMixedConditionChooseTarget(this); return false;"><?=$condition['object']? $condition['object'].':'.$condition['field'] : GetMessage('BPMC_PD_FIELD_CHOOSE')?></a>
			<input type="hidden" name="mixed_condition[<?= $i ?>][object]" data-role="condition-object" value="<?=htmlspecialcharsbx($condition['object'])?>">
			<input type="hidden" name="mixed_condition[<?= $i ?>][field]" data-role="condition-field" value="<?=htmlspecialcharsbx($condition['field'])?>">
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPMC_PD_CONDITION") ?>:</td>
		<td width="60%" class="adm-detail-content-cell-r">
			<select name="mixed_condition[<?= $i ?>][operator]" data-role="operator-selector" onchange="BPMixedConditionChange(this)">
				<?
				foreach ($arC as $key => $value)
				{
					?><option value="<?= $key ?>"<?= ($condition['operator'] == $key) ? " selected" : "" ?>><?= $value ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<? $hidden = in_array($condition['operator'], ['empty', '!empty']);?>
	<tr data-role="value-row" style="<?if ($hidden) echo 'display:none'?>">
		<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPMC_PD_VALUE") ?>:</td>
		<td width="60%" data-role="value-cell" class="adm-detail-content-cell-r">
			<?=$documentService->GetFieldInputControl(
				$documentType,
				$condition['__property__'],
				['Field' => "mixed_condition_value_".$i, 'Form' => $formName],
				$condition['value'],
				true
			)?>
		</td>
	</tr>
	</tbody>
	<?
}
?>
<tbody>
	<tr>
		<td class="adm-detail-content-cell-l"></td>
		<td class="adm-detail-content-cell-r">
			<a href="#" onclick="BPMixedConditionAdd(this); return false;"><?= GetMessage("BPMC_PD_ADD") ?></a>
		</td>
	</tr>
</tbody>
<?= $javascriptFunctions ?>
<script>
	BX.namespace('BX.Bizproc');

	var bpmc_counter = <?=count($conditions)?>;

	function BPMixedConditionChooseTarget(element)
	{
		var menuItems = [];
		var tbody = element.closest('tbody');

		var clickHandler = function(e, item)
		{
			(this.getRootMenuWindow() || this.popupWindow).close();

			element.textContent = item.propertyObject + ':' + item.propertyField;
			tbody.setAttribute('data-object', item.propertyObject);
			tbody.setAttribute('data-field', item.propertyField);
			tbody.querySelector('[data-role="condition-object"]').value = item.propertyObject;
			tbody.querySelector('[data-role="condition-field"]').value = item.propertyField;
			BPMixedConditionRenderValue(tbody);
		}

		var extractMenuItem = function(items, object)
		{
			var result = [];
			var key;
			for (key in items)
			{
				if (!items.hasOwnProperty(key))
					continue;

				result.push({
					text: BX.util.htmlspecialchars(items[key].Name),
					propertyObject: object,
					propertyField: key,
					property: items[key],
					onclick: clickHandler
				});
			}
			return result;
		};

		if (BX.util.object_keys(window.arWorkflowParameters).length > 0)
		{
			menuItems.push({
				text: '<?=GetMessageJs('BPMC_PD_PARAMS')?>',
				items: extractMenuItem(window.arWorkflowParameters, 'Template')
			});
		}

		if (BX.util.object_keys(window.arWorkflowVariables).length > 0)
		{
			menuItems.push({
				text: '<?=GetMessageJs('BPMC_PD_VARS')?>',
				items: extractMenuItem(window.arWorkflowVariables, 'Variable')
			});
		}

		if (BX.util.object_keys(window.arWorkflowConstants).length > 0)
		{
			menuItems.push({
				text: '<?=GetMessageJs('BPMC_PD_CONSTANTS')?>',
				items: extractMenuItem(window.arWorkflowConstants, 'Constant')
			});
		}

		if (window.arWorkflowGlobalConstants && BX.util.object_keys(window.arWorkflowGlobalConstants).length > 0)
		{
			menuItems.push({
				text: '@<?=GetMessageJs('BPMC_PD_CONSTANTS')?>',
				items: extractMenuItem(window.arWorkflowGlobalConstants, 'GlobalConst')
			});
		}

		if (typeof window.arDocumentFields !== 'undefined')
		{
			menuItems.push({
				text: '<?=GetMessageJs('BPMC_PD_DOCUMENT_FIELDS')?>',
				items: extractMenuItem(arDocumentFields, 'Document')
			});
		}

		if (BX.Bizproc.Selector)
		{
			var results = BX.Bizproc.Selector.getActivitiesItems(true).map(function(item) {
				return {
					text: BX.util.htmlspecialchars(item.text + ' ('+item.description+')'),
					propertyObject: item.propertyObject,
					propertyField: item.propertyField,
					onclick: clickHandler
				}
			});
			if (results.length)
			{
				menuItems.push({
					text: '<?=GetMessageJs('BPMC_PD_ACTIVITY_RESULTS')?>',
					items: results
				});
			}
		}

		BX.PopupMenu.show(
			BX.util.getRandomString(),
			element,
			menuItems,
			{
				zIndex: 200,
				autoHide: true,
				offsetLeft: (BX.pos(element)['width'] / 2),
				angle: { position: 'top', offset: 0 },
				maxWidth: 300,
				maxHeight: 500,
				events: {
					onPopupClose: function ()
					{
						this.destroy();
					}
				}
			}
		);
	}

	function BPMixedConditionRenderField(conditionContainer)
	{
		var cell = conditionContainer.querySelector('[data-role="value-cell"]');
		var ind = conditionContainer.getAttribute('data-index');
		var property = BPMixedConditionGetProperty(
			conditionContainer.getAttribute('data-object'),
			conditionContainer.getAttribute('data-field')
		);

		if (!property)
		{
			return;
		}

		objFieldsPVC.GetFieldInputControl(
			property,
			'',
			{
				Field: "mixed_condition_value_" +ind,
				Form:'<?= $formName ?>'
			},
			function(v){
				cell.innerHTML = v;
				if (typeof BX.Bizproc.Selector !== 'undefined')
				{
					BX.Bizproc.Selector.initSelectors(cell);
				}
			},
			true
		);
	}

	function BPMixedConditionRenderValue(conditionContainer, operator)
	{
		operator = operator || conditionContainer.querySelector('[data-role="operator-selector"]').value;
		var hidden = (operator === 'empty' || operator === '!empty');
		var valueRow = conditionContainer.querySelector('[data-role="value-row"]');

		valueRow.style.display = hidden ? 'none' : '';
		if (!hidden)
		{
			BPMixedConditionRenderField(conditionContainer);
		}
	}

	function BPMixedConditionGetProperty(object, field)
	{
		switch (object)
		{
			case 'Template':
				return window.arWorkflowParameters[field];
				break;
			case 'Variable':
				return window.arWorkflowVariables[field];
				break;
			case 'Constant':
				return window.arWorkflowConstants[field];
				break;
			case 'GlobalConst':
				return window.arWorkflowGlobalConstants[field];
				break;
			case 'Document':
				return arDocumentFields[field];
				break;
			default:
				var results = BX.Bizproc.Selector.getActivitiesItems();
				for (var i = 0; i < results.length; ++i)
				{
					if (results[i].propertyObject === object && results[i].propertyField === field)
					{
						return results[i].property;
					}
				}
		}

		return null;
	}

	function BPMixedConditionChange(select)
	{
		BPMixedConditionRenderValue(select.closest('tbody'), select.value);
	}

	function BPMixedConditionAdd(element)
	{
		var addrowTr = element.closest('tbody');
		var tbody = BX.create('tbody');
		var i = 0;

		var newRow = tbody.insertRow(i);
		var newCell = newRow.insertCell(-1);
		newCell.width = "40%";
		newCell.className = "adm-detail-content-cell-l";
		newCell.align = "right";
		var newSelect = document.createElement("select");
		newSelect.name = "mixed_condition["+bpmc_counter+"][joiner]";
		newSelect.options[0] = new Option("<?= GetMessage("BPMC_PD_AND") ?>", "0");
		newSelect.options[1] = new Option("<?= GetMessage("BPMC_PD_OR") ?>", "1");
		newCell.appendChild(newSelect);

		var newCell = newRow.insertCell(-1);
		newCell.width = "60%";
		newCell.className = "adm-detail-content-cell-r";
		newCell.align = "right";
		newCell.innerHTML = '<a href="#" onclick="BPMixedConditionDelete(this); return false;"><?= GetMessageJS("BPMC_PD_DELETE") ?></a>';

		var newRow = tbody.insertRow(i + 1);
		var newCell = newRow.insertCell(-1);
		newCell.width = "40%";
		newCell.className = "adm-detail-content-cell-l";
		newCell.align = "right";
		newCell.innerHTML = "<?= GetMessage("BPMC_PD_FIELD") ?>:";
		var newCell = newRow.insertCell(-1);
		newCell.width = "60%";
		newCell.className = "adm-detail-content-cell-r";
		newCell.innerHTML = '<a href="#" onclick="BPMixedConditionChooseTarget(this); return false;"><?=GetMessageJS('BPMC_PD_FIELD_CHOOSE')?></a>';

		newCell.appendChild(BX.create('input', {attrs: {
			type: 'hidden', 'data-role': 'condition-object',
				name: 'mixed_condition['+bpmc_counter+'][object]'
		}}));
		newCell.appendChild(BX.create('input', {attrs: {
			type: 'hidden', 'data-role': 'condition-field',
				name: 'mixed_condition['+bpmc_counter+'][field]'
		}}));


		var newRow = tbody.insertRow(i + 2);
		var newCell = newRow.insertCell(-1);
		newCell.width = "40%";
		newCell.className = "adm-detail-content-cell-l";
		newCell.align = "right";
		newCell.innerHTML = "<?= GetMessage("BPMC_PD_CONDITION") ?>:";
		var newCell = newRow.insertCell(-1);
		newCell.width = "60%";
		newCell.className = "adm-detail-content-cell-r";
		var newSelect = document.createElement("select");
		newSelect.name = "mixed_condition["+bpmc_counter+"][operator]";
		newSelect.onchange = function()
		{
			BPMixedConditionChange(this)
		};
		<?
			$i = -1;
			foreach ($arC as $key => $value)
			{
			$i++;
			?>newSelect.options[<?= $i ?>] = new Option("<?= htmlspecialcharsbx($value) ?>", "<?= htmlspecialcharsbx($key) ?>");
		<?
		}
		?>
		newSelect.value = '!empty';
		newSelect.setAttribute('data-role', 'operator-selector');
		newCell.appendChild(newSelect);

		var newRow = tbody.insertRow(i + 3);
		newRow.setAttribute('data-role', 'value-row');
		newRow.style.display = 'none';
		var newCell = newRow.insertCell(-1);
		newCell.width = "40%";
		newCell.className = "adm-detail-content-cell-l";
		newCell.align = "right";
		newCell.innerHTML = "<?= GetMessage("BPMC_PD_VALUE") ?>:";
		var newCell = newRow.insertCell(-1);
		newCell.width = "60%";
		newCell.className = "adm-detail-content-cell-r";
		newCell.textContent = '...';
		newCell.setAttribute('data-role', 'value-cell');

		tbody.setAttribute('data-index', bpmc_counter);
		bpmc_counter++;
		addrowTr.parentNode.insertBefore(tbody, addrowTr);
	}

	function BPMixedConditionDelete(element)
	{
		var target = element.closest('tbody');
		BX.remove(target);
	}
</script>