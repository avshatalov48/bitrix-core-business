<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<?
$arC = array(
	"=" => GetMessage("BPFC_PD_EQ"),
	">" => GetMessage("BPFC_PD_GT"),
	">=" => GetMessage("BPFC_PD_GE"),
	"<" => GetMessage("BPFC_PD_LT"),
	"<=" => GetMessage("BPFC_PD_LE"),
	"!=" => GetMessage("BPFC_PD_NE"),
	"in" => GetMessage("BPFC_PD_IN"),
	"contain" => GetMessage("BPFC_PD_CONTAIN")
);

$arVariableConditionCount = array(1);
if (array_key_exists("variable_condition_count", $arCurrentValues) && strlen($arCurrentValues["variable_condition_count"]) > 0)
	$arVariableConditionCount = explode(",", $arCurrentValues["variable_condition_count"]);

$arCurrentValues["variable_condition_count"] = "";
$bwfvcCounter = 0;
$defaultFieldValue = "";
foreach ($arVariableConditionCount as $i)
{
	if (intval($i)."!" != $i."!")
		continue;

	$i = intval($i);
	if (strlen($arCurrentValues["variable_condition_count"]) > 0)
	{
		$arCurrentValues["variable_condition_count"] .= ",";
		?>
		<tr id="bwfvc_deleterow_tr_<?= $i ?>">
			<td align="right" width="40%" class="adm-detail-content-cell-l">
				<select name="variable_condition_joiner_<?=$i?>">
					<option value="0"><?= GetMessage("BPFC_PD_AND") ?></option>
					<option value="1" <?if($arCurrentValues["variable_condition_joiner_".$i]==1) echo 'selected'?>><?= GetMessage("BPFC_PD_OR") ?></option>
				</select>
			</td>
			<td align="right" width="60%" class="adm-detail-content-cell-r"><a href="#" onclick="BWFVCDeleteCondition(<?= $i ?>); return false;"><?= GetMessage("BPFC_PD_DELETE") ?></a></td>
		</tr>
		<?
	}
	$arCurrentValues["variable_condition_count"] .= $i;
	if ($i > $bwfvcCounter)
		$bwfvcCounter = $i;
	?>
	<tr>
		<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPFC_PD_FIELD") ?>:</td>
		<td width="60%" class="adm-detail-content-cell-r">
			<select name="variable_condition_field_<?= $i ?>" data-old="<?=htmlspecialcharsbx($arCurrentValues["variable_condition_field_".$i])?>" onchange="BWFVCSetAndChangeFieldType(<?= $i ?>, this)">
				<?if ($arProperties):?><optgroup label="<?=GetMessage('BPFC_PD_PARAMS')?>"><?endif;
				foreach ($arProperties as $key => $value)
				{
					if (strlen($defaultFieldValue) <= 0)
						$defaultFieldValue = $key;
					?><option value="<?= htmlspecialcharsbx($key) ?>"<?= ($arCurrentValues["variable_condition_field_".$i] == $key) ? " selected" : "" ?>><?= htmlspecialcharsbx($value["Name"]) ?></option><?
				}
				?>
				<?if ($arProperties):?></optgroup><?endif;
				if ($arVariables):?><optgroup label="<?=GetMessage('BPFC_PD_VARS')?>"><?endif;
				foreach ($arVariables as $key => $value)
				{
					if (strlen($defaultFieldValue) <= 0)
						$defaultFieldValue = $key;
					?><option value="<?= htmlspecialcharsbx($key) ?>"<?= ($arCurrentValues["variable_condition_field_".$i] == $key) ? " selected" : "" ?>><?= htmlspecialcharsbx($value["Name"]) ?></option><?
				}
				?>
				<?if ($arVariables):?></optgroup><?endif;?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPFC_PD_CONDITION") ?>:</td>
		<td width="60%" class="adm-detail-content-cell-r">
			<select name="variable_condition_condition_<?= $i ?>">
				<?
				foreach ($arC as $key => $value)
				{
					?><option value="<?= $key ?>"<?= ($arCurrentValues["variable_condition_condition_".$i] == $key) ? " selected" : "" ?>><?= $value ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPFC_PD_VALUE") ?>:</td>
		<td width="60%" id="id_td_variable_condition_value_<?= $i ?>" class="adm-detail-content-cell-r">
			<input type="text" name="variable_condition_value_<?= $i ?>" value="<?= htmlspecialcharsbx((string)$arCurrentValues["variable_condition_value_".$i]) ?>">
		</td>
	</tr>
	<?
}
?>
<tr id="bwfvc_addrow_tr">
	<td class="adm-detail-content-cell-l"></td>
	<td class="adm-detail-content-cell-r">
		<?= CAdminCalendar::ShowScript() ?>
		<script language="JavaScript">
		var bwfvc_arFieldTypes = {<?
		$fl = false;
		foreach ($arProperties as $key => $value)
		{
			if ($fl)
				echo ",";
			echo "'".CUtil::JSEscape($key)."':'".CUtil::JSEscape($value["Type"])."'";
			$fl = true;
		}
		foreach ($arVariables as $key => $value)
		{
			if ($fl)
				echo ",";
			echo "'".CUtil::JSEscape($key)."':'".CUtil::JSEscape($value["Type"])."'";
			$fl = true;
		}
		?>};

		var bwfvc_counter = <?= $bwfvcCounter + 1 ?>;
		BX.namespace('BX.Bizproc');

		function BWFVCChangeFieldType(ind, field, value)
		{
			BX.showWait();
			var valueTd = document.getElementById('id_td_variable_condition_value_' + ind);

			objFieldsPVC.GetFieldInputControl(
				objFieldsPVC.arDocumentFields[field],
				value,
				{'Field':"variable_condition_value_" + ind, 'Form':'<?= $formName ?>'},
				function(v){
					valueTd.innerHTML = v;

					if (typeof BX.Bizproc.Selector !== 'undefined')
						BX.Bizproc.Selector.initSelectors(valueTd);

					BX.closeWait();
				},
				true
			);
		}

		function BWFVCSetAndChangeFieldType(ind, select)
		{
			BX.showWait();

			var field = select.value;
			var oldField = select.getAttribute('data-old');

			if (!oldField)
				oldField = select.options[0].value;

			objFieldsPVC.GetFieldInputValue(
					objFieldsPVC.arDocumentFields[oldField],
					{'Field':"variable_condition_value_" + ind, 'Form':'<?= $formName ?>'},
					function(value)
					{
						if (typeof value == "object")
							value = value[0];

						select.setAttribute('data-old', field);
						BWFVCChangeFieldType(ind, field, value);
						BX.closeWait();
					}
			);
		}

		function BWFVCAddCondition()
		{
			var addrowTr = document.getElementById('bwfvc_addrow_tr');
			var parentAddrowTr = addrowTr.parentNode;

			var cnt = parentAddrowTr.rows.length;
			for (i = 0; i < cnt; i++)
			{
				if (parentAddrowTr.rows[i].id != "bwfvc_addrow_tr")
					continue;

				var newRow = parentAddrowTr.insertRow(i);
				newRow.id = "bwfvc_deleterow_tr_" + bwfvc_counter;
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.className="adm-detail-content-cell-l";
				newCell.align="right";
				var newSelect = document.createElement("select");
				newSelect.name = "variable_condition_joiner_" + bwfvc_counter;
				newSelect.options[0] = new Option("<?= GetMessage("BPFC_PD_AND") ?>", "0");
				newSelect.options[1] = new Option("<?= GetMessage("BPFC_PD_OR") ?>", "1");
				newCell.appendChild(newSelect);

				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				newCell.className="adm-detail-content-cell-r";
				newCell.align="right";
				newCell.innerHTML = '<a href="#" onclick="BWFVCDeleteCondition(' + bwfvc_counter + '); return false;"><?= GetMessage("BPFC_PD_DELETE") ?></a>';

				var newRow = parentAddrowTr.insertRow(i + 1);
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.className="adm-detail-content-cell-l";
				newCell.align="right";
				newCell.innerHTML = "<?= GetMessage("BPFC_PD_FIELD") ?>:";
				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				newCell.className="adm-detail-content-cell-r";
				var newSelect = document.createElement("select");
				newSelect.setAttribute('bwfvc_counter', bwfvc_counter);
				newSelect.onchange = function(){BWFVCSetAndChangeFieldType(this.getAttribute("bwfvc_counter"), this)};
				newSelect.name = "variable_condition_field_" + bwfvc_counter;
				<?
				$i = -1;
				foreach ($arProperties as $key => $value)
				{
					$i++;
					?>newSelect.options[<?= $i ?>] = new Option("<?= htmlspecialcharsbx($value["Name"]) ?>", "<?= htmlspecialcharsbx($key) ?>");
					<?
				}
				foreach ($arVariables as $key => $value)
				{
					$i++;
					?>newSelect.options[<?= $i ?>] = new Option("<?= htmlspecialcharsbx($value["Name"]) ?>", "<?= htmlspecialcharsbx($key) ?>");
					<?
				}
				?>
				newCell.appendChild(newSelect);

				var newRow = parentAddrowTr.insertRow(i + 2);
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.className="adm-detail-content-cell-l";
				newCell.align="right";
				newCell.innerHTML = "<?= GetMessage("BPFC_PD_CONDITION") ?>:";
				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				newCell.className="adm-detail-content-cell-r";
				var newSelect = document.createElement("select");
				newSelect.name = "variable_condition_condition_" + bwfvc_counter;
				<?
				$i = -1;
				foreach ($arC as $key => $value)
				{
					$i++;
					?>newSelect.options[<?= $i ?>] = new Option("<?= htmlspecialcharsbx($value) ?>", "<?= htmlspecialcharsbx($key) ?>");
					<?
				}
				?>
				newCell.appendChild(newSelect);

				var newRow = parentAddrowTr.insertRow(i + 3);
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.className="adm-detail-content-cell-l";
				newCell.align="right";
				newCell.innerHTML = "<?= GetMessage("BPFC_PD_VALUE") ?>:";
				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				newCell.className="adm-detail-content-cell-r";
				newCell.id="id_td_variable_condition_value_" + bwfvc_counter;
				var newSelect = document.createElement("input");
				newSelect.type = "text";
				newSelect.name = "variable_condition_value_" + bwfvc_counter;
				newCell.appendChild(newSelect);

				BWFVCChangeFieldType(bwfvc_counter, '<?= CUtil::JSEscape($defaultFieldValue) ?>', null);

				document.getElementById('id_variable_condition_count').value += "," + bwfvc_counter;
				bwfvc_counter++;

				break;
			}
		}

		function BWFVCDeleteCondition(ind)
		{
			var deleterowTr = document.getElementById('bwfvc_deleterow_tr_' + ind);
			var parentDeleterowTr = deleterowTr.parentNode;

			var cnt = parentDeleterowTr.rows.length;
			for (i = 0; i < cnt; i++)
			{
				if (parentDeleterowTr.rows[i].id != 'bwfvc_deleterow_tr_' + ind)
					continue;

				parentDeleterowTr.deleteRow(i + 3);
				parentDeleterowTr.deleteRow(i + 2);
				parentDeleterowTr.deleteRow(i + 1);
				parentDeleterowTr.deleteRow(i);

				var value = document.getElementById('id_variable_condition_count').value;
				var ar = value.split(",");
				value = "";
				for (j = 0; j < ar.length; j++)
				{
					if (ar[j] != ind)
					{
						if (value.length > 0)
							value += ",";
						value += ar[j];
					}
				}
				document.getElementById('id_variable_condition_count').value = value;

				break;
			}
		}

		<?
		foreach ($arVariableConditionCount as $i)
		{
			if (intval($i)."!" != $i."!")
				continue;

			$i = intval($i);
			$v = (array_key_exists("variable_condition_field_".$i, $arCurrentValues) ? $arCurrentValues["variable_condition_field_".$i] : $defaultFieldValue);
			?>
			BWFVCChangeFieldType(<?= $i ?>, '<?= CUtil::JSEscape($v) ?>', <?= CUtil::PhpToJSObject($arCurrentValues["variable_condition_value_".$i]) ?>);
			<?
		}
		?>
		</script>
		<input type="hidden" name="variable_condition_count" id="id_variable_condition_count" value="<?= htmlspecialcharsbx($arCurrentValues["variable_condition_count"]) ?>">
		<a href="#" onclick="BWFVCAddCondition(); return false;"><?= GetMessage("BPFC_PD_ADD") ?></a>
	</td>
</tr>
