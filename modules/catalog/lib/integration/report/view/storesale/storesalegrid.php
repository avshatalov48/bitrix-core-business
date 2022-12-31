<?php

namespace Bitrix\Catalog\Integration\Report\View\StoreSale;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Catalog\Integration\Report\Handler\StoreSale\GridHandler;
use Bitrix\Catalog\Integration\Report\View\CatalogView;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;

class StoreSaleGrid extends CatalogView
{
	public const VIEW_KEY = 'store_sale_grid';
	public const MAX_RENDER_REPORT_COUNT = 1;
	public const USE_IN_VISUAL_CONSTRUCTOR = false;

	public function __construct()
	{
		parent::__construct();

		$this->setLabel(Loc::getMessage('STORE_SALE_GRID_LABEL'));
		$this->setDraggable(false);
		$this->setComponentName('bitrix:catalog.report.store_sale.grid');
		$this->setPreviewImageUri('/bitrix/images/report/visualconstructor/preview/grid.svg');
		$this->setCompatibleDataType(Common::MULTIPLE_REPORT_TYPE);
	}

	public function getViewHandler(): BaseHandler
	{
		return new GridHandler();
	}
}
