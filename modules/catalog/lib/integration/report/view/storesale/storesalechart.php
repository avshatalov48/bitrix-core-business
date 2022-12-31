<?php

namespace Bitrix\Catalog\Integration\Report\View\StoreSale;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Catalog\Integration\Report\Handler\StoreSale\ChartHandler;
use Bitrix\Catalog\Integration\Report\View\CatalogView;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;

class StoreSaleChart extends CatalogView
{
	public const VIEW_KEY = 'store_sale_chart';
	public const MAX_RENDER_REPORT_COUNT = 1;
	public const USE_IN_VISUAL_CONSTRUCTOR = false;

	public function __construct()
	{
		parent::__construct();

		$this->setLabel(Loc::getMessage('STORE_SALE_CHART_LABEL'));
		$this->setDraggable(false);
		$this->setComponentName('bitrix:catalog.report.store_sale.chart');
		$this->setPreviewImageUri('/bitrix/images/report/visualconstructor/preview/graph.svg');
		$this->setCompatibleDataType(Common::MULTIPLE_REPORT_TYPE);
	}

	public function getViewHandler(): BaseHandler
	{
		return new ChartHandler();
	}
}
