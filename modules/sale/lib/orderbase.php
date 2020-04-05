<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Currency;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

abstract class OrderBase
	extends Internals\Entity
{
	/** @var Internals\Fields */
	protected $calculatedFields = null;

	/** @var Basket */
	protected $basketCollection;

	/** @var ShipmentCollection */
	protected $shipmentCollection;

	/** @var PaymentCollection */
	protected $paymentCollection;

	/** @var PropertyValueCollection */
	protected $propertyCollection;

	/** @var Tax $tax */
	protected $tax = null;

	/** @var int */
	protected $internalId = 0;


	/** @var null|string $calculateType */
	protected $calculateType = null;

	const SALE_ORDER_CALC_TYPE_NEW = 'N';
	const SALE_ORDER_CALC_TYPE_CHANGE = 'C';
	const SALE_ORDER_CALC_TYPE_REFRESH = 'R';


	protected static $mapFields = array();

	public function getInternalId()
	{
		static $idPool = 0;
		if ($this->internalId == 0)
		{
			$idPool++;
			$this->internalId = $idPool;
		}
		return $this->internalId;
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		$result = array(
			"LID", "PERSON_TYPE_ID", "CANCELED", "DATE_CANCELED",
			"EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID",  "DEDUCTED",
			"MARKED", "DATE_MARKED", "EMP_MARKED_ID", "REASON_MARKED",
			"PRICE", "CURRENCY", "DISCOUNT_VALUE", "USER_ID",
			"DATE_INSERT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "COMMENTS", "TAX_VALUE",
			"STAT_GID", "RECURRING_ID", "LOCKED_BY", "IS_RECURRING",
			"DATE_LOCK", "RECOUNT_FLAG", "AFFILIATE_ID", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE", "UPDATED_1C",
			"STORE_ID", "ORDER_TOPIC", "RESPONSIBLE_ID", "DATE_BILL", "DATE_PAY_BEFORE", "ACCOUNT_NUMBER",
			"XML_ID", "ID_1C", "VERSION_1C", "VERSION", "EXTERNAL_ORDER", "COMPANY_ID"
		);

		return array_merge($result, static::getCalculatedFields());
	}


	/**
	 * @return array
	 */
	public static function getCalculatedFields()
	{
		return array(
			'PRICE_WITHOUT_DISCOUNT',
			'ORDER_WEIGHT',
			'DISCOUNT_PRICE',
			'BASE_PRICE_DELIVERY',

			'DELIVERY_LOCATION',
			'DELIVERY_LOCATION_ZIP',
			'TAX_LOCATION',
			'TAX_PRICE',

			'VAT_RATE',
			'VAT_VALUE',
			'VAT_SUM',
			'VAT_DELIVERY',
			'USE_VAT',
		);
	}

	/**
	 * @return array
	 */
	public static function getMeaningfulFields()
	{
		return array('PERSON_TYPE_ID', 'PRICE');
	}

	/**
	 * @return array
	 */
	public static function getAllFields()
	{
		if (empty(static::$mapFields))
		{
			static::$mapFields = Internals\CollectableEntity::getAllFieldsByMap(Internals\OrderTable::getMap());
		}
		return static::$mapFields;
	}

	/**
	 * @param array $fields
	 * @throws Main\NotImplementedException
	 * @return Order
	 */
	protected static function createOrderObject(array $fields = array())
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param string $siteId
	 * @param int $userId
	 * @param string $currency
	 * @return static
	 */
	public static function create($siteId, $userId = null, $currency = null)
	{
		$order = static::createOrderObject();
		$order->setFieldNoDemand('LID', $siteId);
		if (intval($userId) > 0)
			$order->setFieldNoDemand('USER_ID', $userId);

		if ($currency == null)
		{
			$currency = Internals\SiteCurrencyTable::getSiteCurrency($siteId);
		}

		if ($currency == null)
		{
			$currency = Currency\CurrencyManager::getBaseCurrency();
		}

		$order->setFieldNoDemand('CURRENCY', $currency);

		$order->calculateType = static::SALE_ORDER_CALC_TYPE_NEW;

		return $order;
	}

	/**
	 * @param $id
	 * @return null|static
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	public static function load($id)
	{
		if (intval($id) <= 0)
			throw new Main\ArgumentNullException("id");

		$filter = array(
			'filter' => array('ID' => $id),
			'select' => array('*'),
		);

		$list = static::loadByFilter($filter);
		if (!empty($list) && is_array($list))
		{
			return reset($list);
		}

		return null;
	}

	/**
	 * @param array $filter
	 * @return array|false
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	public static function loadByFilter(array $filter)
	{
		$list = array();

		/** @var Main\DB\Result $res */
		$res = static::loadFromDb($filter);
		while($orderData = $res->fetch())
		{
			$order = static::createOrderObject($orderData);

			$order->calculateType = static::SALE_ORDER_CALC_TYPE_CHANGE;
			$list[] = $order;
		}

		return (!empty($list) ? $list : null);
	}

	/**
	 * @param array $filter
	 * @return Main\DB\Result
	 * @throws Main\NotImplementedException
	 */
	static protected function loadFromDb(array $filter)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	//abstract protected function loadFromDb($id);

	/**
	 * @param Basket $basket
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function setBasket(Basket $basket)
	{
		if ($this->getId())
		{
			throw new Main\NotSupportedException();
		}

		$result = new Result();

		$basket->setOrder($this);
		$this->basketCollection = $basket;

		if (!$this->isMathActionOnly())
		{
			/** @var Result $r */
			$r = $basket->refreshData(array('PRICE', 'QUANTITY', 'COUPONS'));
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		return $result;
	}

	/**
	 * @param BasketBase $basket
	 *
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function appendBasket(BasketBase $basket)
	{
		if ($this->getId())
		{
			throw new Main\NotSupportedException();
		}

		$basket->setOrder($this);
		$this->basketCollection = $basket;

		return new Result();
	}

	/**
	 * Return order basket.
	 *
	 * @return Basket
	 */
	public function getBasket()
	{
		if (!isset($this->basketCollection) || empty($this->basketCollection))
			$this->basketCollection = $this->loadBasket();
		return $this->basketCollection;
	}

	/**
	 * Return basket exists.
	 *
	 * @return bool
	 */
	public function isNotEmptyBasket()
	{
		if (!isset($this->basketCollection) || empty($this->basketCollection))
			$this->basketCollection = $this->loadBasket();
		return !empty($this->basketCollection);
	}

	/**
	 *
	 */
	abstract protected function loadBasket();


	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	public function setField($name, $value)
	{
		$priceRoundedFields = array(
			'PRICE' => 'PRICE',
			'PRICE_DELIVERY' => 'PRICE_DELIVERY',
			'SUM_PAID' => 'SUM_PAID',
			'PRICE_PAYMENT' => 'PRICE_PAYMENT',
			'DISCOUNT_VALUE' => 'DISCOUNT_VALUE',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
			return new Result();
		}

		$fields = $this->fields->getChangedValues();
		if (!array_key_exists("UPDATED_1C", $fields))
			parent::setField("UPDATED_1C", "N");

		return parent::setField($name, $value);
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldNoDemand($name, $value)
	{
		$priceRoundedFields = array(
			'PRICE' => 'PRICE',
			'PRICE_DELIVERY' => 'PRICE_DELIVERY',
			'SUM_PAID' => 'SUM_PAID',
			'PRICE_PAYMENT' => 'PRICE_PAYMENT',
			'DISCOUNT_VALUE' => 'DISCOUNT_VALUE',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
			return;
		}

		$fields = $this->fields->getChangedValues();
		if (!array_key_exists("UPDATED_1C", $fields))
			parent::setField("UPDATED_1C", "N");

		parent::setFieldNoDemand($name, $value);
	}

	/**
	 * @param $name
	 * @return null|string
	 */
	public function getField($name)
	{
		if ($this->isCalculatedField($name))
		{
			return $this->calculatedFields->get($name);
		}

		return parent::getField($name);
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @return Result|void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function initField($name, $value)
	{
		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
			return new Result();
		}

		parent::initField($name, $value);
	}

//	/**
//	 * @internal
//	 *
//	 * @param array $values
//	 * @return Result|void
//	 * @throws Main\ArgumentOutOfRangeException
//	 */
//	public function setFieldsNoDemand(array $values)
//	{
//		foreach($values as $name => $value)
//		{
//			$this->setFieldNoDemand($name, $value);
//		}
//	}

	/**
	 * @return PropertyValueCollection
	 */
	public function getPropertyCollection()
	{
		if(empty($this->propertyCollection))
		{
			$this->propertyCollection = $this->loadPropertyCollection();
		}

		return $this->propertyCollection;
	}

	abstract public function loadPropertyCollection();

	/**
	 * Full refresh order data.
	 *
	 * @param array $select
	 * @return Result
	 */
	public function refreshData($select = array())
	{
		return new Result();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return intval($this->getField('ID'));
	}

	/**
	 * @return int
	 */
	public function getPersonTypeId()
	{
		return $this->getField('PERSON_TYPE_ID');
	}

	/**
	 * @param $personTypeId
	 *
	 * @return bool|void
	 */
	public function setPersonTypeId($personTypeId)
	{
		return $this->setField('PERSON_TYPE_ID', intval($personTypeId));
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return floatval($this->getField('PRICE'));
	}

	/**
	 * @return float
	 */
	public function getSumPaid()
	{
		return floatval($this->getField('SUM_PAID'));
	}

	/**
	 * @return float
	 */
	public function getDeliveryPrice()
	{
		return floatval($this->getField('PRICE_DELIVERY'));
	}


	/**
	 * @return float
	 */
	public function getDeliveryLocation()
	{
		return $this->getField('DELIVERY_LOCATION');
	}


	/**
	 * @return float
	 */
	public function getTaxPrice()
	{
		return floatval($this->getField('TAX_PRICE'));
	}

	/**
	 * @return float
	 */
	public function getTaxValue()
	{
		return floatval($this->getField('TAX_VALUE'));
	}

	/**
	 * @return float
	 */
	public function getDiscountPrice()
	{
		return floatval($this->getField('DISCOUNT_PRICE'));
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->getField('CURRENCY');
	}


	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->getField('USER_ID');
	}


	/**
	 * @return null|string
	 */
	public function getSiteId()
	{
		return $this->getField('LID');
	}

	/**
	 * @return bool
	 */
	public function isUsedVat()
	{
		$useVat = $this->getField('USE_VAT');
		if ($useVat === null)
		{
			$this->refreshVat();
		}

		return $this->getField('USE_VAT') === 'Y';
	}

	/**
	 * @return mixed|null
	 */
	public function getVatRate()
	{
		$vatRate = $this->getField('VAT_RATE');
		if ($vatRate === null && $this->getId() > 0)
		{
			$this->refreshVat();
			return $this->getField('VAT_RATE');
		}
		return $vatRate;
	}

	/**
	 * @return float
	 */
	public function getVatSum()
	{
		$vatSum = $this->getField('VAT_SUM');
		if ($vatSum === null && $this->getId() > 0)
		{
			$this->refreshVat();
			return $this->getField('VAT_SUM');
		}
		return $vatSum;
	}

	/**
	 * @return null|string
	 */
	public function isMarked()
	{
		return ($this->getField('MARKED') == "Y");
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function resetVat()
	{
		$this->setFieldNoDemand('USE_VAT', 'N');
		$this->setFieldNoDemand('VAT_RATE', 0);

		$this->setFieldNoDemand('VAT_SUM', 0);
		$this->setFieldNoDemand('VAT_DELIVERY', 0);
	}

	/**
	 * @internal
	 */
	public function refreshVat()
	{
		$result = new Result();

		if (($basket = $this->getBasket()) && count($basket) > 0)
		{
			$this->resetVat();

			$vatRate = $basket->getVatRate();
			$isUsedVat = ($vatRate > 0) ? 'Y' : 'N';
			$vatSum = $basket->getVatSum();

			$shipmentCollection = $this->shipmentCollection;
			if ($shipmentCollection)
			{
				/** @var Shipment $shipment */
				foreach ($shipmentCollection as $shipment)
				{
					$shipmentVatRate = $shipment->getVatRate();
					if ($shipmentVatRate)
					{
						$isUsedVat = 'Y';
						$vatSum += $shipment->getVatSum();
						$vatRate = max($vatRate, $shipmentVatRate);
					}
				}
			}

			if ($isUsedVat === 'Y')
			{
				/** @var Result $r */
				$r = $this->setField('USE_VAT', 'Y');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}

				/** @var Result $r */
				$r = $this->setField('VAT_RATE', $vatRate);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}

				/** @var Result $r */
				$r = $this->setField('VAT_SUM', $vatSum);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function isShipped()
	{
		return $this->getField('DEDUCTED');
	}

	/**
	 * @return bool
	 */
	public function isExternal()
	{
		return ($this->getField('EXTERNAL_ORDER') == "Y");
	}



	/**
	 * @param $field
	 * @return bool
	 */
	protected function isCalculatedField($field)
	{
		if ($this->calculatedFields == null )
		{
			$this->calculatedFields = new Internals\Fields();
		}

		return (in_array($field, $this->getCalculatedFields()));
	}

	/**
	 * @return Entity\AddResult|Entity\UpdateResult|mixed
	 */
	abstract public function save();

	
	/**
	 * @param $price
	 */
	public function setVatSum($price)
	{
		$this->setField('VAT_SUM', $price);
	}

	/**
	 * @param $price
	 */
	public function setVatDelivery($price)
	{
		$this->setField('VAT_DELIVERY', $price);
	}


	/**
	 * @return Main\Type\DateTime
	 */
	public function getDateInsert()
	{
		return $this->getField('DATE_INSERT');
	}

	/**
	 * @return null|string
	 */
	public function getCalculateType()
	{
		return $this->calculateType;
	}

	/**
	 * @param string $event
	 * @return array
	 */
	public static function getEventListUsed($event)
	{
		return array();
	}

	/**
	 * @param $action
	 * @param BasketItemBase $basketItem
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 */
	public function onBasketModify($action, BasketItemBase $basketItem, $name = null, $oldValue = null, $value = null)
	{
		return new Result();
	}


	/**
	 * @internal
	 * @return Result
	 */
	public function onBeforeBasketRefresh()
	{
		return new Result();
	}


	/**
	 * @internal
	 * @return Result
	 */
	public function onAfterBasketRefresh()
	{
		return new Result();
	}

	/**
	 * @param string $reasonMarked
	 * @return Result
	 */
	public function addMarker($reasonMarked)
	{
		return new Result();
	}

	/**
	 * Get the entity of taxes
	 *
	 * @return Tax
	 */
	public function getTax()
	{
		if ($this->tax === null)
		{
			$this->tax = $this->loadTax();
		}
		return $this->tax;
	}

	/**
	 * @return Tax
	 */
	abstract protected function loadTax();
}