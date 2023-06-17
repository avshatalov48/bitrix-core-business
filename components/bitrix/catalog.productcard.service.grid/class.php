<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\BaseForm;
use Bitrix\Catalog\Component\GridServiceForm;
use Bitrix\Catalog\Component\GridVariation\InitSkuCollectionFromParams;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
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

class CatalogProductServiceGridComponent
	extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;
	use InitSkuCollectionFromParams;

	public const HEADER_EMPTY_PROPERTY_COLUMN = 'EMPTY_PROPERTIES';

	/** @var \Bitrix\Catalog\v2\Product\BaseProduct */
	private $product;
	/** @var null|GridServiceForm */
	private ?GridServiceForm $defaultForm = null;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	public function configureActions(): array
	{
		return [];
	}

	protected function listKeysSignedParameters(): array
	{
		return [];
	}

	public function onPrepareComponentParams($params): array
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
			$isLoadRowsFromParams = !empty($this->arParams['~ROWS']) && is_array($this->arParams['~ROWS']);

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

					if ($skuCollection->isEmpty() && !$isLoadRowsFromParams)
					{
						$skuCollection->create();
					}

					$this->initializeExternalProductFields($this->product);
				}
			}

			if ($isLoadRowsFromParams)
			{
				$this->initFieldsSkuCollectionItems(
					$this->product->getSkuCollection(),
					$this->arParams['~ROWS'],
					true
				);
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

		/*if ($gridGroupAction === 'delete')
		{
			$ids = $request['ID'] ?? [];
			$actionAllRows = 'action_all_rows_'.$this->getGridId();
			$allRows = ($request[$actionAllRows] ?? 'N') === 'Y';

			$this->processGridDelete($ids, $allRows);
		}
		elseif ($gridItemAction === 'deleteRow')
		{
			$id = $request['id'] ?? null;

			if (is_numeric($id))
			{
				$this->processGridDelete([$id]);
			}
		} */
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

		//$this->arResult['CAN_HAVE_SKU'] = $this->canHaveSku();
		$this->arResult['CAN_HAVE_SKU'] = false;
		$this->arResult['PROPERTY_MODIFY_LINK'] = $this->getPropertyModifyLink();
		$this->arResult['PROPERTY_COPY_LINK'] = $this->getProductCopyLink();
		$this->arResult['GRID'] = $this->getGridData();
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

	private function getDefaultVariationRowForm(): ?GridServiceForm
	{
		if ($this->defaultForm === null)
		{
			$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
			if ($productFactory)
			{
				$newProduct = $productFactory->createEntity();
				$newProduct->setType(ProductTable::TYPE_SERVICE);
				$emptyVariation = $newProduct->getSkuCollection()->create();
				$emptyVariation->setType(ProductTable::TYPE_SERVICE);
				$mode = $this->isNewProduct() ? BaseForm::CREATION_MODE : BaseForm::EDIT_MODE;
				$this->defaultForm = new GridServiceForm($emptyVariation, ['MODE' => $mode]);
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
		static $gridOptions = null;

		if ($gridOptions === null)
		{
			$gridOptions = new \CGridOptions($this->getGridId());
		}

		return $gridOptions;
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

		$rowMode = $this->isNewProduct() ? BaseForm::CREATION_MODE : BaseForm::EDIT_MODE;

		$product = $this->getProduct()->getSkuCollection()->getFirst();
		$skuRowForm = new GridServiceForm($product, ['MODE' => $rowMode]);
		$item = $skuRowForm->getValues($product->isNew());
		$columns = $skuRowForm->getColumnValues($product->isNew());

		$actions = [];

		$rows[] = [
			'id' => $product->getId() ?? $product->getHash(),
			'data' => $item,
			'columns' => $columns,
			'actions' => $actions,
		];

		return $rows;
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

	public static function formatFieldName($name): string
	{
		return BaseForm::GRID_FIELD_PREFIX . $name;
	}

	protected function getHiddenProperties(): array
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

	protected function getGridNavObject(): PageNavigation
	{
		return new PageNavigation('nav-'.$this->getGridId());
	}

	protected function getGridActionPanel(): array
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

	protected function getGridData(): array
	{
		$gridSorting = $this->getGridOptionsSorting();
		$rows = $this->getGridRows();
		$isReadOnly = $this->isReadOnly();

		return [
			'ID' => $this->getGridId(),
			'HEADERS' => $this->getDefaultVariationRowForm()->getGridHeaders(),
			'HIDDEN_PROPERTIES' => $this->getHiddenProperties(),
			'ROWS' => $rows,
			'SORT' => $gridSorting['sort'],
			'SORT_VARS' => $gridSorting['vars'],
			'NAV_OBJECT' => $this->getGridNavObject(),
			'ACTION_PANEL' => $this->getGridActionPanel(),

			'EDIT_DATA' => $this->getGridEditData($rows),

			'IS_READ_ONLY' => $isReadOnly,
			'SHOW_CHECK_ALL_CHECKBOXES' => false,
			'SHOW_ROW_CHECKBOXES' => !$isReadOnly,
			'SHOW_ROW_ACTIONS_MENU' => false,
			'SHOW_GRID_SETTINGS_MENU' => $this->getDefaultVariationRowForm()->isCardSettingsEditable(),
			'SHOW_NAVIGATION_PANEL' => true,
			'SHOW_PAGINATION' => false,
			'SHOW_SELECTED_COUNTER' => false,
			'SHOW_TOTAL_COUNTER' => false,
			'SHOW_PAGESIZE' => false,
			'SHOW_ACTION_PANEL' => false,
			'ENABLE_FIELDS_SEARCH' => 'Y',
		];
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

/*		if ($product->getSkuCollection()->isEmpty())
		{
			$product->getSkuCollection()->create();
		}

		foreach ($product->getSkuCollection() as $sku)
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
		} */
	}

	private function getReservedDealsSliderLink()
	{
		$sliderUrl = \CComponentEngine::makeComponentPath('bitrix:catalog.productcard.reserved.deal.list');

		return getLocalPath('components'.$sliderUrl.'/slider.php');
	}
}
