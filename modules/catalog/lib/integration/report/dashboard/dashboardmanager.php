<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\Integration\Report\View\ViewRenderable;
use Bitrix\Catalog\Restriction\ToolAvailabilityManager;
use Bitrix\Main\Loader;

final class DashboardManager
{
	private static DashboardManager $manager;

	/** @var array
	 * list of <b>Dashboard</b> instances that rendering in warehouse report
	 */
	private array $dashboardList = [];
	/**
	 * @var array
	 * list of <b>Dashboard</b> instances that filtered by user access rights
	 */
	private ?array $allowedDashboardList;

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

	public static function getCatalogDashboardList(): array
	{
		$dashboards =  [
			new StoreProfitDashboard(),
			new StoreStockDashboard(),
			new StoreSaleDashboard(),
		];

		return $dashboards;
	}

	public function getActiveViewList(): array
	{
		if (!self::checkAccessRights())
		{
			return [];
		}

		$viewList = [];

		/** @var CatalogDashboard $dashboard */
		foreach ($this->getAllowedDashboards() as $dashboard)
		{
			array_push($viewList, ...$dashboard->getActiveViewList());
		}
		return $viewList;
	}

	public function getActiveHandlerList(): array
	{
		if (!self::checkAccessRights())
		{
			return [];
		}

		$handlersList = [];

		/** @var CatalogDashboard $dashboard */
		foreach ($this->getAllowedDashboards() as $dashboard)
		{
			/** @var array $viewHandlers */
			$viewHandlers = array_map(static function(ViewRenderable $view) {
				return $view->getViewHandler();
			}, $dashboard->getActiveViewList());

			array_push($handlersList, ...$viewHandlers);
		}

		return $handlersList;
	}

	public function getDashboardList(): array
	{
		if (!self::checkAccessRights())
		{
			return [];
		}

		$dashboards = [];
		/** @var CatalogDashboard $dashboard */
		foreach ($this->getAllowedDashboards() as $dashboard)
		{
			$dashboards[] = $dashboard->getDashboard();
		}

		return $dashboards;
	}

	public function getAnalyticBoardList(): array
	{
		if (!self::checkAccessRights())
		{
			return [];
		}

		if (!ToolAvailabilityManager::getInstance()->checkInventoryManagementAvailability())
		{
			return [];
		}

		$boards = [];
		/** @var CatalogDashboard $dashboard */
		foreach ($this->getAllowedDashboards() as $dashboard)
		{
			$boards[] = $dashboard->getAnalyticBoard();
		}

		return $boards;
	}

	public function getAnalyticBoardBatchList(): array
	{
		if (!self::checkAccessRights())
		{
			return [];
		}

		if (!ToolAvailabilityManager::getInstance()->checkInventoryManagementAvailability())
		{
			return [];
		}

		$boards = [];
		/** @var CatalogDashboard $dashboard */
		foreach ($this->getAllowedDashboards() as $dashboard)
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
		$this->addDashboard(...self::getCatalogDashboardList());
	}

	/**
	 * @return CatalogDashboard[]
	 */
	public function getAllowedDashboards(): array
	{
		if (isset($this->allowedDashboardList))
		{
			return $this->allowedDashboardList;
		}

		$acceptedDashboards = AccessController::getCurrent()->getPermissionValue(ActionDictionary::ACTION_STORE_ANALYTIC_VIEW);
		if (!$acceptedDashboards)
		{
			return [];
		}

		$allAccepted = in_array(PermissionDictionary::VALUE_VARIATION_ALL, $acceptedDashboards, true);
		$this->allowedDashboardList = [];
		/** @var CatalogDashboard $dashboard */
		foreach ($this->dashboardList as $dashboard)
		{
			if ($allAccepted || in_array($dashboard->getAccessBoardId(), $acceptedDashboards, true))
			{
				$this->allowedDashboardList[] = $dashboard;
			}
		}

		return $this->allowedDashboardList;
	}

	private static function checkAccessRights(): bool
	{
		return
			Loader::includeModule('catalog')
			&& AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
			&& AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_ANALYTIC_VIEW)
		;
	}
}
