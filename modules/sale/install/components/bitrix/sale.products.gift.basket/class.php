<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main;
use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock\Component\ElementList;

Loc::loadMessages(__FILE__);

if (!Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('SPGB_IBLOCK_MODULE_NOT_INSTALLED'));

	return;
}

class SaleProductsGiftBasketComponent extends ElementList
{
	/** @var \Bitrix\Sale\Discount\Gift\Manager */
	protected $giftManager;
	/** @var  \Bitrix\Sale\Basket */
	private $basket;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(true)->setMultiIblockMode(true)->setPaginationMode(false);
	}

	public function onPrepareComponentParams($params)
	{
		if (empty($params['SHOW_DISCOUNT_PERCENT']))
		{
			$params['SHOW_DISCOUNT_PERCENT'] = 'Y';
		}

		if (empty($params['SHOW_OLD_PRICE']))
		{
			$params['SHOW_OLD_PRICE'] = 'Y';
		}

		$params = parent::onPrepareComponentParams($params);

		if (empty($params['FULL_DISCOUNT_LIST']))
		{
			$params['FULL_DISCOUNT_LIST'] = [];
		}

		if (empty($params['APPLIED_DISCOUNT_LIST']))
		{
			$params['APPLIED_DISCOUNT_LIST'] = [];
		}

		return $params;
	}

	private function buildIblockDependedParameters()
	{
		$iblockInfo = $this->prepareIblockInfo();

		if (!empty($iblockInfo))
		{
			foreach ($iblockInfo as $productIblockId => $offerIblockId)
			{
				$this->arParams["SHOW_PRODUCTS_{$productIblockId}"] = 'Y';
				$this->arParams["ADDITIONAL_PICT_PROP_{$productIblockId}"] = 'MORE_PHOTO';

				if (!empty($offerIblockId))
				{
					$properties = $this->getProperties($offerIblockId);
					$this->arParams["PROPERTY_CODE_{$offerIblockId}"] = $properties + ['MORE_PHOTO'];
					$this->arParams["CART_PROPERTIES_{$productIblockId}"] = $properties;
					$this->arParams["OFFER_TREE_PROPS_{$offerIblockId}"] = $properties;
					$this->arParams["ADDITIONAL_PICT_PROP_{$offerIblockId}"] = 'MORE_PHOTO';
				}
			}
		}

		//TODO: change price types selection to api
		$this->arParams['PRICE_CODE'] = [];
		$fullPriceTypeList = \CCatalogGroup::GetListArray();
		if (!empty($fullPriceTypeList))
		{
			$iterator = \Bitrix\Catalog\GroupAccessTable::getList([
				'select' => array('CATALOG_GROUP_ID'),
				'filter' => array('@GROUP_ID' => $this->getUserGroups(), '=ACCESS' => \Bitrix\Catalog\GroupAccessTable::ACCESS_BUY),
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['CATALOG_GROUP_ID'];
				if (!isset($fullPriceTypeList[$id]))
					continue;
				$this->arParams['PRICE_CODE'][$id] = $fullPriceTypeList[$id]['NAME'];
			}
			unset($id, $row, $iterator);
			if (!empty($this->arParams['PRICE_CODE']))
				$this->arParams['PRICE_CODE'] = array_values($this->arParams['PRICE_CODE']);
		}
		unset($fullPriceTypeList);

		$this->storage['IBLOCK_PARAMS'] = $this->getMultiIblockParams($this->arParams);
	}

	private function prepareIblockInfo()
	{
		$iblocks = [];

		$iblockIds = $this->fetchIblockIds();

		if (!empty($iblockIds))
		{
			foreach ($iblockIds as $iblockId)
			{
				$info = CCatalogSKU::getInfoByIBlock($iblockId);

				if (!empty($info))
				{
					$productIblockId = $offersIblockId = null;

					if ($info['CATALOG_TYPE'] === CCatalogSKU::TYPE_OFFERS)
					{
						$productIblockId = $info['PRODUCT_IBLOCK_ID'];
						$offersIblockId = $info['IBLOCK_ID'];
					}
					elseif ($info['CATALOG_TYPE'] === CCatalogSKU::TYPE_FULL)
					{
						$productIblockId = $info['PRODUCT_IBLOCK_ID'];
						$offersIblockId = $info['IBLOCK_ID'];
					}
					elseif ($info['CATALOG_TYPE'] === CCatalogSKU::TYPE_CATALOG)
					{
						$productIblockId = $info['IBLOCK_ID'];
						$offersIblockId = null;
					}

					if (!empty($productIblockId))
					{
						$iblocks[$productIblockId] = $offersIblockId;
					}
				}
			}
		}

		return $iblocks;
	}

	private function fetchIblockIds()
	{
		$ids = [];
		$productIds = [];

		/** @var \Bitrix\Sale\BasketItem $item */
		foreach ($this->getBasket() as $item)
		{
			if ($this->isExtendedCatalogProvider($item))
			{
				$productIds[] = $item->getProductId();
			}
		}

		if (!empty($productIds))
		{
			$elements = \Bitrix\Iblock\ElementTable::getList([
				'select' => ['IBLOCK_ID'],
				'filter' => ['@ID' => $productIds],
			]);
			foreach ($elements as $element)
			{
				if (!isset($ids[$element['IBLOCK_ID']]))
				{
					$ids[$element['IBLOCK_ID']] = true;
				}
			}
		}

		return array_keys($ids);
	}

	private function getBasket()
	{
		if ($this->basket === null)
		{
			$basketStorage = \Bitrix\Sale\Basket\Storage::getInstance(\Bitrix\Sale\Fuser::getId(), SITE_ID);
			$this->basket = $basketStorage->getBasket();
		}

		return $this->basket;
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	private function isExtendedCatalogProvider(\Bitrix\Sale\BasketItem $item)
	{
		$providerName = $item->getProvider();

		return
			$item->getField('MODULE') === 'catalog'
			&& $providerName
			&& (
				array_key_exists('Bitrix\Sale\SaleProviderBase', class_parents($providerName))
				|| array_key_exists('IBXSaleProductProvider', class_implements($providerName))
			);
	}

	private function getProperties($offersIblockId)
	{
		$properties = [];
		$offers = CCatalogSKU::getInfoByOfferIBlock($offersIblockId);

		if (empty($offers))
		{
			return [];
		}

		$propertyIterator = \Bitrix\Iblock\PropertyTable::getList([
			'select' => ['ID', 'CODE', 'PROPERTY_TYPE', 'USER_TYPE'],
			'filter' => [
				'=IBLOCK_ID' => $offers['IBLOCK_ID'],
				'=ACTIVE' => 'Y',
				'!=ID' => $offers['SKU_PROPERTY_ID'],
				'@PROPERTY_TYPE' => [
					\Bitrix\Iblock\PropertyTable::TYPE_STRING,
					\Bitrix\Iblock\PropertyTable::TYPE_LIST,
				],
				'=MULTIPLE' => 'N',
			],
			'order' => ['SORT' => 'ASC', 'NAME' => 'ASC'],
		]);
		while ($property = $propertyIterator->fetch())
		{
			$property['USER_TYPE'] = (string)$property['USER_TYPE'];
			if ($property['PROPERTY_TYPE'] == \Bitrix\Iblock\PropertyTable::TYPE_STRING && $property['USER_TYPE'] != 'directory')
			{
				continue;
			}

			$propertyCode = (string)$property['CODE'];
			if ($propertyCode == '')
			{
				$propertyCode = $property['ID'];
			}

			$properties[] = $propertyCode;
		}

		return $properties;
	}

	protected function checkModules()
	{
		if ($success = parent::checkModules())
		{
			if (!$this->useCatalog || !Main\Loader::includeModule('sale'))
			{
				$success = false;
				$this->abortResultCache();

				if (!$this->useCatalog)
				{
					$this->errorCollection->setError(new Error(Loc::getMessage('SPGB_CATALOG_MODULE_NOT_INSTALLED'), self::ERROR_TEXT));
				}

				if (!Main\Loader::includeModule('sale'))
				{
					$this->errorCollection->setError(new Error(Loc::getMessage('SPGB_SALE_MODULE_NOT_INSTALLED'), self::ERROR_TEXT));
				}
			}
		}

		if ($success)
		{
			$this->buildIblockDependedParameters();
			$this->initGiftManager();
		}

		return $success;
	}

	/**
	 * Initializes gift manager.
	 * @return void
	 */
	protected function initGiftManager()
	{
		$userId = Main\Engine\CurrentUser::get()->getId();
		$this->giftManager = \Bitrix\Sale\Discount\Gift\Manager::getInstance()->setUserId($userId);
	}

	/**
	 * Returns gift collections for current basket.
	 * @return array
	 */
	protected function getGiftCollections()
	{
		$collections = [];

		if ($this->request->isAjaxRequest() && $this->request->get('recalculateDiscounts') === 'Y')
		{
			$collections = $this->giftManager->getCollectionsByBasket($this->getBasket());
		}
		elseif (!empty($this->arParams['FULL_DISCOUNT_LIST']))
		{
			$collections = $this->giftManager->getCollectionsByBasket(
				$this->getBasket(),
				$this->arParams['FULL_DISCOUNT_LIST'],
				$this->arParams['APPLIED_DISCOUNT_LIST']
			);
		}

		return $collections;
	}

	/**
	 * Returns list of product ids which will be showed on first hit.
	 * @return array
	 */
	protected function getProductIds()
	{
		return [];
	}

	/**
	 * Returns list of product ids which will be showed via ajax.
	 * @return array
	 */
	protected function getDeferredProductIds()
	{
		\Bitrix\Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
		$collections = $this->getGiftCollections();
		\Bitrix\Sale\Compatible\DiscountCompatibility::revertUsageCompatible();

		$productIds = [];

		foreach ($collections as $collection)
		{
			/** @var \Bitrix\Sale\Discount\Gift\Gift $gift */
			foreach ($collection as $gift)
			{
				$productIds[] = $gift->getProductId();
			}
		}

		return $productIds;
	}

	protected function processProducts()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::processProducts();

		foreach ($this->elementLinks as &$element)
		{
			if (!empty($element['ITEM_PRICES']))
			{
				$this->setGiftDiscountToMinPrice($element);
			}
		}
		unset($element);

		if ($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	/**
	 * Add offers for each catalog product.
	 * @return void
	 */
	protected function processOffers()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::processOffers();

		foreach ($this->elementLinks as &$item)
		{
			if (!isset($item['OFFERS']))
			{
				continue;
			}

			foreach ($item['OFFERS'] as &$offer)
			{
				if (!empty($offer['ITEM_PRICES']))
				{
					$this->setGiftDiscountToMinPrice($offer);
				}
			}
			unset($offer);
		}
		unset($item);

		if ($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	/**
	 * @param array $item
	 */
	protected function setGiftDiscountToMinPrice(array &$item)
	{
		$selectedPrice =& $item['ITEM_PRICES'][$item['ITEM_PRICE_SELECTED']];

		$selectedPrice['PRICE'] = $selectedPrice['DISCOUNT'];
		$selectedPrice['PRINT_PRICE'] = $selectedPrice['PRINT_DISCOUNT'];
		$selectedPrice['DISCOUNT'] = $selectedPrice['BASE_PRICE'];
		$selectedPrice['PRINT_DISCOUNT'] = $selectedPrice['PRINT_BASE_PRICE'];
		$selectedPrice['RATIO_PRICE'] = $selectedPrice['RATIO_DISCOUNT'];
		$selectedPrice['PRINT_RATIO_PRICE'] = $selectedPrice['PRINT_RATIO_DISCOUNT'];
		$selectedPrice['RATIO_DISCOUNT'] = $selectedPrice['RATIO_BASE_PRICE'];
		$selectedPrice['PRINT_RATIO_DISCOUNT'] = $selectedPrice['PRINT_RATIO_BASE_PRICE'];
		$selectedPrice['PERCENT'] = 100;
	}
}