<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard;

use Bitrix\Catalog\Integration\Report\Handler;
use Bitrix\Catalog\Integration\Report\View;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor;
use Bitrix\Report\VisualConstructor\Entity\Dashboard;
use Bitrix\Report\VisualConstructor\Entity\DashboardRow;
use Bitrix\Report\VisualConstructor\Entity\Report;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;

class StoreStock
{
	public const BOARD_VERSION = 'v1';
	public const BOARD_KEY = 'catalog_warehouse_stock';

	public static function getDashboard(): Dashboard
	{
		$board = new Dashboard();
		$board->setVersion(self::BOARD_VERSION);
		$board->setBoardKey(static::BOARD_KEY);
		$board->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$board->setUserId(0);

		$chartRow = DashboardRow::factoryWithHorizontalCells(1);
		$chartRow->setWeight(1);
		$storeStockChart = static::buildStoreStockSaleChart();
		$storeStockChart->setWeight($chartRow->getLayoutMap()['elements'][0]['id']);
		$chartRow->addWidgets($storeStockChart);

		$tableRow = DashboardRow::factoryWithHorizontalCells(1);
		$tableRow->setWeight(2);
		$storeStockGrid = static::buildStoreStockGrid();
		$storeStockGrid->setWeight($tableRow->getLayoutMap()['elements'][0]['id']);
		$tableRow->addWidgets($storeStockGrid);


		$board->addRows($chartRow);
		$board->addRows($tableRow);

		return $board;
	}

	private static function buildStoreStockGrid(): Widget
	{
		$widget = new Widget();

		$widget->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(View\StoreStock\StoreStockGrid::VIEW_KEY);
		$widget->setCategoryKey('catalog');
		$widget->setBoardId(static::BOARD_KEY);
		$widget->getWidgetHandler(true)
			->updateFormElementValue('label', Loc::getMessage('STORE_STOCK_GRID_LABEL'));
		$widget->addConfigurations($widget->getWidgetHandler(true)->getConfigurations());

		$report = new Report();
		$report->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$report->setReportClassName(Handler\StoreStock::class);
		$report->setWidget($widget);
		$report->addConfigurations($report->getReportHandler(true)->getConfigurations());
		$widget->addReports($report);

		return $widget;
	}

	private static function buildStoreStockSaleChart(): Widget
	{
		$widget = new Widget();

		$widget->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(View\StoreStock\StoreStockSaleChart::VIEW_KEY);
		$widget->setCategoryKey('catalog');
		$widget->setBoardId(static::BOARD_KEY);
		$widget->getWidgetHandler(true)
			->updateFormElementValue('label',Loc::getMessage('STORE_STOCK_CHART_LABEL'));
		$widget->addConfigurations($widget->getWidgetHandler(true)->getConfigurations());

		$report = new Report();
		$report->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$report->setReportClassName(Handler\StoreStock::class);
		$report->setWidget($widget);
		$report->addConfigurations($report->getReportHandler(true)->getConfigurations());
		$widget->addReports($report);

		return $widget;
	}
}
