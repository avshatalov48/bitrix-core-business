<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Valuable\ColorPicker $field */
$field = $arResult['CONFIGURATION_FIELD'];
$fieldValue = $field->getValue();
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$fieldName = $field->getName();
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$id = $field->getId();
\CJSCore::init("color_picker");
?>
<div class="report-configuration-item report-configuration-simple-colorpicker-item <?= $field->isPickerFieldHidden() ? 'report-configuration-simple-colorpicker-item-hidden' : '' ?>">
	<div class="report-configuration-col-content">
		<div class="report-configuration-content-center">
			<div class="report-color-picker" id="<?= $id ?>">
				<div class="report-color-picker-wrapper" data-role="visualconstructor-fields-picker">
					<div class="report-configuration-color-picker-item"></div>
					<input class="report-color-picker-input" data-role="visualconstructor-color-picker-input" id="<?= $id ?>_input_field" name="<?= $fieldName ?>" value="<?= $fieldValue ?>">
				</div>
			</div>
		</div>
	</div>
</div>


<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.SimpleColorPicker({
		defaultColor: "<?=$field->getDefaultValue()?>",
		value: "<?=$field->getValue()?>",
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>
	});
</script>