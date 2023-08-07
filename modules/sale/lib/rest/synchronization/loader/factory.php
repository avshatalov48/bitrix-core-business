<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


use Bitrix\Main\NotSupportedException;
use Bitrix\Sale\Registry;

class Factory
{
	public static function create($typeName, $params=[])
	{
		if($typeName === Registry::ENTITY_SHIPMENT
			|| $typeName === Registry::ENTITY_ORDER
			|| $typeName === Registry::ENTITY_PAYMENT
			|| $typeName === Registry::ENTITY_PROPERTY_VALUE
			|| $typeName === Registry::ENTITY_BUNDLE_COLLECTION
			|| $typeName === Registry::ENTITY_TAX
			|| $typeName === Registry::ENTITY_BASKET_ITEM
			|| $typeName === Registry::ENTITY_BASKET_PROPERTY_ITEM
			|| $typeName === Registry::ENTITY_SHIPMENT_ITEM
			|| $typeName === Registry::ENTITY_SHIPMENT_ITEM_STORE
			|| $typeName === Registry::ENTITY_SHIPMENT_ITEM_STORE_COLLECTION
			|| $typeName === Registry::ENTITY_PROPERTY_VALUE_COLLECTION
			|| $typeName === Registry::ENTITY_OPTIONS
			|| $typeName === Registry::ENTITY_DISCOUNT
			|| $typeName === Registry::ENTITY_DISCOUNT_COUPON
			|| $typeName === Registry::ENTITY_ORDER_DISCOUNT
			|| $typeName === Registry::ENTITY_PERSON_TYPE
			|| $typeName === Registry::ENTITY_ORDER_STATUS
			|| $typeName === Registry::ENTITY_DELIVERY_STATUS
			|| $typeName === Registry::ENTITY_ENTITY_MARKER
			|| $typeName === Registry::ENTITY_ORDER_HISTORY
			|| $typeName === Registry::ENTITY_NOTIFY
			|| $typeName === Registry::ENTITY_TRADE_BINDING_ENTITY
			|| $typeName === ENTITY_CRM_CONTACT_COMPANY_COLLECTION)
		{
			return new Entity($typeName);
		}
		elseif ($typeName === Registry::ENTITY_TRADE_BINDING_COLLECTION)
		{
			return new TradeBinding($typeName, $params);
		}
		elseif ($typeName === Registry::ENTITY_SHIPMENT_ITEM_COLLECTION)
		{
			return new ShipmentItem($typeName, $params);
		}
		elseif ($typeName === Registry::ENTITY_SHIPMENT_COLLECTION)
		{
			return new Shipment($typeName, $params);
		}
		elseif ($typeName === Registry::ENTITY_PAYMENT_COLLECTION)
		{
			return new Payment($typeName, $params);
		}
		elseif($typeName === Registry::ENTITY_BASKET_PROPERTIES_COLLECTION)
		{
			return new BasketProperties($typeName, $params);
		}
		elseif($typeName === Registry::ENTITY_BASKET)
		{
			return new BasketItem($typeName, $params);
		}
		elseif($typeName === Registry::ENTITY_PROPERTY)
		{
			return new Property($typeName);
		}
		elseif ($typeName === 'TRADING_PLATFORM_TYPE')
		{
			return new TradingPlatform($typeName);
		}
		elseif ($typeName === 'PRODUCT')
		{
			return new Product($typeName, $params);
		}
		elseif ($typeName === 'PAY_SYSTEM_TYPE')
		{
			return new PaySystem($typeName, $params);
		}
		elseif ($typeName === 'DELIVERY_SYSTEM_TYPE')
		{
			return new DeliverySystem($typeName, $params);
		}
		elseif ($typeName === 'PERSON_TYPE_TYPE')
		{
			return new PersonType($typeName, $params);
		}
		else
		{
			throw new NotSupportedException("Entity type: '".$typeName."' is not supported in current context");
		}
	}
}