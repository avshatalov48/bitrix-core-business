<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;

/**
 * Class Serial
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
abstract class Serial extends Base
{
	const MAX_RENDER_REPORT_COUNT = 15;

	const ENABLE_SORTING = true;
	/**
	 * Serial widget base constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setCompatibleDataType(Common::MULTIPLE_GROUPED_REPORT_TYPE);
	}


	/**
	 * Handle all data prepared for this view.
	 * Collect paremeters for pass serial amchart.
	 * @see Amchart documantation.
	 *
	 * @param array $dataFromReport Parameters prepared in report handlers.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($dataFromReport)
	{
		$result = parent::handlerFinallyBeforePassToView($dataFromReport);
		$result += array(
			'dataProvider' => array(),
			'dataDateFormat' => 'YYYY-MM-DD',
			'valueAxes' => array(
				array(
					'integersOnly' => true,
					'maximum' => 0,
					'minimum' => 0,
					'reversed' => false,
					'axisAlpha' => 0,
					'position' => 'left'
				)
			),
			'startDuration' => 0.5,
			'graphs' => array(),
			'categoryField' => 'groupingField',
			'categoryAxis' => array(
				'axisAlpha' => 0,
				'fillAlpha' => 0.05,
				'gridAlpha' => 0,
				'position' => 'bottom',
			),
			'export' => array(
				'enabled' => true,
				'position' => 'bottom-right'
			),
			'legend' => array(
				'useGraphSettings' => true,
				'equalWidths' => false,
				'position' => "bottom"
			),
			'chartCursor' => array(
				'enabled' => true,
				'oneBalloonOnly' => true,
				'categoryBalloonEnabled' => true,
				'categoryBalloonColor' => "#000000",
				'cursorAlpha' => 1,
				'zoomable' => true,
			),
		);

		$reportCount = 0;
		foreach ($dataFromReport as $data)
		{
			$reportCount++;
			if (isset($data['items']))
			{
				foreach ($data['items'] as $key => $res)
				{
					if (!isset($result['dataProvider'][$res['groupBy']]))
					{
						$result['dataProvider'][$res['groupBy']] = [
							'groupingField' => $data['config']['groupsLabelMap'][$res['groupBy']] ?? '-',
						];
					}
					//$result['dataProvider'][$res['groupBy']]['bullet'] = "https://www.amcharts.com/lib/images/faces/A04.png";
					$result['dataProvider'][$res['groupBy']]['value_' . $reportCount] = $res['value'];

					if ($res['label'])
					{
						$result['dataProvider'][$res['groupBy']]['label_' . $reportCount] = $res['label'];
					}
					if ($res['targetUrl'])
					{
						$result['dataProvider'][$res['groupBy']]['targetUrl_' . $reportCount] = $res['targetUrl'];
					}

					if ($res['balloon'])
					{
						$balloon = $result['dataProvider'][$res['groupBy']]['balloon'] ?: [];
						$result['dataProvider'][$res['groupBy']]['balloon'] = array_merge($balloon, $res['balloon']);
					}

					if ($result['valueAxes'][0]['maximum'] < $res['value'])
					{
						$result['valueAxes'][0]['maximum'] = $res['value'];
					}
				}

				$graph = array(
					"bullet" => "round",
					//"labelText" => "[[value]]",
					"title" => htmlspecialcharsbx($data['config']['reportTitle']),
					"fillColors" => $data['config']['reportColor'],
					"lineColor" => $data['config']['reportColor'],
					"valueField" => 'value_' . $reportCount,
					"descriptionField" => 'label_' . $reportCount,
					"fillAlphas" => 0,
				);

				if(isset($data["config"]["balloonFunction"]))
				{
					$graph["balloonFunction"] = $data["config"]["balloonFunction"];
				}
				else
				{
					$graph["balloonText"] = htmlspecialcharsbx($data["config"]["reportTitle"]) . " [[value]]";
				}
				$result['graphs'][] = $graph;
			}
		}

		if (static::ENABLE_SORTING)
		{
			ksort($result['dataProvider']);
		}

		$result['dataProvider'] = array_values($result['dataProvider']);
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
		/** @var DropDown $whatWillCalculateField */
		$whatWillCalculateField = $reportHandler->getFormElement('calculate');
		$labelField = $reportHandler->getFormElement('label');
		if ($whatWillCalculateField)
		{
			$labelField->addJsEventListener($whatWillCalculateField, $whatWillCalculateField::JS_EVENT_ON_CHANGE, array(
				'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.Title',
				'action' => 'whatWillCalculateChange',
			));
			$whatWillCalculateField->addAssets(array(
				'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporttitle.js')
			));
		}

	}

	/**
	 * Return amchar classification type.
	 *
	 * @return string
	 */
	protected function getAmChartType()
	{
		return 'serial';
	}
}