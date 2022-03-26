<?php

use Bitrix\Catalog\Component\SkuTree;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\v2\Product\Product;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Main\ErrorCollection;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CBitrixComponent::includeComponentClass('bitrix:catalog.productcard.store.amount');

class CatalogProductStoreAmountDetailsComponent extends \CBitrixComponent implements  Controllerable, Errorable
{
	use ErrorableImplementation;

	private const GRID_NAME = 'productcard_store_amount_details';
	private const NAVIGATION_ID = 'page';
	private const DEFAULT_PAGE_SIZE = 5;
	private $navigation;
	private $measures;
	private $filterPresets;
	private $storeProductCount;
	private $storeProducts;
	private $gridId;
	private $productId;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	public function executeComponent()
	{
		$this->setProductId((int)$this->arParams['PRODUCT_ID']);

		if ($this->checkModules() && $this->checkPermissions() && $this->checkProduct())
		{
			$this->placePageTitle();
			$this->fillResult();
			$this->includeComponentTemplate();
		}

		if ($this->hasErrors())
		{
			$this->showErrors();
		}
	}

	private function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "catalog" is not installed.');

			return false;
		}

		return true;
	}

	private function checkPermissions(): bool
	{
		return true;
	}

	private function checkProduct(): bool
	{
		if (!$this->getProduct() instanceof Product)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(
				'PRODUCT_ENTITY parameter is not instance of Bitrix\Catalog\v2\Product\Product'
			);

			return false;
		}

		return true;
	}

	private function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	private function fillResult(): void
	{
		$this->arResult['IS_SHOWED_STORE_RESERVE'] = \Bitrix\Catalog\Config\State::isShowedStoreReserve();
		$this->arResult['GRID'] = $this->getGridData();
		$this->arResult['FILTER_PARAMS'] = $this->getFilterParams();
		$this->arResult['TOTAL_DATA'] = $this->getTotalData();
	}

	private function placePageTitle(): void
	{
		$title = HtmlFilter::encode($this->getProduct()->getName());
		global $APPLICATION;
		$APPLICATION->setTitle($title);
	}

	private function getFilterParams(): array
	{
		return [
			'FILTER_ID' => $this->getGridId(),
			'GRID_ID' => $this->getGridId(),
			'FILTER' => [
				[
					'id' => 'STORE',
					'name' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_STORE'),
					'type' => 'list',
					'items' => $this->getStoresForFilter(),
					'params' => [
						'multiple' => 'Y',
					],
					'filterable' => '',
					'default' => true,
				],
			],
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'ENABLE_LIVE_SEARCH' => false,
		];
	}

	private function setProductId(int $productId): void
	{
		$this->productId = $productId;
	}

	private function getProductId(): int
	{
		return $this->productId;
	}

	private function getGridId(): string
	{
		return self::GRID_NAME . '_' . $this->getProduct()->getId();
	}

	private function getNavigation(): PageNavigation
	{
		if (!isset($this->pageNavigation))
		{
			$this->pageNavigation = new PageNavigation(self::NAVIGATION_ID);
			$this->pageNavigation
				->allowAllRecords(false)
				->setPageSize($this->getPageSize())
				->initFromUri();

			$this->pageNavigation->setRecordCount($this->getStoreProductsCount());
		}

		return $this->pageNavigation;
	}

	private function getGridOptions(): Bitrix\Main\Grid\Options
	{
		if (!isset($this->gridOptions))
		{
			$this->gridOptions = new Bitrix\Main\Grid\Options($this->getGridId());
		}
		return $this->gridOptions;
	}

	private function getPageSize(): int
	{
		$navParams = $this->getGridOptions()->getNavParams();

		return (int)($navParams['nPageSize'] ?? self::DEFAULT_PAGE_SIZE);
	}

	private function getFilterPresets(): array
	{
		if (!$this->filterPresets)
		{
			$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->getGridId());

			if (!$this->isGridAction())
			{
				$filterOptions->setFilterSettings(
					\Bitrix\Main\UI\Filter\Options::TMP_FILTER,
					[
						'fields' => [
							'STORE' => [
								$this->getStoreIdFromRequest()
							]
						]
					],
					true,
					false
				);
				$filterOptions->save();
			}

			$this->filterPresets = $filterOptions->getFilter();
		}

		return $this->filterPresets;
	}

	private function isGridAction(): bool
	{
		return $this->request->get('grid_action') !== null || $this->request->get('action') !== null;
	}

	private function getStoreIdFromRequest(): ?int
	{
		return (int)$this->request->get('storeId');
	}

	private function getGridData(): array
	{
		return [
			'GRID_ID' => $this->getGridId(),
			'HEADERS' => $this->getGridHeaders(),
			'ROWS' => $this->getGridRows(),

			'AJAX_MODE' => 'Y',
			'AJAX_ID' => \CAjax::GetComponentID('bitrix:main.ui.grid', '', ''),
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',

			'ALLOW_COLUMNS_SORT' => true,
			'ALLOW_COLUMNS_RESIZE' => true,
			'ALLOW_HORIZONTAL_SCROLL' => true,
			'ALLOW_SORT' => false,
			'ALLOW_PIN_HEADER' => true,
			'ALLOW_CONTEXT_MENU' => true,

			'TOTAL_ROWS_COUNT' => $this->getStoreProductsCount(),

			'NAV_PARAM_NAME' => self::NAVIGATION_ID,
			'SHOW_NAVIGATION_PANEL' => true,
			'NAV_OBJECT' => $this->getNavigation(),
			'SHOW_PAGINATION' => $this->getStoreProductsCount() > 0,
			'CURRENT_PAGE' => $this->getNavigation()->getCurrentPage(),
			'SHOW_PAGESIZE' => true,
			'PAGE_SIZES' => [
				['NAME' => 5, 'VALUE' => 5],
				['NAME' => 10, 'VALUE' => 10],
				['NAME' => 20, 'VALUE' => 20],
				['NAME' => 50, 'VALUE' => 50],
			],
			'DEFAULT_PAGE_SIZE' => self::DEFAULT_PAGE_SIZE,

			'SHOW_CHECK_ALL_CHECKBOXES'=> false,
			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_ROW_ACTIONS_MENU'=> true,
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_SELECTED_COUNTER'=> false,
			'SHOW_TOTAL_COUNTER' => true,
			'SHOW_MORE_BUTTON'=> true,

			'SHOW_ACTION_PANEL' => false,
		];
	}

	private function getStoresForFilter(): array
	{
		return array_column($this->getStores(), 'TITLE', 'ID');
	}

	private function getStores(): array
	{
		$stores = StoreTable::getList([
			'select' => ['ID', 'TITLE'],
			'filter' => [
				'ACTIVE' => 'Y',
			]
		])->fetchAll();

		$storesMap = [];
		foreach ($stores as $store)
		{
			if (!$store['TITLE'])
			{
				$store['TITLE'] = Loc::getMessage('STORE_LIST_DETAILS_STORE_TITLE_WITHOUT_NAME');
			}
			$storesMap[(int)$store['ID']] = $store;
		}

		return $storesMap;
	}

	private function getProduct(): ?BaseProduct
	{
		if (!isset($this->product))
		{
			$repositoryFacade = ServiceContainer::getRepositoryFacade();
			$variation = $repositoryFacade->loadVariation($this->getProductId());
			if (!$variation)
			{
				return null;
			}
			$this->product = $variation->getParent();
		}

		return $this->product;
	}

	private function getGridHeaders(): array
	{
		if (!empty($this->headers))
		{
			return $this->headers;
		}

		$defaultWidth = 180;
		$defaultProductFieldWidth = 400;

		$headers = [
			[
				'id' => 'STORE',
				'name' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_STORE'),
				'title' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_STORE'),
				'sort' => 'TITLE',
				'type' => 'string',
				'width' => $defaultWidth,
				'default' => true,
			],
			[
				'id' => 'PRODUCT',
				'name' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_PRODUCT'),
				'title' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_PRODUCT'),
				'sort' => 'PRODUCT',
				'type' => 'custom',
				'width' => $defaultProductFieldWidth,
				'default' => true
			],
			[
				'id' => 'QUANTITY_COMMON',
				'name' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_QUANTITY_COMMON'),
				'title' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_QUANTITY_COMMON'),
				'sort' => 'QUANTITY_COMMON',
				'type' => 'number',
				'width' => $defaultWidth,
				'default' => true
			]
		];

		if ($this->arResult['IS_SHOWED_STORE_RESERVE'])
		{
			$headers[] = [
				'id' => 'QUANTITY_AVAILABLE',
				'name' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_QUANTITY_AVAILABLE'),
				'title' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_QUANTITY_AVAILABLE'),
				'sort' => 'QUANTITY_AVAILABLE',
				'type' => 'number',
				'width' => $defaultWidth,
				'default' => true
			];
			$headers[] = [
				'id' => 'QUANTITY_RESERVED',
				'name' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_QUANTITY_RESERVED1'),
				'title' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_QUANTITY_RESERVED1'),
				'sort' => 'QUANTITY_RESERVED',
				'type' => 'number',
				'width' => $defaultWidth,
				'default' => true
			];
		}

		$headers[] = [
			'id' => 'PURCHASING_PRICE',
			'name' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_PURCHASING_PRICE'),
			'title' => Loc::getMessage('STORE_LIST_DETAILS_HEADER_PURCHASING_PRICE'),
			'sort' => 'PURCHASING_PRICE',
			'type' => 'money',
			'width' => $defaultWidth,
			'default' => true
		];

		$this->headers = $headers;
		return $this->headers;
	}

	private function getGridRows(): array
	{
		$rows = [];
		$storeProducts = $this->getStoreProducts();

		foreach ($storeProducts as $storeProduct)
		{
			$storeProduct['AMOUNT'] = (float)$storeProduct['AMOUNT'];
			$storeProduct['QUANTITY_RESERVED'] = (float)$storeProduct['QUANTITY_RESERVED'];
			$storeTitle = $storeProduct['CATALOG_STORE_PRODUCT_STORE_TITLE']
				? HtmlFilter::encode($storeProduct['CATALOG_STORE_PRODUCT_STORE_TITLE'])
				: Loc::getMessage('STORE_LIST_DETAILS_STORE_TITLE_WITHOUT_NAME')
			;
			$rows[] = [
				'data' => [
					'STORE' => $storeTitle,
					'PRODUCT' => $this->getViewProductField(
						$storeProduct['ID'],
						$storeProduct['PRODUCT_ID']
					),
					'QUANTITY_COMMON' => $this->getFormattedQuantity(
						$storeProduct['PRODUCT_ID'],
						$storeProduct['AMOUNT']
					),
					'QUANTITY_AVAILABLE' => $this->getFormattedQuantity(
						$storeProduct['PRODUCT_ID'],
						$storeProduct['AMOUNT'] - $storeProduct['QUANTITY_RESERVED']
					),
					'QUANTITY_RESERVED' => $this->getFormattedQuantity(
						$storeProduct['PRODUCT_ID'],
						$storeProduct['QUANTITY_RESERVED']
					),
					'PURCHASING_PRICE' => $this->getFormattedPurchasingPrice(
						$storeProduct['PRODUCT_ID']
					),
				],
			];
		}

		return $rows;
	}

	private function getFormattedQuantity($skuId, $quantity): ?string
	{
		$sku = $this->getSkuById($skuId);
		if (!$sku)
		{
			return null;
		}

		$measure = $this->getMeasure($sku->getField('MEASURE'));

		return Loc::getMessage(
			'STORE_LIST_DETAILS_QUANTITY_MEASURE',
			['#QUANTITY#' => $quantity, '#MEASURE#' => $measure]
		);
	}

	private function getFormattedPurchasingPrice($skuId)
	{
		$sku = $this->getSkuById($skuId);
		if (!$sku)
		{
			return null;
		}

		return CCurrencyLang::CurrencyFormat(
			$sku->getField('PURCHASING_PRICE'),
			$sku->getField('PURCHASING_CURRENCY'),
			true
		);
	}

	private function getSkuById($skuId)
	{
		return $this->getProduct()->getSkuCollection()->findById((int)$skuId);
	}

	private function getViewProductField($rowId, $skuId)
	{
		global $APPLICATION;

		$sku = $this->getSkuById($skuId);
		if (!$sku)
		{
			return null;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:catalog.grid.product.field',
			'',
			[
				'BUILDER_CONTEXT' => $this->arParams['BUILDER_CONTEXT'],
				'GRID_ID' => $this->getGridId(),
				'ROW_ID' => $rowId,
				'GUID' => 'catalog_document_grid_' . $rowId,
				'PRODUCT_FIELDS' => [
					'ID' => $this->getProduct()->getId(),
					'NAME' => $this->getProduct()->getName(),
					'IBLOCK_ID' => $this->getProduct()->getIblockId(),
					'SKU_IBLOCK_ID' => $sku->getIblockId(),
					'SKU_ID' => $sku->getId(),
				],
				'SKU_TREE' => $this->getSkuTreeById($sku->getId()),
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

	private function getSkuTreeById($skuId)
	{
		/** @var SkuTree $skuTree */
		$skuTree = ServiceContainer::make('sku.tree', ['iblockId' => $this->getProduct()->getIblockId()]);
		if (!$skuTree)
		{
			return null;
		}

		$skuTreeItems = $skuTree->loadJsonOffers([$this->getProduct()->getId() => [$skuId]]);
		if (!$skuTreeItems)
		{
			return null;
		}

		return $skuTreeItems[$this->getProduct()->getId()][$skuId];
	}

	private function getStoreFilter(): ?array
	{
		$skuCollection = $this->getProduct()->getSkuCollection();
		$skuIds = array_keys($skuCollection->toArray());

		$filter = [
			'=PRODUCT_ID' => $skuIds,
			'=STORE.ACTIVE' => 'Y',
			[
				'LOGIC' => 'OR',
				'!=AMOUNT' => '0',
				'!=QUANTITY_RESERVED' => '0',
			],
		];

		$filteredStoreIds = $this->getFilterPresets()['STORE'];
		if ($filteredStoreIds)
		{
			$filter['=STORE_ID'] = $filteredStoreIds;
		}

		return $filter;
	}

	private function getStoreProducts(): ?array
	{
		if (!$this->storeProducts)
		{
			$this->storeProducts = StoreProductTable::getList([
				'select' => ['*', 'STORE.TITLE'],
				'filter' => $this->getStoreFilter(),
				'order' => [
					'STORE.SORT' => 'ASC'
				],
				'limit' => $this->getPageSize(),
				'offset' => $this->getNavigation()->getOffset(),
			])->fetchAll();
		}

		return $this->storeProducts;
	}

	private function getStoreProductsCount(): int
	{
		if (!$this->storeProductCount)
		{
			$this->storeProductCount = StoreProductTable::getCount($this->getStoreFilter());
		}

		return $this->storeProductCount;
	}

	protected function getTotalData(): array
	{
		return [
			'QUANTITY_AVAILABLE' => $this->getHtmlTotalQuantities('QUANTITY_AVAILABLE'),
			'QUANTITY_RESERVED' => $this->getHtmlTotalQuantities('QUANTITY_RESERVED'),
			'QUANTITY_COMMON' => $this->getHtmlTotalQuantities('QUANTITY_COMMON'),
			'PRICE' => $this->getHtmlTotalPrices(),
		];
	}

	private function getHtmlTotalQuantities($quantitiesTypeName): string
	{
		$formattedQuantities = $this->getFormattedTotalQuantities($quantitiesTypeName);
		$htmlTotalQuantities = '';
		foreach ($formattedQuantities as $quantity)
		{
			$htmlTotalQuantities .= $quantity . '<br>';
		}

		return $htmlTotalQuantities;
	}

	private function getHtmlTotalPrices(): string
	{
		$formattedPrices = $this->getFormattedTotalPrices();
		$htmlTotalPrices = '';
		foreach ($formattedPrices as $price)
		{
			$htmlTotalPrices .= $price . '<br>';
		}

		return $htmlTotalPrices;
	}

	private function getFormattedTotalQuantities($quantityTypeName): array
	{
		$totalQuantitiesGroupedByMeasures = $this->getTotalQuantitiesGroupedByMeasures($quantityTypeName);
		$viewTotalQuantities = [];

		foreach ($totalQuantitiesGroupedByMeasures as $totalQuantity)
		{
			$viewQuantity =
				'<span class="total-info-value">'
				. $totalQuantity['quantity']
				. '</span>&nbsp;'
				. $this->getMeasure($totalQuantity['measure'])
			;
			$viewTotalQuantities[] = $viewQuantity;
		}

		return $viewTotalQuantities;
	}

	private function getTotalQuantitiesGroupedByMeasures($quantityTypeName): array
	{
		$quantitiesGroupedByMeasures = [];
		$storeProducts = $this->getStoreProducts();

		foreach ($storeProducts as $storeProduct)
		{
			$currentSku = $this->getProduct()->getSkuCollection()->findById($storeProduct['PRODUCT_ID']);
			if (!$currentSku)
			{
				continue;
			}

			if ($quantityTypeName === 'QUANTITY_COMMON')
			{
				$quantity = $storeProduct['AMOUNT'];
			}
			else if ($quantityTypeName === 'QUANTITY_AVAILABLE')
			{
				$quantity = $storeProduct['AMOUNT'] - $storeProduct['QUANTITY_RESERVED'];
			}
			else
			{
				$quantity = $storeProduct['QUANTITY_RESERVED'];
			}

			$measure = $currentSku->getField('MEASURE');
			if (empty($quantitiesGroupedByMeasures[$measure]))
			{
				$quantitiesGroupedByMeasures[$measure] = [
					'quantity' => $quantity,
					'measure' => $measure,
				];
			}
			else
			{
				$quantitiesGroupedByMeasures[$measure]['quantity'] += $quantity;
			}
		}

		return $quantitiesGroupedByMeasures;
	}

	private function getFormattedTotalPrices(): array
	{
		$totalPricesGroupedByCurrencies = $this->getTotalPricesGroupedByCurrencies();
		$viewTotalPrices = [];

		foreach ($totalPricesGroupedByCurrencies as $totalPrice)
		{
			$viewPrice =
				'<span class="total-info-value">'
				. CCurrencyLang::CurrencyFormat(
					$totalPrice['purchasingPrice'],
					$totalPrice['purchasingCurrency'],
					false
				)
				.'</span>'
			;
			$formattedViewPrice = CCurrencyLang::getPriceControl(
				$viewPrice,
				(string)$totalPrice['purchasingCurrency'],
			);
			$viewTotalPrices[] = $formattedViewPrice;
		}

		return $viewTotalPrices;
	}

	private function getTotalPricesGroupedByCurrencies(): array
	{
		$totalPricesGroupedByCurrency = [];
		$storeProducts = $this->getStoreProducts();

		foreach ($storeProducts as $storeProduct)
		{
			$currentSku = $this->getProduct()->getSkuCollection()->findById($storeProduct['PRODUCT_ID']);
			if (!$currentSku)
			{
				continue;
			}

			$purchasingCurrency = $currentSku->getField('PURCHASING_CURRENCY');
			$purchasingPrice = $currentSku->getField('PURCHASING_PRICE');
			$quantity = $storeProduct['AMOUNT'];
			if (empty($totalPricesGroupedByCurrency[$purchasingCurrency]))
			{
				$totalPricesGroupedByCurrency[$purchasingCurrency] = [
					'purchasingPrice' => $purchasingPrice * $quantity,
					'purchasingCurrency' => $purchasingCurrency,
				];
			}
			else
			{
				$totalPricesGroupedByCurrency[$purchasingCurrency]['purchasingPrice'] += $purchasingPrice * $quantity;
			}
		}

		return $totalPricesGroupedByCurrency;
	}

	private function getMeasure(?int $measureId): string
	{
		if ($measureId === null)
		{
			return $this->getDefaultMeasure();
		}

		if (!isset($this->measures[$measureId]))
		{
			$this->measures[$measureId] = HtmlFilter::encode(
				CCatalogMeasure::getList([], ['=ID' => $measureId])->Fetch()['SYMBOL']
			);
		}

		return $this->measures[$measureId];
	}

	private function getDefaultMeasure(): string
	{
		if (!isset($this->defaultMeasure))
		{
			$fetchedMeasure = CCatalogMeasure::getList([], ['=IS_DEFAULT' => 'Y'])->Fetch();
			if ($fetchedMeasure)
			{
				$this->defaultMeasure = HtmlFilter::encode($fetchedMeasure['SYMBOL']);
			}
			else
			{
				$this->defaultMeasure = '';
			}
		}

		return $this->defaultMeasure;
	}

	public function updateTotalDataAction($productId): ?array
	{
		$this->setProductId((int)$productId);

		if (!$this->checkModules() || !$this->checkPermissions() || !$this->checkProduct())
		{
			return null;
		}

		return [
			'TOTAL_DATA' => $this->getTotalData(),
		];
	}

	public function configureActions()
	{
		return [];
	}
}