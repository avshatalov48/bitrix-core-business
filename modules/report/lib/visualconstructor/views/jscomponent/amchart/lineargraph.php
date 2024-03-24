<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;

/**
 * Classic  Linear Graph with two orientation
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
class LinearGraph extends Serial
{
	const VIEW_KEY = 'linearGraph';

	/**
	 * Linear graph constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_LINEAR_GRAPH_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-graph.jpg');
	}

	/**
	 * Return list of compatible view type keys, to this view types can switch without reform configurations.
	 *
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		$viewTypes = parent::getCompatibleViewTypes();
		$viewTypes[] = 'stack';
		$viewTypes[] = 'smoothedLineGraph';
		$viewTypes[] = 'column';
		return $viewTypes;
	}

	/**
	 * Handle all data prepared for this view.
	 *
	 * @param array $dataFromReport Parameters prepared in report handlers.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($dataFromReport)
	{
		$result = parent::handlerFinallyBeforePassToView($dataFromReport);
		$result['categoryAxis'] += array(
			'dashLength' => 1,
			'minorGridEnabled' => true
		);

		$isAllReportModeIsDate = true;
		foreach ($dataFromReport as $report)
		{
			if (!isset($report['config']['mode']) || $report['config']['mode'] !== 'date')
			{
				$isAllReportModeIsDate = false;
				break;
			}
		}
		$result['categoryAxis']['parseDates'] = $isAllReportModeIsDate;

		if (!empty($dataFromReport[0]['config']['categoryAxis']['labelFrequency']))
		{
			$result['categoryAxis']['labelFrequency'] = $dataFromReport[0]['config']['categoryAxis']['labelFrequency'];
		}

		return $result;
	}
}