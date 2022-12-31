<?php

namespace Bitrix\Catalog\Integration\Report\View\StoreStock;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Catalog\Integration\Report\Handler\StoreStock\GridHandler;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Catalog\Integration\Report\View\CatalogView;

class StoreStockGrid extends CatalogView
{
	public const VIEW_KEY = 'store_stock_grid';
	public const MAX_RENDER_REPORT_COUNT = 1;
	public const USE_IN_VISUAL_CONSTRUCTOR = false;

	public function __construct()
	{
		parent::__construct();

		$this->setLabel(Loc::getMessage('STORE_STOCK_GRID_LABEL'));
		$this->setDraggable(false);
		$this->setComponentName('bitrix:catalog.report.store_stock.grid');
		$this->setPreviewImageUri('/bitrix/images/report/visualconstructor/preview/grid.svg');
		$this->setCompatibleDataType(Common::MULTIPLE_REPORT_TYPE);
	}

	public function getViewHandler(): BaseHandler
	{
		return new GridHandler();
	}
}
