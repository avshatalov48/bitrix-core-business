<?php
namespace Bitrix\Sale;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class Registry
 * @package Bitrix\Sale
 */
final class Registry
{
	const EVENT_ON_INIT_REGISTRY_LIST = 'OnInitRegistryList';

	const REGISTRY_TYPE_ORDER = 'ORDER';
	const REGISTRY_TYPE_ARCHIVE_ORDER = 'ARCHIVE_ORDER';

	const ENTITY_SHIPMENT = 'SHIPMENT';
	const ENTITY_ORDER = 'ORDER';
	const ENTITY_PAYMENT = 'PAYMENT';
	const ENTITY_PAYMENT_COLLECTION = 'PAYMENT_COLLECTION';
	const ENTITY_SHIPMENT_COLLECTION = 'SHIPMENT_COLLECTION';
	const ENTITY_PROPERTY_VALUE = 'PROPERTY_VALUE';
	const ENTITY_BUNDLE_COLLECTION = 'BUNDLE_COLLECTION';
	const ENTITY_BASKET = 'BASKET';
	const ENTITY_TAX = 'TAX';
	const ENTITY_BASKET_ITEM = 'BASKET_ITEM';
	const ENTITY_BASKET_PROPERTIES_COLLECTION = 'BASKET_PROPERTIES_COLLECTION';
	const ENTITY_BASKET_PROPERTY_ITEM = 'BASKET_PROPERTY_ITEM';
	const ENTITY_SHIPMENT_ITEM = 'SHIPMENT_ITEM';
	const ENTITY_SHIPMENT_ITEM_COLLECTION = 'SHIPMENT_ITEM_COLLECTION';
	const ENTITY_SHIPMENT_ITEM_STORE = 'SHIPMENT_ITEM_STORE';
	const ENTITY_SHIPMENT_ITEM_STORE_COLLECTION = 'SHIPMENT_ITEM_STORE_COLLECTION';
	const ENTITY_PROPERTY_VALUE_COLLECTION = 'PROPERTY_VALUE_COLLECTION';
	const ENTITY_OPTIONS = 'CONFIG_OPTION';
	const ENTITY_DISCOUNT = 'DISCOUNT';
	const ENTITY_DISCOUNT_COUPON = 'DISCOUNT_COUPON';
	const ENTITY_ORDER_DISCOUNT = 'ORDER_DISCOUNT';
	const ENTITY_PERSON_TYPE = 'PERSON_TYPE';
	const ENTITY_ORDER_STATUS = 'ORDER_STATUS';
	const ENTITY_DELIVERY_STATUS = 'DELIVERY_STATUS';
	const ENTITY_ENTITY_MARKER = 'ENTITY_MARKER';
	const ENTITY_ORDER_HISTORY = 'ORDER_HISTORY';
	const ENTITY_PROPERTY = 'PROPERTIES';
	const ENTITY_NOTIFY = 'NOTIFY';
	const ENTITY_TRADE_BINDING_COLLECTION = 'TRADE_BINDING_COLLECTION';
	const ENTITY_TRADE_BINDING_ENTITY = 'TRADE_BINDING_ENTITY';

	private static $registryMap = array();
	private static $registryObjects = array();

	private $type = '';

	/**
	 * @return void
	 */
	private static function initRegistry()
	{
		static::$registryMap = array(
			static::REGISTRY_TYPE_ORDER => array(
				Registry::ENTITY_ORDER => '\Bitrix\Sale\Order',
				Registry::ENTITY_PAYMENT => '\Bitrix\Sale\Payment',
				Registry::ENTITY_PAYMENT_COLLECTION => '\Bitrix\Sale\PaymentCollection',
				Registry::ENTITY_SHIPMENT => '\Bitrix\Sale\Shipment',
				Registry::ENTITY_SHIPMENT_COLLECTION => '\Bitrix\Sale\ShipmentCollection',
				Registry::ENTITY_SHIPMENT_ITEM => '\Bitrix\Sale\ShipmentItem',
				Registry::ENTITY_SHIPMENT_ITEM_COLLECTION => '\Bitrix\Sale\ShipmentItemCollection',
				Registry::ENTITY_SHIPMENT_ITEM_STORE => '\Bitrix\Sale\ShipmentItemStore',
				Registry::ENTITY_SHIPMENT_ITEM_STORE_COLLECTION => '\Bitrix\Sale\ShipmentItemStoreCollection',
				Registry::ENTITY_PROPERTY_VALUE_COLLECTION => '\Bitrix\Sale\PropertyValueCollection',
				Registry::ENTITY_PROPERTY_VALUE => '\Bitrix\Sale\PropertyValue',
				Registry::ENTITY_PROPERTY => 'Bitrix\Sale\Property',
				Registry::ENTITY_TAX => '\Bitrix\Sale\Tax',
				Registry::ENTITY_BASKET_PROPERTY_ITEM => '\Bitrix\Sale\BasketPropertyItem',
				Registry::ENTITY_BUNDLE_COLLECTION => '\Bitrix\Sale\BundleCollection',
				Registry::ENTITY_BASKET => '\Bitrix\Sale\Basket',
				Registry::ENTITY_BASKET_ITEM => '\Bitrix\Sale\BasketItem',
				Registry::ENTITY_BASKET_PROPERTIES_COLLECTION => '\Bitrix\Sale\BasketPropertiesCollection',
				Registry::ENTITY_DISCOUNT => '\Bitrix\Sale\Discount',
				Registry::ENTITY_DISCOUNT_COUPON => '\Bitrix\Sale\DiscountCouponsManager',
				Registry::ENTITY_ORDER_DISCOUNT => '\Bitrix\Sale\OrderDiscount',
				Registry::ENTITY_OPTIONS => 'Bitrix\Main\Config\Option',
				Registry::ENTITY_PERSON_TYPE => 'Bitrix\Sale\PersonType',
				Registry::ENTITY_ORDER_STATUS => 'Bitrix\Sale\OrderStatus',
				Registry::ENTITY_DELIVERY_STATUS => 'Bitrix\Sale\DeliveryStatus',
				Registry::ENTITY_ENTITY_MARKER => '\Bitrix\Sale\EntityMarker',
				Registry::ENTITY_ORDER_HISTORY => 'Bitrix\Sale\OrderHistory',
				Registry::ENTITY_NOTIFY => 'Bitrix\Sale\Notify',
				Registry::ENTITY_TRADE_BINDING_COLLECTION => 'Bitrix\Sale\TradeBindingCollection',
				Registry::ENTITY_TRADE_BINDING_ENTITY => 'Bitrix\Sale\TradeBindingEntity',
			),
			static::REGISTRY_TYPE_ARCHIVE_ORDER => array(
				Registry::ENTITY_ORDER => '\Bitrix\Sale\Archive\Order',
				Registry::ENTITY_PAYMENT => '\Bitrix\Sale\Payment',
				Registry::ENTITY_PAYMENT_COLLECTION => '\Bitrix\Sale\PaymentCollection',
				Registry::ENTITY_SHIPMENT => '\Bitrix\Sale\Shipment',
				Registry::ENTITY_SHIPMENT_COLLECTION => '\Bitrix\Sale\ShipmentCollection',
				Registry::ENTITY_SHIPMENT_ITEM => '\Bitrix\Sale\ShipmentItem',
				Registry::ENTITY_SHIPMENT_ITEM_COLLECTION => '\Bitrix\Sale\ShipmentItemCollection',
				Registry::ENTITY_SHIPMENT_ITEM_STORE => '\Bitrix\Sale\ShipmentItemStore',
				Registry::ENTITY_SHIPMENT_ITEM_STORE_COLLECTION => '\Bitrix\Sale\ShipmentItemStoreCollection',
				Registry::ENTITY_PROPERTY_VALUE_COLLECTION => '\Bitrix\Sale\PropertyValueCollection',
				Registry::ENTITY_PROPERTY_VALUE => '\Bitrix\Sale\PropertyValue',
				Registry::ENTITY_PROPERTY => 'Bitrix\Sale\Property',
				Registry::ENTITY_TAX => '\Bitrix\Sale\Tax',
				Registry::ENTITY_BASKET_PROPERTY_ITEM => '\Bitrix\Sale\BasketPropertyItem',
				Registry::ENTITY_BUNDLE_COLLECTION => '\Bitrix\Sale\BundleCollection',
				Registry::ENTITY_BASKET => '\Bitrix\Sale\Basket',
				Registry::ENTITY_BASKET_ITEM => '\Bitrix\Sale\BasketItem',
				Registry::ENTITY_BASKET_PROPERTIES_COLLECTION => '\Bitrix\Sale\BasketPropertiesCollection',
				Registry::ENTITY_DISCOUNT => '\Bitrix\Sale\Discount',
				Registry::ENTITY_DISCOUNT_COUPON => '\Bitrix\Sale\DiscountCouponsManager',
				Registry::ENTITY_ORDER_DISCOUNT => '\Bitrix\Sale\OrderDiscount',
				Registry::ENTITY_OPTIONS => 'Bitrix\Main\Config\Option',
				Registry::ENTITY_PERSON_TYPE => 'Bitrix\Sale\PersonType',
				Registry::ENTITY_ORDER_STATUS => 'Bitrix\Sale\OrderStatus',
				Registry::ENTITY_DELIVERY_STATUS => 'Bitrix\Sale\DeliveryStatus',
				Registry::ENTITY_ENTITY_MARKER => '\Bitrix\Sale\EntityMarker',
				Registry::ENTITY_ORDER_HISTORY => 'Bitrix\Sale\OrderHistory',
				Registry::ENTITY_TRADE_BINDING_COLLECTION => 'Bitrix\Sale\TradeBindingCollection',
				Registry::ENTITY_TRADE_BINDING_ENTITY => 'Bitrix\Sale\TradeBindingEntity',
				Registry::ENTITY_NOTIFY => 'Bitrix\Sale\Notify',
			),
		);

		$event = new Main\Event('sale', static::EVENT_ON_INIT_REGISTRY_LIST);
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			foreach ($resultList as $eventResult)
			{
				/** @var  Main\EventResult $eventResult */
				if ($eventResult->getType() === Main\EventResult::SUCCESS)
				{
					$params = $eventResult->getParameters();
					if (!empty($params) && is_array($params))
					{
						static::$registryMap = array_merge(static::$registryMap, $params);
					}
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param $type
	 * @return Registry
	 * @throws Main\ArgumentException
	 */
	public static function getInstance($type)
	{
		if (!static::$registryMap)
			static::initRegistry();

		if (!isset(static::$registryObjects[$type]))
		{
			if (isset(static::$registryMap[$type]))
				static::$registryObjects[$type] = new static($type);
			else
				throw new Main\ArgumentException();
		}

		return static::$registryObjects[$type];
	}

	/**
	 * @param $code
	 * @param $registryItem
	 * @return void
	 */
	public static function setRegistry($code, $registryItem)
	{
		if (!static::$registryMap)
			static::initRegistry();

		static::$registryMap[$code] = $registryItem;
	}

	/**
	 * Registry constructor.
	 * @param $type
	 */
	private function __construct($type)
	{
		$this->type = $type;
	}

	/**
	 * @param $code
	 * @param $className
	 */
	public function set($code, $className)
	{
		static::$registryMap[$this->type][$code] = $className;
	}

	/**
	 * @param $code
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function get($code)
	{
		if (isset(static::$registryMap[$this->type][$code]))
		{
			return static::$registryMap[$this->type][$code];
		}

		throw new Main\SystemException(
			Main\Localization\Loc::getMessage(
				'SALE_REGISTRY_CODE_VALUE_NO_EXISTS',
				['#TYPE#' => $this->getType(), '#CODE#' => $code]
			)
		);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getOrderClassName()
	{
		return $this->get(static::ENTITY_ORDER);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getPaymentClassName()
	{
		return $this->get(static::ENTITY_PAYMENT);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getShipmentClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getShipmentItemCollectionClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM_COLLECTION);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getShipmentItemClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getShipmentItemStoreClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM_STORE);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getShipmentItemStoreCollectionClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM_STORE_COLLECTION);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getBasketItemClassName()
	{
		return $this->get(static::ENTITY_BASKET_ITEM);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getShipmentCollectionClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_COLLECTION);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getPaymentCollectionClassName()
	{
		return $this->get(static::ENTITY_PAYMENT_COLLECTION);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getPropertyValueCollectionClassName()
	{
		return $this->get(static::ENTITY_PROPERTY_VALUE_COLLECTION);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getPropertyValueClassName()
	{
		return $this->get(static::ENTITY_PROPERTY_VALUE);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getBasketClassName()
	{
		return $this->get(static::ENTITY_BASKET);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getBundleCollectionClassName()
	{
		return $this->get(static::ENTITY_BUNDLE_COLLECTION);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getDiscountClassName()
	{
		return $this->get(static::ENTITY_DISCOUNT);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getDiscountCouponClassName()
	{
		return $this->get(static::ENTITY_DISCOUNT_COUPON);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getOrderDiscountClassName()
	{
		return $this->get(static::ENTITY_ORDER_DISCOUNT);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getTaxClassName()
	{
		return $this->get(static::ENTITY_TAX);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getBasketPropertiesCollectionClassName()
	{
		return $this->get(static::ENTITY_BASKET_PROPERTIES_COLLECTION);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getBasketPropertyItemClassName()
	{
		return $this->get(static::ENTITY_BASKET_PROPERTY_ITEM);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getPersonTypeClassName()
	{
		return $this->get(static::ENTITY_PERSON_TYPE);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getOrderStatusClassName()
	{
		return $this->get(static::ENTITY_ORDER_STATUS);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getDeliveryStatusClassName()
	{
		return $this->get(static::ENTITY_DELIVERY_STATUS);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getEntityMarkerClassName()
	{
		return $this->get(static::ENTITY_ENTITY_MARKER);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getOrderHistoryClassName()
	{
		return $this->get(static::ENTITY_ORDER_HISTORY);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getNotifyClassName()
	{
		return $this->get(static::ENTITY_NOTIFY);
	}
	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public function getPropertyClassName()
	{
		return $this->get(static::ENTITY_PROPERTY);
	}
}