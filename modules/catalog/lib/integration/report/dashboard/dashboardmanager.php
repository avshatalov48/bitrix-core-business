<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Integration\Report\View\CatalogView;
use Bitrix\Main\Loader;

final class DashboardManager
{
	private static DashboardManager $manager;

	/** @var array
	 * list of <b>Dashboard</b> instances that rendering in warehouse report
	 */
	private array $dashboardList = [];

	/**
	 * Returns instance of <b>DashboardManager</b>
	 * @return DashboardManager
	 */
	public static function getManager(): self
	{
		if (!isset(self::$manager))
		{
			self::$manager = new DashboardManager();
		}

		return self::$manager;
	}

	private static function getDefaultDashboardList(): array
	{
		return [
			new StoreStockDashboard(),
			new StoreSaleDashboard(),
		];
	}

	public function getActiveViewList(): array
	{
		if (!self::checkDocumentReadRights())
		{
			return [];
		}

		$viewList = [];

		/** @var CatalogDashboard $dashboard */
		foreach ($this->dashboardList as $dashboard)
		{
			array_push($viewList, ...$dashboard->getActiveViewList());
		}
		return $viewList;
	}

	public function getActiveHandlerList(): array
	{
		if (!self::checkDocumentReadRights())
		{
			return [];
		}

		$handlersList = [];

		/** @var CatalogDashboard $dashboard */
		foreach ($this->dashboardList as $dashboard)
		{
			/** @var array $viewHandlers */
			$viewHandlers = array_map(static function(CatalogView $view) {
				return $view->getViewHandler();
			}, $dashboard->getActiveViewList());

			array_push($handlersList, ...$viewHandlers);
		}

		return $handlersList;
	}

	public function getDashboardList(): array
	{
		if (!self::checkDocumentReadRights())
		{
			return [];
		}

		$dashboards = [];
		/** @var CatalogDashboard $dashboard */
		foreach ($this->dashboardList as $dashboard)
		{
			$dashboards[] = $dashboard->getDashboard();
		}

		return $dashboards;
	}

	public function getAnalyticBoardList(): array
	{
		if (!self::checkDocumentReadRights())
		{
			return [];
		}

		$boards = [];
		/** @var CatalogDashboard $dashboard */
		foreach ($this->dashboardList as $dashboard)
		{
			$boards[] = $dashboard->getAnalyticBoard();
		}

		return $boards;
	}

	public function getAnalyticBoardBatchList(): array
	{
		if (!self::checkDocumentReadRights())
		{
			return [];
		}

		$boards = [];
		/** @var CatalogDashboard $dashboard */
		foreach ($this->dashboardList as $dashboard)
		{
			$boards[] = $dashboard->getAnalyticBoardBatch();
		}

		return $boards;
	}

	/**
	 * Add new dashboard to rendering in warehouse report
	 * @param CatalogDashboard ...$dashboard
	 * @return void
	 */
	public function addDashboard(CatalogDashboard ...$dashboard): void
	{
		array_push($this->dashboardList, ...$dashboard);
	}

	private function __construct()
	{
		$this->addDashboard(...self::getDefaultDashboardList());
	}

	private static function checkDocumentReadRights(): bool
	{
		return Loader::includeModule('catalog') && AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}
}
