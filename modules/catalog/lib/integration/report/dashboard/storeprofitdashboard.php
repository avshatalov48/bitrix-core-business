<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Integration\Report\Dashboard\Group\Group;
use Bitrix\Catalog\Integration\Report\Dashboard\Group\StoreGroup;
use Bitrix\Catalog\Integration\Report\Filter\StoreProfitFilter;
use Bitrix\Catalog\Integration\Report\View\StoreProfit\StoreProfitGraph;
use Bitrix\Catalog\Integration\Report\View\StoreProfit\StoreProfitGrid;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\AnalyticBoard;

class StoreProfitDashboard extends CatalogDashboard
{
	public const BOARD_VERSION = 'v2';
	public const BOARD_KEY = 'catalog_warehouse_profit';

	public const ACCESS_BOARD_ID = 3;

	private bool $isBatchMethodSelected;

	public function __construct()
	{
		parent::__construct();

		$this->isBatchMethodSelected = State::isProductBatchMethodSelected();
	}

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
		// we have to swap the versions to clear the dashboard's cache (or use different caches if it has already been cached) when we turn batch methods on and off
		if ($this->isBatchMethodSelected)
		{
			return 'active_' . self::BOARD_VERSION;
		}

		return 'inactive_' . self::BOARD_VERSION;
	}

	public function getAnalyticBoard(): AnalyticBoard
	{
		$analyticBoard = parent::getAnalyticBoard();
		$analyticBoard->setFilter(new StoreProfitFilter($this->getBoardKey()));

		return $analyticBoard;
	}

	protected static function getDefaultGroup(): Group
	{
		return (new StoreGroup());
	}

	/**
	 * if batch method is not selected, we will show a warning
	 * @see self::getBoardVersion
	 */
	protected static function getDefaultViewList(): array
	{
		if (!State::isProductBatchMethodSelected())
		{
			return [
				new StoreProfitGrid(),
			];
		}

		return [
			new StoreProfitGraph(),
			new StoreProfitGrid(),
		];
	}

	public function getBoardTitle(): ?string
	{
		return Loc::getMessage('STORE_PROFIT_DASHBOARD_TITLE');
	}
}
