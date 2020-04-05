<?php
namespace Bitrix\Report\VisualConstructor\Controller;

use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ReportProvider;

/**
 * Class Report
 * @package Bitrix\Report\VisualConstructor\Controller
 */
class Report extends Base
{
	/**
	 * @param string $categoryKey Category key.
	 * @return array
	 */
	public function getReportHandlersByCategoryAction($categoryKey = '__')
	{
		$result = array();
		$reports = new ReportProvider();
		if ($categoryKey !== '__')
		{
			$reports->addFilter('categories', array($categoryKey));
		}

		$reports->execute();

		/** @var BaseReport[] $reportHandlers */
		$reportHandlers = $reports->getResults();
		foreach ($reportHandlers as $report)
		{
			$result[$report::getClassName()] = $report->getTitle();
		}
		return $result;
	}
}