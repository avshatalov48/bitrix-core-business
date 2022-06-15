<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */

namespace Bitrix\Sale;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals;
use Bitrix\Currency;
use Bitrix\Sale\Reservation\Configuration\ReserveCondition;

Loc::loadMessages(__FILE__);

/**
 * Class ProviderBase
 * @package Bitrix\Sale
 */
abstract class ProviderBase
{
	/** @var Internals\Pool[] */
	protected static $reservationPool = array();

	/** @var array  */
	protected static $hitCache = array();

	/** @var array  */
	protected static $trustData = array();

	/** @var bool */
	protected static $useReadTrustData = false;

	/** @var Internals\Pool[] */
	protected static $quantityPool = array();

	static $productData = array();

	const POOL_ACTION_RESERVATION = "RESERVE";
	const POOL_ACTION_SHIP = "SHIP";

	/**
	 * @param $key
	 * @return Internals\Pool
	 */
	protected static function getReservationPool($key)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		return $pool->getByType(Internals\PoolQuantity::POOL_RESERVE_TYPE);
	}

	/**
	 * @param $key
	 *
	 * @return Internals\Pool
	 */
	protected static function resetReservationPool($key)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		$pool->reset(Internals\PoolQuantity::POOL_RESERVE_TYPE);
	}

	/**
	 * @param $key
	 * @param BasketItem $item
	 * @return float|null
	 */
	public static function getReservationPoolItem($key, BasketItem $item)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		return $pool->get(Internals\PoolQuantity::POOL_RESERVE_TYPE, $item->getField('PRODUCT_ID'));
	}

	/**
	 * @param $key
	 * @param BasketItem $item
	 * @param $value
	 */
	protected static function setReservationPoolItem($key, BasketItem $item, $value)
	{
		$poolInstance = Internals\PoolQuantity::getInstance($key);
		$code = $item->getBasketCode()."|".$item->getField('MODULE')."|".$item->getField('PRODUCT_ID');
		$poolInstance->set(Internals\PoolQuantity::POOL_RESERVE_TYPE, $code, $value);

		$pool = $poolInstance->getByType(Internals\PoolQuantity::POOL_RESERVE_TYPE);
		$pool->addItem($code, $item);
	}

	/**
	 * @param $key
	 * @param BasketItem $item
	 * @param $value
	 */
	protected static function addReservationPoolItem($key, BasketItem $item, $value)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		$pool->add(Internals\PoolQuantity::POOL_RESERVE_TYPE, $item->getField('PRODUCT_ID'), $value);
	}

	/**
	 * @param $key
	 * @return Internals\Pool
	 */
	protected static function getQuantityPool($key)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		return $pool->getByType(Internals\PoolQuantity::POOL_QUANTITY_TYPE);
	}

	/**
	 * @param $key
	 */
	protected static function resetQuantityPool($key)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		$pool->reset(Internals\PoolQuantity::POOL_QUANTITY_TYPE);
	}

	/**
	 * @param $key
	 * @param BasketItem $item
	 * @return float|null
	 */
	public static function getQuantityPoolItem($key, BasketItem $item)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		return $pool->get(Internals\PoolQuantity::POOL_QUANTITY_TYPE, $item->getField('PRODUCT_ID'));
	}

	/**
	 * @param $key
	 * @param BasketItem $item
	 * @param $value
	 */
	protected static function setQuantityPoolItem($key, BasketItem $item, $value)
	{
		$code = $item->getBasketCode()."|".$item->getField('MODULE')."|".$item->getField('PRODUCT_ID');
		$poolInstance = Internals\PoolQuantity::getInstance($key);
		$poolInstance->set(Internals\PoolQuantity::POOL_RESERVE_TYPE, $code, $value);

		$pool = $poolInstance->getByType(Internals\PoolQuantity::POOL_RESERVE_TYPE);
		$pool->addItem($code, $item);
	}

	/**
	 * @internal
	 *
	 * @param $key
	 * @param BasketItem $item
	 * @param $value
	 */
	public static function addQuantityPoolItem($key, BasketItem $item, $value)
	{
		$pool = Internals\PoolQuantity::getInstance($key);
		$pool->add(Internals\PoolQuantity::POOL_QUANTITY_TYPE, $item->getField('PRODUCT_ID'), $value);
	}

	/**
	 * @param Order $order
	 * @return Result
	 * @throws NotImplementedException
	 * @throws SystemException
	 */
	public static function onOrderSave(Order $order)
	{
		$result = new Result();

		static::resetTrustData($order->getSiteId());

		/** @var Result $r */
		$r = Internals\Catalog\Provider::save($order);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
			EntityMarker::addMarker($order, $order, $r);
			if ($order->getId() > 0)
			{
				Internals\OrderTable::update($order->getId(), array('MARKED' => 'Y'));
			}
		}

		static::refreshMarkers($order);

		return $result;
	}


	/**
	 * @internal
	 * @param BasketItemBase $basketItem
	 *
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 */
	public static function shipBasketItem(BasketItemBase $basketItem)
	{

		$result = new Result();

		/** @var Basket $basket */
		if (!$basket = $basketItem->getCollection())
		{
			throw new ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var Order $order */
		if (!$order = $basket->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = $order->getShipmentCollection();

		/** @var Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			$needShip = $shipment->needShip();
			if ($needShip === null)
				continue;

			$r = static::shipShipment($shipment);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
				EntityMarker::addMarker($order, $shipment, $r);
				if (!$shipment->isSystem())
				{
					$shipment->setField('MARKED', 'Y');
				}
			}
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return Result
	 * @throws NotSupportedException
	 * @throws SystemException
	 */
	public static function shipShipment(Shipment $shipment)
	{
		$result = new Result();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $shipment->getShipmentItemCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		$pool = Internals\PoolQuantity::getInstance($order->getInternalId());
		$quantityPool = $pool->getQuantities(Internals\PoolQuantity::POOL_QUANTITY_TYPE);
		if (empty($quantityPool))
		{
			return $result;
		}

		$reverse = false;

		$resultList = array();

		$basketList = static::getBasketFromShipmentItemCollection($shipmentItemCollection);

		$basketProviderMap = static::createProviderBasketMap($basketList, array('QUANTITY', 'RESERVED'));
		$basketProviderList = static::redistributeToProviders($basketProviderMap);
		$storeDataList = array();

		if (Configuration::useStoreControl())
		{

			/** @var Result $r */
			$r = static::getStoreDataFromShipmentItemCollection($shipmentItemCollection);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			else
			{
				$resultStoreData = $r->getData();
				if (!empty($resultStoreData['STORE_DATA_LIST']))
				{
					$storeDataList = $resultStoreData['STORE_DATA_LIST'];
				}

			}
		}

		if (!empty($basketProviderList))
		{
			foreach ($basketProviderList as $provider => $providerBasketItemList)
			{
				if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
				{

					foreach ($providerBasketItemList as $providerBasketItem)
					{

						if ($providerBasketItem['BASKET_ITEM']->isBundleParent())
						{
							continue;
						}

						$poolQuantity = static::getQuantityPoolItem($order->getInternalId(), $providerBasketItem['BASKET_ITEM']);

						if ($poolQuantity == 0)
							continue;

						if ($providerBasketItem['BASKET_ITEM']->getField('MODULE') != '')
						{
							$shipFields = array_merge($providerBasketItem, array(
								'DEDUCTION' => ($poolQuantity < 0)
							));

							$r = static::shipProductData($provider, $shipFields, $storeDataList);

							if (!$r->isSuccess())
							{
								$result->addErrors($r->getErrors());
							}
							$resultProductData = $r->getData();

						}
						else
						{
							$resultProductData['RESULT'] = true;
						}

						$resultList[$providerBasketItem['BASKET_CODE']] = $resultProductData;

						if (array_key_exists("RESULT", $resultProductData)
							&& $resultProductData['RESULT'] === false && $poolQuantity < 0)
						{
							$reverse = true;
							break;
						}

					}

				}
				elseif (class_exists($provider))
				{
					$context = array(
						'SITE_ID' => $order->getSiteId(),
						'CURRENCY' => $order->getCurrency(),
					);

					if ($order->getUserId() > 0)
					{
						$context['USER_ID'] = $order->getUserId();
					}
					else
					{
						global $USER;
						$context['USER_ID'] = $USER->getId();
					}

					$creator = Internals\ProviderCreator::create($context);
					/** @var ShipmentItem $shipmentItem */
					foreach ($shipmentItemCollection as $shipmentItem)
					{
						$basketItem = $shipmentItem->getBasketItem();
						$providerClass = $basketItem->getProviderEntity();

						if ($providerClass instanceof SaleProviderBase)
						{
							$shipmentProductData = $creator->createItemForShip($shipmentItem);
							$creator->addProductData($shipmentProductData);
						}
					}

					$r = $creator->ship();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					$r = $creator->setItemsResultAfterShip($r);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}

		if ($reverse === true)
		{
			static::reverseShipment($shipment, $resultList);
		}
		else
		{
			static::setShipmentItemReserved($shipment);
		}

		return $result;
	}

	/**
	 * @param $provider
	 * @param array $fields
	 * @param array $storeDataList
	 *
	 * @return Result
	 */
	public static function shipProductData($provider, array $fields, array $storeDataList = array())
	{
		$result = new Result();

		$quantity = $fields['QUANTITY'];
		$basketCode = $fields['BASKET_CODE'];

		/** @var BasketItem $basketItem */
		$basketItem = $fields['BASKET_ITEM'];

		/** @var BasketBase $basket */
		$basket = $basketItem->getCollection();

		/** @var OrderBase $order */
		$order = $basket->getOrder();

		$data = array(
			"BASKET_ITEM" => $basketItem,
			"PRODUCT_ID" => $fields['PRODUCT_ID'],
			"QUANTITY"   => $quantity,
			"PRODUCT_RESERVED"   => "N",
			'UNDO_DEDUCTION' => $fields['DEDUCTED']? 'N' : 'Y',
			'EMULATE' => 'N',
		);

		if ($data['UNDO_DEDUCTION'] == 'N')
		{
			$data['PRODUCT_RESERVED'] = "Y";
		}

		if (!empty($fields['RESERVED']))
		{
			$data['PRODUCT_RESERVED'] = $fields['RESERVED'] ? 'Y' : 'N';
		}

		$resultProductData = array();

		if (Configuration::useStoreControl())
		{

			if (!empty($storeDataList) && is_array($storeDataList) && isset($storeDataList[$basketCode]))
			{
				$data['STORE_DATA'] = $storeDataList[$basketCode];
			}

			if (!empty($data['STORE_DATA']))
			{
				$allBarcodeQuantity = 0;
				foreach($data['STORE_DATA'] as $basketShipmentItemStore)
				{
					$allBarcodeQuantity += $basketShipmentItemStore['QUANTITY'];
				}

				if ($quantity > $allBarcodeQuantity)
				{
					$result->addWarning(new ResultWarning(Loc::getMessage('SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY', array(
						'#PRODUCT_NAME#' => $basketItem->getField('NAME')
					)), 'SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY'));

					$resultProductData['RESULT'] = false;
				}
				elseif ($quantity < $allBarcodeQuantity)
				{
					$result->addWarning(new ResultWarning(Loc::getMessage('SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY', array(
						'#PRODUCT_NAME#' => $basketItem->getField('NAME')
					)), 'SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY'));

					$resultProductData['RESULT'] = false;
				}
			}

		}

		if (!isset($resultProductData['RESULT'])
			|| $resultProductData['RESULT'] !== false)
		{
			global $APPLICATION;
			$APPLICATION->ResetException();
			$resultProductData = $provider::DeductProduct($data);

			$result->setData($resultProductData);

			$needShip = $fields['DEDUCTED'];
			$oldException = $APPLICATION->GetException();
			if (!empty($oldException))
			{
				if ($needShip === true)
				{
					$result->addWarning( new ResultWarning($oldException->GetString(), $oldException->GetID()) );
				}
			}

			if (($oldException && $needShip === false) || !$oldException)
			{
				static::addQuantityPoolItem($order->getInternalId(), $basketItem, ($needShip? 1 : -1) * $quantity);
			}
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @param array $shippedList
	 * @throws NotSupportedException
	 * @throws SystemException
	 */
	private static function reverseShipment(Shipment $shipment, array $shippedList)
	{
		$needShip = $shipment->needShip();

		$correct = null;

		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		$basketList = static::getBasketFromShipmentItemCollection($shipmentItemCollection);

		$bundleIndexList = static::getBundleIndexFromShipmentItemCollection($shipmentItemCollection);

		$basketProviderMap = static::createProviderBasketMap($basketList, array('QUANTITY', 'RESERVED'));
		$basketProviderList = static::redistributeToProviders($basketProviderMap);

		if (Configuration::useStoreControl())
		{
			/** @var Result $r */
			$r = static::getStoreDataFromShipmentItemCollection($shipmentItemCollection);
		}

		if (!empty($basketProviderList))
		{
			foreach ($basketProviderList as $provider => $providerBasketItemList)
			{
				if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
				{

					foreach ($providerBasketItemList as $providerBasketItem)
					{
						if ($providerBasketItem['BASKET_ITEM']->isBundleParent())
						{
							continue;
						}

						$basketCode = $providerBasketItem['BASKET_CODE'];
						if (!isset($shippedList[$basketCode])
							|| (array_key_exists("RESULT", $shippedList[$basketCode]) && $shippedList[$basketCode]['RESULT'] === false))
						{
							if ($needShip && $shipment->isShipped())
							{
								$correct = true;
							}
							continue;
						}

						if ($providerBasketItem['BASKET_ITEM']->getField('MODULE') != '')
						{
							$data = array(
								"BASKET_ITEM" => $providerBasketItem['BASKET_ITEM'],
								"PRODUCT_ID" => $providerBasketItem['PRODUCT_ID'],
								"QUANTITY"   => $providerBasketItem['QUANTITY'],
								"PRODUCT_RESERVED"   => "Y",
								'UNDO_DEDUCTION' => $needShip? 'Y' : 'N',
								'EMULATE' => 'N',
							);

							if (Configuration::useStoreControl() && !empty($storeData) && is_array($storeData) && isset($storeData[$providerBasketItem['BASKET_CODE']]))
							{
								$data['STORE_DATA'] = $storeData[$providerBasketItem['BASKET_CODE']];

								$barcodeReverseList = array();

								if (!empty($shippedList[$basketCode]['BARCODE']) && is_array($shippedList[$basketCode]['BARCODE']))
								{
									foreach ($shippedList[$basketCode]['BARCODE'] as $barcodeValue => $barcodeShipped)
									{
										if ($barcodeShipped === true)
										{
											$barcodeReverseList[] = $barcodeValue;
										}
									}

									foreach ($data['STORE_DATA'] as $storeId => $barcodeData)
									{
										if (!empty($barcodeData['BARCODE']) && is_array($barcodeData['BARCODE']))
										{
											if (empty($barcodeReverseList))
											{
												$data['STORE_DATA'][$storeId]['BARCODE'] = array();
											}
											else
											{
												foreach ($barcodeData['BARCODE'] as $barcodeId => $barcodeValue)
												{
													if (!in_array($barcodeValue, $barcodeReverseList))
													{
														unset($data['STORE_DATA'][$storeId]['BARCODE'][$barcodeId]);
														$data['STORE_DATA'][$storeId]['QUANTITY'] -= 1;
													}
												}
											}

										}
									}
								}
							}

							$resultProductData = $provider::DeductProduct($data);
						}
						else
						{
							$resultProductData['RESULT'] = true;
						}


						$result[$providerBasketItem['BASKET_CODE']] = $resultProductData;

						if (isset($resultProductData['RESULT'])
							&& $resultProductData['RESULT'] === true)
						{
							$correct = true;
						}

					}

				}
			}
		}

		if ($correct === true)
		{
			$shipment->setFieldNoDemand('DEDUCTED', $needShip? 'N' : 'Y');
		}

		if (!empty($result)
			&& !empty($bundleIndexList) && is_array($bundleIndexList))
		{

			foreach ($bundleIndexList as $bundleParentBasketCode => $bundleChildList)
			{
				$tryShipmentBundle = false;
				foreach($bundleChildList as $bundleChildBasketCode)
				{
					if (isset($result[$bundleChildBasketCode])
						&& $result[$bundleChildBasketCode]['RESULT'] === true)
					{
						$tryShipmentBundle = true;
					}
					else
					{
						$tryShipmentBundle = false;
						break;
					}
				}

				$result[$bundleParentBasketCode] = array(
					'RESULT' => $tryShipmentBundle
				);
			}

		}
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	private static function setShipmentItemReserved(Shipment $shipment)
	{

		$result = new Result();

		$needShip = $shipment->needShip();

		if ($needShip === null
			|| ($needShip === false && !$shipment->isReserved()))
		{
			return $result;
		}

		$order = $shipment->getParentOrder();
		if (!$order)
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		if (
			Configuration::isEnableAutomaticReservation()
			&& !$shipment->needReservation()
		)
		{
			if ($needShip === false)
			{
				if (!Internals\ActionEntity::isTypeExists(
						$order->getInternalId(),
						Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY
					)
				)
				{
					Internals\ActionEntity::add(
						$order->getInternalId(),
						Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY,
						array(
							'METHOD' => 'Bitrix\Sale\ShipmentCollection::updateReservedFlag',
							'PARAMS' => array($shipment->getCollection())
						)
					);
				}
			}

			return $result;
		}

		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{

			/** @var BasketItem $basketItem */
			$basketItem = $shipmentItem->getBasketItem();
			if (!$basketItem)
			{
				$result->addError( new ResultError(
									   Loc::getMessage('SALE_PROVIDER_BASKET_ITEM_NOT_FOUND',  array(
										   '#BASKET_ITEM_ID#' => $shipmentItem->getBasketId(),
										   '#SHIPMENT_ID#' => $shipment->getId(),
										   '#SHIPMENT_ITEM_ID#' => $shipmentItem->getId(),
									   )),
									   'PROVIDER_SET_SHIPMENT_ITEM_RESERVED_WRONG_BASKET_ITEM') );
				return $result;
			}

			$providerName = $basketItem->getProvider();
			$providerClass = null;

			if (class_exists($providerName))
			{
				$providerClass = new $providerName();
			}
			if ($providerClass && ($providerClass instanceof SaleProviderBase))
			{
				continue;
			}

			$setReservedQuantity = 0;
			if ($needShip === false)
			{
				if ($basketItem->isBundleParent())
				{
					continue;
				}
				$setReservedQuantity = $shipmentItem->getQuantity();
			}

			$shipmentItem->setFieldNoDemand('RESERVED_QUANTITY', $setReservedQuantity);
		}

		if ($needShip === false)
		{
			if (!Internals\ActionEntity::isTypeExists(
					$order->getInternalId(),
					Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY
				)
			)
			{
				Internals\ActionEntity::add(
					$order->getInternalId(),
					Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY,
					array(
						'METHOD' => 'Bitrix\Sale\ShipmentCollection::updateReservedFlag',
						'PARAMS' => array($shipment->getCollection())
					)
				);
			}
		}

		return $result;
	}

	/**
	 * @param Basket $basketCollection
	 * @param BasketItem $refreshItem
	 * @return array
	 * @throws NotSupportedException
	 */
	public static function getProductAvailableQuantity(Basket $basketCollection, BasketItem $refreshItem = null)
	{

		static $proxyProductAvailableQuantity = array();
		$resultList = array();
		$userId = null;

		if (($order = $basketCollection->getOrder()) !== null)
		{
			$userId = $order->getUserId();
		}

		$basketList = static::makeArrayFromBasketCollection($basketCollection, $refreshItem);

		$basketProviderMap = static::createProviderBasketMap($basketList);
		$basketProviderList = static::redistributeToProviders($basketProviderMap);

		$context = array();
		$productsList = array();
		$providerList = array();
		$basketCodeIndex = array();
		if (!empty($basketProviderList))
		{
			foreach ($basketProviderList as $provider => $providerBasketItemList)
			{
				if (array_key_exists("IBXSaleProductProvider", class_implements($provider)))
				{
					foreach ($providerBasketItemList as $providerBasketItemData)
					{

						$proxyProductKey = $providerBasketItemData['PRODUCT_ID']."|".$userId;
						if (!empty($proxyProductAvailableQuantity[$proxyProductKey]) && is_array($proxyProductAvailableQuantity[$proxyProductKey]))
						{
							$resultProductData = $proxyProductAvailableQuantity[$proxyProductKey];
						}
						else
						{
							$resultProductData = $resultProductData = $provider::getProductAvailableQuantity($providerBasketItemData['PRODUCT_ID'], $userId);
							$proxyProductAvailableQuantity[$proxyProductKey] = $resultProductData;
						}


						$basketCode = $providerBasketItemData['BASKET_ITEM']->getBasketCode();
						$resultList[$basketCode] = $resultProductData;
					}
				}
				elseif (class_exists($provider))
				{
					if (empty($context))
					{
						if ($order)
						{
							$context = array(
								'USER_ID' => $order->getUserId(),
								'SITE_ID' => $order->getSiteId(),
								'CURRENCY' => $order->getCurrency(),
							);
						}
						else
						{
							global $USER;
							$context = array(
								'USER_ID' => $USER->getId(),
								'SITE_ID' => SITE_ID,
								'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
							);
						}
					}

					$providerClass = new $provider($context);
					if (!$providerClass instanceof SaleProviderBase)
					{
						continue;
					}

					/** @var BasketItem $basketItem */
					foreach ($providerBasketItemList as $providerBasketItemData)
					{
						$basketItem = $providerBasketItemData['BASKET_ITEM'];

						$productId = $basketItem->getProductId();
						$basketCode = $basketItem->getBasketCode();
						$basketCodeIndex[$productId][] = $basketItem->getBasketCode();

						$providerList[$provider] = $providerClass;

						if (empty($productsList[$provider][$productId]))
						{
							$productsList[$provider][$productId] = $providerBasketItemData;
						}

						$productsList[$provider][$productId]['QUANTITY_LIST'][$basketCode] = $basketItem->getQuantity();
						$resultList[$basketCode] = 0;
					}
				}
				else
				{
					foreach ($providerBasketItemList as $providerBasketItemData)
					{
						$resultProductData = \CSaleBasket::ExecuteCallbackFunction(
							$providerBasketItemData['CALLBACK_FUNC'],
							$providerBasketItemData['MODULE'],
							$providerBasketItemData['PRODUCT_ID']
						);

						$basketCode = $providerBasketItemData['BASKET_ITEM']->getBasketCode();
						$resultList[$basketCode] = $resultProductData;
					}
				}
			}


			if (!empty($productsList))
			{
				foreach ($productsList as $providerName => $products)
				{
					/** @var SaleProviderBase $providerClass */
					$providerClass = $providerList[$providerName];

					$r = $providerClass->getAvailableQuantity($products);
					if ($r->isSuccess())
					{
						$resultData = $r->getData();
						if (!empty($resultData['AVAILABLE_QUANTITY_LIST']))
						{

							foreach ($resultData['AVAILABLE_QUANTITY_LIST'] as $productId => $availableQuantity)
							{
								if (!empty($basketCodeIndex[$productId]))
								{
									foreach ($basketCodeIndex[$productId] as $basketCode)
									{
										$resultList[$basketCode] = $availableQuantity;
									}
								}
							}
						}
					}
				}
			}
		}

		return $resultList;
	}

	/**
	 * @param BasketItemCollection $basketCollection
	 * @param array $select
	 * @param BasketItem|null $refreshItem
	 *
	 * @return array
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 */
	public static function getProductData(BasketItemCollection $basketCollection, array $select = array(), BasketItem $refreshItem = null)
	{
		$resultList = array();

		$orderId = null;
		$userId = null;
		$siteId = null;
		$currency = null;

		if (($order = $basketCollection->getOrder()) !== null)
		{
			$userId = $order->getUserId();
			$siteId = $order->getSiteId();
			$currency = $order->getCurrency();
		}

		if  ($siteId === null)
		{
			$basket = $basketCollection->getBasket();
			$siteId = $basket->getSiteId();
		}

		if ($siteId === null)
			return array();

		if ($currency === null)
		{
			$currency = Internals\SiteCurrencyTable::getSiteCurrency($siteId);
			if (!$currency)
				$currency = Currency\CurrencyManager::getBaseCurrency();
		}

		$context = array(
			"USER_ID" => $userId,
			"SITE_ID" => $siteId,
			"CURRENCY" => $currency,
		);

		$basketList = static::makeArrayFromBasketCollection($basketCollection, $refreshItem);

		// Process each element separately so that it works correctly with duplicates.
		$basketProviderMap = static::createProviderBasketMap($basketList, array('QUANTITY', 'RENEWAL', 'SITE_ID', 'USER_ID'));
		foreach ($basketProviderMap as $basketProviderMapItem)
		{
			$basketProviderList = static::redistributeToProviders([
				$basketProviderMapItem,
			]);

			if (!empty($basketProviderList))
			{
				$options = array(
					'RETURN_BASKET_ID'
				);

				foreach ($basketProviderList as $providerClassName => $productValueList)
				{
					$r = static::getProductDataByList($productValueList, $providerClassName, $select, $context, $options);
					if ($r->isSuccess())
					{
						$resultData = $r->getData();
						if (!empty($resultData['PRODUCT_DATA_LIST']))
						{
							$resultList = $resultData['PRODUCT_DATA_LIST'] + $resultList;
						}
					}
				}

			}
		}

		return $resultList;
	}

	/**
	 * @internal
	 * @param array $products
	 * @param $providerClassName
	 * @param array $select
	 * @param array $context
	 * @param array $options
	 *
	 * @return Result
	 */
	public static function getProductDataByList(array $products, $providerClassName = null, array $select = array(), array $context, array $options = array())
	{

		$result = new Result();
		$resultList = array();

		$needPrice = in_array('PRICE', $select);
		$needBasePrice = in_array('BASE_PRICE', $select);
		$needCoupons = in_array('COUPONS', $select);
		$data = array(
			'USER_ID' => $context['USER_ID'],
			'SITE_ID' => $context['SITE_ID'],
			'CURRENCY' => $context['CURRENCY'],
			'CHECK_QUANTITY' => (in_array('QUANTITY', $select) ? 'Y' : 'N'),
			'AVAILABLE_QUANTITY' => (in_array('AVAILABLE_QUANTITY', $select) ? 'Y' : 'N'),
			'CHECK_PRICE' => ($needPrice ? 'Y' : 'N'),
			'CHECK_COUPONS' => ($needCoupons ? 'Y' : 'N'),
			'RENEWAL' => (in_array('RENEWAL', $select) ? 'Y' : 'N')
		);

		if ($needBasePrice)
			$data['CHECK_DISCOUNT'] = 'N';

		$useOrderProduct = false;
		if ($needPrice)
			$useOrderProduct = true;

		if ($needCoupons)
			$useOrderProduct = false;

		$data['USE_ORDER_PRODUCT'] = $useOrderProduct;

		unset($needCoupons, $needPrice);


		if ($providerClassName)
		{
			if (array_key_exists("IBXSaleProductProvider", class_implements($providerClassName)))
			{
				$resultProductList = static::getProductProviderData($products, $providerClassName, $data, $select);
				if (in_array('RETURN_BASKET_ID', $options))
				{
					$basketList = array();
					foreach ($products as $productId => $productData)
					{
						$basketItem = $productData['BASKET_ITEM'];
						$basketList[] = $basketItem;
					}

					$resultProductList = static::createItemsAfterGetProductData($basketList, $resultProductList, $select);
				}
			}
			elseif (class_exists($providerClassName))
			{
				$basketList = array();
				foreach ($products as $productId => $productData)
				{
					$basketList[] = $productData['BASKET_ITEM'];
				}

				$r = Internals\Catalog\Provider::getProductData($basketList, $context);
				if ($r->isSuccess())
				{
					$resultProductData = $r->getData();
					if (!empty($resultProductData['PRODUCT_DATA_LIST']))
					{
						$itemsList = $resultProductData['PRODUCT_DATA_LIST'];
						$resultItemsList = array();
						$resultProductList = array();

						foreach ($itemsList as $providerName => $products)
						{
							$resultItemsList = static::createItemsAfterGetProductData($basketList, $products, $select);
						}

						$resultProductList = $resultProductList + $resultItemsList;

					}
				}
			}

			if (!empty($resultProductList))
			{
				if (!empty($resultList) && is_array($resultList))
				{
					$resultList = $resultList + $resultProductList;
				}
				else
				{
					$resultList = $resultProductList;
				}
			}
		}
		else
		{
			$priceFields = static::getPriceFields();

			foreach ($products as $productId => $productData)
			{
				$callbackFunction = null;
				if (!empty($productData['CALLBACK_FUNC']))
				{
					$callbackFunction = $productData['CALLBACK_FUNC'];
				}

				$quantityList = array();

				if (array_key_exists('QUANTITY', $productData))
				{
					$quantityList = array($productData['BASKET_CODE'] => $productData['QUANTITY']);

				}
				elseif (!empty($productData['QUANTITY_LIST']))
				{
					$quantityList = $productData['QUANTITY_LIST'];
				}

				foreach($quantityList as $basketCode => $quantity)
				{
					if (!empty($callbackFunction))
					{
						$resultProductData = \CSaleBasket::executeCallbackFunction(
							$callbackFunction,
							$productData['MODULE'],
							$productId,
							$quantity
						);
					}
					else
					{
						$resultProductData = array(
							'QUANTITY' => $quantity,
							'AVAILABLE_QUANTITY' => $quantity,
						);
					}

					$itemCode = $productId;
					if (in_array('RETURN_BASKET_ID', $options))
					{
						$itemCode = $basketCode;
					}

					if (empty($resultList[$itemCode]))
					{
						$resultList[$itemCode] = $resultProductData;
					}

					if (!empty($resultProductData))
					{
						$resultList[$itemCode]['PRICE_LIST'][$basketCode] = array(
							'QUANTITY' => $resultProductData['QUANTITY'],
							'AVAILABLE_QUANTITY' => $resultProductData['AVAILABLE_QUANTITY'],
							"ITEM_CODE" => $productId,
							"BASKET_CODE" => $basketCode,
						);

						foreach ($priceFields as $fieldName)
						{
							if (isset($resultProductData[$fieldName]))
							{
								$resultList[$itemCode]['PRICE_LIST'][$basketCode][$fieldName] = $resultProductData[$fieldName];
							}
						}
					}
				}

			}
		}


		if (!empty($resultList))
		{
			$result->setData(
				array(
					'PRODUCT_DATA_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param $basketList
	 * @param array $productDataList
	 * @param array $select
	 *
	 * @return array
	 * @throws ArgumentTypeException
	 */
	private static function createItemsAfterGetProductData($basketList, array $productDataList, array $select = array())
	{
		$resultList = array();
		$basketIndexList = array();
		$basketMap = array();

		if (!is_array($basketList) && !($basketList instanceof BasketBase))
		{
			throw new ArgumentTypeException('basketList');
		}

		/** @var BasketItem $basketItem */
		foreach ($basketList as $basketItem)
		{
			$basketCode = $basketItem->getBasketCode();
			$productId = $basketItem->getProductId();

			$basketIndexList[$productId][] = $basketCode;
			$basketMap[$basketCode] = $basketItem;
		}

		if (empty($productDataList))
		{
			return $resultList;
		}

		foreach ($productDataList as $productId => $productData)
		{
			if (empty($basketIndexList[$productId]))
				continue;

			if (empty($productData))
				continue;

			foreach ($basketIndexList[$productId] as $basketCode)
			{
				if (!empty($productData['PRICE_LIST']) && !empty($productData['PRICE_LIST'][$basketCode]))
				{
					$priceData = $productData['PRICE_LIST'][$basketCode];

					if (array_key_exists('AVAILABLE_QUANTITY', $priceData)
						&& !array_key_exists('QUANTITY', $priceData))
					{
						$priceData['QUANTITY'] = $priceData['AVAILABLE_QUANTITY'];
					}

					/** @var BasketItem $basketItem */
					$basketItem = $basketMap[$basketCode];

					if (in_array('PRICE', $select) || $basketItem->getId() == 0)
					{
						$productData = $priceData + $productData;
					}
					else
					{
						if (isset($priceData['QUANTITY']))
						{
							$productData['QUANTITY'] = $priceData['QUANTITY'];
						}

						if (isset($priceData['AVAILABLE_QUANTITY']))
						{
							$productData['AVAILABLE_QUANTITY'] = $priceData['AVAILABLE_QUANTITY'];
						}

						unset($productData['PRICE_LIST']);
					}
				}

				if (in_array('AVAILABLE_QUANTITY', $select) && isset($productData['AVAILABLE_QUANTITY']))
				{
					$productData['QUANTITY'] = $productData['AVAILABLE_QUANTITY'];
				}

				$resultList[$basketCode] = $productData;
			}
		}

		return $resultList;
	}

	/**
	 * @internal
	 * @param array $products
	 * @param $provider
	 * @param array $data
	 * @param array $select
	 *
	 * @return mixed
	 */
	public static function getProductProviderData(array $products, $provider, array $data, array $select = array())
	{
		$result = array();

		foreach ($products as $productData)
		{
			$productSelect = array_fill_keys($select, true);
			$productId = $productData['PRODUCT_ID'];

			$currentUseOrderProduct = $data['USE_ORDER_PRODUCT'];
			if ($productData['IS_NEW'])
				$currentUseOrderProduct = false;

			$fields = $data;

			if ($productData['IS_ORDERABLE'])
			{
				$fields['CHECK_COUPONS'] = 'Y';
			}
			else
			{
				$fields['CHECK_COUPONS'] = 'N';
			}

			if ($productData['IS_BUNDLE_CHILD'])
			{
				$fields['CHECK_DISCOUNT'] = 'N';
				$fields['CHECK_COUPONS'] = 'N';
			}

			$fields['PRODUCT_ID'] = $productId;

			if (isset($productData['SUBSCRIBE']) && $productData['SUBSCRIBE'] === true)
			{
				unset($productSelect['QUANTITY'], $productSelect['AVAILABLE_QUANTITY']);

				$fields['CHECK_QUANTITY'] = 'N';
				$fields['AVAILABLE_QUANTITY'] = 'N';
			}

			$quantityList = array();

			if (!empty($productData['QUANTITY_LIST']))
			{
				$quantityList = $productData['QUANTITY_LIST'];
			}
			else
			{
				$quantityList[$productData['BASKET_CODE']] = $productData['QUANTITY'];
			}

			$basketId = null;

			if (!empty($productData['BASKET_ID']))
			{
				$basketId = $productData['BASKET_ID'];
			}


			if (intval($basketId) == 0)
			{
				/** @var BasketItem $basketItem */
				$basketItem = $productData['BASKET_ITEM'];
				if ($basketItem)
				{
					$basketId = $basketItem->getId();
				}
			}
//
			if (intval($basketId) > 0)
			{
				$fields['BASKET_ID'] = $basketId;
			}

			$hasTrustData = false;

			$trustData = static::getTrustData($data['SITE_ID'], $productData['MODULE'], $productData['PRODUCT_ID']);
			$resultProductData = array();

			if (static::isReadTrustData() === true
				&& !empty($trustData) && is_array($trustData))
			{
				$hasTrustData = true;
				$resultProductData = $trustData;

				foreach (static::getProductDataRequiredFields() as $requiredField)
				{
					if (!array_key_exists($requiredField, $resultProductData))
					{
						$hasTrustData = false;
						break;
					}
				}


				if ($hasTrustData && isset($productSelect['PRICE']))
				{
					foreach (static::getProductDataRequiredPriceFields() as $requiredField)
					{
						if (!array_key_exists($requiredField, $resultProductData))
						{
							$hasTrustData = false;
							break;
						}
					}
				}
			}

			$itemCode = $productData['PRODUCT_ID'];

			$resultProviderDataList = array();

			if(!$hasTrustData)
			{
				foreach($quantityList as $basketCode => $quantity)
				{
					if (!empty($resultProviderDataList[$quantity]))
					{
						$resultProviderDataList[$quantity]['BASKET_CODE'][] = $basketCode;
						continue;
					}

					$requestFields = $fields;
					$requestFields['QUANTITY'] = $quantity;

					$resultProviderDataList[$quantity] = array(
						'BASKET_CODE' => array($basketCode),
						'DATA' => ($currentUseOrderProduct ? $provider::OrderProduct(
							$requestFields
						) : $provider::GetProductData($requestFields))
					);

				}

			}
			else
			{

				if (!isset($productSelect['AVAILABLE_QUANTITY']) && array_key_exists("AVAILABLE_QUANTITY", $resultProductData))
				{
					unset($resultProductData['AVAILABLE_QUANTITY']);
				}

				$productQuantity = floatval($resultProductData['QUANTITY']);

				$resultProviderDataList[$productQuantity] = array(
					'BASKET_CODE' => array($productData['BASKET_CODE']),
					'DATA' => $resultProductData
				);

			}

			$priceFields = static::getPriceFields();

			foreach ($resultProviderDataList as $quantity => $providerData)
			{
				if (empty($result[$itemCode]))
				{
					$result[$itemCode] = $providerData['DATA'];
				}

				$basketCodeList = $providerData['BASKET_CODE'];

				foreach ($basketCodeList as $basketCode)
				{
					$result[$itemCode]['PRICE_LIST'][$basketCode] = array(
						"ITEM_CODE" => $itemCode,
						"BASKET_CODE" => $basketCode,
					);

					if (isset($providerData['DATA']['QUANTITY']) && $providerData['DATA']['QUANTITY'] > 0)
					{
						$result[$itemCode]['PRICE_LIST'][$basketCode]['QUANTITY'] = $providerData['DATA']['QUANTITY'];
					}

					if (isset($providerData['DATA']['AVAILABLE_QUANTITY']))
					{
						$result[$itemCode]['PRICE_LIST'][$basketCode]['AVAILABLE_QUANTITY'] = $providerData['DATA']['AVAILABLE_QUANTITY'];
					}
				}

				foreach ($priceFields as $fieldName)
				{
					if (isset($providerData['DATA'][$fieldName]))
					{
						foreach ($basketCodeList as $basketCode)
						{
							$result[$itemCode]['PRICE_LIST'][$basketCode][$fieldName] = $providerData['DATA'][$fieldName];
						}
					}
				}
			}

//			$result[$itemCode]['ITEM_CODE'] = $productData['ITEM_CODE'];

			if ($productData['IS_BUNDLE_PARENT'])
			{
				$result[$itemCode]["BUNDLE_ITEMS"] = array();
				/** @var array $bundleChildList */
				$bundleChildDataList = static::getBundleChildItemsByProductData($provider, $productData);
				if (!empty($bundleChildDataList) && is_array($bundleChildDataList))
				{

					foreach ($bundleChildDataList["ITEMS"] as &$itemData)
					{
						$itemData['QUANTITY'] = $itemData['QUANTITY'] * $productData['QUANTITY'];
					}
					unset($itemData);
					$result[$itemCode]["BUNDLE_ITEMS"] = $bundleChildDataList["ITEMS"];
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $basketProviderList
	 * @param array $context
	 * @param array $select
	 *
	 * @return array
	 */
	public static function getCatalogData(array $basketProviderList, array $context, array $select = array())
	{
		$needPrice = in_array('PRICE', $select);
		$needBasePrice = in_array('BASE_PRICE', $select);
		$needCoupons = in_array('COUPONS', $select);

		$result = array();
//		$orderId = null;
		$userId = null;
		$siteId = null;
		$currency = null;

		if (!empty($context['USER_ID']) && intval($context['USER_ID']) > 0)
		{
			$userId = $context['USER_ID'];
		}

		if (array_key_exists('SITE_ID', $context))
		{
			$siteId = $context['SITE_ID'];
		}

		if (array_key_exists('CURRENCY', $context))
		{
			$currency = $context['CURRENCY'];
		}

		$data = array(
			'USER_ID' => $userId,
			'SITE_ID' => $siteId,
			'CURRENCY' => $currency,
			'CHECK_QUANTITY' => (in_array('QUANTITY', $select) ? 'Y' : 'N'),
			'AVAILABLE_QUANTITY' => (in_array('AVAILABLE_QUANTITY', $select) ? 'Y' : 'N'),
			'CHECK_PRICE' => ($needPrice ? 'Y' : 'N'),
			'CHECK_COUPONS' => ($needCoupons ? 'Y' : 'N'),
			'RENEWAL' => (in_array('RENEWAL', $select) ? 'Y' : 'N')
		);

		if ($needBasePrice)
			$data['CHECK_DISCOUNT'] = 'N';

		$useOrderProduct = false;
		if ($needPrice)
			$useOrderProduct = true;

		if ($needCoupons)
			$useOrderProduct = false;

		unset($needCoupons, $needPrice);

		foreach ($basketProviderList as $provider => $providerBasketItemList)
		{
			if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
			{
				foreach ($providerBasketItemList as $providerBasketItem)
				{
					$currentUseOrderProduct = $useOrderProduct;
					if (!isset($providerBasketItem['BASKET_ID']) || (int)$providerBasketItem['BASKET_ID'] <= 0)
						$currentUseOrderProduct = false;

					$providerFields = $data;

					if ($providerBasketItem['BASKET_ITEM']->isBundleChild())
					{
						$providerFields['CHECK_DISCOUNT'] = 'N';
					}

					if ($providerBasketItem['BASKET_ITEM']->getField("CAN_BUY") == "N"
						|| $providerBasketItem['BASKET_ITEM']->getField("DELAY") == "Y"
						|| $providerBasketItem['BASKET_ITEM']->getField("SUBSCRIBE") == "Y"
					)
					{
						$providerFields['CHECK_COUPONS'] = 'N';
					}
					else
					{
						$providerFields['CHECK_COUPONS'] = 'Y';
					}

					$providerFields['PRODUCT_ID'] = $providerBasketItem['PRODUCT_ID'];
					$providerFields['QUANTITY'] = $providerBasketItem['QUANTITY'];

					if (intval($providerBasketItem['BASKET_ID']) > 0)
					{
						$providerFields['BASKET_ID'] = $providerBasketItem['BASKET_ID'];
					}

					$hasTrustData = false;

					$trustData = static::getTrustData($siteId, $providerBasketItem['MODULE'], $providerBasketItem['PRODUCT_ID']);

					if (static::isReadTrustData() === true
						&& !empty($trustData) && is_array($trustData))
					{
						$hasTrustData = true;
						$resultProductData = $trustData;

						foreach (static::getProductDataRequiredFields() as $requiredField)
						{
							if (!array_key_exists($requiredField, $resultProductData))
							{
								$hasTrustData = false;
								break;
							}
						}


						if ($hasTrustData && in_array('PRICE', $select))
						{
							foreach (static::getProductDataRequiredPriceFields() as $requiredField)
							{
								if (!array_key_exists($requiredField, $resultProductData))
								{
									$hasTrustData = false;
									break;
								}
							}
						}
					}


					if(!$hasTrustData)
					{
						$resultProductData = ($currentUseOrderProduct ? $provider::OrderProduct($providerFields) : $provider::GetProductData($providerFields));
					}
					else
					{
						if (!in_array('AVAILABLE_QUANTITY', $select) && array_key_exists("AVAILABLE_QUANTITY", $resultProductData))
						{
							unset($resultProductData['AVAILABLE_QUANTITY']);
						}
					}

					$basketCode = $providerBasketItem['BASKET_ITEM']->getBasketCode();
					$result[$basketCode] = $resultProductData;

					if ($providerBasketItem['BASKET_ITEM']->isBundleParent())
					{

						$result[$basketCode]["BUNDLE_ITEMS"] = array();
						/** @var array $bundleChildList */
						$bundleChildDataList = static::getSetItems($providerBasketItem['BASKET_ITEM']);
						if (!empty($bundleChildDataList) && is_array($bundleChildDataList))
						{
							$bundleChildList = reset($bundleChildDataList);

							foreach ($bundleChildList["ITEMS"] as &$itemData)
							{
								$itemData['QUANTITY'] = $itemData['QUANTITY'] * $providerBasketItem['BASKET_ITEM']->getQuantity();
							}
							unset($itemData);
							$result[$basketCode]["BUNDLE_ITEMS"] = $bundleChildList["ITEMS"];
						}

					}
				}
			}
			else
			{
				foreach ($providerBasketItemList as $providerBasketItem)
				{
					$resultProductData = \CSaleBasket::executeCallbackFunction(
						$providerBasketItem['CALLBACK_FUNC'],
						$providerBasketItem['MODULE'],
						$providerBasketItem['PRODUCT_ID'],
						$providerBasketItem['QUANTITY']
					);

					$basketCode = $providerBasketItem['BASKET_ITEM']->getBasketCode();
					$result[$basketCode] = $resultProductData;
				}
			}
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function tryShipment(Shipment $shipment)
	{
		$result = new Result();
		$needShip = $shipment->needShip();
		if ($needShip === null)
			return $result;

		$resultList = array();
		$storeData = array();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		if (!$shipmentItemCollection)
		{
			throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		$shipment = $shipmentItemCollection->getShipment();
		if (!$shipment)
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		if (!$shipmentCollection)
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		$r = static::tryShipmentItemList($shipmentItemCollection);

		$basketList = static::getBasketFromShipmentItemCollection($shipmentItemCollection);

		$bundleIndexList = static::getBundleIndexFromShipmentItemCollection($shipmentItemCollection);

		$basketCountList = static::getBasketCountFromShipmentItemCollection($shipmentItemCollection);

		$basketProviderMap = static::createProviderBasketMap($basketList, array('RESERVED', 'SITE_ID'));
		$basketProviderList = static::redistributeToProviders($basketProviderMap);

		if (Configuration::useStoreControl())
		{
			/** @var Result $r */
			$r = static::getStoreDataFromShipmentItemCollection($shipmentItemCollection);
			if ($r->isSuccess())
			{
				$resultStoreData = $r->getData();
				if (!empty($resultStoreData['STORE_DATA_LIST']))
				{
					$storeDataList = $resultStoreData['STORE_DATA_LIST'];
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

		}

		if (!empty($basketProviderList))
		{
			foreach ($basketProviderList as $provider => $providerBasketItemList)
			{
				if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
				{
					foreach ($providerBasketItemList as $providerBasketItem)
					{
						if ($providerBasketItem['BASKET_ITEM']->isBundleParent())
						{
							continue;
						}

						$resultProduct = new Result();


						$quantity = 0;
						$basketStoreData = array();

						$basketCode = $providerBasketItem['BASKET_CODE'];

						/** @var BasketItem $basketItem */
						if (!$basketItem = $providerBasketItem['BASKET_ITEM'])
						{
							throw new ObjectNotFoundException('Entity "BasketItem" not found');
						}

						if (Configuration::useStoreControl())
						{
							$quantity = $basketCountList[$basketCode];

							if (!empty($storeDataList) && is_array($storeDataList)
							&& isset($storeDataList[$basketCode]))
							{
								$basketStoreData = $storeDataList[$basketCode];
							}

							if (!empty($basketStoreData))
							{
								$allBarcodeQuantity = 0;
								foreach($basketStoreData as $basketShipmentItemStore)
								{
									$allBarcodeQuantity += $basketShipmentItemStore['QUANTITY'];
								}

								if ($quantity > $allBarcodeQuantity)
								{
									$resultProduct->addError(new ResultError(Loc::getMessage('SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY', array(
										'#PRODUCT_NAME#' => $basketItem->getField('NAME')
									)), 'SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY'));
								}
								elseif ($quantity < $allBarcodeQuantity)
								{
									$resultProduct->addError(new ResultError(Loc::getMessage('SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY', array(
										'#PRODUCT_NAME#' => $basketItem->getField('NAME')
									)), 'SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY'));
								}
							}

						}

						if ($resultProduct->isSuccess())
						{

							if ($needShip === true)
							{
								if (method_exists($provider, 'tryShipmentProduct'))
								{
									/** @var Result $resultProductData */
									$resultProduct = $provider::tryShipmentProduct($basketItem, $providerBasketItem['RESERVED'], $basketStoreData, $quantity);
								}
							}
							else
							{
								if (method_exists($provider, 'tryUnshipmentProduct'))
								{
									/** @var Result $resultProductData */
									$resultProduct = $provider::tryUnshipmentProduct($providerBasketItem['PRODUCT_ID']);
								}
							}
						}

						$resultList[$basketCode] = $resultProduct;

					}
				}
				elseif (class_exists($provider))
				{

					/** @var ShipmentCollection $shipmentCollection */
					if (!$shipmentCollection = $shipment->getCollection())
					{
						throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
					}

					/** @var Order $order */
					if (!$order = $shipmentCollection->getOrder())
					{
						throw new ObjectNotFoundException('Entity "Order" not found');
					}

					$pool = Internals\PoolQuantity::getInstance($order->getInternalId());

					$context = array(
						'SITE_ID' => $order->getSiteId(),
						'CURRENCY' => $order->getCurrency(),
					);

					if ($order->getUserId() > 0)
					{
						$context['USER_ID'] = $order->getUserId();
					}
					else
					{
						global $USER;
						$context['USER_ID'] = $USER->getId();
					}

					$creator = Internals\ProviderCreator::create($context);

					$tryShipProductList = array();
					/** @var ShipmentItem $shipmentItem */
					foreach ($shipmentItemCollection as $shipmentItem)
					{
						$basketItem = $shipmentItem->getBasketItem();
						$providerClass = $basketItem->getProviderEntity();

						if ($providerClass instanceof SaleProviderBase)
						{
							$shipmentProductData = $creator->createItemForShip($shipmentItem);
							$creator->addProductData($shipmentProductData);
						}
					}

					$r = $creator->tryShip();
					if ($r->isSuccess())
					{
						if ($r->hasWarnings())
						{
							$result->addWarnings($r->getWarnings());
						}
						else
						{
							$data = $r->getData();
							if (array_key_exists('TRY_SHIP_PRODUCTS_LIST', $data))
							{
								$tryShipProductList = $data['TRY_SHIP_PRODUCTS_LIST'] + $tryShipProductList;

								$creator->setItemsResultAfterTryShip($pool, $tryShipProductList);

							}
						}
					}
					else
					{
						$result->addWarnings($r->getErrors());
					}
				}
			}
		}

		if (!empty($resultList)
			&& !empty($bundleIndexList) && is_array($bundleIndexList))
		{

			foreach ($bundleIndexList as $bundleParentBasketCode => $bundleChildList)
			{
//				$tryShipmentBundle = false;
				foreach($bundleChildList as $bundleChildBasketCode)
				{
					if (!isset($resultList[$bundleChildBasketCode]))
					{
						if (!isset($resultList[$bundleParentBasketCode]))
						{
							$resultList[$bundleParentBasketCode] = new Result();
						}

						$resultList[$bundleParentBasketCode]->addError(new ResultError('Bundle child item not found', 'SALE_PROVIDER_SHIPMENT_SHIPPED_BUNDLE_CHILD_ITEM_NOT_FOUND'));
					}

				}
			}

		}

		if (!empty($resultList))
		{
			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $shipment->getCollection())
			{
				throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			/** @var Order $order */
			if (!$order = $shipmentCollection->getOrder())
			{
				throw new ObjectNotFoundException('Entity "Order" not found');
			}

			$hasErrors = false;

			/** @var ShipmentItem $shipmentItem */
			foreach ($shipmentItemCollection as $shipmentItem)
			{
				/** @var BasketItem $basketItem */
				if(!$basketItem = $shipmentItem->getBasketItem())
				{
					throw new ObjectNotFoundException('Entity "BasketItem" not found');
				}

				if (isset($resultList[$basketItem->getBasketCode()]) && !$resultList[$basketItem->getBasketCode()]->isSuccess())
				{
					$hasErrors = true;
					break;
				}
			}

			if (!$hasErrors)
			{
				/** @var ShipmentItem $shipmentItem */
				foreach ($shipmentItemCollection as $shipmentItem)
				{
					/** @var BasketItem $basketItem */
					if(!$basketItem = $shipmentItem->getBasketItem())
					{
						throw new ObjectNotFoundException('Entity "BasketItem" not found');
					}

					if (isset($resultList[$basketItem->getBasketCode()]) && $resultList[$basketItem->getBasketCode()]->isSuccess())
					{
						static::addQuantityPoolItem($order->getInternalId(), $basketItem, ($needShip? -1 : 1) * $shipmentItem->getQuantity());

						if ($needShip)
							$shipmentItem->setFieldNoDemand("RESERVED_QUANTITY", 0);

					}
				}
			}

			$result->setData($resultList);
		}

		return $result;
	}

	/**
	 * @param ShipmentItem[] $shipmentItemList
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	public static function tryShipmentItemList($shipmentItemList)
	{
		$result = new Result();

		$resultList = array();
		$bundleIndexList = static::getBundleIndexFromShipmentItemCollection($shipmentItemList);

		if (Configuration::useStoreControl())
		{
			/** @var Result $r */
			$r = static::getStoreDataFromShipmentItemCollection($shipmentItemList);
			if ($r->isSuccess())
			{
				$resultStoreData = $r->getData();
				if (!empty($resultStoreData['STORE_DATA_LIST']))
				{
					$storeDataList = $resultStoreData['STORE_DATA_LIST'];
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

		}

		$shipmentItemParentsList = array();

		$tryShipProductList = array();

		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$itemIndex = $shipmentItem->getInternalIndex();
			$basketItem = $shipmentItem->getBasketItem();
			$providerName = $basketItem->getProviderName();

			/** @var ShipmentItemCollection $shipmentItemCollection */
			$shipmentItemCollection = $shipmentItem->getCollection();
			if (!$shipmentItemCollection)
			{
				throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
			}

			/** @var Shipment $shipment */
			$shipment = $shipmentItemCollection->getShipment();
			if (!$shipment)
			{
				throw new ObjectNotFoundException('Entity "Shipment" not found');
			}

			$shipmentItemParentsList[$itemIndex] = array(
				'BASKET_ITEM' => $basketItem,
				'SHIPMENT' => $shipment,
				'SHIPMENT_ITEM_COLLECTION' => $shipmentItemCollection,
			);

			$needShip = $shipment->needShip();
			if ($needShip === null)
				continue;


			if ($providerName && array_key_exists("IBXSaleProductProvider", class_implements($providerName)))
			{
				$basketItem = $shipmentItem->getBasketItem();
				if (!$basketItem)
				{
					throw new ObjectNotFoundException('Entity "BasketItem" not found');
				}

				if ($basketItem->isBundleParent())
				{
					continue;
				}

				$basketCode = $basketItem->getBasketCode();
				$quantity = $shipmentItem->getQuantity();
				$basketStoreData = array();

				$resultProduct = new Result();

				if (Configuration::useStoreControl())
				{
					if (!empty($storeDataList) && is_array($storeDataList)
						&& isset($storeDataList[$basketCode]))
					{
						$basketStoreData = $storeDataList[$basketCode];
					}

					if (!empty($basketStoreData))
					{
						$allBarcodeQuantity = 0;
						foreach($basketStoreData as $basketShipmentItemStore)
						{
							$allBarcodeQuantity += $basketShipmentItemStore['QUANTITY'];
						}

						if ($quantity > $allBarcodeQuantity)
						{
							$resultProduct->addError(new ResultError(Loc::getMessage('SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY', array(
								'#PRODUCT_NAME#' => $basketItem->getField('NAME')
							)), 'SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY'));
						}
						elseif ($quantity < $allBarcodeQuantity)
						{
							$resultProduct->addError(new ResultError(Loc::getMessage('SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY', array(
								'#PRODUCT_NAME#' => $basketItem->getField('NAME')
							)), 'SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY'));
						}
					}

				}

				if ($resultProduct->isSuccess())
				{

					if ($needShip === true)
					{
						if (method_exists($providerName, 'tryShipmentProduct'))
						{
							/** @var Result $resultProductData */
							$resultProduct = $providerName::tryShipmentProduct($basketItem, $basketItem->getField('RESERVED'), $basketStoreData, $quantity);
						}
					}
					else
					{
						if (method_exists($providerName, 'tryUnshipmentProduct'))
						{
							/** @var Result $resultProductData */
							$resultProduct = $providerName::tryUnshipmentProduct($basketItem->getProductId());
						}
					}
				}

				$resultList[$basketCode] = $resultProduct;

			}
			elseif (class_exists($providerName))
			{
				/** @var ShipmentCollection $shipmentCollection */
				$shipmentCollection = $shipment->getCollection();
				if (!$shipmentCollection)
				{
					throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
				}

				/** @var Order $order */
				$order = $shipmentCollection->getOrder();
				if (!$order)
				{
					throw new ObjectNotFoundException('Entity "Order" not found');
				}

				$shipmentItemParentsList[$itemIndex]['SHIPMENT_COLLECTION'] = $shipmentCollection;
				$shipmentItemParentsList[$itemIndex]['ORDER'] = $order;

				$pool = Internals\PoolQuantity::getInstance($order->getInternalId());

				$context = array(
					'SITE_ID' => $order->getSiteId(),
					'CURRENCY' => $order->getCurrency(),
				);

				if ($order->getUserId() > 0)
				{
					$context['USER_ID'] = $order->getUserId();
				}
				else
				{
					global $USER;
					$context['USER_ID'] = $USER->getId();
				}

				$creator = Internals\ProviderCreator::create($context);

				$shipmentProductData = $creator->createItemForShip($shipmentItem);
				$creator->addProductData($shipmentProductData);

				$r = $creator->tryShip();
				if ($r->isSuccess())
				{
					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
					}
					else
					{
						$data = $r->getData();
						if (array_key_exists('TRY_SHIP_PRODUCTS_LIST', $data))
						{
							$tryShipProductList = $data['TRY_SHIP_PRODUCTS_LIST'] + $tryShipProductList;
							$creator->setItemsResultAfterTryShip($pool, $tryShipProductList);
						}
					}
				}
				else
				{
					$result->addWarnings($r->getErrors());
				}
			}
		}

		if (!empty($resultList)
			&& !empty($bundleIndexList) && is_array($bundleIndexList))
		{

			foreach ($bundleIndexList as $bundleParentBasketCode => $bundleChildList)
			{
				foreach($bundleChildList as $bundleChildBasketCode)
				{
					if (!isset($resultList[$bundleChildBasketCode]))
					{
						if (!isset($resultList[$bundleParentBasketCode]))
						{
							$resultList[$bundleParentBasketCode] = new Result();
						}

						$resultList[$bundleParentBasketCode]->addError(new ResultError('Bundle child item not found', 'SALE_PROVIDER_SHIPMENT_SHIPPED_BUNDLE_CHILD_ITEM_NOT_FOUND'));
					}

				}
			}

		}

		if (!empty($resultList))
		{

			$hasErrors = false;

			/** @var ShipmentItem $shipmentItem */
			foreach ($shipmentItemList as $shipmentItem)
			{
				$itemIndex = $shipmentItem->getInternalIndex();

				/** @var BasketItem $basketItem */
				$basketItem = $shipmentItemParentsList[$itemIndex]['BASKET_ITEM'];

				if (isset($resultList[$basketItem->getBasketCode()]) && !$resultList[$basketItem->getBasketCode()]->isSuccess())
				{
					$hasErrors = true;
					break;
				}
			}

			if (!$hasErrors)
			{
				/** @var ShipmentItem $shipmentItem */
				foreach ($shipmentItemList as $shipmentItem)
				{
					$itemIndex = $shipmentItem->getInternalIndex();

					/** @var BasketItem $basketItem */
					$basketItem = $shipmentItemParentsList[$itemIndex]['BASKET_ITEM'];

					$productId = $shipmentItem->getProductId();

					if (isset($resultList[$basketItem->getBasketCode()]) && $resultList[$basketItem->getBasketCode()]->isSuccess())
					{
						/** @var Shipment $shipment */
						$shipment = $shipmentItemParentsList[$itemIndex]['SHIPMENT'];

						/** @var Order $order */
						$order = $shipmentItemParentsList[$itemIndex]['ORDER'];

						if (!$order)
						{
							/** @var ShipmentCollection $shipmentCollection */
							$shipmentCollection = $shipment->getCollection();
							if (!$shipmentCollection)
							{
								throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
							}

							/** @var Order $order */
							$order = $shipmentCollection->getOrder();
							if (!$order)
							{
								throw new ObjectNotFoundException('Entity "Order" not found');
							}

							$shipmentItemParentsList[$itemIndex]['SHIPMENT_COLLECTION'] = $shipmentCollection;
							$shipmentItemParentsList[$itemIndex]['ORDER'] = $order;
						}

						$needShip = $shipment->needShip();

						static::addQuantityPoolItem($order->getInternalId(), $basketItem, ($needShip? -1 : 1) * $shipmentItem->getQuantity());

						if ($needShip)
						{
							$shipmentItem->setFieldNoDemand("RESERVED_QUANTITY", 0);
						}


						$foundItem = false;
						$poolItems = Internals\ItemsPool::get($order->getInternalId(), $productId);
						if (!empty($poolItems))
						{
							/** @var ShipmentItem $poolItem */
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
							Internals\ItemsPool::add($order->getInternalId(), $productId, $shipmentItem);
						}

					}
				}
			}

			$result->setData($resultList);
		}

		return $result;
	}


	/**
	 * @param $shipmentItemList
	 *
	 * @return array
	 */
	protected static function getBundleIndexFromShipmentItemCollection($shipmentItemList)
	{
		$bundleIndexList = array();
		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			/** @var BasketItem $basketItem */
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}


			if ($basketItem->isBundleChild())
			{
				/** @var BasketItem $parentBasketItem */
				$parentBasketItem = $basketItem->getParentBasketItem();
				$parentBasketCode = $parentBasketItem->getBasketCode();

				if (!array_key_exists($parentBasketCode, $bundleIndexList))
				{
					$bundleIndexList[$parentBasketCode] = array();
				}

				$bundleIndexList[$parentBasketCode][] = $basketItem->getBasketCode();
			}
		}

		return $bundleIndexList;
	}

	/**
	 * @param $shipmentItemList
	 *
	 * @return array
	 * @throws ObjectNotFoundException
	 */
	protected static function getBasketFromShipmentItemCollection($shipmentItemList)
	{

		$basketList = array();
		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{

			/** @var BasketItem $basketItem */
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}

			/** @var ShipmentItemCollection $shipmentItemCollection */
			$shipmentItemCollection = $shipmentItem->getCollection();
			if (!$shipmentItemCollection)
			{
				throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
			}

			/** @var Shipment $shipment */
			$shipment = $shipmentItemCollection->getShipment();

			if (!$shipment)
			{
				throw new ObjectNotFoundException('Entity "Shipment" not found');
			}

			$needShip = $shipment->needShip();
			if ($needShip === null)
			{
				continue;
			}

			$reserved = ((($shipmentItem->getQuantity() - $shipmentItem->getReservedQuantity()) == 0)
				|| ($shipment->getField('RESERVED') == "Y"));

			if ($basketItem->isBundleParent()
				|| (!$basketItem->isBundleParent() && !$basketItem->isBundleChild()))
			{

				$basketList[$basketItem->getBasketCode()] = array(
					'BASKET_ITEM' => $basketItem,
					'RESERVED' => ($reserved ? "Y" : "N"),
					'NEED_SHIP' => $needShip,
					'SHIPMENT_ITEM' => $shipmentItem
				);
			}

			if($basketItem->isBundleParent())
			{
				/** @var ShipmentItem $bundleShipmentItem */
				foreach ($shipmentItemCollection as $bundleShipmentItem)
				{
					/** @var BasketItem $bundleBasketItem */
					$bundleBasketItem = $bundleShipmentItem->getBasketItem();

					if($bundleBasketItem->isBundleChild())
					{
						$bundleParentBasketItem = $bundleBasketItem->getParentBasketItem();
						if ($bundleParentBasketItem->getBasketCode() == $basketItem->getBasketCode())
						{

							$basketList[$bundleBasketItem->getBasketCode()] = array(
								'BASKET_ITEM' => $bundleBasketItem,
								'RESERVED' => ($reserved ? "Y" : "N"),
								'NEED_SHIP' => $needShip,
								'SHIPMENT_ITEM' => $shipmentItem
							);
						}
					}
				}
			}


		}

		return $basketList;
	}

	/**
	 * @param $shipmentItemList
	 *
	 * @return array
	 * @throws ObjectNotFoundException
	 */
	protected static function getBasketCountFromShipmentItemCollection($shipmentItemList)
	{

		$basketCountList = array();
		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{

			/** @var BasketItem $basketItem */
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}

			if ($basketItem->isBundleParent()
				|| (!$basketItem->isBundleParent() && !$basketItem->isBundleChild()))
			{
				$basketCountList[$basketItem->getBasketCode()] = floatval($shipmentItem->getQuantity());
			}


			if($basketItem->isBundleParent())
			{
				/** @var ShipmentItem $bundleShipmentItem */
				foreach ($shipmentItemList as $bundleShipmentItem)
				{
					/** @var BasketItem $bundleBasketItem */
					$bundleBasketItem = $bundleShipmentItem->getBasketItem();

					if($bundleBasketItem->isBundleChild())
					{
						$bundleParentBasketItem = $bundleBasketItem->getParentBasketItem();
						if ($bundleParentBasketItem->getBasketCode() == $basketItem->getBasketCode())
						{
							$basketCountList[$bundleBasketItem->getBasketCode()] = floatval($bundleShipmentItem->getQuantity());
						}
					}
				}
			}

		}

		return $basketCountList;
	}

	/**
	 * @param $shipmentItemList
	 *
	 * @return Result
	 */
	protected static function getStoreDataFromShipmentItemCollection($shipmentItemList)
	{
		$result = new Result();
		$list = Internals\Catalog\Provider::createMapShipmentItemCollectionStoreData($shipmentItemList);
		if (!empty($list))
		{
			$result->setData(array(
				'STORE_DATA_LIST' => $list
			));
		}
		return $result;
	}

	/**
	 * @param Basket BasketItemCollection
	 * @param BasketItem|null $refreshItem
	 *
	 * @return array
	 */
	protected static function makeArrayFromBasketCollection(BasketItemCollection $basketCollection, BasketItem $refreshItem = null)
	{
		$basketList = array();
		/** @var BasketItem $basketItem */
		foreach ($basketCollection as $basketItem)
		{
			if ($refreshItem !== null)
			{

				if ($basketItem->getBasketCode() != $refreshItem->getBasketCode() && $basketItem->isBundleParent())
				{
					if ($bundleCollection = $basketItem->getBundleCollection())
					{
						$foundItem = false;
						/** @var BasketItem $bundleBasketItem */
						foreach ($bundleCollection as $bundleBasketItem)
						{
							if ($bundleBasketItem->getBasketCode() == $refreshItem->getBasketCode())
							{
								$foundItem = true;
								break;
							}
						}

						if (!$foundItem)
							continue;

						$basketList[] = $bundleBasketItem;
						continue;
					}
				}
				elseif ($basketItem->getBasketCode() != $refreshItem->getBasketCode())
				{
					continue;
				}

				$basketList[] = $basketItem;

				continue;
			}

			$basketList[] = $basketItem;

		}

		return $basketList;
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	public static function tryReserveShipment(Shipment $shipment)
	{
		$result = new Result();

		/** @var ShipmentItemCollection $shipmentCollection */
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		$shipmentItemList = $shipmentItemCollection->getShippableItems();
		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			try
			{
				/** @var Result $r */
				$r = static::tryReserveShipmentItem($shipmentItem);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
				elseif ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
				}
			}
			catch(\Exception $e)
			{
				/** @var Shipment $shipment */
				if (!$shipment = $shipmentItemCollection->getShipment())
				{
					throw new ObjectNotFoundException('Entity "Shipment" not found');
				}
				else
				{
					throw new $e;
				}

			}

		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	public static function tryUnreserveShipment(Shipment $shipment)
	{
		$result = new Result();
		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $shipment->getShipmentItemCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!($shipmentCollection = $shipment->getCollection()))
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		if (!($order = $shipmentCollection->getOrder()))
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			/** @var Result $r */
			$r = static::tryUnreserveShipmentItem($shipmentItem);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				EntityMarker::addMarker($order, $shipment, $r);
				if (!$shipment->isSystem())
				{
					$shipment->setField('MARKED', 'Y');
				}
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
		}

		return $result;
	}

	/**
	 * @param ShipmentItem $shipmentItem
	 *
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 */
	public static function tryReserveShipmentItem(ShipmentItem $shipmentItem)
	{
		$result = new Result();

		if (floatval($shipmentItem->getQuantity()) == floatval($shipmentItem->getReservedQuantity()))
		{
			return $result;
		}

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $shipmentItem->getCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		if (!$shipment = $shipmentItemCollection->getShipment())
		{
			throw new ObjectNotFoundException('Entity "Shipment" not found');
		}
		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		/** @var BasketItem $basketItem */
		if (!$basketItem = $shipmentItem->getBasketItem())
		{
			$result->addError( new ResultError(
			   Loc::getMessage('SALE_PROVIDER_BASKET_ITEM_NOT_FOUND',  array(
				   '#BASKET_ITEM_ID#' => $shipmentItem->getBasketId(),
				   '#SHIPMENT_ID#' => $shipment->getId(),
				   '#SHIPMENT_ITEM_ID#' => $shipmentItem->getId(),
			   )),
			   'PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_BASKET_ITEM') );
			return $result;
		}

		if ($basketItem->isBundleParent())
		{
			return $result;
		}

		$needQuantity = ($shipmentItem->getQuantity() - $shipmentItem->getReservedQuantity());
		$canReserve = false;

		$providerName  = $basketItem->getProvider();

		if (class_exists($providerName))
		{
			if (empty($context))
			{
				if ($order)
				{
					$context = array(
						'USER_ID' => $order->getUserId(),
						'SITE_ID' => $order->getSiteId(),
						'CURRENCY' => $order->getCurrency(),
					);
				}
				else
				{
					global $USER;
					$context = array(
						'USER_ID' => $USER->getId(),
						'SITE_ID' => SITE_ID,
						'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
					);
				}
			}

			$availableQuantityData = array();

			$providerClass = new $providerName($context);
			if ($providerClass instanceof SaleProviderBase)
			{
				$creator = Internals\ProviderCreator::create($context);
				$shipmentProductData = $creator->createItemForReserveByShipmentItem($shipmentItem);
				$creator->addProductData($shipmentProductData);

				$r = $creator->getAvailableQuantity();
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (!empty($resultData['AVAILABLE_QUANTITY_LIST']))
					{
						$productId = $basketItem->getProductId();

						$resultAvailableQuantityList = $resultData['AVAILABLE_QUANTITY_LIST'];
						if (mb_substr($providerName, 0, 1) == "\\")
						{
							$providerName = mb_substr($providerName, 1);
						}

						if (isset($resultAvailableQuantityList[$providerName]) && isset($resultAvailableQuantityList[$providerName][$productId]))
						{
							$availableQuantityData = array(
								'HAS_PROVIDER' => true,
								'AVAILABLE_QUANTITY' => $resultAvailableQuantityList[$providerName][$productId]
							);
						}
					}

				}
				else
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}
			else
			{
				/** @var Result $r */
				$r = static::tryReserveBasketItem($basketItem, $needQuantity);

				$availableQuantityData = $r->getData();
			}
		}
		else
		{
			/** @var Result $r */
			$r = static::tryReserveBasketItem($basketItem, $needQuantity);

			$availableQuantityData = $r->getData();
		}

		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
			return $result;
		}

		if (array_key_exists('AVAILABLE_QUANTITY', $availableQuantityData))
		{
			$availableQuantity = $availableQuantityData['AVAILABLE_QUANTITY'];
		}
		else
		{
			$result->addWarning( new ResultWarning(Loc::getMessage('SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY', array(
				'#PRODUCT_NAME#' => $basketItem->getField('NAME')
			)), 'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY') );
			return $result;
		}

		if (array_key_exists('HAS_PROVIDER', $availableQuantityData))
		{
			$canReserve = $availableQuantityData['HAS_PROVIDER'];
		}

		if ($canReserve && array_key_exists('QUANTITY_TRACE', $availableQuantityData))
		{
			$canReserve = $availableQuantityData['QUANTITY_TRACE'];
		}

		if ($canReserve)
		{
			if ($r->isSuccess() && ($needQuantity > 0) && ($needQuantity > $availableQuantity)
				/*|| ($needReserved < 0) && ($availableQuantity < $needReserved) */)
			{
				$result->addWarning(new ResultWarning(Loc::getMessage("SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_QUANTITY_NOT_ENOUGH", array(
					'#PRODUCT_NAME#' => $basketItem->getField('NAME')
				)), "SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_QUANTITY_NOT_ENOUGH"));
				return $result;
			}

			// is not completely correct, but will be processed in real reservations while saving
			if (($availableQuantity < 0) && ($shipmentItem->getReservedQuantity() + $availableQuantity < 0))
			{
				$availableQuantity = -1 * $shipmentItem->getReservedQuantity();
			}

			if (Configuration::getProductReservationCondition() != ReserveCondition::ON_SHIP)
			{

				$reservedQuantity = ($availableQuantity >= $needQuantity ? $needQuantity : $availableQuantity);

				static::addReservationPoolItem($order->getInternalId(), $shipmentItem->getBasketItem(), $reservedQuantity);

				$r = $shipmentItem->setField('RESERVED_QUANTITY', $shipmentItem->getReservedQuantity() + $reservedQuantity);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		$result->addData(array(
							'CAN_RESERVE' => $canReserve
						 ));

		return $result;
	}

	/**
	 * @param ShipmentItem $shipmentItem
	 *
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Exception
	 */
	public static function tryUnreserveShipmentItem(ShipmentItem $shipmentItem)
	{
		$result = new Result();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $shipmentItem->getCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		if (!$shipment = $shipmentItemCollection->getShipment())
		{
			throw new ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		/** @var BasketItem $basketItem */
		if (!$basketItem = $shipmentItem->getBasketItem())
		{
			$result->addError( new ResultError(
			   Loc::getMessage('SALE_PROVIDER_BASKET_ITEM_NOT_FOUND',  array(
				   '#BASKET_ITEM_ID#' => $shipmentItem->getBasketId(),
				   '#SHIPMENT_ID#' => $shipment->getId(),
				   '#SHIPMENT_ITEM_ID#' => $shipmentItem->getId(),
			   )),
			   'PROVIDER_TRY_UNRESERVED_SHIPMENT_ITEM_WRONG_BASKET_ITEM')
			);
			return $result;
		}

		if ($basketItem->isBundleParent())
		{
			return $result;
		}

		$quantity = $shipmentItem->getReservedQuantity();

		$canReserve = false;

		$providerName  = $basketItem->getProvider();

		$providerExists = false;
		$availableQuantityData = array(
			'HAS_PROVIDER' => true,
			'AVAILABLE_QUANTITY' => $quantity
		);

		if (class_exists($providerName))
		{
			$providerClass = new $providerName();
			if ($providerClass instanceof SaleProviderBase)
			{
				$providerExists = true;
			}
		}

		if (!$providerExists)
		{
			if (!array_key_exists("IBXSaleProductProvider", class_implements($providerName)))
			{
				$availableQuantityData['HAS_PROVIDER'] = false;
			}
		}

		if (array_key_exists('HAS_PROVIDER', $availableQuantityData))
		{
			$canReserve = $availableQuantityData['HAS_PROVIDER'];
		}

		if ($canReserve)
		{

			static::addReservationPoolItem($order->getInternalId(), $shipmentItem->getBasketItem(), $quantity);

			$reservedQuantity = ($shipmentItem->getReservedQuantity() > 0 ? $shipmentItem->getReservedQuantity() + $quantity : 0);

			$needShip = $shipment->needShip();
			if ($needShip)
			{
				$shipmentItem->setFieldNoDemand('RESERVED_QUANTITY', $reservedQuantity);
			}
			else
			{
				$r = $shipmentItem->setField('RESERVED_QUANTITY', $reservedQuantity);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		$result->addData(array(
							 'CAN_RESERVE' => $canReserve
						 ));

		return $result;
	}

	/**
	 * @param BasketItem $basketItem
	 * @param $quantity
	 * @return Result
	 * @throws NotSupportedException
	 */
	protected static function tryReserveBasketItem(BasketItem $basketItem, $quantity)
	{
		$result = new Result();

		$provider = $basketItem->getProvider();

		if (!$basketItem->isBundleChild())
		{
			/** @var Basket $basket */
			$basket = $basketItem->getCollection();
		}
		else
		{
			/** @var BasketItem $parentBasketItem */
			$parentBasketItem = $basketItem->getParentBasketItem();

			/** @var Basket $basket */
			$basket = $parentBasketItem->getCollection();
		}

		$order = $basket->getOrder();
		$hasProvider = false;
		$quantityTrace = null;

		$poolQuantity = static::getReservationPoolItem($order->getInternalId(), $basketItem);
		$tryQuantity = $quantity + $poolQuantity;

		if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{
			$hasProvider = true;
			$r = static::checkAvailableProductQuantity($basketItem, $tryQuantity);

			$availableQuantityData = $r->getData();
			if (array_key_exists('AVAILABLE_QUANTITY', $availableQuantityData))
			{
				$availableQuantity = floatval($availableQuantityData['AVAILABLE_QUANTITY']);
			}
			else
			{
				$result->addWarning(new ResultWarning(Loc::getMessage('SALE_PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY', array(
					'#PRODUCT_NAME#' => $basketItem->getField('NAME')
				)), 'PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY'));
				return $result;
			}

			if (array_key_exists('QUANTITY_TRACE', $availableQuantityData))
			{
				$quantityTrace = $availableQuantityData['QUANTITY_TRACE'];
			}

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}

			$availableQuantity -= floatval($poolQuantity);
		}
		else
		{
			$availableQuantity = $quantity;
		}

		$fields = array(
			'AVAILABLE_QUANTITY' => $availableQuantity,
			'HAS_PROVIDER' => $hasProvider,
		);

		if ($quantityTrace !== null)
		{
			$fields['QUANTITY_TRACE'] = $quantityTrace;
		}

		$result->setData($fields);
		return $result;
	}


	/**
	 * @param BasketItem $basketItem
	 * @param $quantity
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 */
	private static function reserveBasketItem(BasketItem $basketItem, $quantity)
	{
		$result = new Result();

		$provider = $basketItem->getProvider();

		/** @var Basket $basket */
		if (!$basket = $basketItem->getCollection())
		{
			throw new ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var Order $order */
		if (!$order = $basket->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		$r = static::reserveProduct($provider, $basketItem->getProductId(), $quantity);

		if ($r->hasWarnings() || !$r->isSuccess())
		{
			if (!$r->isSuccess())
			{
				$result->addWarnings($r->getErrors());
			}

			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}

			/** @var Basket $basket */
			if (!$basket = $basketItem->getCollection())
			{
				throw new ObjectNotFoundException('Entity "Basket" not found');
			}

			if ($order = $basket->getOrder())
			{
				/** @var ShipmentCollection $shipmentCollection */
				if (!$shipmentCollection = $order->getShipmentCollection())
				{
					throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
				}

				/** @var Shipment $shipment */
				foreach ($shipmentCollection as $shipment)
				{
					/** @var ShipmentItemCollection $shipmentItemCollection */
					if (!$shipmentItemCollection = $shipment->getShipmentItemCollection())
					{
						throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
					}

					if($shipmentItemCollection->getItemByBasketCode($basketItem->getBasketCode()))
					{
						EntityMarker::addMarker($order, $shipment, $result);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @internal
	 * @param $provider
	 * @param $productId
	 * @param $quantity
	 *
	 * @return Result
	 */
	public static function reserveProduct($provider, $productId, $quantity)
	{
		global $APPLICATION;

		$result = new Result();
		$fields = array();

		if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{
			$hasProvider = true;
			$data = array("PRODUCT_ID" => $productId);

			if ($quantity > 0)
			{
				$data["UNDO_RESERVATION"] = "N";
				$data["QUANTITY_ADD"] = $quantity;
			}
			else
			{
				$data["UNDO_RESERVATION"] = "Y";
				$data["QUANTITY_ADD"] = abs($quantity);
			}

			$APPLICATION->ResetException();
			if (($resultReserveData = $provider::ReserveProduct($data)))
			{
				if ($resultReserveData['RESULT'])
				{
					$fields['QUANTITY'] = $resultReserveData['QUANTITY_RESERVED'];

					if ($quantity < 0)
					{
						$fields['QUANTITY'] = $quantity;
					}

					$fields['HAS_PROVIDER'] = $hasProvider;
					$result->setData($fields);
					$exception = $APPLICATION->GetException();
					if ($exception)
					{
						$result->addWarning(new ResultWarning($exception->GetString(), $exception->GetID()));
					}
					return $result;
				}
				else
				{
					$exception = $APPLICATION->GetException();
					if ($exception)
					{
						$result->addWarning(new ResultWarning($exception->GetString(), $exception->GetID()));
					}
					else
					{
						$result->addError(new ResultError(Loc::getMessage('SALE_PROVIDER_RESERVE_BASKET_ITEM_ERROR'), 'SALE_PROVIDER_RESERVE_BASKET_ITEM_ERROR')) ;
					}
				}

			}
			else
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_PROVIDER_RESERVE_BASKET_ITEM_ERROR'), 'SALE_PROVIDER_RESERVE_BASKET_ITEM_ERROR')) ;
			}

		}
		else
		{
			$fields['QUANTITY'] = $quantity;
			$result->setData($fields);
		}

		return $result;
	}

	/**
	 * @param ShipmentItem $shipmentItem
	 * @param $quantity
	 *
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 */
	public static function reserveShipmentItem(ShipmentItem $shipmentItem, $quantity)
	{
		global $APPLICATION;
		$result = new Result();
		$fields = array();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItem->getCollection();
		if (!$shipmentItemCollection)
		{
			throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		$shipment = $shipmentItemCollection->getShipment();
		if (!$shipment)
		{
			throw new ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var BasketItem $basketItem */
		$basketItem = $shipmentItem->getBasketItem();
		if (!$basketItem)
		{
			$result->addError( new ResultError(
			   Loc::getMessage('SALE_PROVIDER_BASKET_ITEM_NOT_FOUND',  array(
				   '#BASKET_ITEM_ID#' => $shipmentItem->getBasketId(),
				   '#SHIPMENT_ID#' => $shipment->getId(),
				   '#SHIPMENT_ITEM_ID#' => $shipmentItem->getId(),
			   )),
			   'PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_BASKET_ITEM') );
			return $result;
		}

		$provider = $basketItem->getProvider();


		if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{

			$data = array(
				"PRODUCT_ID" => $basketItem->getProductId(),
				"UNDO_RESERVATION" => "N",
				"QUANTITY_ADD"   => $quantity,
				"ORDER_DEDUCTED" => $shipment->isShipped()? "Y" : "N",
			);

			$APPLICATION->ResetException();
			if (($resultReserveData = $provider::ReserveProduct($data)))
			{
				if ($resultReserveData['RESULT'])
				{
					$fields['QUANTITY'] = $resultReserveData['QUANTITY_RESERVED'];

					if (isset($resultReserveData['QUANTITY_NOT_RESERVED']) && floatval($resultReserveData['QUANTITY_NOT_RESERVED']) > 0)
					{
						$fields['QUANTITY'] = $shipmentItem->getReservedQuantity() + ($shipmentItem->getQuantity() - $shipmentItem->getReservedQuantity()) -  $resultReserveData['QUANTITY_NOT_RESERVED'];
					}

					$result->setData($fields);
					return $result;
				}
				else
				{
					if ($ex = $APPLICATION->GetException())
					{
						if ($ex->GetID() != "ALREADY_FLAG")
							$result->addError(new ResultError($ex->GetString())) ;
					}
					else
					{
						$result->addError(new ResultError(Loc::getMessage('SALE_PROVIDER_RESERVE_BASKET_ITEM_ERROR'), 'SALE_PROVIDER_RESERVE_BASKET_ITEM_ERROR')) ;
					}
				}

			}

		}
		elseif (class_exists($provider))
		{
			/** @var ShipmentCollection $shipmentCollection */
			$shipmentCollection = $shipment->getCollection();
			if (!$shipmentCollection)
			{
				throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			/** @var Order $order */
			$order = $shipmentCollection->getOrder();
			if (!$order)
			{
				throw new ObjectNotFoundException('Entity "Order" not found');
			}

			$context = array(
				'SITE_ID' => $order->getSiteId(),
				'CURRENCY' => $order->getCurrency(),
			);

			if ($order->getUserId() > 0)
			{
				$context['USER_ID'] = $order->getUserId();
			}
			else
			{
				global $USER;
				$context['USER_ID'] = $USER->getId();
			}

			/** @var SaleProviderBase $providerClass */
			$providerClass = new $provider($context);
			if ($providerClass && $providerClass instanceof SaleProviderBase)
			{

				$creator = Internals\ProviderCreator::create($context);
				$creator->addShipmentItem($shipmentItem);

				$r = $creator->reserve();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}

			}
		}

		if (!empty($fields))
		{
			$result->setData($fields);
		}
		return $result;
	}

	/**
	 * reduce in the quantity of product if the reservation is disabled
	 * @param ShipmentCollection $shipmentCollection
	 * @param array $shipmentReserveList
	 *
	 * @throws ObjectNotFoundException
	 */
	public static function reduceProductQuantity(ShipmentCollection $shipmentCollection, array $shipmentReserveList = array())
	{
		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		$options = array(
			'ORDER_DEDUCTED' => $order->isShipped()
		);

		$shipmentReserveListKeys = array_keys($shipmentReserveList);

		foreach ($shipmentCollection as $shipmentKey => $shipment)
		{
			if (!in_array($shipment->getId(), $shipmentReserveListKeys))
			{
				unset($shipmentCollection[$shipmentKey]);
			}
		}


		foreach ($shipmentCollection as $shipment)
		{
			$basketProviderList = static::getProviderBasketFromShipment($shipment);

			$productList = static::getProductListFromBasketProviderList($basketProviderList);

			if (!empty($basketProviderList))
			{
				foreach ($basketProviderList as $provider => $providerBasketItemList)
				{
					$shipmentReserveListData = array();
					if (!empty($shipmentReserveList)
						&& !empty($shipmentReserveList[$shipment->getId()]) && is_array($shipmentReserveList[$shipment->getId()]))
					{
						$shipmentReserveListData = $shipmentReserveList[$shipment->getId()];
					}

					$result = $provider::reduceProductQuantity($providerBasketItemList, $productList, $shipmentReserveListData, $options);
				}
			}

		}
	}

	/**
	 * increase in the quantity of product if the reservation is disabled
	 * @param ShipmentCollection $shipmentCollection
	 * @param array $shipmentReserveList
	 *
	 * @throws ObjectNotFoundException
	 */
	public static function increaseProductQuantity(ShipmentCollection $shipmentCollection, array $shipmentReserveList = array())
	{
		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		$options = array(
			'ORDER_DEDUCTED' => $order->isShipped()
		);

		$shipmentReserveListKeys = array_keys($shipmentReserveList);

		foreach ($shipmentCollection as $shipmentKey => $shipment)
		{
			if (!in_array($shipment->getId(), $shipmentReserveListKeys))
			{
				unset($shipmentCollection[$shipmentKey]);
			}
		}


		foreach ($shipmentCollection as $shipment)
		{
			$basketProviderList = static::getProviderBasketFromShipment($shipment);

			$productList = static::getProductListFromBasketProviderList($basketProviderList);

			if (!empty($basketProviderList))
			{
				foreach ($basketProviderList as $provider => $providerBasketItemList)
				{
					$shipmentReserveListData = array();
					if (!empty($shipmentReserveList)
						&& !empty($shipmentReserveList[$shipment->getId()]) && is_array($shipmentReserveList[$shipment->getId()]))
					{
						$shipmentReserveListData = $shipmentReserveList[$shipment->getId()];
					}

					$result = $provider::increaseProductQuantity($providerBasketItemList, $productList, $shipmentReserveListData, $options);
				}
			}

		}
	}

	/**
	 * @param BasketItem $basketItem
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	public static function getProductStores(BasketItem $basketItem)
	{
		$result = new Result();

		$basketItemProviderMap = static::createProviderBasketItemMap($basketItem, array('SITE_ID'));

		if (!empty($basketItemProviderMap))
		{
			$provider = $basketItemProviderMap['PROVIDER'];

			if (array_key_exists("IBXSaleProductProvider", class_implements($provider)))
			{
				$productId = $basketItemProviderMap["PRODUCT_ID"];
				$data = array(
					"PRODUCT_ID" => $productId,
					"SITE_ID" => $basketItemProviderMap["SITE_ID"],
					'BASKET_ID' => $basketItemProviderMap['BASKET_ID']
				);

				$r = static::getStores($provider, $data);
				if ($r->isSuccess())
				{
					$resultProductData = $r->getData();
					if (array_key_exists($productId, $resultProductData))
					{
						$result->setData($resultProductData);
					}
				}

			}
			elseif (class_exists($provider))
			{
				/** @var Basket $basket */
				$basket = $basketItem->getCollection();
				if (!$basket)
				{
					throw new ObjectNotFoundException('Entity "Basket" not found');
				}

				/** @var Order $order */
				$order = $basket->getOrder();
				if (!$order)
				{
					throw new ObjectNotFoundException('Entity "Order" not found');
				}

				$context = array(
					'SITE_ID' => $order->getSiteId(),
					'CURRENCY' => $order->getCurrency(),
				);

				if ($order->getUserId() > 0)
				{
					$context['USER_ID'] = $order->getUserId();
				}
				else
				{
					global $USER;
					$context['USER_ID'] = $USER->getId();
				}

				/** @var SaleProviderBase $providerClass */
				$providerClass = new $provider($context);
				if ($providerClass && $providerClass instanceof SaleProviderBase)
				{

					$creator = Internals\ProviderCreator::create($context);
					$creator->addBasketItem($basketItem);

					$r = $creator->getProductStores();
					if ($r->isSuccess())
					{
						$result->setData($r->getData());
					}
					else
					{
						$result->addErrors($r->getErrors());
					}

				}
			}
		}

		return $result;
	}

	/**
	 * @internal
	 * @param $provider
	 * @param array $fields
	 *
	 * @return Result
	 */
	public static function getStores($provider, array $fields)
	{
		$result = new Result();
		$resultData = $provider::getProductStores($fields);

		$result->setData(
			array(
				$fields['PRODUCT_ID'] => $resultData
			)
		);

		return $result;
	}

	/**
	 * @param BasketItem $basketItem
	 * @param array $params
	 *
	 * @return bool
	 * @throws ObjectNotFoundException
	 */
	public static function checkProductBarcode(BasketItem $basketItem, array $params = array())
	{

		$provider = $basketItem->getProvider();
		$productId = $basketItem->getProductId();
		$data = array(
			'BARCODE' => $params['BARCODE'],
			'STORE_ID' => $params['STORE_ID'],
			'PRODUCT_ID' => $productId
		);
		$result = false;

		if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{
			$r = static::checkBarcode($provider, $data);
			if ($r->isSuccess())
			{
				$resultData = $r->getData();
				if (!empty($resultData) && array_key_exists($productId, $resultData))
				{
					$result = $resultData[$productId];
				}
			}
		}
		elseif (class_exists($provider))
		{
			/** @var Basket $basket */
			$basket = $basketItem->getCollection();
			if (!$basket)
			{
				throw new ObjectNotFoundException('Entity "Basket" not found');
			}

			$order = $basket->getOrder();

			if ($order)
			{
				$context = array(
					'USER_ID' => $order->getUserId(),
					'SITE_ID' => $order->getSiteId(),
					'CURRENCY' => $order->getCurrency(),
				);
			}
			else
			{
				global $USER;
				$context = array(
					'USER_ID' => $USER->getId(),
					'SITE_ID' => SITE_ID,
					'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
				);
			}

			$creator = Internals\ProviderCreator::create($context);

			$providerClass = $basketItem->getProviderEntity();
			if ($providerClass instanceof SaleProviderBase)
			{
				$creator->addBasketItemBarcodeData($basketItem, $data);
			}

			$r = $creator->checkBarcode();
			if ($r->isSuccess())
			{
				if (!empty($providerClass))
				{
					$reflect = new \ReflectionClass($provider);
					$providerName = $reflect->getName();
				}
				else
				{
					$providerName = $basketItem->getCallbackFunction();
				}

				$resultData = $r->getData();
				if (!empty($resultData) && array_key_exists('BARCODE_CHECK_LIST', $resultData))
				{
					$resultList = $resultData['BARCODE_CHECK_LIST'];
					if (isset($resultList[$providerName]) && isset($resultList[$providerName][$data['BARCODE']]))
					{
						$result = $resultList[$providerName][$data['BARCODE']];
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @internal
	 * @param $provider
	 * @param array $barcodeParams
	 *
	 * @return Result
	 */
	public static function checkBarcode($provider, array $barcodeParams)
	{
		$result = new Result();
		if (!array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{
			return $result;
		}

		$resultData = $provider::checkProductBarcode($barcodeParams);

		$result->setData(
			array(
				$barcodeParams["PRODUCT_ID"] => $resultData
			)
		);

		return $result;
	}

	/**
	 * @param BasketItem $basketItem
	 *
	 * @return array
	 * @throws ObjectNotFoundException
	 */
	public static function viewProduct(BasketItem $basketItem)
	{
		$result = new Result();
		$basketProviderData = static::createProviderBasketItemMap($basketItem, array('SITE_ID', 'USER_ID'));
		$provider = $basketProviderData['PROVIDER'];
		if (!empty($provider))
		{
			if (array_key_exists("IBXSaleProductProvider", class_implements($provider)))
			{
				$productId = $basketProviderData['PRODUCT_ID'];
				$data = array(
					'PRODUCT_ID' => $productId,
					'USER_ID' => $basketProviderData['USER_ID'],
					'SITE_ID' => $basketProviderData['SITE_ID'],
				);

				$r = static::getViewProduct($provider, $data);
				if ($r->isSuccess())
				{
					$resultProductData = $r->getData();
					if (array_key_exists($productId, $resultProductData))
					{
						$result->setData($resultProductData);
					}
				}

			}
			elseif (class_exists($provider))
			{
				/** @var Basket $basket */
				$basket = $basketItem->getCollection();
				if (!$basket)
				{
					throw new ObjectNotFoundException('Entity "Basket" not found');
				}

				$order = $basket->getOrder();

				if ($order)
				{
					$context = array(
						'USER_ID' => $order->getUserId(),
						'SITE_ID' => $order->getSiteId(),
						'CURRENCY' => $order->getCurrency(),
					);
				}
				else
				{
					global $USER;
					$context = array(
						'USER_ID' => $USER->getId(),
						'SITE_ID' => SITE_ID,
						'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
					);
				}

				$creator = Internals\ProviderCreator::create($context);

				$providerClass = $basketItem->getProviderEntity();
				if ($providerClass instanceof SaleProviderBase)
				{
					$creator->addBasketItem($basketItem);
				}

				$r = $creator->viewProduct();
				if ($r->isSuccess())
				{
					$data = $r->getData();
					if (array_key_exists('VIEW_PRODUCTS_LIST', $data))
					{
						$resultList = $data['VIEW_PRODUCTS_LIST'];

						if (!empty($resultList))
						{
							$productId = $basketItem->getProductId();
							$result = reset($resultList);

							$result->setData(
								array(
									$productId => reset($resultList)
								)
							);
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @internal
	 * @param $provider
	 * @param array $fields
	 *
	 * @return Result
	 * @throws ArgumentTypeException
	 */
	public static function getViewProduct($provider, array $fields)
	{
		$result = new Result();

		if (!array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{
			throw new ArgumentTypeException('provider');
		}

		$resultData = $provider::viewProduct($fields);
		$result->setData(
			array(
				$fields['PRODUCT_ID'] => $resultData
			)
		);
		return $result;
	}

	/**
	 * @param BasketItem $basketItem
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	public static function recurringOrderProduct(BasketItem $basketItem)
	{
		$result = new Result();
		$basketProviderData = static::createProviderBasketItemMap($basketItem, array('SITE_ID', 'USER_ID'));
		$provider = $basketProviderData['PROVIDER'];
		if (!empty($provider))
		{
			if (array_key_exists("IBXSaleProductProvider", class_implements($provider)))
			{
				$data = array(
					'PRODUCT_ID' => $basketProviderData['PRODUCT_ID'],
					'USER_ID' => $basketProviderData['USER_ID'],
				);

				$r = static::recurringProduct($provider, $data);
				if ($r->isSuccess())
				{
					$resultProductData = $r->getData();
					if (array_key_exists($basketProviderData['PRODUCT_ID'], $resultProductData))
					{
						$result->setData($resultProductData);
					}

				}

			}
			elseif (class_exists($provider))
			{
				/** @var Basket $basket */
				$basket = $basketItem->getCollection();
				if (!$basket)
				{
					throw new ObjectNotFoundException('Entity "Basket" not found');
				}

				$order = $basket->getOrder();

				if ($order)
				{
					$context = array(
						'USER_ID' => $order->getUserId(),
						'SITE_ID' => $order->getSiteId(),
						'CURRENCY' => $order->getCurrency(),
					);
				}
				else
				{
					global $USER;
					$context = array(
						'USER_ID' => $USER->getId(),
						'SITE_ID' => SITE_ID,
						'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
					);
				}

				$creator = Internals\ProviderCreator::create($context);

				$providerClass = $basketItem->getProviderEntity();
				if ($providerClass instanceof SaleProviderBase)
				{
					$creator->addBasketItem($basketItem);
				}

				$r = $creator->recurring();
				if ($r->isSuccess())
				{
					$data = $r->getData();
					if (array_key_exists('RECURRING_PRODUCTS_LIST', $data))
					{
						$resultList = $data['RECURRING_PRODUCTS_LIST'];

						if (!empty($resultList))
						{
							$productId = $basketItem->getProductId();
							$result = reset($resultList);

							$result->setData(
								array(
									$productId => reset($resultList)
								)
							);
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param $provider
	 * @param array $fields
	 *
	 * @return Result
	 * @throws ArgumentTypeException
	 */
	public static function recurringProduct($provider, array $fields)
	{
		$result = new Result();
		if (!array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{
			throw new ArgumentTypeException('provider');
		}

		$resultData =  $provider::recurringOrderProduct($fields);
		$result->setData(
			array(
				$fields['PRODUCT_ID'] => $resultData
			)
		);
		return $result;
	}

	/**
	 * @param BasketItemBase $basketItem
	 *
	 * @return array|bool|mixed
	 * @throws ObjectNotFoundException
	 */
	public static function getSetItems(BasketItemBase $basketItem)
	{
		$bundleChildList = array();
		$provider = $basketItem->getProvider();
		if ($provider)
		{
			if (array_key_exists("IBXSaleProductProvider", class_implements($provider)))
			{
				$bundleChildList = $provider::GetSetItems($basketItem->getProductId(), BasketItem::TYPE_SET, array('BASKET_ID' => $basketItem->getId()));
			}
			elseif (class_exists($provider))
			{
				/** @var BasketItemCollection $collection */
				$collection = $basketItem->getCollection();

				/** @var Basket $basket */
				$basket = $collection->getBasket();
				if (!$basket)
				{
					throw new ObjectNotFoundException('Entity "Basket" not found');
				}

				$order = $basket->getOrder();

				if ($order)
				{
					$context = array(
						'SITE_ID' => $order->getSiteId(),
						'USER_ID' => $order->getUserId(),
						'CURRENCY' => $order->getCurrency(),
					);
				}
				else
				{
					global $USER;
					$context = array(
						'SITE_ID' => SITE_ID,
						'USER_ID' => $USER && $USER->GetID() > 0 ? $USER->GetID() : 0,
						'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
					);
				}
				$creator = Internals\ProviderCreator::create($context);

				$creator->addBasketItem($basketItem);

				$r = $creator->getBundleItems();
				if ($r->isSuccess())
				{
					$resultProductListData = $r->getData();
					if (!empty($resultProductListData['BUNDLE_LIST']))
					{
						$bundleChildList = $resultProductListData['BUNDLE_LIST'];
					}
				}

				$order = $basket->getOrder();

				if ($order)
				{
					$context = array(
						'SITE_ID' => $order->getSiteId(),
						'USER_ID' => $order->getUserId(),
						'CURRENCY' => $order->getCurrency(),
					);
				}
				else
				{
					global $USER;
					$context = array(
						'SITE_ID' => SITE_ID,
						'USER_ID' => $USER && $USER->GetID() > 0 ? $USER->GetID() : 0,
						'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
					);
				}
				$creator = Internals\ProviderCreator::create($context);

				$creator->addBasketItem($basketItem);

				$r = $creator->getBundleItems();
				if ($r->isSuccess())
				{
					$resultProductListData = $r->getData();
					if (!empty($resultProductListData['BUNDLE_LIST']))
					{
						$bundleChildList = $resultProductListData['BUNDLE_LIST'];
					}
				}
			}
			else
			{
				$bundleChildList = \CSaleBasket::executeCallbackFunction(
					$basketItem->getField('CALLBACK_FUNC'),
					$basketItem->getField('MODULE'),
					$basketItem->getField('PRODUCT_ID'),
					$basketItem->getField('QUANTITY')
				);
			}

			return $bundleChildList;
		}

		return false;
	}

	/**
	 * @param $providerName
	 * @param array $productData
	 *
	 * @return bool|mixed
	 */
	private static function getBundleChildItemsByProductData($providerName, array $productData)
	{
		if (array_key_exists("IBXSaleProductProvider", class_implements($providerName)))
		{
			$bundleChildList = $providerName::GetSetItems($productData['PRODUCT_ID'], BasketItem::TYPE_SET, array('BASKET_ID' => $productData['BASKET_ID']));
		}
		else
		{
			$bundleChildList = \CSaleBasket::executeCallbackFunction(
				$productData['CALLBACK_FUNC'],
				$productData['MODULE'],
				$productData['PRODUCT_ID'],
				$productData['QUANTITY']
			);
		}

		if (is_array($bundleChildList))
		{
			$bundleChildList = reset($bundleChildList);
		}

		return $bundleChildList;
	}


	/**
	 * @param $providerName
	 * @param array $products
	 *
	 * @return Result
	 */
	public static function getBundleChildItems($providerName, array $products)
	{
		$result = new Result();
		$resultList = array();

		foreach ($products as $productId => $productData)
		{
			$resultList[$productId] = static::getBundleChildItemsByProductData($providerName, $productData);
		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					'BUNDLE_LIST' => $resultList,
				)
			);
		}

		return $result;
	}


	/**
	 * @param $basketProviderList
	 * @param array $productList
	 * @return array|bool
	 */
	protected static function getProductListFromBasketProviderList($basketProviderList, array $productList = array())
	{
		$select = array(
			'ID',
			'CAN_BUY_ZERO',
			'NEGATIVE_AMOUNT_TRACE',
			'QUANTITY_TRACE',
			'QUANTITY',
			'QUANTITY_RESERVED'
		);

		$providerProductList = array();

		if (!empty($basketProviderList))
		{
			foreach ($basketProviderList as $provider => $providerBasketItemList)
			{
				$providerProductList = $provider::getProductList($providerBasketItemList, $productList, $select) + $providerProductList;
			}
		}

		return (!empty($providerProductList) && is_array($providerProductList) ? $providerProductList : false);
	}

	/**
	 * @param BasketItemBase $basketItem
	 * @param $deltaQuantity
	 *
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 */
	public static function checkAvailableProductQuantity(BasketItemBase $basketItem, $deltaQuantity)
	{
		global $APPLICATION;

		$result = new Result();

		$resultProductData = array();

		$orderId = null;
		$userId = null;
		$siteId = null;

		/** @var BasketItemCollection $collection */
		$collection = $basketItem->getCollection();

		/** @var Basket $basket */
		if (!$basket = $collection->getBasket())
		{
			throw new ObjectNotFoundException('Entity "Basket" not found');
		}


		if (($order = $basket->getOrder()) !== null)
		{
			$userId = $order->getUserId();
			$siteId = $order->getSiteId();
		}

		if ($siteId === null)
		{
			$siteId = $basket->getSiteId();
		}

		$provider = $basketItem->getProvider();

		if (!empty($provider))
		{
			if (array_key_exists("IBXSaleProductProvider", class_implements($provider)))
			{
				$needQuantity = $basketItem->getQuantity();
				if ($order && $order->getId() > 0)
				{
					$needQuantity = $deltaQuantity;
				}

				$poolQuantity = 0;

				if ($order)
				{
					$poolQuantity = static::getQuantityPoolItem($order->getInternalId(), $basketItem);
				}

				$checkQuantity = $needQuantity - floatval($poolQuantity);

				$data = array(
					"PRODUCT_ID" => $basketItem->getProductId(),
					"QUANTITY"   => $checkQuantity,
					"USER_ID"   => $userId,
					"SITE_ID"   => $siteId,
					"BASKET_ID" => $basketItem->getId(),
					"CHECK_QUANTITY" => "Y",
					"AVAILABLE_QUANTITY" => "Y",
					'CHECK_PRICE' => 'N',
					'CHECK_COUPONS' => 'N',
					"SELECT_QUANTITY_TRACE" => "Y",
				);

				// TODO: !
				if ($deltaQuantity <= 0 || $checkQuantity == 0)
				{
					$result->setData(array('AVAILABLE_QUANTITY' => $deltaQuantity));
					return $result;
				}

				$hasTrustData = false;

				$trustData = static::getTrustData($siteId, $basketItem->getField('MODULE'), $basketItem->getField('PRODUCT_ID'));

				if (static::isReadTrustData() === true
					&& !empty($trustData) && is_array($trustData))
				{
					$hasTrustData = true;
					$resultProductData = $trustData;
					$productDataRequiredFields = array_merge(static::getProductDataRequiredFields(), array('AVAILABLE_QUANTITY'));
					foreach ($productDataRequiredFields as $requiredField)
					{
						if (!array_key_exists($requiredField, $resultProductData))
						{
							$hasTrustData = false;
							break;
						}
					}

					if ($hasTrustData
						&& roundEx($checkQuantity, SALE_VALUE_PRECISION) > roundEx($resultProductData["AVAILABLE_QUANTITY"], SALE_VALUE_PRECISION))
					{
						$hasTrustData = false;
					}

				}

				if(!$hasTrustData)
				{
					$APPLICATION->ResetException();
					$resultProductData = $provider::GetProductData($data);
					$ex = $APPLICATION->GetException();
					if ($ex)
					{
						$result->addWarning( new ResultWarning($ex->GetString(), $ex->GetID()) );
					}
				}

			}
			elseif (class_exists($provider))
			{
				/** @var SaleProviderBase $providerClass */
				$providerClass = new $provider();
				if ($providerClass && $providerClass instanceof SaleProviderBase)
				{
					$productId = $basketItem->getProductId();
					$products = array(
						$productId => array(
							'ITEM_CODE' => $productId,
							'BASKET_CODE' => $basketItem->getBasketCode(),
							'QUANTITY' => $deltaQuantity,
						)
					);
					$r = $providerClass->getAvailableQuantity($products);
					if ($r->isSuccess())
					{
						$resultData = $r->getData();
						if (!empty($resultData['AVAILABLE_QUANTITY_LIST']))
						{
							$resultProductData = array(
								'AVAILABLE_QUANTITY' => reset($resultData['AVAILABLE_QUANTITY_LIST'])
							);
						}
					}
				}
			}
			else
			{
				$APPLICATION->ResetException();
				$resultProductData = \CSaleBasket::ExecuteCallbackFunction(
					$basketItem->getField('CALLBACK_FUNC'),
					$basketItem->getField('MODULE'),
					$basketItem->getProductId(),
					$basketItem->getQuantity()
				);

				if ($ex = $APPLICATION->GetException())
				{
					$result->addWarning( new ResultWarning($ex->GetString(), $ex->GetID()) );
				}
			}
		}
		else
		{
			$availableQuantity = $basketItem->getQuantity();
			if ($deltaQuantity <= 0)
			{
				$availableQuantity = $deltaQuantity;
			}
			$result->setData(array(
								 'AVAILABLE_QUANTITY' => $availableQuantity
							 ));
			return $result;
		}

		$fields = array();

		if (array_key_exists('AVAILABLE_QUANTITY', $resultProductData))
		{
			$fields['AVAILABLE_QUANTITY'] = $resultProductData['AVAILABLE_QUANTITY'];
		}

		if (array_key_exists('QUANTITY_TRACE', $resultProductData))
		{
			$fields['QUANTITY_TRACE'] = ($resultProductData['QUANTITY_TRACE'] == "Y");
		}

		if (!empty($fields))
		{
			$result->setData($fields);
		}

		return $result;
	}

	/**
	 * @param $providerClass
	 * @param $productData
	 * @param array $context
	 *
	 * @return Result
	 * @throws ArgumentNullException
	 */
	private static function getAvailableQuantityByProductData($providerClass, $productData, array $context)
	{
		global $APPLICATION;

		$result = new Result();

		$callbackFunction = null;
		$basketItem =  null;
		if (!empty($productData['BASKET_ITEM']))
		{
			$basketItem = $productData['BASKET_ITEM'];
		}

		if (!empty($productData['CALLBACK_FUNC']))
		{
			$callbackFunction = $productData['CALLBACK_FUNC'];
		}

		$resultProductData = array();

		$userId = $context['USER_ID'];
		$siteId = $context['SITE_ID'];

		$productId = $productData['PRODUCT_ID'];

		$productQuantity = 0;
		if (array_key_exists('QUANTITY', $productData))
		{
			$productQuantity = $productData['QUANTITY'];
		}
		elseif (!empty($productData['QUANTITY_LIST']))
		{
			foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
			{
				$productQuantity += $quantity;
			}
		}

		if (!empty($providerClass) && array_key_exists("IBXSaleProductProvider", class_implements($providerClass)))
		{
			if ($productQuantity <= 0)
			{
				$result->setData(
					array(
						'AVAILABLE_QUANTITY' => $productQuantity
					)
				);
				return $result;
			}

			$basketId = null;
			if ($basketItem)
			{
				$basketId = $basketItem->getId();
			}

			$data = array(
				"PRODUCT_ID" => $productId,
				"QUANTITY" => $productQuantity,
				"USER_ID" => $userId,
				"SITE_ID" => $siteId,
				"BASKET_ID" => $basketId,
				"CHECK_QUANTITY" => "Y",
				"AVAILABLE_QUANTITY" => "Y",
				'CHECK_PRICE' => 'N',
				'CHECK_COUPONS' => 'N',
				"SELECT_QUANTITY_TRACE" => "Y",
			);

			// TODO: !
//				if ($deltaQuantity <= 0 || $checkQuantity == 0)
//				{
//					$result->setData(array('AVAILABLE_QUANTITY' => $deltaQuantity));
//					return $result;
//				}

			$hasTrustData = false;

			$trustData = static::getTrustData($siteId, $productData['MODULE'], $productId);

			if (static::isReadTrustData() === true
				&& !empty($trustData) && is_array($trustData))
			{
				$hasTrustData = true;
				$resultProductData = $trustData;
				$productDataRequiredFields = array_merge(static::getProductDataRequiredFields(), array('AVAILABLE_QUANTITY'));
				foreach ($productDataRequiredFields as $requiredField)
				{
					if (!array_key_exists($requiredField, $resultProductData))
					{
						$hasTrustData = false;
						break;
					}
				}

				if ($hasTrustData
					&& roundEx($productQuantity, SALE_VALUE_PRECISION) > roundEx($resultProductData["AVAILABLE_QUANTITY"], SALE_VALUE_PRECISION))
				{
					$hasTrustData = false;
				}

			}

			if(!$hasTrustData)
			{
				$APPLICATION->ResetException();
				$resultProductData = $providerClass::GetProductData($data);
				if ($ex = $APPLICATION->GetException())
				{
					$result->addWarning( new ResultWarning($ex->GetString(), $ex->GetID()) );
				}
			}

		}
		elseif (!empty($callbackFunction))
		{
			$APPLICATION->ResetException();
			$resultProductData = \CSaleBasket::ExecuteCallbackFunction(
				$callbackFunction,
				$productData['MODULE'],
				$productId,
				$productQuantity
			);

			if ($ex = $APPLICATION->GetException())
			{
				$result->addWarning( new ResultWarning($ex->GetString(), $ex->GetID()) );
			}
		}
		else
		{
			$result->setData(
				array(
					'AVAILABLE_QUANTITY' => $productQuantity
				)
			);
			return $result;
		}

		$fields = array();

		if (!empty($resultProductData))
		{
			if (array_key_exists('AVAILABLE_QUANTITY', $resultProductData))
			{
				$fields['AVAILABLE_QUANTITY'] = $resultProductData['AVAILABLE_QUANTITY'];
			}

			if (array_key_exists('QUANTITY_TRACE', $resultProductData))
			{
				$fields['QUANTITY_TRACE'] = ($resultProductData['QUANTITY_TRACE'] == "Y");
			}
		}

		if (!empty($fields))
		{
			$result->setData($fields);
		}

		return $result;
	}

	/**
	 * @param $providerClass
	 * @param $productData
	 * @param array $context
	 *
	 * @return Result
	 * @throws ArgumentNullException
	 */
	private static function getProviderDataByProductData($providerClass, $productData, array $context)
	{
		$result = new Result();

		$providerName = null;
		if (!empty($providerClass))
		{
			$reflect = new \ReflectionClass($providerClass);
			$providerName = $reflect->getName();
		}

		$productId = $productData['PRODUCT_ID'];

		$items = array( $productId => $productData );

		$r = static::getProductDataByList($items, $providerName, array('PRICE', 'COUPONS', 'AVAILABLE_QUANTITY', 'QUANTITY'), $context);

		if ($r->isSuccess())
		{
			$resultData = $r->getData();
			$isExistsProductDataList = isset($resultData['PRODUCT_DATA_LIST']) && !empty($resultData['PRODUCT_DATA_LIST']);
			$isExistsProductData = isset($resultData['PRODUCT_DATA_LIST'][$productId]);

			if ($isExistsProductDataList && $isExistsProductData)
			{
				$result->setData($resultData['PRODUCT_DATA_LIST'][$productId]);
			}
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return Result
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 */
	public static function deliverShipment(Shipment $shipment)
	{

		$result = new Result();

		$needDeliver = null;
		if ($shipment->getFields()->isChanged('ALLOW_DELIVERY'))
		{
			$needDeliver = $shipment->getField('ALLOW_DELIVERY') === "Y";
		}

		if ($needDeliver === null || ($needDeliver === false && $shipment->getId() <= 0))
			return $result;

		$resultList = array();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $shipment->getShipmentItemCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new ObjectNotFoundException('Entity "Order" not found');
		}

		/** @var Basket $basket */
		if (!$basket = $order->getBasket())
		{
			return $result;
		}

		$basketList = static::getBasketFromShipmentItemCollection($shipmentItemCollection);

		$basketProviderMap = static::createProviderBasketMap($basketList, array('ORDER_ID', 'USER_ID', 'QUANTITY', 'ALLOW_DELIVERY', 'PAY_CALLBACK', 'PAID'));
		$basketProviderList = static::redistributeToProviders($basketProviderMap);

		if (!empty($basketProviderList))
		{
			foreach ($basketProviderList as $provider => $providerBasketItemList)
			{
				if (array_key_exists("IBXSaleProductProvider", class_implements($provider)))
				{

					foreach ($providerBasketItemList as $providerBasketItem)
					{

						if ($providerBasketItem['BASKET_ITEM']->isBundleParent())
						{
							continue;
						}

						if ($providerBasketItem['BASKET_ITEM']->getField('MODULE') != '')
						{
							$data = array(
								"PRODUCT_ID" => $providerBasketItem["PRODUCT_ID"],
								"USER_ID"    => $providerBasketItem["USER_ID"],
								"PAID"		 => $providerBasketItem["PAID"],
								"ORDER_ID"   => $providerBasketItem["ORDER_ID"],
								"BASKET_ID"  => $providerBasketItem['BASKET_ID']
							);

							$r = static::deliverProductData($provider, $data);
							if ($r->isSuccess())
							{
								$resultData = $r->getData();

								if (array_key_exists($providerBasketItem["PRODUCT_ID"], $resultData))
								{
									$resultProductData = $resultData[$providerBasketItem["PRODUCT_ID"]];
								}
							}
							else
							{
								$result->addErrors($r->getErrors());
							}

							if (!empty($resultProductData) && is_array($resultProductData))
							{
								$resultProductData['ORDER_ID'] = $providerBasketItem['ORDER_ID'];
							}
						}
						else
						{
							$resultProductData = true;
						}

						$resultList[$providerBasketItem['BASKET_CODE']] = $resultProductData;

					}

				}
				elseif (class_exists($provider))
				{
					$context = array(
						'SITE_ID' => $order->getSiteId(),
						'CURRENCY' => $order->getCurrency(),
					);

					if ($order->getUserId() > 0)
					{
						$context['USER_ID'] = $order->getUserId();
					}
					else
					{
						global $USER;
						$context['USER_ID'] = $USER->getId();
					}

					$creator = Internals\ProviderCreator::create($context);

					/** @var ShipmentItem $shipmentItem */
					foreach ($shipmentItemCollection as $shipmentItem)
					{
						$basketItem = $shipmentItem->getBasketItem();
						$providerClass = $basketItem->getProviderEntity();

						if ($providerClass instanceof SaleProviderBase)
						{
							$creator->addShipmentItem($shipmentItem);
						}
					}

					$r = $creator->deliver();
					if ($r->isSuccess())
					{
						$r = $creator->createItemsResultAfterDeliver($r);
						if ($r->isSuccess())
						{
							$data = $r->getData();
							if (array_key_exists('RESULT_AFTER_DELIVER_LIST', $data))
							{
								$resultList = $data['RESULT_AFTER_DELIVER_LIST'] + $resultList;
							}
						}
					}
					else
					{
						$result->addErrors($r->getErrors());
					}
				}
				else
				{
					foreach ($providerBasketItemList as $providerBasketItem)
					{
						$resultProductData = \CSaleBasket::ExecuteCallbackFunction(
							$providerBasketItem['CALLBACK_FUNC'],
							$providerBasketItem['MODULE'],
							$providerBasketItem['PRODUCT_ID'],
							$providerBasketItem['USER_ID'],
							$providerBasketItem["ALLOW_DELIVERY"],
							$providerBasketItem['ORDER_ID'],
							$providerBasketItem["QUANTITY"]
						);

						$basketCode = $providerBasketItem['BASKET_ITEM']->getBasketCode();

						if (!empty($resultProductData) && is_array($resultProductData))
						{
							$resultProductData['ORDER_ID'] = $providerBasketItem['ORDER_ID'];
						}

						$resultList[$basketCode] = $resultProductData;
					}
				}
			}

			if (!empty($resultList) && is_array($resultList))
			{
				$recurringID = intval($order->getField("RECURRING_ID"));
				foreach ($resultList as $basketCode => $resultData)
				{
					if ($order->isPaid())
					{
						if (!empty($resultData) && is_array($resultData))
						{
							if (empty($resultData['ORDER_ID']) || intval($resultData['ORDER_ID']) < 0)
								$resultData["ORDER_ID"] = $order->getId();

							$resultData["REMAINING_ATTEMPTS"] = (defined("SALE_PROC_REC_ATTEMPTS") ? SALE_PROC_REC_ATTEMPTS : 3);
							$resultData["SUCCESS_PAYMENT"] = "Y";

							if ($recurringID > 0)
								\CSaleRecurring::Update($recurringID, $resultData);
							else
								\CSaleRecurring::Add($resultData);
						}
						elseif ($recurringID > 0)
						{
							\CSaleRecurring::Delete($recurringID);
						}
					}
					else
					{
						/** @var BasketItem $basketItem */
						if (!$basketItem = $basket->getItemByBasketCode($basketCode))
						{
							throw new ObjectNotFoundException('Entity "BasketItem" not found');
						}

						$resRecurring = \CSaleRecurring::GetList(
							array(),
							array(
								"USER_ID" => $order->getUserId(),
								"PRODUCT_ID" => $basketItem->getProductId(),
								"MODULE" => $basketItem->getField("MODULE")
							)
						);
						while ($recurringData = $resRecurring->Fetch())
						{
							\CSaleRecurring::Delete($recurringData["ID"]);
						}
					}
				}
			}
		}

		if (!empty($resultList))
		{
			$result->setData($resultList);
		}

		return $result;
	}

	/**
	 * @param $provider
	 * @param array $fields
	 *
	 * @return Result
	 */
	public static function deliverProductData($provider, array $fields)
	{
		global $APPLICATION;

		$result = new Result();
		$APPLICATION->ResetException();
		$resultProductData = false;

		if ($provider && array_key_exists("IBXSaleProductProvider", class_implements($provider)))
		{
			$resultProductData = $provider::DeliverProduct($fields);
		}
		else
		{
			$resultProductData = \CSaleBasket::ExecuteCallbackFunction(
				$fields['CALLBACK_FUNC'],
				$fields['MODULE'],
				$fields['PRODUCT_ID'],
				$fields['USER_ID'],
				$fields["ALLOW_DELIVERY"],
				$fields['ORDER_ID'],
				$fields["QUANTITY"]
			);

			if (!empty($resultProductData) && is_array($resultProductData))
			{
				$resultProductData['ORDER_ID'] = $fields['ORDER_ID'];
			}

		}

		$ex = $APPLICATION->GetException();
		if (!empty($ex))
		{
			$result->addError( new ResultError($ex->GetString(), $ex->GetID()) );
		}
		else
		{
			$resultList[$fields['PRODUCT_ID']] = $resultProductData;
		}

		if (!empty($resultList) && is_array($resultList))
		{
			$result->setData($resultList);
		}

		return $result;
	}


	/**
	 * @param array $basketList
	 * @param array $select
	 * @return array
	 * @throws ObjectNotFoundException
	 */
	protected static function createProviderBasketMap(array $basketList, array $select = array())
	{
		$basketProviderMap = array();

		/**
		 * @var string $basketKey
		 * @var BasketItem $basketItem
		 */
		foreach($basketList as $basketIndex => $basketItemDat)
		{
			if (is_array($basketItemDat) && isset($basketItemDat['BASKET_ITEM']))
			{
				$basketItem = $basketItemDat['BASKET_ITEM'];
			}
			else
			{
				$basketItem = $basketItemDat;
			}

			$basketProviderData = static::createProviderBasketItemMap($basketItem, $select);
			if (!$basketProviderData)
			{
				continue;
			}

			$basketProviderMap[$basketIndex] = $basketProviderData;

		}

		return $basketProviderMap;
	}


	protected static function createProviderBasketItemMap(BasketItem $basketItem, array $select = array())
	{

		$basketProviderData = array(
			'BASKET_ITEM' => $basketItem,
			'BASKET_ID' => $basketItem->getId(),
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $basketItem->getProductId(),
			'MODULE' => $basketItem->getField('MODULE'),
		);

		$provider = $basketItem->getProvider();
		$providerClass = $basketItem->getProviderEntity();
		if ($provider)
		{
			if (array_key_exists("IBXSaleProductProvider", class_implements($provider))
				|| $providerClass instanceof SaleProviderBase)
			{
				$basketProviderData['PROVIDER'] = $provider;
			}
		}
		elseif (strval($basketItem->getField('CALLBACK_FUNC')) != '')
		{
			$basketProviderData['CALLBACK_FUNC'] = $basketItem->getField('CALLBACK_FUNC');
		}
		elseif (strval($basketItem->getField('PAY_CALLBACK_FUNC')) != '' && in_array('PAY_CALLBACK', $select))
		{
			$basketProviderData['CALLBACK_FUNC'] = $basketItem->getField('PAY_CALLBACK_FUNC');
		}

		if (in_array('QUANTITY', $select))
		{
			$basketProviderData['QUANTITY'] = $basketItem->getQuantity(); // ????
		}

		if (in_array('RENEWAL', $select))
		{
			$basketProviderData['RENEWAL'] = $basketItem->getField('RENEWAL')!== null && $basketItem->getField('RENEWAL') != 'N'? 'Y' : 'N';
		}

		if (in_array('RESERVED', $select))
		{
			$basketProviderData['RESERVED'] = $basketItem->getField('RESERVED');
		}

		if (in_array('SITE_ID', $select))
		{
			$basketProviderData['SITE_ID'] = $basketItem->getField('LID');
		}

		if (in_array('ORDER_ID', $select))
		{
			/** @var Basket $basket */
			if (!$basket = $basketItem->getCollection())
			{
				throw new ObjectNotFoundException('Entity "Basket" not found');
			}

			if ($basket->getOrder() && $basket->getOrderId() > 0)
			{
				$basketProviderData['ORDER_ID'] = $basket->getOrderId();
			}

		}

		if (in_array('USER_ID', $select))
		{
			/** @var Basket $basket */
			if (!$basket = $basketItem->getCollection())
			{
				throw new ObjectNotFoundException('Entity "Basket" not found');
			}

			if ($order = $basket->getOrder())
			{
				$userId = $order->getUserId();

				if ($userId === null)
				{
					$userId = \CSaleUser::GetUserID($basket->getFUserId());
				}

				if ($userId > 0)
				{
					$basketProviderData['USER_ID'] = $userId;
				}
			}

		}

		if (in_array('PAID', $select))
		{
			/** @var Basket $basket */
			if (!$basket = $basketItem->getCollection())
			{
				throw new ObjectNotFoundException('Entity "Basket" not found');
			}

			if ($basket->getOrder() && $basket->getOrderId() > 0)
			{
				$order = $basket->getOrder();
				$basketProviderData['PAID'] = $order->isPaid();
			}

		}

		if (in_array('ALLOW_DELIVERY', $select))
		{
			/** @var Basket $basket */
			if (!$basket = $basketItem->getCollection())
			{
				throw new ObjectNotFoundException('Entity "Basket" not found');
			}

			if ($basket->getOrder() && $basket->getOrderId() > 0)
			{
				/** @var Order $order */
				$order = $basket->getOrder();

				/** @var ShipmentCollection $shipmentCollection */
				if ($shipmentCollection = $order->getShipmentCollection())
				{
					$basketProviderData['ALLOW_DELIVERY'] = $shipmentCollection->isAllowDelivery();
				}
			}

		}

		return $basketProviderData;
	}
	/**
	 * @param Shipment $shipment
	 * @return array
	 */
	private static function getProviderBasketFromShipment(Shipment $shipment)
	{
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		$basketList = static::getBasketFromShipmentItemCollection($shipmentItemCollection);

		$basketProviderMap = static::createProviderBasketMap($basketList, array('QUANTITY', 'PRODUCT_ID'));

		$basketProviderList = static::redistributeToProviders($basketProviderMap);

		return $basketProviderList;
	}

	/**
	 * @param array $basketProviderMap
	 * @return array
	 */
	protected static function redistributeToProviders(array $basketProviderMap)
	{

		$basketProviderList = array();
		foreach($basketProviderMap as $basketProviderItem)
		{
			$providerName = $basketProviderItem['PROVIDER'];
			$productId = $basketProviderItem['BASKET_ITEM']->getProductId();
			$quantity = floatval($basketProviderItem['QUANTITY']);
			unset($basketProviderItem['QUANTITY']);

			$basketCode = $basketProviderItem['BASKET_CODE'];

			if (!isset($basketProviderList[$providerName][$productId]))
			{
				$basketProviderList[$providerName][$productId] = $basketProviderItem;
			}

			if (isset($basketProviderList[$providerName][$productId]['QUANTITY_LIST'][$basketCode]))
			{
				$basketProviderList[$providerName][$productId]['QUANTITY_LIST'][$basketCode] += $quantity;
			}
			else
			{
				$basketProviderList[$providerName][$productId]['QUANTITY_LIST'][$basketCode] = $quantity;
			}



		}

		return $basketProviderList;
	}

	/**
	 * @internal
	 * @param bool $value
	 */
	public static function setUsingTrustData($value)
	{
		static::$useReadTrustData = (bool)$value;
	}

	/**
	 * @internal
	 * @return bool
	 */
	public static function isReadTrustData()
	{
		return (bool)static::$useReadTrustData;
	}


	/**
	 * @internal
	 * @param $siteId
	 * @param $module
	 * @param $productId
	 *
	 * @return bool
	 */
	public static function isExistsTrustData($siteId, $module, $productId)
	{
		return (!empty(static::$trustData[$siteId][$module][$productId]) && is_array(static::$trustData[$siteId][$module][$productId]));
	}


	/**
	 * @internal
	 * @param string $siteId
	 * @param string $module
	 * @param int $productId
	 * @param array $fields
	 */
	public static function setTrustData($siteId, $module, $productId, array $fields)
	{
		static::$trustData[$siteId][$module][$productId] = $fields;
	}


	/**
	 * @internal
	 * @param $siteId
	 * @param $module
	 * @param $productId
	 *
	 * @return null
	 */
	public static function getTrustData($siteId, $module, $productId)
	{
		if (static::isExistsTrustData($siteId, $module, $productId))
			return static::$trustData[$siteId][$module][$productId];

		return null;
	}

	/**
	 * @internal
	 * @param null|string $siteId
	 * @param null|string $module
	 * @param null|int $productId
	 */
	public static function resetTrustData($siteId = null, $module = null, $productId = null)
	{
		if (strval($siteId) != '')
		{
			if (!empty(static::$trustData[$siteId]))
			{
				if (intval($productId) > 0 )
				{
					if (strval($module) == '')
					{
						foreach (static::$trustData[$siteId] as $moduleName => $data)
						{
							if (isset(static::$trustData[$siteId][$moduleName][$productId]))
								unset(static::$trustData[$siteId][$moduleName][$productId]);
						}
					}
					else
					{
						if (isset(static::$trustData[$siteId][$module][$productId]))
							unset(static::$trustData[$siteId][$module][$productId]);
					}
				}
				elseif (strval($module) != '')
				{
					if (isset(static::$trustData[$siteId][$module]))
						unset(static::$trustData[$siteId][$module]);
				}
				else
				{
					if (isset(static::$trustData[$siteId]))
						unset(static::$trustData[$siteId]);
				}
			}
		}
		else
		{
			static::$trustData = array();
		}

	}

	/**
	 * @param Order $order
	 *
	 * @throws ArgumentNullException
	 * @throws NotImplementedException
	 * @throws NotSupportedException
	 * @throws ObjectNotFoundException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	protected static function refreshMarkers(Order $order)
	{
		if ($order->getId() == 0)
		{
			return;
		}

		if (!$shipmentCollection = $order->getShipmentCollection())
		{
			throw new ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		if (!$paymentCollection = $order->getPaymentCollection())
		{
			throw new ObjectNotFoundException('Entity "PaymentCollection" not found');
		}

		if (!$basket = $order->getBasket())
		{
			throw new ObjectNotFoundException('Entity "Basket" not found');
		}

		$markList = array();

		$markerEntityList = array();

		$filter = array(
			'filter' => array(
				'=ORDER_ID' => $order->getId(),
				'!=SUCCESS' => EntityMarker::ENTITY_SUCCESS_CODE_DONE
			),
			'select' => array('ID', 'ENTITY_TYPE', 'ENTITY_ID', 'CODE', 'SUCCESS'),
			'order' => array('ID' => 'DESC')
		);
		$res = EntityMarker::getList($filter);
		while($markerData = $res->fetch())
		{
			if (!empty($markList[$markerData['ENTITY_TYPE']])
				&& !empty($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']])
				&& $markerData['CODE'] == $markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']]
			)
			{
				continue;
			}

			if ($markerData['SUCCESS'] != EntityMarker::ENTITY_SUCCESS_CODE_DONE)
			{
				$markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']][] = $markerData['CODE'];
			}

			if ($poolItemSuccess = EntityMarker::getPoolItemSuccess($order, $markerData['ID'], $markerData['ENTITY_TYPE'], $markerData['ENTITY_ID'], $markerData['CODE']))
			{
				if ($poolItemSuccess == EntityMarker::ENTITY_SUCCESS_CODE_DONE)
				{
					foreach ($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']] as $markerIndex => $markerCode)
					{
						if ($markerData['CODE'] == $markerCode)
						{
							unset($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']][$markerIndex]);
						}
					}

					if (empty($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']]))
					{
						unset($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']]);
					}
				}
			}

			if (empty($markList[$markerData['ENTITY_TYPE']]))
			{
				unset($markList[$markerData['ENTITY_TYPE']]);
			}
		}

		if (!empty($markList))
		{
			foreach ($markList as $markEntityType => $markEntityList)
			{
				foreach ($markEntityList as $markEntityId => $markEntityCodeList)
				{
					if (empty($markEntityCodeList))
					{
						if (($entity = EntityMarker::getEntity($order, $markEntityType, $markEntityId)) && ($entity instanceof \IEntityMarker))
						{
							if ($entity->canMarked())
							{
								$markedField = $entity->getMarkField();
								$entity->setField($markedField, 'N');
							}
						}
					}
				}
			}
		}

		if (empty($markList) && !EntityMarker::hasErrors($order))
		{
			if ($shipmentCollection->isMarked())
			{
				/** @var Shipment $shipment */
				foreach ($shipmentCollection as $shipment)
				{
					if ($shipment->isMarked())
					{
						$shipment->setField('MARKED', 'N');
					}
				}
			}
			if ($paymentCollection->isMarked())
			{
				/** @var Payment $payment */
				foreach ($paymentCollection as $payment)
				{
					if ($payment->isMarked())
					{
						$payment->setField('MARKED', 'N');
					}
				}
			}

			$order->setField('MARKED', 'N');
		}
	}



	/**
	 * @return array
	 */
	protected static function getPrimaryFields()
	{
		return array_merge(
			array(
				'NAME',
				'CATALOG_XML_ID',
				'PRODUCT_XML_ID',
				'WEIGHT',
				'DETAIL_PAGE_URL',
				'BARCODE_MULTI',
				'DIMENSIONS',
				'TYPE',
				'SET_PARENT_ID',
				'MEASURE_CODE',
				'MEASURE_NAME',
			),
			static::getUpdatableFields()
		);
	}

	/**
	 * @internal
	 * @return array
	 */
	public static function getUpdatableFields()
	{
		return array(
			'CAN_BUY',

			'VAT_RATE',
			'VAT_INCLUDED',

			'PRODUCT_PRICE_ID',
			'PRICE',
			'CURRENCY',
			'BASE_PRICE',
			'DISCOUNT_PRICE',

			'QUANTITY',
			'QUANTITY_RESERVED',
		);
	}

	/**
	 * @internal
	 * @return array
	 */
	protected static function getProductDataRequiredFields()
	{
		return array(
			'NAME',
			'CAN_BUY',
			'BARCODE_MULTI',
			'WEIGHT',
			'TYPE',
			'QUANTITY',
		);
	}

	/**
	 * @internal
	 * @return array
	 */
	protected static function getProductDataRequiredPriceFields()
	{
		return array(
			'PRODUCT_PRICE_ID',
			'NOTES',
			'VAT_RATE',
			'BASE_PRICE',
			'PRICE',
			'CURRENCY',
			'DISCOUNT_PRICE',
		);
	}

	/**
	 * @internal
	 * @param $providerClass
	 * @param array $products
	 * @param array $context
	 *
	 * @return Result
	 */
	public static function getAvailableQuantity($providerClass, array $products, array $context)
	{
		$result = new Result();
		$resultList = array();

		foreach ($products as $productId => $productData)
		{
			$r = static::getAvailableQuantityByProductData($providerClass, $productData, $context);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}

			$providerName = null;
			if (!empty($providerClass))
			{
				$reflect = new \ReflectionClass($providerClass);
				$providerName = $reflect->getName();
			}
			else
			{
				/** @var BasketItem $basketItem */
				$basketItem = $productData['BASKET_ITEM'];
				$providerName = $basketItem->getCallbackFunction();
			}

			$availableQuantityData = $r->getData();
			if (array_key_exists('AVAILABLE_QUANTITY', $availableQuantityData))
			{
				if (!isset($resultList))
				{
					$resultList = array();
				}

				$resultList[$productId] += floatval($availableQuantityData['AVAILABLE_QUANTITY']);
			}
			else
			{
				$result->addWarning(new ResultWarning(Loc::getMessage('SALE_PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY', array(
					'#PRODUCT_ID#' => $productId
				)), 'PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY'));

			}
		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					'AVAILABLE_QUANTITY_LIST' => $resultList,
				)
			);
		}

		return $result;
	}

	/**
	 * @internal
	 * @param $providerClass
	 * @param array $products
	 * @param array $context
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	public static function getAvailableQuantityAndPrice($providerClass, array $products, array $context)
	{
		$result = new Result();
		$availableQuantityList = array();
		$priceData = array();
		$providerName = null;

		foreach ($products as $productId => $productData)
		{
			/** @var BasketItem $basketItem */
			$basketItem = $productData['BASKET_ITEM'];
			if (!$basketItem)
			{
				throw new ObjectNotFoundException('Entity "BasketItem" not found');
			}

			$callbackFunction = null;
			if (!empty($productData['CALLBACK_FUNC']))
			{
				$callbackFunction = $productData['CALLBACK_FUNC'];
			}

			$isCustomItem = !($providerClass || $callbackFunction);

			if ($isCustomItem)
			{
				$providerData = $basketItem->getFieldValues();
				$providerData['AVAILABLE_QUANTITY'] = $basketItem->getQuantity();
			}
			else
			{
				$r = static::getProviderDataByProductData($providerClass, $productData, $context);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
				elseif ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
				}
				$providerData = $r->getData();
			}

			if (!empty($providerData))
			{
				if (isset($providerData['AVAILABLE_QUANTITY']))
				{
					$availableQuantityList[$productId] += floatval($providerData['AVAILABLE_QUANTITY']);
				}
				else
				{
					$result->addWarning(new ResultWarning(Loc::getMessage('SALE_PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY', array(
						'#PRODUCT_ID#' => $productId
					)), 'PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY'));

				}

				if (!$isCustomItem)
				{
					$priceFields = static::getPriceFields();

					foreach ($priceFields as $fieldName)
					{
						if (array_key_exists($fieldName, $providerData))
						{
							$priceData[$productId][$basketItem->getBasketCode()][$fieldName] = $providerData[$fieldName];
						}

					}
				}
			}
		}

		$result->setData(
			array(
				'PRODUCT_DATA_LIST' => array(
					'PRICE_LIST' => $priceData,
					'AVAILABLE_QUANTITY_LIST' => $availableQuantityList
				)
			)
		);

		return $result;
	}


	/**
	 * @param ShipmentItem[] $shipmentItemList
	 *
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	public static function isNeedShip($shipmentItemList)
	{
		$result = new Result();

		$resultList = array();

		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();
			$providerName = $basketItem->getProviderName();

			if ($providerName && array_key_exists("IBXSaleProductProvider", class_implements($providerName)))
			{

				$isNeedShip = false;

				if (method_exists($providerName, 'isNeedShip'))
				{
					$isNeedShip = $providerName::isNeedShip();
				}

				$resultList[$providerName] = $isNeedShip;

			}
		}

		if (!empty($resultList))
		{
			$result->setData($resultList);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getPriceFields()
	{
		return array(
			'PRODUCT_PRICE_ID',
			'NOTES',
			'VAT_RATE',
			'DISCOUNT_NAME',
			'DISCOUNT_COUPON',
			'DISCOUNT_VALUE',
			'RESULT_PRICE',
			'PRICE_TYPE_ID',
			'BASE_PRICE',
			'PRICE',
			'CURRENCY',
			'DISCOUNT_PRICE',
			'CUSTOM_PRICE',
		);
	}


}
