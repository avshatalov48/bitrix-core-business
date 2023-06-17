<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Internals\ShipmentItemStoreTable;
use Bitrix\Sale\Internals\ShipmentItemTable;
use Bitrix\Sale\Internals\ShipmentTable;

abstract class ReportProductList extends ProductList
{
	protected int $storeId = 0;
	protected string $navParamName = 'page';
	protected array $catalogData = [];
	protected array $defaultGridSort = ['PRODUCT_ID' => 'desc'];
	protected string $reportFilterClass;
	protected \Bitrix\Main\Grid\Options $gridOptions;

	abstract protected function getGridId(): string;

	abstract protected function getFilterId(): string;

	abstract protected function prepareProductFilter(array $productIds): array;

	abstract protected function getProductFilterDialogContext(): string;

	abstract protected function getReceivedQuantityData(int $storeId, array $formattedFilter): array;

	abstract protected function getOutgoingQuantityData(int $storeId, array $formattedFilter): array;

	abstract protected function getAmountSoldData(int $storeId, array $formattedFilter): array;

	abstract protected function getGridColumns(): array;

	protected static function getEmptyStub(): string
	{
		return Loc::getMessage('CATALOG_REPORT_PRODUCT_LIST_NO_PRODUCTS');
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams['STORE_ID'] = (int)$arParams['STORE_ID'];

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			$this->includeComponentTemplate();

			return;
		}

		if (!$this->checkDocumentReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CATALOG_REPORT_PRODUCT_LIST_NO_READ_RIGHTS_ERROR');
			$this->includeComponentTemplate();

			return;
		}

		$this->init();

		$this->loadMeasures();
		$this->arResult['GRID'] = $this->getGridData();
		$this->arResult['STORE_TITLE'] = htmlspecialcharsbx($this->getStoreTitle());
		if (empty($this->arResult['STORE_TITLE']))
		{
			$this->arResult['STORE_TITLE'] = Loc::getMessage('CATALOG_REPORT_PRODUCT_LIST_DEFAULT_STORE_NAME');
		}

		$filterOptions = [
			'GRID_ID' => $this->getGridId(),
			'FILTER_ID' => $this->getFilterId(),
			'FILTER' => $this->getFilterFields(),
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'THEME' => \Bitrix\Main\UI\Filter\Theme::LIGHT,
		];
		$this->arResult['FILTER_OPTIONS'] = $filterOptions;

		$this->includeComponentTemplate();
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->arResult['ERROR_MESSAGES'][] = 'Module Catalog is not installed';

			return false;
		}

		if (!Loader::includeModule('report'))
		{
			$this->arResult['ERROR_MESSAGES'][] = 'Module Report is not installed';

			return false;
		}

		return true;
	}

	protected function getGridRows(): ?array
	{
		$productData = $this->getProductData();
		if (!$productData)
		{
			return null;
		}

		$rows = [];

		$this->catalogData = $this->loadCatalog(array_column($productData, 'PRODUCT_ID'));

		$formattedFilter = $this->getFormattedFilter();
		$receivedQuantityData = $this->getReceivedQuantityData($this->storeId, $formattedFilter);
		$outgoingQuantityData = $this->getOutgoingQuantityData($this->storeId, $formattedFilter);
		$amountSoldData = $this->getAmountSoldData($this->storeId, $formattedFilter);

		$receivedQuantityAmountDifferenceData = [];
		$outgoingQuantityAmountDifferenceData = [];
		$amountSoldAmountDifferenceData = [];

		if (!empty($formattedFilter['REPORT_INTERVAL']))
		{
			$differenceFilter = $formattedFilter;
			$currentTime = new DateTime();
			$filterTimeTo = new DateTime($differenceFilter['REPORT_INTERVAL']['TO']);
			if ($currentTime > $filterTimeTo)
			{
				$differenceFilter['REPORT_INTERVAL']['FROM'] = $differenceFilter['REPORT_INTERVAL']['TO'];
				$differenceFilter['REPORT_INTERVAL']['TO'] = (new DateTime())->toString();
				$receivedQuantityAmountDifferenceData = $this->getReceivedQuantityData($this->storeId, $differenceFilter);
				$outgoingQuantityAmountDifferenceData = $this->getOutgoingQuantityData($this->storeId, $differenceFilter);
				$amountSoldAmountDifferenceData = $this->getAmountSoldData($this->storeId, $differenceFilter);
			}
		}

		foreach ($productData as $key => $item)
		{
			$receivedQuantityAmountDifference = (float)($receivedQuantityAmountDifferenceData[$item['PRODUCT_ID']] ?? 0);
			$outgoingQuantityAmountDifference = (float)($outgoingQuantityAmountDifferenceData[$item['PRODUCT_ID']] ?? 0);
			$amountSoldAmountDifference = (float)($amountSoldAmountDifferenceData[$item['PRODUCT_ID']] ?? 0);
			$item['AMOUNT'] =
				$item['AMOUNT']
				- $receivedQuantityAmountDifference
				+ $outgoingQuantityAmountDifference
				+ $amountSoldAmountDifference
			;

			$receivedQuantity = (float)($receivedQuantityData[$item['PRODUCT_ID']] ?? 0);
			$outgoingQuantity = (float)($outgoingQuantityData[$item['PRODUCT_ID']] ?? 0);
			$amountSold = (float)($amountSoldData[$item['PRODUCT_ID']] ?? 0);
			$item['STARTING_QUANTITY'] = (float)$item['AMOUNT'] - $receivedQuantity + $outgoingQuantity + $amountSold;
			$item['RECEIVED_QUANTITY'] = (float)($receivedQuantityData[$item['PRODUCT_ID']] ?? 0);
			$item['AMOUNT_SOLD'] = (float)($amountSoldData[$item['PRODUCT_ID']] ?? 0);
			$item['QUANTITY'] = (float)$item['AMOUNT'] - (float)$item['QUANTITY_RESERVED'];
			$rows[] = [
				'id' => $item['ID'],
				'data' => $item,
				'columns' => $this->prepareItemColumn($item),
			];
		}

		return $rows;
	}

	protected function getGridData(): array
	{
		$navParams = $this->gridOptions->getNavParams();
		$pageSize = (int)$navParams['nPageSize'];

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$totalCount = $this->getTotalCount();

		$pageNavigation->setRecordCount($totalCount);

		return [
			'GRID_ID' => $this->getGridId(),
			'COLUMNS' => $this->getGridColumns(),
			'ROWS' => $this->getGridRows(),
			'STUB' => $totalCount <= 0 ? ['title' => static::getEmptyStub()] : null,

			'NAV_PARAM_NAME' => $this->navParamName,
			'CURRENT_PAGE' => $pageNavigation->getCurrentPage(),
			'NAV_OBJECT' => $pageNavigation,
			'TOTAL_ROWS_COUNT' => $totalCount,
			'AJAX_MODE' => 'Y',
			'ALLOW_ROWS_SORT' => false,
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
			'AJAX_ID' => \CAjax::GetComponentID('bitrix:main.ui.grid', '', ''),
			'SHOW_PAGINATION' => $totalCount > 0,
			'SHOW_NAVIGATION_PANEL' => true,
			'SHOW_PAGESIZE' => true,

			'PAGE_SIZES' => [
				['NAME' => '10', 'VALUE' => '10'],
				['NAME' => '20', 'VALUE' => '20'],
				['NAME' => '50', 'VALUE' => '50'],
				['NAME' => '100', 'VALUE' => '100'],
				['NAME' => '200', 'VALUE' => '200'],
				['NAME' => '500', 'VALUE' => '500'],
			],

			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_CHECK_ALL_CHECKBOXES' => false,
			'SHOW_ACTION_PANEL' => false,
			'SHOW_GRID_SETTINGS_MENU' => false,
			'SHOW_SELECTED_COUNTER' => false,
			'HANDLE_RESPONSE_ERRORS' => true,
		];
	}

	protected function getProductData(): array
	{
		$navParams = $this->gridOptions->getNavParams();
		$pageSize = (int)$navParams['nPageSize'];
		$gridSort = $this->gridOptions->GetSorting(['sort' => $this->defaultGridSort]);

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$this->arResult['GRID']['ROWS'] = [];

		$offset = $pageNavigation->getOffset();
		$order = $gridSort['sort'];
		$limit = $pageNavigation->getLimit();

		$query = $this->buildDataQuery($order, $limit, $offset);

		return $query->exec()->fetchAll();
	}

	/**
	 * In this report we don't have to display products that haven't been stored in the store
	 * at any point during the report period, so here we are looking for products that are
	 * not in the store right now but that might have been there before (meaning that they have
	 * been sold or moved to another store)
	 *
	 * @param $order
	 * @param $limit
	 * @param $offset
	 * @return Query
	 */
	protected function buildDataQuery($order = null, $limit = null, $offset = null): Query
	{
		$storeId = $this->storeId;

		$baseFilter = $this->getListFilter();
		unset($baseFilter['=STORE_ID']);
		$reportInterval = $baseFilter['REPORT_INTERVAL'] ?? [];
		unset($baseFilter['REPORT_INTERVAL']);

		$storeDocsFilter = $baseFilter + [
				'=STORE_ID' => $storeId,
				[
					'LOGIC' => 'OR',
					'=DOCS_ELEMENT.STORE_FROM' => $storeId,
					'=DOCS_ELEMENT.STORE_TO' => $storeId,
				],
				'=DOCUMENT.STATUS' => 'Y',
			];

		if (!empty($reportInterval))
		{
			$storeDocsFilter += [
				'<=DOCUMENT.DATE_STATUS' => new DateTime($reportInterval['TO']),
			];
		}

		$storeDocsQuery = StoreProductTable::query();
		$storeDocsQuery->setSelect(['ID' ,'PRODUCT_ID', 'AMOUNT', 'QUANTITY_RESERVED', 'MEASURE_ID' => 'PRODUCT.MEASURE']);
		$storeDocsQuery->registerRuntimeField(
			new Reference(
				'DOCS_ELEMENT',
				StoreDocumentElementTable::class,
				Join::on('this.PRODUCT_ID', 'ref.ELEMENT_ID')
			)
		);
		$storeDocsQuery->registerRuntimeField(
			new Reference(
				'DOCUMENT',
				StoreDocumentTable::class,
				Join::on('this.DOCS_ELEMENT.DOC_ID', 'ref.ID')
			)
		);
		$storeDocsQuery->setFilter($storeDocsFilter);

		$shipmentsFilter = $baseFilter + [
				'=STORE_ID' => $storeId,
				'=STORE_BARCODE.STORE_ID' => $storeId,
				'=ORDER_DELIVERY.DEDUCTED' => 'Y',
			];

		if (!empty($reportInterval))
		{
			$shipmentsFilter += [
				'<=ORDER_DELIVERY.DATE_DEDUCTED' => new DateTime($reportInterval['TO']),
			];
		}

		$shipmentsQuery = StoreProductTable::query();
		$shipmentsQuery->setSelect(['ID' ,'PRODUCT_ID', 'AMOUNT', 'QUANTITY_RESERVED', 'MEASURE_ID' => 'PRODUCT.MEASURE']);
		$shipmentsQuery->registerRuntimeField(
			new Reference(
				'BASKET',
				BasketTable::class,
				Join::on('this.PRODUCT_ID', 'ref.PRODUCT_ID')
			)
		);
		$shipmentsQuery->registerRuntimeField(
			new Reference(
				'SHIPMENT_ITEM',
				ShipmentItemTable::class,
				Join::on('this.BASKET.ID', 'ref.BASKET_ID')
			)
		);
		$shipmentsQuery->registerRuntimeField(
			new Reference(
				'STORE_BARCODE',
				ShipmentItemStoreTable::class,
				Join::on('this.SHIPMENT_ITEM.ID', 'ref.ORDER_DELIVERY_BASKET_ID')
			)
		);
		$shipmentsQuery->registerRuntimeField(
			new Reference(
				'ORDER_DELIVERY',
				ShipmentTable::class,
				Join::on('this.SHIPMENT_ITEM.ORDER_DELIVERY_ID', 'ref.ID')
			)
		);
		$shipmentsQuery->setFilter($shipmentsFilter);

		$storeDocsQuery->union($shipmentsQuery);

		if (isset($order))
		{
			$storeDocsQuery->setUnionOrder($order);
		}
		if (isset($limit))
		{
			$storeDocsQuery->setUnionLimit($limit);
		}
		if (isset($offset))
		{
			$storeDocsQuery->setUnionOffset($offset);
		}

		$storeDocsQuery->countTotal(true);

		return $storeDocsQuery;
	}

	protected function getFormattedFilter(): array
	{
		$result = [];

		$incomingFilter = $this->arParams['INCOMING_FILTER'] ?? [];

		if (!empty($incomingFilter))
		{
			if (!empty($incomingFilter['PRODUCTS']))
			{
				$result['PRODUCTS'] = $this->prepareProductFilter($incomingFilter['PRODUCTS']);
			}

			if
			(
				!empty($incomingFilter['REPORT_INTERVAL_from'])
				&& !empty($incomingFilter['REPORT_INTERVAL_to'])
			)
			{
				$result['REPORT_INTERVAL'] = [
					'FROM' => $incomingFilter['REPORT_INTERVAL_from'],
					'TO' => $incomingFilter['REPORT_INTERVAL_to'],
				];
			}
		}
		else
		{
			$getListFilter = $this->getListFilter();
			if (!empty($getListFilter['=PRODUCT_ID']))
			{
				$result['PRODUCTS'] = $getListFilter['=PRODUCT_ID'];
			}

			$userFilter = $this->getUserFilter();
			if
			(
				!empty($userFilter['>=REPORT_INTERVAL'])
				&& !empty($userFilter['<=REPORT_INTERVAL'])
			)
			{
				$result['REPORT_INTERVAL'] = [
					'FROM' => $userFilter['>=REPORT_INTERVAL'],
					'TO' => $userFilter['<=REPORT_INTERVAL'],
				];
			}
		}

		return $result;
	}

	protected function prepareItemColumn(array $item): array
	{
		$column = $item;

		$column['PRODUCT_ID'] = $this->getProductView($column);

		foreach (['STARTING_QUANTITY', 'RECEIVED_QUANTITY', 'AMOUNT', 'QUANTITY_RESERVED', 'QUANTITY', 'AMOUNT_SOLD'] as $totalField)
		{
			$column[$totalField] = $this->formatNumberWithMeasure($column[$totalField], (int)$column['MEASURE_ID']);
		}

		unset($column['MEASURE_ID']);

		$column['QUANTITY_RESERVED'] = $this->getReservedDealListLink((int)$item['PRODUCT_ID'], $column['QUANTITY_RESERVED']);

		return $column;
	}

	protected function getReservedDealListLink(int $productId, string $quantityReservedView): string
	{
		return
			'<a
				class="main-grid-cell-content-store-amount-reserved-quantity"
				onclick="BX.SidePanel.Instance.open(\'' . $this->getReservedDealsSliderLink($productId) . '\')"
			>' . $quantityReservedView . '</a>'
			;
	}

	protected function getReservedDealsSliderLink(int $productId): string
	{
		$sliderUrl = \CComponentEngine::makeComponentPath('bitrix:catalog.productcard.reserved.deal.list');
		$sliderUrl = getLocalPath('components'.$sliderUrl.'/slider.php');
		$sliderUrlEntity = new \Bitrix\Main\Web\Uri($sliderUrl);
		$sliderUrlEntity->addParams([
			'storeId' => $this->storeId,
			'productId' => $productId,
		]);

		return $sliderUrlEntity->getUri();
	}

	protected function formatNumberWithMeasure($number, int $measureId)
	{
		if (!$measureId)
		{
			$measureId = $this->getDefaultMeasure()['ID'];
		}
		return Loc::getMessage(
			'CATALOG_REPORT_PRODUCT_LIST_MEASURE_TEMPLATE',
			[
				'#NUMBER#' => $number,
				'#MEASURE_SYMBOL#' => $this->getMeasureSymbol($measureId),
			]
		);
	}

	protected function getMeasureSymbol(int $measureId): string
	{
		return htmlspecialcharsbx($this->measures[$measureId]['SYMBOL']);
	}

	protected function getProductView(array $column): string
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
				'VIEW_FORMAT' => 'short',
				'ENABLE_SEARCH' => false,
				'ENABLE_IMAGE_CHANGE_SAVING' => false,
				'ENABLE_IMAGE_INPUT' => false,
				'ENABLE_INPUT_DETAIL_LINK' => true,
				'ENABLE_EMPTY_PRODUCT_ERROR' => false,
				'ENABLE_SKU_SELECTION' => false,
				'HIDE_UNSELECTED_ITEMS' => true,
				'IS_NEW' => false,
			]
		);

		return ob_get_clean();
	}

	protected function init(): void
	{
		$this->storeId = $this->arParams['STORE_ID'];
		$this->gridOptions = new \Bitrix\Main\Grid\Options($this->getGridId());

		if ($this->arParams['OPENED_FROM_REPORT'])
		{
			$this->getFilterOptions()->reset();
		}

		if (isset($this->arParams['INCOMING_FILTER']) && is_array($this->arParams['INCOMING_FILTER']))
		{
			$this->initFilterFromIncomingData($this->arParams['INCOMING_FILTER']);
		}
	}

	protected function initFilterFromIncomingData(array $incomingFilter): void
	{
		$filterFields = [];
		if (isset($incomingFilter['PRODUCTS'], $incomingFilter['PRODUCTS_label']))
		{
			$filterFields['PRODUCTS'] = $incomingFilter['PRODUCTS'];
			$filterFields['PRODUCTS_label'] = $incomingFilter['PRODUCTS_label'];
		}

		if (count($filterFields) > 0)
		{
			$this->setFilterFields($filterFields);
		}
	}

	protected function setFilterFields(array $filterFields): void
	{
		$filterOptions = $this->getFilterOptions();
		$currentFilterSettings = $filterOptions->getFilterSettings('tmp_filter');
		$currentFilterSettings['fields'] = $filterFields;
		$filterOptions->setFilterSettings(
			\Bitrix\Main\UI\Filter\Options::TMP_FILTER,
			$currentFilterSettings,
			true,
			false
		);
		$filterOptions->save();
	}

	protected function checkDocumentReadRights(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}

	protected function getTotalCount(): int
	{
		return $this->buildDataQuery()->exec()->getCount();
	}

	protected function getListFilter(): array
	{
		$filter = [
			'=STORE_ID' => $this->storeId,
		];

		$searchString = trim($this->getFilterOptions()->getSearchString());
		if ($searchString)
		{
			$filter['%PRODUCT.IBLOCK_ELEMENT.SEARCHABLE_CONTENT'] = mb_strtoupper($searchString);
		}

		$userFilter = $this->getUserFilter();
		if (!empty($userFilter['PRODUCTS']))
		{
			$filter['=PRODUCT_ID'] = $this->prepareProductFilter($userFilter['PRODUCTS']);
		}

		if
		(
			!empty($userFilter['>=REPORT_INTERVAL'])
			&& !empty($userFilter['<=REPORT_INTERVAL'])
		)
		{
			$filter['REPORT_INTERVAL'] = [
				'FROM' => $userFilter['>=REPORT_INTERVAL'],
				'TO' => $userFilter['<=REPORT_INTERVAL'],
			];
		}

		return $filter;
	}

	protected function getStoreTitle(): string
	{
		$storeData = \Bitrix\Catalog\StoreTable::getList([
			'select' => ['TITLE'],
			'filter' => ['=ID' => $this->storeId],
			'limit' => 1,
		])->fetch();

		return $storeData['TITLE'] ?? '';
	}

	protected function getFilterFields(): array
	{
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
				'name' => Loc::getMessage('CATALOG_REPORT_PRODUCT_LIST_FILTER_PRODUCTS_TITLE'),
				'type' => 'entity_selector',
				'default' => true,
				'partial' => true,
				'params' => [
					'multiple' => true,
					'showDialogOnEmptyInput' => false,
					'dropdownMode' => true,
					'dialogOptions' => [
						'hideOnSelect' => false,
						'context' => $this->getProductFilterDialogContext(),
						'entities' => $entities,
						'recentTabOptions' => [
							'stub' => true,
							'stubOptions' => [
								'title' => Loc::getMessage('CATALOG_REPORT_PRODUCT_LIST_PRODUCT_FILTER_STUB'),
							],
						],
						'events' => [
							'onBeforeSearch' => 'onBeforeDialogSearch',
						],
					],
				],
			]
		];
	}

	protected function getUserFilter(): array
	{
		$filterOptions = $this->getFilterOptions();
		$filterFields = $this->getFilterFields();

		return $filterOptions->getFilterLogic($filterFields);
	}

	protected function getFilterOptions(): \Bitrix\Main\UI\Filter\Options
	{
		static $filterOptions = null;
		if (is_null($filterOptions))
		{
			$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->getFilterId());
		}

		return $filterOptions;
	}
}
