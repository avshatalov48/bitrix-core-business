<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Localization;
use Bitrix\Sale\Basket\RefreshFactory;
use Bitrix\Sale\Internals;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class BasketItemBase
 * @package Bitrix\Sale
 */
abstract class BasketItemBase extends Internals\CollectableEntity
{
	/** @var BasketPropertiesCollectionBase $propertyCollection */
	protected $propertyCollection;

	/** @var Internals\Fields */
	protected $calculatedFields;

	/** @var  ProviderBase */
	protected $provider;

	/** @var string */
	protected $internalId = null;

	protected static $idBasket = 0;

	/**
	 * @param $basketCode
	 * @return BasketItemBase|null
	 * @throws Main\ArgumentNullException
	 */
	public function findItemByBasketCode($basketCode)
	{
		if ((string)$this->getBasketCode() === (string)$basketCode)
			return $this;

		return null;
	}

	/**
	 * @param $id
	 * @return BasketItemBase|null
	 * @throws Main\ArgumentNullException
	 */
	public function findItemById($id)
	{
		if ($id <= 0)
			return null;

		if ((int)$this->getId() === (int)$id)
			return $this;

		return null;
	}

	/**
	 * @return int|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getBasketCode()
	{
		if ($this->internalId == null)
		{
			if ($this->getId() > 0)
			{
				$this->internalId = $this->getId();
			}
			else
			{
				static::$idBasket++;
				$this->internalId = 'n'.static::$idBasket;
			}
		}

		return $this->internalId;
	}

	/**
	 * @param BasketItemCollection $basketItemCollection
	 * @param $moduleId
	 * @param $productId
	 * @param null $basketCode
	 * @return BasketItemBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	public static function create(BasketItemCollection $basketItemCollection, $moduleId, $productId, $basketCode = null)
	{
		$dateInsert = new Main\Type\DateTime();
		$fields = array(
			"MODULE" => $moduleId,
			"BASE_PRICE" => 0,
			"PRODUCT_ID" => $productId,
			'DATE_INSERT' => $dateInsert,
			'DATE_UPDATE' => $dateInsert,
			'XML_ID' => static::generateXmlId(),
		);

		$basketItem = static::createBasketItemObject($fields);

		if ($basketCode !== null)
		{
			$basketItem->internalId = $basketCode;
			if (strpos($basketCode, 'n') === 0)
			{
				$internalId = intval(substr($basketCode, 1));
				if ($internalId > static::$idBasket)
				{
					static::$idBasket = $internalId;
				}
			}
		}

		$basketItem->setCollection($basketItemCollection);

		return $basketItem;
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @param array $fields
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	private static function createBasketItemObject(array $fields = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$basketItemClassName = $registry->getBasketItemClassName();

		return new $basketItemClassName($fields);
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @return array
	 */
	public static function getSettableFields()
	{
		$result = array(
			"NAME", "LID", "SORT", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE_TYPE_ID",
			"CATALOG_XML_ID", "PRODUCT_XML_ID", "DETAIL_PAGE_URL",
			"BASE_PRICE", "PRICE", "DISCOUNT_PRICE", "CURRENCY", "CUSTOM_PRICE",
			"QUANTITY", "WEIGHT", "DIMENSIONS",
			"MEASURE_CODE", "MEASURE_NAME",
			"DELAY", "CAN_BUY", "NOTES",
			"VAT_RATE", "VAT_INCLUDED", "BARCODE_MULTI", "SUBSCRIBE",
			"PRODUCT_PROVIDER_CLASS", "CALLBACK_FUNC", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC", "PAY_CALLBACK_FUNC",
			"TYPE", "SET_PARENT_ID",
			"DISCOUNT_NAME", "DISCOUNT_VALUE", "DISCOUNT_COUPON", "RECOMMENDATION", "XML_ID"
		);

		return array_merge($result, static::getCalculatedFields());
	}

	/**
	 * @return array|null
	 */
	public static function getSettableFieldsMap()
	{
		static $fieldsMap = null;

		if ($fieldsMap === null)
		{
			$fieldsMap = array_fill_keys(static::getSettableFields(), true);
		}

		return $fieldsMap;
	}

	/**
	 * @return array
	 */
	public static function getCalculatedFields()
	{
		return array(
			'DISCOUNT_PRICE_PERCENT',
			'IGNORE_CALLBACK_FUNC',
			'DEFAULT_PRICE',
			'DISCOUNT_LIST'
		);
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return static::getSettableFields();
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array('QUANTITY', 'PRICE', 'CUSTOM_PRICE');
	}

	/**
	 * @param array $fields				Data.
	 */
	protected function __construct(array $fields = array())
	{
		$priceRoundedFields = ['BASE_PRICE', 'PRICE', 'DISCOUNT_PRICE'];

		foreach ($priceRoundedFields as $code)
		{
			if (isset($fields[$code]))
			{
				$fields[$code] = PriceMaths::roundPrecision($fields[$code]);
			}
		}

		parent::__construct($fields);

		$this->calculatedFields = new Internals\Fields();
	}

	/**
	 * @return Result
	 */
	protected function checkBeforeDelete()
	{
		return new Result();
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function delete()
	{
		$result = new Result();

		$checkResult = $this->checkBeforeDelete();
		if (!$checkResult->isSuccess())
		{
			$result->addErrors($checkResult->getErrors());
			return $result;
		}

		$eventName = static::getEntityEventName();

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnBefore".$eventName."EntityDeleted", array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
		));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if($eventResult->getType() == Main\EventResult::ERROR)
				{
					$eventResultData = $eventResult->getParameters();
					if ($eventResultData instanceof ResultError)
					{
						$error = $eventResultData;
					}
					else
					{
						$error = new ResultError(
							Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_'.ToUpper($eventName).'_ENTITY_DELETED_ERROR'),
							'SALE_EVENT_ON_BEFORE_'.ToUpper($eventName).'_ENTITY_DELETED_ERROR'
						);
					}

					$result->addError($error);
				}
			}

			if (!$result->isSuccess())
			{
				return $result;
			}
		}

		$this->setFieldNoDemand("QUANTITY", 0);

		/** @var Result $r */
		$r = parent::delete();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "On".$eventName."EntityDeleted", array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
		));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if($eventResult->getType() == Main\EventResult::ERROR)
				{
					$eventResultData = $eventResult->getParameters();
					if ($eventResultData instanceof ResultError)
					{
						$error = $eventResultData;
					}
					else
					{
						$error = new ResultError(
							Localization\Loc::getMessage('SALE_EVENT_ON_'.ToUpper($eventName).'_ENTITY_DELETED_ERROR'),
							'SALE_EVENT_ON_'.ToUpper($eventName).'_ENTITY_DELETED_ERROR'
						);
					}

					$result->addError($error);
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $name						Field name.
	 * @param string|int|float $value			Field value.
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public function setField($name, $value)
	{
		$priceRoundedFields = array(
			'BASE_PRICE' => 'BASE_PRICE',
			'PRICE' => 'PRICE',
			'DISCOUNT_PRICE' => 'DISCOUNT_PRICE',
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

		return parent::setField($name, $value);
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldNoDemand($name, $value)
	{
		$priceRoundedFields = array(
			'BASE_PRICE' => 'BASE_PRICE',
			'PRICE' => 'PRICE',
			'DISCOUNT_PRICE' => 'DISCOUNT_PRICE',
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

		parent::setFieldNoDemand($name, $value);
	}

	/**
	 * @param $name
	 * @return float|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getField($name)
	{
		static $calculatedFields = null;

		if ($calculatedFields === null)
		{
			$calculatedFields = array_fill_keys(static::getCalculatedFields(), true);
		}

		if (isset($calculatedFields[$name]))
		{
			if (
				isset($this->calculatedFields[$name])
				|| (is_array($this->calculatedFields) && array_key_exists($name, $this->calculatedFields))
			)
			{
				return $this->calculatedFields->get($name);
			}

			return null;
		}

		$value = parent::getField($name);
		if ($name == "BASE_PRICE" && $value === null)
		{
			$value = PriceMaths::roundPrecision($this->getField('PRICE') + $this->getField('DISCOUNT_PRICE'));
		}

		return $value;
	}

	/**
	 * @param array $fields
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function setFields(array $fields)
	{
		foreach ($fields as $name => $value)
		{
			if ($this->isCalculatedField($name))
			{
				$this->calculatedFields[$name] = $value;
				unset($fields[$name]);
			}
		}

		$priorityFields = array(
			'CURRENCY', 'CUSTOM_PRICE', 'VAT_RATE', 'VAT_INCLUDED',
			'PRODUCT_PROVIDER_CLASS', 'SUBSCRIBE', 'TYPE', 'LID', 'FUSER_ID', 'SUBSCRIBE'
		);
		foreach ($priorityFields as $fieldName)
		{
			if (!empty($fields[$fieldName]))
			{
				$this->setField($fieldName, $fields[$fieldName]);
				unset($fields[$fieldName]);
			}
		}

		return parent::setFields($fields);
	}

	/**
	 * @return ProviderBase|null
	 * @throws Main\ArgumentNullException
	 */
	public function getProviderName()
	{
		return $this->getProvider();
	}

	/**
	 * @return null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getCallbackFunction()
	{
		$callbackFunction = trim($this->getField('CALLBACK_FUNC'));
		if (!isset($callbackFunction) || (strval(trim($callbackFunction)) == ""))
		{
			return null;
		}

		if (!function_exists($callbackFunction))
		{
			return null;
		}

		return $callbackFunction;
	}

	/**
	 * @return bool|mixed|null
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 */
	public function getProviderEntity()
	{
		$module = $this->getField('MODULE');
		$productProviderName = $this->getField('PRODUCT_PROVIDER_CLASS');
		if (
			!isset($module)
			|| !isset($productProviderName)
			|| (strval($productProviderName) == "")
		)
		{
			return false;
		}

		if (!empty($module) && Main\Loader::includeModule($module))
		{
			return Internals\Catalog\Provider::getProviderEntity($productProviderName);
		}

		return null;
	}

	/**
	 * @return ProviderBase|null|string
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 */
	public function getProvider()
	{
		if ($this->provider !== null)
			return $this->provider;

		$module = $this->getField('MODULE');
		$productProviderName = $this->getField('PRODUCT_PROVIDER_CLASS');
		if (
			!isset($module)
			|| !isset($productProviderName)
			|| (strval($productProviderName) == "")
		)
		{
			return null;
		}

		$providerName = Internals\Catalog\Provider::getProviderName($module, $productProviderName);
		if ($providerName !== null)
		{
			$this->provider = $providerName;
		}

		return $providerName;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		$result = new Result();

		if ($name == "QUANTITY")
		{
			if ($value == 0)
			{
				$result->addError(new Main\Error(
					Localization\Loc::getMessage(
						'SALE_BASKET_ITEM_ERR_QUANTITY_ZERO',
						['#PRODUCT_NAME#' => $this->getField('NAME')]
					)
				));
				return $result;
			}

			$value = (float)$value;
			$oldValue = (float)$oldValue;
			$deltaQuantity = $value - $oldValue;

			/** @var Basket $basket */
			$basket = $this->getCollection();
			$context = $basket->getContext();

			/** @var Result $r */
			$r = Internals\Catalog\Provider::getAvailableQuantityAndPriceByBasketItem($this, $context);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				$result->setData($r->getData());

				return $result;
			}

			$providerData = $r->getData();

			if ($this->getField('SUBSCRIBE') !== 'Y')
			{
				if (array_key_exists('AVAILABLE_QUANTITY', $providerData) && $providerData['AVAILABLE_QUANTITY'] > 0)
				{
					$availableQuantity = $providerData['AVAILABLE_QUANTITY'];
				}
				else
				{
					$result->addError(
						new ResultError(
							Localization\Loc::getMessage(
								'SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY',
								array('#PRODUCT_NAME#' => $this->getField('NAME'))
							),
							'SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY'
						)
					);

					return $result;
				}
			}
			else
			{
				$availableQuantity = $value;
			}

			if (!empty($providerData['PRICE_DATA']))
			{
				if (isset($providerData['PRICE_DATA']['CUSTOM_PRICE']))
				{
					$this->setField('CUSTOM_PRICE', $providerData['PRICE_DATA']['CUSTOM_PRICE']);
				}
			}

			if ($value != 0
				&& (($deltaQuantity > 0) && (roundEx($availableQuantity, SALE_VALUE_PRECISION) < roundEx($value, SALE_VALUE_PRECISION))   // plus
				|| ($deltaQuantity < 0) && (roundEx($availableQuantity, SALE_VALUE_PRECISION) > roundEx($value, SALE_VALUE_PRECISION)))
			)   // minus
			{
				if ($deltaQuantity > 0)
				{
					$mess = Localization\Loc::getMessage(
						'SALE_BASKET_AVAILABLE_FOR_PURCHASE_QUANTITY',
						array(
							'#PRODUCT_NAME#' => $this->getField('NAME'),
							'#AVAILABLE_QUANTITY#' => $availableQuantity
						)
					);
				}
				else
				{
					$mess = Localization\Loc::getMessage(
						'SALE_BASKET_AVAILABLE_FOR_DECREASE_QUANTITY',
						array(
							'#PRODUCT_NAME#' => $this->getField('NAME'),
							'#AVAILABLE_QUANTITY#' => $availableQuantity
						)
					);
				}

				$result->addError(new ResultError($mess, "SALE_BASKET_AVAILABLE_QUANTITY"));
				$result->setData(array("AVAILABLE_QUANTITY" => $availableQuantity, "REQUIRED_QUANTITY" => $deltaQuantity));

				return $result;
			}

			/** @var BasketItemCollection $collection */
			$collection = $this->getCollection();

			/** @var BasketBase $basket */
			$basket = $collection->getBasket();

			if ((!$basket->getOrder() || $basket->getOrderId() == 0) && !($collection instanceof BundleCollection))
			{
				if ($this->getField("CUSTOM_PRICE") != "Y" && $value > 0)
				{
					$r = $basket->refresh(RefreshFactory::createSingle($this->getBasketCode()));
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
						return $result;
					}
				}
			}

			if ($this->getField("CUSTOM_PRICE") != "Y")
			{

				$providerName = $this->getProviderName();
				if (strval($providerName) == '')
				{
					$providerName = $this->getCallbackFunction();
				}


				if (!empty($providerData['PRICE_DATA']))
				{
					if (isset($providerData['PRICE_DATA']['PRICE']))
					{
						$this->setField('PRICE', $providerData['PRICE_DATA']['PRICE']);
					}

					if (isset($providerData['PRICE_DATA']['BASE_PRICE']))
					{
						$this->setField('BASE_PRICE', $providerData['PRICE_DATA']['BASE_PRICE']);
					}

					if (isset($providerData['PRICE_DATA']['DISCOUNT_PRICE']))
					{
						$this->setField('DISCOUNT_PRICE', $providerData['PRICE_DATA']['DISCOUNT_PRICE']);
					}
				}
				elseif ($providerName && !$this->isCustom())
				{
					$result->addError(
						new ResultError(
							Localization\Loc::getMessage(
								'SALE_BASKET_ITEM_WRONG_PRICE',
								array('#PRODUCT_NAME#' => $this->getField('NAME'))
							),
							'SALE_BASKET_ITEM_WRONG_PRICE'
						)
					);

					return $result;
				}
			}
		}

		$r = parent::onFieldModify($name, $oldValue, $value);
		if ($r->isSuccess())
		{
			if (($name === 'BASE_PRICE') || ($name === 'DISCOUNT_PRICE'))
			{
				if ($this->getField('CUSTOM_PRICE') !== 'Y')
				{
					$price = $this->getField('BASE_PRICE') - $this->getField('DISCOUNT_PRICE');
					$r = $this->setField('PRICE', $price);
					if (!$r->isSuccess())
						$result->addErrors($r->getErrors());
				}
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public function isVatInPrice()
	{
		return $this->getField('VAT_INCLUDED') === 'Y';
	}

	/**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getVat()
	{
		$vatRate = $this->getVatRate();
		if ($vatRate == 0)
			return 0;

		if ($this->isVatInPrice())
			$vat = PriceMaths::roundPrecision(($this->getPrice() * $this->getQuantity() * $vatRate / ($vatRate + 1)));
		else
			$vat = PriceMaths::roundPrecision(($this->getPrice() * $this->getQuantity() * $vatRate));

		return $vat;
	}

	/**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getInitialPrice()
	{
		$price = PriceMaths::roundPrecision($this->getPrice() * $this->getQuantity());

		if ($this->isVatInPrice())
			$price -= $this->getVat();

		return $price;
	}

	/**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getBasePriceWithVat()
	{
		$price = $this->getBasePrice();

		if (!$this->isVatInPrice())
		{
			$vatRate = $this->getVatRate();
			$price += $this->getBasePrice() * $vatRate;
		}

		return PriceMaths::roundPrecision($price);
	}

	/**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getPriceWithVat()
	{
		$price = $this->getPrice();

		if (!$this->isVatInPrice())
		{
			$vatRate = $this->getVatRate();
			$price += $this->getPrice() * $vatRate;
		}

		return PriceMaths::roundPrecision($price);
	}

	/**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getFinalPrice()
	{
		$price = PriceMaths::roundPrecision($this->getPrice() * $this->getQuantity());

		if (!$this->isVatInPrice())
			$price += $this->getVat();

		return $price;
	}

	/**
	 * @param string $field			Field name.
	 * @return bool
	 */
	protected function isCalculatedField($field)
	{
		static $calculateFields = null;

		if ($calculateFields === null)
		{
			$calculateFields = array_fill_keys(static::getCalculatedFields(), true);
		}

		return isset($calculateFields[$field]);
	}

	/**
	 * @return int|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getId()
	{
		return (int)$this->getField('ID');
	}

	/**
	 * @return float|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getProductId()
	{
		return $this->getField('PRODUCT_ID');
	}

	/**
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getPrice()
	{
		return (float)$this->getField('PRICE');
	}

	/**
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getBasePrice()
	{
		return (float)$this->getField('BASE_PRICE');
	}

	/**
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getDefaultPrice()
	{
		return (float)$this->getField('DEFAULT_PRICE');
	}

	/**
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getDiscountPrice()
	{
		return (float)$this->getField('DISCOUNT_PRICE');
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public function isCustomPrice()
	{
		return $this->getField('CUSTOM_PRICE') === 'Y';
	}

	/**
	 * @return float|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getCurrency()
	{
		return $this->getField('CURRENCY');
	}

	/**
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getQuantity()
	{
		return (float)$this->getField('QUANTITY');
	}

	/**
	 * @return float|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getWeight()
	{
		return $this->getField('WEIGHT');
	}

	/**
	 * @return float|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getVatRate()
	{
		return $this->getField('VAT_RATE');
	}

	/**
	 * @return float|null|string
	 * @throws Main\ArgumentNullException
	 */
	public function getFUserId()
	{
		return $this->getField('FUSER_ID');
	}

	/**
	 * @param $id
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public function setOrderId($id)
	{
		$this->setField('ORDER_ID', (int)$id);
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public function isBarcodeMulti()
	{
		return $this->getField('BARCODE_MULTI') === "Y";
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public function canBuy()
	{
		return $this->getField('CAN_BUY') === "Y";
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public function isDelay()
	{
		return $this->getField('DELAY') === "Y";
	}

	/**
	 * @return BasketPropertiesCollectionBase|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function getPropertyCollection()
	{
		if ($this->propertyCollection === null)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var BasketPropertiesCollectionBase $basketPropertyCollectionClassName */
			$basketPropertyCollectionClassName = $registry->getBasketPropertiesCollectionClassName();

			if ($this->getId() > 0)
			{
				/** @var BasketItemCollection $collection */
				$collection = $this->getCollection();
				$basketPropertyCollectionClassName::loadByCollection($collection);
			}

			if ($this->propertyCollection === null)
			{
				$this->propertyCollection = $basketPropertyCollectionClassName::load($this);
			}
		}

		return $this->propertyCollection;
	}

	/**
	 * @return bool
	 */
	public function isExistPropertyCollection()
	{
		return $this->propertyCollection !== null;
	}

	/**
	 * @internal
	 * @param BasketPropertiesCollectionBase $propertyCollection
	 */
	public function setPropertyCollection(BasketPropertiesCollectionBase $propertyCollection)
	{
		$this->propertyCollection = $propertyCollection;
	}

	/**
	 * @param $value
	 * @param bool $custom
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public function setPrice($value, $custom = false)
	{
		$result = new Result();

		$r = $this->setField('CUSTOM_PRICE', ($custom ? 'Y' : 'N'));
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		$r = $this->setField('PRICE', $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @internal
	 * @return array
	 */
	public static function getRoundFields()
	{
		return array(
			'BASE_PRICE',
			'DISCOUNT_PRICE',
			'DISCOUNT_PRICE_PERCENT',
		);
	}

	/**
	 * @param array $values
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function initFields(array $values)
	{
		if (!isset($values['BASE_PRICE']) || doubleval($values['BASE_PRICE']) == 0)
			$values['BASE_PRICE'] = $values['PRICE'] + $values['DISCOUNT_PRICE'];

		parent::initFields($values);
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();
		$id = (int)$this->getId();
		$isNew = $id === 0;

		$this->onBeforeSave();

		$r = $this->callEventSaleBasketItemBeforeSaved($isNew);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if (!$this->isChanged())
		{
			return $result;
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

		if (!$r->isSuccess())
		{
			return $r;
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		$r = $this->callEventSaleBasketItemSaved($isNew);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$r = $this->saveProperties();
		if (!$r->isSuccess())
			$result->addErrors($r->getErrors());

		$this->callEventOnBasketItemEntitySaved();

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	private function saveProperties()
	{
		/** @var BasketPropertiesCollection $basketPropertyCollection */
		$basketPropertyCollection = $this->getPropertyCollection();
		return $basketPropertyCollection->save();
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @return void
	 */
	protected function onBeforeSave()
	{
		/** @var BasketItemCollection $collection */
		$collection = $this->getCollection();

		/** @var BasketBase $basket */
		if (!$basket = $collection->getBasket())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var BasketBase $basket */
		$basket = $collection->getBasket();

		if ($this->getField('ORDER_ID') <= 0)
		{
			$orderId = (int)$collection->getOrderId();
			if ($orderId > 0)
			{
				$this->setFieldNoDemand('ORDER_ID', $orderId);
			}
		}

		if ($this->getId() <= 0)
		{
			if ($this->getField('FUSER_ID') <= 0)
			{
				$fUserId = (int)$basket->getFUserId(true);
				if ($fUserId <= 0)
					throw new Main\ArgumentNullException('FUSER_ID');

				$this->setFieldNoDemand('FUSER_ID', $fUserId);
			}
		}
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function add()
	{
		$result = new Result();

		$fields = $this->fields->getValues();

		$r = $this->addInternal($fields);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($resultData = $r->getData())
			$result->setData($resultData);

		$id = $r->getId();
		$this->setFieldNoDemand('ID', $id);
		$result->setId($id);

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectException
	 */
	protected function update()
	{
		$result = new Result();

		$this->setFieldNoDemand('DATE_UPDATE', new Main\Type\DateTime());

		$fields = $this->fields->getChangedValues();

		if (!empty($fields))
		{
			$r = $this->updateInternal($this->getId(), $fields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
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
	protected function callEventOnBasketItemEntitySaved()
	{
		$eventName = static::getEntityEventName();

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		if (!empty($oldEntityValues))
		{
			/** @var Main\Event $event */
			$event = new Main\Event(
				'sale',
				'On'.$eventName.'EntitySaved',
				array(
					'ENTITY' => $this,
					'VALUES' => $oldEntityValues,
				)
			);

			$event->send();
		}
	}

	/**
	 * @param $isNewEntity
	 * @return Result
	 */
	protected function callEventSaleBasketItemBeforeSaved($isNewEntity)
	{
		$result = new Result();

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', EventActions::EVENT_ON_BASKET_ITEM_BEFORE_SAVED, array(
			'ENTITY' => $this,
			'IS_NEW' => $isNewEntity,
			'VALUES' => $oldEntityValues,
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
						Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_BASKET_ITEM_SAVED'),
						'SALE_EVENT_ON_BEFORE_BASKET_ITEM_SAVED'
					);

					$eventResultData = $eventResult->getParameters();
					if ($eventResultData instanceof ResultError)
						$errorMsg = $eventResultData;

					$result->addError($errorMsg);
				}
			}
		}

		return $result;
	}

	/**
	 * @param $isNewEntity
	 * @return Result
	 */
	protected function callEventSaleBasketItemSaved($isNewEntity)
	{
		$result = new Result();

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', EventActions::EVENT_ON_BASKET_ITEM_SAVED, array(
			'ENTITY' => $this,
			'IS_NEW' => $isNewEntity,
			'VALUES' => $oldEntityValues,
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
						Localization\Loc::getMessage('SALE_EVENT_ON_BASKET_ITEM_SAVED_ERROR'),
						'SALE_EVENT_ON_BASKET_ITEM_SAVED_ERROR'
					);
					$eventResultData = $eventResult->getParameters();

					if ($eventResultData instanceof ResultError)
						$errorMsg = $eventResultData;

					$result->addError($errorMsg);
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $fields
	 * @return Main\Entity\AddResult
	 */
	abstract protected function addInternal(array $fields);

	/**
	 * @param $primary
	 * @param array $fields
	 * @return Main\Entity\UpdateResult
	 */
	abstract protected function updateInternal($primary, array $fields);

	/**
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public function isChanged()
	{
		$isChanged = parent::isChanged();
		if ($isChanged === false)
		{
			$propertyCollection = $this->getPropertyCollection();
			$isChanged = $propertyCollection->isChanged();
		}

		return $isChanged;
	}

	/**
	 * @param BasketItemCollection $basketItemCollection
	 * @param $data
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function load(BasketItemCollection $basketItemCollection, $data)
	{
		$fields = array(
			'MODULE' => $data['MODULE'],
			'PRODUCT_ID' => $data['PRODUCT_ID'],
		);

		$basketItem = static::createBasketItemObject($fields);
		$basketItem->initFields($data);
		$basketItem->setCollection($basketItemCollection);

		return $basketItem;
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

		if ((float)$this->getField('QUANTITY') <= 0)
		{
			$result->addError(new Main\Error(
				Localization\Loc::getMessage('SALE_BASKET_ITEM_ERR_QUANTITY_ZERO')
			));
		}

		if (!$this->getField('CURRENCY'))
		{
			$result->addError(new Main\Error(
				Localization\Loc::getMessage('SALE_BASKET_ITEM_ERR_CURRENCY_EMPTY')
			));
		}

		if ($basketPropertyCollection = $this->getPropertyCollection())
		{
			$r = $basketPropertyCollection->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return float
	 */
	public function getDeltaQuantity()
	{
		$fields = $this->getFields();
		$originalValues = $fields->getOriginalValues();
		$values = $fields->getValues();
		$deltaQuantity = floatval($values['QUANTITY']) - floatval($originalValues['QUANTITY']);

		return $deltaQuantity;
	}

	/**
	 * @return float
	 */
	abstract public function getReservedQuantity();

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public function isCustom()
	{
		$moduleId = trim($this->getField('MODULE'));
		$providerClassName = trim($this->getField('PRODUCT_PROVIDER_CLASS'));
		$callbackFunct = trim($this->getField('CALLBACK_FUNC'));

		return (empty($moduleId) && empty($providerClassName) && empty($callbackFunct));
	}
}