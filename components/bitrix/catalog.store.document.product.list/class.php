<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\StoreBarcodeTable;
use Bitrix\Catalog\StoreDocumentBarcodeTable;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\Url\InventoryBuilder;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\Json;
use Bitrix\Catalog\ProductTable;

if (!Loader::includeModule('catalog'))
{
	return;
}

final class CatalogStoreDocumentProductListComponent
	extends \Bitrix\Catalog\Component\ProductList
	implements Main\Engine\Contract\Controllerable, Main\Errorable
{
	use Main\ErrorableImplementation;

	private const NEW_ROW_ID_PREFIX = 'n';
	private const PRODUCT_ID_MASK = '#PRODUCT_ID_MASK#';

	private const VIEW_MODE_GRID_ID_POSTFIX = 'V';
	private const EDIT_MODE_GRID_ID_POSTFIX = 'E';

	/** @var Main\Grid\Options $gridConfig */
	protected $gridConfig;
	protected $storage = [];
	protected $defaultSettings = [];
	protected $rows = [];

	/** @var Main\UI\PageNavigation $navigation */
	protected $navigation;

	protected $currency = [
		'ID' => '',
		'TEMPLATE' => '',
		'TEXT' => '',
		'FORMAT' => [],
	];
	protected $stores = [];
	protected $newRowCounter = 0;

	protected $externalDocument = [];
	protected AccessController $accessController;
	/**
	 * @var int[]
	 */
	protected array $accessibleStoresIds;

	/**
	 * Base constructor.
	 *
	 * @param \CBitrixComponent|null $component Component object if exists.
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new Main\ErrorCollection();
		$this->accessController = AccessController::getCurrent();
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @param $params
	 * @return array
	 */
	public function onPrepareComponentParams($params): array
	{
		/**
		 * GRID_ID - string - custom grid id
		 * NAVIGATION_ID - string - custom navigation id (may be create from GRID_ID)
		 * FORM_ID - string - custom form identifier (may be create from GRID_ID), default empty
		 * TAB_ID - string - custom product tab identifier, default empty
		 *
		 * AJAX_ID - string - ajax component identifier
		 * AJAX_MODE - string - is ajax enabled (Y/N), default Y
		 * AJAX_OPTION_JUMP - string - ajax option (Y/N), default N
		 * AJAX_OPTION_HISTORY - string - ajax option (Y/N), default N
		 * AJAX_LOADER - mixed|null - not used in titleflex template, default null
		 *
		 * SHOW_PAGINATION - bool or Y/N - show pagination block, default false
		 * SHOW_TOTAL_COUNTER - bool or Y/N - show count of rows, default false
		 * SHOW_PAGESIZE - bool or Y/N - show page size select, default false
		 * PAGINATION - array - pagination info (pages size, offset, etc), default - empty array
		 *
		 * PRODUCTS - array|null - product list
		 * TOTAL_PRODUCTS_COUNT - int - full product rows quantity
		 *
		 * CUSTOM_SITE_ID - string - entity site identifier, default SITE_ID
		 * CUSTOM_LANGUAGE_ID - string - current lang identifier, default LANGUAGE_ID
		 * SET_ITEMS - bool - set rows (Y/N), default N
		 * ALLOW_EDIT - bool - allow modify data (Y/N), default N
		 * ALLOW_ADD_PRODUCT - bool - add product to entity button (Y/N), default N
		 * ALLOW_CREATE_NEW_PRODUCT - bool - create fake products button (Y/N), default N
		 * if ALLOW_EDIT off - ALLOW_ADD_PRODUCT and ALLOW_CREATE_NEW_PRODUCT already off
		 *
		 * DOCUMENT_ID - string|int - parent entity id
		 *
		 * EXTERNAL_DOCUMENT - array|null - custom external documents
		 */

		$this->prepareEntityIds($params);
		$this->prepareAjaxOptions($params);
		$this->preparePaginationOptions($params);
		$this->prepareProducts($params);
		$this->prepareSettings($params);
		$this->prepareEntitySettings($params);

		return $params;
	}

	/**
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->fillSettings();
		if ($this->isExistErrors())
		{
			$this->showErrors();

			return;
		}

		$this->loadData();

		$this->rows = $this->prepareRowsForAccessRights($this->rows);
		$this->prepareResult();

		$this->includeComponentTemplate();
	}

	/**
	 * @return array
	 */
	protected function listKeysSignedParameters()
	{
		return [
			// prepareEntityIds
			'GRID_ID',
			'NAVIGATION_ID',
			'FORM_ID',
			'TAB_ID',
			'AJAX_ID',
			'CATALOG_ID',
			// prepareAjaxOptions
			'AJAX_MODE',
			'AJAX_OPTION_JUMP',
			'AJAX_OPTION_HISTORY',
			'AJAX_LOADER',
			// preparePaginationOptions
			'SHOW_PAGINATION',
			'SHOW_TOTAL_COUNTER',
			'SHOW_PAGESIZE',
			// prepareSettings
			'CUSTOM_SITE_ID',
			'CUSTOM_LANGUAGE_ID',
			'CURRENCY',
			'SET_ITEMS',
			'ALLOW_EDIT',
			'ALLOW_ADD_PRODUCT',
			'ALLOW_CREATE_NEW_PRODUCT',
			'PREFIX',
			'ID',
			'PRODUCT_DATA_FIELD_NAME',
			// prepareEntitySettings
			'DOCUMENT_TYPE',
			'DOCUMENT_ID',
			'EXTERNAL_DOCUMENT',
		];
	}

	/**
	 * @return bool
	 */
	protected function isExistErrors(): bool
	{
		return !$this->errorCollection->isEmpty();
	}

	/**
	 * @return void
	 */
	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			\ShowError($error);
		}
	}

	/**
	 * @param string $message
	 * @return void
	 */
	protected function addErrorMessage(string $message): void
	{
		$this->errorCollection->setError(new Main\Error($message));
	}

	/**
	 * @param array &$params
	 * @return void
	 */
	protected function prepareEntityIds(array &$params): void
	{
		static::validateListParameters(
			$params,
			[
				'GRID_ID',
				'NAVIGATION_ID',
				'FORM_ID',
				'TAB_ID',
				'AJAX_ID',
			]
		);

		if (!empty($params['GRID_ID']))
		{
			if (empty($params['NAVIGATION_ID']))
			{
				$params['NAVIGATION_ID'] = static::createNavigationId($params['GRID_ID']);
			}
			if (!isset($params['FORM_ID']))
			{
				$params['FORM_ID'] = static::createFormId($params['GRID_ID']);
			}
		}
	}

	private function isAcceptableDocumentType($type): bool
	{
		$acceptableDocumentTypes = [
			StoreDocumentTable::TYPE_ARRIVAL,
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
			StoreDocumentTable::TYPE_DEDUCT,
			StoreDocumentTable::TYPE_MOVING,
		];

		if (!empty($this->externalDocument['TYPE']))
		{
			$acceptableDocumentTypes[] = $this->externalDocument['TYPE'];
		}

		return in_array($type, $acceptableDocumentTypes, true);
	}

	/**
	 * @param array &$params
	 * @return void
	 */
	protected function prepareAjaxOptions(array &$params): void
	{
		$params['AJAX_MODE'] = isset($params['AJAX_MODE']) && $params['AJAX_MODE'] === 'N' ? 'N' : 'Y';
		$params['AJAX_OPTION_JUMP'] = isset($params['AJAX_OPTION_JUMP']) && $params['AJAX_OPTION_JUMP'] === 'Y' ? 'Y' : 'N';
		$params['AJAX_OPTION_HISTORY'] = isset($params['AJAX_OPTION_HISTORY']) && $params['AJAX_OPTION_HISTORY'] === 'Y' ? 'Y' : 'N';
		$params['AJAX_LOADER'] = $params['AJAX_LOADER'] ?? null;
	}

	/**
	 * @param array &$params
	 * @return void
	 */
	protected function preparePaginationOptions(array &$params): void
	{
		static::validateBoolList(
			$params,
			[
				'SHOW_PAGINATION',
				'SHOW_TOTAL_COUNTER',
				'SHOW_PAGESIZE',
			]
		);

		if (empty($params['PAGINATION']) || !is_array($params['PAGINATION']))
		{
			$params['PAGINATION'] = [];
		}
	}

	/**
	 * @param array &$params
	 * @return void
	 */
	protected function prepareProducts(array &$params): void
	{
		if (isset($params['PRODUCTS']) && !is_array($params['PRODUCTS']))
		{
			$params['PRODUCTS'] = null;
		}
	}

	/**
	 * @param array &$params
	 * @return void
	 */
	protected function prepareSettings(array &$params): void
	{
		$params['CURRENCY'] = isset($params['CURRENCY']) && is_string($params['CURRENCY'])
			? $params['CURRENCY']
			: ''
		;

		$params['SET_ITEMS'] = isset($params['SET_ITEMS']) && $params['SET_ITEMS'] === 'Y';
		$params['ALLOW_EDIT'] = isset($params['ALLOW_EDIT']) && $params['ALLOW_EDIT'] === 'Y';
		$params['ALLOW_ADD_PRODUCT'] = isset($params['ALLOW_ADD_PRODUCT']) && $params['ALLOW_ADD_PRODUCT'] === 'Y';
		$params['ALLOW_CREATE_NEW_PRODUCT'] = isset($params['ALLOW_CREATE_NEW_PRODUCT']) && $params['ALLOW_CREATE_NEW_PRODUCT'] === 'Y';

		if (!$params['ALLOW_EDIT'])
		{
			$params['ALLOW_ADD_PRODUCT'] = false;
			$params['ALLOW_CREATE_NEW_PRODUCT'] = false;
		}

		$params['BUILDER_CONTEXT'] =
			isset($params['BUILDER_CONTEXT']) && is_string($params['BUILDER_CONTEXT'])
				? trim($params['BUILDER_CONTEXT'])
				: InventoryBuilder::TYPE_ID
		;
		$params['PREFIX'] = (isset($params['PREFIX']) && is_string($params['PREFIX']) ? trim($params['PREFIX']) : '');
		$params['ID'] = (isset($params['ID']) && is_string($params['ID']) ? trim($params['ID']) : '');
		$params['PRODUCT_DATA_FIELD_NAME'] = isset($params['PRODUCT_DATA_FIELD_NAME']) ? $params['PRODUCT_DATA_FIELD_NAME'] : 'PRODUCT_ROW_DATA';

		$params['EXTERNAL_DOCUMENT'] = $params['EXTERNAL_DOCUMENT'] ?? [];
		$this->externalDocument = $params['EXTERNAL_DOCUMENT'];
	}

	/**
	 * @param array &$params
	 * @return void
	 */
	protected function prepareEntitySettings(array &$params): void
	{
		if (empty($params['DOCUMENT_TYPE']) || !$this->isAcceptableDocumentType($params['DOCUMENT_TYPE']))
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_WRONG_DOCUMENT_TYPE'));
			return;
		}

		$params['DOCUMENT_ID'] = (isset($params['DOCUMENT_ID']) ? (int)$params['DOCUMENT_ID'] : 0);
		if ($params['DOCUMENT_ID'] < 0)
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_WRONG_DOCUMENT_ID'));
		}

		$params['CATALOG_ID'] = (isset($params['CATALOG_ID']) ? (int)$params['CATALOG_ID'] : 0);
		if ($params['CATALOG_ID'] <= 0)
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_WRONG_CATALOG_ID'));
		}
	}

	/**
	 * @return void
	 */
	protected function fillSettings(): void
	{
		$this->checkModules();
		$this->initDefaultSettings();
		$this->loadReferences();
		$this->initSettings();
	}

	/**
	 * @return void
	 */
	protected function initDefaultSettings(): void
	{
		$this->defaultSettings = [
			'GRID_ID' => $this->getDefaultGridId(),
		];
		$this->defaultSettings['NAVIGATION_ID'] = static::createNavigationId($this->defaultSettings['GRID_ID']);
		$this->defaultSettings['FORM_ID'] = static::createFormId($this->defaultSettings['GRID_ID']);
		$this->defaultSettings['TAB_ID'] = '';
		$this->defaultSettings['AJAX_ID'] = '';
		$this->defaultSettings['PAGE_SIZES'] = [5, 10, 20, 50, 100];
		$this->defaultSettings['PRICE_PRECISION'] = 2;
		$this->defaultSettings['AMOUNT_PRECISION'] = 4;
		$this->defaultSettings['COMMON_PRECISION'] = 2;
		$this->defaultSettings['CREATE_PRODUCT_PATH'] = $this->getElementDetailUrl($this->arParams['CATALOG_ID']);
		$this->defaultSettings['NEW_ROW_POSITION'] = CUserOptions::GetOption(
			'catalog.store.document.product.list',
			'new.row.position',
			'top'
		);

		$this->defaultSettings['BASE_PRICE_ID'] = GroupTable::getBasePriceTypeId();
	}

	protected function getDefaultSetting($name)
	{
		return $this->defaultSettings[$name] ?? null;
	}

	/**
	 * @return string
	 */
	public function getDefaultGridId(): string
	{
		$modePostfix = $this->isReadOnly() ? self::VIEW_MODE_GRID_ID_POSTFIX : self::EDIT_MODE_GRID_ID_POSTFIX;
		return self::clearStringValue(self::class) . '_' . $this->getDocumentType() . '_' . $modePostfix;
	}

	protected function getDocumentId(): int
	{
		return (int)$this->arParams['DOCUMENT_ID'];
	}

	protected function getDocumentType(): ?string
	{
		return $this->arParams['DOCUMENT_TYPE'];
	}

	/**
	 * @param string $gridId
	 * @return string
	 */
	protected static function createNavigationId(string $gridId): string
	{
		return $gridId . '_NAVIGATION';
	}

	/**
	 * @param string $gridId
	 * @return string
	 */
	protected static function createFormId(string $gridId): string
	{
		return 'form_' . $gridId;
	}

	/**
	 * @return void
	 */
	protected function initSettings(): void
	{
		$paramsList = [
			'GRID_ID',
			'NAVIGATION_ID',
			'PAGE_SIZES',
			'FORM_ID',
			'TAB_ID',
			'AJAX_ID',
			'NEW_ROW_POSITION',
			'PRICE_PRECISION',
			'AMOUNT_PRECISION',
			'COMMON_PRECISION',
			'CREATE_PRODUCT_PATH',
		];
		foreach ($paramsList as $param)
		{
			$value = !empty($this->arParams[$param]) ? $this->arParams[$param] : $this->defaultSettings[$param];
			$this->setStorageItem($param, $value);
		}

		$this->initGrid();
	}

	/**
	 * @return void
	 */
	protected function loadReferences(): void
	{
		$this->loadCurrency();
		$this->loadMeasures();
		$this->loadStores();
	}

	/**
	 * @return void
	 */
	protected function loadCurrency(): void
	{
		$this->currency['ID'] =
			!empty($this->arParams['CURRENCY'])
				? $this->arParams['CURRENCY']
				: CurrencyManager::getBaseCurrency()
		;

		$format = \CCurrencyLang::GetFormatDescription($this->currency['ID']);
		$this->currency['TEMPLATE'] = $format['FORMAT_STRING'] ?? '';
		$this->currency['TEXT'] =
			isset($this->currency['TEMPLATE'])
				? trim(\CCurrencyLang::applyTemplate('', $this->currency['TEMPLATE']))
				: ''
		;

		$this->currency['FORMAT'] = $format;
	}

	/**
	 * @return array
	 */
	protected function getCurrency(): array
	{
		return $this->currency;
	}

	/**
	 * @return string
	 */
	protected function getCurrencyId(): string
	{
		return $this->currency['ID'];
	}

	/**
	 * @return string
	 */
	protected function getDefaultTotalCalculationField(): string
	{
		if (!empty($this->externalDocument['TOTAL_CALCULATION_FIELD']))
		{
			return (string)$this->externalDocument['TOTAL_CALCULATION_FIELD'];
		}

		return 'PURCHASING_PRICE';
	}

	/**
	 * @return string
	 */
	protected function getCurrencyTemplate(): string
	{
		return $this->currency['TEMPLATE'];
	}

	/**
	 * @return string
	 */
	protected function getCurrencyText(): string
	{
		return $this->currency['TEXT'];
	}

	/**
	 * @return array
	 */
	protected function getCurrencyFormat(): array
	{
		return $this->currency['FORMAT'];
	}

	protected function loadStores(): void
	{
		$this->stores = [];
		$productStoreRaw = StoreTable::getList([
			'select' => ['ID', 'TITLE', 'IS_DEFAULT']
		]);

		while ($store = $productStoreRaw->fetch())
		{
			if ($store['TITLE'] === '')
			{
				$store['TITLE'] = Loc::getMessage('CATALOG_DOCUMENT_EMPTY_STORE_TITLE');
			}

			$this->stores[$store['ID']] = $store;
		}
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->addErrorMessage('Module "catalog" is not installed.');

			return false;
		}

		if (!Loader::includeModule('iblock'))
		{
			$this->addErrorMessage('Module "iblock" is not installed.');

			return false;
		}

		if (!Loader::includeModule('currency'))
		{
			$this->addErrorMessage('Module "currency" is not installed.');

			return false;
		}

		if (!Loader::includeModule('sale'))
		{
			$this->addErrorMessage('Module "sale" is not installed.');

			return false;
		}

		return true;
	}

	/**
	 * @return void
	 */
	protected function initUiScope(): void
	{
		global $APPLICATION;

		Main\UI\Extension::load($this->getUiExtensions());

		foreach ($this->getUiStyles() as $styleList)
		{
			$APPLICATION->SetAdditionalCSS($styleList);
		}

		$scripts = $this->getUiScripts();
		if (!empty($scripts))
		{
			$asset = Main\Page\Asset::getInstance();
			foreach ($scripts as $row)
			{
				$asset->addJs($row);
			}
			unset($row, $asset);
		}
		unset($scripts);
	}

	/**
	 * @return void
	 */
	protected function loadData(): void
	{
		$this->rows = [];

		if (
			isset($this->arParams['REQUEST'], $this->arParams['~PRODUCTS'])
			&& is_array($this->arParams['~PRODUCTS'])
		)
		{
			$this->rows = $this->getProductRowsFromRequest();

			return;
		}

		if (!empty($this->arParams['PRODUCTS']))
		{
			$documentProducts = $this->arParams['PRODUCTS'];
		}
		elseif (!$this->externalDocument && $this->getDocumentId() > 0)
		{
			$documentProducts = $this->getDocumentProducts();
		}
		elseif ($this->arParams['PRESELECTED_PRODUCT_ID'])
		{
			$documentProducts = $this->getPreselectDocumentProducts();
		}
		elseif (!empty($this->arParams['REQUEST']) && !empty($this->externalDocument['INITIAL_PRODUCTS']))
		{
			$documentProducts = $this->externalDocument['INITIAL_PRODUCTS'];
		}

		if (empty($documentProducts))
		{
			return;
		}

		$productIds = array_column($documentProducts, 'ELEMENT_ID');
		$productIds = array_unique($productIds);

		if (empty($productIds))
		{
			return;
		}

		$productInfo = $this->loadCatalog($productIds);

		$restrictedProductTypes = ProductTable::getStoreDocumentRestrictedProductTypes();
		$productIdsWithoutRestrictedTypes = array_keys(array_filter(
			$productInfo,
			static fn($product): bool => !in_array($product['FIELDS']['TYPE'], $restrictedProductTypes, true)
		));

		$productStoreInfo = $this->getProductStoreInfo($productIdsWithoutRestrictedTypes);
		$barcodes = $this->getBarcodes($productIdsWithoutRestrictedTypes);

		foreach ($documentProducts as $id => $document)
		{
			$productId = (int)($document['ELEMENT_ID'] ?? null);

			if (isset($productInfo[$productId]))
			{
				$product = $productInfo[$productId]['FIELDS'];
				if ($productInfo[$productId]['SKU'] instanceof \Bitrix\Catalog\v2\Sku\BaseSku)
				{
					$skuImageField = new ImageInput($productInfo[$productId]['SKU']);
					$product['IMAGE_INFO'] = $skuImageField->getFormattedField();
				}
			}

			$productName = $product['NAME'] ?? '';
			$rowId = (int)($product['ID'] ?? 0);
			if (
				isset($product)
				&& $productName === ''
				&& is_numeric($product['ID'])
				&& $rowId > 0
			)
			{
				$productName = "[{$rowId}]";
			}


			if (!empty($document['BARCODE']))
			{
				$barcode = $document['BARCODE'];
			}
			else
			{
				$barcode = $barcodes[$productId] ?? '';
			}

			$existsStoreTo = isset($document['STORE_TO']) && (int)$document['STORE_TO'] > 0;
			$existsStoreFrom = isset($document['STORE_FROM']) && (int)$document['STORE_FROM'] > 0;

			$availableAmountTo = 0;
			if ($productId && $existsStoreTo)
			{
				$availableAmountTo = $this->getAvailableProductAmountOnStore($productStoreInfo, $productId, $document['STORE_TO']);
			}

			$availableAmountFrom = 0;
			if ($productId && $existsStoreFrom)
			{
				$availableAmountFrom = $this->getAvailableProductAmountOnStore($productStoreInfo, $productId, $document['STORE_FROM']);
			}

			$amount = (float)$document['AMOUNT'];
			$calculatedPrice = (float)($document[$this->getDefaultTotalCalculationField()] ?? 0.0);
			$totalPrice = $amount * $calculatedPrice;

			$additionalData = [
				'ROW_ID' => $this->getRowIdPrefix($document['ID']),
				'BARCODE' => $barcode,
				'DOC_BARCODE' => $barcode,
				'STORE_TO_AVAILABLE_AMOUNT' => $availableAmountTo,
				'STORE_FROM_AVAILABLE_AMOUNT' => $availableAmountFrom,
				'STORE_AMOUNT_MAP' => $productStoreInfo[$productId] ?? null,
				'IBLOCK_ID' => $product['IBLOCK_ID'] ?? $this->arParams['IBLOCK_ID'],
				'BASE_PRICE_ID' => $product['BASE_PRICE_ID'] ?? $this->getStorageItem('BASE_PRICE_ID'),
				'PARENT_PRODUCT_ID' => $product['PARENT_PRODUCT_ID'] ?? null,
				'OFFERS_IBLOCK_ID' => $product['OFFERS_IBLOCK_ID'] ?? null,
				'SKU_ID' => $product['SKU_ID'] ?? null,
				'PRODUCT_ID' => $product['PRODUCT_ID'] ?? null,
				'SKU_TREE' => !empty($product['SKU_TREE']) ? Json::encode($product['SKU_TREE']) : null,
				'DETAIL_URL' => $product['DETAIL_URL'] ?? null,
				'IMAGE_INFO' => $product['IMAGE_INFO'] ?? null,
				'MEASURE_NAME' => $product['MEASURE_NAME'] ?? null,
				'MEASURE_CODE' => $product['MEASURE_CODE'] ?? null,
				'NAME' => $productName,
				'BASE_PRICE' => $document['BASE_PRICE'] ?? null,
				'PURCHASING_PRICE' => $document['PURCHASING_PRICE'] ?? 0,
				'TOTAL_PRICE' => $totalPrice,
				'BASKET_ID' => $document['BASKET_ID'] ?? 0,
				'TYPE' => $product['TYPE'] ?? null,
			];

			if ($existsStoreTo)
			{
				$additionalData['STORE_TO_TITLE'] = $this->stores[$document['STORE_TO']]['TITLE'] ?? '';
				$additionalData['STORE_TO_AMOUNT'] = $productStoreInfo[$productId][$document['STORE_TO']]['AMOUNT'] ?? '';
				$additionalData['STORE_TO_RESERVED'] = $productStoreInfo[$productId][$document['STORE_TO']]['QUANTITY_RESERVED'] ?? '';
			}
			else
			{
				$additionalData['STORE_TO_TITLE'] = '';
				$additionalData['STORE_TO_AMOUNT'] = '';
				$additionalData['STORE_TO_RESERVED'] = '';
			}

			if ($existsStoreFrom)
			{
				$additionalData['STORE_FROM_TITLE'] = $this->stores[$document['STORE_FROM']]['TITLE'] ?? '';
				$additionalData['STORE_FROM_AMOUNT'] = $productStoreInfo[$productId][$document['STORE_FROM']]['AMOUNT'] ?? '';
				$additionalData['STORE_FROM_RESERVED'] = $productStoreInfo[$productId][$document['STORE_FROM']]['QUANTITY_RESERVED'] ?? '';
			}
			else
			{
				$additionalData['STORE_FROM_TITLE'] = '';
				$additionalData['STORE_FROM_AMOUNT'] = '';
				$additionalData['STORE_FROM_RESERVED'] = '';
			}

			$documentProducts[$id] = array_merge($document, $additionalData);
		}

		$this->rows = $documentProducts;
	}

	/**
	 * Updating rows based on access rights.
	 *
	 * @return void
	 */
	private function prepareRowsForAccessRights(array $rows): array
	{
		$accessibleStoresIds = $this->getAccessibleStoresIds();
		$hiddenFields = $this->getHiddenFieldsWithoutAccess();

		$notHasAccessToPurchasingPrice = !AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW);

		foreach ($rows as &$row)
		{
			$hasAccess = true;

			$storeTo = (int)($row['STORE_TO'] ?? 0);
			$storeFrom = (int)($row['STORE_FROM'] ?? 0);

			if ($storeTo && $storeFrom)
			{
				$hasAccess =
					in_array($storeTo, $accessibleStoresIds, true)
					&& in_array($storeFrom, $accessibleStoresIds, true)
				;
			}
			elseif ($storeTo)
			{
				$hasAccess = in_array($storeTo, $accessibleStoresIds, true);
			}
			elseif ($storeFrom)
			{
				$hasAccess = in_array($storeFrom, $accessibleStoresIds, true);
			}

			$realValues = null;
			if (!$hasAccess)
			{
				$realValues = [];
				foreach ($hiddenFields as $fieldName)
				{
					if (isset($row[$fieldName]))
					{
						$realValues[$fieldName] = $row[$fieldName];
						$row[$fieldName] = null;
					}
				}

				$row['REAL_VALUES'] = base64_encode(Json::encode($realValues));
			}

			$row['ACCESS_DENIED'] = !$hasAccess;
			$row['ACCESS_DENIED_TO_PURCHASING_PRICE'] = $notHasAccessToPurchasingPrice;
		}

		return $rows;
	}

	/**
	 * Fields that are hidden when there is no access.
	 *
	 * @return array
	 */
	private function getHiddenFieldsWithoutAccess(): array
	{
		return [
			'STORE_TO',
			'STORE_TO_INFO',
			'STORE_TO_TITLE',
			'STORE_TO_AMOUNT',
			'STORE_TO_RESERVED',
			'STORE_TO_AVAILABLE_AMOUNT',
			'STORE_FROM',
			'STORE_FROM_INFO',
			'STORE_FROM_TITLE',
			'STORE_FROM_AMOUNT',
			'STORE_FROM_RESERVED',
			'STORE_FROM_AVAILABLE_AMOUNT',
			'PURCHASING_PRICE',
			'BASE_PRICE',
			'TOTAL_PRICE',
			'AMOUNT',
		];
	}

	protected function getProductRowsFromRequest(): array
	{
		$rows = $this->arParams['~PRODUCTS'];
		$rows = array_filter($rows);

		if (
			$this->arParams['REQUEST']['action_button_' . $this->getGridId()] === 'delete'
			&& $this->arParams['REQUEST']['action_all_rows_' . $this->getGridId()] === 'Y'
		)
		{
			return [];
		}

		$skuTreeItems = $this->getSkuTreeItems($rows);

		foreach ($rows as $index => $row)
		{
			if (
				$this->arParams['REQUEST']['action_button_' . $this->getGridId()] === 'delete'
				&& is_array($this->arParams['REQUEST']['ID'])
				&& in_array($row['ID'], $this->arParams['REQUEST']['ID'], true)
			)
			{
				unset($rows[$index]);
				continue;
			}

			if (!isset($row['ID']))
			{
				$rows[$index]['ID'] = $this->getNewRowId();
			}

			$intFields = [
				'IBLOCK_ID',
				'DOCUMENT_ID',
				'PARENT_PRODUCT_ID',
				'PRODUCT_ID',
				'OFFERS_IBLOCK_ID',
				'SKU_ID',
				'DISCOUNT_TYPE_ID',
				'SORT',
			];
			foreach ($intFields as $name)
			{
				if (isset($rows[$index][$name]))
				{
					$rows[$index][$name] = (int)$rows[$index][$name];
				}
			}

			$floatFields = [
				'AMOUNT',
				'PURCHASING_PRICE',
				'TOTAL_PRICE',
			];

			if ($rows[$index]['BASE_PRICE'] === '' || $rows[$index]['BASE_PRICE'] === null)
			{
				$rows[$index]['BASE_PRICE'] = null;
			}
			else
			{
				$floatFields[] = 'BASE_PRICE';
			}

			foreach ($floatFields as $name)
			{
				if (isset($rows[$index][$name]))
				{
					$rows[$index][$name] = (float)$rows[$index][$name];
				}
			}

			if ($row["SKU_ID"] > 0)
			{
				$sku = $this->getSkuByProductId($row["SKU_ID"]);
				if ($sku)
				{
					$skuImageField = new ImageInput($sku);
					$rows[$index]['IMAGE_INFO'] = $skuImageField->getFormattedField();

					$skuTree = $skuTreeItems[$row['IBLOCK_ID']][$row['PRODUCT_ID']][$row['SKU_ID']] ?? null;
					$rows[$index]['SKU_TREE'] = $skuTree ? Json::encode($skuTree) : null;
				}
			}
		}

		return $rows;
	}

	private function getSkuTreeItems(array $rows): array
	{
		$iblockProductOfferIds = [];
		foreach ($rows as $row)
		{
			if (empty($row['SKU_ID']))
			{
				continue;
			}

			$iblockProductOfferIds[$row['IBLOCK_ID']][$row['PRODUCT_ID']][] = (int)$row['SKU_ID'];
		}
		$skuTreeItems = [];
		foreach ($iblockProductOfferIds as $iblockId => $productOfferIds)
		{
			$skuTree = \Bitrix\Catalog\v2\IoC\ServiceContainer::make('sku.tree', ['iblockId' => $iblockId]);
			if (!$skuTree)
			{
				continue;
			}

			$skuTreeItems[$iblockId] = $skuTree->loadJsonOffers($productOfferIds);
		}

		return $skuTreeItems;
	}

	protected function getDocumentProducts(): array
	{
		$products = [];

		$documentProductRaw = StoreDocumentElementTable::getList([
			'filter' => [
				'=DOC_ID' => $this->getDocumentId(),
			],
		]);

		while($documentProduct = $documentProductRaw->fetch())
		{
			$documentProduct['BARCODE'] = '';
			$products[$documentProduct['ID']] = $documentProduct;
		}

		$rowBarcodesRaw = StoreDocumentBarcodeTable::getList([
			'select' => ['DOC_ELEMENT_ID', 'BARCODE'],
			'filter' => ['=DOC_ID' => $this->getDocumentId()]
		]);
		while ($barcode = $rowBarcodesRaw->fetch())
		{
			$rowId = $barcode['DOC_ELEMENT_ID'];
			$products[$rowId]['BARCODE'] = $barcode['BARCODE'];
		}

		return $products;
	}

	protected function getProductStoreInfo(array $productIds): array
	{
		$productStoreInfo = [];
		$productStoreRaw = StoreProductTable::getList([
			'filter' => ['=PRODUCT_ID' => $productIds],
			'select' => [
				'STORE_ID',
				'PRODUCT_ID',
				'AMOUNT',
				'QUANTITY_RESERVED',
				'STORE_TITLE' => 'STORE.TITLE'
			]
		]);

		while ($productStore = $productStoreRaw->Fetch())
		{
			$productStoreInfo[$productStore['PRODUCT_ID']] = $productStoreInfo[$productStore['PRODUCT_ID']] ?? [];
			$productStoreInfo[$productStore['PRODUCT_ID']][$productStore['STORE_ID']] = $productStore;
		}

		return $productStoreInfo;
	}

	protected function getBarcodes(array $productIds): array
	{
		$barcodes = [];
		$barcodeRaw = StoreBarcodeTable::getList([
			'filter' => [
				'PRODUCT_ID' => $productIds,
			]
		]);

		while ($barcode = $barcodeRaw->fetch())
		{
			$barcodes[$barcode['PRODUCT_ID']] = $barcode['BARCODE'];
		}

		return $barcodes;
	}

	/**
	 * @return void
	 */
	protected function prepareResult(): void
	{
		$this->initUiScope();

		$this->arResult['ID'] = $this->getGridId();
		$gridRows = $this->getGridRows();
		$this->arResult['GRID'] = $this->getGridParams($gridRows);
		$this->arResult['GRID_EDITOR_CONFIG'] = $this->getGridEditorConfig($gridRows);
		$this->arResult['SETTINGS'] = $this->getSettings();
		$this->arResult['HIDDEN_FIELDS'] = $this->getHiddenFieldsWithoutAccess();
		$this->arResult['TOTAL_SUM'] = 0;
	}

	protected function getGridParams(array $gridRows): array
	{
		return [
			'GRID_ID' => $this->getGridId(),
			'HEADERS' => array_values($this->getColumns()),
			'SORT' => $this->getStorageItem('GRID_ORDER'),
			'SORT_VARS' => $this->getStorageItem('GRID_ORDER_VARS'),
			'ROWS' => $gridRows,

			'SHOW_ROW_ACTIONS_MENU' => true,
			'ALLOW_SORT' => false,
			'ALLOW_ROWS_SORT' => false,
			'ALLOW_ROWS_SORT_IN_EDIT_MODE' => false,
			'ALLOW_ROWS_SORT_INSTANT_SAVE' => false,
			'ENABLE_ROW_COUNT_LOADER' => false,
			'HIDE_FILTER' => true,
			'ENABLE_COLLAPSIBLE_ROWS' => false,
			'ADVANCED_EDIT_MODE' => true,
			'ALLOW_EDIT_SELECTION' => true,
			'NAME_TEMPLATE' => (string)($arParams['~NAME_TEMPLATE'] ?? ''),
			'SHOW_ACTION_PANEL' => true,
			// 'SETTINGS_WINDOW_TITLE' => $arResult['ENTITY']['TITLE'],

			'SHOW_NAVIGATION_PANEL' => false,
			'SHOW_PAGINATION' => false,
			'SHOW_TOTAL_COUNTER' => false,
			'SHOW_PAGESIZE' => false,
			'PAGINATION' => [],
			'NAV_OBJECT' => $this->navigation,
			'~NAV_PARAMS' => ['SHOW_ALWAYS' => false],
			'SHOW_ROW_CHECKBOXES' => true,

			'SHOW_SELECTED_COUNTER' => true,
			'ACTION_PANEL' => $this->getGridActionPanel(),

			// checked
			'VISIBLE_COLUMNS' => array_values($this->getVisibleColumns()),
			'AJAX_ID' => $this->getStorageItem( 'AJAX_ID'),
			'AJAX_MODE' => $this->arParams['~AJAX_MODE'],
			'AJAX_OPTION_JUMP' => $this->arParams['~AJAX_OPTION_JUMP'],
			'AJAX_OPTION_HISTORY' => $this->arParams['~AJAX_OPTION_HISTORY'],
			'AJAX_LOADER' => $this->arParams['~AJAX_LOADER'],
			'FORM_ID' => $this->getStorageItem('FORM_ID'),
			'TAB_ID' => $this->getStorageItem('TAB_ID'),

			'TOTAL_ROWS_COUNT' => $this->arParams['~TOTAL_PRODUCTS_COUNT'] ?? count($gridRows),
		];
	}

	protected function getGridActionPanel(): array
	{
		if ($this->isReadOnly())
		{
			return [];
		}

		$snippet = new Snippet();

		$dropdownStores = [];
		foreach ($this->getAccessibleStores() as $store)
		{
			$dropdownStores[] = ['NAME' => $store['TITLE'], 'VALUE' => (int)$store['ID']];
		}

		$actionList = [];
		if ($dropdownStores)
		{
			$items = [
				[
					'NAME' => Loc::getMessage('CATALOG_DOCUMENT_ACTION_DEFAULT'),
					'VALUE' => 'default',
					'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS,
				],
			];

			$isExternalDocument = (bool)($this->externalDocument['TYPE'] ?? false);
			if (
				$isExternalDocument
				|| $this->getDocumentType() === StoreDocumentTable::TYPE_MOVING
				|| $this->getDocumentType() === StoreDocumentTable::TYPE_DEDUCT
				|| $this->getDocumentType() === StoreDocumentTable::TYPE_SALES_ORDERS
			)
			{
				$storeFromActionTitle =
					$this->getDocumentType() === StoreDocumentTable::TYPE_MOVING
						? Loc::getMessage('CATALOG_DOCUMENT_ACTION_SELECT_STORE_FROM')
						: Loc::getMessage('CATALOG_DOCUMENT_ACTION_SELECT_STORE')
				;

				$items[] = $this->getDropdownActionField(
					$snippet,
					'STORE_FROM_INFO',
					$dropdownStores,
					$storeFromActionTitle
				);
			}

			if (
				!$isExternalDocument
				&& $this->getDocumentType() !== StoreDocumentTable::TYPE_DEDUCT
				&& $this->getDocumentType() !== StoreDocumentTable::TYPE_SALES_ORDERS
			)
			{
				$storeToActionTitle =
					$this->getDocumentType() === StoreDocumentTable::TYPE_MOVING
						? Loc::getMessage('CATALOG_DOCUMENT_ACTION_SELECT_STORE_TO')
						: Loc::getMessage('CATALOG_DOCUMENT_ACTION_SELECT_STORE')
				;

				$items[] = $this->getDropdownActionField(
					$snippet,
					'STORE_TO_INFO',
					$dropdownStores,
					$storeToActionTitle
				);
			}

			$actionList = [
				'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
				'ID' => 'actionListId',
				'NAME' => 'actionList',
				'ITEMS' => $items
			];
		}

		return [
			'GROUPS' => [
				[
					'ITEMS' => [
						$snippet->getRemoveButton(),
						$actionList,
						$snippet->getForAllCheckbox(),
					],
				],
			],
		];
	}

	/**
	 * @param Snippet $snippet
	 * @param string $fieldId
	 * @param array $list
	 * @param string $title
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	private function getDropdownActionField(Snippet $snippet, string $fieldId, array $list, string $title): array
	{
		$action = [
			'ACTION' => Main\Grid\Panel\Actions::CREATE,
			'DATA' => [
				[
					'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
					'ID' => $fieldId,
					'NAME' => $fieldId,
					'ITEMS' => $list,
				],
				$snippet->getApplyButton([
					'ONCHANGE' => [
						[
							'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
							'DATA' => [
								[
									"JS" => "BX.Catalog.Store.ProductList.Instance.processApplyActionButtonClick('{$fieldId}')",
								]
							]
						]
					]
				]),
			]
		];

		return [
			'NAME' => $title,
			'VALUE' => $fieldId,
			'ONCHANGE' => [$action]
		];
	}

	/**
	 * @return array
	 */
	protected function getUiExtensions(): array
	{
		return [
			'core',
			'ajax',
			'tooltip',
			'ui.fonts.ruble',
			'ui.notification',
			'catalog.product-calculator',
			'catalog.product-selector',
			'catalog.store-selector',
			'currency',
		];
	}

	/**
	 * @return array
	 */
	protected function getUiStyles(): array
	{
		return [];
	}

	/**
	 * @return array
	 */
	protected function getUiScripts(): array
	{
		return [];
	}

	/**
	 * @return array
	 */
	protected function getSettings(): array
	{
		return [
			'SITE_ID' => $this->getSiteId(),
			'LANGUAGE_ID' => $this->getLanguageId(),
			'SET_ITEMS' => $this->arParams['SET_ITEMS'],
			'ALLOW_EDIT' => $this->arParams['ALLOW_EDIT'],
			'IS_READ_ONLY' => $this->isReadOnly(),
			'CURRENCY' => $this->getCurrency(),
			'NEW_ROW_ID_PREFIX' => self::NEW_ROW_ID_PREFIX,
			'NEW_ROW_ID_COUNTER' => $this->getNewRowCounter(),
			'NEW_ROW_POSITION' => $this->getStorageItem( 'NEW_ROW_POSITION'),
			'CREATE_PRODUCT_PATH' => $this->getStorageItem( 'CREATE_PRODUCT_PATH'),
			'TOTAL_SUM_CONTAINER_ID' => $this->getPrefix() . '_product_sum_total_container',
		];
	}

	/* Storage tools */

	/**
	 * @param string $node
	 * @param array $nodeValues
	 * @return void
	 */
	protected function fillStorageNode(array $nodeValues): void
	{
		if (empty($nodeValues))
		{
			return;
		}

		$this->storage = array_merge($this->storage, $nodeValues);
	}

	/**
	 * @param string $node
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 */
	protected function setStorageItem(string $item, $value): void
	{
		$this->storage[$item] = $value;
	}

	/**
	 * @param string $node
	 * @param string $item
	 * @return mixed|null
	 */
	protected function getStorageItem(string $item)
	{
		return $this->storage[$item] ?? null;
	}

	/**
	 * @return string
	 */
	protected function getGridId(): ?string
	{
		return $this->getStorageItem('GRID_ID');
	}

	/**
	 * @return string
	 */
	protected function getFormId(): ?string
	{
		return $this->getStorageItem('FORM_ID');
	}

	/**
	 * @return string
	 */
	protected function getNavigationId(): string
	{
		return $this->getStorageItem('NAVIGATION_ID');
	}

	/**
	 * @return array
	 */
	protected function getPageSizes(): array
	{
		return $this->getStorageItem('PAGE_SIZES');
	}

	/* Storage tools finish */

	/**
	 * @return void
	 */
	protected function initGrid(): void
	{
		$this->initGridConfig();
		$this->initGridColumns();
		$this->initGridPageNavigation();
		$this->initGridOrder();
	}

	/**
	 * @return void
	 */
	protected function initGridConfig(): void
	{
		$this->gridConfig = new Main\Grid\Options($this->getGridId());
	}

	/**
	 * @return void
	 */
	protected function initGridColumns(): void
	{
		$visibleColumns = [];
		$visibleColumnsMap = [];

		$defaultList = true;
		$userColumnsOrder = [];
		$userColumns = $this->getUserGridColumnIds();
		if (!empty($userColumns))
		{
			$defaultList = false;
			$userColumnsOrder = array_fill_keys($userColumns, true);
		}

		$defaultColumnsOrder = $this->getDefaultColumns();

		$columnDescriptions = $this->getGridColumnsDescription();
		if ($defaultList)
		{
			$userColumnsOrder = array_filter(
				$defaultColumnsOrder,
				static function($columnName) use ($columnDescriptions)
				{
					return $columnDescriptions[$columnName]['default'] === true;
				}
			);
		}

		foreach ($userColumnsOrder as $index)
		{
			$visibleColumnsMap[$index] = true;
			$visibleColumns[$index] = $columnDescriptions[$index];
		}

		$columns = [];
		foreach ($defaultColumnsOrder as $columnCode)
		{
			if (isset($columnDescriptions[$columnCode]))
			{
				$columns[] = $columnDescriptions[$columnCode];
			}
		}

		$this->fillStorageNode( [
			'COLUMNS' => $columns,
			'VISIBLE_COLUMNS' => $visibleColumns,
			'VISIBLE_COLUMNS_MAP' => $visibleColumnsMap,
		]);
	}

	/**
	 * @return void
	 */
	protected function initGridPageNavigation(): void
	{
		$naviParams = $this->getGridNavigationParams();
		$this->navigation = new Main\UI\PageNavigation($this->getNavigationId());
		$this->navigation->setPageSizes($this->getPageSizes());
		$this->navigation->allowAllRecords(false);
		$this->navigation->setPageSize($naviParams['nPageSize']);

		//		if (!$this->isUsedImplicitPageNavigation())
		//		{
		$this->navigation->initFromUri();
		//		}
	}

	/**
	 * @return array
	 */
	protected function getGridNavigationParams(): array
	{
		return $this->gridConfig->getNavParams(['nPageSize' => 20]);
	}

	/**
	 * @return void
	 */
	protected function initGridOrder(): void
	{
		$result = ['ID' => 'DESC'];

		$sorting = $this->gridConfig->getSorting(['sort' => $result]);

		$order = strtolower(reset($sorting['sort']));
		if ($order !== 'asc')
		{
			$order = 'desc';
		}

		$field = key($sorting['sort']);
		$found = false;

		foreach ($this->getVisibleColumns() as $column)
		{
			if (!isset($column['sort']))
				continue;
			if ($column['sort'] == $field)
			{
				$found = true;
				break;
			}
		}
		unset($column);

		if ($found)
			$result = [$field => $order];

		$this->fillStorageNode(
			[
				'GRID_ORDER' => $this->modifyGridOrder($result),
				'GRID_ORDER_VARS' => $sorting['vars'],
			]
		);

		unset($found, $field, $order, $sorting, $result);
	}

	/**
	 * @param array $order
	 * @return array
	 */
	protected function modifyGridOrder(array $order): array
	{
		return $order;
	}

	protected function getCurrencyListForMoneyField(): array
	{
		return [
			$this->getCurrencyId() => $this->getCurrencyText(),
		];
	}

	protected function getDefaultColumns(): array
	{
		if (!empty($this->externalDocument['DEFAULT_COLUMNS']))
		{
			return $this->externalDocument['DEFAULT_COLUMNS'];
		}

		switch ($this->getDocumentType())
		{
			case StoreDocumentTable::TYPE_STORE_ADJUSTMENT:
			case StoreDocumentTable::TYPE_ARRIVAL:
				if ($this->isReadOnly())
				{
					return [
						'MAIN_INFO','PURCHASING_PRICE', 'BASE_PRICE',
						'AMOUNT', 'STORE_TO_INFO', 'STORE_TO_AMOUNT', 'BARCODE_INFO',
						'TOTAL_PRICE',
					];
				}

				return [
					'MAIN_INFO', 'BARCODE_INFO', 'PURCHASING_PRICE', 'BASE_PRICE',
					'AMOUNT', 'STORE_TO_INFO', 'STORE_TO_AMOUNT',
					'TOTAL_PRICE',
				];
			case StoreDocumentTable::TYPE_DEDUCT:
				if ($this->isReadOnly())
				{
					return [
						'MAIN_INFO',
						'STORE_FROM_INFO', 'STORE_FROM_AMOUNT', 'AMOUNT',
						'PURCHASING_PRICE', 'BASE_PRICE', 'BARCODE_INFO',
						'TOTAL_PRICE',
					];
				}

				return [
					'MAIN_INFO', 'BARCODE_INFO', 'AMOUNT',
					'STORE_FROM_INFO', 'STORE_FROM_AMOUNT',
					'PURCHASING_PRICE', 'BASE_PRICE',
					'TOTAL_PRICE',
				];
			case StoreDocumentTable::TYPE_MOVING:
				if ($this->isReadOnly())
				{
					return [
						'MAIN_INFO',
						'STORE_FROM_INFO', 'STORE_FROM_AVAILABLE_AMOUNT', 'STORE_FROM_AMOUNT',
						'STORE_TO_INFO', 'STORE_TO_AVAILABLE_AMOUNT', 'STORE_TO_AMOUNT', 'AMOUNT',
						'PURCHASING_PRICE', 'BASE_PRICE', 'BARCODE_INFO',
						'TOTAL_PRICE',
					];
				}

				return [
					'MAIN_INFO', 'BARCODE_INFO', 'AMOUNT',
					'STORE_FROM_INFO', 'STORE_FROM_AVAILABLE_AMOUNT', 'STORE_FROM_AMOUNT',
					'STORE_TO_INFO', 'STORE_TO_AVAILABLE_AMOUNT', 'STORE_TO_AMOUNT',
					'PURCHASING_PRICE', 'BASE_PRICE',
					'TOTAL_PRICE',
				];
		}

		return [];
	}

	/**
	 * @return array
	 */
	protected function getGridColumnsDescription(): array
	{
		$result = [];
		$columnDefaultWidth = 150;

		$result['MAIN_INFO'] = [
			'id' => 'MAIN_INFO',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_MAIN_INFO'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_MAIN_INFO'),
			'sort' => 'NAME',
			'default' => true,
		];

		$result['BARCODE_INFO'] = [
			'id' => 'BARCODE_INFO',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_BARCODE'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_BARCODE'),
			'default' => true,
			'width' => 300,
		];

		$priceEditable = [
			'TYPE' => Types::MONEY,
			'CURRENCY_LIST' => $this->getCurrencyListForMoneyField(),
			'PLACEHOLDER' => '0',
			'HTML_ENTITY' => true,
		];

		$purchasingPriceName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_PURCHASING_PRICE');
		$purchasingPriceName = $this->externalDocument['CUSTOM_COLUMN_NAMES']['PURCHASING_PRICE'] ?? $purchasingPriceName;
		$purchasingPriceEditable =
			$this->accessController->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW)
			&& !(
				$this->getDocumentType() === StoreDocumentTable::TYPE_MOVING
				|| $this->getDocumentType() === StoreDocumentTable::TYPE_DEDUCT
			)
		;

		$result['PURCHASING_PRICE'] = [
			'id' => 'PURCHASING_PRICE',
			'name' => $purchasingPriceName,
			'title' => $purchasingPriceName,
			'sort' => 'PURCHASING_PRICE',
			'default' => true,
			'editable' => $purchasingPriceEditable ? $priceEditable : false,
			'width' => $columnDefaultWidth,
		];

		$result['BASE_PRICE'] = [
			'id' => 'BASE_PRICE',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_PRICE'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_PRICE'),
			'sort' => 'BASE_PRICE',
			'default' => true,
			'editable' => $this->isEditableBasePrice() ? $priceEditable : false,
			'width' => $columnDefaultWidth,
		];

		$storeFromName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_FROM_INFO');
		$storeFromAmountName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_FROM_AMOUNT');
		if ($this->getDocumentType() === StoreDocumentTable::TYPE_MOVING)
		{
			$storeFromName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_FROM_INFO_MOVING');
			$storeFromAmountName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_FROM_AMOUNT_MOVING');
		}
		elseif ($this->getDocumentType() === StoreDocumentTable::TYPE_DEDUCT)
		{
			$storeFromName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_INFO');
			$storeFromAmountName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_AMOUNT');
		}

		$storeFromName = $this->externalDocument['CUSTOM_COLUMN_NAMES']['STORE_FROM_INFO'] ?? $storeFromName;
		$result['STORE_FROM_INFO'] = [
			'id' => 'STORE_FROM_INFO',
			'name' => $storeFromName,
			'title' => $storeFromName,
			'sort' => 'STORE_FROM',
			'default' => true,
		];

		$storeFromAmountName = $this->externalDocument['CUSTOM_COLUMN_NAMES']['STORE_FROM_AMOUNT'] ?? $storeFromAmountName;
		$result['STORE_FROM_AMOUNT'] = [
			'id' => 'STORE_FROM_AMOUNT',
			'name' => $storeFromAmountName,
			'title' => $storeFromAmountName,
			'sort' => 'STORE_FROM_AMOUNT',
			'default' => !$this->isReadOnly(),
			'editable' => false,
			'width' => $columnDefaultWidth,
		];

		$result['STORE_FROM_RESERVED'] = [
			'id' => 'STORE_FROM_RESERVED',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_FROM_AMOUNT_RESERVED'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_FROM_AMOUNT_RESERVED'),
			'sort' => 'STORE_FROM_RESERVED',
			'default' => true,
			'editable' => false,
			'width' => $columnDefaultWidth,
		];

		$storeFromCommonAmountName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_FROM_AMOUNT_AVAILABLE');
		$storeFromCommonAmountName = $this->externalDocument['CUSTOM_COLUMN_NAMES']['STORE_FROM_AVAILABLE_AMOUNT'] ?? $storeFromCommonAmountName;

		$result['STORE_FROM_AVAILABLE_AMOUNT'] = [
			'id' => 'STORE_FROM_AVAILABLE_AMOUNT',
			'name' => $storeFromCommonAmountName,
			'title' => $storeFromCommonAmountName,
			'sort' => 'STORE_FROM_AVAILABLE_AMOUNT',
			'default' => !$this->isReadOnly(),
			'editable' => false,
			'width' => $columnDefaultWidth,
		];

		$storeToInfoName = $this->getDocumentType() === StoreDocumentTable::TYPE_MOVING
			? Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_TO_INFO')
			: Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_INFO')
		;

		$result['STORE_TO_INFO'] = [
			'id' => 'STORE_TO_INFO',
			'name' =>$storeToInfoName,
			'title' =>$storeToInfoName,
			'sort' => 'STORE_TO',
			'default' => true,
		];

		$result['STORE_TO_AMOUNT'] = [
			'id' => 'STORE_TO_AMOUNT',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_TO_AMOUNT'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_TO_AMOUNT'),
			'sort' => 'STORE_TO_AMOUNT',
			'default' => !$this->isReadOnly(),
			'editable' => false,
			'width' => $columnDefaultWidth,
		];

		$result['STORE_TO_RESERVED'] = [
			'id' => 'STORE_TO_RESERVED',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_AMOUNT_RESERVED'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_AMOUNT_RESERVED'),
			'sort' => 'STORE_TO_RESERVED',
			'default' => true,
			'editable' => false,
			'width' => $columnDefaultWidth,
		];

		$result['STORE_TO_AVAILABLE_AMOUNT'] = [
			'id' => 'STORE_TO_AVAILABLE_AMOUNT',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_TO_AMOUNT_AVAILABLE'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_STORE_TO_AMOUNT_AVAILABLE'),
			'sort' => 'STORE_TO_AVAILABLE_AMOUNT',
			'default' => !$this->isReadOnly(),
			'editable' => false,
			'width' => $columnDefaultWidth,
		];

		$amountColumnName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_AMOUNT');
		if (
			$this->getDocumentType() === StoreDocumentTable::TYPE_ARRIVAL
			|| $this->getDocumentType() === StoreDocumentTable::TYPE_STORE_ADJUSTMENT
		)
		{
			$amountColumnName = Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_AMOUNT_ARRIVAL');
		}

		$result['AMOUNT'] = [
			'id' => 'AMOUNT',
			'name' => $amountColumnName,
			'title' => $amountColumnName,
			'sort' => 'AMOUNT',
			'default' => true,
			'editable' => [
				'TYPE' => Types::MONEY,
				'CURRENCY_LIST' => $this->getMeasureListForMoneyField(),
				'PLACEHOLDER' => '0',
			],
			'width' => $columnDefaultWidth,
		];

		$result['TOTAL_PRICE'] = [
			'id' => 'TOTAL_PRICE',
			'name' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_TOTAL_PRICE'),
			'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COLUMN_TOTAL_PRICE'),
			'sort' => null,
			'default' => true,
			'editable' => false,
			'width' => $columnDefaultWidth,
		];

		foreach ($result as &$item)
		{
			if (empty($item['editable']))
			{
				$item['editable'] = [
					'TYPE' => Types::CUSTOM,
				];
			}
		}

		unset($item);

		return $result;
	}

	protected function getMeasureListForMoneyField(): array
	{
		return array_column($this->measures, 'SYMBOL', 'CODE');
	}

	protected function getUserGridColumnIds(): array
	{
		$result = $this->gridConfig->GetVisibleColumns();

		if (!empty($result) && !in_array('ID', $result, true))
		{
			array_unshift($result, 'ID');
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getColumns()
	{
		return $this->getStorageItem('COLUMNS');
	}

	/**
	 * @return array
	 */
	protected function getVisibleColumns()
	{
		return $this->getStorageItem('VISIBLE_COLUMNS');
	}

	protected function getGridEditorConfig(array $gridRows): array
	{
		$componentId = $this->randString();
		$defaultRow = $this->getDefaultRow();
		$editData = [
			'template_0' => $this->prepareEditorRow($defaultRow),
		];

		foreach ($gridRows as $row)
		{
			if ($row['editable'] === false)
			{
				continue;
			}

			$editData[$row['id']] = $row['data'];
		}

		return [
			'componentName' => $this->getName(),
			'documentType' => $this->getDocumentType(),
			'signedParameters' => $this->getSignedParameters(),
			'reloadUrl' => $this->getPath() . '/list.ajax.php',

			'containerId' => $this->getPrefix() . '_catalog_document_product_list_container',
			'totalBlockContainerId' => $this->getPrefix() . '_product_sum_total_container',
			'gridId' => $this->getGridId(),
			'formId' => $this->getFormId(),

			'allowEdit' => !$this->isReadOnly(),
			'dataFieldName' => $this->arParams['PRODUCT_DATA_FIELD_NAME'],

			'rowIdPrefix' => $this->getRowIdPrefix(),

			'pricePrecision' => $this->getStorageItem('PRICE_PRECISION'),
			'quantityPrecision' => $this->getStorageItem('AMOUNT_PRECISION'),
			'commonPrecision' => $this->getStorageItem('COMMON_PRECISION'),

			'newRowPosition' => $this->getStorageItem('NEW_ROW_POSITION'),
			'createProductPath' => $this->getStorageItem('CREATE_PRODUCT_PATH'),

			'measures' => array_values($this->measures),
			'stores' => $this->getAccessibleStores(),
			'defaultMeasure' => $this->getDefaultMeasure(),

			'currencyId' => $this->getCurrencyId(),
			'totalCalculationSumField' => $this->getDefaultTotalCalculationField(),

			'popupSettings' => $this->getPopupSettings(),
			'languageId' => $this->getLanguageId(),
			'siteId' => $this->getSiteId(),
			'catalogId' => $componentId,
			'componentId' => $this->randString(),
			'jsEventsManagerId' => "PageEventsManager_{$componentId}",

			'readOnly' => $this->isReadOnly(),
			'items' => $this->getEditorItems(),
			'rowSettings' => $this->getEditorRowSettings(),
			'templateItemFields' => $defaultRow,
			'templateIdMask' => self::PRODUCT_ID_MASK,
			'paintedColumns' => ['AMOUNT'],
			'templateGridEditData' => $editData,
			'enabledCreateProductButton' => $this->isAllowedProductCreation(),

			'productUrlBuilderContext' => htmlspecialcharsbx($this->arParams['BUILDER_CONTEXT']),

			'restrictedProductTypes' => $this->getRestrictedProductTypesForSelector(),
		];
	}

	private function getEditorItems(): array
	{
		$items = [];
		foreach ($this->rows as $row)
		{
			$items[] = [
				'rowId' => $row['ROW_ID'],
				'fields' => $row,
			];
		}

		return $items;
	}

	private function getEditorRowSettings(): array
	{
		$columns = $this->getDefaultColumns();
		$storeHeaders = [];
		foreach ($columns as $column)
		{
			if ($column === 'STORE_TO_INFO')
			{
				$storeHeaders[$column] = 'STORE_TO';
			}
			elseif ($column === 'STORE_FROM_INFO')
			{
				$storeHeaders[$column] = 'STORE_FROM';
			}
		}

		return [
			'storeHeaderMap' => $storeHeaders,
			'isAllowedCreationProduct' => true,
			'documentType' => $this->getDocumentType(),
		];
	}

	/**
	 * @return array
	 */
	protected function getGridRows(): array
	{
		global $APPLICATION;
		$rows = [];
		foreach ($this->rows as $row)
		{
			$item = $this->prepareEditorRow($row);
			$editable = !($row['ACCESS_DENIED'] ?? false);

			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:catalog.grid.product.field',
				'',
				[
					'BUILDER_CONTEXT' => $this->arParams['BUILDER_CONTEXT'],
					'GRID_ID' => $this->getGridId(),
					'ROW_ID' => $row['ID'],
					'GUID' => 'catalog_document_grid_'.$row['ID'],
					'PRODUCT_FIELDS' => [
						'ID' => $row['PRODUCT_ID'],
						'NAME' => $row['NAME'],
						'IBLOCK_ID' => $row['IBLOCK_ID'] ?? null,
						'SKU_IBLOCK_ID' => $row['OFFERS_IBLOCK_ID'] ?? null,
						'SKU_ID' => $row['SKU_ID'] ?? null,
						'BASE_PRICE_ID' => $row['BASE_PRICE_ID'] ?? null,
					],
					'SKU_TREE' => $row['SKU_TREE'] ? Json::decode($row['SKU_TREE']) : '',
					'MODE' => 'view',
					'VIEW_FORMAT' => 'short',
					'ENABLE_SEARCH' => false,
					'ENABLE_IMAGE_CHANGE_SAVING' => false,
					'ENABLE_INPUT_DETAIL_LINK' => true,
					'ENABLE_EMPTY_PRODUCT_ERROR' => false,
					'ENABLE_SKU_SELECTION' => false,
					'HIDE_UNSELECTED_ITEMS' => true,
					'IS_NEW' => $row['IS_NEW'] ?? 'N',
				]
			);

			$mainInfo = '<div class="main-grid-row-number"></div>' . ob_get_clean();

			$rows[] = [
				'id' => ($row['ID'] === self::PRODUCT_ID_MASK) ? 'template_0' : $row['ID'],
				'raw_data' => $row,
				'data' => $item,
				'columns' => [
					'MAIN_INFO' => $mainInfo,
					'STORE_FROM_INFO' => HtmlFilter::encode($item['STORE_FROM_TITLE']),
					'STORE_TO_INFO' => HtmlFilter::encode($item['STORE_TO_TITLE']),
					'BARCODE_INFO' => HtmlFilter::encode($item['BARCODE']),
					'BASE_PRICE' =>
						$item['BASE_PRICE'] !== null
							? \CCurrencyLang::formatValue($item['BASE_PRICE_FORMATTED'], $this->currency['FORMAT'])
							: null
					,
					'PURCHASING_PRICE' => \CCurrencyLang::formatValue($item['PURCHASING_PRICE_FORMATTED'], $this->currency['FORMAT']),
					'TOTAL_PRICE' => \CCurrencyLang::formatValue($item['TOTAL_PRICE_FORMATTED'], $this->currency['FORMAT']),
					'AMOUNT' => (float)$row['AMOUNT'].' '.htmlspecialcharsbx($row['MEASURE_NAME']),
					'STORE_FROM_AMOUNT' => $this->formatRowStoreAmount($row, 'STORE_FROM_AMOUNT'),
					'STORE_TO_AMOUNT' => $this->formatRowStoreAmount($row, 'STORE_TO_AMOUNT'),
					'STORE_FROM_RESERVED' => $this->formatRowStoreAmount($row, 'STORE_FROM_RESERVED'),
					'STORE_TO_RESERVED' => $this->formatRowStoreAmount($row, 'STORE_TO_RESERVED'),
					'STORE_FROM_AVAILABLE_AMOUNT' => $this->formatRowStoreAmount($row, 'STORE_FROM_AVAILABLE_AMOUNT'),
					'STORE_TO_AVAILABLE_AMOUNT' => $this->formatRowStoreAmount($row, 'STORE_TO_AVAILABLE_AMOUNT'),
				],
				'editable' => !$this->isReadOnly() && $editable,
			];
		}

		return $rows;
	}

	private function formatPrices($price)
	{
		return number_format(
			$price,
			$this->getStorageItem('PRICE_PRECISION'),
			'.',
			''
		);
	}

	private function formatRowStoreAmount(array $row, string $amountFieldName): ?string
	{
		$restrictedProductTypes = $this->getRestrictedProductTypes();

		if (
			!isset($row[$amountFieldName])
			|| !$row['PRODUCT_ID']
			|| in_array((int)$row['TYPE'], $restrictedProductTypes, true))
		{
			return null;
		}

		$formattedValue = (float)$row[$amountFieldName] . ' ' . htmlspecialcharsbx($row['MEASURE_NAME']);

		$isNegativeOrZeroStoreFromAvailableAmount =
			$amountFieldName === 'STORE_FROM_AVAILABLE_AMOUNT' && $row['STORE_FROM_AVAILABLE_AMOUNT'] <= 0
		;
		$isNegativeOrZeroStoreToAvailableAmount =
			$amountFieldName === 'STORE_TO_AVAILABLE_AMOUNT' && $row['STORE_TO_AVAILABLE_AMOUNT'] <= 0
		;
		if ($isNegativeOrZeroStoreFromAvailableAmount || $isNegativeOrZeroStoreToAvailableAmount)
		{
			$formattedValue = '<span class="text--danger">' . $formattedValue . '</span>';
		}

		return $formattedValue;
	}

	private function prepareEditorRow(array $row): array
	{
		$rowId = $row['ROW_ID'];

		$priceFormatted = null;
		if ($row['BASE_PRICE'] !== null)
		{
			$priceFormatted = $this->formatPrices($row['BASE_PRICE']);
		}

		$purchasingPriceFormatted = null;
		if ($row['PURCHASING_PRICE'] !== null)
		{
			$purchasingPriceFormatted = $this->formatPrices($row['PURCHASING_PRICE']);
		}

		$row['TOTAL_PRICE'] ??= 0;
		$totalPriceFormatted = $this->formatPrices($row['TOTAL_PRICE']);

		$editorFields = [
			'AMOUNT' => [
				'PRICE' => [
					'NAME' => $rowId .'_AMOUNT',
					'VALUE' => $row['AMOUNT'],
				],
				'CURRENCY' => [
					'NAME' => $rowId .'_MEASURE_CODE',
					'VALUE' => $row['MEASURE_CODE'],
					'DISABLED' => !$this->isCanChangeProductMeasure(),
				],
			],
			'STORE_AMOUNT_MAP' => $row['STORE_AMOUNT_MAP'] ?? null,
			'SKU_TREE' => $row['SKU_TREE'] ?? null,
			'BASE_PRICE_EXTRA' => $row['BASE_PRICE_EXTRA'] ?? null,
			'BASE_PRICE_EXTRA_RATE' => $row['BASE_PRICE_EXTRA_RATE'] ?? null,
			'BASE_PRICE_FORMATTED' => $priceFormatted,
			'TOTAL_PRICE_FORMATTED' => $totalPriceFormatted,
			'PURCHASING_PRICE_FORMATTED' => $purchasingPriceFormatted,
		];
		foreach($this->getColumns() as $column)
		{
			$columnId = $column['id'];
			switch ($columnId)
			{
				case 'BASE_PRICE':
				case 'TOTAL_PRICE':
				case 'PURCHASING_PRICE':
					if ($column['editable']['TYPE'] === Types::MONEY)
					{
						$editorFields[$columnId] = [
							'PRICE' => [
								'NAME' => $rowId . '_' . $columnId,
								'VALUE' =>
									$columnId === 'BASE_PRICE'
										? $priceFormatted
										: $purchasingPriceFormatted
								,
							],
							'CURRENCY' => [
								'NAME' => $rowId . '_' . $columnId . '_CURRENCY',
								'VALUE' => $this->getCurrencyId(),
							],
						];
					}
					elseif ($row[$columnId] !== null)
					{
						$editorFields[$columnId] = \CCurrencyLang::CurrencyFormat($row[$columnId], $this->getCurrencyId());
					}
					break;

				case 'STORE_TO_INFO':
					$editorFields['STORE_TO'] = $row['STORE_TO'];
					$editorFields['STORE_TO_AMOUNT'] = $this->formatRowStoreAmount($row, 'STORE_TO_AMOUNT');
					$editorFields['STORE_TO_RESERVED'] = $this->formatRowStoreAmount($row, 'STORE_TO_RESERVED');
					$editorFields['STORE_TO_AVAILABLE_AMOUNT'] = $this->formatRowStoreAmount($row, 'STORE_TO_AVAILABLE_AMOUNT');
					break;

				case 'STORE_FROM_INFO':
					$editorFields['STORE_FROM'] = $row['STORE_FROM'];
					$editorFields['STORE_FROM_AMOUNT'] = $this->formatRowStoreAmount($row, 'STORE_FROM_AMOUNT');
					$editorFields['STORE_FROM_RESERVED'] = $this->formatRowStoreAmount($row, 'STORE_FROM_RESERVED');
					$editorFields['STORE_FROM_AVAILABLE_AMOUNT'] = $this->formatRowStoreAmount($row, 'STORE_FROM_AVAILABLE_AMOUNT');
					break;
			}
		}

		return array_merge($row, $editorFields);
	}

	private function getDefaultRow(): array
	{
		$defaultStore = $this->getDefaultStore();
		$defaultStoreId = $defaultStore['ID'] ?? null;
		$defaultStoreTitle = $defaultStore['TITLE'] ?? null;

		$defaultMeasure = $this->getDefaultMeasure();
		$row = [
			'ROW_ID' => $this->getRowIdPrefix(self::PRODUCT_ID_MASK),
			'ID' => self::PRODUCT_ID_MASK,
			'IBLOCK_ID' => $this->arParams['CATALOG_ID'],
			'OFFERS_IBLOCK_ID' => 0,
			'SKU_ID' => null,
			'BASE_PRICE_ID' => $this->getDefaultSetting('BASE_PRICE_ID'),
			'PRODUCT_ID' => null,
			'NAME' => '',
			'BARCODE' => '',
			'DOC_BARCODE' => '',
			'BASE_PRICE' => null,
			'TOTAL_PRICE' => null,
			'PURCHASING_PRICE' => null,
			'CURRENCY' => $this->getCurrency(),
			'MEASURE_NAME' => $defaultMeasure['SYMBOL'] ?? '',
			'MEASURE_CODE' => $defaultMeasure['CODE'] ?? '',
			'AMOUNT' => 0,
			'STORE_TO' => $defaultStoreId ?? null,
			'STORE_TO_TITLE' => $defaultStoreTitle ?? null,
			'STORE_TO_AMOUNT' => 0,
			'STORE_TO_RESERVED' => 0,
			'STORE_TO_AVAILABLE_AMOUNT' => 0,
			'STORE_FROM' => $defaultStoreId ?? null,
			'STORE_FROM_TITLE' => $defaultStoreTitle ?? null,
			'STORE_FROM_AMOUNT' => 0,
			'STORE_AMOUNT_MAP' => null,
			'STORE_FROM_RESERVED' => 0,
			'STORE_FROM_AVAILABLE_AMOUNT' => 0,
			'IS_NEW' => 'N',
			'BASE_PRICE_EXTRA' => '',
			'BASE_PRICE_EXTRA_RATE' => StoreDocumentElementTable::EXTRA_RATE_PERCENTAGE,
			'TYPE' => 0,
		];

		$row = $this->prepareRowsForAccessRights([ $row ])[0];

		return $row;
	}

	protected function isReadOnly(): bool
	{
		return !$this->arParams['ALLOW_EDIT'];
	}

	/**
	 * @return string
	 */
	protected function getNewRowId(): string
	{
		$result = self::NEW_ROW_ID_PREFIX . $this->getNewRowCounter();
		$this->newRowCounter++;

		return $result;
	}

	/**
	 * @return int
	 */
	protected function getNewRowCounter(): int
	{
		return $this->newRowCounter;
	}

	/* Access rights tools */

	protected function getPrefix(): string
	{
		return $this->arParams['PREFIX'] !== '' ? $this->arParams['PREFIX'] : $this->getDefaultPrefix();
	}

	protected function getRowIdPrefix(string $code = null): string
	{
		return $this->getPrefix() . '_product_row_' . $code;
	}

	/**
	 * @return string
	 */
	protected function getDefaultPrefix(): string
	{
		$suffix =
			$this->getDocumentId() > 0
				? strtolower($this->getDocumentType()) . '_' . $this->getDocumentId()
				: 'new_' . strtolower($this->getDocumentType())
		;

		return "document_{$suffix}_product_editor";
	}

	/**
	 * @param string $value
	 * @return string
	 */
	private static function clearStringValue(string $value): string
	{
		return preg_replace('/[^a-zA-Z0-9_:\\[\\]]/', '', $value);
	}

	/**
	 * @param array &$params
	 * @param string $field
	 * @return void
	 */
	private static function validateSingleParameter(array &$params, string $field): void
	{
		$value = '';

		if (isset($params[$field]) && is_string($params[$field]))
		{
			$value = static::clearStringValue($params[$field]);
		}

		$params[$field] = $value;
	}

	/**
	 * @param array &$params
	 * @param array $list
	 * @return void
	 */
	private static function validateListParameters(array &$params, array $list): void
	{
		foreach ($list as $field)
		{
			static::validateSingleParameter($params, $field);
		}
	}

	/**
	 * @param array $params
	 * @param string $field
	 * @return void
	 */
	private static function validateBoolParameter(array &$params, string $field): void
	{
		if (!isset($params[$field]))
		{
			$params[$field] = false;
		}
		if (is_string($params[$field]))
		{
			$params[$field] = ($params[$field] === 'Y');
		}
		$params[$field] = (is_bool($params[$field]) && $params[$field]);
	}

	/**
	 * @param array $params
	 * @param array $list
	 * @return void
	 */
	private static function validateBoolList(array &$params, array $list): void
	{
		foreach ($list as $field)
		{
			static::validateBoolParameter($params, $field);
		}
		unset($field);
	}

	private function getPreselectDocumentProducts(): array
	{
		$preselectedSku = $this->getSkuByProductId((int)$this->arParams['PRESELECTED_PRODUCT_ID']);
		if ($preselectedSku)
		{
			$basePriceEntity = $preselectedSku->getPriceCollection()->findBasePrice();
			$defaultStore = $this->getDefaultStore();
			$defaultStoreId = $defaultStore['ID'] ?? null;

			$convertedPurchasingPrice = \CCurrencyRates::ConvertCurrency(
				(float)$preselectedSku->getField('PURCHASING_PRICE'),
				(string)$preselectedSku->getField('PURCHASING_CURRENCY'),
				$this->getCurrencyId()
			);

			$basePrice = $basePriceEntity ? $basePriceEntity->getPrice() : null;
			$basePriceCurrency = $basePriceEntity ? $basePriceEntity->getCurrency() : null;
			$convertedBasePrice = \CCurrencyRates::ConvertCurrency(
				(float)$basePrice,
				(string)$basePriceCurrency,
				$this->getCurrencyId()
			);

			return [
				[
					'ID' => Main\Security\Random::getString(8, false),
					'DOC_ID' => null,
					'STORE_FROM' => $defaultStoreId,
					'STORE_TO' => $defaultStoreId,
					'ELEMENT_ID' => $preselectedSku->getId(),
					'AMOUNT' => null,
					'PURCHASING_PRICE' => $convertedPurchasingPrice,
					'BASE_PRICE' => $convertedBasePrice,
					'BASE_PRICE_EXTRA' => null,
					'BASE_PRICE_EXTRA_RATE' => StoreDocumentElementTable::EXTRA_RATE_PERCENTAGE,
				],
			];
		}

		return [];
	}

	private function getSkuByProductId(int $productId): ?BaseSku
	{
		$repositoryFacade = ServiceContainer::getRepositoryFacade();

		return $repositoryFacade->loadVariation($productId);
	}

	private function getStores(): array
	{
		if (empty($this->stores))
		{
			$this->loadStores();
		}

		return $this->stores;
	}

	private function getAccessibleStores(): array
	{
		return array_intersect_key($this->getStores(), array_flip($this->getAccessibleStoresIds()));
	}

	/**
	 * Warehouses to which the user has access.
	 *
	 * @return array
	 */
	private function getAccessibleStoresIds(): array
	{
		if (isset($this->accessibleStoresIds))
		{
			return $this->accessibleStoresIds;
		}

		$storeIds = (array)$this->accessController->getPermissionValue(ActionDictionary::ACTION_STORE_VIEW);
		if (in_array(PermissionDictionary::VALUE_VARIATION_ALL, $storeIds, true))
		{
			$storeIds = array_column($this->getStores(), 'ID');
		}

		$this->accessibleStoresIds = array_map('intval', $storeIds);

		return $this->accessibleStoresIds;
	}

	private function getDefaultStore(): ?array
	{
		static $defaultStore;

		if (isset($defaultStore))
		{
			return $defaultStore;
		}

		$accessibleStoresIds = $this->getAccessibleStoresIds();
		if (empty($accessibleStoresIds))
		{
			return null;
		}

		$accessibleStores = array_filter(
			$this->getStores(),
			static function($store) use($accessibleStoresIds)
			{
				return in_array((int)$store['ID'], $accessibleStoresIds, true);
			}
		);

		$filteredStores = array_filter(
			$accessibleStores,
			static function($store)
			{
				return $store['IS_DEFAULT'] === 'Y';
			}
		);

		$defaultStore = reset($filteredStores) ?: reset($accessibleStores);

		return $defaultStore;
	}

	/**
	 * Returns available amount on store (amount - quantity in reserve)
	 *
	 * @param array $productStoreInfo
	 * @param int $productId
	 * @param int $storeId
	 * @return float
	 */
	private function getAvailableProductAmountOnStore(array $productStoreInfo, int $productId, int $storeId): float
	{
		$amount = 0.0;

		if (
			isset(
				$productStoreInfo[$productId][$storeId]['AMOUNT'],
				$productStoreInfo[$productId][$storeId]['QUANTITY_RESERVED']
			)
		)
		{
			$amount =
				$productStoreInfo[$productId][$storeId]['AMOUNT']
				- $productStoreInfo[$productId][$storeId]['QUANTITY_RESERVED']
			;
		}

		return $amount;
	}

	public function getPopupSettings(): array
	{
		return [
			[
				'id' => 'ADD_NEW_ROW_TOP',
				'checked' => ($this->defaultSettings['NEW_ROW_POSITION'] !== 'bottom'),
				'title' => Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_SETTING_NEW_ROW_POSITION_TITLE'),
				'desc' => '',
				'action' => 'grid',
			]
		];
	}

	public function setGridSettingAction(string $settingId, $selected): Bitrix\Main\Engine\Response\AjaxJson
	{
		if (!$this->checkModules())
		{
			return Bitrix\Main\Engine\Response\AjaxJson::createError($this->errorCollection);
		}

		if ($settingId === 'ADD_NEW_ROW_TOP')
		{
			$direction = ($selected === 'true') ? 'top' : 'bottom';
			\CUserOptions::SetOption('catalog.store.document.product.list', 'new.row.position', $direction);
		}

		return Bitrix\Main\Engine\Response\AjaxJson::createSuccess();
	}

	/** @noinspection PhpUnused
	 *
	 * @param array $products
	 * @param string currencyId
	 * @return null|array
	 */
	public function calculateProductPricesAction(array $products, string $currencyId, string $oldCurrencyId): ?array
	{
		$this->fillSettings();
		if ($this->isExistErrors())
		{
			return null;
		}

		$response = [];

		foreach ($products as $product)
		{
			$fields = $product['fields'] ?? [];

			\CCurrencyRates::ConvertCurrency(
				(float)$fields['BASE_PRICE'],
				$oldCurrencyId,
				$currencyId
			);

			$basePrice = null;
			if ($fields['BASE_PRICE'] !== null)
			{
				$basePrice = $this->formatPrices(
					\CCurrencyRates::ConvertCurrency(
						(float)$fields['BASE_PRICE'],
						$oldCurrencyId,
						$currencyId
					)
				);
			}

			$response[$product['id']] = [
				'BASE_PRICE' => $basePrice,
				'PURCHASING_PRICE' => $this->formatPrices(
					\CCurrencyRates::ConvertCurrency(
						(float)$fields['PURCHASING_PRICE'],
						$oldCurrencyId,
						$currencyId
					)
				),
			];
		}

		return $response;
	}

	private function getRestrictedProductTypesForSelector(): array
	{
		$restrictedProductTypes = $this->getRestrictedProductTypes();

		if (!empty($this->externalDocument['RESTRICTED_PRODUCT_TYPES']))
		{
			$restrictedProductTypes = $this->externalDocument['RESTRICTED_PRODUCT_TYPES'];
		}

		return $restrictedProductTypes;
	}

	private function getRestrictedProductTypes(): array
	{
		return ProductTable::getStoreDocumentRestrictedProductTypes();
	}

	private function isEditableBasePrice(): bool
	{
		if (!$this->accessController->check(ActionDictionary::ACTION_PRICE_EDIT))
		{
			return false;
		}

		return ! in_array($this->getDocumentType(), [
			StoreDocumentTable::TYPE_MOVING,
			StoreDocumentTable::TYPE_DEDUCT,
		], true);
	}

	private function isCanChangeProductMeasure(): bool
	{
		return $this->accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT);
	}

	public function isAllowedProductCreation(): bool
	{
		return $this->accessController->check(ActionDictionary::ACTION_PRODUCT_ADD);
	}
}
