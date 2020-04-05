<?php

namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Report\VisualConstructor\BaseReportHandler;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;

/**
 * Class ReportManager
 * @package Bitrix\Report\VisualConstructor\Internal\Manager
 */
class ReportManager extends Base
{
	private static $reportsList = array();
	private static $indices = array(
		'categories' => array(),
		'unit' => array(),
		'dataType' => array(),
		'reportClassName' => array()
	);
	private static $called = false;

	/**
	 * @return string
	 */
	protected function getEventTypeKey()
	{
		return Common::EVENT_REPORT_COLLECT;
	}


	/**
	 * @return array|bool
	 */
	public function getReportList()
	{
		return $this->isCalled() ? self::$reportsList : false;
	}

	/**
	 * @return array|bool
	 */
	public function getIndices()
	{
		return $this->isCalled() ? self::$indices : false;
	}

	/**
	 * @return bool|array
	 */
	public function getIndexByCategory()
	{
		return $this->isCalled() ? self::$indices['categories'] : false;
	}

	/**
	 * @return bool|array
	 */
	public function getIndexByUnit()
	{
		return $this->isCalled() ? self::$indices['unit'] : false;
	}

	/**
	 * @return bool|array
	 */
	public function getIndexByDataType()
	{
		return $this->isCalled() ? self::$indices['dataType'] : false;
	}

	/**
	 * Call special Event end build list of result and create index list for searchable keys
	 * @return void
	 */
	public function call()
	{
		if (!self::$called)
		{
			/** @var BaseReport[] $reports */
			$reports = $this->getResult();
			foreach ($reports as $key => $report)
			{
				self::$reportsList[$key] = $report;
				self::$indices['categories'][$report->getCategoryKey()][] = $key;
				self::$indices['reportClassName'][$report::getClassName()][] = $key;
				self::$indices['unit'][$report->getUnitKey()][] = $key;
				foreach ($report->getReportImplementedDataTypes() as $dataType)
				{
					self::$indices['dataType'][$dataType][] = $key;
				}
			}
		}
		self::$called = true;
	}


	/**
	 * @return bool
	 */
	private function isCalled()
	{
		if (!self::$called)
		{
			$this->errors[]	= new Error('invoke call method manager before get some parameters');
			return false;
		}
		else
		{
			return true;
		}
	}

}