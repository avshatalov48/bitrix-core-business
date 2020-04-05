<?php

namespace Bitrix\Report\VisualConstructor\Views\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;


/**
 * Class Number
 * @package Bitrix\Report\VisualConstructor\Views
 */
class Number extends Base
{
	const VIEW_KEY                = 'number';
	const MAX_RENDER_REPORT_COUNT = 1;

	/**
	 * Number constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setHeight(180);
		$this->setLabel(Loc::getMessage('REPORT_NUMBER_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-number.jpg');
		$this->setComponentName('bitrix:report.visualconstructor.widget.content.number');
		$this->setCompatibleDataType(Common::SINGLE_REPORT_TYPE);
	}


	/**
	 * Method to modify report form elements.
	 *
	 * @param BaseReport $reportHandler Report handler.
	 * @return void
	 */
	public function collectReportHandlerFormElements($reportHandler)
	{
		parent::collectReportHandlerFormElements($reportHandler);

		$removeFormElement = $reportHandler->getFormElementByDataAttribute('role', 'report-remove-button');
		if ($removeFormElement)
		{
			$removeFormElement->setDisplay(false);
		}

		/** @var DropDown $whatWillCalculateField */
		$whatWillCalculateField = $reportHandler->getFormElement('calculate');
		$labelField = $reportHandler->getFormElement('label');
		if ($whatWillCalculateField)
		{
			$labelField->addJsEventListener($whatWillCalculateField, $whatWillCalculateField::JS_EVENT_ON_CHANGE, array(
				'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.Title',
				'action' => 'whatWillCalculateChange',
			));
			$whatWillCalculateField->addAssets(array(
				'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporttitle.js')
			));
		}

	}


	/**
	 * Method to modify Content which pass to widget view, in absolute end.
	 * Set widget color from report color.
	 * In interface we choose color for report, but actually change widget background color.
	 *
	 * @param Widget $widget Widget entity.
	 * @param bool $withCalculatedData Marker for calculate or not reports.
	 * @return array
	 */
	public function prepareWidgetContent(Widget $widget, $withCalculatedData = false)
	{
		$resultWidget = parent::prepareWidgetContent($widget, $withCalculatedData);


		if ($withCalculatedData)
		{
			$resultWidget['config']['color'] = $widget->getWidgetHandler()->getReportHandlers()[0]->getFormElement('color')->getValue();
			$resultWidget['config']['title'] = $widget->getWidgetHandler()->getReportHandlers()[0]->getFormElement('label')->getValue();
		}


		return $resultWidget;
	}
}