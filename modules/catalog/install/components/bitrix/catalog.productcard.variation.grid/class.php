<?php

use Bitrix\Catalog\Component\BaseForm;
use Bitrix\Catalog\Component\GridVariationForm;
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

	public const HEADER_EMPTY_PROPERTY_COLUMN = 'EMPTY_PROPERTIES';

	/** @var \Bitrix\Catalog\v2\Product\BaseProduct */
	private $product;
	/** @var \Bitrix\Catalog\Component\GridVariationForm */
	private $defaultForm;

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

	public function onPrepareComponentParams($params)
	{
		$this->product = $params['PRODUCT_ENTITY'] ?? null;

		$params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);
		$params['PRODUCT_ID'] = (int)($params['PRODUCT_ID'] ?? 0);

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
							$copySkuCollection = $copyProduct->getSkuCollection();
							foreach ($copySkuCollection as $copyItem)
							{
								$sku = $skuCollection->create();
								$fields = $copyItem->getFields();
								unset($fields['ID'], $fields['IBLOCK_ID'], $fields['PREVIEW_PICTURE'], $fields['DETAIL_PICTURE']);
								$sku->setFields($fields);

								$propertyValues = [];
								foreach ($copyItem->getPropertyCollection() as $property)
								{
									if ($property->isFileType())
									{
										$propertyValues[$property->getId()] = [];
									}
									else
									{
										$propertyValues[$property->getId()] = $property->getPropertyValueCollection()->toArray();
									}
								}
								$sku->getPropertyCollection()->setValues($propertyValues);

								$sku->getPriceCollection()->setValues($copyItem->getPriceCollection()->getValues());

								$measureRatio = $copyItem->getMeasureRatioCollection()->findDefault();
								if ($measureRatio)
								{
									$sku->getMeasureRatioCollection()->setDefault($measureRatio->getRatio());
								}
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
		if (!\Bitrix\Catalog\Config\State::isProductCardSliderEnabled())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('New product card feature disabled.');

			return false;
		}

		return true;
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

		if ($gridGroupAction && $gridGroupAction === 'delete')
		{
			$ids = $request['ID'] ?? [];
			$actionAllRows = 'action_all_rows_'.$this->getGridId();
			$allRows = ($request[$actionAllRows] ?? 'N') === 'Y';

			$this->processGridDelete($ids, $allRows);
		}
		elseif ($gridItemAction && $gridItemAction === 'deleteRow')
		{
			$id = $request['id'] ?? null;

			if (is_numeric($id))
			{
				$this->processGridDelete([$id]);
			}
		}
	}

	private function processGridDelete(array $ids, bool $allRows = false): void
	{
		$product = $this->getProduct();
		/** @var \Bitrix\Catalog\v2\Sku\SkuCollection $skuCollection */
		$skuCollection = $product->getSkuCollection();

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
		$this->arResult['CAN_HAVE_SKU'] = $this->canHaveSku();
		$this->arResult['PROPERTY_MODIFY_LINK'] = $this->getPropertyModifyLink();
		$this->arResult['GRID'] = $this->getGridData();
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
				$this->defaultForm = new GridVariationForm($emptyVariation);
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

	protected function getGridRows(): array
	{
		$rows = [];

		foreach ($this->getProduct()->getSkuCollection() as $sku)
		{
			$skuRowForm = new GridVariationForm($sku);

			$item = $skuRowForm->getValues($sku->isNew());
			$columns = $skuRowForm->getColumnValues($sku->isNew());

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
				$actions[] = [
					'iconclass' => 'delete',
					'title' => Loc::getMessage('CATALOG_PRODUCT_CARD_GRID_MENU_DELETE_TITLE'),
					'text' => Loc::getMessage('CATALOG_PRODUCT_CARD_GRID_MENU_DELETE_TITLE'),
					'onclick' => "BX.Catalog.VariationGrid.Instance.removeRowFromGrid({$skuId});",
					'default' => false,
				];
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
				static function ($value, $name) use ($propertyPrefix) {
					return mb_strpos($name, $propertyPrefix) === 0 && mb_strpos($name, $propertyPrefix.'MORE_PHOTO') === false;
				}, ARRAY_FILTER_USE_BOTH
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
						static function ($value, $name) use ($propertyPrefix) {
							return mb_strpos($name, $propertyPrefix) === 0 && mb_strpos($name, $propertyPrefix.'MORE_PHOTO') === false;
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

	protected function getGridNavObject()
	{
		return new PageNavigation('nav-'.$this->getGridId());
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

			'SHOW_CHECK_ALL_CHECKBOXES' => true,
			'SHOW_ROW_CHECKBOXES' => true,
			'SHOW_ROW_ACTIONS_MENU' => true,
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_NAVIGATION_PANEL' => true,
			'SHOW_PAGINATION' => true,
			'SHOW_SELECTED_COUNTER' => true,
			'SHOW_TOTAL_COUNTER' => true,
			'SHOW_PAGESIZE' => true,
			'SHOW_ACTION_PANEL' => true,
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

	protected function initializeExternalProductFields(BaseProduct $product): void
	{
		$fields = $this->arParams['EXTERNAL_FIELDS'] ?? [];

		if (empty($fields))
		{
			return;
		}

		$product->setFields($fields);

		if ($product->getSkuCollection()->isEmpty())
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
		}
	}
}