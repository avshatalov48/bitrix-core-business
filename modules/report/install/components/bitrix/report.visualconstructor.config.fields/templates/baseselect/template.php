<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown $field */
$field = $arResult['CONFIGURATION_FIELD'];
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$configurationValue = $field->getValue();
$fieldName = $field->getName();
$id = $field->getId();
?>
<div class="report-configuration-item report-configuration-base-select-field-item">
	<?php if ($field->isDisplayLabel()): ?>
		<div class="report-configuration-col-title">
			<div class="report-configuration-label">
				<label for="<?= $id ?>"><?= $field->getLabel() ?></label>
			</div>
		</div>
	<?php endif; ?>
	<div class="report-configuration-col-content">
		<div class="report-configuration-content-center">
			<select class="report-configuration-field-select" id="<?= $id ?>" name="<?= $fieldName ?>">
				<?php foreach ($field->getOptions() as $value => $title): ?>
					<option <?= ($configurationValue === (string)$value) ? 'selected' : '' ?>
							value="<?= $value ?>"><?= $title ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
</div>


<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.DropDown({
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>
	});
</script>

