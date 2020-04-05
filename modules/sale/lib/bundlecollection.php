<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\EntityCollection;

/**
 * Class BundleCollection
 * @package Bitrix\Sale
 */
class BundleCollection extends BasketItemCollection
{
	/** @var null|BasketItem */
	protected $parentBasketItem = null;

	/**
	 * @param BasketItem $basketItem
	 */
	public function setParentBasketItem(BasketItem $basketItem)
	{
		$this->parentBasketItem = $basketItem;
	}

	/**
	 * @return BasketItem|null
	 */
	public function getParentBasketItem()
	{
		return $this->parentBasketItem;
	}

	/**
	 * @return BasketItem|null
	 */
	protected function getEntityParent()
	{
		return $this->getParentBasketItem();
	}

	/**
	 * @param CollectableEntity $item
	 * @return CollectableEntity
	 * @throws Main\ArgumentTypeException
	 */
	public function addItem(CollectableEntity $item)
	{
		return parent::addItem($item);
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters)
	{
		return Basket::getList($parameters);
	}

	/**
	 * @param $index
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function deleteItem($index)
	{
		/** @var Order $order */
		if ($order = $this->getOrder())
		{
			/** @var BasketItem $item */
			$item = $this->getItemByIndex($index);

			$order->onBeforeBasketItemDelete($item);
		}

		return parent::deleteItem($index);
	}

	/**
	 * @return BundleCollection
	 * @throws Main\ArgumentException
	 */
	public static function createBundleCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$bundleCollectionClassName = $registry->getBundleCollectionClassName();

		return new $bundleCollectionClassName();
	}

	/**
	 * @param array $filter
	 * @return BundleCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 */
	public function loadFromDb(array $filter)
	{
		$select = array("ID", "LID", "MODULE", "PRODUCT_ID", "QUANTITY", "WEIGHT",
			"DELAY", "CAN_BUY", "PRICE", "CUSTOM_PRICE", "BASE_PRICE", 'PRODUCT_PRICE_ID', "CURRENCY", 'BARCODE_MULTI',
			"RESERVED", "RESERVE_QUANTITY",	"NAME", "CATALOG_XML_ID", "VAT_RATE", "NOTES", "DISCOUNT_PRICE",
			"PRODUCT_PROVIDER_CLASS", "CALLBACK_FUNC", "ORDER_CALLBACK_FUNC", "PAY_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC",
			"DIMENSIONS", "TYPE", "SET_PARENT_ID", "DETAIL_PAGE_URL", "FUSER_ID", 'MEASURE_CODE', 'MEASURE_NAME', 'ORDER_ID',
			'DATE_INSERT', 'DATE_UPDATE', 'PRODUCT_XML_ID', 'SUBSCRIBE', 'RECOMMENDATION', 'VAT_INCLUDED', 'SORT'
		);

		$itemList = array();

		$res = static::getList(array(
			"filter" => $filter,
			"select" => $select,
			"order" => array('SORT' => 'ASC', 'ID' => 'ASC'),
		));
		while ($item = $res->fetch())
		{
			$itemList[$item['ID']] = $item;
		}

		$this->loadFromArray($itemList);

		return $this;
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage|null $cloneEntity
	 * @return BundleCollection|EntityCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function createClone(\SplObjectStorage $cloneEntity = null)
	{
		if ($cloneEntity === null)
		{
			$cloneEntity = new \SplObjectStorage();
		}

		/** @var BundleCollection $bundleClone */
		$bundleClone = parent::createClone($cloneEntity);

		/** @var BasketItem $parentBasketItem */
		if ($parentBasketItem = $this->parentBasketItem)
		{
			if (!$cloneEntity->contains($parentBasketItem))
			{
				$cloneEntity[$parentBasketItem] = $parentBasketItem->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($parentBasketItem))
			{
				$bundleClone->parentBasketItem = $cloneEntity[$parentBasketItem];
			}
		}

		return $bundleClone;
	}

	/**
	 * @return BasketItemCollection
	 */
	public function getBasket()
	{
		$collection = $this;

		while($collection && $collection instanceof BundleCollection)
		{
			$entityParent = $collection->getEntityParent();
			$collection = $entityParent->getCollection();
		}

		if ($collection instanceof BasketBase)
		{
			return $collection;
		}

		return null;
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		if (!($item instanceof BasketItemBase))
			throw new Main\ArgumentTypeException($item);

		$result = new Result();

		/** @var Order $order */
		$order = $this->getOrder();
		if ($order)
		{
			$shipmentCollection = $order->getShipmentCollection();
			if ($shipmentCollection)
			{
				$r = $shipmentCollection->onBasketModify(EventActions::UPDATE, $item, $name, $oldValue, $value);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}
}
