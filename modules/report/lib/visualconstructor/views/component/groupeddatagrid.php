<?php

namespace Bitrix\Report\VisualConstructor\Views\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Fields\Valuable\ColorPicker;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;


/**
 * Class Number
 * @package Bitrix\Report\VisualConstructor\Views
 */
class GroupedDataGrid extends Base
{
	const VIEW_KEY = 'tripleDataWithProgress';

	const MAX_RENDER_REPORT_COUNT = 8;

	/**
	 * Number constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage('REPORT_GROUPED_DATA_GRID_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-lines-list.png');
		$this->setComponentName('bitrix:report.visualconstructor.widget.content.groupeddatagrid');
		$this->setCompatibleDataType(Common::MULTIPLE_GROUPED_REPORT_TYPE);
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.GroupedDataGrid');
		$this->setHeight('auto');
		$this->setHorizontalResizable(false);
	}


	/**
	 * Handle all data prepared for this view.
	 *
	 * @param array $calculatedPerformedData Calculated data from report handler.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($calculatedPerformedData)
	{
		$calculatedPerformedData = parent::handlerFinallyBeforePassToView($calculatedPerformedData);
		$result = array(
			'items' => array()
		);
		if ($allCalculatedReportData = $calculatedPerformedData['data'])
		{
			foreach ($allCalculatedReportData as $reportKey => $reportHandlerResult)
			{
				$items = $reportHandlerResult['items'];
				if (!$items)
				{
					continue;
				}

				foreach ($items as $item)
				{
					$result['items'][$item['groupBy']][$reportKey] = $item;
				}
				$result['config']['reportOptions'][$reportKey]['title'] = htmlspecialcharsbx($reportHandlerResult['config']['reportTitle']);

				foreach ($reportHandlerResult['config']['groupsLabelMap'] as $groupKey => $label)
				{
					$result['config']['groupOptions'][$groupKey]['title'] = htmlspecialcharsbx($label);
				}

				foreach ($reportHandlerResult['config']['groupsLogoMap'] as $groupKey => $logUrl)
				{
					$result['config']['groupOptions'][$groupKey]['logo'] = $logUrl;
				}


			}
		}

		return $result;
	}

	/**
	 * Method to modify widget form elements.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return void
	 */
	public function collectWidgetHandlerFormElements($widgetHandler)
	{
		parent::collectWidgetHandlerFormElements($widgetHandler);
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
		/** @var ColorPicker $colorField */
		$colorField = $reportHandler->getFormElement('color');
		if ($colorField)
		{
			$colorField->setPickerFieldHidden(true);
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
		/** @var DropDown $calculateField */
		$calculateField = $reportHandler->getFormElement('calculate');
		if ($calculateField)
		{
			$groupByField = $reportHandler->getFormElement('groupingBy');
			$groupByField->setDefaultValue('RESPONSIBLE');
			$reportHandler->addFormElementBefore($groupByField, $reportHandler->getFormElement('main_container_end'));
		}
	}

	/**
	 * Always in this report color should be gray.
	 *
	 * @param int $num Number of color which need.
	 * @return string
	 */
	public function getReportDefaultColor($num)
	{
		return '#acacac';
	}
}