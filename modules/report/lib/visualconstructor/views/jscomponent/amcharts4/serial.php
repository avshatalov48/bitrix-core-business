<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmCharts4;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;

/**
 * Class Serial
 * @package Bitrix\Report\VisualConstructor\Views\AmCharts4
 */
abstract class Serial extends Base
{
	const MAX_RENDER_REPORT_COUNT = 15;
	const ENABLE_SORTING = true;
	const USE_IN_VISUAL_CONSTRUCTOR = false;

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
	 * Collect parameters for pass serial amchart.
	 * @param array $dataFromReport Parameters prepared in report handlers.
	 * @return array
	 * @see Amchart documantation.
	 *
	 */
	public function handlerFinallyBeforePassToView($dataFromReport)
	{
		$result = parent::handlerFinallyBeforePassToView($dataFromReport);
		$result += array(
			'paddingTop' => 0,
			'maskBullets' => false,
			'fontSize' => '11px',
			'data' => [],
			"series" => [],
			"xAxes" => [
				[
					"type" => "CategoryAxis",
					"dataFields" => [
						"category" => "groupingField"
					],
					"renderer" => [
						"grid" => [
							"disabled" => true,
						],
						"labels" => [
							"template" => [
								"truncate" => true,
								"ellipsis" => "&hellip;",
								"fixedWidthGrid" => true,
								"horizontalCenter" => "middle",
								"fullWords" => false,
								"tooltipText" => "{category}"
							],
						],
					]
				]
			],
			"yAxes" => [[
				"type" => "ValueAxis",
				"min" => 0,
			]],
		);

		$reportCount = 0;
		foreach ($dataFromReport as $data)
		{
			$reportCount++;
			if (isset($data['items']))
			{
				foreach ($data['items'] as $key => $res)
				{
					if (!isset($result['data'][$res['groupBy']]))
					{
						$result['data'][$res['groupBy']] = [
							'groupingField' => $data['config']['groupsLabelMap'][$res['groupBy']] ?? '-',
						];
					}
					$result['data'][$res['groupBy']]['value_'.$reportCount] = $res['value'];

					if ($res['label'])
					{
						$result['data'][$res['groupBy']]['label_'.$reportCount] = $res['label'];
					}
					if ($res['targetUrl'])
					{
						$result['data'][$res['groupBy']]['targetUrl_'.$reportCount] = $res['targetUrl'];
					}

					if ($res['balloon'])
					{
						$balloon = $result['data'][$res['groupBy']]['balloon'] ?: [];
						$result['data'][$res['groupBy']]['balloon'] = array_merge($balloon, $res['balloon']);
					}
				}

				$series = [
					"type" => "", // should be filled in child class
					"dataFields" => [
						"valueY" => 'value_'.$reportCount,
						"categoryX" => "groupingField"
					],
					"columns" => [
						"fill" => $data['config']['reportColor'],
						"strokeWidth" => 0,
						"propertyFields" => [
							"fill" => "color",
							//"tooltipText" => 'label_'.$reportCount
							// we are not using standard url, because we are trying to open in in a sidepanel
							"valueUrl" => "targetUrl_1",
						],
						"width" => "85%",
						"tooltipText" => htmlspecialcharsbx($data["config"]["reportTitle"]) . " {valueY}",
					],

					"tooltip" => [
						"background" => ["disabled" => true],
						"filters" => [
							[
								"type" => "DropShadowFilter",
								"opacity" => 0.1
							]
						]
					]
				];

				$result['series'][] = $series;
			}
		}

		if (static::ENABLE_SORTING)
		{
			ksort($result['data']);
		}

		$result['data'] = array_values($result['data']);
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
		return 'XYChart';
	}
}