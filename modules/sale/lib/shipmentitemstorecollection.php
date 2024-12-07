<?php


namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ShipmentItemStoreCollection extends Internals\EntityCollection
{
	/** @var  ShipmentItem */
	private $shipmentItem;

	/**
	 * @return ShipmentItem
	 */
	protected function getEntityParent()
	{
		return $this->getShipmentItem();
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	private static function createShipmentItemStoreCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$shipmentItemStoreCollectionClassName = $registry->getShipmentItemStoreCollectionClassName();

		return new $shipmentItemStoreCollectionClassName();
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param ShipmentItem $shipmentItem
	 * @return ShipmentItemStoreCollection
	 */
	public static function load(ShipmentItem $shipmentItem)
	{
		/** @var ShipmentItemStoreCollection $shipmentItemStoreCollection */
		$shipmentItemStoreCollection = static::createShipmentItemStoreCollectionObject();
		$shipmentItemStoreCollection->shipmentItem = $shipmentItem;

		if ($shipmentItem->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var ShipmentItemStore $itemClassName */
			$itemClassName = $registry->getShipmentItemStoreClassName();

			$shipmentItemStoreList = $itemClassName::loadForShipmentItem($shipmentItem->getId());

			/** @var ShipmentItemStore $shipmentItemStore */
			foreach ($shipmentItemStoreList as $shipmentItemStore)
			{
				$shipmentItemStore->setCollection($shipmentItemStoreCollection);
				$shipmentItemStoreCollection->bindItem($shipmentItemStore);
			}
		}

		return $shipmentItemStoreCollection;
	}

	/**
	 * @param BasketItem $basketItem
	 * @return ShipmentItemStore
	 * @throws Main\ArgumentNullException
	 */
	public function createItem(BasketItem $basketItem)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var ShipmentItemStore $itemClassName */
		$itemClassName =  $registry->getShipmentItemStoreClassName();

		$item = $itemClassName::create($this, $basketItem);

		$this->addItem($item);

		return $item;
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
			$quantity += $item->getQuantity();
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

	/**
	 * @param $action
	 * @param ShipmentItem $shipmentItem
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function onShipmentItemModify($action, ShipmentItem $shipmentItem, $name = null, $oldValue = null, $value = null)
	{
		if ($action !== EventActions::UPDATE)
		{
			return new Result();
		}

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
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
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
			if (strval($shipmentItemStore->getBarcode()) == "")
			{
				$barcodeList[$shipmentItemStore->getId()] = $shipmentItemStore;
			}
		}

		if ($basketItem->isBarcodeMulti())
		{
			if (count($barcodeList) < $oldValue)
			{
				return $result;
			}

			$oldItemsList = array();

			/** @var ShipmentItemStore $shipmentItemStore */
			foreach ($this->collection as $shipmentItemStore)
			{
				$oldItemsList[$shipmentItemStore->getId()] = $shipmentItemStore;
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
	 * @return Result
	 * @throws \Exception
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		if ($name == "QUANTITY")
		{
			$r = $this->checkAvailableQuantity($item);
			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}
		}

		return new Result();
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\SystemException
	 */
	public function checkAvailableQuantity(Internals\CollectableEntity $item)
	{
		$result = new Result();

		if (!$item instanceof ShipmentItemStore)
		{
			return $result;
		}

		$shipmentItem = $this->getShipmentItem();

		$itemStoreQuantity = (float)$this->getQuantityByBasketCode($shipmentItem->getBasketCode());

		if (
			(float)$item->getQuantity() > $shipmentItem->getQuantity()
			||
			$itemStoreQuantity > $shipmentItem->getQuantity()
		)
		{
			$result->addError(new Main\Error(
					Loc::getMessage(
						'SALE_SHIPMENT_ITEM_STORE_QUANTITY_LARGER_ALLOWED',
						['#PRODUCT_NAME#' => $this->getShipmentItem()->getBasketItem()->getField('NAME')]
					),
					'SALE_SHIPMENT_ITEM_STORE_QUANTITY_LARGER_ALLOWED'
				)
			);
		}

		return $result;
	}

	/**
	 * @return Main\Entity\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 */
	public function save()
	{
		$result = new Main\Entity\Result();

		$originalItemValues = $this->getOriginalItemValues();

		/** @var ShipmentItemStore $item */
		foreach ($this->collection as $item)
		{
			$r = $item->save();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if (isset($originalItemValues[$item->getId()]))
			{
				unset($originalItemValues[$item->getId()]);
			}
		}

		if ($originalItemValues)
		{
			foreach ($originalItemValues as $id => $itemValues)
			{
				$this->callEventOnBeforeSaleShipmentItemStoreDeleted($itemValues);

				$this->deleteInternal($id);

				$this->callEventOnSaleShipmentItemStoreDeleted($itemValues);
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getOriginalItemValues() : array
	{
		$itemsFromDb = array();

		if ($this->getShipmentItem()->getId() > 0)
		{
			$itemsFromDbList = static::getList(
				array(
					"filter" => array("ORDER_DELIVERY_BASKET_ID" => $this->getShipmentItem()->getId()),
				)
			);
			while ($itemsFromDbItem = $itemsFromDbList->fetch())
			{
				$itemsFromDb[$itemsFromDbItem["ID"]] = $itemsFromDbItem;
			}
		}

		return $itemsFromDb;
	}

	/**
	 * @param array $itemValues
	 */
	protected function callEventOnBeforeSaleShipmentItemStoreDeleted(array $itemValues)
	{
		$itemValues['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnBeforeSaleShipmentItemStoreDeleted", ['VALUES' => $itemValues]);
		$event->send();
	}

	/**
	 * @param array $itemValues
	 */
	protected function callEventOnSaleShipmentItemStoreDeleted(array $itemValues)
	{
		$itemValues['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnSaleShipmentItemStoreDeleted", ['VALUES' => $itemValues]);
		$event->send();
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
		$result = [];

		/** @var ShipmentItemStore $item */
		foreach ($this->collection as $item)
		{
			if (!isset($result[$item->getStoreId()]))
			{
				$result[$item->getStoreId()] = [];
			}

			$result[$item->getStoreId()][$item->getBarcode()] = [
				'ID' => $item->getId(),
				'QUANTITY' => $item->getQuantity(),
			];
		}

		return $result;
	}

	/**
	 * @param $barcode
	 * @return ShipmentItemStore|null
	 */
	public function getItemByBarcode($barcode)
	{
		/** @var ShipmentItemStore $item */
		foreach ($this->collection as $item)
		{
			if ((string)$item->getBarcode() === (string)$barcode)
			{
				return $item;
			}
		}

		return null;
	}

	public function getItemsByStoreId(int $storeId) : Internals\CollectionFilterIterator
	{
		$callback = function (ShipmentItemStore $itemStore) use ($storeId)
		{
			return $itemStore->getStoreId() === $storeId;
		};

		return new Internals\CollectionFilterIterator($this->getIterator(), $callback);
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage $cloneEntity
	 * @return Internals\EntityCollection|ShipmentItemStoreCollection|object
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var ShipmentItemStoreCollection $shipmentItemStoreCollectionClone */
		$shipmentItemStoreCollectionClone = parent::createClone($cloneEntity) ;

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

		return $shipmentItemStoreCollectionClone;
	}

	/**
	 * @param $value
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

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result|Internals\EO_ShipmentItemStore_Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\ShipmentItemStoreTable::getList($parameters);
	}

	/**
	 * @param $primary
	 * @return Main\ORM\Data\DeleteResult
	 * @throws \Exception
	 */
	protected function deleteInternal($primary)
	{
		return Internals\ShipmentItemStoreTable::delete($primary);
	}
}