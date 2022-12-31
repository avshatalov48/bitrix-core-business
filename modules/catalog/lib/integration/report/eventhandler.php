<?php

namespace Bitrix\Catalog\Integration\Report;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Integration\Report\Dashboard\DashboardManager;
use Bitrix\Main\Loader;

final class EventHandler
{
	public const BATCH_GROUP_CATALOG = 'catalog_general';

	public const BATCH_INVENTORY_MANAGEMENT = 'catalog_inventory_management';
	public const BATCH_INVENTORY_MANAGEMENT_SORT = 160;

	public static function onAnalyticPageBatchCollect(): array
	{
		return DashboardManager::getManager()->getAnalyticBoardBatchList();
	}

	public static function onAnalyticPageCollect(): array
	{
		return DashboardManager::getManager()->getAnalyticBoardList();
	}

	public static function onReportHandlerCollect(): array
	{
		return DashboardManager::getManager()->getActiveHandlerList();
	}

	public static function onViewsCollect(): array
	{
		return DashboardManager::getManager()->getActiveViewList();
	}

	public static function onDefaultBoardsCollect(): array
	{
		return DashboardManager::getManager()->getDashboardList();
	}

	private static function checkDocumentReadRights(): bool
	{
		return Loader::includeModule('catalog') && AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}
}
