<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\v2\Product\Product;
use Bitrix\Catalog\Component\StoreAmount;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Entity;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductStoreAmountComponent
	extends \CBitrixComponent
	implements  Controllerable, Errorable
{
	// Errorable implementation trait
	use ErrorableImplementation;

	protected const NAV_PARAM_NAME = 'page';
	protected const DEFAULT_PAGE_SIZE = 20;

	protected $pageNavigation;
	protected $gridOptions;

	protected $storeAmount;

	protected $storesCount;
	protected $stores;
	protected $storeTotal;

	protected $productId;

	protected $headers = [];

	protected $defaultMeasure;

	private AccessController $accessController;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->accessController = AccessController::getCurrent();
	}

	public function executeComponent()
	{
		if ($this->checkModules() && $this->checkProductId())
		{
			if ($this->checkPermissions())
			{
				$this->initializeStoreAmountGrid();
				$this->includeComponentTemplate();
			}
			else
			{
				$this->includeComponentTemplate('access_denied');
				return;
			}
		}

		if ($this->hasErrors())
		{
			$this->showErrors();
		}
	}

	/**
	 * Check for installed module 'catalog'
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "catalog" is not installed.');

			return false;
		}

		return true;
	}

	/**
	 * Check if new product card feature is enabled
	 * @return bool
	 */
	protected function checkPermissions(): bool
	{
		if (!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			return false;
		}

		$availableStores = $this->accessController->getPermissionValue(ActionDictionary::ACTION_STORE_VIEW);
		if (empty($availableStores))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check that arParams['PRODUCT_ID'] param is given
	 * @return bool
	 */
	protected function checkProductId(): bool
	{
		if (!$this->getProductId())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(' parameter PRODUCT_ID not found while execute component');

			return false;
		}

		return true;
	}

	protected function getStoreAmount(): StoreAmount
	{
		if (!$this->storeAmount)
		{
			$this->storeAmount = new StoreAmount($this->getProductId());
		}

		return $this->storeAmount;
	}


	/**
	 * Get product Id from PRODUCT_ENTITY param
	 * @return int
	 */
	protected function getProductId(): int
	{
		if (!isset($this->product))
		{
			$this->productId = (int)$this->arParams['PRODUCT_ID'];
		}

		return $this->productId;
	}

	protected function getProductIblockId(): int
	{
		return (int) $this->arParams['PRODUCT_IBLOCK_ID'];
	}

	/**
	 * Show all errors from errorCollection
	 */
	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	/**
	 * Initialize arResult data
	 */
	protected function initializeStoreAmountGrid(): void
	{
		if (Config\State::isUsedInventoryManagement())
		{
			$this->arResult['GRID'] = $this->getGridData();

			$this->arResult['TOTAL_WRAPPER_ID'] = $this->getTotalWrapperId();
			$this->arResult['STORE_RESERVE_ENABLE'] = Config\State::isShowedStoreReserve();
			$this->arResult['RESERVED_DEALS_SLIDER_LINK'] = $this->getReservedDealsSliderLink();

			$this->arResult['IM_LINK'] = null;
		}
		else
		{
			$sliderPath = \CComponentEngine::makeComponentPath('bitrix:catalog.warehouse.master.clear');
			$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');

			$this->arResult['GRID'] = $this->getEmptyGridData();

			$this->arResult['TOTAL_WRAPPER_ID'] = null;

			$this->arResult['IM_LINK'] = $sliderPath;
		}

		$this->arResult['PRODUCT_ID'] = $this->getProductId();
		$this->arResult['SIGNED_PARAMS'] = $this->getStoreAmount()->getStoreAmountSignedParameters();
	}

	/**
	 * Return data for Empty Grid with turn off Inventory Management
	 * @return array
	 */
	protected function getEmptyGridData()
	{
		$imDisableStubTitle = Loc::getMessage('STORE_LIST_GRID_IM_STUB_TITLE');
		$imDisableStubLinkTitle = Loc::getMessage('STORE_LIST_INVENTORY_MANAGEMENT');

		$emptyGridStub =
			"<div class='main-grid-empty-block-title'>"
			. "<span class='store-amount-stub-text'>{$imDisableStubTitle}</span> "
			. "<a onclick='BX.Catalog.ProductStoreGridManager.Instance.openInventoryManagementSlider()' href='#' class='ui-link ui-link-dashed store-amount-stub-text'>{$imDisableStubLinkTitle}</a>"
			. "</div>";

		return [
			'GRID_ID' => $this->getGridId(),
			'HEADERS' => $this->getGridHeaders(),
			'ROWS' => [],
			'STUB' => $emptyGridStub,

			'AJAX_MODE' => 'Y',
			'AJAX_ID' => \CAjax::GetComponentID('bitrix:main.ui.grid', '', ''),
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
		];
	}

	/**
	 * Get data for grid building
	 * @return array
	 */
	protected function getGridData(): array
	{
		$totalCount = $this->getStoreAmount()->getStoresCount();
		$pageNavigation = $this->getPageNavigation();

		return [
			'GRID_ID' => $this->getGridId(),
			'HEADERS' => $this->getGridHeaders(),
			'ROWS' => $this->getGridRows(),
			'STUB' => $totalCount <= 0 ? $this->getStub() : null,

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
			'ALLOW_CONTEXT_MENU' => false,

			'TOTAL_ROWS_COUNT' => $totalCount,

			'NAV_PARAM_NAME' => self::NAV_PARAM_NAME,
			'SHOW_NAVIGATION_PANEL' => true,
			'NAV_OBJECT' => $pageNavigation,
			'SHOW_PAGINATION' => $totalCount > 0,
			'CURRENT_PAGE' => $pageNavigation->getCurrentPage(),
			'SHOW_PAGESIZE' => true,
			'PAGE_SIZES' => [
				['NAME' => '5', 'VALUE' => '5'],
				['NAME' => '10', 'VALUE' => '10'],
				['NAME' => '20', 'VALUE' => '20'],
				['NAME' => '50', 'VALUE' => '50'],
			],
			'DEFAULT_PAGE_SIZE' => self::DEFAULT_PAGE_SIZE,

			'SHOW_CHECK_ALL_CHECKBOXES'=> false,
			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_ROW_ACTIONS_MENU'=> false,
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_SELECTED_COUNTER'=> false,
			'SHOW_TOTAL_COUNTER' => true,
			'SHOW_MORE_BUTTON'=> true,

			'SHOW_ACTION_PANEL' => false,
		];
	}

	protected function getGridOptions(): Bitrix\Main\Grid\Options
	{
		if (!isset($this->gridOptions))
		{
			$this->gridOptions = new Bitrix\Main\Grid\Options($this->getGridId());
		}
		return $this->gridOptions;
	}

	protected function getPageSize(): int
	{
		$navParams = $this->getGridOptions()->getNavParams();

		return (int)($navParams['nPageSize'] ?? self::DEFAULT_PAGE_SIZE);
	}

	protected function getPageNavigation(): PageNavigation
	{
		if (!isset($this->pageNavigation))
		{
			$this->pageNavigation = new PageNavigation(self::NAV_PARAM_NAME);
			$this->pageNavigation
				->allowAllRecords(false)
				->setPageSize($this->getPageSize())
				->initFromUri();

			$this->pageNavigation->setRecordCount($this->getStoreAmount()->getStoresCount());
		}

		return $this->pageNavigation;
	}

	protected function getStub(): array
	{
		return [
			'title' => Loc::getMessage('STORE_LIST_GRID_STUB_TITLE'),
			'description' => '',
		];
	}

	/**
	 * Return unique ID of a product stores grid
	 * @return string
	 */
	protected function getGridId(): string
	{
		return $this->getStoreAmount()->getStoreAmountGridId();
	}

	protected function getTotalWrapperId(): string
	{
		return $this->getStoreAmount()->getStoreAmountGridId() . '_total';
	}

	protected function getGridHeaders(): array
	{
		if (!empty($this->headers))
		{
			return $this->headers;
		}

		$defaultWidth = 200;

		$headers = [
			[
				'id' => 'TITLE',
				'name' => Loc::getMessage('STORE_LIST_GRID_HEADER_TITLE'),
				'title' => Loc::getMessage('STORE_LIST_GRID_HEADER_TITLE'),
				'sort' => 'TITLE',
				'type' => 'string',
				'width' => $defaultWidth * 1.5,
				'default' => true,
			],
			[
				'id' => 'QUANTITY_COMMON',
				'name' => Loc::getMessage('STORE_LIST_GRID_HEADER_QUANTITY_COMMON'),
				'title' => Loc::getMessage('STORE_LIST_GRID_HEADER_QUANTITY_COMMON'),
				'sort' => 'QUANTITY_COMMON',
				'type' => 'number',
				'width' => $defaultWidth,
				'default' => true
			]
		];

		if (Config\State::isShowedStoreReserve())
		{
			array_push($headers,
			[
				'id' => 'QUANTITY_RESERVED',
				'name' => Loc::getMessage('STORE_LIST_GRID_HEADER_QUANTITY_RESERVED'),
				'title' => Loc::getMessage('STORE_LIST_GRID_HEADER_QUANTITY_RESERVED'),
				'sort' => 'QUANTITY_RESERVED',
				'type' => 'number',
				'width' => $defaultWidth,
				'default' => true
			],
			[
				'id' => 'QUANTITY',
				'name' => Loc::getMessage('STORE_LIST_GRID_HEADER_QUANTITY'),
				'title' => Loc::getMessage('STORE_LIST_GRID_HEADER_QUANTITY'),
				'sort' => 'QUANTITY',
				'type' => 'number',
				'width' => $defaultWidth,
				'default' => true
			]);
		}

		$headers[] = [
			'id' => 'AMOUNT',
			'name' => Loc::getMessage('STORE_LIST_GRID_HEADER_AMOUNT'),
			'title' => Loc::getMessage('STORE_LIST_GRID_HEADER_AMOUNT'),
			'sort' => 'AMOUNT',
			'type' => 'money',
			'width' => $defaultWidth,
			'default' => true
		];

		$this->headers = $headers;
		return $this->headers;
	}

	/**
	 * Return array of grid rows
	 * @return array
	 */
	protected function getGridRows(): array
	{
		$rows = [];
		$productStores = $this->getProductStores();

		foreach ($productStores as $productStore)
		{
			$row = $this->prepareRow($productStore);
			$rows[] = [
				'data' => [
					'ID' => $productStore['ID'],
					'TITLE' => $this->getLinkedRowTitle($productStore['ID'], $row['TITLE']),
					'QUANTITY_COMMON' => $row['QUANTITY_COMMON'],
					'QUANTITY_RESERVED' => $row['QUANTITY_RESERVED'],
					'QUANTITY' => $row['QUANTITY'],
					'AMOUNT' => $row['AMOUNT'],
				],
			];
		}

		return $rows;
	}

	private function getLinkedRowTitle(int $productStoreId, string $title): string
	{
		if (!$title)
		{
			$title = Loc::getMessage('STORE_LIST_GRID_STORE_TITLE_WITHOUT_NAME');
		}

		return '<a href="' . $this->getStoreAmountDetailsLink($productStoreId) . '">' . $title . '</a>';
	}

	private function getStoreAmountDetailsLink(int $storeId):? string
	{
		return str_replace(
			['#IBLOCK_ID#', '#PRODUCT_ID#', '#STORE_ID#'],
			[$this->getProductIblockId(), $this->getProductId(), $storeId],
			$this->arParams['PATH_TO']['PRODUCT_STORE_AMOUNT_DETAILS']
		);
	}

	/**
	 * Prepare row to render in page.
	 * @param array $productStore Data of store
	 * @return string[]
	 */
	protected function prepareRow(array $productStore): array
	{
		$reservedQuantity = '<a class="main-grid-cell-content-store-amount-reserved-quantity">';
		$commonQuantity = '';
		$quantity = '';
		$amount = '';

		$title = htmlspecialcharsbx($productStore['TITLE']);

		foreach ($productStore['QUANTITY'] as $storeQuantity)
		{
			$measureSymbol = htmlspecialcharsbx($this->getStoreAmount()->getMeasure($storeQuantity['MEASURE_ID']));

			$commonQuantity .= "{$storeQuantity['QUANTITY_COMMON']} $measureSymbol<br>";
			$reservedQuantity .= "{$storeQuantity['QUANTITY_RESERVED']} $measureSymbol<br>";

			$quantityValue = (float)$storeQuantity['QUANTITY_COMMON'] - (float)$storeQuantity['QUANTITY_RESERVED'];
			$currentQuantity = $quantityValue . ' ' . $measureSymbol;
			if ($quantityValue <= 0)
			{
				$currentQuantity = '<span class="text--danger">' . $currentQuantity . '</span>';
			}
			$quantity .= $currentQuantity . '<br>';
		}

		$reservedQuantity .= '</a>';

		foreach ($productStore['AMOUNT'] as $storeAmount)
		{
			if (isset($storeAmount['CURRENCY']))
			{
				$amount .= CCurrencyLang::CurrencyFormat($storeAmount['AMOUNT'], $storeAmount['CURRENCY'], true).'<br>';
			}
		}

		return [
			'TITLE' => $title,
			'QUANTITY_RESERVED' => $reservedQuantity,
			'QUANTITY_COMMON' => $commonQuantity,
			'QUANTITY' => $quantity,
			'AMOUNT' => $amount,
		];
	}

	/**
	 * Check exists and return array of stores of a product
	 * @return array
	 */
	protected function getProductStores(): array
	{
		if (!isset($this->stores))
		{
			$pageNavigation = $this->getPageNavigation();
			$storesParams = [
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
			];

			$this->stores = $this->getStoreAmount()->getProductStores($storesParams);
		}

		return $this->stores;
	}

	private function getReservedDealsSliderLink()
	{
		$sliderUrl = \CComponentEngine::makeComponentPath('bitrix:catalog.productcard.reserved.deal.list');
		$sliderUrl = getLocalPath('components'.$sliderUrl.'/slider.php');

		return $sliderUrl;
	}

	// Controllerable implementation
	public function configureActions()
	{
		return [];
	}
}
