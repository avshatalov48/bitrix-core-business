<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;

/**
 * Class Stack
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
class Stack extends Column
{
	const VIEW_KEY = 'stack';


	/**
	 * Stack view type constructor. set label and miniature src
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_STACK_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-bar-stack.jpg');
	}

	/**
	 * Return list of compatible view type keys, to this view types can switch without reform configurations.
	 *
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		$viewTypes = parent::getCompatibleViewTypes();
		$viewTypes[] = 'smoothedLineGraph';
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

		foreach ($result['dataProvider'] as &$data)
		{
			foreach ($data as $valueKey => $value)
			{
				if (mb_strpos($valueKey, 'value_') === 0 && $value === 0)
				{
					unset($data[$valueKey]);
				}
			}
		}


		$result['valueAxes'][0] = array(
			'stackType' => "regular",
			'axisAlpha' => 0,
			'gridAlpha' => 0
		);
		return $result;
	}
}