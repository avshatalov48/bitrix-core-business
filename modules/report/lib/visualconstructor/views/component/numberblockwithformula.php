<?php

namespace Bitrix\Report\VisualConstructor\Views\Component;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Handler\EmptyReport;
use Bitrix\Report\VisualConstructor\Handler\Formula;
use Bitrix\Report\VisualConstructor\Helper\Report;

/**
 * NumberBlock with formula, this view type create widget type like number block except last report, last report will change with formula
 * @package Bitrix\Report\VisualConstructor\Views\Component
 */
class NumberBlockWithFormula extends NumberBlock
{
	const VIEW_KEY                   = 'numberBlockAAA';
	const MAX_RENDER_REPORT_COUNT    = 3;
	const DEFAULT_EMPTY_REPORT_COUNT = 3;

	/**
	 * Number block with formula constructor constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setHeight(380);
		$this->setLabel('Number Block');
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-number-block.jpg');
		$this->setComponentName('bitrix:report.visualconstructor.widget.content.numberblock');
		$this->setCompatibleDataType(Common::SINGLE_REPORT_TYPE);
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.NumberBlock');
	}


	/**
	 * When building new widget, add default Report handlers to widget.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return BaseWidget
	 */
	public function addDefaultReportHandlersToWidgetHandler(BaseWidget $widgetHandler)
	{

		$reportHandler = Report::buildReportHandlerForWidget(EmptyReport::getClassName(), $widgetHandler->getWidget());
		$widgetHandler->addReportHandler($reportHandler);
		$reportHandler = Report::buildReportHandlerForWidget(EmptyReport::getClassName(), $widgetHandler->getWidget());
		$widgetHandler->addReportHandler($reportHandler);
		$reportHandler = Report::buildReportHandlerForWidget(Formula::getClassName(), $widgetHandler->getWidget());
		$widgetHandler->addReportHandler($reportHandler);
		return $widgetHandler;
	}

	/**
	 * Set non-displayable calculate and report category fields.
	 *
	 * @param BaseReport $reportHandler Report handler.
	 * @return void
	 */
	public function collectReportHandlerFormElements($reportHandler)
	{
		parent::collectReportHandlerFormElements($reportHandler);
		if ($reportHandler instanceof Formula)
		{
			$reportHandler->getFormElement('calculate')->setDisplay(false);
			$reportHandler->getFormElement('reportCategory')->setDisplay(false);
		}
	}
}