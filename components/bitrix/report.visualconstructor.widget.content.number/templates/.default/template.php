<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult
 *  CALCULATION_RESULT @see \Bitrix\Report\VisualConstructor\IReportSingleData::getSingleData()
 */
$calculateResult = $arResult['CALCULATION_RESULT'];
$backgroundColor = $arResult['WIDGET_COLOR'];
$unitOfMeasurement = !empty($calculateResult['data']['config']['unitOfMeasurement']) ? $calculateResult['data']['config']['unitOfMeasurement'] : '';
?>
<div class="report-widget-number-diagram-content">
	<div class="report-widget-number-value">
		<?php if ($calculateResult['data']['targetUrl']): ?>
			<a href="<?= $calculateResult['data']['targetUrl'] ?>">
				<span class="report-value"><?= $calculateResult['data']['value'] ?></span>
				<span class="report-value-unit-of-measurement"><?= $unitOfMeasurement ?></span>
			</a>
		<?php else: ?>
			<span class="report-value"><?= $calculateResult['data']['value'] ?></span>
			<span class="report-value-unit-of-measurement"><?= $unitOfMeasurement ?></span>
		<?php endif; ?>

	</div>
</div>
<script>
	BX.Report.Dashboard.Content.Html.ready(function(context) {
		//new BX.Report.VC.SetFontSize({node: context.querySelectorAll('.report-widget-number-diagram-content .report-value')});
		//new BX.Report.VC.SetFontSize({node: context.querySelectorAll('.report-widget-number-diagram-content .report-value-unit-of-measurement')});
		new BX.VisualConstructor.NumberComponent({
			context: context
		});
	});


</script>