<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
/** @var \Bitrix\ImOpenLines\Integrations\Report\VisualConstructor\Fields\Valuable\DropDownResponsible $field */
$field = $arResult['CONFIGURATION_FIELD'];
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$configurationValue = $field->getValue();
$fieldName = $field->getName();
$id = $field->getId();
$fullOptions = $field->getOptions();
$linesOperators = $field->getLinesOperators();
$idOpenLinesOptions = $field->getOpenLines()->getId();
$idSelectLineValue = $field->getOpenLines()->getValue();

if((int)$idSelectLineValue > 0)
{
	$options = $linesOperators[(int)$idSelectLineValue];
}
else
{
	$options = $fullOptions;
}

?>
<div class="report-configuration-item report-configuration-base-select-field-item-responsible">
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
				<?foreach ($options as $value => $title): ?>
					<option <?= ($configurationValue === (string)$value) ? 'selected' : '' ?>
							value="<?= $value ?>"><?= $title ?></option>
				<?endforeach; ?>
			</select>
		</div>
	</div>
</div>


<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.DropDownResponsible({
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>,
		fullOptions:  <?=CUtil::PhpToJSObject($fullOptions)?>,
		linesOperators:  <?=CUtil::PhpToJSObject($linesOperators)?>,
		idOpenLinesOptions:  <?=CUtil::PhpToJSObject($idOpenLinesOptions);?>
	});
</script>
