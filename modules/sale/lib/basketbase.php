<?php
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Basket\RefreshFactory;
use Bitrix\Sale\Basket\RefreshStrategy;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

/**
 * Class BasketBase
 * @package Bitrix\Sale
 */
abstract class BasketBase extends BasketItemCollection
{
	/** @var string */
	protected $siteId = null;

	/** @var int */
	protected $fUserId = null;

	/** @var OrderBase */
	protected $order = null;

	/** @var array $basketItemIndexMap */
	protected $basketItemIndexMap = array();

	/** @var int $maxItemSort */
	protected $maxItemSort = null;

	/**
	 * @param $itemCode
	 * @return BasketItemBase|null
	 */
	public function getItemByBasketCode($itemCode)
	{
		if (
			isset($this->basketItemIndexMap[$itemCode])
			&& isset($this->collection[$this->basketItemIndexMap[$itemCode]])
		)
		{
			return $this->collection[$this->basketItemIndexMap[$itemCode]];
		}

		return parent::getItemByBasketCode($itemCode);
	}

	/**
	 * @param BasketItemBase $item
	 *
	 * @return BasketItemBase|null
	 */
	public function getExistsItemByItem(BasketItemBase $item)
	{
		$propertyList = [];
		$propertyCollection = $item->getPropertyCollection();
		if ($propertyCollection)
		{
			$propertyList = $propertyCollection->getPropertyValues();
		}
		return $this->getExistsItem($item->getField('MODULE'), $item->getField('PRODUCT_ID'), $propertyList);
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return BasketItemBase
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function deleteItem($index)
	{
		$oldItem = parent::deleteItem($index);

		unset($this->basketItemIndexMap[$oldItem->getBasketCode()]);

		/** @var OrderBase $order */
		if ($order = $this->getOrder())
			$order->onBasketModify(EventActions::DELETE, $oldItem);

		return $oldItem;
	}

	/**
	 * @return OrderBase
	 */
	protected function getEntityParent()
	{
		return $this->getOrder();
	}

	/**
	 * @return bool
	 */
	public function isLoadForFUserId()
	{
		return $this->fUserId !== null;
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return BasketBase
	 */
	protected static function createBasketObject()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $fUserId
	 * @param $siteId
	 * @return BasketBase
	 */
	public static function loadItemsForFUser($fUserId, $siteId)
	{
		/** @var BasketBase $basket */
		$basket = static::create($siteId);

		$basket->setFUserId($fUserId);

		/** @var BasketBase $collection */
		return $basket->loadFromDb(
			array(
				"FUSER_ID" => $fUserId,
				"=LID" => $siteId,
				"ORDER_ID" => null
			)
		);
	}

	/**
	 * Returns copy of current basket.
	 * For example, the copy will be used to calculate discounts.
	 * So, basket does not contain full information about BasketItem with bundleCollection, because now it is not
	 * necessary.
	 *
	 * Attention! Don't save the basket.
	 *
	 * @internal
	 * @return BasketBase
	 * @throws Main\SystemException
	 */
	public function copy()
	{
		if($this->order !== null)
		{
			throw new Main\SystemException('Could not clone basket which has order.');
		}

		$basket = static::create($this->siteId);
		/**@var BasketItemBase $item */
		foreach($this as $originalItem)
		{
			$item = $basket->createItem($originalItem->getField("MODULE"), $originalItem->getProductId());
			$item->initFields($originalItem->getFields()->getValues());
		}

		return $basket;
	}

	/**
	 * @param array $requestBasket
	 * @return BasketBase
	 * @throws Main\NotImplementedException
	 */
	public static function createFromRequest(array $requestBasket)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @internal
	 *
	 * Load the contents of the basket to order
	 *
	 * @param OrderBase $order - object of the order
	 * @return BasketBase
	 */
	public static function loadItemsForOrder(OrderBase $order)
	{
		$basket = static::createBasketObject();
		$basket->setOrder($order);
		$basket->setSiteId($order->getSiteId());

		return $basket->loadFromDb(array("ORDER_ID" => $order->getId()));
	}

	/**
	 * @param array $filter
	 * @throws \Exception
	 * @return BasketBase
	 */
	abstract public function loadFromDb(array $filter);

	/**
	 * Attach to the essence of the object of the order basket
	 *
	 * @param OrderBase $order - object of the order
	 */
	public function setOrder(OrderBase $order)
	{
		$this->order = $order;
	}

	/**
	 * Getting the object of the order
	 *
	 * @return OrderBase
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @internal
	 *
	 * @param Internals\CollectableEntity $basketItem
	 * @return void
	 */
	public function addItem(Internals\CollectableEntity $basketItem)
	{
		/** @var BasketItemBase $basketItem */
		$basketItem = parent::addItem($basketItem);

		$this->basketItemIndexMap[$basketItem->getBasketCode()] = $basketItem->getInternalIndex();

		$this->verifyItemSort($basketItem);

		$basketItem->setCollection($this);

		/** @var OrderBase $order */
		if ($order = $this->getOrder())
		{
			$order->onBasketModify(EventActions::ADD, $basketItem);
		}
	}

	protected function verifyItemSort(BasketItemBase $item)
	{
		$itemSort = (int)$item->getField('SORT') ?: 100;

		if ($this->maxItemSort === null)
		{
			$this->maxItemSort = $itemSort;
		}
		else
		{
			if ($itemSort > $this->maxItemSort)
			{
				$this->maxItemSort = $itemSort;
			}
			else
			{
				$this->maxItemSort += 100 + $this->maxItemSort % 100;
			}
		}

		$item->setFieldNoDemand('SORT', $this->maxItemSort);
	}

	/**
	 * @param $siteId
	 * @return BasketBase
	 */
	public static function create($siteId)
	{
		$basket = static::createBasketObject();
		$basket->setSiteId($siteId);

		return $basket;
	}

	/**
	 * Getting basket price with discounts and taxes
	 *
	 * @return float
	 */
	public function getPrice()
	{
		$orderPrice = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
			$orderPrice += $basketItem->getFinalPrice();

		return $orderPrice;
	}

	/**
	 * Getting basket price without discounts
	 *
	 * @return float
	 */
	public function getBasePrice()
	{
		$orderPrice = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			if ($basketItem->isCustomPrice())
				$basePrice = $basketItem->getPrice();
			else
				$basePrice = $basketItem->getBasePrice();

			$orderPrice += PriceMaths::roundPrecision($basePrice * $basketItem->getQuantity());
		}

		$orderPrice = PriceMaths::roundPrecision($orderPrice);

		return $orderPrice;
	}

	/**
	 * Getting the value of the tax basket
	 *
	 * @return float
	 */
	public function getVatSum()
	{
		$vatSum = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			// BasketItem that is removed is not involved
			if ($basketItem->getQuantity() == 0)
				continue;

			$vatSum += $basketItem->getVat();
		}

		return $vatSum;
	}

	/**
	 * Getting the value of the tax rate basket
	 *
	 * @return float
	 */
	public function getVatRate()
	{
		$vatRate = 0;
		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			// BasketItem that is removed is not involved
			if ($basketItem->getQuantity() == 0)
				continue;

			if ($basketItem->getVatRate() > $vatRate)
			{
				$vatRate = $basketItem->getVatRate();
			}
		}

		return $vatRate;
	}

	/**
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function verify()
	{
		$result = new Result();

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$r = $basketItem->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());

				/** @var OrderBase $order */
				if ($order = $this->getOrder())
				{
					EntityMarker::addMarker($order, $basketItem, $r);
					$order->setField('MARKED', 'Y');
				}
			}
		}

		return $result;
	}

	/**
	 * Getting the weight basket
	 *
	 * @return int
	 */
	public function getWeight()
	{
		$orderWeight = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$orderWeight += $basketItem->getWeight() * $basketItem->getQuantity();
		}

		return $orderWeight;
	}

	/**
	 * @return array
	 */
	abstract protected function getOriginalItemsValues();

	/**
	 * @param array $itemValues
	 */
	abstract protected function deleteInternal(array $itemValues);

	/**
	 * @return string
	 */
	abstract protected function getItemEventName();

	/**
	 * Save basket
	 *
	 * @return Result
	 */
	public function save()
	{
		$result = new Result();

		/** @var OrderBase $order */
		$order = $this->getOrder();
		$orderId = ($order) ? $order->getId() : 0;

		if (!$order)
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', EventActions::EVENT_ON_BASKET_BEFORE_SAVED, array(
				'ENTITY' => $this
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_BASKET_SAVED'), 'SALE_EVENT_ON_BEFORE_BASKET_SAVED');
						if ($eventResultData = $eventResult->getParameters())
						{
							if (isset($eventResultData) && $eventResultData instanceof ResultError)
							{
								/** @var ResultError $errorMsg */
								$errorMsg = $eventResultData;
							}
						}

						$result->addError($errorMsg);
					}
				}

				if (!$result->isSuccess())
				{
					return $result;
				}
			}
		}

		$originalItemsValues = $this->getOriginalItemsValues();

		$changeMeaningfulFields = array(
			"PRODUCT_ID",
			"QUANTITY",
			"PRICE",
			"DISCOUNT_VALUE",
			"VAT_RATE",
			"NAME",
		);

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$isNew = (bool)($basketItem->getId() <= 0);
			$isChanged = $basketItem->isChanged();

			$logFields = array();
			if ($orderId > 0 && $isChanged)
			{
				$itemValues = $basketItem->getFields();
				$originalValues = $itemValues->getOriginalValues();

				foreach($originalValues as $originalFieldName => $originalFieldValue)
				{
					if (in_array($originalFieldName, $changeMeaningfulFields) && $basketItem->getField($originalFieldName) != $originalFieldValue)
					{
						$logFields[$originalFieldName] = $basketItem->getField($originalFieldName);
						$logFields['OLD_'.$originalFieldName] = $originalFieldValue;
					}
				}
			}

			$r = $basketItem->save();
			if ($r->isSuccess())
			{
				if ($orderId > 0 && $isChanged)
				{
					OrderHistory::addLog(
						'BASKET',
						$orderId,
						$isNew ? "BASKET_ITEM_ADD" : "BASKET_ITEM_UPDATE",
						$basketItem->getId(),
						$basketItem,
						$logFields,
						OrderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
					);

					OrderHistory::addAction(
						'BASKET',
						$orderId,
						"BASKET_SAVED",
						$basketItem->getId(),
						$basketItem,
						array(),
						OrderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
					);
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if (isset($originalItemsValues[$basketItem->getId()]) && $basketItem->getQuantity() > 0)
				unset($originalItemsValues[$basketItem->getId()]);
		}

		if ($originalItemsValues)
		{
			$itemEventName = $this->getItemEventName();

			foreach ($originalItemsValues as $id => $itemValues)
			{
				/** @var Main\Event $event */
				$event = new Main\Event('sale', "OnBefore".$itemEventName."Deleted", array('VALUES' => $itemValues));
				$event->send();

				$this->deleteInternal($itemValues);

				if ($orderId > 0)
				{
					OrderHistory::addLog(
						'BASKET',
						$orderId,
						'BASKET_ITEM_DELETED',
						$itemValues['ID'],
						null,
						array(
							"PRODUCT_ID" => $itemValues["PRODUCT_ID"],
							"NAME" => $itemValues["NAME"],
							"QUANTITY" => $itemValues["QUANTITY"],
						),
						OrderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
					);
				}

				/** @var Main\Event $event */
				$event = new Main\Event('sale', "On".$itemEventName."Deleted", array('VALUES' => $itemValues));
				$event->send();

				if ($orderId > 0)
				{
					OrderHistory::addAction(
						'BASKET',
						$orderId,
						'BASKET_REMOVED',
						$id ,
						null,
						array(
							'NAME' => $itemValues['NAME'],
							'QUANTITY' => $itemValues['QUANTITY'],
							'PRODUCT_ID' => $itemValues['PRODUCT_ID'],
						)
					);

					EntityMarker::deleteByFilter(array(
						'=ORDER_ID' => $orderId,
						'=ENTITY_TYPE' => EntityMarker::ENTITY_TYPE_BASKET_ITEM,
						'=ENTITY_ID' => $id,
					));
				}
			}
		}

		if ($orderId > 0)
		{
			OrderHistory::collectEntityFields('BASKET', $orderId);
		}

		if (!$order)
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', EventActions::EVENT_ON_BASKET_SAVED, array(
				'ENTITY' => $this
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BASKET_SAVED'), 'SALE_EVENT_ON_BASKET_SAVED');
						if ($eventResultData = $eventResult->getParameters())
						{
							if (isset($eventResultData) && $eventResultData instanceof ResultError)
							{
								/** @var ResultError $errorMsg */
								$errorMsg = $eventResultData;
							}
						}

						$result->addError($errorMsg);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Setting Customer ID to basket
	 *
	 * @param $fUserId - customer ID
	 */
	public function setFUserId($fUserId)
	{
		$this->fUserId = (int)$fUserId > 0 ? (int)$fUserId : null;
	}

	/**
	 * Setting site ID to basket
	 *
	 * @param $siteId - site ID
	 */
	protected function setSiteId($siteId)
	{
		$this->siteId = $siteId;
	}

	/**
	 * Getting Customer ID
	 *
	 * @param bool $skipCreate - Creating a buyer if it is not found
	 * @return int|null
	 */
	public function getFUserId($skipCreate = false)
	{
		if ($this->fUserId === null)
		{
			$this->fUserId = Fuser::getId($skipCreate);
		}
		return $this->fUserId;
	}

	/**
	 * Getting Site ID
	 *
	 * @return string
	 */
	public function getSiteId()
	{
		return $this->siteId;
	}

	/**
	 * Getting a list of a count of elements in the basket
	 *
	 * @return array
	 */
	public function getQuantityList()
	{
		$quantityList = array();

		/**
		 * @var  $basketKey
		 * @var BasketItemBase $basketItem
		 */
		foreach ($this->collection as $basketKey => $basketItem)
		{
			$quantityList[$basketItem->getBasketCode()] = $basketItem->getQuantity();
		}

		return $quantityList;
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return BasketItemCollection
	 */
	public function createClone(\SplObjectStorage $cloneEntity = null)
	{
		if ($cloneEntity === null)
		{
			$cloneEntity = new \SplObjectStorage();
		}

		/** @var BasketBase $basketClone */
		$basketClone = parent::createClone($cloneEntity);

		if ($this->order)
		{
			if ($cloneEntity->contains($this->order))
			{
				$basketClone->order = $cloneEntity[$this->order];
			}
		}

		return $basketClone;
	}

	/**
	 * @param array $parameters
	 * @throws Main\NotImplementedException
	 * @return mixed
	 */
	public static function getList(array $parameters = array())
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentTypeException
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		if (!($item instanceof BasketItemBase))
			throw new Main\ArgumentTypeException($item);

		$result = new Result();

		/** @var OrderBase $order */
		if ($order = $this->getOrder())
		{
			$r = $order->onBasketModify(EventActions::UPDATE, $item, $name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}


	/**
	 * @param RefreshStrategy|null $strategy
	 * @return Result
	 */
	public function refresh(RefreshStrategy $strategy = null)
	{
		$isStartField = $this->isStartField();

		/** @var OrderBase $order */
		$order = $this->getOrder();
		if ($order)
		{
			$r = $order->onBeforeBasketRefresh();
			if (!$r->isSuccess())
			{
				return $r;
			}
		}

		if ($strategy === null)
		{
			$strategy = RefreshFactory::create();
		}

		$result = $strategy->refresh($this);

		if ($order)
		{
			$r = $order->onAfterBasketRefresh();
			if (!$r->isSuccess())
			{
				return $r;
			}
		}

		$changedBasketItems = $result->get('CHANGED_BASKET_ITEMS');
		if (!empty($changedBasketItems))
		{
			/** @var OrderBase $order */
			$order = $this->getOrder();
			if ($order)
			{
				$r = $order->refreshData(array('PRICE', 'PRICE_DELIVERY'));
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $this->hasMeaningfulField();

			/** @var Result $r */
			$r = $this->doFinalAction($hasMeaningfulFields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param array           $select
	 * @param BasketItemBase|null $refreshItem
	 *
	 * @return Result
	 */
	public function refreshData($select = array(), BasketItemBase $refreshItem = null)
	{
		if ($refreshItem !== null)
		{
			$strategy = RefreshFactory::createSingle($refreshItem->getBasketCode());
		}
		else
		{
			$strategy = RefreshFactory::create(RefreshFactory::TYPE_FULL);
		}

		return $this->refresh($strategy);
	}

	/**
	 * @return BasketBase
	 */
	public function getOrderableItems()
	{
		/** @var BasketBase $basket */
		$basket = static::create($this->getSiteId());

		if ($this->isLoadForFUserId())
		{
			$basket->setFUserId($this->getFUserId(true));
		}

		if ($order = $this->getOrder())
		{
			$basket->setOrder($order);
		}

		$sortedCollection = $this->collection;
		usort($sortedCollection, function(BasketItemBase $a, BasketItemBase $b){
			return (int)$a->getField('SORT') - (int)$b->getField('SORT');
		});

		/** @var BasketItemBase $item */
		foreach ($sortedCollection as $item)
		{
			if (!$item->canBuy() || $item->isDelay())
				continue;

			$item->setCollection($basket);
			$basket->addItem($item);
		}

		return $basket;
	}

	/**
	 * @return BasketItemCollection
	 */
	public function getBasket()
	{
		return $this;
	}
}
