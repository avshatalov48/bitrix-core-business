<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Valuable\TimePeriod $field */
$field = $arResult['CONFIGURATION_FIELD'];
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$configurationValue = $field->getValue();

$fieldName = $field->getName();
$fieldId = $field->getId();


$monthList = $field->getMonthList();
$quarterList = $field->getQuarterList();
$typeList = $field->getTypeList();
$yearList = $field->getYearList();


$typeValue = !empty($configurationValue['type']) ? $configurationValue['type'] : $field::DEFAULT_TIME_PERIOD_TYPE;
$yearValue = !empty($configurationValue['year']) ? $configurationValue['year'] : $field->getCurrentYear();
$quarterValue = !empty($configurationValue['quarter']) ? $configurationValue['quarter'] : $field->getCurrentQuarter();
$monthValue = !empty($configurationValue['month']) ? $configurationValue['month'] : $field->getCurrentMonth();


?>
<div class="report-configuration-item report-configuration-item-inline report-configuration-time-period-item">
	<?php if ($field->isDisplayLabel()): ?>
		<div class="report-configuration-col-title">
			<div class="report-configuration-label">
				<label for="<?= $fieldId ?>"><?= $field->getLabel(); ?></label>
			</div>
		</div>
	<?php endif; ?>
	<div class="report-configuration-col-content">
		<div class="report-configuration-content-center" id="<?= $fieldId ?>">

			<div class="report-configuration-time-period-field-control report-field-time-period-type" data-role="visualconstructor-field-time-period-type"><?= $typeList[$typeValue] ?></div>
			<div class="report-configuration-time-period-field-control report-field-time-period-quarter <?= ('QUARTER' == $typeValue) ? 'report-field-time-period-sub-field-visible' : '' ?>" data-role="visualconstructor-field-time-period-quarter"><?= $quarterList[$quarterValue] ?></div>
			<div class="report-configuration-time-period-field-control report-field-time-period-month <?= ('MONTH' == $typeValue) ? 'report-field-time-period-sub-field-visible' : '' ?>" data-role="visualconstructor-field-time-period-month"><?= $monthList[$monthValue] ?></div>
			<div class="report-configuration-time-period-field-control report-field-time-period-year <?= (in_array($typeValue, array('MONTH', 'QUARTER', 'YEAR'))) ? 'report-field-time-period-sub-field-visible' : '' ?>" data-role="visualconstructor-field-time-period-year"><?= $yearList[$yearValue] ?></div>


			<input type="hidden" data-role="visualconstructor-field-time-period-type-value" name="<?= $fieldName ?>[type]" id="<?= $fieldId ?>_type" value="<?= $typeValue ?>">
			<input type="hidden" data-role="visualconstructor-field-time-period-quarter-value" name="<?= $fieldName ?>[quarter]" id="<?= $fieldId ?>_quarter" value="<?= $quarterValue ?>">
			<input type="hidden" data-role="visualconstructor-field-time-period-month-value" name="<?= $fieldName ?>[month]" id="<?= $fieldId ?>_month" value="<?= $monthValue ?>">
			<input type="hidden" data-role="visualconstructor-field-time-period-year-value" name="<?= $fieldName ?>[year]" id="<?= $fieldId ?>_year" value="<?= $yearValue ?>">
		</div>
	</div>
</div>

<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.TimePeriod({
		fieldScope: BX("<?=$fieldId?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>,
		typeList: <?=CUtil::PhpToJSObject($typeList)?>,
		yearList: <?=CUtil::PhpToJSObject($yearList)?>,
		quarterList: <?=CUtil::PhpToJSObject($quarterList)?>,
		monthList: <?=CUtil::PhpToJSObject($monthList)?>
	});
</script>