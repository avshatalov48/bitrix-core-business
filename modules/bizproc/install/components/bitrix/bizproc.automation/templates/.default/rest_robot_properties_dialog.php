<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$dialog = $arResult['dialog'];

$data = $dialog->getRuntimeData();
$map = $dialog->getMap();
$activityData = $data['ACTIVITY_DATA'];

$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
$currentValues = $dialog->getCurrentValues();

foreach ($properties as $name => $property):
	$name = $map[$name]['FieldName'];
	$required = CBPHelper::getBool($property['REQUIRED']);
	$multiple = CBPHelper::getBool($property['MULTIPLE']);

	$values = !CBPHelper::isEmptyValue($currentValues[$name]) ? (array)$currentValues[$name] : (array)$property['DEFAULT'];
	if (count($values) < 1)
	{
		$values[] = null;
	}
	if (!$multiple && count($values) > 1)
	{
		$values = array_slice($values, 0, 1);
	}

	$title = \Bitrix\Bizproc\RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID);
	if ($multiple && $property['TYPE'] !== 'user')
	{
		$name .= '[]';
	}
	?>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"><?=htmlspecialcharsbx($title)?>: </span>
		<?
		switch ($property['TYPE'])
		{
			case 'bool':
				foreach ($values as $value):
				?>
				<select class="bizproc-automation-popup-settings-dropdown <?=$multiple?'multiple':''?>" name="<?=htmlspecialcharsbx($name)?>">
					<option value=""><?=GetMessage('BIZPROC_AUTOMATION_NOT_SELECTED')?></option>
					<option value="Y" <?=($value == 'Y') ? 'selected':''?>><?=GetMessage('MAIN_YES')?></option>
					<option value="N" <?=($value == 'N') ? 'selected':''?>><?=GetMessage('MAIN_NO')?></option>
				</select>
				<?
				endforeach;
				break;
			case 'date':
			case 'datetime':
				foreach ($values as $value):
				?>
				<input name="<?=htmlspecialcharsbx($name)?>" type="text" class="bizproc-automation-popup-input bizproc-automation-popup-input-calendar <?=$multiple?'multiple':''?>"
					value="<?=htmlspecialcharsbx($value)?>"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
					data-selector-type="<?=htmlspecialcharsbx($property['TYPE'])?>"
					data-selector-write-mode="replace"
				>
				<?
				endforeach;
				break;
			case 'double':
			case 'int':
				foreach ($values as $value):
				?>
				<input name="<?=htmlspecialcharsbx($name)?>" type="text" class="bizproc-automation-popup-input bizproc-automation-popup-input-numeric <?=$multiple?'multiple':''?>"
					value="<?=htmlspecialcharsbx($value)?>"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
				>
				<?
				endforeach;
				break;
			case 'select':
				$options = isset($property['OPTIONS']) && is_array($property['OPTIONS'])
					? $property['OPTIONS'] : array();
				?>
				<select class="bizproc-automation-popup-settings-dropdown<?=$multiple?'-multiple':''?>" name="<?=htmlspecialcharsbx($name)?>" <?=$multiple ? 'multiple="multiple"' : ''?>>
					<?if (!$multiple):?><option value=""><?=GetMessage('BIZPROC_AUTOMATION_NOT_SELECTED')?></option><?endif;?>
					<?
					foreach ($options as $k => $v)
					{
						echo '<option value="'.htmlspecialcharsbx($k).'"'.(in_array($k,$values) ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
					}
					?>
				</select>
				<?
				break;
			case 'string':
				foreach ($values as $value):
				?>
				<input name="<?=htmlspecialcharsbx($name)?>" type="text" class="bizproc-automation-popup-input <?=$multiple?'multiple':''?>"
					value="<?=htmlspecialcharsbx($value)?>"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
				>
				<?
				endforeach;
				break;
			case 'text':
				foreach ($values as $value):
				?>
				<textarea name="<?=htmlspecialcharsbx($name)?>"
					class="bizproc-automation-popup-textarea <?=$multiple?'multiple':''?>"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
				><?=htmlspecialcharsbx($value)?></textarea>
				<?
				endforeach;
				break;
			case 'user':
				?>
				<div data-role="user-selector" data-config="<?=htmlspecialcharsbx(
						\Bitrix\Main\Web\Json::encode(array(
							'valueInputName' => $name,
							'selected' => \Bitrix\Bizproc\Automation\Helper::prepareUserSelectorEntities(
								$dialog->getDocumentType(),
								$currentValues[$name]
							),
							'multiple' => $multiple,
							'required' => $required,
						))
				)?>"></div>
				<?
				break;
		}
		if ($multiple && $property['TYPE'] !== 'select' && $property['TYPE'] !== 'user'):?>
		<div>
			<a onclick="BPRobotSetField.cloneControl('<?=CUtil::JSEscape($property['TYPE'])?>', '<?=CUtil::JSEscape($name)?>', this.parentNode); return false;" class="bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-thin">
				<?=htmlspecialcharsbx(GetMessage('BIZPROC_AUTOMATION_ADD_MULTIPLE'))?>
			</a>
		</div>
		<?endif;?>
	</div>
	<?
endforeach;
?>
<script>
	BX.ready(function()
	{
		window.BPRobotSetField = {
			cloneControl: function(type, name, node)
			{
				var controlNode;

				switch (type)
				{
					case 'bool':
						controlNode = this.createBoolNode(name);
						break;
					case 'date':
					case 'datetime':
						controlNode = this.createDateNode(name, type);
						break;
					case 'double':
					case 'int':
						controlNode = this.createNumericNode(name, type);
						break;
					case 'string':
						controlNode = this.createStringNode(name);
						break;
					case 'text':
						controlNode = this.createTextNode(name);
				}

				if (controlNode && node.parentNode)
				{
					var wrapper = BX.create('div', {children: [controlNode]});
					var dlg = BX.Bizproc.Automation.Designer.getRobotSettingsDialog();
					if (dlg)
					{
						dlg.template.initRobotSettingsControls(dlg.robot, wrapper);
					}

					node.parentNode.insertBefore(wrapper, node);
				}
			},
			createBoolNode: function(name)
			{
				var div = BX.create('div');
				name = BX.util.htmlspecialchars(name);

				div.innerHTML = '<select class="bizproc-automation-popup-settings-dropdown multiple" name="'+ name + '">'
				+ '<option value=""><?=GetMessageJS('BIZPROC_AUTOMATION_NOT_SELECTED')?></option>'
				+ '<option value="Y"><?=GetMessageJS('MAIN_YES')?></option>'
				+ '<option value="N"><?=GetMessageJS('MAIN_NO')?></option>'
				+ '</select>';

				return div.firstChild;
			},
			createDateNode: function(name, type)
			{
				var div = BX.create('div');
				name = BX.util.htmlspecialchars(name);
				type = BX.util.htmlspecialchars(type);

				div.innerHTML = '<input name="'+name+'" type="text" class="bizproc-automation-popup-input bizproc-automation-popup-input-calendar multiple"'
				+ ' data-role="inline-selector-target" data-selector-type="' + type + '" data-selector-write-mode="replace">';

				return div.firstChild;
			},
			createNumericNode: function(name, type)
			{
				var div = BX.create('div');
				name = BX.util.htmlspecialchars(name);
				type = BX.util.htmlspecialchars(type);

				div.innerHTML = '<input name="'+name+'" type="text" class="bizproc-automation-popup-input bizproc-automation-popup-input-numeric multiple"'
					+ ' data-role="inline-selector-target">';

				return div.firstChild;
			},
			createStringNode: function(name)
			{
				var div = BX.create('div');
				name = BX.util.htmlspecialchars(name);

				div.innerHTML = '<input name="'+name+'" type="text" class="bizproc-automation-popup-input multiple>"'
					+ ' data-role="inline-selector-target">';

				return div.firstChild;
			},
			createTextNode: function(name)
			{
				var div = BX.create('div');
				name = BX.util.htmlspecialchars(name);

				div.innerHTML = '<textarea name="'+name+'" class="bizproc-automation-popup-textarea multiple" data-role="inline-selector-target"></textarea>';

				return div.firstChild;
			}
		}
	})
</script>