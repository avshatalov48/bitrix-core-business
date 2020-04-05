<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent;

use Bitrix\Report\VisualConstructor\Config\Common;

/**
 * Class Circle
 * @package Bitrix\Report\VisualConstructor\Views\JsComponent
 */
class Circle extends Base
{
	const VIEW_KEY = 'circle';

	/**
	 * Circle constructor.
	 */
	public function __construct()
	{
		$this->setLabel('Circle');
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-circle.jpg');
		$this->setCompatibleDataType(Common::SINGLE_REPORT_TYPE);
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.Circle');
	}

	/**
	 * Handle all data prepared for this view.
	 *
	 * @param array $dataFromReport Calculated data from report handler.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($dataFromReport)
	{
		return $dataFromReport;
	}
}