<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard;

use Bitrix\Catalog\Integration\Report\Dashboard\Group\Group;
use Bitrix\Catalog\Integration\Report\Dashboard\Group\StoreGroup;
use Bitrix\Catalog\Integration\Report\Filter\StoreStockFilter;
use Bitrix\Catalog\Integration\Report\View\StoreStock\StoreStockGrid;
use Bitrix\Catalog\Integration\Report\View\StoreStock\StoreStockSaleChart;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\AnalyticBoard;

final class StoreStockDashboard extends CatalogDashboard
{
	public const BOARD_VERSION = 'v2';
	public const BOARD_KEY = 'catalog_warehouse_stock';

	public const ACCESS_BOARD_ID = 2;

	public function getBoardKey(): string
	{
		return self::BOARD_KEY;
	}

	public function getBoardVersion(): string
	{
		return self::BOARD_VERSION;
	}

	public function getAccessBoardId(): int
	{
		return self::ACCESS_BOARD_ID;
	}

	public function getBoardTitle(): string
	{
		return Loc::getMessage('STORE_STOCK_DASHBOARD_TITLE');
	}

	protected static function getDefaultViewList(): array
	{
		return [
			new StoreStockSaleChart(),
			new StoreStockGrid(),
		];
	}

	protected static function getDefaultGroup(): Group
	{
		return (new StoreGroup());
	}

	public function getAnalyticBoard(): AnalyticBoard
	{
		$analyticBoard = parent::getAnalyticBoard();
		$analyticBoard->setFilter(new StoreStockFilter($this->getBoardKey()));

		return $analyticBoard;
	}
}
