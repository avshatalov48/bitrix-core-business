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

<div class="report-configuration-item report-configuration-color-picker-item">
	<?php if ($field->isDisplayLabel()): ?>
		<div class="report-configuration-col-title">
			<div class="report-configuration-label">
				<?= $field->getLabel() ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="report-configuration-col-content">
		<div class="report-configuration-content-center">
			<div class="report-color-picker" id="color-picker-<?= $id ?>">
				<div class="report-color-picker-wrapper" data-role="visualconstructor-fields-picker">
					<div class="report-color-picker-color" data-role="visualconstructor-fields-picker-preview" style="background-color: <?= $field->getValue() ?>"></div>
					<input class="report-color-picker-input" data-role="visualconstructor-color-picker-input" id="<?= $id ?>" name="<?= $fieldName ?>" value="<?= $fieldValue ?>">
					<div class="report-color-picker-reset" data-role="visualconstructor-fields-picker-reset"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.ColorPicker({
		defaultColor: "<?=$field->getDefaultValue()?>",
		fieldScope: BX("color-picker-<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>
	});
</script>