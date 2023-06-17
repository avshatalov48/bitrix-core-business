<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\BaseForm;
use Bitrix\Catalog\Component\GridVariation\GridState;
use Bitrix\Catalog\Component\GridVariation\GridStateStorage;
use Bitrix\Catalog\Component\GridVariation\InitSkuCollectionFromParams;
use Bitrix\Catalog\Component\GridVariationForm;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\SkuCollection;
use Bitrix\Catalog\v2\Sku\SkuRepositoryContract;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\PageNavigation;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductVariationGridComponent
	extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;
	use InitSkuCollectionFromParams;

	public const HEADER_EMPTY_PROPERTY_COLUMN = 'EMPTY_PROPERTIES';
	private const STORE_AMOUNT_POPUP_LIMIT = 4;

	/** @var \Bitrix\Catalog\v2\Product\BaseProduct */
	private $product;
	/** @var \Bitrix\Catalog\Component\GridVariationForm */
	private $defaultForm;
	private CGridOptions $gridOptions;
	private PageNavigation $gridPagination;
	private SkuCollection $preparedSkuCollection;
	private ?SkuRepositoryContract $skuRepository;
	private GridState $state;
	/** @var array|null */
	private $skuStoreAmount;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function showErrors()
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [];
	}

	private function getSkuRepository(): ?SkuRepositoryContract
	{
		if (!isset($this->skuRepository))
		{
			$skuIblockId = $this->getProduct()->getIblockInfo()->getSkuIblockId();
			if (!isset($skuIblockId))
			{
				return null;
			}

			$this->skuRepository = ServiceContainer::getSkuRepository($skuIblockId);
		}

		return $this->skuRepository;
	}

	private function getGridState(): GridState
	{
		$this->state ??= (new GridStateStorage)->load(
			$this->getProductId(),
			(string)$this->getGridId()
		);

		return $this->state;
	}

	private function checkGridStateCurrentPage(): void
	{
		$state = $this->getGridState();

		if (isset($this->gridPagination))
		{
			$pagination = $this->loadGridNavObject();
			$pagination->setCurrentPage($state->getCurrentPage());
		}
		else
		{
			$pagination = $this->getGridNavObject();
		}

		$pageCount = $pagination->getPageCount();
		$page = $pagination->getCurrentPage();
		if ($page > $pageCount)
		{
			$pagination->setCurrentPage($pageCount);
			$state->setCurrentPage($pageCount);
			$state->save();
		}
	}

	/**
	 * SKU collection with order and pagination.
	 *
	 * @return SkuCollection
	 */
	private function getPreparedSkuCollection(): SkuCollection
	{
		if (isset($this->preparedSkuCollection))
		{
			return $this->preparedSkuCollection;
		}

		$repository = $this->getSkuRepository();
		if ($repository && !$this->isNewProduct())
		{
			$pagination = $this->getGridNavObject();

			$params = [
				'order' => $this->getGridOptionsSorting()['sort'],
				'nav' => [
					'nTopCount' => $pagination->getLimit(),
					'nOffset' => $pagination->getOffset(),
				],
			];

			$skus = $repository->getEntitiesByProduct($this->getProduct(), $params);
			$factory = ServiceContainer::getSkuFactory($this->getProduct()->getIblockId());
			if ($factory)
			{
				$this->preparedSkuCollection =
					$factory
						->createCollection()
						->setParent($this->getProduct())
						->add(... $skus)
				;
			}
		}

		$this->preparedSkuCollection ??= $this->getProduct()->loadSkuCollection();
		if ($this->preparedSkuCollection->isEmpty())
		{
			$this->preparedSkuCollection->create();
		}

		if (!empty($this->arParams['~ROWS']) && is_array($this->arParams['~ROWS']))
		{
			if ($this->isNewProduct())
			{
				$this->preparedSkuCollection->remove(
					... $this->preparedSkuCollection
				);
			}

			if (!empty($this->arParams['~ROWS']) && is_array($this->arParams['~ROWS']))
			{
				$this->initFieldsSkuCollectionItems(
					$this->preparedSkuCollection,
					$this->arParams['~ROWS'],
					false
				);
			}
		}

		return $this->preparedSkuCollection;
	}

	public function onPrepareComponentParams($params)
	{
		$this->product = $params['PRODUCT_ENTITY'] ?? null;

		$params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);
		$params['PRODUCT_ID'] = (int)($params['PRODUCT_ID'] ?? 0);

		$params['VARIATION_ID_LIST'] = $params['VARIATION_ID_LIST'] ?? null;

		$params['PATH_TO'] = $params['PATH_TO'] ?? [];

		$params['EXTERNAL_FIELDS'] = $params['EXTERNAL_FIELDS'] ?? [];
		if (!is_array($params['EXTERNAL_FIELDS']))
		{
			$params['EXTERNAL_FIELDS'] = [$params['EXTERNAL_FIELDS']];
		}

		return parent::onPrepareComponentParams($params);
	}

	protected function getProduct(): BaseProduct
	{
		if ($this->product === null && $this->arParams['IBLOCK_ID'] > 0)
		{
			if ($this->arParams['PRODUCT_ID'] > 0)
			{
				$productRepository = ServiceContainer::getProductRepository($this->arParams['IBLOCK_ID']);

				if ($productRepository)
				{
					$this->product = $productRepository->getEntityById($this->arParams['PRODUCT_ID']);
				}
			}
			else
			{
				$productFactory = ServiceContainer::getProductFactory($this->arParams['IBLOCK_ID']);

				if ($productFactory)
				{
					/** @var \Bitrix\Catalog\v2\Product\BaseProduct $product */
					$this->product = $productFactory
						->createEntity()
						->setActive(true)
					;

					$skuCollection = $this->product->getSkuCollection();

					$copyProductId = (int)($this->arParams['COPY_PRODUCT_ID'] ?? 0);
					if ($copyProductId > 0)
					{
						$productRepository = ServiceContainer::getProductRepository($this->arParams['IBLOCK_ID']);
						/** @var \Bitrix\Catalog\v2\Product\BaseProduct $copyProduct */
						$copyProduct = $productRepository->getEntityById($copyProductId);
						if ($copyProduct)
						{
							$copyItemMap = [];
							$copySkuCollection = $copyProduct->loadSkuCollection();
							foreach ($copySkuCollection as $copyItem)
							{
								$sku = $skuCollection->create();
								$copyItemMap[$sku->getHash()] = $copyItem->getId();
								$fields = $copyItem->getFields();
								unset(
									$fields['ID'], $fields['IBLOCK_ID'], $fields['PREVIEW_PICTURE'],
									$fields['DETAIL_PICTURE'], $fields['QUANTITY'], $fields['QUANTITY_RESERVED']
								);

								$sku->setFields($fields);

								$propertyValues = [];
								foreach ($copyItem->getPropertyCollection() as $property)
								{
									if ($property->getCode() === 'MORE_PHOTO')
									{
										continue;
									}
									$propertyValues[$property->getId()] = $property->getPropertyValueCollection()->toArray();
								}
								$sku->getPropertyCollection()->setValues($propertyValues);

								$sku->getPriceCollection()->setValues($copyItem->getPriceCollection()->getValues());

								$measureRatio = $copyItem->getMeasureRatioCollection()->findDefault();
								if ($measureRatio)
								{
									$sku->getMeasureRatioCollection()->setDefault($measureRatio->getRatio());
								}

								$sku->getImageCollection()->setValues($copyItem->getImageCollection()->toArray());
							}

							if (!empty($copyItemMap))
							{
								$this->arResult['COPY_ITEM_MAP'] = $copyItemMap;
							}
						}
					}

					if ($skuCollection->isEmpty())
					{
						$skuCollection->create();
					}

					$this->initializeExternalProductFields($this->product);
				}
			}
		}

		return $this->product;
	}

	public function getIblockId(): int
	{
		return $this->getProduct()->getIblockId();
	}

	public function getProductId(): int
	{
		return $this->getProduct()->getId() ?? 0;
	}

	public function isNewProduct(): bool
	{
		return $this->getProduct()->isNew();
	}

	public function isSimpleProduct(): bool
	{
		return $this->getProduct()->isSimple();
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "catalog" is not installed.');

			return false;
		}

		return true;
	}

	protected function checkPermissions(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}

	protected function checkProduct(): bool
	{
		if (!($this->getProduct() instanceof BaseProduct))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Product entity not found.');

			return false;
		}

		return true;
	}

	public function isAjaxGridAction(Request $request = null): bool
	{
		if ($request === null)
		{
			$request = $this->request;
		}

		return $request->isAjaxRequest() && $this->getGridId() === $request->get('grid_id');
	}

	public function doAjaxGridAction(Request $request)
	{
		if ($this->checkModules() && $this->checkPermissions() && $this->checkProduct())
		{
			$this->processGridActions($request);
		}
	}

	private function processGridActions(Request $request): void
	{
		$actionButton = 'action_button_'.$this->getGridId();
		$gridGroupAction = $request[$actionButton] ?? null;
		$gridItemAction = $request['action'] ?? null;
		$gridAction = $request['grid_action'] ?? null;

		if ($gridGroupAction && $gridGroupAction === 'delete')
		{
			$ids = $request['ID'] ?? [];
			$actionAllRows = 'action_all_rows_'.$this->getGridId();
			$allRows = ($request[$actionAllRows] ?? 'N') === 'Y';

			$this->processGridDelete($ids, $allRows);
			$this->checkGridStateCurrentPage();
		}
		elseif ($gridItemAction && $gridItemAction === 'deleteRow')
		{
			$id = $request['id'] ?? null;

			if (is_numeric($id))
			{
				$this->processGridDelete([$id]);
			}

			$this->checkGridStateCurrentPage();
		}
		elseif ($gridAction === 'pagination')
		{
			$pagination = $this->loadGridNavObject();
			$pagination->initFromUri();

			$state = $this->getGridState();
			$state->setCurrentPage($pagination->getCurrentPage());
			$state->save();
		}
		elseif ($gridAction === 'showpage')
		{
			$state = $this->getGridState();
			$state->reset();
			$state->save();
		}
	}

	private function processGridDelete(array $ids, bool $allRows = false): void
	{
		$product = $this->getProduct();
		if ($product->isSimple() || $product->isNew())
		{
			return;
		}

		/** @var \Bitrix\Catalog\v2\Sku\SkuCollection $skuCollection */
		$skuCollection = $product->loadSkuCollection();

		if ($allRows)
		{
			$gridVariants = $skuCollection;
		}
		else
		{
			/** @var \Bitrix\Catalog\v2\Sku\BaseSku[] $gridVariants */
			$gridVariants = [];

			foreach ($ids as $id)
			{
				if (!is_numeric($id))
				{
					continue;
				}

				$sku = $skuCollection->findById($id);

				if ($sku)
				{
					$gridVariants[] = $sku;
				}
			}
		}

		if (!empty($gridVariants))
		{
			$skuCollection->remove(...$gridVariants);

			// if ($skuCollection->count() === 1 && !$this->hasSkuProperties($skuCollection))
			// {
			// 	/** @var \Bitrix\Catalog\v2\Converter\ProductConverter $converter */
			// 	$converter = ServiceContainer::get(Dependency::PRODUCT_CONVERTER);
			// 	$converter->convert($product, $converter::SIMPLE_PRODUCT);
			// }

			$result = $product->save();

			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());
			}
		}
	}

	private function hasSkuProperties(\Bitrix\Catalog\v2\Sku\SkuCollection $skuCollection): bool
	{
		foreach ($skuCollection as $sku)
		{
			foreach ($sku->getPropertyCollection() as $property)
			{
				if ((int)$property->getId() === $sku->getIblockInfo()->getSkuPropertyId())
				{
					continue;
				}

				if (!$property->getPropertyValueCollection()->isEmpty())
				{
					return true;
				}
			}
		}

		return false;
	}

	public function executeComponent()
	{
		if ($this->checkModules() && $this->checkPermissions() && $this->checkProduct())
		{
			$this->initializeVariantsGrid();
			$this->includeComponentTemplate();
		}

		if ($this->hasErrors())
		{
			$this->showErrors();
		}
	}

	protected function initializeVariantsGrid()
	{
		$form = $this->getDefaultVariationRowForm();

		$this->arResult['CAN_HAVE_SKU'] = $this->canHaveSku() && !$this->isReadOnly();
		$this->arResult['IS_READ_ONLY'] = $this->isReadOnly();
		$this->arResult['PROPERTY_MODIFY_LINK'] = $this->getPropertyModifyLink();
		$this->arResult['PROPERTY_COPY_LINK'] = $this->getProductCopyLink();
		$this->arResult['GRID'] = $this->getGridData();
		$this->arResult['STORE_AMOUNT'] = $this->getStoreAmount();
		$this->arResult['IS_SHOWED_STORE_RESERVE'] = \Bitrix\Catalog\Config\State::isShowedStoreReserve();
		$this->arResult['RESERVED_DEALS_SLIDER_LINK'] = $this->getReservedDealsSliderLink();
		$this->arResult['SUPPORTED_AJAX_FIELDS'] = $form ? $form->getGridSupportedAjaxColumns() : [];
	}

	public function getGridId(): ?string
	{
		$form = $this->getDefaultVariationRowForm();

		if ($form)
		{
			return $form->getVariationGridId();
		}

		return null;
	}

	private function getDefaultVariationRowForm(): ?GridVariationForm
	{
		if ($this->defaultForm === null)
		{
			$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
			if ($productFactory)
			{
				$newProduct = $productFactory->createEntity();
				$emptyVariation = $newProduct->getSkuCollection()->create();
				$mode = $this->isNewProduct() ? GridVariationForm::CREATION_MODE : GridVariationForm::EDIT_MODE;
				$this->defaultForm = new GridVariationForm($emptyVariation, ['MODE' => $mode]);
				$this->defaultForm->loadGridHeaders();
			}
		}

		return $this->defaultForm;
	}

	protected function getPropertyModifyLink()
	{
		return str_replace(
			'#IBLOCK_ID#',
			$this->getDefaultVariationRowForm()->getVariationIblockId(),
			$this->arParams['PATH_TO']['PROPERTY_MODIFY']
		);
	}

	private function getIblockPropertiesDescriptions(): array
	{
		$form = $this->getDefaultVariationRowForm();
		if ($form)
		{
			return $form->getIblockPropertiesDescriptions();
		}

		return [];
	}

	private function getGridOptions(): CGridOptions
	{
		$this->gridOptions ??= new CGridOptions($this->getGridId());

		return $this->gridOptions;
	}

	public function getGridOptionsSorting(): array
	{
		return $this->getGridOptions()
			->getSorting([
				'sort' => ['NAME' => 'ASC'],
				'vars' => ['by' => 'by', 'order' => 'order'],
			])
		;
	}

	protected function getVariationLink(?int $skuId): ?string
	{
		if ($skuId !== null && $this->canHaveSku())
		{
			return str_replace(
				['#IBLOCK_ID#', '#PRODUCT_ID#', '#VARIATION_ID#'],
				[$this->getIblockId(), $this->getProductId(), $skuId],
				$this->arParams['PATH_TO']['VARIATION_DETAILS']
			);
		}

		return null;
	}

	protected function getProductCopyLink(): string
	{
		return str_replace(
			['#IBLOCK_ID#', '#COPY_PRODUCT_ID#'],
			[$this->getIblockId(), $this->getProductId()],
			$this->arParams['PATH_TO']['PRODUCT_COPY_DETAILS']
		);
	}

	protected function getGridRows(): array
	{
		$rows = [];
		$skuCollection = $this->getPreparedSkuCollection();
		$skuCount = $skuCollection->count();

		$rowMode = $this->isNewProduct() ? GridVariationForm::CREATION_MODE : GridVariationForm::EDIT_MODE;
		foreach ($skuCollection as $sku)
		{
			if ($this->arParams['VARIATION_ID_LIST'] && !in_array($sku->getId(), $this->arParams['VARIATION_ID_LIST'], true))
			{
				continue;
			}

			$skuRowForm = new GridVariationForm($sku, ['MODE' => $rowMode]);

			$item = $skuRowForm->getValues($sku->isNew());
			$columns = $skuRowForm->getColumnValues($sku->isNew());

			if (State::isUsedInventoryManagement())
			{
				$storeAmount = $this->getSkuStoreAmount();
				$quantity = 0;
				$reserveQuantity = 0;

				if (isset($storeAmount[$sku->getId()]))
				{
					foreach ($storeAmount[$sku->getId()]['stores'] as $storeInfo)
					{
						$quantity += $storeInfo['quantityAvailable'];
						$reserveQuantity += $storeInfo['quantityReserved'];
					}
				}

				$columns['SKU_GRID_QUANTITY'] = $this->getDomElementForPopupQuantity($quantity);
				$columns['SKU_GRID_QUANTITY_RESERVED'] = $this->getDomElementForReservedQuantity($reserveQuantity);
			}

			$item['SKU_GRID_BARCODE_VALUES'] = $item['SKU_GRID_BARCODE'] ?? [];
			$item['SKU_GRID_BARCODE'] = '<div data-role="barcode-selector"></div>';
			$actions = [];

			if (!$sku->isSimple() && !$sku->isNew())
			{
				$skuId = $sku->getId();
				$actions[] = [
					'iconclass' => 'view',
					'title' => Loc::getMessage('CATALOG_PRODUCT_CARD_GRID_MENU_OPEN_TITLE'),
					'text' => Loc::getMessage('CATALOG_PRODUCT_CARD_GRID_MENU_OPEN_TITLE'),
					'href' => $this->getVariationLink($skuId),
					'default' => false,
				];
				if (!$this->isReadOnly() && $skuCount > 1)
				{
					$actions[] = [
						'iconclass' => 'delete',
						'title' => Loc::getMessage('CATALOG_PRODUCT_CARD_GRID_MENU_DELETE_TITLE'),
						'text' => Loc::getMessage('CATALOG_PRODUCT_CARD_GRID_MENU_DELETE_TITLE'),
						'onclick' => "BX.Catalog.VariationGrid.Instance.removeRowFromGrid({$skuId});",
						'default' => false,
					];
				}
			}

			$rows[] = [
				'id' => $sku->getId() ?? $sku->getHash(),
				'data' => $item,
				'columns' => $columns,
				'actions' => $actions,
			];
		}

		return $rows;
	}

	private function getDomElementForPopupQuantity($quantity): string
	{
		$className = 'main-grid-cell-content-catalog-quantity-inventory-management';
		if ($quantity <= 0)
		{
			$className .= ' text--danger';
		}

		return "<a class=\"{$className}\">{$quantity}</a>";
	}

	private function getDomElementForReservedQuantity($quantity): string
	{
		return $this->isNewProduct() ? (string)$quantity : '<a class="main-grid-cell-content-catalog-reserved-quantity">' . $quantity . '</a>';
	}

	protected function getGridEditData(array $rows): array
	{
		$editData = [];

		$defaultForm = $this->getDefaultVariationRowForm();
		if ($defaultForm)
		{
			$editData['template_0'] = $defaultForm->getValues(false);
		}

		$isSimpleProduct = $this->getProduct()->isSimple();

		if ($isSimpleProduct)
		{
			$propertyPrefix = $defaultForm::preparePropertyName();
			$defaultSkuProperties = array_filter(
				$editData['template_0'],
				static function ($value, $name) use ($propertyPrefix)
				{
					return
						mb_strpos($name, $propertyPrefix) === 0
						&& mb_strpos($name, $propertyPrefix.'MORE_PHOTO') === false
					;
				},
				ARRAY_FILTER_USE_BOTH
			);
		}

		$productPropertiesKeys = null;

		foreach ($rows as $row)
		{
			$rowFields = $row['data'];

			if ($isSimpleProduct)
			{
				if ($productPropertiesKeys === null)
				{
					$productPropertiesKeys = array_filter(
						$row['data'],
						static function ($value, $name) use ($propertyPrefix)
						{
							return
								mb_strpos($name, $propertyPrefix) === 0
								&& mb_strpos($name, $propertyPrefix.'MORE_PHOTO') === false
							;
						},
						ARRAY_FILTER_USE_BOTH
					);
				}

				$rowFields = array_diff_key($rowFields, $productPropertiesKeys);
				$rowFields = array_merge($rowFields, $defaultSkuProperties);
			}

			$editData[$row['id']] = $rowFields;
		}

		return $editData;
	}

	public static function formatFieldName($name)
	{
		return BaseForm::GRID_FIELD_PREFIX.$name;
	}

	protected function getHiddenProperties()
	{
		$options = new \Bitrix\Main\Grid\Options($this->getGridId());
		$allUsedHeaders = $options->getUsedColumns();
		$properties = $this->getIblockPropertiesDescriptions();
		if (empty($properties))
		{
			return [];
		}

		$hiddenNames = [];
		foreach ($properties as $property)
		{
			if (in_array($property['name'], $allUsedHeaders, true))
			{
				continue;
			}

			$hiddenNames[] = [
				'NAME' => $property['name'],
				'TITLE' => $property['title'],
			];
		}

		return $hiddenNames;
	}

	private function getNavParamName(): string
	{
		return 'nav-'.$this->getGridId();
	}

	private function getTotalCountProductSkus(): int
	{
		$product = $this->getProduct();
		$repository = $this->getSkuRepository();

		if (isset($repository) && !$product->isNew())
		{
			return $repository->getCountByProductId($product->getId());
		}

		return 0;
	}

	private function loadGridNavObject(): PageNavigation
	{
		$totalCount = $this->getTotalCountProductSkus();
		$gridOptions = $this->getGridOptions();
		$pageSize = (int)$gridOptions->GetNavParams()['nPageSize'];

		$pagination = new PageNavigation($this->getNavParamName());
		$pagination->allowAllRecords(false);
		$pagination->setPageSize($pageSize);
		$pagination->setRecordCount($totalCount);

		return $pagination;
	}

	protected function getGridNavObject(): PageNavigation
	{
		if (!isset($this->gridPagination))
		{
			$this->gridPagination = $this->loadGridNavObject();
			$this->gridPagination->setCurrentPage(
				$this->getGridState()->getCurrentPage()
			);
		}

		return $this->gridPagination;
	}

	protected function getGridActionPanel()
	{
		$snippet = new Snippet();

		return [
			'GROUPS' => [
				[
					'ITEMS' => [
						$snippet->getRemoveButton(),
						$snippet->getForAllCheckbox(),
					],
				],
			],
		];
	}

	protected function getGridData()
	{
		$gridSorting = $this->getGridOptionsSorting();
		$rows = $this->getGridRows();
		$isReadOnly = $this->isReadOnly();
		$pagination = $this->getGridNavObject();
		$isShowPagination = $pagination->getPageCount() > 1;

		return [
			//'ID' => $this->getGridId(),
			'GRID_ID' => $this->getGridId(),
			'HEADERS' => $this->getDefaultVariationRowForm()->getGridHeaders(),
			'HIDDEN_PROPERTIES' => $this->getHiddenProperties(),
			'ROWS' => $rows,
			'SORT' => $gridSorting['sort'],
			'SORT_VARS' => $gridSorting['vars'],

			'NAV_OBJECT' => $pagination,
			'NAV_PARAM_NAME' => $pagination->getId(),
			'CURRENT_PAGE' => $pagination->getCurrentPage(),
			'TOTAL_ROWS_COUNT' => $pagination->getRecordCount(),

			'ACTION_PANEL' => $this->getGridActionPanel(),

			'EDIT_DATA' => $this->getGridEditData($rows),

			'SHOW_CHECK_ALL_CHECKBOXES' => !$isReadOnly,
			'SHOW_ROW_CHECKBOXES' => !$isReadOnly,
			'SHOW_ROW_ACTIONS_MENU' => true,
			'SHOW_GRID_SETTINGS_MENU' => $this->getDefaultVariationRowForm()->isCardSettingsEditable(),
			'SHOW_NAVIGATION_PANEL' => true,
			'SHOW_PAGINATION' => $isShowPagination,
			'SHOW_SELECTED_COUNTER' => !$isReadOnly,
			'SHOW_TOTAL_COUNTER' => true,
			'SHOW_PAGESIZE' => true,
			'SHOW_ACTION_PANEL' => !$this->getProduct()->isSimple() && !$isReadOnly,
			'ENABLE_FIELDS_SEARCH' => 'Y',
		];
	}

	protected function getCreateVariationLink()
	{
		return $this->getVariationLink(0);
	}

	private function canHaveSku()
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->getIblockId());

		return $iblockInfo && $iblockInfo->canHaveSku();
	}

	private function isReadOnly(): bool
	{
		return $this->getDefaultVariationRowForm()->isReadOnly();
	}

	protected function initializeExternalProductFields(BaseProduct $product): void
	{
		$fields = $this->arParams['EXTERNAL_FIELDS'] ?? [];

		if (empty($fields))
		{
			return;
		}

		$product->setFields($fields);

		foreach ($this->getPreparedSkuCollection() as $sku)
		{
			$sku->setFields($fields);

			if (isset($fields['PRICE']) || isset($fields['CURRENCY']))
			{
				$sku->getPriceCollection()->setValues([
					'BASE' => [
						'PRICE' => $fields['PRICE'] ?? null,
						'CURRENCY' => $fields['CURRENCY'] ?? null,
					],
				])
				;
				break;
			}
		}
	}

	private function getStoreAmount(): array
	{
		$skuStoreAmounts = $this->getSkuStoreAmount();
		foreach ($skuStoreAmounts as &$storeAmount)
		{
			if (isset($storeAmount['stores']))
			{
				$storeAmount['stores'] = array_slice($storeAmount['stores'], 0 ,static::STORE_AMOUNT_POPUP_LIMIT);
			}
		}

		return $skuStoreAmounts;
	}

	private function getSkuStoreAmount(): array
	{
		if ($this->skuStoreAmount !== null)
		{
			return $this->skuStoreAmount;
		}

		$this->skuStoreAmount = [];

		if ($this->getProduct()->isNew())
		{
			return [];
		}

		$skus = $this->getPreparedSkuCollection()->toArray();
		$skuIds = array_column($skus, 'ID');

		if (!$skuIds)
		{
			return [];
		}

		$filter = [
			'=PRODUCT_ID' => $skuIds,
			'=STORE.ACTIVE' => 'Y',
			[
				'LOGIC' => 'OR',
				'!=AMOUNT' => 0,
				'!=QUANTITY_RESERVED' => 0,
			]
		];

		$filter = array_merge(
			$filter,
			AccessController::getCurrent()->getEntityFilter(
				ActionDictionary::ACTION_STORE_VIEW,
				StoreProductTable::class
			)
		);

		$productStoreMap = [];
		$storeProductRaws = StoreProductTable::getList([
			'select' => ['*', 'STORE.TITLE'],
			'filter' => $filter,
			'order' => [
				'STORE.SORT' => 'ASC'
			],
		]);

		while ($productStore = $storeProductRaws->fetch())
		{
			$productStoreMap[$productStore['PRODUCT_ID']][] = $productStore;
		}

		foreach ($productStoreMap as $skuId => $productStoreSkuInfos)
		{
			if (!is_array($productStoreSkuInfos))
			{
				continue;
			}

			$formattedStoreInfos = [];

			$storeCount = count($productStoreSkuInfos);
			foreach ($productStoreSkuInfos as $storeProduct)
			{
				$storeProduct['AMOUNT'] = (float)$storeProduct['AMOUNT'];
				$storeProduct['QUANTITY_RESERVED'] = (float)$storeProduct['QUANTITY_RESERVED'];
				$storeTitle = $storeProduct['CATALOG_STORE_PRODUCT_STORE_TITLE']
					? HtmlFilter::encode($storeProduct['CATALOG_STORE_PRODUCT_STORE_TITLE'])
					: Loc::getMessage('CATALOG_PRODUCT_CARD_GRID_STORE_AMOUNT_POPUP_STORE_TITLE_WITHOUT_NAME')
				;
				$formattedStoreInfos[] = [
					'title' => $storeTitle,
					'storeId' => $storeProduct['STORE_ID'],
					'quantityCommon' => $storeProduct['AMOUNT'],
					'quantityReserved' => $storeProduct['QUANTITY_RESERVED'],
					'quantityAvailable' => $storeProduct['AMOUNT'] - $storeProduct['QUANTITY_RESERVED'],
				];
			}

			$this->skuStoreAmount[$skuId] = [
				'stores' => $formattedStoreInfos,
				'linkToDetails' =>
					$storeCount > self::STORE_AMOUNT_POPUP_LIMIT
						? $this->getLinkToVariationStoreAmountDetails($skuId)
						: null
				,
				'storesCount' => $storeCount,
			];
		}

		return $this->skuStoreAmount;
	}

	private function getLinkToVariationStoreAmountDetails(int $skuId): string
	{
		return str_replace(
			['#IBLOCK_ID#', '#PRODUCT_ID#', '#VARIATION_ID#'],
			[$this->getIblockId(), $this->getProductId(), $skuId],
			$this->arParams['PATH_TO']['PRODUCT_STORE_AMOUNT_DETAILS_SLIDER'],
		);
	}

	private function getReservedDealsSliderLink()
	{
		$sliderUrl = \CComponentEngine::makeComponentPath('bitrix:catalog.productcard.reserved.deal.list');
		$sliderUrl = getLocalPath('components'.$sliderUrl.'/slider.php');

		return $sliderUrl;
	}
}
