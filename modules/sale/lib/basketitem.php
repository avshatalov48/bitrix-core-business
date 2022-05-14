<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

/**
 * Class BasketItem
 * @package Bitrix\Sale
 */
class BasketItem extends BasketItemBase
{
	const TYPE_SET = 1;

	/** @var BundleCollection */
	private $bundleCollection = null;

	/** @var ReserveQuantityCollection $reserveQuantityCollection */
	protected $reserveQuantityCollection;

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return Result
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = parent::save();
		if (!$result->isSuccess())
		{
			return $result;
		}

		$reserveCollection = $this->getReserveQuantityCollection();
		$r = $reserveCollection->save();
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		if ($this->isBundleParent())
		{
			$bundleCollection = $this->getBundleCollection();
			$itemsFromDb = [];

			$id = $this->getId();
			if ($id != 0)
			{
				$register = Registry::getInstance(static::getRegistryType());
				/** @var BasketBase $basketClassName */
				$basketClassName = $register->getBasketClassName();

				$itemsFromDbList = $basketClassName::getList(
					[
						'select' => ['ID'],
						'filter' => ['SET_PARENT_ID' => $id],
					]
				);
				while ($itemsFromDbItem = $itemsFromDbList->fetch())
				{
					if ($itemsFromDbItem['ID'] == $id)
						continue;

					$itemsFromDb[$itemsFromDbItem['ID']] = true;
				}
			}

			/** @var BasketItem $bundleItem */
			foreach ($bundleCollection as $bundleItem)
			{
				$parentId = (int)$bundleItem->getField('SET_PARENT_ID');
				if ($parentId <= 0)
					$bundleItem->setFieldNoDemand('SET_PARENT_ID', $id);

				$saveResult = $bundleItem->save();
				if (!$saveResult->isSuccess())
					$result->addErrors($saveResult->getErrors());

				if (isset($itemsFromDb[$bundleItem->getId()]))
					unset($itemsFromDb[$bundleItem->getId()]);
			}

			foreach ($itemsFromDb as $id => $value)
			{
				$this->deleteInternal($id);
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws ObjectNotFoundException
	 */
	protected function add()
	{
		$logFields = $this->getLoggedFields();

		$result = parent::add();

		/** @var BasketItemCollection $collection */
		$collection = $this->getCollection();

		/** @var BasketBase $basket */
		if (!$basket = $collection->getBasket())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		if ($basket->getOrderId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());
			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();

			if (!$result->isSuccess())
			{
				$orderHistory::addAction(
					'BASKET',
					$basket->getOrderId(),
					'BASKET_ITEM_ADD_ERROR',
					null,
					$this,
					["ERROR" => $result->getErrorMessages()]
				);
			}
			else
			{
				$orderHistory::addLog(
					'BASKET',
					$basket->getOrderId(),
					"BASKET_ITEM_ADD",
					$this->getId(),
					$this,
					$logFields,
					$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
				);

				$orderHistory::addAction(
					'BASKET',
					$basket->getOrderId(),
					"BASKET_SAVED",
					$this->getId(),
					$this,
					[],
					$orderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
				);
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws ObjectNotFoundException
	 */
	protected function update()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();

		/** @var BasketItemCollection $collection */
		$collection = $this->getCollection();

		/** @var BasketBase $basket */
		if (!$basket = $collection->getBasket())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		$logFields = $this->getLoggedFields();

		$result = parent::update();

		if (!$result->isSuccess())
		{
			if ($basket->getOrderId() > 0)
			{
				$orderHistory::addAction(
					'BASKET',
					$basket->getOrderId(),
					'BASKET_ITEM_UPDATE_ERROR',
					null,
					$this,
					["ERROR" => $result->getErrorMessages()]
				);
			}
		}
		else
		{
			$orderHistory::addLog(
				'BASKET',
				$basket->getOrderId(),
				"BASKET_ITEM_UPDATE",
				$this->getId(),
				$this,
				$logFields,
				$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
			);

			$orderHistory::addAction(
				'BASKET',
				$basket->getOrderId(),
				"BASKET_SAVED",
				$this->getId(),
				$this,
				[],
				$orderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
			);
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	private function getLoggedFields()
	{
		/** @var Basket $basket */
		$basket = $this->getCollection();

		$orderId = $basket->getOrderId();

		$changeMeaningfulFields = [
			"PRODUCT_ID",
			"QUANTITY",
			"PRICE",
			"DISCOUNT_VALUE",
			"VAT_RATE",
			"NAME",
		];

		$logFields = [];
		if ($orderId > 0 && $this->isChanged())
		{
			$itemValues = $this->getFields();
			$originalValues = $itemValues->getOriginalValues();

			foreach($originalValues as $originalFieldName => $originalFieldValue)
			{
				if (in_array($originalFieldName, $changeMeaningfulFields) && $this->getField($originalFieldName) != $originalFieldValue)
				{
					$logFields[$originalFieldName] = $this->getField($originalFieldName);
					$logFields['OLD_'.$originalFieldName] = $originalFieldValue;
				}
			}
		}

		return $logFields;
	}

	/**
	 * @return Result
	 * @throws ArgumentNullException
	 * @throws ObjectNotFoundException
	 */
	protected function checkBeforeDelete()
	{
		$result = new Result();

		/** @var BasketItemCollection $collection */
		$collection = $this->getCollection();

		/** @var Order $order */
		$order = $collection->getBasket()->getOrder();

		if ($order)
		{
			/** @var Shipment $shipment */
			foreach ($order->getShipmentCollection() as $shipment)
			{
				if ($shipment->isSystem())
				{
					continue;
				}

				/** @var ShipmentItemCollection $shipmentItemCollection */
				if ($shipmentItemCollection = $shipment->getShipmentItemCollection())
				{
					if ($shipmentItemCollection->getItemByBasketCode($this->getBasketCode())
						&& $shipment->isShipped()
					)
					{
						$result->addError(
							new ResultError(
								Loc::getMessage(
									'SALE_BASKET_ITEM_REMOVE_IMPOSSIBLE_BECAUSE_SHIPPED',
									['#PRODUCT_NAME#' => $this->getField('NAME')]
								),
								'SALE_BASKET_ITEM_REMOVE_IMPOSSIBLE_BECAUSE_SHIPPED'
							)
						);

						return $result;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws ArgumentOutOfRangeException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	public function delete()
	{
		$result = new Result();

		$deleteResult = parent::delete();
		if (!$deleteResult->isSuccess())
		{
			$result->addErrors($deleteResult->getErrors());
			return $result;
		}

		if ($this->isBundleParent())
		{
			$bundleCollection = $this->getBundleCollection();
			if ($bundleCollection)
			{
				/** @var BasketItem $bundleItem */
				foreach ($bundleCollection as $bundleItem)
				{
					$deleteResult = $bundleItem->delete();
					if (!$deleteResult->isSuccess())
					{
						$result->addErrors($deleteResult->getErrors());
					}
				}
			}
		}

		/** @var ReserveQuantity $reserve */
		foreach ($this->getReserveQuantityCollection() as $reserve)
		{
			$r = $reserve->delete();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return ReserveQuantityCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public function getReserveQuantityCollection()
	{
		if ($this->reserveQuantityCollection === null)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var ReserveQuantityCollection $reserveCollectionClassName */
			$reserveCollectionClassName = $registry->getReserveCollectionClassName();

			$this->reserveQuantityCollection = $reserveCollectionClassName::load($this);
		}

		return $this->reserveQuantityCollection;
	}

	/**
	 * @param array $fields
	 * @return array
	 * @throws Main\NotImplementedException
	 */
	private function clearBundleItemFields(array $fields)
	{
		if (!empty($fields))
		{
			$settableFields = static::getAllFields();

			foreach ($fields as $name => $value)
			{
				if (!isset($settableFields[$name]))
				{
					unset($fields[$name]);
				}
			}
		}

		return $fields;
	}

	/**
	 * @return BasketItem|null
	 */
	public function getParentBasketItem()
	{
		$collection = $this->getCollection();

		if ($collection instanceof BundleCollection)
		{
			return $collection->getParentBasketItem();
		}

		return null;
	}

	/**
	 * @return int|null|string
	 * @throws ArgumentNullException
	 */
	public function getParentBasketItemId()
	{
		if ($parentBasketItem = $this->getParentBasketItem())
		{
			return $parentBasketItem->getId();
		}
		return null;
	}

	/**
	 * @return bool
	 * @throws ArgumentNullException
	 */
	public function isBundleParent()
	{
		return (int)$this->getField('TYPE') === static::TYPE_SET;
	}

	/**
	 * @return bool
	 */
	public function isBundleChild()
	{
		return $this->collection instanceof BundleCollection;
	}

	/**
	 * @return array|bool
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	public function getBundleBaseQuantity()
	{
		if ($this->isBundleParent())
		{
			/** @var BundleCollection $bundleCollection */
			if (!($bundleCollection = $this->getBundleCollection()))
			{
				throw new ObjectNotFoundException('Entity "BasketBundleCollection" not found');
			}

			$bundleChildList = [];
			$result = [];

			$originalQuantity = $this->getQuantity();
			$originalValues = $this->fields->getOriginalValues();
			if (array_key_exists('QUANTITY', $originalValues) && $originalValues['QUANTITY'] !== null)
			{
				$originalQuantity = $originalValues['QUANTITY'];
			}
			/** @var BasketItem $bundleBasketItem */
			foreach ($bundleCollection as $bundleBasketItem)
			{
				$originalBundleQuantity = $bundleBasketItem->getQuantity();
				$originalBundleValues = $bundleBasketItem->getFields()->getOriginalValues();
				if (array_key_exists('QUANTITY', $originalBundleValues)  && $originalBundleValues['QUANTITY'] !== null)
				{
					$originalBundleQuantity = $originalBundleValues['QUANTITY'];
				}

				if ($originalQuantity > 0)
				{
					$bundleQuantity = $originalBundleQuantity / $originalQuantity;
				}
				else
				{
					$bundleQuantity = 0;
				}

				$bundleChildList[]["ITEMS"][] = [
						"PRODUCT_ID" => $bundleBasketItem->getProductId(),
						"QUANTITY" => $bundleQuantity
				];

			}

			if (empty($bundleChildList))
				return false;

			foreach ($bundleChildList as $bundleBasketListDat)
			{
				foreach ($bundleBasketListDat["ITEMS"] as $bundleDat)
				{
					$result[$bundleDat['PRODUCT_ID']] = $bundleDat['QUANTITY'];
				}
			}

			return $result;
		}

		return false;
	}

	/**
	 * @return BundleCollection|null
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	public function getBundleCollection()
	{
		if ($this->bundleCollection === null)
		{
			if ($this->getId() > 0)
			{
				$this->bundleCollection = $this->loadBundleCollectionFromDb();
			}
			else
			{
				$this->bundleCollection = $this->loadBundleCollectionFromProvider();
			}
		}

		return $this->bundleCollection;
	}

	/**
	 * @return BundleCollection
	 * @throws ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public function createBundleCollection()
	{
		if ($this->bundleCollection === null)
		{
			$registry = Registry::getInstance(static::getRegistryType());
			/** @var BundleCollection $bundleClassName */
			$bundleClassName = $registry->getBundleCollectionClassName();

			$this->bundleCollection = $bundleClassName::createBundleCollectionObject();
			$this->bundleCollection->setParentBasketItem($this);

			$this->setField('TYPE', static::TYPE_SET);
		}

		return $this->bundleCollection;
	}

	/**
	 * @return BundleCollection
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	protected function loadBundleCollectionFromDb()
	{
		$collection = $this->createBundleCollection();

		if ($this->getId() > 0)
		{
			return $collection->loadFromDb(["SET_PARENT_ID" => $this->getId(), "TYPE" => false]);
		}

		return $collection;
	}

	/**
	 * @return BundleCollection|null
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	protected function loadBundleCollectionFromProvider()
	{
		global $USER;

		$bundleChildList = [];

		/** @var BasketItemCollection $basket */
		if (!$basket = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var Order $order */
		$order = $basket->getOrder();
		if ($order)
		{
			$context = [
				'SITE_ID' => $order->getSiteId(),
				'USER_ID' => $order->getUserId(),
				'CURRENCY' => $order->getCurrency(),
			];
		}
		else
		{
			$context = [
				'SITE_ID' => SITE_ID,
				'USER_ID' => $USER && $USER->GetID() > 0 ? $USER->GetID() : 0,
				'CURRENCY' => CurrencyManager::getBaseCurrency(),
			];
		}

		$creator = Internals\ProviderCreator::create($context);
		$creator->addBasketItem($this);
		$r = $creator->getBundleItems();
		if ($r->isSuccess())
		{
			$resultProductListData = $r->getData();
			if (!empty($resultProductListData['BUNDLE_LIST']))
			{
				$bundleChildList = $resultProductListData['BUNDLE_LIST'];
			}
		}

		if (empty($bundleChildList))
		{
			return null;
		}

		$this->bundleCollection = $this->setItemsAfterGetBundle($bundleChildList);
		return $this->bundleCollection;
	}

	/**
	 * @param array $items
	 * @return BundleCollection
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws \Exception
	 */
	private function setItemsAfterGetBundle(array $items)
	{
		/** @var BundleCollection $bundleCollection */
		$bundleCollection = $this->createBundleCollection();
		foreach ($items as $providerClassName => $products)
		{
			foreach ($products as $productId => $bundleBasketListDat)
			{
				foreach ($bundleBasketListDat["ITEMS"] as $bundleDat)
				{
					$bundleFields = $this->clearBundleItemFields($bundleDat);
					unset($bundleFields['ID']);

					$bundleFields['CURRENCY'] = $this->getCurrency();

					if ($this->getId() > 0)
					{
						$bundleFields['SET_PARENT_ID'] = $this->getId();
					}

					/** @var BasketItem $basketItem */
					$bundleBasketItem = static::create($bundleCollection, $bundleFields['MODULE'], $bundleFields['PRODUCT_ID']);

					if (!empty($bundleDat["PROPS"]) && is_array($bundleDat["PROPS"]))
					{
						/** @var BasketPropertiesCollection $property */
						$property = $bundleBasketItem->getPropertyCollection();
						$property->setProperty($bundleDat["PROPS"]);
					}

					$bundleQuantity = $bundleFields['QUANTITY'] * $this->getQuantity();
					unset($bundleFields['QUANTITY']);

					$bundleBasketItem->setFieldsNoDemand($bundleFields);
					$bundleBasketItem->setField('QUANTITY', $bundleQuantity);
					$bundleCollection->addItem($bundleBasketItem);
				}
			}
		}

		return $bundleCollection;
	}

	/**
	 * @param $basketCode
	 * @return BasketItemBase|null
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	public function findItemByBasketCode($basketCode)
	{
		$item = parent::findItemByBasketCode($basketCode);
		if ($item !== null)
			return $item;

		if ($this->isBundleParent())
		{
			$collection = $this->getBundleCollection();
			/** @var BasketItemBase $basketItem */
			foreach ($collection as $basketItem)
			{
				$item = $basketItem->findItemByBasketCode($basketCode);
				if ($item !== null)
					return $item;
			}
		}

		return null;
	}

	/**
	 * @param $id
	 * @return BasketItemBase|null
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	public function findItemById($id)
	{
		$item = parent::findItemById($id);
		if ($item !== null)
			return $item;

		if ($this->isBundleParent())
		{
			$collection = $this->getBundleCollection();
			/** @var BasketItemBase $basketItem */
			foreach ($collection as $basketItem)
			{
				$item = $basketItem->findItemById($id);
				if ($item !== null)
					return $item;
			}
		}

		return null;
	}

	/**
	 * @param string $name
	 * @param null $oldValue
	 * @param null $value
	 * @throws ArgumentNullException
	 * @throws ObjectNotFoundException
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() > 0)
		{
			$fields = [];
			/** @var Basket $basket */
			if (!$basket = $this->getCollection())
			{
				throw new ObjectNotFoundException('Entity "Basket" not found');
			}

			if ($basket->getOrder() && $basket->getOrderId() > 0)
			{
				if ($name == "QUANTITY")
				{
					if (floatval($value) == 0)
					{
						return;
					}
					$fields = [
						'PRODUCT_ID' => $this->getProductId(),
						'QUANTITY' => $this->getQuantity(),
						'NAME' => $this->getField('NAME'),
					];
				}

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addField(
					'BASKET',
					$basket->getOrderId(),
					$name,
					$oldValue,
					$value,
					$this->getId(),
					$this,
					$fields
				);
			}
		}
	}

	/**
	 * @param $quantity
	 * @return float|string
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 */
	public static function formatQuantity($quantity)
	{
		$format = Config\Option::get('sale', 'format_quantity', 'AUTO');
		if ($format == 'AUTO' || intval($format) <= 0)
		{
			$quantity = round($quantity, SALE_VALUE_PRECISION);
		}
		else
		{
			$quantity = number_format($quantity, intval($format), '.', '');
		}

		return $quantity;
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\BasketTable::getMap();
	}

	public function isChanged()
	{
		$isChanged = parent::isChanged();

		if ($isChanged === false)
		{
			$reserveCollection = $this->getReserveQuantityCollection();
			$isChanged = $reserveCollection->isChanged();
		}

		return $isChanged;
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage $cloneEntity
	 * @return BasketItem|Internals\CollectableEntity|object
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var BasketItem $basketItemClone */
		$basketItemClone = parent::createClone($cloneEntity);

		/** @var Internals\Fields $calculatedFields */
		if ($calculatedFields = $this->calculatedFields)
		{
			$basketItemClone->calculatedFields = $calculatedFields->createClone($cloneEntity);
		}

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $basketItemClone;
		}

		/** @var BasketPropertiesCollection $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			if (!$cloneEntity->contains($propertyCollection))
			{
				$cloneEntity[$propertyCollection] = $propertyCollection->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($propertyCollection))
			{
				$basketItemClone->propertyCollection = $cloneEntity[$propertyCollection];
			}
		}

		/** @var ReserveQuantityCollection $reservedCollection */
		if ($reservedCollection = $this->getReserveQuantityCollection())
		{
			if (!$cloneEntity->contains($reservedCollection))
			{
				$cloneEntity[$reservedCollection] = $reservedCollection->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($reservedCollection))
			{
				$basketItemClone->reserveQuantityCollection = $cloneEntity[$reservedCollection];
			}
		}

		if ($this->isBundleParent())
		{
			/** @var BundleCollection $bundleCollection */
			if ($bundleCollection = $this->getBundleCollection())
			{
				if (!$cloneEntity->contains($bundleCollection))
				{
					$cloneEntity[$bundleCollection] = $bundleCollection->createClone($cloneEntity);
				}

				if ($cloneEntity->contains($bundleCollection))
				{
					$basketItemClone->bundleCollection = $cloneEntity[$bundleCollection];
				}
			}
		}

		return $basketItemClone;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 * @throws ObjectNotFoundException
	 * @throws \Exception
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		$result = new Result();

		$r = parent::onFieldModify($name, $oldValue, $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if (!$this->isBundleParent())
			return $result;

		if ($name === 'QUANTITY')
		{
			$deltaQuantity = $value - $oldValue;
			if ($deltaQuantity != 0)
			{
				if ($bundleCollection = $this->getBundleCollection())
				{
					$bundleBaseQuantity = $this->getBundleBaseQuantity();

					/** @var BasketItemBase $bundleItem */
					foreach ($bundleCollection as $bundleItem)
					{
						$bundleProductId = $bundleItem->getProductId();

						if (!isset($bundleBaseQuantity[$bundleProductId]))
							throw new ArgumentOutOfRangeException('bundle product id');

						$quantity = $bundleBaseQuantity[$bundleProductId] * $value;

						$r = $bundleItem->setField('QUANTITY', $quantity);
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}
		}
		elseif ($name == "DELAY")
		{
			/** @var BundleCollection $bundleCollection */
			if ($bundleCollection = $this->getBundleCollection())
			{
				/** @var BasketItemBase $bundleItem */
				foreach ($bundleCollection as $bundleItem)
				{
					$r = $bundleItem->setField('DELAY', $value);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}
		elseif ($name == "CAN_BUY")
		{
			/** @var BundleCollection $bundleCollection */
			if ($bundleCollection = $this->getBundleCollection())
			{
				/** @var BasketItemBase $bundleItem */
				foreach ($bundleCollection as $bundleItem)
				{
					$r = $bundleItem->setField('CAN_BUY', $value);
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
	 * @param BasketItemCollection $basket
	 * @param $data
	 * @return BasketItem|mixed
	 * @throws ArgumentException
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws \Exception
	 */
	public static function load(BasketItemCollection $basket, $data)
	{
		$bundleItems = [];
		if (isset($data['ITEMS']))
		{
			$bundleItems = $data['ITEMS'];
			unset($data['ITEMS']);
		}

		/** @var BasketItem $basketItem */
		$basketItem = parent::load($basket, $data);

		if ($bundleItems)
		{
			$bundleCollection = $basketItem->createBundleCollection();
			$bundleCollection->loadFromArray($bundleItems);
		}

		return $basketItem;
	}

	/**
	 * @param array $fields
	 * @return Main\Entity\AddResult
	 * @throws \Exception
	 */
	protected function addInternal(array $fields)
	{
		return Internals\BasketTable::add($fields);
	}

	/**
	 * @param $primary
	 * @param array $fields
	 * @return Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	protected function updateInternal($primary, array $fields)
	{
		return Internals\BasketTable::update($primary, $fields);
	}

	/**
	 * @param $primary
	 * @return Main\Entity\DeleteResult
	 * @throws \Exception
	 */
	protected function deleteInternal($primary)
	{
		return Internals\BasketTable::delete($primary);
	}

	/**
	 * @return float
	 * @throws ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public function getReservedQuantity()
	{
		return $this->getReserveQuantityCollection()->getQuantity();
	}

	/**
	 * @return float
	 */
	public function getNotPurchasedQuantity() : float
	{
		$quantity = parent::getNotPurchasedQuantity();

		/** @var Order $order */
		$order = $this->getCollection()->getOrder();
		if ($order)
		{
			$quantity -= $order->getShipmentCollection()->getBasketItemShippedQuantity($this);
		}

		return $quantity;
	}

}