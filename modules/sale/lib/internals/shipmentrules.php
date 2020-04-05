<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class ShipmentRules
 * @package Bitrix\Sale\Internals
 */
class ShipmentRules
{
	/**
	 * ShipmentRules constructor.
	 */
	protected function __construct()
	{
	}

	/**
	 * @param Sale\Order $order
	 * @param PoolQuantity $pool
	 *
	 * @return array
	 */
	public static function createOrderRuleMap(Sale\Order $order, PoolQuantity $pool)
	{
		$resultList = array();

		$items = ItemsPool::getPoolByCode($order->getInternalId());
		if (empty($items))
		{
			return $resultList;
		}

		foreach ($items as $productId => $shipmentItemList)
		{
			foreach ($shipmentItemList as $shipmentItem)
			{
				$shipmentItemRule = static::createReserveRule($shipmentItem, $pool);
				if (!empty($shipmentItemRule) && is_array($shipmentItemRule))
				{
					$resultList[] = $shipmentItemRule;
				}

				$shipmentItemRule = static::createShipRule($shipmentItem, $pool);
				if (!empty($shipmentItemRule) && is_array($shipmentItemRule))
				{
					$resultList[] = $shipmentItemRule;
				}
			}
		}
		return $resultList;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @param PoolQuantity $pool
	 *
	 * @return array|bool
	 * @throws Main\ObjectNotFoundException
	 */
	private static function createShipRule(Sale\ShipmentItem $shipmentItem, PoolQuantity $pool)
	{
		$basketItem = $shipmentItem->getBasketItem();
		if (!$basketItem)
		{
			throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
		}

		if ($basketItem->isBundleParent())
		{
			return false;
		}

		/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItem->getCollection();

		if (!$shipmentItemCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		$shipment = $shipmentItemCollection->getShipment();
		if (!$shipment)
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		$poolQuantitiesList = $pool->getQuantities(PoolQuantity::POOL_QUANTITY_TYPE);
		if (empty($poolQuantitiesList))
		{
			return false;
		}

		$productId = $basketItem->getProductId();
		$needQuantity = (float)$shipmentItem->getQuantity();

		$rule = array(
			'SHIPMENT_ITEM' => $shipmentItem,
			'PRODUCT_ID' => $productId,
			'PROVIDER_NAME' => $basketItem->getProvider(),
			'NEED_RESERVE' => $shipmentItem->needReserve(),
		);

		$storeData = Sale\Internals\Catalog\Provider::createMapShipmentItemStoreData($shipmentItem);
		if (!empty($storeData))
		{
			$shipmentItemIndex = $shipmentItem->getInternalIndex();
			$rule['STORE_DATA'] = array(
				$shipmentItemIndex => $storeData
			);
		}

		if (array_key_exists($productId, $poolQuantitiesList))
		{
			if ($shipment->needShip() === Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_SHIP)
			{
				$needQuantity *= -1;
			}

			$shipQuantity = $poolQuantitiesList[$productId] - $needQuantity;
			$rule['ACTION'][PoolQuantity::POOL_QUANTITY_TYPE] = $needQuantity;

			$pool->set(PoolQuantity::POOL_QUANTITY_TYPE, $productId, $shipQuantity);
		}

		return $rule;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @param PoolQuantity $pool
	 *
	 * @return array|bool
	 * @throws Main\ObjectNotFoundException
	 */
	private static function createReserveRule(Sale\ShipmentItem $shipmentItem, PoolQuantity $pool)
	{
		$basketItem = $shipmentItem->getBasketItem();
		if (!$basketItem)
		{
			throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
		}

		/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItem->getCollection();

		if (!$shipmentItemCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		$shipment = $shipmentItemCollection->getShipment();
		if (!$shipment)
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		$poolReservationList = $pool->getQuantities(PoolQuantity::POOL_RESERVE_TYPE);

		if (empty($poolReservationList))
		{
			return false;
		}

		$order = $shipment->getParentOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$productId = $basketItem->getProductId();
		$needQuantity = floatval($shipmentItem->getNeedReserveQuantity());

		$rule = array(
			'SHIPMENT_ITEM' => $shipmentItem,
			'PRODUCT_ID' => $productId,
			'PROVIDER_NAME' => $basketItem->getProvider(),
			'STORE' => array()
		);

		if ($shipment->needShip() === Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP)
		{
			return false;
		}

		if (isset($poolReservationList[$productId]))
		{
			if ($needQuantity == 0)
			{
				$needQuantity = $poolReservationList[$productId];
			}

			$reserveQuantity = $poolReservationList[$productId] - $needQuantity;
			$rule['ACTION'][PoolQuantity::POOL_RESERVE_TYPE] = $needQuantity;

			$pool->set(PoolQuantity::POOL_RESERVE_TYPE, $productId, $reserveQuantity);
		}

		return $rule;
	}


	/**
	 * @param array $rules
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function saveRules(array $rules, array $context)
	{
		$result = new Sale\Result();
		$shipProductsList = array();

		foreach ($rules as $ruleData)
		{
			if (empty($ruleData['ACTION']))
				continue;

			foreach ($ruleData['ACTION'] as $action => $quantity)
			{
				if ($quantity == 0)
					continue;

				$fields = $ruleData;
				$fields['QUANTITY'] = $quantity;
				unset($fields['ACTION']);

				if ($action == PoolQuantity::POOL_QUANTITY_TYPE)
				{
					$shipProductsList[] = $fields;
				}
				elseif ($action == PoolQuantity::POOL_RESERVE_TYPE)
				{
					$reserveProductsList[] = $fields;
				}
			}
		}

		if (!empty($reserveProductsList))
		{
			$creator = Sale\Internals\ProviderCreator::create($context);
			/** @var Sale\ShipmentItem $shipmentItem */
			foreach ($reserveProductsList as $reserveProductData)
			{
				$creator->addShipmentProductData($reserveProductData);
			}

			$r = $creator->reserve();
			if ($r->isSuccess())
			{
				$r = $creator->setItemsResultAfterReserve($r);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

		}
		
		if (!empty($shipProductsList))
		{
			$creator = Sale\Internals\ProviderCreator::create($context);
			/** @var Sale\ShipmentItem $shipmentItem */
			foreach ($shipProductsList as $shipProductData)
			{
				$creator->addShipmentProductData($shipProductData);
			}

			$r = $creator->ship();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}

			$r = $creator->setItemsResultAfterShip($r);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}


		return $result;
	}

}