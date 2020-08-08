<?php
namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmCharts4;

/**
 * Class Column
 * @package Bitrix\Report\VisualConstructor\Views\AmCharts4
 */
class Column extends Serial
{
	const VIEW_KEY = 'amcharts4_column';

	/**
	 * Column view type constructor constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-bar.jpg');
	}

	/**
	 * Return list of compatible view type keys, to this view types can switch without reform configurations.
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		return [];
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

		foreach ($result['series'] as $k => $series)
		{
			$result['series'][$k]['type'] = 'ColumnSeries';
		}

		return  $result;
	}
}