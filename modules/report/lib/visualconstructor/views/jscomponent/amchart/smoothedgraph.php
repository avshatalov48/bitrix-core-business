<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;

/**
 * Class SmoothedGraph
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
class SmoothedGraph extends LinearGraph
{
	const VIEW_KEY = 'smoothedLineGraph';

	/**
	 * Smoothed graph  constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_SMOOTHED_LINEAR_GRAPH_VIEW_LABEL'));
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
		$viewTypes[] = 'linearGraph';
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
		foreach ($result['graphs'] as &$graphConfig)
		{
			$graphConfig['type'] = 'smoothedLine';
		}
		return $result;
	}

}