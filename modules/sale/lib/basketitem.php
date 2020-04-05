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
use Bitrix\Main\NotImplementedException;
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

	/** @var array */
	protected static $mapFields = array();

	/**
	 * @param BasketItemCollection $basketItemCollection
	 * @param string $moduleId
	 * @param int $productId
	 * @param null|string $basketCode
	 * @return BasketItemBase
	 */
	public static function create(BasketItemCollection $basketItemCollection, $moduleId, $productId, $basketCode = null)
	{
		$basketItem = parent::create($basketItemCollection, $moduleId, $productId, $basketCode);

		$basket = $basketItemCollection->getBasket();
		if ($basket instanceof Basket)
		{
			$basketItem->setField('LID', $basket->getSiteId());
		}

		return $basketItem;
	}

	/**
	 * @param array $fields
	 * @throws NotImplementedException
	 * @return BasketItem
	 */
	protected static function createBasketItemObject(array $fields = array())
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$basketItemClassName = $registry->getBasketItemClassName();

		return new $basketItemClassName($fields);
	}

	/**
	 * @return Result
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		$saveResult = parent::save();
		if (!$saveResult->isSuccess())
		{
			$result->addErrors($saveResult->getErrors());
			return $result;
		}

		if ($this->isBundleParent())
		{
			$bundleCollection = $this->getBundleCollection();
			$itemsFromDb = array();

			$id = $this->getId();
			if ($id != 0)
			{
				$itemsFromDbList = Basket::getList(
					array(
						'select' => array('ID'),
						'filter' => array('SET_PARENT_ID' => $id),
					)
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
				Internals\BasketTable::delete($id);
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws ObjectNotFoundException
	 */
	protected function checkBeforeDelete()
	{
		$result = new Result();

		/** @var BasketItemCollection $collection */
		$collection = $this->getCollection();

		/** @var Basket $basket */
		if (!$basket = $collection->getBasket())
		{
			throw new ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var Order $order */
		if ($order = $basket->getOrder())
		{
			/** @var ShipmentCollection $shipmentCollection */
			if ($shipmentCollection = $order->getShipmentCollection())
			{
				/** @var Shipment $shipment */
				foreach ($shipmentCollection as $shipment)
				{
					if ($shipment->isSystem())
					{
						continue;
					}

					/** @var ShipmentItemCollection $shipmentItemCollection */
					if ($shipmentItemCollection = $shipment->getShipmentItemCollection())
					{
						if ($shipmentItemCollection->getItemByBasketCode($this->getBasketCode()) && $shipment->isShipped())
						{
							$result->addError(
								new ResultError(
									Loc::getMessage(
										'SALE_BASKET_ITEM_REMOVE_IMPOSSIBLE_BECAUSE_SHIPPED',
										array('#PRODUCT_NAME#' => $this->getField('NAME'))
									),
									'SALE_BASKET_ITEM_REMOVE_IMPOSSIBLE_BECAUSE_SHIPPED'
								)
							);

							return $result;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws ObjectNotFoundException
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

		return $result;
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	private function clearBundleItemFields(array $fields)
	{
		if (!empty($fields))
		{
			$settableFields = static::getAllFieldsMap();

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
	 * @return bool|int
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
	 * @return BasketPropertiesCollection
	 */
	public function getPropertyCollection()
	{
		if (!$this->existsPropertyCollection())
		{
			$this->propertyCollection = BasketPropertiesCollection::load($this);
		}
		return $this->propertyCollection;
	}

	/**
	 * @return bool
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
	 * @throws ObjectNotFoundException
	 * @throws \Bitrix\Main\NotSupportedException
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

			$bundleChildList = array();
			$result = array();

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

				$bundleChildList[]["ITEMS"][] = array(
						"PRODUCT_ID" => $bundleBasketItem->getProductId(),
						"QUANTITY" => $bundleQuantity
				);

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
	 * @return BundleCollection
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
	 * @return BundleCollection|null
	 */
	public function createBundleCollection()
	{
		if ($this->bundleCollection === null)
		{
			$this->bundleCollection = BundleCollection::createBundleCollectionObject();
			$this->bundleCollection->setParentBasketItem($this);

			$this->setField('TYPE', static::TYPE_SET);
		}

		return $this->bundleCollection;
	}

	/**
	 * @return BundleCollection
	 */
	protected function loadBundleCollectionFromDb()
	{
		$collection = $this->createBundleCollection();

		if ($this->getId() > 0)
		{
			return $collection->loadFromDb(array("SET_PARENT_ID" => $this->getId(), "TYPE" => false));
		}

		return $collection;
	}

	/**
	 * @return BundleCollection|null
	 * @throws Main\ObjectNotFoundException
	 */
	protected function loadBundleCollectionFromProvider()
	{
		global $USER;

		$bundleChildList = array();

		/** @var BasketItemCollection $basket */
		if (!$basket = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var Order $order */
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
			$context = array(
				'SITE_ID' => SITE_ID,
				'USER_ID' => $USER && $USER->GetID() > 0 ? $USER->GetID() : 0,
				'CURRENCY' => CurrencyManager::getBaseCurrency(),
			);
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
	 *
	 * @return BundleCollection
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
	 * @throws ObjectNotFoundException
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() > 0)
		{
			$fields = array();
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
					$fields = array(
						'PRODUCT_ID' => $this->getProductId(),
						'QUANTITY' => $this->getQuantity(),
						'NAME' => $this->getField('NAME'),
					);
				}

				OrderHistory::addField(
					'BASKET',
					$basket->getOrderId(),
					$name,
					$oldValue,
					$value,
					$this->getId(),
					$this,
					$fields);
			}
		}
	}

	/**
	 * @param $quantity
	 *
	 * @return float
	 * @throws ArgumentNullException
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
	public static function getAllFields()
	{
		if (empty(static::$mapFields))
		{
			static::$mapFields = parent::getAllFieldsByMap(Internals\BasketTable::getMap());
		}
		return static::$mapFields;
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return BasketItem
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var BasketItem $basketItemClone */
		$basketItemClone = parent::createClone($cloneEntity);
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

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
	 * @throws ArgumentOutOfRangeException
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
	 * @return BasketItemBase
	 */
	public static function load(BasketItemCollection $basket, $data)
	{
		$bundleItems = array();
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
	 */
	protected function addInternal(array $fields)
	{
		return Internals\BasketTable::add($fields);
	}

	/**
	 * @param $primary
	 * @param array $fields
	 * @return Main\Entity\UpdateResult
	 */
	protected function updateInternal($primary, array $fields)
	{
		return Internals\BasketTable::update($primary, $fields);
	}


	/**
	 * @return float
	 */
	public function getReservedQuantity()
	{
		$reservedQuantity = 0;

		/** @var BasketItemCollection $basketItemCollection */
		$basketItemCollection = $this->getCollection();

		/** @var Order $order */
		$order = $basketItemCollection->getOrder();
		if ($order)
		{
			$shipmentCollection = $order->getShipmentCollection();
			/** @var Shipment $shipment */
			foreach ($shipmentCollection as $shipment)
			{
				$shipmentItemCollection = $shipment->getShipmentItemCollection();
				$shipmentItem = $shipmentItemCollection->getItemByBasketCode($this->getBasketCode());
				if ($shipmentItem)
					$reservedQuantity += $shipmentItem->getReservedQuantity();
			}
		}

		return $reservedQuantity;
	}

}