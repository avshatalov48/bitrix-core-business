<?php

namespace Bitrix\Report\VisualConstructor;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Entity\Report;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Helper\Dashboard;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;
use Bitrix\Report\VisualConstructor\Internal\Error\IErrorable;

/**
 * Class ReportDispatcher
 * @package Bitrix\Report\VisualConstructor
 */
class ReportDispatcher implements IErrorable
{
	private $view;
	private $report;
	private $errors = array();

	/**
	 * @return mixed|null|string
	 */
	public function getReportCompatibleData()
	{
		if (!$this->getView())
		{
			$this->errors[] = new Error("Set view to get data");
			return null;
		}
		if (!$this->getReport())
		{
			$this->errors[] = new Error("Set report to get data");
			return null;
		}

		$compatibleDataType = $this->getView()->getCompatibleDataType();
		$result = array();

		if(!isset(Common::$reportImplementationTypesMap[$compatibleDataType]))
		{
			$this->errors[] = new Error("No isset : '" . $compatibleDataType . "' compatible data type.'");
			return null;
		}
		else
		{
			$reportHandler = $this->getReport()->getReportHandler();
			$reportHandler->setView($this->getView());
			if ($reportHandler instanceof Common::$reportImplementationTypesMap[$compatibleDataType]['interface'])
			{
				if (!Dashboard::getBoardModeIsDemo($this->getReport()->getWidget()->getBoardId()))
				{
					$reportHandler->setCalculatedData($reportHandler->prepare());
					$getDataMethodName = Common::$reportImplementationTypesMap[$compatibleDataType]['method'];
					$result = $reportHandler->{$getDataMethodName}();
				}
				else
				{
					$getDemoDataMethodName = Common::$reportImplementationTypesMap[$compatibleDataType]['demoMethod'];
					$result = $reportHandler->{$getDemoDataMethodName}();
				}
			}
			elseif ($reportHandler::getClassName() === BaseReport::getClassName())
			{
				$getDataMethodName = Common::$reportImplementationTypesMap[$compatibleDataType]['method'];
				if (method_exists($reportHandler, $getDataMethodName))
				{
					$result = $reportHandler->{$getDataMethodName}();
				}
				else
				{
					$result = array();
				}

			}
			else
			{
				$this->errors[] = new Error('Report handler were not implemented compatible interface');
			}
		}


		return $result;
	}

	/**
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @return View
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * @param View $view View controller entity.
	 * @return void
	 */
	public function setView(View $view)
	{
		$this->view = $view;
	}

	/**
	 * @return Report
	 */
	public function getReport()
	{
		return $this->report;
	}

	/**
	 * @param Report $report Report entity.
	 * @return void
	 */
	public function setReport($report)
	{
		$this->report = $report;
	}
}