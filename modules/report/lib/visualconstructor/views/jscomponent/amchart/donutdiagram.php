<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;

/**
 * Class DonutDiagram
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
class DonutDiagram extends PieDiagram
{
	const VIEW_KEY                = 'donutDiagram';
	const MAX_RENDER_REPORT_COUNT = 1;

	/**
	 * Pie diagram constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_DONUT_DIAGRAM_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-donut.png');
		$this->setCompatibleDataType(Common::MULTIPLE_REPORT_TYPE);
	}

	/**
	 * Return list of compatible view type keys, to this view types can switch without reform configurations.
	 *
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		$viewTypes = parent::getCompatibleViewTypes();
		$viewTypes[] = 'pieDiagram';
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

		$result['depth3D'] = 0;
		$result['innerRadius'] = '60%';
		$result['angle'] = 0;
		$result['autoMargins'] = false;
		$result['marginLeft'] = 10;
		$result['marginRight'] = 10;
		$result['marginBottom'] = 10;
		$result['marginTop'] = 10;
		$result['legend']['position'] = "right";
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
}