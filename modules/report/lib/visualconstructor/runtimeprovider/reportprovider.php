<?php
namespace Bitrix\Report\VisualConstructor\RuntimeProvider;

use Bitrix\Report\VisualConstructor\BaseReportHandler;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Internal\Manager\ReportManager;

/**
 * @method BaseReport|null getFirstResult()
 * Class ReportProvider
 * @package Bitrix\Report\VisualConstructor\RuntimeProvider
 */
class ReportProvider extends Base
{
	/**
	 * @return array
	 */
	protected function availableFilterKeys()
	{
		return array('reportClassName', 'categories', 'unit', 'dataType', 'primary');
	}

	/**
	 * @return array
	 */
	protected function availableRelations()
	{
		return array('category', 'unit');
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\Internal\Manager\ReportManager
	 */
	protected function getManagerInstance()
	{
		return ReportManager::getInstance();
	}

	/**
	 * @return BaseReportHandler[]
	 */
	protected function getEntitiesList()
	{
		return $this->getManagerInstance()->getReportList();
	}

	/**
	 * @return array
	 */
	protected function getIndices()
	{
		return $this->getManagerInstance()->getIndices();
	}

	/**
	 * @param BaseReport $report
	 */
	protected function processWithCategory(BaseReport $report)
	{
		$categoryProvider = new CategoryProvider();
		$categoryProvider->addFilter('primary', $report->getCategoryKey());
		$categoryProvider->execute();
		$results = $categoryProvider->getResults();
		$report->category = reset($results);
	}

	/**
	 * @param string $className Report handler class name.
	 * @return BaseReport|null
	 */
	public static function getReportHandlerByClassName($className)
	{
		$reportProvider = new ReportProvider();
		$reportProvider->addFilter('reportClassName', $className);
		return $reportProvider->execute()->getFirstResult();
	}

}