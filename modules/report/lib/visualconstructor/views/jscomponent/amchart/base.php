<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;


/**
 * Base class for AmChart widget
 * @package Bitrix\Report\VisualConstructor\Views\AmChart
 */
abstract class Base extends \Bitrix\Report\VisualConstructor\Views\JsComponent\Base
{
	const AM_CHART_LIB_PATH = '/bitrix/js/main/amcharts/3.21';
	/**
	 * Base constructor. for all AmChart diagrams.
	 */
	public function __construct()
	{
		$this->setHeight(380);
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.AmChart');
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
			'language' => 'ru',
			'pathToImages' => self::AM_CHART_LIB_PATH . '/images/',
			'zoomOutText' => Loc::getMessage('AM_CHART_SHOW_ALL_BUTTON_TEXT'),
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