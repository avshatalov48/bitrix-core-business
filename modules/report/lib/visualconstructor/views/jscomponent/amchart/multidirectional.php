<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;


/**
 * Class MultiDirectional.
 * Construct 2 directional linear graph,
 * First report in first direction, second in second sdirection.
 *
 * @package Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart
 */
class MultiDirectional extends Serial
{
	const VIEW_KEY = 'multiDirectional';

	const MAX_RENDER_REPORT_COUNT    = 2;
	const DEFAULT_EMPTY_REPORT_COUNT = 2;

	/**
	 * Multidirecional graoph constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_MULTI_LINEAR_GRAPH_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-multiple-direction.png');
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
		$result['valueAxes'] = array(
			array(
				"id" => "v1",
				"axisColor" => "#FF6600",
				"axisThickness" => 2,
				"axisAlpha" => 1,
				"position" => "left"
			),
			array(
				"id" => "v2",
				"axisColor" => "#FFFCCC",
				"axisThickness" => 2,
				"axisAlpha" => 1,
				"position" => "right",
			)
		);

		$isAllReportModeIsDate = true;
		foreach ($dataFromReport as $key => $report)
		{
			if (!isset($report['config']['mode']) && $report['config']['mode'] !== 'date')
			{
				$isAllReportModeIsDate = false;
				break;
			}
			if ($report['config']['reportColor'])
			{
				$result['valueAxes'][$key]["axisColor"] = $report['config']['reportColor'];
			}
		}
		$result['categoryAxis']['parseDates'] = $isAllReportModeIsDate;

		foreach ($result['graphs'] as $key => &$graph)
		{
			$graph['valueAxis'] = $result['valueAxes'][$key]['id'];
		}
		return $result;
	}

	/**
	 * Method to modify widget form elements.
	 *
	 * @param BaseReport $reportHandler Widget handler.
	 * @return void
	 */
	public function collectReportHandlerFormElements($reportHandler)
	{
		parent::collectReportHandlerFormElements($reportHandler);
		$removeReportControlFormElement = $reportHandler->getFormElementByDataAttribute('role', 'report-remove-button');
		if ($removeReportControlFormElement)
		{
			$reportHandler->removeFormElement($removeReportControlFormElement);
		}
	}


}