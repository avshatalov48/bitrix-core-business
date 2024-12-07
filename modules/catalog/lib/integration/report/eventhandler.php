<?php

namespace Bitrix\Catalog\Integration\Report;

use Bitrix\Catalog\Integration\Report\Dashboard\DashboardManager;
use Bitrix\Catalog\Store\EnableWizard\Manager;

final class EventHandler
{
	public const BATCH_GROUP_CATALOG = 'catalog_general';

	public const BATCH_INVENTORY_MANAGEMENT = 'catalog_inventory_management';
	public const BATCH_INVENTORY_MANAGEMENT_SORT = 160;

	public static function onAnalyticPageBatchCollect(): array
	{
		if (Manager::isOnecMode())
		{
			return [];
		}

		return DashboardManager::getManager()->getAnalyticBoardBatchList();
	}

	public static function onAnalyticPageCollect(): array
	{
		if (Manager::isOnecMode())
		{
			return [];
		}

		return DashboardManager::getManager()->getAnalyticBoardList();
	}

	public static function onReportHandlerCollect(): array
	{
		if (Manager::isOnecMode())
		{
			return [];
		}

		return DashboardManager::getManager()->getActiveHandlerList();
	}

	public static function onViewsCollect(): array
	{
		if (Manager::isOnecMode())
		{
			return [];
		}

		return DashboardManager::getManager()->getActiveViewList();
	}

	public static function onDefaultBoardsCollect(): array
	{
		if (Manager::isOnecMode())
		{
			return [];
		}

		return DashboardManager::getManager()->getDashboardList();
	}
}
