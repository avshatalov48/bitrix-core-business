<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard;

use Bitrix\Catalog\Integration\Report\Dashboard\Group\Group;
use Bitrix\Catalog\Integration\Report\Dashboard\Group\StoreGroup;
use Bitrix\Catalog\Integration\Report\Filter\StoreSaleFilter;
use Bitrix\Catalog\Integration\Report\View\StoreSale\StoreSaleChart;
use Bitrix\Catalog\Integration\Report\View\StoreSale\StoreSaleGrid;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\AnalyticBoard;

class StoreSaleDashboard extends CatalogDashboard
{
	public const BOARD_VERSION = 'v2';
	public const BOARD_KEY = 'catalog_warehouse_sale';

	public const ACCESS_BOARD_ID = 1;

	public function getBoardKey(): string
	{
		return static::BOARD_KEY;
	}

	public function getAccessBoardId(): int
	{
		return self::ACCESS_BOARD_ID;
	}

	public function getBoardVersion(): string
	{
		return static::BOARD_VERSION;
	}

	public function getAnalyticBoard(): AnalyticBoard
	{
		$analyticBoard = parent::getAnalyticBoard();
		$analyticBoard->setFilter(new StoreSaleFilter($this->getBoardKey()));

		return $analyticBoard;
	}

	protected static function getDefaultGroup(): Group
	{
		return (new StoreGroup());
	}

	protected static function getDefaultViewList(): array
	{
		return [
			new StoreSaleChart(),
			new StoreSaleGrid(),
		];
	}

	public function getBoardTitle(): string
	{
		return Loc::getMessage('STORE_SALE_DASHBOARD_TITLE');
	}
}
