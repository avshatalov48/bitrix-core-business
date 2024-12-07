<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\Feature;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Grid\Column\PriceProvider;
use Bitrix\Catalog\Grid\Column\ProductProvider;
use Bitrix\Catalog\Grid\ProductGrid;
use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\Url\AdminPage\CatalogBuilder;
use Bitrix\Catalog\Url\ShopBuilder;
use Bitrix\Crm\Order\Import\Instagram;
use Bitrix\Iblock;
use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Grid\Export\ExcelExporter;
use Bitrix\Main\Grid\Settings;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\UI;
use Bitrix\UI\Buttons\SettingsButton;
use Bitrix\UI\Buttons\Split\CreateButton;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductGridComponent extends \CBitrixComponent
	implements Main\Engine\Contract\Controllerable, Main\Errorable
{
	use Main\ErrorableImplementation;

	protected const ERROR_CODE_MODULE_IS_ABSENT = -1;
	protected const ERROR_CODE_BAD_PARAMETER = -2;
	protected const ERROR_CODE_ACCESS = -3;
	protected const ACCESS_LEVEL_IBLOCK = 'iblock_admin_display';
	protected const DOM_ID_MASK = '/[^a-zA-Z0-9_]/';

	protected Catalog\Access\AccessController $accessController;

	protected ?int $iblockId = null;
	protected array $iblock;
	protected string $iblockListMode;

	protected array $catalog;
	protected string $catalogType;
	protected bool $useOffers;

	protected bool $useGridFilter;

	protected Main\UI\PageNavigation $navigation;
	protected array $rows;
	protected array $sectionIds;
	protected array $elementIds;
	protected array $parentIds;
	protected ?Catalog\Component\SkuTree $skuTree;

	protected string $gridId;
	protected ProductGrid $grid;
	protected ShopBuilder $urlBuilder;
	protected ExcelExporter $excelExporter;
	protected array $selectedVariationMap;

	private string $productNavString;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new Main\ErrorCollection();
		$this->accessController = Catalog\Access\AccessController::getCurrent();
	}

	public function configureActions(): array
	{
		return [];
	}

	/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
	/**
	 * @param array|mixed $params
	 *	keys are case sensitive:
	 *		<ul>
	 *		<li>int IBLOCK_ID Information block identifier (required).
	 *		<li>string LIST_MODE Combined or separated product grid (optional, see Iblock\IblockTable::LIST_MODE_*).
	 *		<li>string USE_FILTER Use grid filter (Y/N, default Y).
	 *		<li>string GRID_ID Dom grid id (optional).
	 *		<li>string FILTER_ID Dom filter id (optional).
	 *		<li>string NAVIGATION_ID Dom navigation bar id (optional).
	 *		<li>string BASE_LINK Url for ajax actions (optional).
	 *		</ul>
	 *
	 * @return array|null
	 */
	public function onPrepareComponentParams($params): ?array
	{
		if ($this->checkModules()->hasErrors())
		{
			return null;
		}

		$params = (is_array($params) ? $params : []);

		$params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);
		if ($this->setIblockId($params['IBLOCK_ID'])->hasErrors())
		{
			return null;
		}

		$params['LIST_MODE'] = (string)($params['LIST_MODE'] ?? '');
		if ($this->setListMode($params['LIST_MODE'])->hasErrors())
		{
			return null;
		}
		$params['USE_FILTER'] = (string)($params['USE_FILTER'] ?? 'Y');
		$this->setUseFilter($params['USE_FILTER'] !== 'N');

		$params = $this->prepareDomIdParameters($params);

		$params['BASE_LINK'] = (string)($params['BASE_LINK'] ?? '');
		$params['SHOW_ALL_BUTTON'] ??= 'N';

		$params['SKU_SELECTOR_ENABLE'] = (string)($params['SKU_SELECTOR_ENABLE'] ?? 'Y');
		if ($params['SKU_SELECTOR_ENABLE'] !== 'N')
		{
			$params['SKU_SELECTOR_ENABLE'] = 'Y';
		}

		$params['USE_NEW_CARD'] ??= State::isProductCardSliderEnabled() ? 'Y' : 'N';
		if (isset($params['USE_NEW_CARD']) && $params['USE_NEW_CARD'] !== 'Y')
		{
			$params['USE_NEW_CARD'] = 'N';
		}

		if (isset($params['URL_BUILDER']) && ($params['URL_BUILDER'] instanceof CatalogBuilder) === false)
		{
			throw new SystemException('Parameter "URL_BUILDER" must implement ' . CatalogBuilder::class);
		}

		return parent::onPrepareComponentParams($params);
	}

	protected function checkModules(): self
	{
		if (!Loader::includeModule('iblock'))
		{
			$this->errorCollection[] = new Main\Error(
				Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_MODULE_IBLOCK_IS_ABSENT'),
				self::ERROR_CODE_MODULE_IS_ABSENT
			);

			return $this;
		}
		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new Main\Error(
				Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_MODULE_CATALOG_IS_ABSENT'),
				self::ERROR_CODE_MODULE_IS_ABSENT
			);

			return $this;
		}

		Loader::includeModule('fileman');

		return $this;
	}

	protected function showErrors(): void
	{
		$description =
			Loader::includeModule('bitrix24')
				? null
				: ''
		;
		foreach ($this->getErrors() as $error)
		{
			$this->includeErrorComponent($error->getMessage(), $description);
		}
	}

	protected function showCatalogStub(): void
	{
		$this->arResult['STUB_REDIRECT'] = Loader::includeModule('crm') ? '/crm/' : '/';

		$this->includeComponentTemplate('stub');
	}

	protected function includeErrorComponent(string $errorMessage, string $description = null): void
	{
		UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			'bitrix:ui.info.error',
			'',
			[
				'TITLE' => $errorMessage,
				'DESCRIPTION' => $description,
				'IS_HTML' => 'Y',
			]
		);
	}

	protected function getIblockId(): ?int
	{
		return $this->iblockId;
	}

	protected function hasIblock(): bool
	{
		return $this->getIblockId() !== null;
	}

	protected function setIblockId(int $iblockId): self
	{
		if ($iblockId <= 0)
		{
			$this->errorCollection[] = new Main\Error(
				Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_IBLOCK_ID_IS_ABSENT'),
				self::ERROR_CODE_BAD_PARAMETER
			);

			return $this;
		}

		// TODO: change \CIBlock::GetArrayByID to new d7 api
		$iblock = \CIBlock::GetArrayByID($iblockId);
		if (!is_array($iblock))
		{
			$this->errorCollection[] = new Main\Error(
				Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_BAD_IBLOCK_ID'),
				self::ERROR_CODE_BAD_PARAMETER
			);

			return $this;
		}

		if (!\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::ACCESS_LEVEL_IBLOCK))
		{
			$this->errorCollection[] = new Main\Error(
				Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_IBLOCK_ACCESS_DENIED'),
				self::ERROR_CODE_ACCESS
			);

			return $this;
		}

		$this->iblockId = $iblockId;
		$this->iblock = $iblock;

		return $this->initCatalog();
	}

	protected function initCatalog(): self
	{
		// TODO: cnange \CCatalogSku::GetInfoByIBlock to new d7 api
		$catalog = \CCatalogSku::GetInfoByIBlock($this->iblockId);
		if (!is_array($catalog))
		{
			$this->errorCollection[] = new Main\Error(
				Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_IBLOCK_IS_NOT_CATALOG'),
				self::ERROR_CODE_BAD_PARAMETER
			);

			return $this;
		}


		$this->catalog = $catalog;
		$this->catalogType = $catalog['CATALOG_TYPE'];
		$this->useOffers = (
			$this->catalogType === \CCatalogSku::TYPE_FULL
			|| $this->catalogType === \CCatalogSku::TYPE_PRODUCT
		);
		if ($this->useOffers)
		{
			if (!\CIBlockRights::UserHasRightTo(
					$this->catalog['IBLOCK_ID'],
					$this->catalog['IBLOCK_ID'],
					self::ACCESS_LEVEL_IBLOCK
			))
			{
				$this->errorCollection[] = new Main\Error(
					Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_IBLOCK_OFFERS_ACCESS_DENIED'),
					self::ERROR_CODE_ACCESS
				);
			}
		}

		return $this;
	}

	protected function hasOffersIblockId(): bool
	{
		return $this->useOffers;
	}

	protected function getOffersIblockId(): ?int
	{
		return ($this->hasOffersIblockId() ? $this->catalog['IBLOCK_ID'] : null);
	}

	protected function setListMode(string $listMode): self
	{
		if (
			$listMode === Iblock\IblockTable::LIST_MODE_SEPARATE
			|| $listMode === Iblock\IblockTable::LIST_MODE_COMBINED
		)
		{
			$this->iblockListMode = $listMode;
		}

		return $this;
	}

	protected function getListMode(): string
	{
		if (!isset($this->iblockListMode))
		{
			if (
				$this->hasIblock()
				&& (
					$this->iblock['LIST_MODE'] === Iblock\IblockTable::LIST_MODE_SEPARATE
					|| $this->iblock['LIST_MODE'] === Iblock\IblockTable::LIST_MODE_COMBINED
				)
			)
			{
				$this->iblockListMode = $this->iblock['LIST_MODE'];
			}
			else
			{
				$this->iblockListMode =
					Main\Config\Option::get('iblock', 'combined_list_mode') === 'Y'
						? Iblock\IblockTable::LIST_MODE_COMBINED
						: Iblock\IblockTable::LIST_MODE_SEPARATE
				;
			}
		}

		return $this->iblockListMode;
	}

	protected function isCombinedListMode(): bool
	{
		return $this->getListMode() === Iblock\IblockTable::LIST_MODE_COMBINED;
	}

	protected function isUsedSkuSelector(): bool
	{
		if (!$this->hasOffersIblockId())
		{
			return false;
		}
		if ($this->arParams['SKU_SELECTOR_ENABLE'] !== 'Y')
		{
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	protected function isUsedGridFilter(): bool
	{
		return $this->useGridFilter;
	}

	protected function setUseFilter(bool $state): self
	{
		$this->useGridFilter = $state;

		return $this;
	}

	/**
	 * @return array
	 */
	protected function getVisibleColumnIds(): array
	{
		return $this->grid->getVisibleColumnsIds();
	}

	/**
	 * @param string $gridId
	 * @return string
	 */
	protected static function createFilterId(string $gridId): string
	{
		return $gridId . '_FILTER';
	}

	/**
	 * @param string $gridId
	 * @return string
	 */
	protected static function createNavigationId(string $gridId): string
	{
		return $gridId . '_NAVIGATION';
	}

	protected function prepareDomIdParameters(array $params): array
	{
		$params['GRID_ID'] = (string)($params['GRID_ID'] ?? '');
		$params['GRID_ID'] = preg_replace(self::DOM_ID_MASK, '', $params['GRID_ID']);

		$params['NAVIGATION_ID'] = (string)($params['NAVIGATION_ID'] ?? '');
		$params['NAVIGATION_ID'] = preg_replace(self::DOM_ID_MASK, '', $params['NAVIGATION_ID']) ?: null;

		return $params;
	}

	// region Rights

	protected function checkPermissions(): self
	{
		if (!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			$this->errorCollection[] = new Main\Error(
				Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_ERR_ACCESS_LEVEL_IS_ABSENT'),
				self::ERROR_CODE_ACCESS
			);

			return $this;
		}

		return $this;
	}

	protected function allowAdd(): bool
	{
		return $this->accessController->check(ActionDictionary::ACTION_PRODUCT_ADD);
	}

	protected function allowEditPrice(): bool
	{
		return $this->accessController->check(ActionDictionary::ACTION_PRICE_EDIT);
	}

	protected function allowInstagramImport(): bool
	{
		return
			Loader::includeModule('crm')
			&& Instagram::isAvailable()
			&& $this->accessController->check(ActionDictionary::ACTION_CATALOG_IMPORT_EXECUTION)
		;
	}

	protected function allowExcelExport(): bool
	{
		return $this->accessController->check(ActionDictionary::ACTION_CATALOG_EXPORT_EXECUTION);
	}

	// endregion

	protected function init(): self
	{
		$this->initSkuTree();
		$this->initGrid();
		$this->initGridFilter();

		return $this;
	}

	protected function getSelectedSectionId(): int
	{
		return $this->arParams['SECTION_ID'] ?? 0;
	}

	protected function initSkuTree(): void
	{
		if ($this->isUsedSkuSelector())
		{
			$this->skuTree = Catalog\v2\IoC\ServiceContainer::make(
				'sku.tree',
				[
					'iblockId' => $this->getIblockId(),
				]
			);
		}
		else
		{
			$this->skuTree = null;
		}
	}

	protected function initGrid(): void
	{
		$settings = new \Bitrix\Catalog\Grid\Settings\ProductSettings([
			'ID' => $this->getGridId(),
			'IBLOCK_ID' => $this->getIblockId(),
		]);
		$settings->setListMode($this->getListMode());
		$settings->setUrlBuilder($this->getUrlBuilder());
		$settings->setSelectedProductOfferIds($this->getSelectedVariationMap());
		$settings->setSkuSelectorEnable($this->isUsedSkuSelector());
		$settings->setNewCardEnabled($this->arParams['USE_NEW_CARD'] === 'Y');

		if ($this->isExcelExport())
		{
			$settings->setMode(Settings::MODE_EXCEL);
		}

		$this->grid = new ProductGrid($settings);

		if ($this->isUsedGridFilter())
		{
			$this->grid->setUseFilter(true);

			// process only direct requests so as not to conflict with the filter, which is updated by ajax
			if (!$this->request->isAjaxRequest() && !$this->isExcelExport())
			{
				$selectedSectionId = $this->getSelectedSectionId();
				if ($selectedSectionId >= 0)
				{
					$options = new \Bitrix\Main\UI\Filter\Options(
						$this->grid->getFilter()->getEntityDataProvider()->getSettings()->getID()
					);

					$presetId = $options->getCurrentFilterId();
					if ($presetId !== \Bitrix\Main\UI\Filter\Options::TMP_FILTER)
					{
						$presetId = \Bitrix\Main\UI\Filter\Options::TMP_FILTER;
						$options->setCurrentFilterPresetId($presetId);
						$options->setCurrentPreset($presetId);
					}

					$settings = $options->getFilterSettings($presetId);
					$settings['fields'] = [
						'SECTION_ID' => $selectedSectionId,
					];

					$options->setFilterSettings($presetId, $settings, true, false);
					$options->save();
				}
			}
		}

		$this->grid->initPagination(
			0,
			$this->getNavigationId()
		);
	}

	/**
	 * @return string
	 */
	protected function getGridId(): string
	{
		$this->gridId ??= $this->arParams['GRID_ID'] ?: (new ReflectionClass($this))->getShortName();

		return $this->gridId;
	}

	/**
	 * @return string|null
	 */
	protected function getNavigationId(): ?string
	{
		return $this->arParams['NAVIGATION_ID'] ?? null;
	}

	protected function isShowAllRecords(): bool
	{
		return $this->arParams['SHOW_ALL_BUTTON'] === 'Y';
	}

	/**
	 * @return array
	 */
	protected function getGridNavigationParams(): array
	{
		$pagination = $this->grid->getPagination();
		if (isset($pagination))
		{
			return [
				'nPageSize' => $pagination->getPageSize(),
				'iPageNum' => $pagination->getCurrentPage(),
			];
		}

		return [];
	}

	protected function initGridFilter(): void
	{
		if (!$this->isUsedGridFilter() || !$this->hasIblock())
		{
			return;
		}

		// toolbar
		$options = \Bitrix\Main\Filter\Component\ComponentParams::get(
			$this->grid->getFilter(),
			[
				'GRID_ID' => $this->grid->getId(),
				'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => \Bitrix\Main\ModuleManager::isModuleInstalled('ui'),
				'ENABLE_FIELDS_SEARCH' => 'Y',
				'CONFIG' => [
					'popupWidth' => 800,
				],
			]
		);
		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($options);
	}

	private function getUpdateVariationFieldNames(): array
	{
		$result = [
			'VAT_ID',
			'VAT_INCLUDED',
			'MEASURE_RATIO',
			'MEASURE',
			'PURCHASING_PRICE',
			'PURCHASING_CURRENCY',
			'QUANTITY_TRACE',
			'QUANTITY',
			'QUANTITY_RESERVED',
			'WIDTH',
			'LENGTH',
			'WEIGHT',
			'HEIGHT',
			'CAN_BUY_ZERO',
			'BARCODE',
			'MORE_PHOTO',
		];

		foreach (GroupTable::getTypeList() as $priceType)
		{
			$result[] = PriceProvider::getPriceTypeColumnId($priceType['ID']);
			$result[] = PriceProvider::getCurrencyPriceTypeId($priceType['ID']);
		}

		return $result;
	}

	private function setSelectedVariationMap(array $value): void
	{
		$this->selectedVariationMap = $value;
	}

	private function setSelectedVariationMapFromProductIds(array $productIds): void
	{
		if (!isset($this->skuTree))
		{
			return;
		}

		$selectedVariationMap = $this->getSelectedVariationMap();

		// load from sku tree, to get correct order of offers.
		$tree = $this->skuTree->load($productIds);
		foreach ($tree as $productId => $item)
		{
			if (!isset($selectedVariationMap[$productId]) && !empty($item['OFFERS']))
			{
				$firstOffer = reset($item['OFFERS']);
				if (is_array($firstOffer))
				{
					$selectedVariationMap[$productId] = (int)$firstOffer['ID'];
				}
			}
		}

		if (empty($selectedVariationMap))
		{
			return;
		}

		$this->setSelectedVariationMap($selectedVariationMap);

		// updates it in the grid if it is already initialized.
		if (isset($this->grid))
		{
			$this->grid->getSettings()->setSelectedProductOfferIds($selectedVariationMap);
		}
	}

	private function getSelectedVariationMap(): array
	{
		if (!isset($this->selectedVariationMap))
		{
			$this->selectedVariationMap = [];

			$request = Context::getCurrent()->getRequest();

			$requestedProductId = (int)($request->get('productId') ?? 0);
			$requestedVariationId = (int)($request->get('variationId') ?? 0);

			if ($requestedProductId > 0 && $requestedVariationId > 0)
			{
				$this->selectedVariationMap = [
					$requestedProductId => $requestedVariationId,
				];
			}

		}

		return $this->selectedVariationMap;
	}

	protected function prepareResult(): void
	{
		$additional = [
			'NAV_STRING' => $this->productNavString,
			'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => \Bitrix\Main\ModuleManager::isModuleInstalled('ui'),
			'ENABLE_FIELDS_SEARCH' => 'Y',
			'CONFIG' => [
				'popupWidth' => 800,
			],
		];
		$this->arResult = [
			'GRID' => \Bitrix\Main\Grid\Component\ComponentParams::get($this->grid, $additional),
			'SKU_FIELD_NAMES' => $this->getUpdateVariationFieldNames(),
			'SKU_PRODUCT_MAP' => $this->getSelectedVariationMap(),
			'CAN_EDIT_PRICE' => $this->allowEditPrice(),
			'URL_TO_ADD_PRODUCT' => $this->getUrlBuilder()->getProductDetailUrl(0),
		];
	}

	// endregion

	// region Data

	protected function getDataOrder(): array
	{
		return $this->grid->getOrmOrder();
	}

	protected function getDataFilter(): array
	{
		$filter = $this->grid->getOrmFilter();
		if (isset($filter['SECTION_ID']) && isset($filter['INCLUDE_SUBSECTIONS']))
		{
			if ((int)$filter['SECTION_ID'] === 0 && $filter['INCLUDE_SUBSECTIONS'] === 'Y')
			{
				unset($filter['SECTION_ID'], $filter['INCLUDE_SUBSECTIONS']);
			}
		}

		return $filter;
	}

	// region Viewed column tools
	protected function isViewElementCount():bool
	{
		return in_array('ELEMENT_CNT', $this->getVisibleColumnIds(), true);
	}

	protected function isViewProductBlock(): bool
	{
		return in_array('PRODUCT', $this->getVisibleColumnIds(), true);
	}

	// endregion

	protected function loadRows(): void
	{
		$this->initRows();

		if ($this->isCombinedListMode())
		{
			$iterator = $this->getProductWithSectionsIterator();
		}
		else
		{
			$iterator = $this->getProductIterator();
		}

		if (!$this->isExcelExport())
		{
			$params = $this->getGridNavigationParams();

			$iterator->NavStart(
				$params['nPageSize'],
				$this->isShowAllRecords(),
				false
			);
		}

		while ($row = $iterator->Fetch())
		{
			$rowType = $row['TYPE'] ?? Iblock\Grid\RowType::ELEMENT;
			$index = Iblock\Grid\RowType::getIndex($rowType, $row['ID']);

			$this->rows[$index] = [
				'ID' => $row['ID'],
				'ROW_TYPE' => $rowType,
			];

			if ($rowType === Iblock\Grid\RowType::SECTION)
			{
				$this->sectionIds[] = (int)$row['ID'];
			}
			else
			{
				$this->elementIds[] = (int)$row['ID'];
			}
		}

		$this->productNavString = $this->getProductImplicitNavigationData($iterator);

		$this->internalLoadSections();
		$this->internalLoadElements();
		$this->internalLoadSku();
		$this->internalLoadProductQuantities();

		$this->grid->setRawRows($this->rows);

		$pagination = $this->grid->getPagination();
		$pagination->setRecordCount($iterator->SelectedRowsCount());
		unset($pagination);

		unset($iterator);
	}

	protected function initRows(): void
	{
		$this->rows = [];
		$this->sectionIds = [];
		$this->elementIds = [];
		$this->parentIds = [];
	}

	protected function getProductWithSectionsIterator(): \CDBResult
	{
		$filter = $this->getDataFilter();
		$getCount = $this->isViewElementCount();
		if ($getCount)
		{
			$filter['CNT_ALL'] = 'Y';
			$filter['ELEMENT_SUBSECTIONS'] = 'N';
		}

		$iterator = \CIBlockSection::GetMixedList(
			$this->getDataOrder(),
			$filter,
			$getCount,
			[
				'ID',
				'IBLOCK_ID',
			]
		);
		unset($getCount, $filter);

		return $iterator;
	}

	protected function getProductIterator(): \CDBResult
	{
		return \CIBlockElement::GetList(
			$this->getDataOrder(),
			$this->getDataFilter(),
			false,
			false,
			[
				'ID',
				'IBLOCK_ID',
			]
		);
	}

	protected function getDataSelect(): array
	{
		return $this->grid->getOrmSelect();
	}

	protected function internalLoadSections(): void
	{
		if (empty($this->sectionIds))
		{
			return;
		}

		//TODO: replace to full code
		$select = $this->getDataSelect();
		$changePreviewPicture = in_array('PREVIEW_PICTURE', $select, true);

		//TODO: replace to full code
		$filter = [
			'IBLOCK_ID' => $this->getIblockId(),
			'CHECK_PERMISSIONS' => 'N',
		];

		foreach (array_chunk($this->sectionIds, CATALOG_PAGE_SIZE) as $pageIds)
		{
			$filter['ID'] = $pageIds;

			$iterator = \CIBlockSection::GetList(
				array(),
				$filter,
				false,
				$select
			);
			while ($row = $iterator->Fetch())
			{
				if ($changePreviewPicture)
				{
					$row['PREVIEW_PICTURE'] = $row['PICTURE'];
				}
				$index = Iblock\Grid\RowType::getIndex(Iblock\Grid\RowType::SECTION, $row['ID']);
				$this->rows[$index] += $row;
			}
			unset($row, $iterator);
		}
	}

	protected function internalLoadElements(): void
	{
		if (empty($this->elementIds))
		{
			return;
		}

		//TODO: replace to full code
		$select = $this->getDataSelect();

		//TODO: replace to full code
		$filter = [
			'IBLOCK_ID' => $this->getIblockId(),
			'CHECK_PERMISSIONS' => 'N',
			'SHOW_NEW' => 'Y',
		];

		foreach (array_chunk($this->elementIds, CATALOG_PAGE_SIZE) as $pageIds)
		{
			$filter['ID'] = $pageIds;

			$iterator = \CIBlockElement::GetList(
				array(),
				$filter,
				false,
				false,
				$select
			);
			while ($row = $iterator->Fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$index = Iblock\Grid\RowType::getIndex(Iblock\Grid\RowType::ELEMENT, $row['ID']);
				$this->rows[$index] += $row;

				if ((int)$row['TYPE'] === Catalog\ProductTable::TYPE_SKU)
				{
					$this->parentIds[] = $row['ID'];
				}
			}
			unset($row, $iterator);
		}

		$this->internalLoadElementsProperties();
	}

	protected function internalLoadElementsProperties(): void
	{
		if (empty($this->elementIds))
		{
			return;
		}

		$propertyColumnsToIds = ElementPropertyProvider::getPropertyIdsFromColumnsIds(
			$this->grid->getVisibleColumnsIds()
		);
		if (empty($propertyColumnsToIds))
		{
			return;
		}

		$multiple = $this->getPropertyMultipleState($propertyColumnsToIds);

		$emptyPropertyValue = $this->getPropertyEmptyValue();

		$emptyProperties = [];
		foreach ($propertyColumnsToIds as $columnId => $propertyId)
		{
			if ($multiple[$propertyId])
			{
				$emptyProperties[$columnId] = [
					$emptyPropertyValue
				];
			}
			else
			{
				$emptyProperties[$columnId] = $emptyPropertyValue;
			}
		}

		$iblockId = $this->getIblockId();
		foreach (array_chunk($this->elementIds, CATALOG_PAGE_SIZE) as $pageIds)
		{
			$properties = [];
			CIBlockElement::GetPropertyValuesArray(
				$properties,
				$iblockId,
				[
					'ID' => $pageIds,
				],
				[
					'ID' => $propertyColumnsToIds,
				],
				[
					'USE_PROPERTY_ID' => 'Y',
					'GET_RAW_DATA' => 'Y',
					'PROPERTY_FIELDS' => [
						'ID',
						'PROPERTY_TYPE',
					],
				]
			);

			foreach ($pageIds as $elementId)
			{
				$index = Iblock\Grid\RowType::getIndex(Iblock\Grid\RowType::ELEMENT, $elementId);
				if (isset($properties[$elementId]))
				{
					foreach ($propertyColumnsToIds as $columnId => $propertyId)
					{
						if (isset($properties[$elementId][$propertyId]))
						{
							$values = $properties[$elementId][$propertyId];
							$isList = $values['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_LIST;
							if ($multiple[$propertyId])
							{
								if (is_array($values['VALUE']))
								{
									$list = [];
									foreach (array_keys($values['VALUE']) as $valueIndex)
									{
										$row = [
											'VALUE' => $values['VALUE'][$valueIndex],
											'DESCRIPTION' => $values['DESCRIPTION'][$valueIndex] ?? null,
										];
										if ($isList)
										{
											$row['VALUE_ENUM_ID'] = $values['VALUE_ENUM_ID'][$valueIndex];
										}
										$list[] = $row;
									}
									unset($valueIndex);
									$this->rows[$index][$columnId] = $list;
									unset($list);
								}
								else
								{
									$this->rows[$index][$columnId] = [
										$emptyPropertyValue
									];
								}
							}
							else
							{
								$this->rows[$index][$columnId] = [
									'VALUE' => $values['VALUE'],
									'DESCRIPTION' => $values['DESCRIPTION'] ?? null,
								];
								if ($isList)
								{
									$this->rows[$index][$columnId]['VALUE_ENUM_ID'] = $values['VALUE_ENUM_ID'];
								}
							}
							unset($values);
						}
						else
						{
							if ($multiple[$propertyId])
							{
								$this->rows[$index][$columnId] = [
									$emptyPropertyValue
								];
							}
							else
							{
								$this->rows[$index][$columnId] = $emptyPropertyValue;
							}
						}
					}
				}
				else
				{
					$this->rows[$index] += $emptyProperties;
				}
			}
			unset($pageIds);
		}
	}

	private function getPropertyMultipleState(array $propertyIds): array
	{
		$multiple = array_fill_keys($propertyIds, false);
		$iterator = Iblock\PropertyTable::getList([
			'select' => [
				'ID',
				'MULTIPLE',
			],
			'filter' => [
				'@ID' => $propertyIds,
			],
			'cache' => [
				86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$multiple[$row['ID']] = $row['MULTIPLE'] === 'Y';
		}
		unset($row, $iterator);

		return $multiple;
	}

	private function getPropertyEmptyValue(): array
	{
		return [
			'VALUE' => null,
			'DESCRIPTION' => null,
		];
	}

	protected function internalLoadProductQuantities(): void
	{
		if (
			empty($this->rows)
			|| !ProductProvider::needSummaryStoreAmountByPermissions()
		)
		{
			return;
		}

		$isShowedQuantitiesColumns =
			in_array('QUANTITY', $this->getVisibleColumnIds(), true)
			|| in_array('QUANTITY_RESERVED', $this->getVisibleColumnIds(), true)
		;
		if (!$isShowedQuantitiesColumns)
		{
			return;
		}

		// zeroing values
		$productsAndOffersIds = [];
		$variationMap = $this->getSelectedVariationMap();

		foreach ($this->rows as &$row)
		{
			$type = (string)$row['ROW_TYPE'];
			if ($type === RowType::SECTION)
			{
				continue;
			}

			$row['QUANTITY'] = null;
			$row['QUANTITY_RESERVED'] = null;

			$productId = (int)$row['ID'];
			$productsAndOffersIds[] = $productId;
			if (isset($variationMap[$productId]))
			{
				$productsAndOffersIds[] = $variationMap[$productId];
			}
		}
		unset($row, $variationMap);

		// fill actual
		$isNoAccessToAnyStores = $this->accessController->getAllowedDefaultStoreId() === null;
		if ($isNoAccessToAnyStores || empty($productsAndOffersIds))
		{
			return;
		}

		$query = StoreProductTable::query()
			->setSelect([
				'PRODUCT_ID',
				new ExpressionField('SUM_QUANTITY', 'SUM(%s - %s)', ['AMOUNT', 'QUANTITY_RESERVED']),
				new ExpressionField('SUM_QUANTITY_RESERVED', 'SUM(%s)', 'QUANTITY_RESERVED'),
			])
			->setGroup('PRODUCT_ID')
			->setFilter([
				'@PRODUCT_ID' => $productsAndOffersIds,
			])
		;

		$accessFilter = $this->accessController->getEntityFilter(ActionDictionary::ACTION_STORE_VIEW, StoreProductTable::class);
		if (!empty($accessFilter))
		{
			$query->addFilter(null, $accessFilter);
		}

		$productVariationMap = array_flip($this->getSelectedVariationMap());
		foreach ($query->exec() as $storeQuantityRow)
		{
			$productId = (int)$storeQuantityRow['PRODUCT_ID'];
			if (isset($productVariationMap[$productId]))
			{
				$productId = $productVariationMap[$productId];
			}

			$index = RowType::getIndex(RowType::ELEMENT, $productId);
			if (empty($this->rows[$index]))
			{
				continue;
			}

			$this->rows[$index]['QUANTITY'] = (float)$storeQuantityRow['SUM_QUANTITY'];
			$this->rows[$index]['QUANTITY_RESERVED'] = (float)$storeQuantityRow['SUM_QUANTITY_RESERVED'];
		}
	}

	protected function internalLoadSku(): void
	{
		if (!$this->hasOffersIblockId())
		{
			return;
		}
		if (!isset($this->skuTree))
		{
			return;
		}
		if (empty($this->parentIds))
		{
			return;
		}
		if (!$this->isViewProductBlock())
		{
			return;
		}

		$this->setSelectedVariationMapFromProductIds($this->parentIds);
		$selectedVariationMap = $this->getSelectedVariationMap();
		if (empty($selectedVariationMap))
		{
			return;
		}

		$offersSelect = [
			'ID',
			'PROPERTY_CML2_LINK',
			... $this->getUpdateVariationFieldNames()
		];
		$offersFilter = [
			'ID' => $selectedVariationMap,
			'IBLOCK_ID' => $this->getOffersIblockId(),
			'CHECK_PERMISSIONS' => 'N',
			'SHOW_NEW' => 'Y',
		];
		$iterator = \CIBlockElement::GetList(
			['SORT' => 'ASC', 'ID' => 'ASC'],
			$offersFilter,
			false,
			false,
			$offersSelect
		);

		while ($row = $iterator->Fetch())
		{
			$productId = (int)$row['PROPERTY_CML2_LINK_VALUE'];
			unset(
				$row['ID'],
				$row['PROPERTY_CML2_LINK_VALUE'],
				$row['PROPERTY_CML2_LINK_VALUE_ID'],
				$row['SORT'],
			);

			$index = Iblock\Grid\RowType::getIndex(Iblock\Grid\RowType::ELEMENT, $productId);
			if (isset($this->rows[$index]))
			{
				$this->rows[$index] = array_merge($this->rows[$index], $row);
			}
		}
		unset($iterator);
	}

	public function executeComponent(): void
	{
		if ($this->hasErrors())
		{
			$this->showErrors();

			return;
		}
		if ($this->checkPermissions()->hasErrors())
		{
			$this->showErrors();

			return;
		}

		if (State::isExternalCatalog())
		{
			$this->showCatalogStub();

			return;
		}

		if ($this->init()->hasErrors())
		{
			$this->showErrors();

			return;
		}

		$this->grid->processRequest();

		$this->loadRows();
		$this->prepareResult();

		if ($this->allowExcelExport())
		{
			$this->processExcelExport();
		}

		$this->initToolbar();
		$this->includeComponentTemplate();

		$this->clear();
	}

	/**
	 * @return bool
	 */
	public function isExcelExport(): bool
	{
		return $this->getExcelExporter()->isExportRequest($this->request);
	}

	protected function processExcelExport(): void
	{
		if ($this->isExcelExport())
		{
			$this->getExcelExporter()->process(
				$this->grid,
				$this->arParams['EXPORT_FILE_NAME'] ?? 'export'
			);
		}
	}

	protected function clear(): void
	{
		//unset($this->rightList);

		unset($this->rows);
		unset($this->elementIds, $this->sectionIds);
	}

	private function getExcelExporter(): ExcelExporter
	{
		$this->excelExporter ??= new ExcelExporter();

		return $this->excelExporter;
	}

	private function getUrlBuilder(): ShopBuilder
	{
		if (!isset($this->urlBuilder))
		{
			$paramsBuilder = $this->arParams['URL_BUILDER'] ?? null;
			if ($paramsBuilder instanceof ShopBuilder)
			{
				$this->urlBuilder = $paramsBuilder;
			}
			else
			{
				$this->urlBuilder = new ShopBuilder();
			}

			$this->urlBuilder->setIblockId($this->getIblockId());
			$this->urlBuilder->setUrlParams([]); // hack for append slider URL params
		}

		return $this->urlBuilder;
	}

	private function initToolbar(): void
	{
		$this->initToolbarCreateButtons();
		$this->initToolbarSettings();
	}

	private function getCreateButtonMenuItemsForNewCard(): array
	{
		$options = [
			'IBLOCK_SECTION_ID' => $this->getSelectedSectionId(),
		];

		return [
			[
				'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_PRODUCT_TEXT'),
				'href' => $this->getUrlBuilder()->getProductDetailUrl(0, $options),
			],
			[
				'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_SERVICE_TEXT'),
				'href' => $this->getUrlBuilder()->getProductDetailUrl(0, $options + [
					'productTypeId' => ProductTable::TYPE_SERVICE,
				]),
			],
			[
				'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_SECTION_TEXT'),
				'href' => $this->getUrlBuilder()->getSectionDetailUrl(0, $options),
			],
		];
	}

	private function getCreateButtonMenuItemsForOldCard(): array
	{
		$options = [
			'IBLOCK_SECTION_ID' => $this->getSelectedSectionId(),
		];

		$result = [
			[
				'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_PRODUCT_TEXT'),
				'href' => $this->getUrlBuilder()->getElementDetailUrl(0, $options),
			],
		];

		if ($this->catalogType === CCatalogSku::TYPE_FULL)
		{
			$result[] = [
				'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_SKU_TEXT'),
				'href' => $this->getUrlBuilder()->getElementDetailUrl(0, $options + [
					'PRODUCT_TYPE' => CCatalogAdminTools::TAB_SKU,
				]),
			];
		}

		$result[] = [
			'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_SERVICE_TEXT'),
			'href' => $this->getUrlBuilder()->getElementDetailUrl(0, $options + [
				'PRODUCT_TYPE' => CCatalogAdminTools::TAB_SERVICE,
			]),
		];

		if (Feature::isProductSetsEnabled())
		{
			if ($this->catalogType !== CCatalogSku::TYPE_OFFERS)
			{
				$result[] = [
					'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_SET_TEXT'),
					'href' => $this->getUrlBuilder()->getElementDetailUrl(0, $options + [
						'PRODUCT_TYPE' => CCatalogAdminTools::TAB_SET,
					]),
				];
			}

			$result[] = [
				'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_GROUP_TEXT'),
				'href' => $this->getUrlBuilder()->getElementDetailUrl(0, $options + [
					'PRODUCT_TYPE' => CCatalogAdminTools::TAB_GROUP,
				]),
			];
		}

		$result[] = [
			'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_ADD_SECTION_TEXT'),
			'href' => $this->getUrlBuilder()->getSectionDetailUrl(0, $options),
		];

		return $result;
	}

	private function initToolbarCreateButtons(): void
	{
		$productLimits = null;
		if ($this->hasIblock())
		{
			$productLimits = Catalog\Config\State::getExceedingProductLimit(
				$this->getIblockId(),
				$this->getSelectedSectionId() ?: null
			);
		}

		if (empty($productLimits))
		{
			$createButton = new CreateButton();

			// flag create button for js ext `catalog.iblock-product-list`
			$createButton->addDataAttribute('grid-create-button', $this->getGridId());
			$createButton->addDataAttribute('toolbar-collapsed-icon', UI\Buttons\Icon::ADD);

			$menuItems = [];
			if ($this->allowAdd())
			{
				if ($this->arParams['USE_NEW_CARD'] === 'Y')
				{
					$menuItems = $this->getCreateButtonMenuItemsForNewCard();
				}
				else
				{
					$menuItems = $this->getCreateButtonMenuItemsForOldCard();
				}
			}

			if ($this->allowInstagramImport())
			{
				$menuItems[] = [
					'text' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_IMPORT_INSTAGRAM_TEXT'),
					'title' => Loc::getMessage('CATALOG_PRODUCT_GRID_CMP_TOOLBAR_BUTTONS_IMPORT_INSTAGRAM_TITLE'),
					'href' => Option::get('crm', 'path_to_order_import_instagram'),
				];
			}

			if (empty($menuItems))
			{
				$createButton->setDisabled();
				$createButton->getAttributeCollection()['id'] = 'create_new_product_button_access_denied';
			}
			else
			{
				$createButton->getMainButton()->setLink(
					$menuItems[0]['href']
				);
				$createButton->setMenu([
					'items' => $menuItems,
					'closeByEsc' => true,
					'angle' => true,
					'offsetLeft' => 20,
				]);
				$createButton->getAttributeCollection()->addJsonOption(
					'menuTarget',
					\Bitrix\UI\Buttons\Split\Type::MENU
				);
			}

		}
		else
		{
			if (isset($productLimits['HELP_MESSAGE']['TYPE'], $productLimits['HELP_MESSAGE']['LINK']))
			{
				$createButton = new \Bitrix\UI\Buttons\CreateButton();
				if ($productLimits['HELP_MESSAGE']['TYPE'] === 'ONCLICK')
				{
					$createButton->getAttributeCollection()['onclick'] = $productLimits['HELP_MESSAGE']['LINK'];
				}
				else
				{
					$createButton->setLink($productLimits['HELP_MESSAGE']['LINK']);
				}
			}
		}

		if (isset($createButton))
		{
			Toolbar::addButton($createButton, ButtonLocation::AFTER_TITLE);
		}
	}

	private function initToolbarSettings(): void
	{
		$items = $this->getUrlBuilder()->getContextMenuItems(
			CatalogBuilder::PAGE_ELEMENT_LIST
		);
		$menu = array_map(
			static function (array $item)
			{
				$result = [
					'text' => $item['TEXT'] ?? null,
				];

				if (isset($item['TITLE']))
				{
					$result['title'] = $item['TITLE'];
				}

				if (isset($item['LINK']))
				{
					$result['href'] = $item['LINK'];
				}

				if (isset($item['ONCLICK']))
				{
					$result['onclick'] = [
						'code' => $item['ONCLICK'],
					];
				}

				return $result;
			},
			$items
		);

		if ($this->allowExcelExport())
		{
			$menu[] = $this->getExportExcelDropdownItem();
		}

		$settingsButton = new SettingsButton();
		$settingsButton->setMenu([
			'items' => $menu,
		]);

		Toolbar::addButton($settingsButton, ButtonLocation::RIGHT);
	}

	private function getExportExcelDropdownItem(): array
	{
		$button = $this->getExcelExporter()->getControl($this->request);

		$link = new Uri($button->getLink());
		$link->deleteParams([
			'SECTION_ID',
			'find_section_section',
			'apply_filter',
		]);
		// TODO: remove this code after stable main 23.800.0
		$link->addParams([
			'ncc' => 1,
		]);

		return [
			'text' => $button->getText(),
			// 'href' => $button->getLink(),
			'href' => (string)$link,
		];
	}

	public function showDetailPageSlider(): void
	{
		$this->getUrlBuilder()->showDetailPageSlider();
	}

	private function getProductImplicitNavigationData(\CDBResult $iterator): string
	{
		$navComponentObject = null;
		$navComponentParameters = [];
		if ($this->arParams['BASE_LINK'] !== '')
		{
			$navComponentParameters["BASE_LINK"] = \CHTTP::urlAddParams(
				$this->arParams['BASE_LINK'],
				[],
				['encode' => true]
			);
		}
		return (string)$iterator->GetPageNavStringEx(
			$navComponentObject,
			'',
			'grid',
			true,
			null,
			$navComponentParameters
		);
	}
}
