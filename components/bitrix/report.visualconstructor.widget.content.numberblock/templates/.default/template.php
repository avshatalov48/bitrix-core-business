<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load('ui.design-tokens');

/** @var array $arResult
 *  CALCULATION_RESULT @see \Bitrix\Report\VisualConstructor\IReportSingleData::getSingleData()
 *  WIDGET @see \Bitrix\Report\VisualConstructor\Controller\Widget
 */
$calculateResult = $arResult['CALCULATION_RESULT'];
$unitOfMeasurement[0] = !empty($calculateResult['data'][0]['config']['unitOfMeasurement']) ? $calculateResult['data'][0]['config']['unitOfMeasurement'] : '';
$unitOfMeasurement[1] = !empty($calculateResult['data'][1]['config']['unitOfMeasurement']) ? $calculateResult['data'][1]['config']['unitOfMeasurement'] : '';
$unitOfMeasurement[2] = !empty($calculateResult['data'][2]['config']['unitOfMeasurement']) ? $calculateResult['data'][2]['config']['unitOfMeasurement'] : '';

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
?>
<div class="report-widget-number-block-container">
	<div class="report-widget-number-block-row-container">
		<div class="report-widget-number-block-item report-widget-number-block-first-block" style="background-color: <?= $calculateResult['data'][0]['config']['color'] ?>">
			<div class="report-widget-number-block-value-container">
				<span class="report-value"><?= $calculateResult['data'][0]['value'] ?></span>
				<span class="report-value-unit-of-measurement"><?= $unitOfMeasurement[0] ?></span>
			</div>
		</div>
	</div>
	<div class="report-widget-number-block-row-container">
		<div class="report-widget-number-block-item report-widget-number-block-second-block" style="background-color: <?= $calculateResult['data'][1]['config']['color'] ?>">
			<div class="report-widget-number-block-item-header">
				<div class="report-widget-number-block-title-container"><?= $calculateResult['data'][1]['title'] ?></div>
			</div>
			<div class="report-widget-number-block-value-container">
				<span class="report-value"><?= $calculateResult['data'][1]['value'] ?></span>
				<span class="report-value-unit-of-measurement"><?= $unitOfMeasurement[1] ?></span>
			</div>
		</div>
		<div class="report-widget-number-block-item report-widget-number-block-third-block" style="background-color: <?= $calculateResult['data'][2]['config']['color'] ?>">
			<div class="report-widget-number-block-item-header">
				<div class="report-widget-number-block-title-container"><?= $calculateResult['data'][2]['title'] ?></div>
			</div>
			<div class="report-widget-number-block-value-container">
				<span class="report-value"><?= $calculateResult['data'][2]['value'] ?></span>
				<span class="report-value-unit-of-measurement"><?= $unitOfMeasurement[2] ?></span>
			</div>
		</div>
	</div>
</div>

<script>
	BX.Report.Dashboard.Content.Html.ready(function(context) {
		new BX.Report.VC.SetFontSize({node: context.querySelectorAll('.report-widget-number-block-value-container')});
	});
</script>