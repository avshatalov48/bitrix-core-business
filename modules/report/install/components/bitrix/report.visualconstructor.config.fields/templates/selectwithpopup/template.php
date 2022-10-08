<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Valuable\CustomDropDown $field */
$field = $arResult['CONFIGURATION_FIELD'];
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$value = $field->getValue();

$fieldName = $field->getName();
$fieldId = $field->getId();
$options = $field->getOptions();

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);
?>

<div class="report-configuration-item report-configuration-item-inline report-configuration-custom-select-item">
	<?php if ($field->isDisplayLabel()): ?>
		<div class="report-configuration-col-title">
			<div class="report-configuration-label">
				<label for="<?= $fieldId ?>"><?= $field->getLabel(); ?></label>
			</div>
		</div>
	<?php endif; ?>

	<div class="report-configuration-col-content">
		<div class="report-configuration-content-center" id="<?= $fieldId ?>">
			<div class="report-field-custom-select" data-role="visualconstructor-field-custom-select"><?= $options[$value] ? $options[$value] : $options[$field->getDefaultValue()] ?></div>
			<input type="hidden" data-role="visualconstructor-field-custom-select-value" name="<?= $fieldName ?>" value="<?= $value ?>" <?= $field->getRenderedDataAttributes(); ?>>
		</div>
	</div>
</div>


<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.SelectWithPopup({
		fieldScope: BX("<?=$fieldId?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>,
		optionList: <?=CUtil::PhpToJSObject($options)?>,
		value: <?=CUtil::PhpToJSObject($value)?>
	});
</script>