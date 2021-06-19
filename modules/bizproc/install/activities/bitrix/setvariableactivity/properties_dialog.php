<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<tr>
	<td colspan="2">
		<table width="100%" border="0" cellpadding="2" cellspacing="2" id="bwfvc_addrow_table">
			<?
			$defaultFieldValue = "";
			$t = array_keys($arVariables);
			if (count($t) > 0)
				$defaultFieldValue = $t[0];
			$ind = -1;
			foreach ($arCurrentValues as $variableKey => $variableValue)
			{
				if (!array_key_exists($variableKey, $arVariables))
					continue;
				if ($defaultFieldValue == '')
					$defaultFieldValue = $variableKey;
				$ind++;
				?>
				<tr id="delete_row_<?= $ind ?>">
					<td>
						<select name="variable_field_<?= $ind ?>" onchange="BWFVCChangeFieldType(<?= $ind ?>, this.options[this.selectedIndex].value, null)">
							<?
							foreach ($arVariables as $k => $v)
							{
								?><option value="<?= htmlspecialcharsbx($k) ?>"<?= ($k == $variableKey) ? " selected" : "" ?>><?= htmlspecialcharsbx($v["Name"]) ?></option><?
							}
							?>
						</select>
					</td>
					<td>=</td>
					<td id="id_td_variable_value_<?= $ind ?>">
						<input type="text" name="<?= htmlspecialcharsbx($variableKey) ?>" value="...">
					</td>
					<td align="right">
						<a href="#" onclick="BWFVCDeleteCondition(<?= $ind ?>); return false;"><?= GetMessage("BPSVA_PD_DELETE") ?></a>
					</td>
				</tr>
				<?
			}
			?>
		</table>

		<?= CAdminCalendar::ShowScript() ?>

		<script language="text/javascript">
		var bwfvc_arFieldTypes = {<?
		$fl = false;
		foreach ($arVariables as $key => $value)
		{
			if ($fl)
				echo ",";
			echo "'".CUtil::JSEscape($key)."':'".CUtil::JSEscape($value["Type"])."'";
			$fl = true;
		}
		?>};
		var bwfvc_arFieldOptions = {<?
		$fl = false;
		foreach ($arVariables as $key => $value)
		{
			if ($value["Type"] != "select")
				continue;
			if ($fl)
				echo ",";
			echo "'".CUtil::JSEscape($key)."':{";
			$ix = 0;
			foreach ($value["Options"] as $k => $v)
			{
				if ($ix > 0)
					echo ",";
				echo $ix.":{0:'".CUtil::JSEscape($k)."',1:'".CUtil::JSEscape($v)."'}";
				$ix++;
			}
			echo "}";

			$fl = true;
		}
		?>};
		BX.namespace('BX.Bizproc');
		function BWFVCChangeFieldType(ind, field, value)
		{
			BX.showWait();
			var valueTd = document.getElementById('id_td_variable_value_' + ind);

			objFieldsVars.GetFieldInputControl(
				objFieldsVars.arDocumentFields[field],
				value,
				{'Field':field, 'Form':'<?= $formName ?>'},
				function(v){
					valueTd.innerHTML = v;

					if (typeof BX.Bizproc.Selector !== 'undefined')
						BX.Bizproc.Selector.initSelectors(valueTd);

					BX.closeWait();
				},
				true
			);
		}

		var bwfvc_counter = <?= $ind ?>;

		function BWFVCAddCondition()
		{
			var addrowTable = document.getElementById('bwfvc_addrow_table');

			bwfvc_counter++;
			var newRow = addrowTable.insertRow(-1);
			newRow.id = "delete_row_" + bwfvc_counter;

			var newCell = newRow.insertCell(-1);
			var newSelect = document.createElement("select");
			newSelect.setAttribute('bwfvc_counter', bwfvc_counter);
			newSelect.onchange = function(){BWFVCChangeFieldType(this.getAttribute("bwfvc_counter"), this.options[this.selectedIndex].value, null)};
			newSelect.name = "variable_field_" + bwfvc_counter;
			<?
			$i = -1;
			foreach ($arVariables as $key => $value)
			{
				$i++;
				?>newSelect.options[<?= $i ?>] = new Option("<?= CUtil::JSEscape($value["Name"]) ?>", "<?= CUtil::JSEscape($key) ?>");
				<?
			}
			?>
			newCell.appendChild(newSelect);

			var newCell = newRow.insertCell(-1);
			newCell.innerHTML = "=";

			var newCell = newRow.insertCell(-1);
			newCell.id = "id_td_variable_value_" + bwfvc_counter;
			newCell.innerHTML = "";

			var newCell = newRow.insertCell(-1);
			newCell.align="right";
			newCell.innerHTML = '<a href="#" onclick="BWFVCDeleteCondition(' + bwfvc_counter + '); return false;"><?= GetMessage("BPSVA_PD_DELETE") ?></a>';

			BWFVCChangeFieldType(bwfvc_counter, '<?= CUtil::JSEscape($defaultFieldValue) ?>', null);
		}

		function BWFVCDeleteCondition(ind)
		{
			var addrowTable = document.getElementById('bwfvc_addrow_table');

			var cnt = addrowTable.rows.length;
			for (i = 0; i < cnt; i++)
			{
				if (addrowTable.rows[i].id != 'delete_row_' + ind)
					continue;

				addrowTable.deleteRow(i);

				break;
			}
		}

		<?
		$i = -1;
		foreach ($arCurrentValues as $variableKey => $variableValue)
		{
			if (!array_key_exists($variableKey, $arVariables))
				continue;

			$i++;
			?>
			BWFVCChangeFieldType(<?= $i ?>, '<?= CUtil::JSEscape($variableKey) ?>', <?= CUtil::PhpToJSObject($variableValue) ?>);
			<?
		}

		if ($i < 0)
		{
			?>BWFVCAddCondition();<?
		}
		?>
		</script>
		<a href="#" onclick="BWFVCAddCondition(); return false;"><?= GetMessage("BPSVA_PD_ADD") ?></a>

	</td>
</tr>