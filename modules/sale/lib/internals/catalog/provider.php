<?php


namespace Bitrix\Sale\Internals\Catalog;


use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Internals\PoolQuantity;

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
	 */
	public static function getAvailableQuantityAndPriceByBasketItem(Sale\BasketItemBase $basketItem, array $context = array())
	{
		$result = new Sale\Result();

		/** @var Sale\Basket $basket */
		$basket = $basketItem->getCollection();

		$order = $basket->getOrder();

		if (empty($context) && !$order)
		{
			$context = $basket->getContext();
		}

		if (empty($context) && $order)
		{
			$context = self::prepareContext($order, $context);
		}

		$r = self::checkContext($context);
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

			$providerName = self::clearProviderName($providerName);

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
	public static function tryReserveShipment(Sale\Shipment $shipment)
	{
		$context = self::prepareContext($shipment->getOrder());
		$r = self::checkContext($context);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$shipmentItemList = [];
		/** @var Sale\ShipmentItem $item */
		foreach ($shipment->getShipmentItemCollection() as $item)
		{
			$basketItem = $item->getBasketItem();
			if ($basketItem->isReservableItem())
			{
				$shipmentItemList[] = $item;
			}
		}

		return self::tryReserveShipmentItemArray($shipmentItemList, $context);
	}

	/**
	 * @param array $shipmentItemList
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	protected static function tryReserveShipmentItemArray(array $shipmentItemList, array $context)
	{
		$result = new Sale\Result();

		/** @var Sale\ShipmentItem $shipmentItem */
		$shipmentItem = current($shipmentItemList);
		if (!$shipmentItem)
		{
			return $result;
		}

		$availableQuantityList = [];

		$needQuantityList = self::getNeedQuantityByShipmentItemList($shipmentItemList);
		if (!$needQuantityList)
		{
			return $result;
		}

		$creator = Sale\Internals\ProviderCreator::create($context);
		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$productData = $creator->createItemForReserveByShipmentItem($shipmentItem);
			$creator->addProductData($productData);
		}

		$r = $creator->getAvailableQuantityByStore();
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (array_key_exists('AVAILABLE_QUANTITY_LIST_BY_STORE', $data))
			{
				$availableQuantityList = $data['AVAILABLE_QUANTITY_LIST_BY_STORE'];
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

		if (!$result->isSuccess())
		{
			return $result;
		}

		$applyItemsList = [];

		$pool = PoolQuantity::getInstance($shipmentItem->getCollection()->getShipment()->getOrder()->getInternalId());

		foreach ($availableQuantityList as $providerName => $productAvailableQuantityList)
		{
			$providerName = trim($providerName);
			foreach ($productAvailableQuantityList as $productId => $quantityByStore)
			{
				if (array_key_exists($productId, $needQuantityList[$providerName]))
				{
					if (Sale\Configuration::getProductReservationCondition() !== Sale\Reservation\Configuration\ReserveCondition::ON_SHIP)
					{
						if (!isset($applyItemsList[$providerName][$productId]))
						{
							$applyItemsList[$providerName][$productId] = [];
						}

						foreach ($quantityByStore as $storeId => $quantity)
						{
							if ($quantity < $needQuantityList[$providerName][$productId][$storeId])
							{
								$poolQuantity = (float)$pool->getByStore(
									PoolQuantity::POOL_QUANTITY_TYPE,
									$productId,
									$storeId
								);

								$delta = $needQuantityList[$providerName][$productId][$storeId] - $quantity;

								if ($delta < $poolQuantity)
								{
									$applyItemsList[$providerName][$productId][$storeId] = $needQuantityList[$providerName][$productId][$storeId];
								}
								elseif ($poolQuantity > 0)
								{
									$applyItemsList[$providerName][$productId][$storeId] = $quantity + $poolQuantity;
								}
								else
								{
									$applyItemsList[$providerName][$productId][$storeId] = $quantity;
								}
							}
							else
							{
								$applyItemsList[$providerName][$productId][$storeId] = $quantity;
							}
						}
					}
				}
				else
				{
					/** @var Sale\ShipmentItem $shipmentItem */
					foreach ($shipmentItemList as $shipmentItem)
					{
						$basketItem = $shipmentItem->getBasketItem();

						if ($basketItem->getProductId() === $productId)
						{
							$result->addWarning(
								new Sale\ResultWarning(
									Main\Localization\Loc::getMessage(
										'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY',
										['#PRODUCT_NAME#' => $basketItem->getField('NAME')]
									),
									'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY'
								)
							);

							break;
						}
					}
				}
			}
		}

		if (!empty($applyItemsList))
		{
			$shipmentItemMap = self::createProductShipmentItemMap($shipmentItemList);

			self::setQuantityAfterReserve($shipmentItemMap, $applyItemsList);
		}

		return $result;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public static function tryReserveShipmentItem(Sale\ShipmentItem $shipmentItem, array $context = array())
	{
		/** @var Sale\Order $order */
		$order = $shipmentItem->getCollection()->getShipment()->getOrder();

		$context = self::prepareContext($order, $context);
		$r = self::checkContext($context);
		if (!$r->isSuccess())
		{
			return $r;
		}

		return self::tryReserveShipmentItemArray([$shipmentItem], $context);
	}

	/**
	 * @param Sale\ReserveQuantity $reserveQuantity
	 * @param $quantity
	 * @param array $context
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 */
	public static function tryReserve(Sale\ReserveQuantity $reserveQuantity, array $context = array())
	{
		$result = new Sale\Result();

		$basketItem = $reserveQuantity->getCollection()->getBasketItem();

		$order = $basketItem->getBasket()->getOrder();

		$context = self::prepareContext($order, $context);
		$r = self::checkContext($context);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$creator = Sale\Internals\ProviderCreator::create($context);

		$productData = $creator->createItemForReserve($reserveQuantity);
		$creator->addProductData($productData);

		$availableQuantityList = [];

		$r = $creator->getAvailableQuantityByStore();
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (array_key_exists('AVAILABLE_QUANTITY_LIST_BY_STORE', $data))
			{
				$availableQuantityList = $data['AVAILABLE_QUANTITY_LIST_BY_STORE'];
			}
		}
		else
		{
			return $result->addErrors($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());

		$providerName = $basketItem->getProviderName();
		$providerName = self::clearProviderName($providerName);

		$storeId = $reserveQuantity->getStoreId();
		$availableQuantity = $availableQuantityList[$providerName][$basketItem->getProductId()][$storeId] ?? 0;

		if ($availableQuantity < $productData['QUANTITY'])
		{
			$result->addError(
				new Sale\ResultError(
					Main\Localization\Loc::getMessage('SALE_PROVIDER_RESERVE_WRONG_AVAILABLE_QUANTITY'),
					'SALE_PROVIDER_RESERVE_WRONG_AVAILABLE_QUANTITY'
				)
			);
		}
		else
		{
			$pool->addByStore(
				Sale\Internals\PoolQuantity::POOL_RESERVE_TYPE,
				$basketItem->getProductId(),
				$storeId,
				$productData['QUANTITY']
			);

			$foundItem = false;
			$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $basketItem->getProductId());
			if (!empty($poolItems))
			{
				foreach ($poolItems as $poolItem)
				{
					if (
						$poolItem instanceof Sale\ReserveQuantity
						&& $poolItem->getInternalIndex() === $reserveQuantity->getInternalIndex()
						&& $poolItem->getCollection()->getBasketItem()->getInternalIndex() === $reserveQuantity->getCollection()->getBasketItem()->getInternalIndex()
					)
					{
						$foundItem = true;
						break;
					}
				}
			}

			if (!$foundItem)
			{
				Sale\Internals\ItemsPool::add(
					$order->getInternalId(),
					$basketItem->getProductId(),
					$reserveQuantity
				);
			}
		}

		return $result;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @return Sale\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public static function tryUnreserveShipmentItem(Sale\ShipmentItem $shipmentItem) : Sale\Result
	{
		$result = new Sale\Result();

		/** @var Sale\Order $order */
		$order = $shipmentItem->getCollection()->getShipment()->getOrder();

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\BasketItem $basketItem */
		$basketItem = $shipmentItem->getBasketItem();

		$productId = $basketItem->getProductId();

		$reservedQuantity = $shipmentItem->getReservedQuantity();

		if ($reservedQuantity == 0)
		{
			return $result;
		}

		$pool->add(Sale\Internals\PoolQuantity::POOL_RESERVE_TYPE, $productId, -1 * $reservedQuantity);

		$foundItem = false;
		$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $productId);
		if (!empty($poolItems))
		{
			/** @var Sale\ShipmentItem $poolItem */
			foreach ($poolItems as $poolItem)
			{
				if (
					$poolItem instanceof Sale\ShipmentItem
					&& $poolItem->getInternalIndex() == $shipmentItem->getInternalIndex()
				)
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

		$r = $shipmentItem->setField('RESERVED_QUANTITY', $shipmentItem->getReservedQuantity() + -1 * $reservedQuantity);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param array $shipmentItemList
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	private static function getNeedQuantityByShipmentItemList(array $shipmentItemList) : array
	{
		$needQuantityList = [];

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$quantityList = self::getNeedQuantityByShipmentItem($shipmentItem);
			$providerName = key($quantityList);
			$productId = key($quantityList[$providerName]);

			foreach ($quantityList[$providerName][$productId] as $storeId => $quantity)
			{
				if (!isset($needQuantityList[$providerName][$productId][$storeId]))
				{
					$needQuantityList[$providerName][$productId][$storeId] = 0;
				}

				$needQuantityList[$providerName][$productId][$storeId] += $quantity;
			}
		}

		return $needQuantityList;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @return \array[][]
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 */
	private static function getNeedQuantityByShipmentItem(Sale\ShipmentItem $shipmentItem) : array
	{
		$basketItem = $shipmentItem->getBasketItem();

		$productId = $basketItem->getProductId();

		$providerName = $basketItem->getProviderName();
		$providerName = self::clearProviderName($providerName);

		$quantity = $shipmentItem->getQuantity();
		if ($quantity == 0)
		{
			return [
				$providerName => [
					$productId => [
						Sale\Configuration::getDefaultStoreId() => -$shipmentItem->getReservedQuantity(),
					],
				],
			];
		}
		else
		{
			$quantityByStore = [];

			/** @var Sale\ShipmentItemStoreCollection $shipmentItemStoreCollection */
			$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
			if ($shipmentItemStoreCollection)
			{
				/** @var Sale\ShipmentItemStore $shipmentItemStore */
				foreach ($shipmentItemStoreCollection as $shipmentItemStore)
				{
					if (!isset($quantityByStore[$shipmentItemStore->getStoreId()]))
					{
						$quantityByStore[$shipmentItemStore->getStoreId()] = 0;
					}

					$quantityByStore[$shipmentItemStore->getStoreId()] += $shipmentItemStore->getQuantity();

					$quantity -= $shipmentItemStore->getQuantity();
				}
			}

			if ($quantity)
			{
				$storeId = Sale\Configuration::getDefaultStoreId();

				if (!isset($quantityByStore[$storeId]))
				{
					$quantityByStore[$storeId] = 0;
				}

				$quantityByStore[$storeId] += $quantity;
			}

			return [
				$providerName => [
					$productId => $quantityByStore,
				],
			];
		}
	}

	/**
	 * @param Sale\Shipment $shipment
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public static function tryUnreserveShipment(Sale\Shipment $shipment)
	{
		$result = new Sale\Result();

		/** @var Sale\Order $order */
		$order = $shipment->getOrder();

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			/** @var Sale\BasketItem $basketItem */
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}

			if ((int)$shipmentItem->getReservedQuantity() === 0)
			{
				continue;
			}

			$shipmentItemReserveQuantity = $shipmentItem->getReservedQuantity();

			/** @var Sale\ReserveQuantityCollection $reserveCollection */
			$reserveCollection = $basketItem->getReserveQuantityCollection();
			if (!$reserveCollection)
			{
				continue;
			}

			/** @var Sale\ShipmentItemStoreCollection $shipmentItemStoreCollection */
			$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
			if ($shipmentItemStoreCollection)
			{
				/** @var Sale\ShipmentItemStore $itemStore */
				foreach ($shipmentItemStoreCollection as $itemStore)
				{
					if ($shipmentItemReserveQuantity == 0)
					{
						break;
					}

					/** @var Sale\ReserveQuantity $reserve */
					foreach ($reserveCollection as $reserve)
					{
						if ($reserve->getStoreId() !== $itemStore->getStoreId())
						{
							continue;
						}

						// try to guess reserved quantity on shipment item store
						if ($shipmentItemReserveQuantity > $itemStore->getQuantity())
						{
							$reserveStoreQuantity =  $itemStore->getQuantity();
						}
						else
						{
							$reserveStoreQuantity =  $shipmentItemReserveQuantity;
						}

						if ($reserveStoreQuantity >= $reserve->getQuantity())
						{
							$poolQuantity = $reserve->getQuantity();

							$reserve->deleteNoDemand();
						}
						else
						{
							$poolQuantity = $reserveStoreQuantity;

							$reserve->setFieldNoDemand('QUANTITY', $reserve->getQuantity() - $reserveStoreQuantity);
						}

						$shipmentItemReserveQuantity -= $reserveStoreQuantity;
						$pool->addByStore(
							PoolQuantity::POOL_RESERVE_TYPE,
							$basketItem->getProductId(),
							$itemStore->getStoreId(),
							-$poolQuantity
						);
					}
				}
			}


			if ($shipmentItemReserveQuantity > 0)
			{
				$storeId = Sale\Configuration::getDefaultStoreId();

				foreach ($reserveCollection as $reserve)
				{
					if ($shipmentItemReserveQuantity == 0)
					{
						break;
					}

					if ($reserve->getStoreId() !== $storeId)
					{
						continue;
					}

					if ($shipmentItemReserveQuantity >= $reserve->getQuantity())
					{
						$poolQuantity = $reserve->getQuantity();
						$shipmentItemReserveQuantity -= $poolQuantity;

						$reserve->deleteNoDemand();
					}
					else
					{
						$reserve->setFieldNoDemand('QUANTITY', $reserve->getQuantity() - $shipmentItemReserveQuantity);
						$shipmentItemReserveQuantity = 0;

						$poolQuantity = $shipmentItemReserveQuantity;
					}

					$pool->addByStore(
						PoolQuantity::POOL_RESERVE_TYPE,
						$basketItem->getProductId(),
						$storeId,
						-$poolQuantity
					);
				}
			}

			$shipmentItem->getFields()->set('RESERVED_QUANTITY', 0);

			if (!Sale\Internals\ActionEntity::isTypeExists(
					$order->getInternalId(),
					Sale\Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY
				)
			)
			{
				Sale\Internals\ActionEntity::add(
					$order->getInternalId(),
					Sale\Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY,
					[
						'METHOD' => 'Bitrix\Sale\Shipment::updateReservedFlag',
						'PARAMS' => [$shipment],
					]
				);
			}

			$foundItem = false;
			$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $basketItem->getProductId());
			if (!empty($poolItems))
			{
				/** @var Sale\Shipment $poolItem */
				foreach ($poolItems as $poolItem)
				{
					if (
						$poolItem instanceof Sale\ShipmentItem
						&& $poolItem->getInternalIndex() == $shipmentItem->getInternalIndex()
					)
					{
						$foundItem = true;
						break;
					}
				}
			}

			if (!$foundItem)
			{
				Sale\Internals\ItemsPool::add($order->getInternalId(), $basketItem->getProductId(), $shipmentItem);
			}
		}

		return $result;
	}

	/**
	 * @param array $shipmentItemList
	 *
	 * @return array
	 */
	private static function createProductShipmentItemMap(array $shipmentItemList)
	{
		$result = [];

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();

			$providerName = self::clearProviderName($basketItem->getProviderName());

			$result[$providerName][$basketItem->getProductId()][$shipmentItem->getInternalIndex()] = $shipmentItem;
		}

		return $result;
	}

	/**
	 * @param Sale\Order $order
	 * @param array $shipmentItemMap
	 * @param array $availableQuantityList
	 *
	 * @return Sale\Result
	 * @throws \Exception
	 */
	private static function setQuantityAfterReserve(array $shipmentItemMap, array $availableQuantityList)
	{
		$result = new Sale\Result();

		foreach ($availableQuantityList as $providerName => $productsList)
		{
			foreach ($productsList as $productId => $reservedQuantityByStore)
			{
				foreach ($reservedQuantityByStore as $storeId => $reservedQuantity)
				{
					$r = self::setReserveQuantityByProduct($shipmentItemMap[$providerName][$productId], $storeId, $reservedQuantity);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}

		return $result;
	}

	private static function setReserveQuantityByProduct(array $shipmentItemList, $storeId, $quantity) : Sale\Result
	{
		$result = new Sale\Result();

		if ($quantity == 0)
		{
			return $result;
		}

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();
			if (!$basketItem->isReservableItem())
			{
				continue;
			}

			/** @var Sale\ReserveQuantityCollection $reserveQuantityCollection */
			$reserveQuantityCollection = $basketItem->getReserveQuantityCollection();
			if (!$reserveQuantityCollection)
			{
				continue;
			}

			$productId = $basketItem->getProductId();

			/** @var Sale\Order $order */
			$order = $basketItem->getBasket()->getOrder();

			$pool = PoolQuantity::getInstance($order->getInternalId());

			$reserve = null;

			/** @var Sale\ReserveQuantity $item */
			foreach ($reserveQuantityCollection as $item)
			{
				if ($item->getStoreId() === $storeId)
				{
					$reserve = $item;
					break;
				}
			}

			if ($reserve === null)
			{
				$reserve = $reserveQuantityCollection->create();
				$reserve->setStoreId($storeId);
			}

			if ($quantity < 0)
			{
				$settableQuantity = $quantity;
				if ($shipmentItem->getReservedQuantity() < $quantity)
				{
					$settableQuantity = $shipmentItem->getReservedQuantity();
				}
			}
			else
			{
				$needQuantity = $basketItem->getQuantity() - $reserve->getQuantity();
				$settableQuantity = (abs($needQuantity) >= abs($quantity)) ? $quantity : $needQuantity;
			}

			$reserve->setFieldNoDemand('QUANTITY', $reserve->getQuantity() + $settableQuantity);

			self::applyReserveToShipmentItem($shipmentItem, $settableQuantity);

			$quantity -= $settableQuantity;

			$pool->addByStore(Sale\Internals\PoolQuantity::POOL_RESERVE_TYPE, $productId, $storeId, $settableQuantity);

			$foundItem = false;
			$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $productId);
			if (!empty($poolItems))
			{
				/** @var Sale\ShipmentItem $poolItem */
				foreach ($poolItems as $poolItem)
				{
					if (
						$poolItem instanceof Sale\ShipmentItem
						&& $poolItem->getInternalIndex() == $shipmentItem->getInternalIndex()
					)
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

		return $result;
	}

	protected static function applyReserveToShipmentItem(Sale\ShipmentItem $item, $quantity)
	{
		$item->getFields()->set('RESERVED_QUANTITY', $item->getReservedQuantity() + $quantity);

		$shipment = $item->getCollection()->getShipment();
		$order = $shipment->getOrder();

		if (!Sale\Internals\ActionEntity::isTypeExists(
				$order->getInternalId(),
				Sale\Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_RESERVED_QUANTITY
			)
		)
		{
			Sale\Internals\ActionEntity::add(
				$order->getInternalId(),
				Sale\Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_RESERVED_QUANTITY,
				[
					'METHOD' => 'Bitrix\Sale\Shipment::updateReservedFlag',
					'PARAMS' => [$shipment],
				]
			);
		}

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

		$order = $shipment->getOrder();

		$context = self::prepareContext($order, $context);

		$pool = PoolQuantity::getInstance($order->getInternalId());

		/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
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
			$creator->addProductData($shipmentProductData);
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

			if (self::isIgnoreErrors())
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
				foreach ($tryShipProductList as &$productList)
				{
					$productList = array_fill_keys(array_keys($productList), true);
				}
			}

			$creator->setItemsResultAfterTryShip($pool, $tryShipProductList);
		}

		return $result;
	}

	public static function deliver(Sale\Shipment $shipment)
	{
		$result = new Sale\Result();

		$order = $shipment->getOrder();

		$context = array(
			'USER_ID' => $order->getUserId(),
			'SITE_ID' => $order->getSiteId(),
		);

		$creator = Sale\Internals\ProviderCreator::create($context);

		foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			$creator->addShipmentItem($shipmentItem);
		}

		$r = $creator->deliver();
		if ($r->isSuccess())
		{
			$r = $creator->createItemsResultAfterDeliver($r);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (
					!empty($data['RESULT_AFTER_DELIVER_LIST'])
					&& is_array($data['RESULT_AFTER_DELIVER_LIST'])
				)
				{
					$result->setData($data['RESULT_AFTER_DELIVER_LIST']);
				}
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @internal
	 * @param Sale\ShipmentItem $shipmentItem
	 *
	 * @return array|bool
	 * @throws Main\ObjectNotFoundException
	 */
	public static function createMapShipmentItemStoreData(Sale\ShipmentItem $shipmentItem, $needUseReserve = true)
	{
		$resultList = array();

		/** @var Sale\BasketItem $basketItem */
		$basketItem = $shipmentItem->getBasketItem();
		if (!$basketItem->isReservableItem())
		{
			return false;
		}

		/** @var Sale\ReserveQuantityCollection $reserveQuantityCollection */
		$reserveQuantityCollection = $basketItem->getReserveQuantityCollection();
		if (!$reserveQuantityCollection)
		{
			return false;
		}

		$reserveQuantityStoreList = [];

		$countBarcode = 0;

		/** @var Sale\ShipmentItemStoreCollection $shipmentItemStoreCollection */
		$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
		if ($shipmentItemStoreCollection)
		{
			/** @var Sale\ShipmentItemStore $shipmentItemStore */
			foreach ($shipmentItemStoreCollection as $shipmentItemStore)
			{
				$productId = $basketItem->getProductId();

				$storeId = $shipmentItemStore->getStoreId();

				if (!isset($reserveQuantityStoreList[$storeId]))
				{
					$reserveQuantityStoreList[$storeId] = $reserveQuantityCollection->getQuantityByStoreId($shipmentItemStore->getStoreId());
				}

				if (!isset($resultList[$storeId]))
				{
					$resultList[$storeId] = [
						'PRODUCT_ID' => $productId,
						'QUANTITY' => 0,
						'RESERVED_QUANTITY' => 0,
						'STORE_ID' => $storeId,
						'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
						'BARCODE' => [],
					];
				}

				$barcodeId = ($shipmentItemStore->getId() > 0)? $shipmentItemStore->getId() : 'n'.$countBarcode;
				$countBarcode++;
				$resultList[$storeId]['QUANTITY'] += $basketItem->isBarcodeMulti()? 1 : $shipmentItemStore->getQuantity();
				$resultList[$storeId]['BARCODE'][$barcodeId] = $shipmentItemStore->getBarcode();

				if ($needUseReserve)
				{
					if ($reserveQuantityStoreList[$storeId] > $resultList[$storeId]['QUANTITY'])
					{
						$resultList[$storeId]['RESERVED_QUANTITY'] = $resultList[$storeId]['QUANTITY'];
					}
					elseif ($reserveQuantityStoreList[$storeId] > 0)
					{
						$resultList[$storeId]['RESERVED_QUANTITY'] = $reserveQuantityStoreList[$storeId];
					}

					$reserveQuantityStoreList[$storeId] -= $resultList[$storeId]['RESERVED_QUANTITY'];
				}
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

			$map = self::createMapShipmentItemStoreData($shipmentItem);
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

		$context = self::prepareContext($order, $context);

		$r = self::checkContext($context);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		$pool = PoolQuantity::getInstance($order->getInternalId());

		$rulesMap = Sale\Internals\ShipmentRules::createOrderRuleMap($order, $pool);

		if (empty($rulesMap))
		{
			return $result;
		}

		$r = Sale\Internals\ShipmentRules::saveRules($rulesMap, $context);
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
	 * @param Sale\OrderBase $order
	 * @param array $context
	 *
	 * @return array
	 */
	private static function prepareContext(Sale\OrderBase $order, array $context = array())
	{
		if (empty($context))
		{
			$context = [
				'SITE_ID' => $order->getSiteId(),
				'USER_ID' => $order->getUserId(),
				'CURRENCY' => $order->getCurrency(),
			];
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
			$provider = self::getProviderEntity($name);
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

		if (
			class_exists($name)
			&& (
				is_subclass_of($name, Sale\SaleProviderBase::class)
				|| is_subclass_of($name, \IBXSaleProductProvider::class)
			)
		)
		{
			$providerEntityProxy[$name] = new $name();
			return $providerEntityProxy[$name];
		}

		return null;
	}


	/**
	 * @param $providerName
	 *
	 * @return string
	 */
	private static function clearProviderName($providerName)
	{
		if (empty($providerName) || !is_string($providerName))
		{
			return '';
		}

		if (mb_substr($providerName, 0, 1) === "\\")
		{
			$providerName = mb_substr($providerName, 1);
		}

		return trim($providerName);
	}

	/**
	 * @internal
	 * @param $value
	 */
	public static function setIgnoreErrors($value)
	{
		self::$ignoreErrors = ($value === true);
	}

	/**
	 * @internal
	 * @return bool
	 */
	public static function isIgnoreErrors()
	{
		return self::$ignoreErrors;
	}

}
