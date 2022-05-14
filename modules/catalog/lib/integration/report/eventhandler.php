<?php

namespace Bitrix\Catalog\Integration\Report;

use Bitrix\Catalog\Integration\Report\Dashboard\StoreStock;
use Bitrix\Catalog\Integration\Report\Filter\StoreStockFilter;
use Bitrix\Catalog\Integration\Report\Handler;
use Bitrix\Catalog\Integration\Report\View;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\AnalyticBoardBatch;

final class EventHandler
{
	public const BATCH_GROUP_CATALOG = 'catalog_general';

	public const BATCH_INVENTORY_MANAGEMENT = 'catalog_inventory_management';
	public const BATCH_INVENTORY_MANAGEMENT_SORT = 160;

	public static function onAnalyticPageBatchCollect(): array
	{
		$result = [];

		if (self::checkDocumentReadRights() && \Bitrix\Catalog\Config\State::isUsedInventoryManagement())
		{
			$inventoryManagementBatch = new AnalyticBoardBatch();
			$inventoryManagementBatch->setKey(self::BATCH_INVENTORY_MANAGEMENT);
			$inventoryManagementBatch->setGroup(self::BATCH_GROUP_CATALOG);
			$inventoryManagementBatch->setTitle(Loc::getMessage('INVENTORY_MANAGEMENT_REPORT_BATCH_TITLE'));
			$inventoryManagementBatch->setOrder(self::BATCH_INVENTORY_MANAGEMENT_SORT);
			$result[] = $inventoryManagementBatch;
		}

		return $result;
	}

	public static function onAnalyticPageCollect(): array
	{
		$result = [];

		if (self::checkDocumentReadRights() && \Bitrix\Catalog\Config\State::isUsedInventoryManagement())
		{
			$storeStockBoard = new AnalyticBoard(StoreStock::BOARD_KEY);
			$storeStockBoard->setBatchKey(self::BATCH_INVENTORY_MANAGEMENT);
			$storeStockBoard->setGroup(self::BATCH_GROUP_CATALOG);
			$storeStockBoard->setTitle(Loc::getMessage('STORE_STOCK_REPORT_TITLE'));
			$storeStockBoard->setFilter(new StoreStockFilter(StoreStock::BOARD_KEY));
			$storeStockBoard->addFeedbackButton();
			$result[] = $storeStockBoard;
		}

		return $result;
	}

	public static function onReportHandlerCollect(): array
	{
		return [new Handler\StoreStock()];
	}

	public static function onViewsCollect(): array
	{
		return [
			new View\StoreStock\StoreStockGrid(),
			new View\StoreStock\StoreStockSaleChart(),
		];
	}

	public static function onDefaultBoardsCollect(): array
	{
		$dashboards = [];

		$dashboards[] = StoreStock::getDashboard();

		return $dashboards;
	}

	private static function checkDocumentReadRights(): bool
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->canDoOperation('catalog_read');
	}
}
