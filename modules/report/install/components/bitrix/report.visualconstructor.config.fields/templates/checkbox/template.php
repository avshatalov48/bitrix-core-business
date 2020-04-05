<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Valuable\CheckBox $field */
$field = $arResult['CONFIGURATION_FIELD'];
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$configurationValue = $field->getValue();
$fieldName = $field->getName();
$id = $field->getId();
?>
<div class="report-configuration-item report-configuration-checkbox-field-item">
	<div class="report-configuration-col-content">
		<input type="checkbox" id="<?= $id ?>" name="<?= $fieldName ?>" <?= ($configurationValue ? 'checked="checked"' : '') ?>>
		<label for="<?= $id ?>"><?= $field->getLabel() ?></label>
	</div>
</div>