<?php

namespace Bitrix\Report\VisualConstructor;

use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Handler\EmptyReport;
use Bitrix\Report\VisualConstructor\Views\Component\GroupedDataGrid;
use Bitrix\Report\VisualConstructor\Views\JsComponent\Activity;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\DonutDiagram;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\Funnel;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\LinearGraph;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\MultiDirectional;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\PieDiagram;
use \Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\Column;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\SmoothedGraph;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\Stack;
use Bitrix\Report\VisualConstructor\Views\Component\Number;
use Bitrix\Report\VisualConstructor\Views\Component\NumberBlock;

/**
 * Class EventHandler
 * @package Bitrix\Report\VisualConstructor
 */
class EventHandler
{

	/**
	 * @return BaseWidgetHandler[]
	 */
	public static function onWidgetCollect()
	{
		$widget = new BaseWidget();
		$result[] = $widget;
		return $result;
	}

	/**
	 * @return BaseReportHandler[]
	 */
	public static function onReportsCollect()
	{
		$emptyReportHandler = new EmptyReport();
//		$formula = new Formula();
		$result[] = $emptyReportHandler;
//		$result[] = $formula;

		return $result;
	}

	/**
	 * @return Category[]
	 */
	public static function onCategoriesCollect()
	{
		$main = new Category();
		$main->setKey('main');
		$main->setLabel('Main');

		$categories[] = $main;
		return $categories;
	}

	/**
	 * @return View[]
	 */
	public static function onViewsCollect()
	{

		return array(
			new PieDiagram(),
			new DonutDiagram(),
			new Column(),
			new Stack(),
			new LinearGraph(),
			new SmoothedGraph(),
			new Number(),
			new NumberBlock(),
//			new NumberBlockWithFormula(),
			new Funnel(),
			new MultiDirectional(),
			new Activity(),
			new GroupedDataGrid()
		);
	}

}
