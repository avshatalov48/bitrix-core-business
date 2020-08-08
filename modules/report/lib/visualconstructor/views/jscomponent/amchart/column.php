<?php
namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;

/**
 * Class Column
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
class Column extends Serial
{
	const VIEW_KEY = 'column';

	/**
	 * Column view type constructor constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_COLUMN_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-bar.jpg');
	}

	/**
	 * Return list of compatible view type keys, to this view types can switch without reform configurations.
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		$viewTypes =  parent::getCompatibleViewTypes();
		$viewTypes[] = 'smoothedLineGraph';
		$viewTypes[] = 'linearGraph';
		$viewTypes[] = 'stack';
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
		$result['categoryAxis']['labelRotation'] = 45;
		$result['categoryAxis']['gridPosition'] = "start";
		foreach ($result['graphs'] as &$graphConfig)
		{
			$graphConfig['type'] = 'column';
			$graphConfig['lineAlpha'] = '0.1';
			$graphConfig['fillAlphas'] = '1';
		}
		return $result;
	}
}