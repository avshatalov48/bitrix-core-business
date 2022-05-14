<?php

use Bitrix\Catalog\Integration\Report\Filter\StoreStockFilter;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('report');

class CatalogReportStoreStockProductsGridComponent extends \Bitrix\Catalog\Component\ProductList
{
	private const GRID_ID_PREFIX = 'catalog_report_store_stock_products_grid_';
	private const FILTER_ID_PREFIX = 'catalog_report_store_stock_products_filter_';

	private $storeId = 0;
	private $defaultGridSort = [
		'PRODUCT_ID' => 'desc',
	];
	private $navParamName = 'page';
	private $catalogData = [];

	public function onPrepareComponentParams($arParams)
	{
		$arParams['STORE_ID'] = (int)$arParams['STORE_ID'];

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		$this->init();
		if (!$this->checkDocumentReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_NO_READ_RIGHTS_ERROR');
			$this->includeComponentTemplate();
			return;
		}


		$this->loadMeasures();
		$this->arResult['GRID'] = $this->prepareResult();
		$this->arResult['STORE_TITLE'] = htmlspecialcharsbx($this->getStoreTitle());
		if (empty($this->arResult['STORE_TITLE']))
		{
			$this->arResult['STORE_TITLE'] = Loc::getMessage('STORE_STOCK_PRODUCTS_DEFAULT_STORE_NAME');
		}

		$filterOptions = [
			'GRID_ID' => $this->getGridId(),
			'FILTER_ID' => $this->getFilterId(),
			'FILTER' => $this->getFilterFields(),
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'THEME' => Bitrix\Main\UI\Filter\Theme::LIGHT,
		];
		$this->arResult['FILTER_OPTIONS'] = $filterOptions;

		$this->includeComponentTemplate();
	}

	private function prepareResult(): array
	{
		$result = [];

		$gridId = $this->getGridId();
		$result['GRID_ID'] = $gridId;
		$result['COLUMNS'] = [
			[
				'id' => 'PRODUCT_ID',
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_PRODUCT_COLUMN'),
				'sort' => 'PRODUCT_ID',
				'default' => true,
				'type' => 'custom',
			],
			[
				'id' => 'AMOUNT',
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_AMOUNT_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 220,
			],
			[
				'id' => 'QUANTITY_RESERVED',
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_QUANTITY_RESERVED_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 220,
			],
			[
				'id' => 'QUANTITY',
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_QUANTITY_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 220,
			],
		];

		$gridOptions = new Bitrix\Main\Grid\Options($gridId);
		$navParams = $gridOptions->getNavParams();
		$pageSize = (int)$navParams['nPageSize'];
		$gridSort = $gridOptions->GetSorting(['sort' => $this->defaultGridSort]);

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$this->arResult['GRID']['ROWS'] = [];

		$queryParams = [
			'select' => ['ID' ,'PRODUCT_ID', 'AMOUNT', 'QUANTITY_RESERVED', 'MEASURE_ID' => 'PRODUCT.MEASURE'],
			'filter' => $this->getListFilter(),
			'offset' => $pageNavigation->getOffset(),
			'order' => $gridSort['sort'],
			'limit' => $pageNavigation->getLimit(),
		];

		$productData = StoreProductTable::getList($queryParams)->fetchAll();
		if ($productData)
		{
			$this->catalogData = $this->loadCatalog(array_column($productData, 'PRODUCT_ID'));

			foreach ($productData as $key => $item)
			{
				$item['QUANTITY'] = (float)$item['AMOUNT'] - (float)$item['QUANTITY_RESERVED'];
				$result['ROWS'][] = [
					'id' => $item['ID'],
					'data' => $item,
					'columns' => $this->prepareItemColumn($item),
				];
			}
		}
		else
		{
			$result['STUB']['title'] = Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_NO_PRODUCTS');
		}

		$totalCount = $this->getTotalCount();

		$pageNavigation->setRecordCount($totalCount);
		$result['NAV_PARAM_NAME'] = $this->navParamName;
		$result['CURRENT_PAGE'] = $pageNavigation->getCurrentPage();
		$result['NAV_OBJECT'] = $pageNavigation;
		$result['TOTAL_ROWS_COUNT'] = $totalCount;
		$result['AJAX_MODE'] = 'Y';
		$result['ALLOW_ROWS_SORT'] = false;
		$result['AJAX_OPTION_JUMP'] = 'N';
		$result['AJAX_OPTION_STYLE'] = 'N';
		$result['AJAX_OPTION_HISTORY'] = 'N';
		$result['AJAX_ID'] = \CAjax::GetComponentID('bitrix:main.ui.grid', '', '');
		$result['SHOW_PAGINATION'] = $totalCount > 0;
		$result['SHOW_NAVIGATION_PANEL'] = true;
		$result['SHOW_PAGESIZE'] = true;
		$result['PAGE_SIZES'] = [
			['NAME' => 10, 'VALUE' => 10],
			['NAME' => 20, 'VALUE' => 20],
			['NAME' => 50, 'VALUE' => 50],
			['NAME' => 100, 'VALUE' => 100],
			['NAME' => 200, 'VALUE' => 200],
			['NAME' => 500, 'VALUE' => 500],
		];
		$result['SHOW_ROW_CHECKBOXES'] = false;
		$result['SHOW_CHECK_ALL_CHECKBOXES'] = false;
		$result['SHOW_ACTION_PANEL'] = false;
		$result['SHOW_GRID_SETTINGS_MENU'] = false;
		$result['SHOW_SELECTED_COUNTER'] = false;
		$result['HANDLE_RESPONSE_ERRORS'] = true;

		return $result;
	}

	private function prepareItemColumn(array $item): array
	{
		$column = $item;

		$column['PRODUCT_ID'] = $this->getProductView($column);

		foreach (['AMOUNT', 'QUANTITY_RESERVED', 'QUANTITY'] as $totalField)
		{
			$column[$totalField] = $this->formatNumberWithMeasure($column[$totalField], (int)$column['MEASURE_ID']);
		}

		unset($column['MEASURE_ID']);

		return $column;
	}

	private function formatNumberWithMeasure($number, int $measureId)
	{
		if (!$measureId)
		{
			$measureId = $this->getDefaultMeasure()['ID'];
		}
		return Loc::getMessage(
			'STORE_STOCK_PRODUCTS_REPORT_MEASURE_TEMPLATE',
			[
				'#NUMBER#' => $number,
				'#MEASURE_SYMBOL#' => $this->getMeasureSymbol($measureId),
			]
		);
	}

	private function getMeasureSymbol(int $measureId): string
	{
		return htmlspecialcharsbx($this->measures[$measureId]['SYMBOL']);
	}

	private function getProductView(array $column): string
	{
		global $APPLICATION;

		$product = $this->catalogData[(int)$column['PRODUCT_ID']];

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:catalog.grid.product.field',
			'',
			[
				'BUILDER_CONTEXT' => $this->arParams['BUILDER_CONTEXT'],
				'GRID_ID' => $this->getGridId(),
				'ROW_ID' => $column['ID'],
				'GUID' => 'catalog_document_grid_' . $column['ID'],
				'PRODUCT_FIELDS' => [
					'ID' => $product['FIELDS']['PRODUCT_ID'],
					'NAME' => $product['FIELDS']['NAME'],
					'IBLOCK_ID' => $product['FIELDS']['IBLOCK_ID'],
					'SKU_IBLOCK_ID' => $product['FIELDS']['OFFERS_IBLOCK_ID'],
					'SKU_ID' => $product['FIELDS']['OFFER_ID'],
					'BASE_PRICE_ID' => $product['FIELDS']['BASE_PRICE_ID'],
				],
				'SKU_TREE' => $product['FIELDS']['SKU_TREE'],
				'MODE' => 'view',
				'ENABLE_SEARCH' => false,
				'ENABLE_IMAGE_CHANGE_SAVING' => false,
				'ENABLE_INPUT_DETAIL_LINK' => true,
				'ENABLE_EMPTY_PRODUCT_ERROR' => false,
				'ENABLE_SKU_SELECTION' => false,
				'HIDE_UNSELECTED_ITEMS' => true,
				'IS_NEW' => false,
			]
		);

		return ob_get_clean();
	}

	private function init(): void
	{
		$this->storeId = $this->arParams['STORE_ID'];
	}

	private function checkDocumentReadRights(): bool
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->canDoOperation('catalog_read');
	}

	private function getTotalCount(): int
	{
		return StoreProductTable::getCount($this->getListFilter());
	}

	private function getListFilter(): array
	{
		$filter = [
			'=STORE_ID' => $this->storeId,
			[
				'LOGIC' => 'OR',
				'!=AMOUNT' => 0,
				'!=QUANTITY_RESERVED' => 0,
			]
		];

		$searchString = trim($this->getFilterOptions()->getSearchString());
		if ($searchString)
		{
			$filter['%PRODUCT.IBLOCK_ELEMENT.SEARCHABLE_CONTENT'] = mb_strtoupper($searchString);
		}

		$userFilter = $this->getUserFilter();
		if (!empty($userFilter['PRODUCTS']))
		{
			$filter['=PRODUCT_ID'] = StoreStockFilter::prepareProductFilter($userFilter['PRODUCTS']);
		}

		return $filter;
	}

	private function getStoreTitle(): string
	{
		$storeData = \Bitrix\Catalog\StoreTable::getList([
			'select' => ['TITLE'],
			'filter' => ['=ID' => $this->storeId],
			'limit' => 1,
		])->fetch();

		return $storeData['TITLE'] ?? '';
	}

	private function getGridId(): string
	{
		return self::GRID_ID_PREFIX . $this->storeId;
	}

	private function getFilterId(): string
	{
		return self::FILTER_ID_PREFIX . $this->storeId;
	}

	private function getFilterFields(): array
	{
		Loc::loadMessages(\Bitrix\Main\Application::getDocumentRoot() . '/bitrix/modules/catalog/lib/integration/report/filter/storestockfilter.php');

		$entities = [];

		if (Loader::includeModule('crm'))
		{
			$entities[] = [
				'id' => 'product_variation',
				'options' => [
					'iblockId' => \Bitrix\Crm\Product\Catalog::getDefaultId(),
					'basePriceId' => \Bitrix\Crm\Product\Price::getBaseId(),
					'showPriceInCaption' => false,
				],
			];
		}

		return [
			'PRODUCTS' => [
				'id' => 'PRODUCTS',
				'name' => Loc::getMessage('STOCK_STOCK_PRODUCTS_FILTER_PRODUCTS_TITLE'),
				'type' => 'entity_selector',
				'default' => true,
				'partial' => true,
				'params' => [
					'multiple' => true,
					'showDialogOnEmptyInput' => false,
					'dropdownMode' => true,
					'dialogOptions' => [
						'hideOnSelect' => false,
						'context' => 'report_store_stock_products_filter_products',
						'entities' => $entities,
						'recentTabOptions' => [
							'stub' => true,
							'stubOptions' => [
								'title' => Loc::getMessage('STOCK_FILTER_PRODUCTS_STUB'),
							],
						],
						'events' => [
							'onBeforeSearch' => 'onBeforeDialogSearch',
						],
					],
				],
			],
		];
	}

	private function getUserFilter()
	{
		$filterOptions = $this->getFilterOptions();
		$filterFields = $this->getFilterFields();

		return $filterOptions->getFilterLogic($filterFields);
	}

	private function getFilterOptions(): \Bitrix\Main\UI\Filter\Options
	{
		static $filterOptions = null;
		if (is_null($filterOptions))
		{
			$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->getFilterId());
		}

		return $filterOptions;
	}
}
