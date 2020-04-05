<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;


/**
 * Class Funnel
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
class Funnel extends PieDiagram
{
	const VIEW_KEY = 'funnel';

	/**
	 * Funnel view type constructor. set label and miniature src.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_FUNNEL_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-funnel.jpg');
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
	 * Return amchar classification type.
	 *
	 * @return string
	 */
	protected function getAmChartType()
	{
		return 'funnel';
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
		$result['depth3D'] = 35;
		$result['angle'] = 45;
		return $result;
	}
}