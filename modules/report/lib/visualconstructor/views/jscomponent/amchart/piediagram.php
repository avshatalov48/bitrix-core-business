<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;

/**
 * Class Pie
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
class PieDiagram extends Base
{
	const VIEW_KEY = 'pieDiagram';

	const MAX_RENDER_REPORT_COUNT    = 1;
	const DEFAULT_EMPTY_REPORT_COUNT = 1;

	/**
	 * PieDiagram constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_PIE_DIAGRAM_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-pie.jpg');
		$this->setCompatibleDataType(Common::MULTIPLE_REPORT_TYPE);
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.AmChart.PieDiagram');
	}

	/**
	 * Return list of compatible view type keys, to this view types can switch without reform configurations.
	 *
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		$viewTypes = parent::getCompatibleViewTypes();
		$viewTypes[] = 'funnel';
		$viewTypes[] = 'donutDiagram';
		return $viewTypes;
	}

	/**
	 * Get custom colors list.
	 *
	 * @return array
	 */
	public function getCustomColorList()
	{
		return array(
			'#4fc3f7',
			'#f6ce00',
			'#98d470',
			'#f35455',
			'#cf83ae',
			'#374b89',
		);
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
		$customColors = $this->getCustomColorList();
		$result['dataProvider'] = !empty($dataFromReport['items']) ? $dataFromReport['items'] : array();

		foreach ($result['dataProvider'] as $number => &$data)
		{
			if (!isset($data['color']) && isset($customColors[$number]))
			{
				$data['color'] = $customColors[$number];
			}
		}
		$result['titleField'] = 'label';
		$result['valueField'] = 'value';
		$result['colorField'] = 'color';
		$result['outlineAlpha'] = 0.4;
		$result['outlineColor'] = "#FFFFFF";
		$result['outlineThickness'] = 1;
		$result['labelsEnabled'] = false;
		$result['legend'] = array(
			"markerType" => "circle",
			"position" => "right",
			"marginRight" => 50,
			"marginTop" => 10,
			"autoMargins" => false
		);
		return $result;
	}

	/**
	 * Return amchar classification type.
	 *
	 * @return string
	 */
	protected function getAmChartType()
	{
		return 'pie';
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
		if ($whatWillCalculateField)
		{
			$labelField = $reportHandler->getWidgetHandler()->getFormElement('label');
			$labelField->addJsEventListener($whatWillCalculateField, $whatWillCalculateField::JS_EVENT_ON_CHANGE, array(
				'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.Title',
				'action' => 'whatWillCalculateChange',
			));
			$whatWillCalculateField->addAssets(array(
				'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporttitle.js')
			));
		}

		$removeFormElement = $reportHandler->getFormElementByDataAttribute('role', 'report-remove-button');
		if ($removeFormElement)
		{
			$removeFormElement->setDisplay(false);
		}

		$colorField = $reportHandler->getFormElement('color');
		$reportHandler->getFormElement('label_color_container_start')->setDisplay(false);
		$reportHandler->getFormElement('label_color_container_end')->setDisplay(false);
		$reportHandler->getFormElement('head_container_start')->setDisplay(false);
		$reportHandler->getFormElement('head_container_end')->setDisplay(false);
		if ($colorField)
		{
			$colorField->setDisplay(false);
		}


		$labelField = $reportHandler->getFormElement('label');
		if ($labelField)
		{
			$labelField->setDisplay(false);
		}
	}

	/**
	 * Always in pie diagram report color should be white.
	 *
	 * @param int $num Number of color which need.
	 * @return string
	 */
	public function getReportDefaultColor($num)
	{
		return '#ffffff';
	}
}