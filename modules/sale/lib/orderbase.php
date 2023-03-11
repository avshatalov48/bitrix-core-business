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
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

/**
 * Class OrderBase
 * @package Bitrix\Sale
 */
abstract class OrderBase extends Internals\Entity
{
	/** @var Internals\Fields */
	protected $calculatedFields = null;

	/** @var BasketBase */
	protected $basketCollection;

	/** @var PropertyValueCollectionBase */
	protected $propertyCollection;

	/** @var Discount $discount */
	protected $discount = null;

	/** @var Tax $tax */
	protected $tax = null;

	/** @var int */
	protected $internalId = 0;

	/** @var bool $isNew */
	protected $isNew = true;

	/** @var bool  */
	protected $isSaveExecuting = false;

	/** @var bool $isClone */
	protected $isClone = false;

	/** @var bool $isOnlyMathAction */
	protected $isOnlyMathAction = null;

	/** @var bool $isMeaningfulField */
	protected $isMeaningfulField = false;

	/** @var bool $isStartField */
	protected $isStartField = null;


	/** @var null|string $calculateType */
	protected $calculateType = null;

	const SALE_ORDER_CALC_TYPE_NEW = 'N';
	const SALE_ORDER_CALC_TYPE_CHANGE = 'C';
	const SALE_ORDER_CALC_TYPE_REFRESH = 'R';

	/**
	 * OrderBase constructor.
	 * @param array $fields
	 * @throws Main\ArgumentNullException
	 */
	protected function __construct(array $fields = array())
	{
		parent::__construct($fields);

		$this->isNew = (empty($fields['ID']));
	}

	/**
	 * Return internal index of order
	 *
	 * @return int
	 */
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
	 * Return field names that can set in \Bitrix\Sale\OrderBase::setField
	 *
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
			"XML_ID", "ID_1C", "VERSION_1C", "VERSION", "EXTERNAL_ORDER", "COMPANY_ID", "IS_SYNC_B24"
		);

		return array_merge($result, static::getCalculatedFields());
	}

	/**
	 * Return virtual field names
	 *
	 * @return array
	 */
	protected static function getCalculatedFields()
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
	 * @return bool
	 */
	public function isSaveRunning()
	{
		return $this->isSaveExecuting;
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array('PERSON_TYPE_ID', 'PRICE');
	}

	/**
	 * @param array $fields
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	private static function createOrderObject(array $fields = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$orderClassName = $registry->getOrderClassName();

		return new $orderClassName($fields);
	}

	/**
	 * @return string
	 */
	public static function getRegistryEntity()
	{
		return Registry::ENTITY_ORDER;
	}

	/**
	 * Create \Bitrix\Sale\OrderBase object
	 *
	 * @param $siteId
	 * @param null $userId
	 * @param null $currency
	 * @return Order
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	public static function create($siteId, $userId = null, $currency = null)
	{
		$fields = [
			'CANCELED' => 'N',
			'DEDUCTED' => 'N',
			'ALLOW_DELIVERY' => 'N',
			'PAYED' => 'N',
		];

		$order = static::createOrderObject($fields);
		$order->setFieldNoDemand('LID', $siteId);
		if (intval($userId) > 0)
		{
			$order->setFieldNoDemand('USER_ID', $userId);
		}

		if ($currency == null)
		{
			$currency = Internals\SiteCurrencyTable::getSiteCurrency($siteId);
		}

		if ($currency == null)
		{
			$currency = Currency\CurrencyManager::getBaseCurrency();
		}

		$order->setFieldNoDemand('CURRENCY', $currency);
		$order->setField('STATUS_ID', static::getInitialStatus());
		$order->setFieldNoDemand('XML_ID', static::generateXmlId());

		$order->calculateType = static::SALE_ORDER_CALC_TYPE_NEW;

		return $order;
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * Load order object by id
	 *
	 * @param int $id
	 * @return null|static
	 * @throws Main\ArgumentNullException
	 */
	public static function load($id)
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			throw new Main\ArgumentNullException("id");
		}

		$filter = [
			'filter' => ['ID' => $id],
			'select' => ['*'],
		];

		$list = static::loadByFilter($filter);
		if (!empty($list) && is_array($list))
		{
			return reset($list);
		}

		return null;
	}

	/**
	 * Return object order list satisfying filter
	 *
	 * @param array $parameters
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function loadByFilter(array $parameters)
	{
		$list = [];

		$parameters = static::prepareParams($parameters);

		/** @var Main\DB\Result $res */
		$res = static::loadFromDb($parameters);
		while($orderData = $res->fetch())
		{
			$order = static::createOrderObject($orderData);

			$order->calculateType = static::SALE_ORDER_CALC_TYPE_CHANGE;
			$list[] = $order;
		}

		return $list;
	}

	/**
	 * @param $parameters
	 * @return array
	 */
	private static function prepareParams($parameters)
	{
		$result = array(
			'select' => array('*')
		);

		if (isset($parameters['filter']))
		{
			$result['filter'] = $parameters['filter'];
		}
		if (isset($parameters['limit']))
		{
			$result['limit'] = $parameters['limit'];
		}
		if (isset($parameters['order']))
		{
			$result['order'] = $parameters['order'];
		}
		if (isset($parameters['offset']))
		{
			$result['offset'] = $parameters['offset'];
		}
		if (isset($parameters['runtime']))
		{
			$result['runtime'] = $parameters['runtime'];
		}

		return $result;
	}

	/**
	 * Load object order by account number
	 *
	 * @param $value
	 * @return mixed|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	public static function loadByAccountNumber($value)
	{
		if (trim($value) == '')
		{
			throw new Main\ArgumentNullException("value");
		}

		$parameters = [
			'filter' => ['=ACCOUNT_NUMBER' => $value],
			'select' => ['*'],
		];

		$list = static::loadByFilter($parameters);

		return reset($list);
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 * @throws Main\NotImplementedException
	 */
	static protected function loadFromDb(array $parameters)
	{
		return static::getList($parameters);
	}

	/**
	 * Append basket to order and refresh it
	 *
	 * @param BasketBase $basket
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function setBasket(BasketBase $basket)
	{
		$result = self::appendBasket($basket);

		if (!$this->isMathActionOnly())
		{
			/** @var Result $r */
			$r = $basket->refreshData(['PRICE', 'QUANTITY', 'COUPONS']);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Append basket to order
	 *
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
	 * @return BasketBase
	 */
	public function getBasket()
	{
		if (!isset($this->basketCollection) || empty($this->basketCollection))
		{
			$this->basketCollection = $this->loadBasket();
		}

		return $this->basketCollection;
	}

	/**
	 * Load basket appended to order
	 *
	 * @return BasketBase|null
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected function loadBasket()
	{
		if ($this->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());
			/** @var BasketBase $basketClassName */
			$basketClassName = $registry->getBasketClassName();

			return $basketClassName::loadItemsForOrder($this);
		}

		return null;
	}

	/**
	 * Set value with call events on field modify
	 *
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setField($name, $value)
	{
		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
			return new Result();
		}

		return parent::setField($name, $value);
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\NotImplementedException
	 */
	protected function checkValueBeforeSet($name, $value)
	{
		$result = parent::checkValueBeforeSet($name, $value);

		if ($name === 'ACCOUNT_NUMBER')
		{
			$dbRes = static::getList([
				'select' => ['ID'],
				'filter' => ['=ACCOUNT_NUMBER' => $value]
			]);

			if ($dbRes->fetch())
			{
				$result->addError(
					new ResultError(
						Loc::getMessage('SALE_ORDER_ACCOUNT_NUMBER_EXISTS')
					)
				);
			}
		}

		return $result;
	}

	protected function normalizeValue($name, $value)
	{
		if ($this->isPriceField($name))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		return parent::normalizeValue($name, $value);
	}

	/**
	 * @internal
	 * Set value without call events on field modify
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setFieldNoDemand($name, $value)
	{
		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
			return;
		}

		if (!$this->fields->isChanged("UPDATED_1C") && $name != 'UPDATED_1C')
		{
			$this->setField("UPDATED_1C", "N");
		}

		if ($this->isSaveExecuting === false)
		{
			if ($name === 'ID')
			{
				$this->isNew = false;
			}
		}

		parent::setFieldNoDemand($name, $value);
	}

	/**
	 * Return field value
	 *
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
	 * Init field
	 *
	 * @param $name
	 * @param $value
	 * @return void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function initField($name, $value)
	{
		if ($this->isCalculatedField($name))
		{
			$this->calculatedFields->set($name, $value);
		}

		if ($name === 'ID')
		{
			$this->isNew = false;
		}

		parent::initField($name, $value);
	}

	/**
	 * @return PropertyValueCollectionBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPropertyCollection()
	{
		if(empty($this->propertyCollection))
		{
			$this->propertyCollection = $this->loadPropertyCollection();
		}

		return $this->propertyCollection;
	}

	/**
	 * @return PropertyValueCollectionBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function loadPropertyCollection()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		/** @var PropertyValueCollectionBase $propertyCollectionClassName */
		$propertyCollectionClassName = $registry->getPropertyValueCollectionClassName();

		return $propertyCollectionClassName::load($this);
	}

	/**
	 * @internal
	 *
	 * @param string $action Action.
	 * @param EntityPropertyValue $property Property.
	 * @param null|string $name Field name.
	 * @param null|string|int|float $oldValue Old value.
	 * @param null|string|int|float $value New value.
	 * @return Result
	 */
	public function onPropertyValueCollectionModify($action, EntityPropertyValue $property, $name = null, $oldValue = null, $value = null)
	{
		return new Result();
	}

	/**
	 * Full order refresh
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 */
	public function refreshData()
	{
		$result = new Result();

		$isStartField = $this->isStartField();

		$this->calculateType = ($this->getId() > 0 ? static::SALE_ORDER_CALC_TYPE_REFRESH : static::SALE_ORDER_CALC_TYPE_NEW);

		$this->resetData();

		$this->refreshInternal();

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
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function refreshInternal()
	{
		$result = new Result();

		/** @var Basket $basket */
		$basket = $this->getBasket();
		if (!$basket)
		{
			return $result;
		}

		/** @var Result $r */
		$r = $this->setField('PRICE', $basket->getPrice());
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		return $result;
	}

	/**
	 * Return person type id of order
	 *
	 * @return int
	 */
	public function getPersonTypeId()
	{
		return $this->getField('PERSON_TYPE_ID');
	}

	/**
	 * Set person type id of order
	 *
	 * @param $personTypeId
	 *
	 * @return Result
	 */
	public function setPersonTypeId($personTypeId)
	{
		return $this->setField('PERSON_TYPE_ID', intval($personTypeId));
	}

	/**
	 * Return order price
	 *
	 * @return float
	 */
	public function getPrice()
	{
		return floatval($this->getField('PRICE'));
	}

	/**
	 * Returns order price without discounts.
	 *
	 * @return float
	 */
	public function getBasePrice(): float
	{
		$basket = $this->getBasket();
		$taxPrice = !$this->isUsedVat() ? $this->getField('TAX_PRICE') : 0;

		return $basket->getBasePrice() + $taxPrice;
	}

	/**
	 * Return paid sum
	 *
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
	 * Return discount price
	 *
	 * @return float
	 */
	public function getDiscountPrice()
	{
		return floatval($this->getField('DISCOUNT_PRICE'));
	}

	/**
	 * Return currency
	 *
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->getField('CURRENCY');
	}

	/**
	 * Change order currency.
	 *
	 * @param string $currency
	 *
	 * @return Main\Result
	 *
	 * @throws ArgumentNullException if currency empty
	 */
	public function changeCurrency(string $currency): Main\Result
	{
		$result = new Main\Result();

		if ($this->getCurrency() === $currency)
		{
			return $result;
		}
		elseif (empty($currency))
		{
			throw new ArgumentNullException('currency');
		}

		$this->setFieldNoDemand('CURRENCY', $currency);

		foreach ($this->getBasket() as $basketItem)
		{
			/**
			 * @var BasketItem $basketItem
			 */

			$result->addErrors(
				$basketItem->changeCurrency($currency)->getErrors()
			);
		}

		return $result;
	}

	/**
	 * Return user id
	 *
	 * @return int
	 */
	public function getUserId()
	{
		return $this->getField('USER_ID');
	}

	/**
	 * Return site id
	 *
	 * @return null|string
	 */
	public function getSiteId()
	{
		return $this->getField('LID');
	}

	/**
	 * Return TRUE if VAT is used. Else return FALSE
	 *
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
	 * Return order vat rate
	 *
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
	 * Return order vat sum
	 *
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
	 * Return TRUE if order has problems. Else return FALSE
	 * @return null|string
	 */
	public function isMarked()
	{
		return $this->getField('MARKED') === "Y";
	}

	protected function isPriceField(string $name) : bool
	{
		return
			$name === 'PRICE'
			|| $name === 'PRICE_DELIVERY'
			|| $name === 'SUM_PAID'
			|| $name === 'PRICE_PAYMENT'
			|| $name === 'DISCOUNT_VALUE'
		;
	}

	/**
	 * Clear VAT info
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
	 *
	 * Recalculate VAT
	 */
	public function refreshVat()
	{
		$this->resetVat();

		$vatInfo = $this->calculateVat();
		if ($vatInfo && $vatInfo['VAT_RATE'] > 0)
		{
			return $this->applyCalculatedVat($vatInfo);
		}

		return new Result();
	}

	/**
	 * Calculate VAT
	 *
	 * @return array
	 */
	protected function calculateVat()
	{
		$result = array();

		$basket = $this->getBasket();
		if ($basket)
		{
			$result['VAT_RATE'] = $basket->getVatRate();
			$result['VAT_SUM'] = $basket->getVatSum();
		}

		return $result;
	}

	/**
	 * @param array $vatInfo
	 * @return Result
	 */
	private function applyCalculatedVat(array $vatInfo)
	{
		$result = new Result();

		/** @var Result $r */
		$r = $this->setField('USE_VAT', 'Y');
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		/** @var Result $r */
		$r = $this->setField('VAT_RATE', $vatInfo['VAT_RATE']);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		/** @var Result $r */
		$r = $this->setField('VAT_SUM', $vatInfo['VAT_SUM']);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * Return TRUE if order is deducted. Else return FALSE
	 *
	 * @return string
	 */
	public function isShipped()
	{
		return $this->getField('DEDUCTED') === 'Y';
	}

	/**
	 * Return TRUE if order is external. Else return FALSE
	 *
	 * @return bool
	 */
	public function isExternal()
	{
		return $this->getField('EXTERNAL_ORDER') == "Y";
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

		return (in_array($field, static::getCalculatedFields()));
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected static function getInitialStatus()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderStatus $orderStatus */
		$orderStatus = $registry->getOrderStatusClassName();
		return $orderStatus::getInitialStatus();
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected static function getFinalStatus()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderStatus $orderStatus */
		$orderStatus = $registry->getOrderStatusClassName();
		return $orderStatus::getFinalStatus();
	}

	/**
	 * Save order
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	public function save()
	{
		if ($this->isSaveExecuting)
		{
			trigger_error("Order saving in recursion", E_USER_WARNING);
		}

		$this->isSaveExecuting = true;

		$result = new Result();

		$id = $this->getId();
		$this->isNew = ($id == 0);
		$needUpdateDateInsert = $this->getDateInsert() === null;

		$r = $this->callEventOnBeforeOrderSaved();
		if (!$r->isSuccess())
		{
			$this->isSaveExecuting = false;
			return $r;
		}

		$r = $this->verify();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$r = $this->onBeforeSave();
		if (!$r->isSuccess())
		{
			$this->isSaveExecuting = false;
			return $r;
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if ($id > 0)
		{
			$r = $this->update();
		}
		else
		{
			$r = $this->add();
			if ($r->getId() > 0)
			{
				$id = $r->getId();
			}
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if (!$r->isSuccess())
		{
			$this->isSaveExecuting = false;
			return $r;
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		$this->callEventOnSaleOrderEntitySaved();

		$r = $this->saveEntities();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		/** @var Discount $discount */
		$discount = $this->getDiscount();

		/** @var Result $r */
		$r = $discount->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$r = $this->completeSaving($needUpdateDateInsert);
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$this->callEventOnSaleOrderSaved();
		$this->callDelayedEvents();

		$this->onAfterSave();

		$this->isNew = false;
		$this->isSaveExecuting = false;
		$this->clearChanged();

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function onAfterSave()
	{
		return new Result();
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @return void
	 */
	protected function callDelayedEvents()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$notifyClassName = $registry->getNotifyClassName();

		$eventList = Internals\EventsPool::getEvents($this->getInternalId());
		if ($eventList)
		{
			foreach ($eventList as $eventName => $eventData)
			{
				$event = new Main\Event('sale', $eventName, $eventData);
				$event->send();

				$notifyClassName::callNotify($this, $eventName);
			}

			Internals\EventsPool::resetEvents($this->getInternalId());
		}

		$notifyClassName::callNotify($this, EventActions::EVENT_ON_ORDER_SAVED);
	}

	/**
	 * @param $needUpdateDateInsert
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function completeSaving($needUpdateDateInsert)
	{
		$result = new Result();

		$currentDateTime = new Type\DateTime();
		$updateFields = ['RUNNING' => 'N'];

		$changedFields = $this->fields->getChangedValues();
		if ($this->isNew
			|| (
				$this->isChanged()
				&& !array_key_exists('DATE_UPDATE', $changedFields)
			)
		)
		{
			$updateFields['DATE_UPDATE'] = $currentDateTime;
		}

		if ($needUpdateDateInsert)
		{
			$updateFields['DATE_INSERT'] = $currentDateTime;
		}

		$this->setFieldsNoDemand($updateFields);
		$r = static::updateInternal($this->getId(), $updateFields);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function add()
	{
		global $USER;

		$result = new Result();

		$currentDateTime = new Type\DateTime();

		if (!$this->getField('DATE_UPDATE'))
		{
			$this->setField('DATE_UPDATE', $currentDateTime);
		}

		if (!$this->getField('DATE_INSERT'))
		{
			$this->setField('DATE_INSERT', $currentDateTime);
		}

		$fields = $this->fields->getValues();

		if (is_object($USER) && $USER->isAuthorized())
		{
			$fields['CREATED_BY'] = $USER->getID();
			$this->setFieldNoDemand('CREATED_BY', $fields['CREATED_BY']);
		}

		if (array_key_exists('REASON_MARKED', $fields) && mb_strlen($fields['REASON_MARKED']) > 255)
		{
			$fields['REASON_MARKED'] = mb_substr($fields['REASON_MARKED'], 0, 255);
		}

		$fields['RUNNING'] = 'Y';

		$r = $this->addInternal($fields);
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		if ($resultData = $r->getData())
		{
			$result->setData($resultData);
		}

		$id = $r->getId();
		$this->setFieldNoDemand('ID', $id);
		$result->setId($id);

		$this->setAccountNumber();

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function update()
	{
		$result = new Result();

		$fields = $this->fields->getChangedValues();

		if ($this->isChanged())
		{
			if (array_key_exists('DATE_UPDATE', $fields) && $fields['DATE_UPDATE'] === null)
			{
				unset($fields['DATE_UPDATE']);
			}

			$fields['VERSION'] = intval($this->getField('VERSION')) + 1;
			$this->setFieldNoDemand('VERSION', $fields['VERSION']);

			if (array_key_exists('REASON_MARKED', $fields) && mb_strlen($fields['REASON_MARKED']) > 255)
			{
				$fields['REASON_MARKED'] = mb_substr($fields['REASON_MARKED'], 0, 255);
			}

			$r = static::updateInternal($this->getId(), $fields);

			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}

			if ($resultData = $r->getData())
			{
				$result->setData($resultData);
			}
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function callEventOnSaleOrderEntitySaved()
	{
		$oldEntityValues = $this->fields->getOriginalValues();

		if (!empty($oldEntityValues))
		{
			$eventManager = Main\EventManager::getInstance();
			if ($eventsList = $eventManager->findEventHandlers('sale', 'OnSaleOrderEntitySaved'))
			{
				/** @var Main\Event $event */
				$event = new Main\Event('sale', 'OnSaleOrderEntitySaved', array(
					'ENTITY' => $this,
					'VALUES' => $oldEntityValues,
				));
				$event->send();
			}
		}
	}

	/**
	 * @return void
	 */
	protected function callEventOnSaleOrderSaved()
	{
		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_ORDER_SAVED))
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_ORDER_SAVED, array(
				'ENTITY' => $this,
				'IS_NEW' => $this->isNew,
				'IS_CHANGED' => $this->isChanged(),
				'VALUES' => $this->fields->getOriginalValues(),
			));
			$event->send();
		}
	}

	/**
	 * @return Result
	 */
	protected function callEventOnBeforeOrderSaved()
	{
		$result = new Result();

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_ORDER_BEFORE_SAVED))
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', EventActions::EVENT_ON_ORDER_BEFORE_SAVED, array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR'), 'SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR');
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
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function saveEntities()
	{
		$result = new Result();

		$r = $this->getBasket()->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$r = $this->getTax()->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$r = $this->getPropertyCollection()->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		return $result;
	}

	/**
	 * Set account number.
	 *
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function setAccountNumber()
	{
		$accountNumber = Internals\AccountNumberGenerator::generateForOrder($this);
		if ($accountNumber !== false)
		{
			$this->setField('ACCOUNT_NUMBER', $accountNumber);

			static::updateInternal($this->getId(), ['ACCOUNT_NUMBER' => $accountNumber]);
		}
	}

	/**
	 * Set VAT sum
	 *
	 * @param $price
	 */
	public function setVatSum($price)
	{
		$this->setField('VAT_SUM', $price);
	}

	/**
	 * Set VAT delivery sum
	 *
	 * @param $price
	 */
	public function setVatDelivery($price)
	{
		$this->setField('VAT_DELIVERY', $price);
	}

	/**
	 * Return date order insert
	 *
	 * @return mixed
	 */
	public function getDateInsert()
	{
		return $this->getField('DATE_INSERT');
	}

	/**
	 * Return value: OrderBase::SALE_ORDER_CALC_TYPE_REFRESH, OrderBase::SALE_ORDER_CALC_TYPE_CHANGE, OrderBase::SALE_ORDER_CALC_TYPE_NEW
	 *
	 * @return null|string
	 */
	public function getCalculateType()
	{
		return $this->calculateType;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		$result = new Result();

		if ($name !== 'UPDATED_1C' && !$this->getFields()->isChanged('UPDATED_1C'))
		{
			$this->setField("UPDATED_1C", "N");
		}

		if ($name == "PRICE")
		{
			/** @var Result $r */
			$r = $this->refreshVat();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}
		elseif ($name == "CURRENCY")
		{
			throw new Main\NotImplementedException('field CURRENCY');
		}
		elseif ($name == "CANCELED")
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_ORDER_CANCELED, array(
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
							Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_CANCELED_ERROR'),
							'SALE_EVENT_ON_BEFORE_ORDER_CANCELED_ERROR'
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

			if (!$result->isSuccess())
			{
				return $result;
			}

			$r = $this->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$this->setField('DATE_CANCELED', new Type\DateTime());

			if (is_object($USER) && $USER->isAuthorized())
			{
				$this->setField('EMP_CANCELED_ID', $USER->getID());
			}

			Internals\EventsPool::addEvent(
				$this->getInternalId(),
				EventActions::EVENT_ON_ORDER_CANCELED,
				array('ENTITY' => $this)
			);

			Internals\EventsPool::addEvent(
				$this->getInternalId(),
				EventActions::EVENT_ON_ORDER_CANCELED_SEND_MAIL,
				array('ENTITY' => $this)
			);
		}
		elseif ($name == "USER_ID")
		{
			throw new Main\NotImplementedException('field USER_ID');
		}
		elseif($name == "MARKED")
		{
			if ($oldValue != "Y")
			{
				$this->setField('DATE_MARKED', new Type\DateTime());

				if (is_object($USER) && $USER->isAuthorized())
				{
					$this->setField('EMP_MARKED_ID', $USER->getID());
				}
			}
			elseif ($value == "N")
			{
				$this->setField('REASON_MARKED', '');
			}
		}
		elseif ($name == "STATUS_ID")
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_ORDER_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
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
							Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_STATUS_CHANGE_ERROR'),
							'SALE_EVENT_ON_BEFORE_ORDER_STATUS_CHANGE_ERROR'
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

			if (!$result->isSuccess())
			{
				return $result;
			}

			$this->setField('DATE_STATUS', new Type\DateTime());

			if (is_object($USER) && $USER->isAuthorized())
			{
				$this->setField('EMP_STATUS_ID', $USER->GetID());
			}

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_CHANGE_SEND_MAIL, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));

			if ($this->isStatusChangedOnPay($value, $oldValue))
			{
				Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_ALLOW_PAY_CHANGE, array(
					'ENTITY' => $this,
					'VALUE' => $value,
					'OLD_VALUE' => $oldValue,
				));
			}
		}

		return $result;
	}

	/**
	 * @param $name
	 * @param $oldValue
	 * @param $value
	 * @return Result
	 */
	protected function onOrderModify($name, $oldValue, $value)
	{
		return new Result();
	}

	/**
	 * Modify basket.
	 *
	 * @param string $action				Action.
	 * @param BasketItemBase $basketItem		Basket item.
	 * @param null|string $name				Field name.
	 * @param null|string|int|float $oldValue		Old value.
	 * @param null|string|int|float $value			New value.
	 * @return Result
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function onBasketModify($action, BasketItemBase $basketItem, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		if ($action === EventActions::DELETE)
		{
			/** @var Result $r */
			$r = $this->refreshVat();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			if ($tax = $this->getTax())
			{
				$tax->resetTaxList();
			}

			/** @var Result $result */
			$r = $this->refreshOrderPrice();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			return $result;
		}
		elseif ($action !== EventActions::UPDATE)
		{
			return $result;
		}

		if ($name == "QUANTITY" || $name == "PRICE")
		{
			/** @var Result $result */
			$r = $this->refreshOrderPrice();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		elseif ($name == "CURRENCY")
		{
			if ($value != $this->getField("CURRENCY"))
			{
				throw new Main\NotSupportedException("CURRENCY");
			}
		}

		return $result;
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
	 * @internal
	 *
	 * Return TRUE if order is new. Else return FALSE
	 *
	 * @return null|bool
	 */
	public function isNew()
	{
		return $this->isNew;
	}

	/**
	 * Reset the value of taxes
	 *
	 * @internal
	 */
	public function resetTax()
	{
		$this->setFieldNoDemand('TAX_PRICE', 0);
		$this->setFieldNoDemand('TAX_VALUE', 0);
	}

	/**
	 * Return TRUE if order is changed. Else return FALSE
	 *
	 * @return bool
	 */
	public function isChanged()
	{
		if (parent::isChanged())
			return true;

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			if ($propertyCollection->isChanged())
			{
				return true;
			}
		}

		/** @var BasketBase $basket */
		if ($basket = $this->getBasket())
		{
			if ($basket->isChanged())
			{
				return true;
			}

		}

		return false;
	}

	/**
	 * @internal
	 *
	 * Reset flag of order change
	 *
	 * @return void
	 */
	public function clearChanged()
	{
		parent::clearChanged();

		if ($basket = $this->getBasket())
		{
			$basket->clearChanged();
		}

		if ($property = $this->getPropertyCollection())
		{
			$property->clearChanged();
		}

	}

	/**
	 * Return TRUE, if this order is cloned. Else return FALSE
	 *
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}

	/**
	 * Return TRUE, if order is payed. Else return FALSE
	 *
	 * @return bool
	 */
	public function isPaid()
	{
		return $this->getField('PAYED') === 'Y';
	}

	/**
	 * Return TRUE, if order is allowed delivery. Else return FALSE
	 *
	 * @return bool
	 */
	public function isAllowDelivery()
	{
		return $this->getField('ALLOW_DELIVERY') === 'Y';
	}

	/**
	 * Return TRUE, if order is deducted. Else return FALSE
	 *
	 * @return bool
	 */
	public function isDeducted()
	{
		return $this->getField('DEDUCTED') === 'Y';
	}

	/**
	 * Return TRUE, if order is canceled. Else return FALSE
	 *
	 * @return bool
	 */
	public function isCanceled()
	{
		return $this->getField('CANCELED') === 'Y';
	}

	/**
	 * Return order hash
	 *
	 * @return mixed
	 */
	public function getHash()
	{
		/** @var Main\Type\DateTime $dateInsert */
		$dateInsert = $this->getDateInsert()->setTimeZone(new \DateTimeZone("Europe/Moscow"));
		$timestamp = $dateInsert->getTimestamp();
		return md5(
			$this->getId().
			$timestamp.
			$this->getUserId().
			$this->getField('ACCOUNT_NUMBER')
		);
	}

	/**
	 * Verify object to correctness
	 *
	 * @return Result
	 */
	public function verify()
	{
		$result = new Result();

		/** @var BasketBase $basket */
		if ($basket = $this->getBasket())
		{
			$r = $basket->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			$r = $propertyCollection->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		/** @var DiscountBase $discounts */
		if ($discounts = $this->getDiscount())
		{
			$r = $discounts->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			unset($r);
		}
		unset($discounts);

		return $result;
	}

	/**
	 * Get order information
	 *
	 * @param array $parameters
	 * @throws Main\NotImplementedException
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * Return tax location
	 *
	 * @return null|string
	 */
	public function getTaxLocation()
	{
		if ((string)$this->getField('TAX_LOCATION') === "")
		{
			$propertyCollection = $this->getPropertyCollection();

			if ($property = $propertyCollection->getTaxLocation())
			{
				$this->setField('TAX_LOCATION', $property->getValue());
			}

		}

		return $this->getField('TAX_LOCATION');
	}

	/**
	 * Return TRUE if calculations are based on current values. Data from the provider is not requested. Else return false
	 *
	 * @return bool
	 */
	public function isMathActionOnly()
	{
		return $this->isOnlyMathAction;
	}

	/**
	 * @return bool
	 */
	public function hasMeaningfulField()
	{
		return $this->isMeaningfulField;
	}

	/**
	 * Reset order flags: \Bitrix\Sale\OrderBase::$isStartField, \Bitrix\Sale\OrderBase::$isMeaningfulField
	 *
	 * @return void
	 */
	public function clearStartField()
	{
		$this->isStartField = null;
		$this->isMeaningfulField = false;
	}

	/**
	 * @param bool $isMeaningfulField
	 * @return bool
	 */
	public function isStartField($isMeaningfulField = false)
	{
		if ($this->isStartField === null)
		{
			$this->isStartField = true;
		}
		else
		{
			$this->isStartField = false;
		}

		if ($isMeaningfulField === true)
		{
			$this->isMeaningfulField = true;
		}

		return $this->isStartField;
	}

	/**
	 * @internal
	 *
	 * Set TRUE if calculations should be held on current values. Data from the provider is not requested
	 *
	 * @param bool $value
	 */
	public function setMathActionOnly($value = false)
	{
		$this->isOnlyMathAction = $value;
	}

	/**
	 * @internal
	 *
	 * Delete order without demands.
	 *
	 * @param $id
	 * @return Result
	 * @throws Main\NotImplementedException
	 */
	public static function deleteNoDemand($id)
	{
		$result = new Result();

		if (!static::isExists($id))
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND'));
			return $result;
		}

		/** @var Result $deleteResult */
		$deleteResult = static::deleteEntitiesNoDemand($id);
		if (!$deleteResult->isSuccess())
		{
			$result->addErrors($deleteResult->getErrors());
			return $result;
		}

		$r = static::deleteInternal($id);
		if (!$r->isSuccess())
			$result->addErrors($r->getErrors());

		static::deleteExternalEntities($id);

		return $result;
	}

	/**
	 * Delete order
	 *
	 * @param int $id				Order id.
	 * @return Result
	 * @throws Main\ArgumentNullException
	 */
	public static function delete($id)
	{
		$result = new Result();

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var OrderBase $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		if (!$order = $orderClassName::load($id))
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND'));
			return $result;
		}

		/** @var Notify $notifyClassName */
		$notifyClassName = $registry->getNotifyClassName();
		$notifyClassName::setNotifyDisable(true);

		/** @var Result $r */
		$r = $order->setField('CANCELED', 'Y');
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		$r = $order->save();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		static::deleteEntities($order);

		$event = new Main\Event(
			'sale',
			EventActions::EVENT_ON_BEFORE_ORDER_DELETE,
			array('ENTITY' => $order)
		);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			$return = null;
			if ($eventResult->getType() == Main\EventResult::ERROR)
			{
				if ($eventResultData = $eventResult->getParameters())
				{
					if (isset($eventResultData) && $eventResultData instanceof ResultError)
					{
						/** @var ResultError $errorMsg */
						$errorMsg = $eventResultData;
					}
				}

				if (!isset($errorMsg))
					$errorMsg = new ResultError('EVENT_ORDER_DELETE_ERROR');

				$result->addError($errorMsg);
				return $result;
			}
		}

		/** @var Result $r */
		$r = $order->save();
		if ($r->isSuccess())
		{
			/** @var Main\Entity\DeleteResult $r */
			$r = static::deleteInternal($id);
			if ($r->isSuccess())
				static::deleteExternalEntities($id);
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		$notifyClassName::setNotifyDisable(false);

		$event = new Main\Event(
			'sale',
			EventActions::EVENT_ON_ORDER_DELETED,
			array('ENTITY' => $order, 'VALUE' => $r->isSuccess())
		);
		$event->send();

		$result->addData(array('ORDER' => $order));

		return $result;
	}

	/**
	 * @param OrderBase $order
	 * @return void
	 */
	protected static function deleteEntities(OrderBase $order)
	{
		/** @var BasketBase $basketCollection */
		if ($basketCollection = $order->getBasket())
		{
			/** @var BasketItemBase $basketItem */
			foreach ($basketCollection as $basketItem)
			{
				$basketItem->delete();
			}
		}

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $order->getPropertyCollection())
		{
			/** @var PropertyValue $property */
			foreach ($propertyCollection as $property)
			{
				$property->delete();
			}
		}
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws Main\NotImplementedException
	 */
	protected static function isExists($id)
	{
		$dbRes = static::getList(array('filter' => array('ID' => $id)));
		if ($dbRes->fetch())
			return true;

		return false;
	}

	/**
	 * @deprecated Use OrderStatus::isAllowPay instead
	 *
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function isAllowPay()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderStatus $orderClassName */
		$orderClassName = $registry->getOrderStatusClassName();
		return $orderClassName::isAllowPay($this->getField('STATUS_ID'));
	}

	/**
	 * @param $orderId
	 */
	protected static function deleteExternalEntities($orderId)
	{
		return;
	}

	/**
	 * @param $orderId
	 * @return Result
	 */
	protected static function deleteEntitiesNoDemand($orderId)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var BasketBase $basketClassName */
		$basketClassName = $registry->getBasketClassName();
		$r = $basketClassName::deleteNoDemand($orderId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		/** @var PropertyValueCollectionBase $propertyValueCollectionClassName */
		$propertyValueCollectionClassName = $registry->getPropertyValueCollectionClassName();
		$r = $propertyValueCollectionClassName::deleteNoDemand($orderId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		/** @var OrderDiscountBase $orderDiscountClassName */
		$orderDiscountClassName = $registry->getOrderDiscountClassName();
		$orderDiscountClassName::deleteByOrder($orderId);

		return new Result();
	}

	/**
	 * Return discount object
	 *
	 * @return Discount
	 */
	public function getDiscount()
	{
		if ($this->discount === null)
		{
			$this->discount = $this->loadDiscount();
		}

		return $this->discount;
	}

	/**
	 * @return Tax
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected function loadTax()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var Tax $taxClassName */
		$taxClassName = $registry->getTaxClassName();
		return $taxClassName::load($this);
	}

	/**
	 * @return DiscountBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected function loadDiscount()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var Discount $discountClassName */
		$discountClassName = $registry->getDiscountClassName();
		return $discountClassName::buildFromOrder($this);
	}

	/**
	 * @return Result
	 */
	private function refreshOrderPrice()
	{
		return $this->setField("PRICE", $this->calculatePrice());
	}

	/**
	 * @return float
	 */
	protected function calculatePrice()
	{
		$basket = $this->getBasket();
		$taxPrice = !$this->isUsedVat() ? $this->getField('TAX_PRICE') : 0;

		return $basket->getPrice() + $taxPrice;
	}

	/**
	 * @return Result
	 */
	protected function onBeforeSave()
	{
		return new Result();
	}

	/**
	 * @param bool $hasMeaningfulField
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function doFinalAction($hasMeaningfulField = false)
	{
		$result = new Result();

		$orderInternalId = $this->getInternalId();

		$r = Internals\ActionEntity::runActions($orderInternalId);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		if (!$hasMeaningfulField)
		{
			$this->clearStartField();
			return $result;
		}


		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		$currentIsMathActionOnly = $this->isMathActionOnly();

		$basket = $this->getBasket();
		if ($basket)
		{
			$this->setMathActionOnly(true);

			$eventManager = Main\EventManager::getInstance();
			$eventsList = $eventManager->findEventHandlers('sale', 'OnBeforeSaleOrderFinalAction');
			if (!empty($eventsList))
			{
				$event = new Main\Event('sale', 'OnBeforeSaleOrderFinalAction', array(
					'ENTITY' => $this,
					'HAS_MEANINGFUL_FIELD' => $hasMeaningfulField,
					'BASKET' => $basket,
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
								Main\Localization\Loc::getMessage(
									'SALE_EVENT_ON_BEFORE_SALEORDER_FINAL_ACTION_ERROR'
								),
								'SALE_EVENT_ON_BEFORE_SALEORDER_FINAL_ACTION_ERROR'
							);

							$eventResultData = $eventResult->getParameters();
							if ($eventResultData)
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

			if (!$result->isSuccess())
			{
				return $result;
			}

			// discount
			$discount = $this->getDiscount();
			$r = $discount->calculate();
			if (!$r->isSuccess())
			{
//				$this->clearStartField();
//				$result->addErrors($r->getErrors());
//				return $result;
			}

			if ($r->isSuccess() && ($discountData = $r->getData()) && !empty($discountData) && is_array($discountData))
			{
				/** @var Result $r */
				$r = $this->applyDiscount($discountData);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}

			/** @var Tax $tax */
			$tax = $this->getTax();
			/** @var Result $r */
			$r = $tax->refreshData();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$taxResult = $r->getData();

			$taxChanged = false;
			if (isset($taxResult['TAX_PRICE']) && floatval($taxResult['TAX_PRICE']) >= 0)
			{
				if (!$this->isUsedVat())
				{
					$taxChanged = $this->getField('TAX_PRICE') !== $taxResult['TAX_PRICE'];
					if ($taxChanged)
					{
						$this->setField('TAX_PRICE', $taxResult['TAX_PRICE']);
						$this->refreshOrderPrice();
					}
				}

			}

			if (array_key_exists('VAT_SUM', $taxResult))
			{
				if ($this->isUsedVat())
				{
					$this->setField('VAT_SUM', $taxResult['VAT_SUM']);
				}
			}

			if ($taxChanged || $this->isUsedVat())
			{
				$taxValue = $this->isUsedVat()? $this->getVatSum() : $this->getField('TAX_PRICE');
				if (floatval($taxValue) != floatval($this->getField('TAX_VALUE')))
				{
					$this->setField('TAX_VALUE', floatval($taxValue));
				}
			}

		}

		if (!$currentIsMathActionOnly)
			$this->setMathActionOnly(false);

		$this->clearStartField();

		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', 'OnAfterSaleOrderFinalAction'))
		{
			$event = new Main\Event(
				'sale',
				'OnAfterSaleOrderFinalAction',
				array('ENTITY' => $this)
			);
			$event->send();
		}

		return $result;
	}

	/**
	 * Apply the result of the discounts to the order.
	 *
	 * @internal
	 *
	 * @param array $data
	 * @return Result
	 * @throws Main\ArgumentNullException
	 */
	public function applyDiscount(array $data)
	{
		if (!empty($data['BASKET_ITEMS']) && is_array($data['BASKET_ITEMS']))
		{
			/** @var BasketBase $basket */
			$basket = $this->getBasket();
			$basketResult = $basket->applyDiscount($data['BASKET_ITEMS']);
			if (!$basketResult->isSuccess())
				return $basketResult;
			unset($basketResult, $basket);

			$this->refreshOrderPrice();
		}

		return new Result();
	}

	/**
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	protected function setReasonMarked($value)
	{
		$result = new Result();

		if (!empty($value))
		{
			$orderReasonMarked = $this->getField('REASON_MARKED');
			if (is_array($value))
			{
				$newOrderReasonMarked = '';

				foreach ($value as $err)
				{
					$newOrderReasonMarked .= (strval($newOrderReasonMarked) != '' ? "\n" : "") . $err;
				}
			}
			else
			{
				$newOrderReasonMarked = $value;
			}

			/** @var Result $r */
			$r = $this->setField('REASON_MARKED', $orderReasonMarked. (strval($orderReasonMarked) != '' ? "\n" : ""). $newOrderReasonMarked);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Reset the value of the order and delivery
	 *
	 * @internal
	 *
	 * @param array $select
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function resetData($select = array('PRICE'))
	{
		if (in_array('PRICE', $select))
		{
			$this->setField('PRICE', 0);
		}

		if (in_array('PRICE_DELIVERY', $select))
		{
			$this->setField('PRICE_DELIVERY', 0);
		}
	}

	/**
	 * @param $value
	 * @param $oldValue
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function isStatusChangedOnPay($value, $oldValue)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderStatus $orderStatus */
		$orderStatus = $registry->getOrderStatusClassName();

		$allowPayStatus = $orderStatus::getAllowPayStatusList();
		$disallowPayStatus = $orderStatus::getDisallowPayStatusList();

		return !empty($disallowPayStatus)
				&& in_array($oldValue, $disallowPayStatus)
				&& !empty($allowPayStatus)
				&& in_array($value, $allowPayStatus);
	}

	/**
	 * Create order clone
	 *
	 * @return OrderBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public function createClone()
	{
		$cloneEntity = new \SplObjectStorage();

		/** @var OrderBase $orderClone */
		$orderClone = clone $this;
		$orderClone->isClone = true;

		/** @var Internals\Fields $fields */
		if ($fields = $this->fields)
		{
			$orderClone->fields = $fields->createClone($cloneEntity);
		}

		/** @var Internals\Fields $calculatedFields */
		if ($calculatedFields = $this->calculatedFields)
		{
			$orderClone->calculatedFields = $calculatedFields->createClone($cloneEntity);
		}

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $orderClone;
		}

		$this->cloneEntities($cloneEntity);

		return $orderClone;
	}

	/**
	 * @param \SplObjectStorage $cloneEntity
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function cloneEntities(\SplObjectStorage $cloneEntity)
	{
		if (!$cloneEntity->contains($this))
		{
			throw new Main\SystemException();
		}

		$orderClone = $cloneEntity[$this];

		/** @var BasketBase $basket */
		if ($basket = $this->getBasket())
		{
			$orderClone->basketCollection = $basket->createClone($cloneEntity);
		}

		/** @var PropertyValueCollectionBase $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			$orderClone->propertyCollection = $propertyCollection->createClone($cloneEntity);
		}

		if ($tax = $this->getTax())
		{
			$orderClone->tax = $tax->createClone($cloneEntity);
		}

		if ($discount = $this->getDiscount())
		{
			$orderClone->discount = $discount->createClone($cloneEntity);
		}
	}

	/**
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\AddResult
	 */
	abstract protected function addInternal(array $data);

	/**
	 * @param $primary
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\UpdateResult
	 */
	protected static function updateInternal($primary, array $data)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $primary
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\DeleteResult
	 */
	protected static function deleteInternal($primary)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * Return user field id
	 *
	 * @return null
	 */
	public static function getUfId()
	{
		return null;
	}

	/**
	 * @deprecated Use \Bitrix\Sale\OrderBase::getAvailableFields instead
	 *
	 * @returns array
	 */
	public static function getSettableFields()
	{
		return static::getAvailableFields();
	}

	/**
	 * @internal
	 *
	 * @return string
	 */
	public static function getEntityEventName()
	{
		return 'SaleOrder';
	}

	public function toArray() : array
	{
		$result = parent::toArray();

		$result['BASKET_ITEMS'] = $this->getBasket()->toArray();
		$result['PROPERTIES'] = $this->getPropertyCollection()->toArray();

		return $result;
	}
}
