<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.viewed.products");

class CSaleGiftBasketComponent extends CCatalogViewedProductsComponent
{
	/** @var \Bitrix\Sale\Discount\Gift\Manager */
	protected $giftManager;
	/** @var  \Bitrix\Sale\Basket */
	private $basket;
	/** @var array */
	private $productIds;

	/**
	 * Checks required modules.
	 * @throws Exception
	 * @return void
	 */
	protected function checkModules()
	{
		parent::checkModules();
		if(!$this->isSale)
		{
			throw new SystemException(Loc::getMessage("CVP_SALE_MODULE_NOT_INSTALLED"));
		}

		$this->initGiftManager();
		$this->buildIblockDependedParameters();
	}

	/**
	 * Initializes gift manager.
	 * @return void
	 */
	protected function initGiftManager()
	{
		global $USER;
		$userId = $USER instanceof CAllUser? $USER->getId() : null;
		$this->giftManager = \Bitrix\Sale\Discount\Gift\Manager::getInstance()->setUserId($userId);
	}

	/**
	 * Event called from includeComponent before component execution.
	 *
	 * <p>Takes component parameters as argument and should return it formatted as needed.</p>
	 * @param array[string]mixed $arParams
	 * @return array[string]mixed
	 *
	 */
	public function onPrepareComponentParams($params)
	{
		if(empty($params['SHOW_DISCOUNT_PERCENT']))
		{
			$params['SHOW_DISCOUNT_PERCENT'] = 'Y';
		}
		if(empty($params['SHOW_OLD_PRICE']))
		{
			$params['SHOW_OLD_PRICE'] = 'Y';
		}

		$params = parent::onPrepareComponentParams($params);
		if(empty($params["FULL_DISCOUNT_LIST"]))
		{
			$params["FULL_DISCOUNT_LIST"] = array();
		}
		if(empty($params["APPLIED_DISCOUNT_LIST"]))
		{
			$params["APPLIED_DISCOUNT_LIST"] = array();
		}

		return $params;
	}

	private function getBasket()
	{
		if($this->basket === null)
		{
			$basketStorage = \Bitrix\Sale\Basket\Storage::getInstance(\Bitrix\Sale\Fuser::getId(), SITE_ID);
			$this->basket = $basketStorage->getBasket();
		}

		return $this->basket;
	}

	private function fetchFirstIblockId()
	{
		/** @var \Bitrix\Sale\BasketItem $item */
		foreach($this->getBasket() as $item)
		{
			if($this->isExtendedCatalogProvider($item))
			{
				$element = \Bitrix\Iblock\ElementTable::getRow(array(
					'select' => array('IBLOCK_ID'),
					'filter' => array('ID' => $item->getProductId()),
				));
				if(!empty($element['IBLOCK_ID']))
				{
					return $element['IBLOCK_ID'];
				}
			}
		}
		unset($item);

		return null;
	}

	private function guessIblocks()
	{
		$catalogIblockId = $offersIblockId = null;

		$iblockId = $this->fetchFirstIblockId();
		if(!$iblockId)
		{
			return null;
		}

		$info = CCatalogSKU::getInfoByIBlock($iblockId);
		if(!$info)
		{
			return null;
		}
		if($info['CATALOG_TYPE'] == CCatalogSKU::TYPE_OFFERS)
		{
			$offersIblockId = $info['IBLOCK_ID'];
			$catalogIblockId = $info['PRODUCT_IBLOCK_ID'];
		}
		elseif($info['CATALOG_TYPE'] == CCatalogSKU::TYPE_FULL)
		{
			$offersIblockId = $info['IBLOCK_ID'];
			$catalogIblockId = $info['PRODUCT_IBLOCK_ID'];
		}
		elseif($info['CATALOG_TYPE'] == CCatalogSKU::TYPE_CATALOG)
		{
			$offersIblockId = null;
			$catalogIblockId = $info['IBLOCK_ID'];
		}

		return array($catalogIblockId, $offersIblockId);
	}

	private function fetchProductPriceId()
	{
		/** @var \Bitrix\Sale\BasketItem $item */
		foreach($this->getBasket() as $item)
		{
			if($this->isExtendedCatalogProvider($item))
			{
				return $item->getField('PRODUCT_PRICE_ID');
			}
		}
		unset($item);

		return null;
	}

	private function buildIblockDependedParameters()
	{
		list($catalogIblockId, $offersIblockId) = $this->guessIblocks();
		if(!$catalogIblockId)
		{
			return;
		}
		$this->arParams['IBLOCK_ID'] = $catalogIblockId;

		$properties = $this->getProperties($offersIblockId);
		$this->arParams['PROPERTY_CODE'] = array(
			$offersIblockId => $properties,
		);

		if($this->hasProperty($offersIblockId, 'MORE_PHOTO'))
		{
			$this->arParams['ADDITIONAL_PICT_PROP'] = array(
				$offersIblockId => 'MORE_PHOTO',
			);
			array_push($this->arParams['PROPERTY_CODE'][$offersIblockId], 'MORE_PHOTO');
		}

		$this->arParams['OFFER_TREE_PROPS'] =
		$this->arParams['CART_PROPERTIES']  = array(
			$offersIblockId => $properties,
		);
		$this->arParams['SHOW_PRODUCTS'] = array(
			$catalogIblockId => 'Y',
		);

		$this->arParams['PRICE_CODE'] = array(
			$this->getPriceCode($this->fetchProductPriceId()),
		);
	}

	private function getPriceCode($productPriceId)
	{
		if(!$productPriceId)
		{
			return null;
		}

		$rsPrices = CPrice::GetListEx(
			array(),
			array('ID' => $productPriceId),
			false,
			false,
			array(
				'ID',
				'CATALOG_GROUP_CODE',
			)
		);
		if(!$rsPrices)
		{
			return null;
		}
		$price = $rsPrices->fetch();

		return $price['CATALOG_GROUP_CODE']?: null;
	}

	private function hasProperty($catalogIblockId, $propertyName)
	{
		$offers = CCatalogSKU::getInfoByProductIBlock($catalogIblockId);
		if(!$offers)
		{
			return false;
		}

		return (bool)\Bitrix\Iblock\PropertyTable::getRow(array(
			'select' => array('ID', 'CODE', 'PROPERTY_TYPE', 'USER_TYPE'),
			'filter' => array(
				'=CODE' => $propertyName,
				'=IBLOCK_ID' => $offers['IBLOCK_ID'],
				'=ACTIVE' => 'Y',
				'!=ID' => $offers['SKU_PROPERTY_ID'],
				'@PROPERTY_TYPE' => array(
					\Bitrix\Iblock\PropertyTable::TYPE_FILE,
				),
				'=MULTIPLE' => 'N'
			),
		));
	}

	private function getProperties($offersIblockId)
	{
		$properties = array();
		$offers = CCatalogSKU::getInfoByOfferIBlock($offersIblockId);
		if(empty($offers))
		{
			return array();
		}
		$propertyIterator = \Bitrix\Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'CODE', 'PROPERTY_TYPE', 'USER_TYPE'),
			'filter' => array(
				'=IBLOCK_ID' => $offers['IBLOCK_ID'],
				'=ACTIVE' => 'Y',
				'!=ID' => $offers['SKU_PROPERTY_ID'],
				'@PROPERTY_TYPE' => array(
					\Bitrix\Iblock\PropertyTable::TYPE_STRING,
					\Bitrix\Iblock\PropertyTable::TYPE_LIST,
				),
				'=MULTIPLE' => 'N'
			),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));
		while($property = $propertyIterator->fetch())
		{
			$property['USER_TYPE'] = (string)$property['USER_TYPE'];
			if($property['PROPERTY_TYPE'] == \Bitrix\Iblock\PropertyTable::TYPE_STRING && $property['USER_TYPE'] != 'directory')
			{
				continue;
			}
			$propertyCode = (string)$property['CODE'];
			if($propertyCode == '')
			{
				$propertyCode = $property['ID'];
			}
			$properties[] = $propertyCode;
		}

		return $properties;
	}

	/**
	 * Returns gift collections for current basket.
	 * @return array
	 */
	protected function getGiftCollections()
	{
		$collections = array();
		if(!empty($this->arParams["FULL_DISCOUNT_LIST"]))
		{
			$collections = $this->giftManager->getCollectionsByBasket(
				$this->getBasket(),
				$this->arParams["FULL_DISCOUNT_LIST"],
				$this->arParams["APPLIED_DISCOUNT_LIST"]
			);
		}

		return $collections;
	}

	/**
	 * Returns list of product ids which will be showed.
	 * @return array
	 */
	protected function getProductIds()
	{
		if($this->productIds !== null)
		{
			return $this->productIds;
		}

		\Bitrix\Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
		$collections = $this->getGiftCollections();
		\Bitrix\Sale\Compatible\DiscountCompatibility::revertUsageCompatible();

		$this->productIds = array();
		foreach($collections as $collection)
		{
			foreach($collection as $gift)
			{
				$this->productIds[] = $gift->getProductId();
			}
			unset($gift);
		}
		unset($collection);

		return $this->productIds;
	}

	/**
	 * Returns pure offers which exist in $this->linkItems, $this->items.
	 * You can use the method after execution parent::setItemsOffers(),
	 * which fills necessary $this->linkItems, $this->items.
	 * @return array
	 */
	private function getPureOffers()
	{
		$pureOffers = array();
		foreach($this->getProductIds() as $productId)
		{
			if(isset($this->linkItems[$productId]))
			{
				continue;
			}
			$pureOffer = $this->findPureOfferInItemsByOfferId($productId);
			if(!$pureOffer)
			{
				continue;
			}
			if(!isset($pureOffers[$pureOffer['LINK_ELEMENT_ID']]))
			{
				$pureOffers[$pureOffer['LINK_ELEMENT_ID']] = array();
			}
			$pureOffers[$pureOffer['LINK_ELEMENT_ID']][] = $pureOffer;
		}
		unset($productId);

		return $pureOffers;
	}

	/**
	 * Finds array with data which fully describes offer (SKU) by offer id.
	 * The method uses $this->items.
	 * @param $offerId
	 * @return null|array
	 */
	private function findPureOfferInItemsByOfferId($offerId)
	{
		if(!empty($this->items[$offerId]['OFFERS']))
		{
			//positive search
			foreach($this->items[$offerId]['OFFERS'] as $i => $offer)
			{
				if($offer['ID'] == $offerId)
				{
					return $offer;
				}
			}
			unset($offer);
		}

		//if we have two or more offers for one product, then only one of them has OFFERS, all of another don't have.
		foreach($this->items as $item)
		{
			if(!$item['OFFERS'])
			{
				continue;
			}
			foreach($item['OFFERS'] as $offer)
			{
				if($offer['ID'] == $offerId)
				{
					return $offer;
				}
			}
			unset($offer);
		}
		unset($offer);


		return null;
	}

	/**
	 * Sets offers to specific product. If exists another offer, then it will delete.
	 * Notice! List of offers have to belong to identical product.
	 * @param array $pureOffers
	 * @return void
	 */
	private function setPureOffersToProduct(array $pureOffers)
	{
		$parentElementId = null;
		foreach($pureOffers as $pureOffer)
		{
			if(!$parentElementId)
			{
				$parentElementId = $pureOffer['LINK_ELEMENT_ID'];
				$this->items[$parentElementId]['OFFERS'] = $pureOffers;
			}
			else
			{
				//we have to delete another offers, because they will repeat base product.
				unset($this->items[$pureOffer['ID']]);
			}
		}
		unset($pureOffer);

		if($parentElementId)
		{
			$this->linkItems[$parentElementId]['OFFERS'] = $pureOffers;
		}
	}

	/**
	 * Add offers for each catalog product.
	 * @return void
	 */
	final protected function setItemsOffers()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::setItemsOffers();

		foreach($this->linkItems as &$item)
		{
			if(!isset($item['OFFERS']))
			{
				continue;
			}

			foreach($item['OFFERS'] as &$offer)
			{
				$this->setGiftDiscountToMinPrice($offer);
			}
			unset($offer);
		}
		unset($item);

		foreach($this->getPureOffers() as $offers)
		{
			$this->setPureOffersToProduct($offers);
		}
		unset($offerId);

		if($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	protected function setItemsPrices()
	{
		parent::setItemsPrices();

		foreach ($this->items as &$item)
		{
			if (!empty($item['OFFERS']))
			{
				continue;
			}

			$this->setGiftDiscountToMinPrice($item);
		}
	}

	protected function formatResult()
	{
		$this->items = array_slice($this->items, 0, $this->arParams['PAGE_ELEMENT_COUNT']);
		parent::formatResult();
	}

	/**
	 * Returns catalog prices data by product.
	 * @param array $item Product.
	 * @return array
	 */
	protected function getPriceDataByItem(array $item)
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		$priceDataByItem = parent::getPriceDataByItem($item);

		if($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}

		return $priceDataByItem;
	}

	/**
	 * @param $offer
	 * @return mixed
	 */
	private function setGiftDiscountToMinPrice(array &$offer)
	{
		$offer['MIN_PRICE']['PRINT_DISCOUNT_VALUE_NOVAT'] = $offer['MIN_PRICE']['PRINT_DISCOUNT_DIFF'];
		$offer['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] = $offer['MIN_PRICE']['PRINT_DISCOUNT_DIFF'];
		$offer['MIN_PRICE']['PRINT_DISCOUNT_VALUE_VAT'] = $offer['MIN_PRICE']['PRINT_DISCOUNT_DIFF'];

		$offer['MIN_PRICE']['DISCOUNT_DIFF'] = $offer['MIN_PRICE']['VALUE'];
		$offer['MIN_PRICE']['DISCOUNT_DIFF'] = $offer['MIN_PRICE']['PRINT_VALUE'];
		$offer['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'] = 100;
		$offer['MIN_PRICE']['DISCOUNT_VALUE_NOVAT'] = 0;
		$offer['MIN_PRICE']['DISCOUNT_VALUE_VAT'] = 0;
		$offer['MIN_PRICE']['DISCOUNT_VALUE'] = 0;
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	private function isExtendedCatalogProvider(\Bitrix\Sale\BasketItem $item)
	{
		return
			$item->getField('MODULE') === 'catalog' &&
			(
				$item->getProvider() &&
				(
					$item->getProvider() === "CCatalogProductProvider"
					|| $item->getProvider() === "\Bitrix\Catalog\Product\CatalogProvider"
					|| array_key_exists("CCatalogProductProvider", class_parents($item->getProvider()))
					|| array_key_exists("\Bitrix\Catalog\Product\CatalogProvider", class_parents($item->getProvider()))
				)
			);
	}
}