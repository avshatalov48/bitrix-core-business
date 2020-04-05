<?php


namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ShipmentItemStoreCollection
	extends Internals\EntityCollection
{
	/** @var  ShipmentItem */
	private $shipmentItem;

	private static $errors = array();

	private static $eventClassName = null;

	/**
	 * @return ShipmentItem
	 */
	protected function getEntityParent()
	{
		return $this->getShipmentItem();
	}

	/**
	 * @param $itemData
	 * @return ShipmentItem
	 */
	protected static function createShipmentItemStoreCollectionObject(array $itemData = array())
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$shipmentItemStoreCollectionClassName = $registry->getShipmentItemStoreCollectionClassName();

		return new $shipmentItemStoreCollectionClassName();
	}

	/**
	 * @param ShipmentItem $shipmentItem
	 * @return ShipmentItemCollection
	 */
	public static function load(ShipmentItem $shipmentItem)
	{
		/** @var ShipmentItemStoreCollection $shipmentItemStoreCollection */
		$shipmentItemStoreCollection = static::createShipmentItemStoreCollectionObject();
		$shipmentItemStoreCollection->shipmentItem = $shipmentItem;

		if ($shipmentItem->getId() > 0)
		{
			$basketItem = $shipmentItem->getBasketItem();
			$shipmentItemStoreList = ShipmentItemStore::loadForShipmentItem($shipmentItem->getId());
			/** @var ShipmentItemStore $shipmentItemStoreDat */
			foreach ($shipmentItemStoreList as $shipmentItemStoreDat)
			{
				$shipmentItemStore = ShipmentItemStore::create($shipmentItemStoreCollection, $basketItem);

				$fields = $shipmentItemStoreDat->getFieldValues();

				$shipmentItemStore->initFields($fields);
				$shipmentItemStoreCollection->addItem($shipmentItemStore);

			}
		}

		return $shipmentItemStoreCollection;
	}

	/**
	 * @param BasketItem $basketItem
	 * @return static
	 * @throws \Exception
	 */
	public function createItem(BasketItem $basketItem)
	{
		/** @var ShipmentItemStore $item */
		$shipmentItemStore = ShipmentItemStore::create($this, $basketItem);

		$this->addItem($shipmentItemStore);

		return $shipmentItemStore;
	}

	/**
	 * @param Internals\CollectableEntity $shipmentItemStore
	 * @return bool|void
	 */
	public function addItem(Internals\CollectableEntity $shipmentItemStore)
	{
		parent::addItem($shipmentItemStore);
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return bool
	 */
	public function deleteItem($index)
	{
		$oldItem = parent::deleteItem($index);

	}

	/**
	 * @param $basketCode
	 * @return float|int
	 */
	public function getQuantityByBasketCode($basketCode)
	{
		$quantity = 0;

		/** @var ShipmentItemStore $item */
		foreach ($this->collection as $item)
		{
			if ($item->getBasketCode() == $basketCode)
			{
				$quantity += $item->getQuantity();
			}
		}

		return $quantity;
	}


	/**
	 * @return ShipmentItem
	 */
	public function getShipmentItem()
	{
		return $this->shipmentItem;
	}


	public function onShipmentItemModify($action, ShipmentItem $shipmentItem, $name = null, $oldValue = null, $value = null)
	{
		if ($action !== EventActions::UPDATE)
			return new Result();

		if ($name == "QUANTITY")
		{
			return $this->syncQuantityAfterModify($shipmentItem, $oldValue, $value);
		}

		return new Result();
	}

	/**
	 * @param ShipmentItem $shipmentItem
	 * @param null $oldValue
	 * @param null $value
	 *
	 * @return Result
	 */
	protected function syncQuantityAfterModify(ShipmentItem $shipmentItem, $oldValue = null, $value = null)
	{
		if (!($basketItem = $shipmentItem->getBasketItem()) || $basketItem->getId() == 0)
			return new Result();

		$result = new Result();

		$deltaQuantity = $value - $oldValue;

		if ($deltaQuantity >= 0)
			return $result;

		$barcodeList = array();
		/** @var ShipmentItemStore $shipmentItemStore */
		foreach($this->collection as $shipmentItemStore)
		{
			if ($shipmentItemStore->getBasketCode() == $basketItem->getBasketCode())
			{
				if (strval($shipmentItemStore->getBarcode()) == "")
				{
					$barcodeList[$shipmentItemStore->getId()] = $shipmentItemStore;
				}
			}
		}

		if ($basketItem->isBarcodeMulti())
		{
			if (count($barcodeList) < $oldValue)
				return $result;

			$oldItemsList = array();

			/** @var ShipmentItemStore $shipmentItemStore */
			foreach ($this->collection as $shipmentItemStore)
			{
				if ($shipmentItemStore->getBasketCode() == $basketItem->getBasketCode())
				{
					$oldItemsList[$shipmentItemStore->getId()] = $shipmentItemStore;
				}
			}

			$cutBarcodeList = array_slice($barcodeList, 0, $deltaQuantity, true);
			if (!empty($oldItemsList) && is_array($oldItemsList))
			{
				/**
				 * @var int $oldItemId
				 * @var ShipmentItemStore $oldItem
				 */
				foreach($oldItemsList as $oldItemId => $oldItem)
				{
					if (!isset($cutBarcodeList[$oldItemId]))
					{
						$oldItem->delete();
					}
				}
			}
		}
		elseif (count($barcodeList) == 1)
		{
			/** @var ShipmentItemStore $barcodeItem */
			$barcodeItem = reset($barcodeList);

			if ($barcodeItem->getQuantity() < $oldValue)
				return new Result();

			/** @var Result $r */
			$r = $barcodeItem->setField(
					"QUANTITY",
					$barcodeItem->getField("QUANTITY") + $deltaQuantity
			);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		return $result;
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return bool
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
//		$shipmentItem = $this->getShipmentItem();

		if ($name == "QUANTITY")
		{
			return $this->checkAvailableQuantity($item);
		}

		return new Result();
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @return Result
	 * @throws \Exception
	 */
	public function checkAvailableQuantity(Internals\CollectableEntity $item)
	{
		$result = new Result();
		$shipmentItem = $this->getShipmentItem();

		$basketItem = $shipmentItem->getBasketItem();

		$itemStoreQuantity = floatval($this->getQuantityByBasketCode($shipmentItem->getBasketCode()));

		if (($shipmentItem->getQuantity() !== null)
			&& (( floatval($item->getQuantity()) > floatval($shipmentItem->getQuantity()))
			|| ( $itemStoreQuantity > floatval($shipmentItem->getQuantity()))))
		{

			if (isset(static::$errors[$basketItem->getBasketCode()][$item->getField('ORDER_DELIVERY_BASKET_ID')]['STORE_QUANTITY_LARGER_ALLOWED']))
			{
				static::$errors[$basketItem->getBasketCode()][$item->getField('ORDER_DELIVERY_BASKET_ID')]['STORE_QUANTITY_LARGER_ALLOWED'] += $item->getQuantity();
			}
			else
			{
				$result->addError(new ResultError(
										Loc::getMessage('SALE_SHIPMENT_ITEM_STORE_QUANTITY_LARGER_ALLOWED', array(
										  '#PRODUCT_NAME#' => $basketItem->getField('NAME'),
										)),
										'SALE_SHIPMENT_ITEM_STORE_QUANTITY_LARGER_ALLOWED')
				);

				static::$errors[$basketItem->getBasketCode()][$item->getField('ORDER_DELIVERY_BASKET_ID')]['STORE_QUANTITY_LARGER_ALLOWED'] = $item->getQuantity();
			}

		}

		return $result;
	}


	/**
	 * @return Main\Entity\Result
	 */
	public function save()
	{
		$result = new Main\Entity\Result();

		$oldBarcodeList = array();

		$itemsFromDb = array();

		$shipmentItem = $this->getShipmentItem();

		$originalValues = $shipmentItem->getFields()
									   ->getOriginalValues();

		$shipmentItemIsNew = (array_key_exists('ID', $originalValues) && $originalValues['ID'] === null);

		if ($this->getShipmentItem() && $this->getShipmentItem()->getId() > 0 && !$shipmentItemIsNew)
		{
			$itemsFromDbList = Internals\ShipmentItemStoreTable::getList(
				array(
					"filter" => array("ORDER_DELIVERY_BASKET_ID" => $this->getShipmentItem()->getId()),
					"select" => ShipmentItemStore::getAllFields()
				)
			);
			while ($itemsFromDbItem = $itemsFromDbList->fetch())
				$itemsFromDb[$itemsFromDbItem["ID"]] = $itemsFromDbItem;
		}

		/** @var ShipmentItemStore $shipmentItemStore */
		foreach ($this->collection as $shipmentItemStore)
		{
			$r = $shipmentItemStore->save();
			if (!$r->isSuccess())
				$result->addErrors($r->getErrors());

			if (isset($itemsFromDb[$shipmentItemStore->getId()]))
				unset($itemsFromDb[$shipmentItemStore->getId()]);
		}

		if (self::$eventClassName === null)
		{
			self::$eventClassName = ShipmentItemStore::getEntityEventName();
		}

		foreach ($itemsFromDb as $k => $v)
		{
			/** @var Main\Event $event */
			$event = new Main\Event('sale', "OnBefore".self::$eventClassName."Deleted", array(
					'VALUES' => $v,
			));
			$event->send();

			Internals\ShipmentItemStoreTable::delete($k);

			/** @var Main\Event $event */
			$event = new Main\Event('sale', "On".self::$eventClassName."Deleted", array(
					'VALUES' => $v,
			));
			$event->send();
		}

		return $result;
	}


	/**
	 * @param array $values
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public function setBarcodeQuantityFromArray(array $values)
	{
		$result = new Result();
		$requestBarcodeList = static::getBarcodeListFromArray($values);

		$plusList = array();
		$oldQuantityList = $this->getAllBarcodeList();

		foreach ($requestBarcodeList as $storeId => $barcodeDat)
		{
			foreach ($barcodeDat as $barcodeValue => $barcode)
			{
				if (isset($oldQuantityList[$storeId][$barcodeValue])
					&& $oldQuantityList[$storeId][$barcodeValue]['ID'] == $barcode['ID'])
				{
					$oldBarcode = $oldQuantityList[$storeId][$barcodeValue];
					if ($barcode['QUANTITY'] == $oldBarcode['QUANTITY'])
					{
						continue;
					}
					elseif ($barcode['QUANTITY'] < $oldBarcode['QUANTITY'])
					{
						/** @var ShipmentItemStore $item */
						$item = $this->getItemById($oldBarcode['ID']);
						if ($item)
							$item->setField('QUANTITY', $barcode['QUANTITY']);
					}
					else
					{
						$plusList[$barcodeValue] = array(
							'ID' => $barcode['ID'],
							'QUANTITY' => $barcode['QUANTITY']
						);
					}
				}
			}
		}

		foreach ($plusList as $barcode)
		{
			if ($barcode['ID'] <= 0)
				continue;

			$item = $this->getItemById($barcode['ID']);
			if ($item)
			{
				/** @var Result $r */
				$r = $item->setField('QUANTITY', $barcode['QUANTITY']);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}


	/**
	 * @param array $values
	 * @return array
	 */
	private function getBarcodeListFromArray(array $values)
	{

		$result = array();

		foreach ($values['BARCODE_INFO'] as $barcodeDat)
		{
			$storeId = $barcodeDat['STORE_ID'];

			if (!isset($barcodeDat['BARCODE']) || !is_array($barcodeDat['BARCODE']))
				continue;

			if (count($barcodeDat['BARCODE']) > 1)
			{
				$quantity = floatval($barcodeDat['QUANTITY'] / count($barcodeDat['BARCODE']));
			}
			else
			{
				$quantity = floatval($barcodeDat['QUANTITY']);
			}

			foreach ($barcodeDat['BARCODE'] as $barcode)
			{
				if (!isset($result[$storeId]))
					$result[$storeId] = array();

				$result[$storeId][$barcode['VALUE']] = array(
					"QUANTITY" => $quantity,
				);

				if (isset($barcode['ID']) && intval($barcode['ID']) > 0)
				{
					$result[$storeId][$barcode['VALUE']]['ID'] = intval($barcode['ID']);
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getAllBarcodeList()
	{
		$result = array();
		/** @var ShipmentItemStore $item */
		foreach ($this->collection as $item)
		{
			if (!isset($result[$item->getField('STORE_ID')]))
			{
				$result[$item->getField('STORE_ID')] = array();
			}

			$result[$item->getField('STORE_ID')][$item->getField('BARCODE')] = array(
				'ID' => $item->getField('ID'),
				'QUANTITY' => $item->getField('QUANTITY'),
			);
		}

		return $result;
	}

	/**
	 * @param string $barcode
	 * @param $basketCode
	 * @param $storeId
	 *
	 * @return ShipmentItemStore|null
	 */
	public function getItemByBarcode($barcode, $basketCode, $storeId = null)
	{
		/** @var ShipmentItemStore $shipmentItemStore */
		foreach ($this->collection as $shipmentItemStore)
		{

			//$storeId == $shipmentItemStore->getStoreId()
			if ($shipmentItemStore->getBarcode() == $barcode)
			{
				/** @var BasketItem $basketItem */
				$basketItem = $shipmentItemStore->getBasketItem();

				if ($basketItem->getBasketCode() != $basketCode)
						continue;

				return $shipmentItemStore;
			}
		}

		return null;
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return ShipmentItemStoreCollection
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		$shipmentItemStoreCollectionClone = clone $this;
		$shipmentItemStoreCollectionClone->isClone = true;

		/** @var ShipmentItem $shipmentItem */
		if ($shipmentItem = $this->shipmentItem)
		{
			if (!$cloneEntity->contains($shipmentItem))
			{
				$cloneEntity[$shipmentItem] = $shipmentItem->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($shipmentItem))
			{
				$shipmentItemStoreCollectionClone->shipmentItem = $cloneEntity[$shipmentItem];
			}
			
		}

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $shipmentItemStoreCollectionClone;
		}


		/**
		 * @var int key
		 * @var ShipmentItemStore $shipmentItemStore
		 */
		foreach ($shipmentItemStoreCollectionClone->collection as $key => $shipmentItemStore)
		{
			if (!$cloneEntity->contains($shipmentItemStore))
			{
				$cloneEntity[$shipmentItemStore] = $shipmentItemStore->createClone($cloneEntity);
			}

			$shipmentItemStoreCollectionClone->collection[$key] = $cloneEntity[$shipmentItemStore];
		}

		return $shipmentItemStoreCollectionClone;
	}


	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function getErrorEntity($value)
	{
		$className = null;
		/** @var ShipmentItemStore $shipmentItemStore */
		foreach ($this->collection as $shipmentItemStore)
		{
			if ($className = $shipmentItemStore->getErrorEntity($value))
			{
				break;
			}
		}
		return $className;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function canAutoFixError($value)
	{
		$autoFix = false;
		/** @var ShipmentItemStore $shipmentItemStore */
		foreach ($this->collection as $shipmentItemStore)
		{
			if ($autoFix = $shipmentItemStore->canAutoFixError($value))
			{
				break;
			}
		}
		return $autoFix;
	}

} 