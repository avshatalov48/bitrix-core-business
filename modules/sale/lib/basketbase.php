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

	/** @var bool $isLoadForFUserId */
	private $isLoadForFUserId = false;

	/** @var bool $isSaveExecuting */
	protected $isSaveExecuting = false;

	/**
	 * @param $code
	 * @return BasketItemBase|null
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentNullException
	 */
	public function getItemByBasketCode($code)
	{
		if (
			isset($this->basketItemIndexMap[$code])
			&& isset($this->collection[$this->basketItemIndexMap[$code]])
		)
		{
			return $this->collection[$this->basketItemIndexMap[$code]];
		}

		return parent::getItemByBasketCode($code);
	}

	/**
	 * @return OrderBase|null
	 */
	protected function getEntityParent()
	{
		return $this->getOrder();
	}

	/**
	 * @return BasketBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	private static function createBasketObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$basketClassName = $registry->getBasketClassName();

		return new $basketClassName;
	}

	/**
	 * @param $fUserId
	 * @param $siteId
	 * @return BasketBase
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 */
	public static function loadItemsForFUser($fUserId, $siteId)
	{
		/** @var BasketBase $basket */
		$basket = static::create($siteId);

		$basket->setFUserId($fUserId);

		$basket->isLoadForFUserId = true;

		/** @var BasketBase $collection */
		return $basket->loadFromDb([
			"=FUSER_ID" => $fUserId,
			"=LID" => $siteId,
			"ORDER_ID" => null
		]);
	}

	/**
	 * @param array $filter
	 * @return BasketBase
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 */
	protected function loadFromDb(array $filter)
	{
		$select = [
			"ID", "LID", "MODULE", "PRODUCT_ID", "QUANTITY", "WEIGHT",
			"DELAY", "CAN_BUY", "PRICE", "CUSTOM_PRICE", "BASE_PRICE",
			'PRODUCT_PRICE_ID', 'PRICE_TYPE_ID', "CURRENCY", 'BARCODE_MULTI',
			"RESERVED", "RESERVE_QUANTITY",	"NAME", "CATALOG_XML_ID",
			"VAT_RATE", "NOTES", "DISCOUNT_PRICE","PRODUCT_PROVIDER_CLASS",
			"CALLBACK_FUNC", "ORDER_CALLBACK_FUNC", "PAY_CALLBACK_FUNC",
			"CANCEL_CALLBACK_FUNC", "DIMENSIONS", "TYPE", "SET_PARENT_ID",
			"DETAIL_PAGE_URL", "FUSER_ID", 'MEASURE_CODE', 'MEASURE_NAME',
			'ORDER_ID', 'DATE_INSERT', 'DATE_UPDATE', 'PRODUCT_XML_ID',
			'SUBSCRIBE', 'RECOMMENDATION', 'VAT_INCLUDED', 'SORT',
			'DATE_REFRESH', 'DISCOUNT_NAME', 'DISCOUNT_VALUE', 'DISCOUNT_COUPON',
			'XML_ID', 'MARKING_CODE_GROUP'
		];

		$itemList = [];
		$first = true;

		$res = static::getList([
			"select" => $select,
			"filter" => $filter,
			"order" => ['SORT' => 'ASC', 'ID' => 'ASC'],
		]);
		while ($item = $res->fetch())
		{
			if ($first)
			{
				$this->setSiteId($item['LID']);
				$this->setFUserId($item['FUSER_ID']);
				$first = false;
			}

			$itemList[$item['ID']] = $item;
		}

		foreach ($itemList as $id => $item)
		{
			if ($item['SET_PARENT_ID'] > 0)
			{
				$itemList[$item['SET_PARENT_ID']]['ITEMS'][$id] = &$itemList[$id];
			}
		}

		$result = [];
		foreach ($itemList as $id => $item)
		{
			if ($item['SET_PARENT_ID'] == 0)
			{
				$result[$id] = $item;
			}
		}

		$this->loadFromArray($result);

		return $this;
	}

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
	 * @return OrderBase|null
	 */
	public function getOrder()
	{
		return $this->order;
	}


	/**
	 * @param BasketItemBase $item
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
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
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
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
	 * @throws Main\ArgumentNullException
	 */
	public function getPrice()
	{
		$orderPrice = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$orderPrice += $basketItem->getFinalPrice();
		}

		return $orderPrice;
	}

	/**
	 * Getting basket price without discounts
	 *
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getBasePrice()
	{
		$orderPrice = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$orderPrice += PriceMaths::roundPrecision($basketItem->getBasePriceWithVat() * $basketItem->getQuantity());
		}

		return $orderPrice;
	}

	/**
	 * Getting the value of the tax basket
	 *
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getVatSum()
	{
		$vatSum = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			// BasketItem that is removed is not involved
			if ($basketItem->getQuantity() == 0)
			{
				continue;
			}

			$vatSum += $basketItem->getVat();
		}

		return $vatSum;
	}

	/**
	 * Getting the value of the tax rate basket
	 *
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getVatRate()
	{
		$vatRate = 0;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			// BasketItem that is removed is not involved
			if ($basketItem->getQuantity() == 0)
			{
				continue;
			}

			if ($basketItem->getVatRate() > $vatRate)
			{
				$vatRate = $basketItem->getVatRate();
			}
		}

		return $vatRate;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
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
					$registry = Registry::getInstance(static::getRegistryType());

					/** @var EntityMarker $entityMarker */
					$entityMarker = $registry->getEntityMarkerClassName();
					$entityMarker::addMarker($order, $basketItem, $r);
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
	private function getOriginalItemsValues()
	{
		$result = array();

		/** @var Order $order */
		$order = $this->getOrder();
		$isNew = $order && $order->isNew();

		$filter = array();
		if (!$isNew && $order && $order->getId() > 0)
		{
			$filter['ORDER_ID'] = $order->getId();
		}
		else
		{
			if ($this->isLoadForFUserId)
			{
				$filter = array(
					'=FUSER_ID' => $this->getFUserId(),
					'ORDER_ID' => null,
					'=LID' => $this->getSiteId()
				);
			}

			if ($isNew)
			{
				$fUserId = $this->getFUserId(true);
				if ($fUserId <= 0)
				{
					$userId = $order->getUserId();
					if (intval($userId) > 0)
					{
						$fUserId = Fuser::getIdByUserId($userId);
						if ($fUserId > 0)
							$this->setFUserId($fUserId);
					}
				}
			}
		}

		if ($filter)
		{
			$dbRes = static::getList(
				array(
					"select" => array("ID", 'TYPE', 'SET_PARENT_ID', 'PRODUCT_ID', 'NAME', 'QUANTITY', 'FUSER_ID', 'ORDER_ID'),
					"filter" => $filter,
				)
			);

			while ($item = $dbRes->fetch())
			{
				if ((int)$item['SET_PARENT_ID'] > 0 && (int)$item['SET_PARENT_ID'] != $item['ID'])
				{
					continue;
				}

				$result[$item["ID"]] = $item;
			}
		}

		return $result;
	}

	/**
	 * @param array $itemValues
	 * @return Result
	 */
	abstract protected function deleteInternal(array $itemValues);

	/**
	 * Save basket
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function save()
	{
		$this->checkCallingContext();

		$result = new Result();

		$this->isSaveExecuting = true;

		/** @var OrderBase $order */
		$order = $this->getOrder();
		if (!$order)
		{
			$r = $this->verify();
			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}

			$r = $this->callEventOnSaleBasketBeforeSaved();
			if (!$r->isSuccess())
			{
				$this->isSaveExecuting = false;

				return $result->addErrors($r->getErrors());
			}
		}

		$originalItemsValues = $this->getOriginalItemsValues();

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$r = $basketItem->save();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if (isset($originalItemsValues[$basketItem->getId()]))
			{
				unset($originalItemsValues[$basketItem->getId()]);
			}
		}

		if ($originalItemsValues)
		{
			foreach ($originalItemsValues as $itemValues)
			{
				$this->callEventOnBeforeSaleBasketItemDeleted($itemValues);

				$this->deleteInternal($itemValues);

				$this->callEventOnSaleBasketItemDeleted($itemValues);
			}
		}

		if (!$order)
		{
			$r = $this->callEventOnSaleBasketSaved();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		$this->clearChanged();

		$this->isSaveExecuting = false;

		return $result;
	}

	/**
	 * @return void
	 */
	private function checkCallingContext() : void
	{
		$order = $this->getOrder();

		if (
			$order
			&& !$order->isSaveRunning()
		)
		{
			trigger_error("Incorrect call to the save process. Use method save() on \Bitrix\Sale\Order entity.", E_USER_WARNING);
		}
	}

	/**
	 * @param $itemValues
	 * @return void
	 */
	private function callEventOnBeforeSaleBasketItemDeleted($itemValues)
	{
		$itemValues['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		$event = new Main\Event('sale', "OnBeforeSaleBasketItemDeleted", array('VALUES' => $itemValues));
		$event->send();
	}

	/**
	 * @param $itemValues
	 * @return void
	 */
	protected function callEventOnSaleBasketItemDeleted($itemValues)
	{
		$itemValues['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		$event = new Main\Event('sale', "OnSaleBasketItemDeleted", array('VALUES' => $itemValues));
		$event->send();
	}

	/**
	 * @return Result
	 */
	protected function callEventOnSaleBasketBeforeSaved()
	{
		$result = new Result();

		/** @var Main\Entity\Event $event */
		$event = new Main\Event(
			'sale',
			EventActions::EVENT_ON_BASKET_BEFORE_SAVED,
			array('ENTITY' => $this)
		);
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(
						Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_BASKET_SAVED'),
						'SALE_EVENT_ON_BEFORE_BASKET_SAVED'
					);
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

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function callEventOnSaleBasketSaved()
	{
		$result = new Result();

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
					$errorMsg = new ResultError(
						Main\Localization\Loc::getMessage('SALE_EVENT_ON_BASKET_SAVED'),
							'SALE_EVENT_ON_BASKET_SAVED'
					);
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
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
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
	 * @return BasketBase
	 * @throws Main\ArgumentNullException
	 */
	public function getOrderableItems()
	{
		/** @var BasketBase $basket */
		$basket = static::create($this->getSiteId());

		if ($this->isLoadForFUserId)
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
	 * @return BasketBase
	 */
	public function getBasket()
	{
		return $this;
	}

	/**
	 * @param $idOrder
	 * @throws Main\NotImplementedException
	 * @return Result
	 */
	public static function deleteNoDemand($idOrder)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @return bool
	 */
	public function isSaveRunning()
	{
		return $this->isSaveExecuting;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 */
	public function getContext()
	{
		$context = array();

		$order = $this->getOrder();
		/** @var OrderBase $order */
		if ($order)
		{
			$context['USER_ID'] = $order->getUserId();
			$context['SITE_ID'] = $order->getSiteId();
			$context['CURRENCY'] = $order->getCurrency();
		}
		else
		{
			$context = parent::getContext();
		}

		return $context;
	}

	/**
	 * Getting a list of a count of elements in the basket
	 *
	 * @return array
	 * @throws Main\ArgumentNullException
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
	 *
	 * @param $index
	 * @return mixed
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function deleteItem($index)
	{
		$oldItem = parent::deleteItem($index);

		unset($this->basketItemIndexMap[$oldItem->getBasketCode()]);

		/** @var OrderBase $order */
		if ($order = $this->getOrder())
		{
			$order->onBasketModify(EventActions::DELETE, $oldItem);
		}

		return $oldItem;
	}

	/**
	 * Apply the result of the discounts to the basket.
	 * @internal
	 *
	 * @param array $basketRows
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function applyDiscount(array $basketRows)
	{
		$result = new Result();

		if ($this->count() == 0 || empty($basketRows))
			return $result;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			if ($basketItem->isCustomPrice())
				continue;
			$basketCode = $basketItem->getBasketCode();
			if (!isset($basketRows[$basketCode]))
				continue;

			$fields = $basketRows[$basketCode];

			if (isset($fields['PRICE']) && isset($fields['DISCOUNT_PRICE']))
			{
				$fields['PRICE'] = (float)$fields['PRICE'];
				$fields['DISCOUNT_PRICE'] = (float)$fields['DISCOUNT_PRICE'];

				if ($fields['PRICE'] >= 0
					&& $basketItem->getPrice() != $fields['PRICE'])
				{
					$fields['PRICE'] = PriceMaths::roundPrecision($fields['PRICE']);
					$basketItem->setFieldNoDemand('PRICE', $fields['PRICE']);
				}

				if ($basketItem->getDiscountPrice() != $fields['DISCOUNT_PRICE'])
				{
					$fields['DISCOUNT_PRICE'] = PriceMaths::roundPrecision($fields['DISCOUNT_PRICE']);
					$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $fields['DISCOUNT_PRICE']);
				}

				if (isset($fields['DISCOUNT_VALUE']))
					$basketItem->setFieldNoDemand('DISCOUNT_VALUE', $fields['DISCOUNT_VALUE']);
			}
		}
		unset($fields, $basketCode, $basketItem);

		return $result;
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
	 * @internal
	 *
	 * Load the contents of the basket to order
	 *
	 * @param OrderBase $order
	 * @return BasketBase
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 */
	public static function loadItemsForOrder(OrderBase $order)
	{
		$basket = static::createBasketObject();
		$basket->setOrder($order);
		$basket->setSiteId($order->getSiteId());

		return $basket->loadFromDb(array("=ORDER_ID" => $order->getId()));
	}

	/**
	 * @internal
	 *
	 * @param Internals\CollectableEntity $basketItem
	 * @return Internals\CollectableEntity|void
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
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

	/**
	 * @deprecated Use \Bitrix\Sale\BasketBase::refresh instead
	 *
	 * @param array $select
	 * @param BasketItemBase|null $refreshItem
	 * @return Result
	 * @throws Main\ArgumentNullException
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
	 * @deprecated the basket can contain duplicate items
	 *
	 * @param BasketItemBase $item
	 * @return BasketItemBase|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
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
}
