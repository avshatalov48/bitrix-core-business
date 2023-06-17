<?php

use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Iblock\Url\AdminPage\BaseBuilder;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogGridProductFieldComponent
	extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;

	/** @var BaseProduct $product */
	private $product;
	/** @var BaseSku $sku */
	private $sku;

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
		return [
			'FILE_TYPE',
			'PRODUCT_FIELDS',
			'USE_SKU_TREE',
		];
	}

	public function onPrepareComponentParams($params): array
	{
		$params['ROW_ID_MASK'] = $params['ROW_ID_MASK'] ?? '#ID#';
		$params['PRODUCT_FIELDS'] = $params['PRODUCT_FIELDS'] ?? [];

		if (!isset($params['USE_SKU_TREE']))
		{
			$params['USE_SKU_TREE'] = isset($params['SKU_TREE']) && is_array($params['SKU_TREE']);
		}

		$params['USE_SKU_TREE'] = $params['USE_SKU_TREE'] === true;

		if (!isset($params['IMAGES']))
		{
			$params['IMAGES'] = [];
		}

		$params['BUILDER_CONTEXT'] = (string)($params['BUILDER_CONTEXT'] ?? '') ?: BaseBuilder::TYPE_AUTODETECT;
		$params['IS_NEW'] = ($params['IS_NEW'] ?? 'N') === 'Y';

		$params['MODE'] = $params['MODE'] ?? '';
		$params['VIEW_FORMAT'] =
			isset($params['VIEW_FORMAT']) && $params['VIEW_FORMAT'] === 'short'
				? 'short'
				: 'full'
		;

		return parent::onPrepareComponentParams($params);
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

	protected function checkRequiredParameters(): bool
	{
		if (empty($this->arParams['PRODUCT_FIELDS']))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Product fields must be specified.');

			return false;
		}

		return true;
	}

	protected function getProductField($name)
	{
		return $this->arParams['~PRODUCT_FIELDS'][$name] ?? null;
	}

	protected function getIblockId(): int
	{
		return (int)$this->getProductField('IBLOCK_ID') ?: 0;
	}

	protected function getProductId(): ?int
	{
		return (int)$this->getProductField('ID') ?: null;
	}

	protected function getProductName(): string
	{
		return (string)$this->getProductField('NAME');
	}

	protected function getBasePriceId(): ?int
	{
		return (int)$this->getProductField('BASE_PRICE_ID') ?: 0;
	}

	protected function getSkuId(): ?int
	{
		return (int)$this->getProductField('SKU_ID') ?: null;
	}

	protected function getBuilderContext(): string
	{
		return $this->arParams['BUILDER_CONTEXT'];
	}

	private function getProduct(): ?BaseProduct
	{
		if ($this->getProductId() > 0)
		{
			$productRepository = ServiceContainer::getProductRepository($this->getIblockId());
			if (!$productRepository)
			{
				$this->errorCollection[] = new \Bitrix\Main\Error('Empty product repository.');

				return null;
			}

			return $productRepository->getEntityById($this->getProductId());
		}

		$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
		if (!$productFactory)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Empty product factory.');

			return null;
		}

		return $productFactory->createEntity();
	}

	public function executeComponent()
	{
		if ($this->checkModules() && $this->checkRequiredParameters())
		{
			$this->arResult['PRODUCT_CONFIG'] = $this->getConfig();
			$this->arResult['SKU_ID'] = 0;
			$this->arResult['PRODUCT_FIELDS'] = [
				'PRODUCT_ID' => $this->getProductId(),
				'SKU_ID' => $this->getSkuId(),
				'NAME' => $this->getProductName(),
			];
			$this->arResult['FILE_TYPE'] = 'product';
			$this->arResult['SKU_TREE'] = null;

			if ($this->getIblockId() > 0 && $this->getProductId() > 0)
			{
				$this->product = $this->getProduct();
				if ($this->product)
				{
					if ($this->getSkuId() === null)
					{
						$this->sku = $this->product->getSkuCollection()->getFirst();
					}
					else
					{
						$skuRepository = ServiceContainer::getSkuRepository($this->product->getIblockId());
						if ($skuRepository)
						{
							try
							{
								$this->sku = $skuRepository->getEntityById($this->getSkuId());
							}
							catch (\Bitrix\Main\SystemException $e)
							{}

							if ($this->sku)
							{
								$this->arResult['SKU_ID'] = $this->sku->getId();
							}
						}
					}

					$this->arResult['SKU_TREE'] = $this->loadSkuTree();

					if ($this->sku)
					{
						if ($this->arResult['PRODUCT_FIELDS']['NAME'] === '')
						{
							$this->arResult['PRODUCT_FIELDS']['NAME'] = $this->sku->getName();
						}

						$variationImageField = new ImageInput($this->sku);
						if (!$variationImageField->isEmpty())
						{
							$imageField = $variationImageField->getFormattedField();
							$this->arResult['FILE_TYPE'] = 'sku';
						}
					}
				}
			}

			if (empty($imageField))
			{
				$productImageField = new ImageInput($this->product);
				$productFormattedField = $productImageField->getFormattedField();
				if ($this->product instanceof BaseProduct)
				{
					$imageField = $productFormattedField;
				}
				else
				{
					$imageField = [
						'emptyInput' => $productFormattedField['emptyInput']
					];
				}
			}

			$this->arResult['IBLOCK_ID'] = $this->getIblockId();
			$this->arResult['PRODUCT_CONFIG'] = $this->getConfig();
			$this->arResult['BASE_PRICE_ID'] = $this->getBasePriceId();
			$this->arResult['FILE_PREVIEW'] = $imageField['preview'] ?? '';
			$this->arResult['IMAGE_HTML'] = $imageField['input'] ?? '';
			$this->arResult['IMAGE_EMPTY_HTML'] = $imageField['emptyInput'] ?? '';
			$this->arResult['IMAGE_INPUT_ID'] = $imageField['id'] ?? '';
			$this->arResult['IMAGE_VALUES'] = $imageField['values'] ?? [];
			$this->arResult['MODE'] = ($this->arParams['MODE'] === 'edit') ? 'edit' : 'view';
			$this->arResult['GUID'] = $this->arParams['GUID'] ?? "catalog_product_field_{$this->arParams['ROW_ID']}";

			$this->includeComponentTemplate();
		}

		if ($this->hasErrors())
		{
			$this->showErrors();
		}
	}

	private function loadSkuTree(): array
	{
		if (!empty($this->arParams['~SKU_TREE']) || !empty($this->arParams['SKU_TREE']))
		{
			$paramSkuValue =
				!empty($this->arParams['~SKU_TREE'])
					? $this->arParams['~SKU_TREE']
					: $this->arParams['SKU_TREE']
			;

			if (is_array($paramSkuValue))
			{
				return $paramSkuValue;
			}

			$decodedValue = Json::decode($paramSkuValue);
			if (is_array($decodedValue))
			{
				return $decodedValue;
			}
		}

		if (!$this->arParams['USE_SKU_TREE'])
		{
			return [];
		}

		if (!$this->sku || $this->sku->isSimple())
		{
			return [];
		}

		/** @var \Bitrix\Catalog\Component\SkuTree $skuTree */
		$skuTree = ServiceContainer::make('sku.tree', [
			'iblockId' => $this->product->getIblockId(),
		]);
		if (!$skuTree)
		{
			return [];
		}

		$productId = $this->product->getId();
		$skuId = $this->sku->getId();

		$offers = $skuTree->loadJsonOffers([
			$productId => $skuId,
		]);

		return $offers[$productId][$skuId] ?? [];
	}

	private function getConfig(): array
	{
		$detailPath = null;

		if ($this->getIblockId() && $this->getProductId())
		{
			$urlBuilder = \Bitrix\Iblock\Url\AdminPage\BuilderManager::getInstance()
				->getBuilder($this->getBuilderContext())
			;
			if ($urlBuilder)
			{
				$urlBuilder->setIblockId($this->getIblockId());
				$detailPath = $urlBuilder->getElementDetailUrl($this->getProductId());
			}
		}

		return [
			'DETAIL_PATH' => $detailPath,
			'ROW_ID' => $this->arParams['ROW_ID'] ?? '',
			'ENABLE_SEARCH' => $this->arParams['ENABLE_SEARCH'] ?? false,
			'ENABLE_IMAGE_CHANGE_SAVING' => $this->arParams['ENABLE_IMAGE_CHANGE_SAVING'] ?? false,
			'ENABLE_INPUT_DETAIL_LINK' => $this->arParams['ENABLE_INPUT_DETAIL_LINK'] ?? false,
			'ENABLE_EMPTY_PRODUCT_ERROR' => $this->arParams['ENABLE_EMPTY_PRODUCT_ERROR'] ?? false,
			'ENABLE_SKU_SELECTION' => $this->arParams['ENABLE_SKU_SELECTION'] ?? true,
			'HIDE_UNSELECTED_ITEMS' => $this->arParams['HIDE_UNSELECTED_ITEMS'] ?? false,
			'URL_BUILDER_CONTEXT' => $this->getBuilderContext(),
			'GRID_ID' => $this->arParams['GRID_ID'] ?? '',
			'ENABLE_IMAGE_INPUT' => $this->arParams['ENABLE_IMAGE_INPUT'] ?? true,
			'ENABLE_CHANGES_RENDERING' => $this->arParams['ENABLE_CHANGES_RENDERING'] ?? true,
			'MODEL_CONFIG' => ['isNew' => $this->arParams['IS_NEW']],
			'VIEW_FORMAT' => $this->arParams['VIEW_FORMAT'],
		];
	}
}
