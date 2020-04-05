<?php

namespace Bitrix\Report\VisualConstructor\Views\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Fields\Container;
use Bitrix\Report\VisualConstructor\Fields\Div;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\WidgetForm;


/**
 * Number block for widgets with 3 numeric block.
 *
 * @package Bitrix\Report\VisualConstructor\Views
 */
class NumberBlock extends Base
{
	const VIEW_KEY = 'numberBlock';

	const MAX_RENDER_REPORT_COUNT    = 3;
	const DEFAULT_EMPTY_REPORT_COUNT = 3;

	/**
	 * Number block view constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setHeight(380);
		$this->setLabel(Loc::getMessage('REPORT_NUMBER_BLOCK_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-number-block.jpg');
		$this->setComponentName('bitrix:report.visualconstructor.widget.content.numberblock');
		$this->setCompatibleDataType(Common::SINGLE_REPORT_TYPE);
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.NumberBlock');
	}

	/**
	 * Collect second and third report configuration block in one horizontal group.
	 *
	 * @param WidgetForm $form Widget form.
	 * @return WidgetForm
	 */
	public function prepareWidgetFormBeforeRender(WidgetForm $form)
	{
		$form = parent::prepareWidgetFormBeforeRender($form);

		$secondReportConfigsContainer = $form->getField('report_configurations_container_2');


		if ($secondReportConfigsContainer instanceof Container)
		{
			$firstGroupedReportContainer = $secondReportConfigsContainer;

			$thirdReportConfigsContainer = $form->getField('report_configurations_container_3');
			if ($thirdReportConfigsContainer instanceof Container)
			{
				$lastGroupedReportContainer = $thirdReportConfigsContainer;
			}
			else
			{
				$lastGroupedReportContainer = $secondReportConfigsContainer;
			}

			$div = new Div();
			$div->setKey('report_configuration_big_container');
			$div->addClass('report-configuration-big-container');
			$form->addFieldBefore($div->start(), $firstGroupedReportContainer);
			$form->addFieldAfter($div->end(), $lastGroupedReportContainer);
		}

		return $form;
	}

	/**
	 * Set non-displayable color field in this view type.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return void
	 */
	public function collectWidgetHandlerFormElements($widgetHandler)
	{
		parent::collectWidgetHandlerFormElements($widgetHandler);
		$widgetHandler->getFormElement('color')->setDisplay(false);
	}

	/**
	 * Set default colors for report configuration block if not exist.
	 * Set non-displayable remove button field in this view type.
	 * Attach what will calculate change event to label field.
	 *
	 * @param BaseReport $reportHandler Base report handler.
	 * @return void
	 */
	public function collectReportHandlerFormElements($reportHandler)
	{
		parent::collectReportHandlerFormElements($reportHandler);
		$removeReportControlFormElement = $reportHandler->getFormElementByDataAttribute('role', 'report-remove-button');


		if ($removeReportControlFormElement)
		{
			$removeReportControlFormElement->setDisplay(false);
		}

		if (!$reportHandler->getConfiguration('color'))
		{
			$reportHandler->getConfiguration('color')->setValue('#4fc3f7');
			$reportHandler->getFormElement('head_container_start')->addInlineStyle('background-color', '#4fc3f7');
			$reportHandler->getFormElement('main_container_start')->addInlineStyle('background-color', '#4fc3f75f');
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
			$labelField->addAssets(array(
				'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporttitle.js')
			));
		}
	}

	/**
	 * Pass first report color to widget background color,.
	 * In interface we modify first report color but actually we change background of all widget.
	 *
	 * @param Widget $widget Widget entity.
	 * @param bool $withCalculatedData Marker to set calculate or no reports.
	 * @return array
	 */
	public function prepareWidgetContent(Widget $widget, $withCalculatedData = false)
	{
		$resultWidget = parent::prepareWidgetContent($widget, $withCalculatedData);

		if ($withCalculatedData)
		{
			$resultWidget['content']['params']['color'] = $widget->getWidgetHandler()->getReportHandlers()[0]->getFormElement('color')->getValue();
			$resultWidget['config']['title'] = $widget->getWidgetHandler()->getReportHandlers()[0]->getFormElement('label')->getValue();
		}

		return $resultWidget;
	}


	/**
	 * Default colors set for reports.
	 *
	 * @param int $num Number of color which need.
	 * @return string
	 */
	public function getReportDefaultColor($num)
	{
		$defaultColorList = array(
			"#4fc3f7"
		);

		return $defaultColorList[$num % count($defaultColorList)];
	}


}