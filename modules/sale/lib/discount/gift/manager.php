<?php

namespace Bitrix\Sale\Discount\Gift;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Discount\Analyzer;
use Bitrix\Sale\Order;
use Bitrix\Sale\Registry;
use CCatalogSKU;
use CSaleDiscountActionApply;
use SplObjectStorage;

final class Manager
{
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var SplObjectStorage */
	protected $basketCloneCache;
	/** @var SplObjectStorage */
	protected $basketAddedProduct;
	/** @var  Manager */
	private static $instance;
	/** @var  int */
	private $userId;

	private function __construct()
	{
		$this->errorCollection = new ErrorCollection;
		$this->basketCloneCache = new SplObjectStorage;
		$this->basketAddedProduct = new SplObjectStorage;
	}

	/**
	 * @param Basket $basket
	 * @return Basket
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getBasketCopy(Basket $basket)
	{
		if(!$this->basketCloneCache->contains($basket))
		{
			$this->basketCloneCache[$basket] = $basket->copy();
		}

		if($this->basketAddedProduct->contains($this->basketCloneCache[$basket]))
		{
			foreach($this->basketAddedProduct[$this->basketCloneCache[$basket]] as $product)
			{
				$this->deleteProductFromBasket($this->basketCloneCache[$basket], $product);
			}
			$this->basketAddedProduct->detach($this->basketCloneCache[$basket]);
		}

		return $this->basketCloneCache[$basket];
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @param int $userId
	 * @return $this
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;

		return $this;
	}

	private function __clone()
	{}

	/**
	 * Returns Singleton of Manager
	 * @return Manager
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Returns list of collections with gifts by basket.
	 * For performance you may set arguments $discounts and $appliedDiscounts, which respond to basket.
	 *
	 * @param Basket     $basket Target basket.
	 * @param array|null $discounts List of all discounts for basket, which already succeed conditions.
	 * @param array|null $appliedDiscounts List of all discounts for basket, which already applied to basket.
	 * @return array
	 */
	public function getCollectionsByBasket(Basket $basket, array $discounts = null, array $appliedDiscounts = null)
	{
		$this->errorCollection->clear();

		if(!$this->existsDiscountsWithGift())
		{
			return array();
		}

		if($discounts === null || $appliedDiscounts === null)
		{
			list($discounts, $appliedDiscounts) = $this->getDiscounts($basket);
		}

		$appliedList = array();
		foreach($appliedDiscounts as $discount)
		{
			$appliedList[$discount['ID']] = $discount;
		}
		unset($discount, $appliedDiscounts);

		if(!$discounts)
		{
			return array();
		}

		$potentialGiftData = $this->getPotentialGiftData($discounts, $appliedList);

		$collections = array();
		foreach($potentialGiftData as $giftData)
		{
			$giftData['GiftValue'] = is_array($giftData['GiftValue'])? $giftData['GiftValue'] : array($giftData['GiftValue']);

			$giftCollection = new Collection(array(), $giftData['Type']);
			foreach($giftData['GiftValue'] as $value)
			{
				$giftCollection[] = new Gift($value);
			}
			unset($value);

			$collections[] = $giftCollection;
		}
		unset($giftData);

		return $collections;
	}

	private function getGiftedProductIdsByAppliedDiscount(array $discount)
	{
		if(empty($discount['RESULT']['BASKET']))
		{
			return array();
		}
		$giftedProducts = array();
		foreach($discount['RESULT']['BASKET'] as $item)
		{
			if(empty($item['VALUE_PERCENT']) || $item['VALUE_PERCENT'] != 100)
			{
				continue;
			}
			//todo today we work only with catalog items. In future we will move the method to gifter and there
			//will return gifted products.
			if(empty($item['MODULE']) || $item['MODULE'] !== 'catalog')
			{
				continue;
			}
			$giftedProducts[] = $item['PRODUCT_ID'];
		}
		unset($item);

		return $giftedProducts;
	}

	private function deleteGiftedProducts(array $gifts, array $giftedProductIds)
	{
		foreach($gifts as $i => &$giftItem)
		{
			if($giftItem['Type'] === CSaleDiscountActionApply::GIFT_SELECT_TYPE_ONE)
			{
				if(array_intersect($giftedProductIds, (array)$giftItem['GiftValue']))
				{
					unset($gifts[$i]);
					continue;
				}
			}
			elseif($giftItem['Type'] === CSaleDiscountActionApply::GIFT_SELECT_TYPE_ALL)
			{
				$giftItem['GiftValue'] = array_diff((array)$giftItem['GiftValue'], $giftedProductIds);
				if(!$giftItem['GiftValue'])
				{
					unset($gifts[$i]);
					continue;
				}
			}
		}
		unset($giftItem);

		return $gifts;
	}

	private function getPotentialGiftData(array $discounts, array $appliedDiscounts = array())
	{
		if(!$discounts)
		{
			return array();
		}

		$potentialGiftData = array();
		foreach($appliedDiscounts as $discount)
		{
			$giftedProductIds = $this->getGiftedProductIdsByAppliedDiscount($discount);
			$potentialGiftData = array_merge(
					$potentialGiftData,
					$this->deleteGiftedProducts(
						\CSaleActionGiftCtrlGroup::ProvideGiftProductData($discount),
						\CSaleActionGiftCtrlGroup::ExtendProductIds($giftedProductIds)
					)
			);
		}
		unset($discount);

		foreach($discounts as $discount)
		{
			if(isset($appliedDiscounts[$discount['ID']]))
			{
				continue;
			}
			//todo Does the list use LAST_DISCOUNT configuration?
			$data = \CSaleActionGiftCtrlGroup::ProvideGiftProductData($discount);
			if(!$data)
			{
				continue;
			}
			$potentialGiftData = array_merge($potentialGiftData, $data);
		}
		unset($discount);

		return $potentialGiftData;
	}

	private function isValidProduct(array $product)
	{
		if(empty($product['ID']))
		{
			$this->errorCollection[] = new Error('Product array has to have ID');
		}
		if(empty($product['MODULE']))
		{
			$this->errorCollection[] = new Error('Product array has to have MODULE');
		}
		if(empty($product['PRODUCT_PROVIDER_CLASS']))
		{
			$this->errorCollection[] = new Error('Product array has to have PRODUCT_PROVIDER_CLASS');
		}
		if(empty($product['QUANTITY']))
		{
			$this->errorCollection[] = new Error('Product array has to have QUANTITY');
		}
		if($this->errorCollection->count())
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns list of collections with gifts by product and some basket.
	 * If basket does not contain product, then we calculate gifts without the product.
	 * After we add the product to basket, calculate gifts and comparing with previous gifts.
	 * If there is difference, then the method returns gifts with the product, else the method returns empty array.
	 *
	 * @param Basket $basket Target basket.
	 * @param array $product Array which describes product (@see isValidProduct()).
	 * @return array|null
	 */
	public function getCollectionsByProduct(Basket $basket, array $product)
	{
		$this->errorCollection->clear();

		if(!$this->existsDiscountsWithGift())
		{
			return array();
		}

		if(!$this->isValidProduct($product))
		{
			return null;
		}

		$pseudoBasket = $this->getBasketCopy($basket);
		$checkProductInBasket = $this->checkProductInBasket($product, $pseudoBasket);
		if($checkProductInBasket)
		{
			$this->deleteProductFromBasket($pseudoBasket, $product);
		}
		else
		{
			$this->addProductToBasket($pseudoBasket, $product);
		}

		$collectionsByPseudoBasket = $this->getCollectionsByBasket($pseudoBasket);
		$collectionsByBasket = $this->getCollectionsByBasket($basket);

		if(!$this->hasDifference($collectionsByBasket, $collectionsByPseudoBasket))
		{
			return array();
		}

		return $checkProductInBasket? $collectionsByBasket : $collectionsByPseudoBasket;
	}

	private function hasDifference(array $collectionsA, array $collectionsB)
	{
		foreach($collectionsA as $i => $collectionA)
		{
			$found = false;
			foreach($collectionsB as $j => $collectionB)
			{
				if($this->isEqual($collectionA, $collectionB))
				{
					$found = true;
					unset($collectionsA[$i]);
					unset($collectionsB[$j]);

					break;
				}
			}
			unset($collectionB);

			if(!$found)
			{
				return true;
			}
		}
		unset($collectionA);

		return (bool)$collectionsB;
	}

	private function isEqual(Collection $collectionA, Collection $collectionB)
	{
		$productIdsFromCollectionA = $this->getProductIdsFromCollection($collectionA);
		$productIdsFromCollectionB = $this->getProductIdsFromCollection($collectionB);

		return
			!array_diff($productIdsFromCollectionA, $productIdsFromCollectionB) &&
			!array_diff($productIdsFromCollectionB, $productIdsFromCollectionA)
		;
	}

	private function getProductIdsFromCollection(Collection $collection)
	{
		$idsFrom = array();
		foreach($collection as $gift)
		{
			/** @var Gift $gift */
			$idsFrom[$gift->getProductId()] = $gift->getProductId();
		}
		unset($gift);

		return $idsFrom;
	}

	/**
	 * Returns basket items in specific format which we can use to insert in discount and build structure which
	 * will look like old discounts in \CAllSaleDiscount::DoProcessOrder in DISCOUNT_LIST.
	 *
	 * @param Basket $basket
	 * @param array  $discountData
	 * @param array  $calcResults
	 * @return array
	 */
	private function getAffectedReformattedBasketItemsInDiscount(Basket $basket, array $discountData, array $calcResults)
	{
		$items = array();
		foreach($calcResults['PRICES']['BASKET'] as $basketCode => $priceData)
		{
			if(empty($priceData['DISCOUNT']))
			{
				continue;
			}
			if(!empty($priceData['PRICE']))
			{
				continue;
			}
			if(empty($calcResults['RESULT']['BASKET'][$basketCode]))
			{
				continue;
			}
			//we have gift and PRICE equals 0.
			$found = false;
			foreach($calcResults['RESULT']['BASKET'][$basketCode] as $data)
			{
				if($data['DISCOUNT_ID'] == $discountData['ID'])
				{
					$found = true;
				}
			}
			unset($data);

			if(!$found)
			{
				continue;
			}

			$basketItem = $basket->getItemByBasketCode($basketCode);
			if(!$basketItem || $basketItem->getField('MODULE') != 'catalog')
			{
				continue;
			}

			$items[] = array(
				'PRODUCT_ID' => $basketItem->getProductId(),
				'VALUE_PERCENT' => '100',
				'MODULE' => 'catalog',
			);
		}
		unset($priceData);

		return $items;
	}

	private function getDiscounts(Basket $basket)
	{
		if($basket->getOrder())
		{
			throw new SystemException('Could not get discounts by basket which has order.');
		}

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$order = $orderClass::create($basket->getSiteId(), $this->userId);
		if(!$order->setBasket($basket)->isSuccess())
		{
			return null;
		}
		$calcResults = $order->getDiscount()->getApplyResult(true);

		$appliedDiscounts = array();
		foreach($calcResults['DISCOUNT_LIST'] as $discountData)
		{
			if(isset($calcResults['FULL_DISCOUNT_LIST'][$discountData['REAL_DISCOUNT_ID']]))
			{
				$appliedDiscounts[$discountData['REAL_DISCOUNT_ID']] = $calcResults['FULL_DISCOUNT_LIST'][$discountData['REAL_DISCOUNT_ID']];
				if(empty($appliedDiscounts[$discountData['REAL_DISCOUNT_ID']]['RESULT']['BASKET']))
				{
					$appliedDiscounts[$discountData['REAL_DISCOUNT_ID']]['RESULT']['BASKET'] = array();
				}

				$appliedDiscounts[$discountData['REAL_DISCOUNT_ID']]['RESULT']['BASKET'] = array_merge(
					$appliedDiscounts[$discountData['REAL_DISCOUNT_ID']]['RESULT']['BASKET'],
					$this->getAffectedReformattedBasketItemsInDiscount($basket, $discountData, $calcResults)
				);
			}
		}
		unset($discountData);

		return array(
			$calcResults['FULL_DISCOUNT_LIST'],
			$appliedDiscounts,
		);
	}

	private function checkProductInBasket(array $product, Basket $basket)
	{
		return (bool)$this->getItemFromBasket($product, $basket);
	}

	private function getItemFromBasket(array $product, Basket $basket)
	{
		foreach($basket as $item)
		{
			/** @var BasketItem $item */
			if(
				$item->getProductId() == $product['ID'] &&
				$item->getField('MODULE') === $product['MODULE']
			)
			{
				return $item;
			}
		}

		return null;
	}

	private function addProductToBasket(Basket $basket, array $product)
	{
		$basketItem = $basket->createItem($product['MODULE'], $product['ID']);
		unset($product['MODULE'], $product['ID']);

		$result = $basketItem->setFields($product);
		if(!$result->isSuccess())
		{
			return;
		}

		if(!$this->basketAddedProduct->contains($basket))
		{
			$this->basketAddedProduct[$basket] = array($product);
		}
		else
		{
			$this->basketAddedProduct[$basket][] = $product;
		}
	}

	private function deleteProductFromBasket(Basket $basket, array $product)
	{
		$item = $this->getItemFromBasket($product, $basket);
		if($item && $item->getQuantity() == $product['QUANTITY'])
		{
			$item->delete();
		}
	}

	private function existProductInAppliedDiscounts(array $product, array $appliedDiscounts)
	{
		foreach($appliedDiscounts as $discount)
		{
			if(array_search($product['ID'], $this->getGiftedProductIdsByAppliedDiscount($discount)) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if the product is gift for basket.
	 *
	 * @param Basket $basket Target basket.
	 * @param array  $product Array which describes product (@see isValidProduct()).
	 * @return bool|null
	 */
	public function isGift(Basket $basket, array $product)
	{
		$this->errorCollection->clear();

		if(!$this->existsDiscountsWithGift())
		{
			return false;
		}

		if(!$this->isValidProduct($product))
		{
			return null;
		}

		if(!$this->checkProductInBasket($product, $basket))
		{
			$basket = $this->getBasketCopy($basket);
			$this->addProductToBasket($basket, $product);
		}
		list(, $appliedDiscounts) = $this->getDiscounts($basket);

		return $this->existProductInAppliedDiscounts($product, $appliedDiscounts);
	}

	/**
	 * Returns true if the discount contains action with gift.
	 *
	 * @param array $discount Discount.
	 * @return bool
	 */
	public function isContainGiftAction(array $discount)
	{
		return Analyzer::getInstance()->isContainGiftAction($discount);
	}

	/**
	 * Returns true if exists discount with gift.
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function existsDiscountsWithGift()
	{
		return Option::get('sale', 'exists_discounts_with_gift', 'N') === 'Y';
	}

	/**
	 * Disables existence discount with gift.
	 * @return void
	 */
	public function disableExistenceDiscountsWithGift()
	{
		Option::set('sale', 'exists_discounts_with_gift', 'N');
	}

	/**
	 * Enables existence discount with gift.
	 * @return void
	 */
	public function enableExistenceDiscountsWithGift()
	{
		Option::set('sale', 'exists_discounts_with_gift', 'Y');
	}
}