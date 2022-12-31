<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\SkuTree;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\v2\Product\Product;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\ErrorCollection;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductStoreAmountDetailsSliderComponent extends \CBitrixComponent implements  Controllerable, Errorable
{
	use ErrorableImplementation;

	private const EMPTY_IMAGE_SOURCE = '/bitrix/js/catalog/entity-selector/src/images/product.svg';

	private $measures;
	private $productSkuTree;
	private $stores;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	public function executeComponent()
	{
		if ($this->checkComponentData())
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

	private function checkComponentData(): bool
	{
		return $this->checkModules() && $this->checkPermissions() && $this->checkProduct() && $this->checkSku();
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
		return
			AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
			&& AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_VIEW)
		;
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

	private function checkSku(): bool
	{
		if (!$this->getSku() instanceof BaseSku)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(
				'SKU_ENTITY parameter is not instance of Bitrix\Catalog\v2\Sku\BaseSku'
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

	private function getSku(): ?BaseSku
	{
		if (!isset($this->sku))
		{
			$repositoryFacade = ServiceContainer::getRepositoryFacade();
			$variation = $repositoryFacade->loadVariation($this->getSkuId());
			$this->sku = $variation ?: null;
		}

		return $this->sku;
	}

	private function placePageTitle(): void
	{
		global $APPLICATION;
		$APPLICATION->setTitle(Loc::getMessage('STORE_LIST_DETAILS_SLIDER_TITLE1'));
	}

	private function getIblockId(): ?int
	{
		return $this->arParams['IBLOCK_ID'];
	}

	private function getProductId(): ?int
	{
		return $this->arParams['PRODUCT_ID'];
	}

	private function getSkuId(): ?int
	{
		return $this->arParams['VARIATION_ID'];
	}

	private function fillResult(): void
	{
		$this->arResult['STORE_AMOUNT_DATA'] = $this->getStoreAmountData();
		$this->arResult['IS_SHOWED_STORE_RESERVE'] = \Bitrix\Catalog\Config\State::isShowedStoreReserve();
	}

	private function getStoreAmountData(): array
	{
		return [
			'SKU_DATA' => $this->getSkuData($this->getSku()),
			'STORES_DATA' => $this->getStoresData($this->getSku()),
		];
	}

	private function getSkuData(BaseSku $sku): array
	{
		return [
			'NAME' => HtmlFilter::encode($sku->getField('NAME')),
			'IMAGE' => $this->getImageSource($sku),
			'PRICE' => $this->getPriceTextValue($sku),
			'PROPERTIES' => HtmlFilter::encode($this->getSkuPropertiesTextValue($sku)),
			'LINK' => $this->getLinkToDetailsCard($sku),
		];
	}

	private function getImageSource(BaseSku $sku): string
	{
		$frontImage = $sku->getImageCollection()->getFrontImage();
		if (!$frontImage)
		{
			return self::EMPTY_IMAGE_SOURCE;
		}

		return $frontImage->getSource();
	}

	private function getPriceTextValue(BaseSku $sku): ?string
	{
		$basePrice = $sku->getPriceCollection()->findBasePrice();
		if (!$basePrice)
		{
			return null;
		}

		$price = $basePrice->getPrice();
		$currency = $basePrice->getCurrency();

		return CCurrencyLang::CurrencyFormat($price, $currency);
	}

	private function getSkuPropertiesTextValue(BaseSku $sku): ?string
	{
		if ($sku->isSimple())
		{
			return null;
		}

		$skuId = $sku->getId();
		$skuTree = $this->getProductSkuTree()[$skuId];
		$selectedValues = $skuTree['SELECTED_VALUES'];
		$offersProp = $skuTree['OFFERS_PROP'];

		$skuProperties = [];
		foreach ($offersProp as $property)
		{
			$selectedValueId = $selectedValues[$property['ID']];
			if ($selectedValueId === 0)
			{
				continue;
			}

			$filteredValues = array_filter(
				$property['VALUES'],
				static function($valuesElement) use($selectedValueId) {
					return $valuesElement['ID'] === $selectedValueId;
				}
			);
			$skuProperties[] = $filteredValues[array_key_first($filteredValues)]['NAME'];
		}

		return implode(', ', $skuProperties);
	}

	private function getProductSkuTree(): ?array
	{
		if (!$this->productSkuTree)
		{
			/** @var SkuTree $skuTreeComponent */
			$skuTreeComponent = ServiceContainer::make('sku.tree', ['iblockId' => $this->getIblockId()]);
			if (!$skuTreeComponent)
			{
				return null;
			}

			$skuIds = array_column($this->getProduct()->getSkuCollection()->toArray(), 'ID');
			$productsSkuTree = $skuTreeComponent->loadWithSelectedOffers(
				[$this->getProductId() => $skuIds]
			);
			$this->productSkuTree = $productsSkuTree[$this->getProductId()];
		}

		return $this->productSkuTree;
	}

	private function getLinkToDetailsCard($sku): string
	{
		if ($sku->isSimple())
		{
			return str_replace(
				['#IBLOCK_ID#', '#PRODUCT_ID#'],
				[$this->getIblockId(), $this->getProductId()],
				$this->arParams['PATH_TO']['PRODUCT_DETAILS'],
			);
		}

		return str_replace(
			['#IBLOCK_ID#', '#PRODUCT_ID#', '#VARIATION_ID#'],
			[$this->getIblockId(), $this->getProductId(), $sku->getId()],
			$this->arParams['PATH_TO']['VARIATION_DETAILS'],
		);
	}

	private function getStoresData(BaseSku $sku): array
	{
		$resultStoreData = [];
		$storesData = StoreProductTable::getList([
			'select' => ['*', 'STORE.TITLE'],
			'filter' => [
				'=PRODUCT_ID' => $sku->getId(),
				'=STORE.ACTIVE' => 'Y',
				[
					'LOGIC' => 'OR',
					'!=AMOUNT' => '0',
					'!=QUANTITY_RESERVED' => '0',
				],
			],
			'order' => [
				'STORE.SORT' => 'ASC'
			],
		])->fetchAll();

		foreach ($storesData as $storeData)
		{
			$storeData['AMOUNT'] = (float)$storeData['AMOUNT'];
			$storeData['QUANTITY_RESERVED'] = (float)$storeData['QUANTITY_RESERVED'];
			$storeTitle = $storeData['CATALOG_STORE_PRODUCT_STORE_TITLE']
				? HtmlFilter::encode($storeData['CATALOG_STORE_PRODUCT_STORE_TITLE'])
				: Loc::getMessage('STORE_LIST_DETAILS_SLIDER_STORE_TITLE_WITHOUT_NAME')
			;
			$resultStoreData[] = [
				'NAME' => $storeTitle,
				'QUANTITY_COMMON' => $this->getQuantityTextValue($sku, $storeData['AMOUNT']),
				'QUANTITY_RESERVED' => $this->getQuantityTextValue($sku, $storeData['QUANTITY_RESERVED']),
				'QUANTITY_AVAILABLE' => $this->getQuantityTextValue(
					$sku,
					$storeData['AMOUNT'] - $storeData['QUANTITY_RESERVED']
				),
			];
		}

		return $resultStoreData;
	}

	private function getQuantityTextValue(BaseSku $sku, $quantity): ?string
	{
		$measure = $this->getMeasure($sku->getField('MEASURE'));

		return Loc::getMessage(
			'STORE_AMOUNT_DETAILS_SLIDER_QUANTITY_MEASURE',
			['#QUANTITY#' => $quantity, '#MEASURE#' => $measure]
		);
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
				$this->defaultMeasure = $fetchedMeasure['SYMBOL'];
			}
			else
			{
				$this->defaultMeasure = '';
			}
		}

		return $this->defaultMeasure;
	}

	public function configureActions()
	{
		return [];
	}
}
