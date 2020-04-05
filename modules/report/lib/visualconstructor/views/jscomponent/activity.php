<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\IReportMultipleGroupedData;

/**
 * Class Activity
 * @package Bitrix\Report\VisualConstructor\Views\JsComponent
 */
class Activity extends Base
{
	const VIEW_KEY                = 'activity';
	const MAX_RENDER_REPORT_COUNT = 1;

	/**
	 * Activity constructor.
	 */
	public function __construct()
	{
		$this->setLabel(Loc::getMessage('REPORT_ACTIVITY_VIEW_LABEL'));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-activity.jpg');
		$this->setJsClassName('BX.Report.VisualConstructor.Widget.Content.Activity');
		$this->setCompatibleDataType(Common::MULTIPLE_BI_GROUPED_REPORT_TYPE);
		$this->setHeight(380);
	}


	/**
	 * Method to modify widget form elements.
	 *
	 * @param BaseReport $reportHandler Widget handler.
	 * @return void
	 */
	public function collectReportHandlerFormElements($reportHandler)
	{
		parent::collectReportHandlerFormElements($reportHandler);

		/** @var DropDown $whatWillCalculateField */
		$whatWillCalculateField = $reportHandler->getFormElement('calculate');
		if ($whatWillCalculateField)
		{
			$labelField = $reportHandler->getWidgetHandler()->getFormElement('label');
			$labelField->addJsEventListener($whatWillCalculateField, $whatWillCalculateField::JS_EVENT_ON_CHANGE, array(
				'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.Title',
				'action' => 'whatWillCalculateChange',
			));
			$whatWillCalculateField->addAssets(array(
				'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporttitle.js')
			));
		}

		$removeFormElement = $reportHandler->getFormElementByDataAttribute('role', 'report-remove-button');
		if ($removeFormElement)
		{
			$removeFormElement->setDisplay(false);
		}

		$colorField = $reportHandler->getFormElement('color');
		$reportHandler->getFormElement('label_color_container_start')->setDisplay(false);
		$reportHandler->getFormElement('label_color_container_end')->setDisplay(false);
		$reportHandler->getFormElement('head_container_start')->setDisplay(false);
		$reportHandler->getFormElement('head_container_end')->setDisplay(false);
		if ($colorField)
		{
			$colorField->setDisplay(false);
		}


		$labelField = $reportHandler->getFormElement('label');
		if ($labelField)
		{
			$labelField->setDisplay(false);
		}
	}

	/**
	 * Handle all data prepared for this view.
	 *
	 * @see IReportMultipleGroupedData::getMultipleGroupedData().
	 * @param array $dataFromReport Calculated data from report handler.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($dataFromReport)
	{
		if ($dataFromReport['items'])
		{
			$items = array();
			foreach ($dataFromReport['items'] as $item)
			{

				if (!empty($items[$item['firstGroupId']][$item['secondGroupId']]))
				{
					$items[$item['firstGroupId']][$item['secondGroupId']]['active'] += (int)$item['value'];
				}
				else
				{
					$items[$item['firstGroupId']][$item['secondGroupId']] = array(
						'labelXid' => (int)$item['firstGroupId'] + 1,
						'labelYid' => (int)$item['secondGroupId'],
						'active' => (int)$item['value'],
					);
				}

			}

			foreach ($items as $firstGroupId => $secondGroup)
			{
				foreach ($secondGroup as $secondGroupId => $newItem)
				{
					$result['items'][] = $newItem;
				}
			}
		}


		$result['config']['labelY'] = $this->getWeekDaysMap();
		$result['config']['labelX'] = $this->getHourList();
		return $result;
	}

	/**
	 * Week days labels map.
	 *
	 * @return array
	 */
	private function getWeekDaysMap()
	{
		return array(
			array(
				'id' => 1,
				'name' => Loc::getMessage('MONDAY'),
			),
			array(
				'id' => 2,
				'name' => Loc::getMessage('TUESDAY'),
			),
			array(
				'id' => 3,
				'name' => Loc::getMessage('WEDNESDAY'),
			),
			array(
				'id' => 4,
				'name' => Loc::getMessage('THURSDAY'),
			),
			array(
				'id' => 5,
				'name' => Loc::getMessage('FRIDAY'),
			),
			array(
				'id' => 6,
				'name' => Loc::getMessage('SATURDAY'),
				'light' => true
			),
			array(
				'id' => 0,
				'name' => Loc::getMessage('SUNDAY'),
				'light' => true
			),
		);
	}


	/**
	 * Get Hour list for grouping data by hour.
	 *
	 * @return array
	 */
	private function getHourList()
	{
		$result = array();
		for ($i = 1; $i <= 24; $i++)
		{
			$hour = array(
				'id' => $i,
				'name' => $i
			);
			if ($i === 0 || $i === 24 || ($i) % 6 == 0)
			{
				$hour['show'] = true;
			}

			if ($i >= 9 && $i <= 18)
			{
				$hour['active'] = true;
			}
			$result[] = $hour;
		}
		return $result;
	}

	/**
	 * Always in activity widget report color should be white.
	 *
	 * @param int $num Number of color which need.
	 * @return string
	 */
	public function getReportDefaultColor($num)
	{
		return '#ffffff';
	}

}