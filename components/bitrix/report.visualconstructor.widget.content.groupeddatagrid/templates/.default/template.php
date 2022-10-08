<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult
 *  CALCULATION_RESULT @see \Bitrix\Report\VisualConstructor\IReportSingleData::getSingleData()
 *  WIDGET @see \Bitrix\Report\VisualConstructor\Controller\Widget
 */
$calculateResult = $arResult['CALCULATION_RESULT'];
$backgroundColor = $arResult['WIDGET_COLOR'];

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
?>
<div class="report-widget-triple-data-with-progress" data-role="report-widget-triple-data-with-progress" style="display: block;">
	<div class="report-widget-triple-data-with-progress-wrapper" data-role="report-widget-triple-data-with-progress-wrapper">
		<? foreach ($calculateResult['items'] as $groupKey => $item): ?>
			<div class="report-operator" data-role="report-operator">
				<div class="report-operator-header" data-role="report-operator-header">
					<div class="report-operator-user">
						<div class="report-operator-user-avatar" style="<?= $calculateResult['config']['groupOptions'][$groupKey]['logo'] ? 'background-image: url(' . $calculateResult['config']['groupOptions'][$groupKey]['logo'] . ')' : ''; ?> "></div>
						<div class="report-operator-user-name">
							<span class="report-operator-user-name-link"><?= $calculateResult['config']['groupOptions'][$groupKey]['title'] ?></span>
							<!--                        <span class="report-operator-user-name-position">-->
							<? //=$groupKey//$calculateResult['config']['groupOptions'][$groupKey]['description']?><!--</span>-->
						</div>
					</div>
					<div class="report-operator-wrapper">
						<div class="report-operator-progress">
							<div class="report-operator-progress-line" style="width: <?= $item[0]['value'] ?>%"></div>
							<div class="report-operator-progress-value"><?= $item[0]['value'] ?>%</div>
						</div>
					</div>
				</div>
				<div class="report-operator-content" data-role="report-operator-content">
					<div class="report-operator-content-wrapper">
						<div class="report-operator-statistic">
							<div class="report-operator-statistic-item">
								<div class="report-operator-statistic-item-block">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][1]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[1]['value'] ?></span>
								</div>
								<div class="report-operator-statistic-item-inline">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][2]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[2]['value'] ?></span>
								</div>
								<div class="report-operator-statistic-item-inline">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][3]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[3]['value'] ?></span>
								</div>
							</div>
							<div class="report-operator-statistic-item">
								<div class="report-operator-statistic-item-block">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][4]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[4]['value'] ?></span>
								</div>
								<div class="report-operator-statistic-item-inline">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][5]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[5]['value'] ?></span>
								</div>
								<div class="report-operator-statistic-item-inline">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][6]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[6]['value'] ?></span>
								</div>
							</div>
							<div class="report-operator-statistic-item report-operator-statistic-item-approve">
								<div class="report-operator-statistic-item-block">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][7]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[7]['value'] ?></span>
								</div>
								<div class="report-operator-statistic-item-inline">
									<span class="report-operator-statistic-text"><?= $calculateResult['config']['reportOptions'][8]['title'] ?></span>
									<span class="report-operator-statistic-value"><?= $item[8]['value'] ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<? endforeach; ?>
	</div>
</div>
<span class="reports-more-users <?=(count($calculateResult['items']) <= 4) ? 'reports-more-users-hidden': ''?>"   data-role="reports-more-users"><?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_GROUPED_DATA_GRID_MORE_TITLE')?></span>
<script>

	BX.message({'REPORT_GROUPED_DATA_GRID_MORE_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_GROUPED_DATA_GRID_MORE_TITLE')?>"});
	BX.message({'REPORT_GROUPED_DATA_GRID_CLOSE_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_GROUPED_DATA_GRID_CLOSE_TITLE')?>"});
	BX.Report.Dashboard.Content.Html.ready(function(context) {
		new BX.VisualConstructor.GroupedDataGrid({context: context});
	});



</script>