<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmCharts4;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;


/**
 * Base class for AmChart widget
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
abstract class Base extends \Bitrix\Report\VisualConstructor\Views\JsComponent\Base
{
	/**
	 * Base constructor. for all AmChart diagrams.
	 */
	public function __construct()
	{
		$this->setHeight(380);
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.AmCharts4');
	}

	/**
	 * Handle all data prepared for this view.
	 *
	 * @param array $data Parameters prepared in report handlers.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($data)
	{
		return $result = array(
			'type' => $this->getAmChartType(),
			'theme' => 'none',
		);
	}

	/**
	 * Set non-displayable color field.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return void
	 */
	public function collectWidgetHandlerFormElements(BaseWidget $widgetHandler)
	{
		parent::collectWidgetHandlerFormElements($widgetHandler);
		$widgetHandler->getFormElement('color')->setDisplay(false);
	}

	/**
	 * Return amchar classification type.
	 *
	 * @return string
	 */
	abstract protected function getAmChartType();
}