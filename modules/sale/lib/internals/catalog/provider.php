<?php


namespace Bitrix\Sale\Internals\Catalog;


use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Internals\PoolQuantity;
use Bitrix\Sale\Internals\ShipmentRules;

/**
 * Class Provider
 *
 * @package Bitrix\Sale\Internals
 */
final class Provider
{

	const SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_SHIP = true;
	const SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP = false;
	const SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_EMPTY = null;

	private static $ignoreErrors = false;


	/**
	 * @param $basketList
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function getProductData($basketList, array $context)
	{
		$result = new Sale\Result();

		if (empty($context))
		{
			throw new Main\ArgumentNullException('context');
		}

		if (!is_array($basketList) && !($basketList instanceof Sale\BasketBase))
		{
			throw new Main\ArgumentTypeException('basketList');
		}

		$creator = Sale\Internals\ProviderCreator::create($context);
		/** @var Sale\BasketItem $basketItem */
		foreach ($basketList as $basketItem)
		{
			$creator->addBasketItem($basketItem);
		}

		$r = $creator->getProductData();
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (array_key_exists('PRODUCT_DATA_LIST', $data))
			{
				$result->setData($data);
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\BasketItemBase $basketItem
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 */
	public static function getBundleItems(Sale\BasketItemBase $basketItem, array $context)
	{
		if (empty($context))
		{
			throw new Main\ArgumentNullException('context');
		}

		$result = new Sale\Result();

		$creator = Sale\Internals\ProviderCreator::create($context);
		/** @var Sale\BasketItem $basketItem */
		$creator->addBasketItem($basketItem);

		$r = $creator->getBundleItems();
		if ($r->isSuccess())
		{
			return $r;
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\BasketItemBase $basketItem
	 * @param array|null $context
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public static function checkAvailableQuantityByBasketItem(Sale\BasketItemBase $basketItem, array $context = array())
	{
		$result = new Sale\Result();

		/** @var \Bitrix\Sale\Basket $basket */
		if (!$basket = $basketItem->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		$order = $basket->getOrder();
		if (empty($context) && $order)
		{
			$context = static::prepareContext($order, $context);
		}

		$r = static::checkContext($context);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$quantity = $basketItem->getQuantity();
		$productId = $basketItem->getProductId();

		$deltaQuantity = $basketItem->getDeltaQuantity();

		$poolQuantity = 0;

		if ($order)
		{
			if ($deltaQuantity <= 0)
			{
				$result->setData(
					array(
						'AVAILABLE_QUANTITY' => $quantity
					)
				);

				return $result;
			}

			$pool = PoolQuantity::getInstance($order->getInternalId());
			$poolQuantity = $pool->get(PoolQuantity::POOL_RESERVE_TYPE, $productId);
			if (!empty($poolQuantity))
			{
				$tryQuantity = $quantity + $poolQuantity;
				if ($tryQuantity == 0)
				{
					$result->setData(array(
						 'AVAILABLE_QUANTITY' => $quantity
					 ));

					return $result;
				}
			}
		}


		$resultList = array();

		$creator = Sale\Internals\ProviderCreator::create($context);

		/** @var Sale\BasketItem $basketItem */
		$creator->addBasketItem($basketItem);

		$r = $creator->getAvailableQuantity();
		if ($r->isSuccess())
		{
			$providerName = null;
			$providerName = $basketItem->getProviderName();

			if (strval(trim($providerName)) == '')
			{
				$providerName = $basketItem->getCallbackFunction();
			}

			if (!empty($providerName) && $providerName[0] == "\\")
			{
				$providerName = ltrim($providerName, '\\');
			}

			$checkProviderName = $providerName;
			$data = $r->getData();
			if (array_key_exists('AVAILABLE_QUANTITY_LIST', $data) && isset($data['AVAILABLE_QUANTITY_LIST'][$checkProviderName]))
			{
				$resultList = $data['AVAILABLE_QUANTITY_LIST'][$checkProviderName];
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		if (isset($resultList[$productId]))
		{
//			$availableQuantity = $deltaQuantity;
			$result->setData(
				array(
					'AVAILABLE_QUANTITY' => $resultList[$productId] - $poolQuantity
				)
			);
		}

		return $result;
	}

	/**
	 * @param Sale\BasketItemBase $basketItem
	 * @param array|null $context
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public static function getAvailableQuantityAndPriceByBasketItem(Sale\BasketItemBase $basketItem, array $context = array())
	{
		$result = new Sale\Result();

		/** @var \Bitrix\Sale\Basket $basket */
		if (!$basket = $basketItem->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		$order = $basket->getOrder();

		if (empty($context) && !$order)
		{
			$context = $basket->getContext();
		}

		if (empty($context) && $order)
		{
			$context = static::prepareContext($order, $context);
		}

		$r = static::checkContext($context);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$resultData = array();

		$creator = Sale\Internals\ProviderCreator::create($context);

		/** @var Sale\BasketItem $basketItem */
		$creator->addBasketItem($basketItem);

		$r = $creator->getAvailableQuantityAndPrice();
		if ($r->isSuccess())
		{
			$providerName = $basketItem->getProviderName();

			if (strval($providerName) == '')
			{
				$providerName = $basketItem->getCallbackFunction();
			}

			$providerName = static::clearProviderName($providerName);

			$checkProviderName = $providerName;
			$data = $r->getData();
			if (array_key_exists('PRODUCT_DATA_LIST', $data) && isset($data['PRODUCT_DATA_LIST'][$checkProviderName]))
			{
				$productData = $data['PRODUCT_DATA_LIST'][$checkProviderName];

				if (isset($productData['PRICE_LIST'][$basketItem->getProductId()][$basketItem->getBasketCode()]))
				{
					$resultData['PRICE_DATA'] = $productData['PRICE_LIST'][$basketItem->getProductId()][$basketItem->getBasketCode()];
				}

				if (isset($productData['AVAILABLE_QUANTITY_LIST'][$basketItem->getProductId()]))
				{
					$resultData['AVAILABLE_QUANTITY'] = $productData['AVAILABLE_QUANTITY_LIST'][$basketItem->getProductId()];
				}
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		if (isset($resultData))
		{
			$result->setData(
				$resultData
			);
		}

		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function tryReserveShipment(Sale\Shipment $shipment, array $context = array())
	{
		$result = new Sale\Result();

		/** @var Sale\ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Sale\Order $order */
		$order = $shipmentCollection->getOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$context = static::prepareContext($order, $context);
		$r = static::checkContext($context);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\ShipmentItemCollection $shipmentCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		$availableQuantityList = array();
		$needQuantityList = array();

		/** @var Sale\Result $r */
		$r = static::getNeedQuantityByShipmentItemCollection($shipmentItemCollection);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		else
		{
			$data = $r->getData();
			if (!empty($data['NEED_QUANTITY_LIST']))
			{
				$needQuantityList = $data['NEED_QUANTITY_LIST'];
			}
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if ($shipmentItemCollection->count() == 0)
		{
			return $result;
		}

		$creator = Sale\Internals\ProviderCreator::create($context);
		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$shipmentProductData = $creator->createItemForReserve($shipmentItem);
			$creator->addShipmentProductData($shipmentProductData);
		}

		$r = $creator->getAvailableQuantity();
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (array_key_exists('AVAILABLE_QUANTITY_LIST', $data))
			{
				$availableQuantityList = $data['AVAILABLE_QUANTITY_LIST'] + $availableQuantityList;
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if (!empty($needQuantityList) && $result->isSuccess())
		{
			$applyItemsList = array();

			foreach ($availableQuantityList as $providerName => $productAvailableQuantityList)
			{
				$providerName = trim($providerName);
				foreach ($productAvailableQuantityList as $productId => $productAvailableQuantity)
				{
					if (array_key_exists($productId, $needQuantityList[$providerName]))
					{
						if (Sale\Configuration::getProductReservationCondition() != Sale\Configuration::RESERVE_ON_SHIP)
						{
							$poolQuantity = 0;
							if ($order->getId() > 0)
							{
								$poolQuantity = (float)$pool->get(PoolQuantity::POOL_RESERVE_TYPE, $productId);
							}
							$needQuantity = $needQuantityList[$providerName][$productId];

							$productAvailableQuantity -= $poolQuantity;
							$reservedQuantity = ($needQuantity >= $productAvailableQuantity ? $productAvailableQuantity : $needQuantity);

							$applyItemsList[$providerName][$productId] = $reservedQuantity;
						}
					}
					else
					{
						/** @var Sale\ShipmentItem $shipmentItem */
						foreach ($shipmentItemCollection as $shipmentItem)
						{
							$basketItem = $shipmentItem->getBasketItem();

							if ($basketItem->getProductId() == $productId)
							{
								$result->addWarning( new Sale\ResultWarning(Main\Localization\Loc::getMessage('SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY', array(
									'#PRODUCT_NAME#' => $basketItem->getField('NAME')
								)), 'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY') );
								break;
							}
						}
					}
				}

			}

			if (!empty($applyItemsList))
			{
				$shipmentProductIndex = static::createProductShipmentItemMapByShipmentItemCollection($shipmentItemCollection);

				/** @var Sale\Shipment $shipment */
				if (!$shipment = $shipmentItemCollection->getShipment())
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollectionCollection" not found');
				}

				/** @var Sale\ShipmentCollection $shipmentCollection */
				if (!$shipmentCollection = $shipment->getCollection())
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
				}

				/** @var Sale\Order $order */
				if (!$order = $shipmentCollection->getOrder())
				{
					throw new Main\ObjectNotFoundException('Entity "Order" not found');
				}

				$pool = PoolQuantity::getInstance($order->getInternalId());

				static::setAvailableQuantityToShipmentItemCollection($pool, $shipmentProductIndex, $applyItemsList);
			}
		}

		return $result;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public static function tryReserveShipmentItem(Sale\ShipmentItem $shipmentItem, array $context = array())
	{
		$result = new Sale\Result();

		/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItem->getCollection();
		if (!$shipmentItemCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Sale\Shipment $shipment */
		$shipment = $shipmentItemCollection->getShipment();
		if (!$shipment)
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		if (!$shipmentCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Sale\Order $order */
		$order = $shipmentCollection->getOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$context = static::prepareContext($order, $context);
		$r = static::checkContext($context);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\ShipmentItemCollection $shipmentCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		$availableQuantityList = [];
		$needQuantityList = [];

		$r = static::getNeedQuantityByShipmentItem($shipmentItem);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		else
		{
			$data = $r->getData();
			if (!empty($data['NEED_QUANTITY_LIST']))
			{
				$needQuantityList = $data['NEED_QUANTITY_LIST'];
			}
		}

		$creator = Sale\Internals\ProviderCreator::create($context);

		$shipmentProductData = $creator->createItemForReserve($shipmentItem);
		$creator->addShipmentProductData($shipmentProductData);

		$r = $creator->getAvailableQuantity();
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (array_key_exists('AVAILABLE_QUANTITY_LIST', $data))
			{
				$availableQuantityList = $data['AVAILABLE_QUANTITY_LIST'];
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if (!empty($needQuantityList) && $result->isSuccess())
		{
			$applyItemsList = [];

			foreach ($availableQuantityList as $providerName => $productAvailableQuantityList)
			{
				$providerName = trim($providerName);
				foreach ($productAvailableQuantityList as $productId => $productAvailableQuantity)
				{
					if (array_key_exists($productId, $needQuantityList[$providerName]))
					{
						if (Sale\Configuration::getProductReservationCondition() != Sale\Configuration::RESERVE_ON_SHIP)
						{
							$poolQuantity = 0;
							if ($order->getId() > 0)
							{
								$poolQuantity = (float)$pool->get(PoolQuantity::POOL_RESERVE_TYPE, $productId);
							}
							$needQuantity = $needQuantityList[$providerName][$productId];

							$productAvailableQuantity -= $poolQuantity;
							$reservedQuantity = ($needQuantity >= $productAvailableQuantity ? $productAvailableQuantity : $needQuantity);

							$applyItemsList[$providerName][$productId] = $reservedQuantity;
						}
					}
					else
					{
						/** @var Sale\ShipmentItem $shipmentItem */
						$basketItem = $shipmentItem->getBasketItem();

						if ($basketItem->getProductId() == $productId)
						{
							$result->addWarning( new Sale\ResultWarning(Main\Localization\Loc::getMessage('SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY', array(
								'#PRODUCT_NAME#' => $basketItem->getField('NAME')
							)), 'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY') );
							break;
						}
					}
				}

			}

			if (!empty($applyItemsList))
			{
				$shipmentProductIndex = static::createProductShipmentItemMapByShipmentItem($shipmentItem);

				/** @var Sale\Shipment $shipment */
				if (!$shipment = $shipmentItemCollection->getShipment())
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollectionCollection" not found');
				}

				/** @var Sale\ShipmentCollection $shipmentCollection */
				if (!$shipmentCollection = $shipment->getCollection())
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
				}

				/** @var Sale\Order $order */
				if (!$order = $shipmentCollection->getOrder())
				{
					throw new Main\ObjectNotFoundException('Entity "Order" not found');
				}

				$pool = PoolQuantity::getInstance($order->getInternalId());
				static::setAvailableQuantityToShipmentItemCollection($pool, $shipmentProductIndex, $applyItemsList);
			}
		}

		return $result;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public static function tryUnreserveShipmentItem(Sale\ShipmentItem $shipmentItem)
	{
		$result = new Sale\Result();

		/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItem->getCollection();
		if (!$shipmentItemCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Sale\Shipment $shipment */
		$shipment = $shipmentItemCollection->getShipment();
		if (!$shipment)
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		if (!$shipmentCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Sale\Order $order */
		$order = $shipmentCollection->getOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\BasketItem $basketItem */
		$basketItem = $shipmentItem->getBasketItem();
		if (!$basketItem)
		{
			throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
		}

		$productId = $basketItem->getProductId();

		$reservedQuantity = $shipmentItem->getReservedQuantity();

		if ($reservedQuantity == 0)
		{
			return $result;
		}

		$pool->add(Sale\Internals\PoolQuantity::POOL_RESERVE_TYPE, $productId, -1 * $reservedQuantity);
		if ($shipmentItem)
		{
			$foundItem = false;
			$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $productId);
			if (!empty($poolItems))
			{
				/** @var Sale\Shipment $poolItem */
				foreach ($poolItems as $poolItem)
				{
					if ($poolItem->getInternalIndex() == $shipmentItem->getInternalIndex())
					{
						$foundItem = true;
						break;
					}
				}
			}

			if (!$foundItem)
			{
				Sale\Internals\ItemsPool::add($order->getInternalId(), $productId, $shipmentItem);
			}
		}

		$r = $shipmentItem->setField('RESERVED_QUANTITY', $shipmentItem->getReservedQuantity() + -1 * $reservedQuantity);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\ShipmentItemCollection $shipmentItemCollection
	 *
	 * @return Sale\Result
	 */
	private static function getNeedQuantityByShipmentItemCollection(Sale\ShipmentItemCollection $shipmentItemCollection)
	{
		$result = new Sale\Result();

		if ($shipmentItemCollection->count() == 0)
		{
			return $result;
		}

		$needQuantityList = array();
		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();
			if (!$basketItem)
			{
				continue;
			}

			$productId = $basketItem->getProductId();

			$providerName = $basketItem->getProviderName();
			$providerName = static::clearProviderName($providerName);

			$needQuantity = ($shipmentItem->getQuantity() - $shipmentItem->getReservedQuantity());
			if (!isset($needQuantityList[$providerName]) || !array_key_exists($productId, $needQuantityList[$providerName]))
			{
				$needQuantityList[$providerName][$productId] = 0;
			}

			$needQuantityList[$providerName][$productId] += $needQuantity;
		}

		if (!empty($needQuantityList))
		{
			$result->setData(
				array(
					'NEED_QUANTITY_LIST' => $needQuantityList,
				)
			);
		}
		return $result;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 *
	 * @return Sale\Result
	 */
	private static function getNeedQuantityByShipmentItem(Sale\ShipmentItem $shipmentItem)
	{
		$result = new Sale\Result();
		$needQuantityData = array();

		$basketItem = $shipmentItem->getBasketItem();

		$productId = $basketItem->getProductId();

		$providerName = $basketItem->getProviderName();
		$providerName = static::clearProviderName($providerName);

		$needQuantityData[$providerName][$productId] = ($shipmentItem->getQuantity() - $shipmentItem->getReservedQuantity());

		$result->setData(
			array(
				'NEED_QUANTITY_LIST' => $needQuantityData,
			)
		);

		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public static function tryUnreserveShipment(Sale\Shipment $shipment)
	{
		$result = new Sale\Result();

		/** @var Sale\ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Sale\Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\ShipmentItemCollection $shipmentCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			/** @var Sale\BasketItem $basketItem */
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
			}

			$productId = $basketItem->getProductId();

			$reservedQuantity = $shipmentItem->getReservedQuantity();

			if ($reservedQuantity == 0)
				continue;

			$pool->add(Sale\Internals\PoolQuantity::POOL_RESERVE_TYPE, $productId, -1 * $reservedQuantity);
			if ($shipmentItem)
			{
				$foundItem = false;
				$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $productId);
				if (!empty($poolItems))
				{
					/** @var Sale\Shipment $poolItem */
					foreach ($poolItems as $poolItem)
					{
						if ($poolItem->getInternalIndex() == $shipmentItem->getInternalIndex())
						{
							$foundItem = true;
							break;
						}
					}
				}

				if (!$foundItem)
				{
					Sale\Internals\ItemsPool::add($order->getInternalId(), $productId, $shipmentItem);
				}
			}

			$r = $shipmentItem->setField('RESERVED_QUANTITY', $shipmentItem->getReservedQuantity() + -1 * $reservedQuantity);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

		}

		return $result;
	}
	/**
	 * @param $needQuantity
	 * @param $reservedQuantity
	 *
	 * @return float|int
	 */
	private static function countNeedQuantity($needQuantity, $reservedQuantity)
	{
		$setQuantity = $needQuantity;
		if ($needQuantity >= $reservedQuantity)
		{
			$setQuantity = $reservedQuantity;
		}

		return $setQuantity;
	}

	/**
	 * @param Sale\ShipmentItemCollection $shipmentItemCollection
	 *
	 * @return array
	 */
	private static function createProductShipmentItemMapByShipmentItemCollection(Sale\ShipmentItemCollection $shipmentItemCollection)
	{
		static $shipmentProductIndex = array();

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$shipmentProductIndexData = static::createProductShipmentItemMapByShipmentItem($shipmentItem);
			$shipmentProductIndex = array_merge($shipmentProductIndex,  $shipmentProductIndexData);
		}

		return $shipmentProductIndex;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 *
	 * @return array
	 */
	private static function createProductShipmentItemMapByShipmentItem(Sale\ShipmentItem $shipmentItem)
	{
		static $shipmentProductIndex = array();

		/** @var Sale\ShipmentItem $shipmentItem */
		$basketItem = $shipmentItem->getBasketItem();

		$providerName = $basketItem->getProviderName();
		$providerName = static::clearProviderName($providerName);

		$productId = $basketItem->getProductId();
		$index = $shipmentItem->getInternalIndex();

		$shipmentProductIndex[$providerName][$productId][$index] = $shipmentItem;

		return $shipmentProductIndex;
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $shipmentProductIndex
	 * @param array $items
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	private static function setAvailableQuantityToShipmentItemCollection(PoolQuantity $pool, array $shipmentProductIndex, array $items)
	{
		$result = new Sale\Result();

		foreach ($items as $providerName => $productsList)
		{
			foreach ($productsList as $productId => $reservedQuantity)
			{
				if (empty($shipmentProductIndex[$providerName][$productId]))
					continue;

				/**
				 * @var  $shipmetnItemIndex
				 * @var Sale\ShipmentItem $shipmentItem
				 */
				foreach ($shipmentProductIndex[$providerName][$productId] as $shipmetnItemIndex => $shipmentItem)
				{
					$needQuantity = ($shipmentItem->getQuantity() - $shipmentItem->getReservedQuantity());

					$setQuantity = static::countNeedQuantity($needQuantity, $reservedQuantity);

					if ($needQuantity == 0 || $setQuantity == 0 )
						continue;

					$reservedQuantity -= $setQuantity;

					$pool->add(Sale\Internals\PoolQuantity::POOL_RESERVE_TYPE, $productId, $setQuantity);

					/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
					$shipmentItemCollection = $shipmentItem->getCollection();
					if (!$shipmentItemCollection)
					{
						throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
					}

					/** @var Sale\Shipment $shipment */
					$shipment = $shipmentItemCollection->getShipment();
					if (!$shipment)
					{
						throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
					}

					/** @var Sale\ShipmentCollection $shipmentCollection */
					if (!$shipmentCollection = $shipment->getCollection())
					{
						throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
					}

					/** @var Sale\Order $order */
					if (!$order = $shipmentCollection->getOrder())
					{
						throw new Main\ObjectNotFoundException('Entity "Order" not found');
					}

					$foundItem = false;
					$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $productId);
					if (!empty($poolItems))
					{
						/** @var Sale\Shipment $poolItem */
						foreach ($poolItems as $poolItem)
						{
							if ($poolItem->getInternalIndex() == $shipmentItem->getInternalIndex())
							{
								$foundItem = true;
								break;
							}
						}
					}

					if (!$foundItem)
					{
						Sale\Internals\ItemsPool::add($order->getInternalId(), $productId, $shipmentItem);
					}


					$r = $shipmentItem->setField('RESERVED_QUANTITY', $shipmentItem->getReservedQuantity() + $setQuantity);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}

			}
		}
		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function tryShipShipment(Sale\Shipment $shipment, array $context = array())
	{
		$result = new Sale\Result();

		$context = static::prepareContextByShipment($shipment, $context);

		/** @var Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		if (!$shipmentCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		$order = $shipmentCollection->getOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\ShipmentItemCollection $shipmentCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		$needShipList = array();
		$creator = Sale\Internals\ProviderCreator::create($context);
		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$creator->addShipmentItem($shipmentItem);
		}

		$r = $creator->isNeedShip();
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (array_key_exists('IS_NEED_SHIP', $data))
			{
				$needShipList = $data['IS_NEED_SHIP'] + $needShipList;
			}
		}

		$creator = Sale\Internals\ProviderCreator::create($context);
		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$shipmentProductData = $creator->createItemForShip($shipmentItem, $needShipList);
			$creator->addShipmentProductData($shipmentProductData);
		}

		$tryShipProductList = array();

		$isIgnoreErrors = false;

		$r = $creator->tryShip();

		$needSetAfterResult = false;
		if ($r->isSuccess())
		{
			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
			else
			{
				$needSetAfterResult = true;
			}
		}
		else
		{
			$result->addWarnings($r->getErrors());

			if (static::isIgnoreErrors())
			{
				$isIgnoreErrors = true;
				$needSetAfterResult = true;
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

		}

		$data = $r->getData();
		if (array_key_exists('TRY_SHIP_PRODUCTS_LIST', $data))
		{
			$tryShipProductList = $data['TRY_SHIP_PRODUCTS_LIST'] + $tryShipProductList;
		}

		if ($needSetAfterResult && !empty($tryShipProductList))
		{

			if ($isIgnoreErrors)
			{
				foreach ($tryShipProductList as $providerName => &$productList)
				{
					$productList = array_fill_keys(array_keys($productList), true);
				}
			}

			$creator->setItemsResultAfterTryShip($pool, $tryShipProductList);
		}

		return $result;
	}

	/**
	 * @param Sale\ShipmentItemCollection $shipmentItemCollection
	 *
	 * @return array
	 * @throws Main\LoaderException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function createProviderItemsMap(Sale\ShipmentItemCollection $shipmentItemCollection)
	{
		$providerProductList = array();
		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();

			if (!$basketItem)
			{
				throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
			}
			if ($basketItem->isBundleParent())
			{
				continue;
			}

			$productId = $basketItem->getProductId();
			$providerName = $basketItem->getProvider();
			if (!isset($providerProductList[$providerName][$productId]))
			{
				$fields = array(
					'PRODUCT_ID' => $productId,
					'QUANTITY' => floatval($shipmentItem->getQuantity()),
					'SHIPMENT_ITEM_LIST' => array(),
					'IS_BUNDLE_CHILD' => $basketItem->isBundleChild(),
				);
			}
			else
			{
				$fields = $providerProductList[$providerName][$productId];
				$fields['QUANTITY'] += floatval($shipmentItem->getQuantity());
			}

			$shipmentItemIndex = $shipmentItem->getInternalIndex();
			$barcodeStoreData = static::createMapShipmentItemStoreData($shipmentItem);
			if (!empty($barcodeStoreData))
			{
				$fields['STORE'][$shipmentItemIndex] = $barcodeStoreData;
//				$fields['STORE'][$shipmentItemIndex]['IS_BARCODE_MULTI'] = $basketItem->isBarcodeMulti();
			}

			$fields['SHIPMENT_ITEM_LIST'][$shipmentItemIndex] = $shipmentItem;

			$providerProductList[$providerName][$productId] = $fields;
		}

		return $providerProductList;
	}

	/**
	 * @param array $rulesProducts
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	public static function createProviderItemsMapByRules(array $rulesProducts)
	{
		$providerProductList = array();

		foreach ($rulesProducts as $ruleData)
		{
			/** @var Sale\ShipmentItem $shipmentItem */
			$shipmentItem = $ruleData['SHIPMENT_ITEM'];
			if (!$shipmentItem)
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentItem" not found');
			}


			$productId = $ruleData['PRODUCT_ID'];
			$providerName = $ruleData['PROVIDER_NAME'];
			$shipmentItemIndex = $shipmentItem->getInternalIndex();


			if (!isset($providerProductList[$providerName][$productId]))
			{
				$fields = $ruleData;
				unset($fields['SHIPMENT_ITEM']);
				unset($fields['STORE']);
				unset($fields['NEED_RESERVE']);
				unset($fields['NEED_SHIP']);
				$fields['SHIPMENT_ITEM_LIST'] = array();
			}
			else
			{
				$fields = $providerProductList[$providerName][$productId];
				$fields['QUANTITY'] += $ruleData['QUANTITY'];
			}

			if (array_key_exists('NEED_RESERVE', $ruleData))
			{
				$fields['NEED_RESERVE'][$shipmentItemIndex] = $ruleData['NEED_RESERVE'];
			}

			if (array_key_exists('NEED_SHIP', $ruleData))
			{
				$fields['NEED_SHIP'][$shipmentItemIndex] = $ruleData['NEED_SHIP'];
			}

			if (!empty($ruleData['STORE']))
			{
				$fields['STORE'][$shipmentItemIndex] = $ruleData['STORE'][$shipmentItemIndex];
			}

			$fields['SHIPMENT_ITEM_LIST'][$shipmentItemIndex] = $shipmentItem;

			$providerProductList[$providerName][$productId] = $fields;
		}

		return $providerProductList;
	}

	/**
	 * @internal
	 * @param Sale\ShipmentItem $shipmentItem
	 *
	 * @return array|bool
	 * @throws Main\ObjectNotFoundException
	 */
	public static function createMapShipmentItemStoreData(Sale\ShipmentItem $shipmentItem)
	{
		$resultList = array();

		/** @var Sale\BasketItem $basketItem */
		if (!$basketItem = $shipmentItem->getBasketItem())
		{
			throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
		}

		if ($basketItem->isBundleParent())
		{
			return false;
		}

		/** @var Sale\ShipmentItemStoreCollection $shipmentItemStoreCollection */
		if (!$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemStoreCollection" not found');
		}

		if ($shipmentItemStoreCollection->count() > 0)
		{
			$countBarcode = 0;
			/** @var Sale\ShipmentItemStore $shipmentItemStore */
			foreach ($shipmentItemStoreCollection as $shipmentItemStore)
			{
				/** @var Sale\BasketItem $basketItem */
				if (!$basketItem = $shipmentItemStore->getBasketItem())
				{
					throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
				}

				//				$basketCode = $basketItem->getBasketCode();
				$productId = $basketItem->getProductId();

				$storeId = $shipmentItemStore->getStoreId();

				if (!isset($resultList[$storeId]))
				{
					$resultList[$storeId] = array(
						'PRODUCT_ID' => $productId,
						'QUANTITY' => 0,
						'STORE_ID' => $storeId,
						'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
						'BARCODE' => array()
					);
				}

				$barcodeId = ($shipmentItemStore->getId() > 0)? $shipmentItemStore->getId() : 'n'.$countBarcode;
				$countBarcode++;
				$resultList[$storeId]['QUANTITY'] += $basketItem->isBarcodeMulti()? 1 : $shipmentItemStore->getQuantity();
				$resultList[$storeId]['BARCODE'][$barcodeId] = $shipmentItemStore->getBarcode();

			}
		}

		return $resultList;
	}

	/**
	 * @internal
	 * @param $shipmentItemList
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public static function createMapShipmentItemCollectionStoreData($shipmentItemList)
	{
		$resultList = array();

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$basketCode = $shipmentItem->getBasketCode();

			if (!isset($resultList[$basketCode]))
			{
				$resultList[$basketCode] = array();
			}
			
			$map = static::createMapShipmentItemStoreData($shipmentItem);
			if (!empty($map) && is_array($map))
			{
				$resultList[$basketCode] = $map + $resultList[$basketCode];
			}
		}

		return $resultList;
	}


	/**
	 * @param Sale\Order $order
	 * @param array|null $context
	 *
	 * @return Sale\Result
	 * @throws Main\SystemException
	 */
	public static function save(Sale\Order $order, array $context = array())
	{
		$result = new Sale\Result();

		$context = static::prepareContext($order, $context);

		$r = static::checkContext($context);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());
		/** @var array $poolQuantitiesList */
		$poolQuantitiesList = $pool->getQuantities(PoolQuantity::POOL_QUANTITY_TYPE);

		/** @var array $poolReservationList */
		$poolReservationList = $pool->getQuantities(PoolQuantity::POOL_RESERVE_TYPE);

		if (empty($poolQuantitiesList) && empty($poolReservationList))
			return $result;

		$rulesMap = ShipmentRules::createOrderRuleMap($order, $pool);

		if (empty($rulesMap))
		{
			return $result;
		}

		$r = ShipmentRules::saveRules($rulesMap, $context);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		$pool->reset(PoolQuantity::POOL_QUANTITY_TYPE);
		$pool->reset(PoolQuantity::POOL_RESERVE_TYPE);

		return $result;
	}


	/**
	 * @param Sale\Shipment $shipment
	 *
	 * @return Sale\Order
	 * @throws Main\ObjectNotFoundException
	 */
	public static function getOrderByShipment(Sale\Shipment $shipment)
	{
		/** @var Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		if (!$shipmentCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		$order = $shipmentCollection->getOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		return $order;
	}



	/**
	 * @param Sale\Shipment $shipment
	 * @param array $context
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	private static function prepareContextByShipment(Sale\Shipment $shipment, array $context = array())
	{
		$order = static::getOrderByShipment($shipment);

		if (empty($context))
		{
			$context = array(
				'SITE_ID' => $order->getSiteId(),
				'USER_ID' => $order->getUserId(),
				'CURRENCY' => $order->getCurrency(),
			);
		}
		else
		{
			if (empty($context['SITE_ID']))
			{
				$context['SITE_ID'] = $order->getSiteId();
			}

			if (empty($context['USER_ID']) && $order->getUserId() > 0)
			{
				$context['USER_ID'] = $order->getUserId();
			}

			if (empty($context['CURRENCY']))
			{
				$context['CURRENCY'] = $order->getCurrency();
			}
		}

		return $context;
	}

	/**
	 * @param Sale\OrderBase $order
	 * @param array $context
	 *
	 * @return array
	 */
	private static function prepareContext(Sale\OrderBase $order, array $context = array())
	{
		if (empty($context))
		{
			$context = array(
				'SITE_ID' => $order->getSiteId(),
				'USER_ID' => $order->getUserId(),
				'CURRENCY' => $order->getCurrency(),
			);
		}

		if (!empty($context))
		{
			if (empty($context['SITE_ID']))
			{
				$context['SITE_ID'] = $order->getSiteId();
			}

			if (empty($context['USER_ID']) && $order->getUserId() > 0)
			{
				$context['USER_ID'] = $order->getUserId();
			}

			if (empty($context['CURRENCY']))
			{
				$context['CURRENCY'] = $order->getCurrency();
			}
		}

		return $context;
	}

	/**
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 */
	private static function checkContext(array $context)
	{
		$result = new Sale\Result();

		if (empty($context['SITE_ID']))
		{
			throw new Main\ArgumentNullException('SITE_ID');
		}

		if (empty($context['CURRENCY']))
		{
			throw new Main\ArgumentNullException('CURRENCY');
		}

		return $result;
	}

	/**
	 * @param $module
	 * @param $name
	 *
	 * @return string|null
	 * @throws Main\LoaderException
	 */
	public static function getProviderName($module, $name)
	{
		static $providerProxy = array();
		$code = $module."|".$name;

		if (array_key_exists($code, $providerProxy))
		{
			return $providerProxy[$code];
		}

		$providerName = null;
		if (strval($module) != '' && Main\Loader::includeModule($module) && class_exists($name))
		{
			$provider = static::getProviderEntity($name);
			if ($provider)
			{
				$providerName = $name;
			}
		}

		if ($providerName !== null)
		{
			$providerProxy[$code] = $providerName;
		}

		return $providerName;
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public static function getProviderEntity($name)
	{
		static $providerEntityProxy = array();
		if (array_key_exists($name, $providerEntityProxy))
		{
			return $providerEntityProxy[$name];
		}

		if (class_exists($name))
		{
			$productProvider = new $name();
			if ($productProvider instanceof Sale\SaleProviderBase || array_key_exists("IBXSaleProductProvider", class_implements($name)))
			{
				$providerEntityProxy[$name] = $productProvider;
				return $productProvider;
			}
		}

		return null;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public static function isProviderCallbackFunction($name)
	{
		return (array_key_exists("IBXSaleProductProvider", class_implements($name)));
	}


	/**
	 * @param $providerName
	 *
	 * @return string
	 */
	private static function clearProviderName($providerName)
	{
		if (substr($providerName, 0, 1) == "\\")
		{
			$providerName = substr($providerName, 1);
		}

		return trim($providerName);
	}

	/**
	 * @internal
	 * @param $value
	 */
	public static function setIgnoreErrors($value)
	{
		static::$ignoreErrors = ($value === true);
	}

	/**
	 * @internal
	 * @return bool
	 */
	public static function isIgnoreErrors()
	{
		return static::$ignoreErrors;
	}

}