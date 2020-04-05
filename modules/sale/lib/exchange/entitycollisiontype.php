<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Main;
use Bitrix\Sale\Exchange;

class EntityCollisionType
{
	const GROUP_E_ERROR = 1;
	const GROUP_E_WARNING = 2;

	const Undefined = 0;
    const OrderIsPayed = 1;
    const OrderIsShipped = 2;
    const OrderFinalStatus = 3;
    const ShipmentIsShipped = 4;
    const ShipmentBasketItemNotFound = 5;
    const ShipmentBasketItemQuantityError = 6;
    const ShipmentBasketItemsModify = 7;
    const OrderShipmentItemsModify = 8;
    const ShipmentBasketItemsModifyError = 9;
    const OrderShipmentItemsModifyError = 10;
    const PaymentIsPayed = 11;
    const OrderShipmentDeleted = 12;
    const OrderShipmentDeletedError = 13;
    const OrderPaymentDeleted = 14;
    const OrderPaymentDeletedError = 15;
    const OrderBasketItemTaxValueError = 16;
    const OrderSynchronizeBasketItemsModify = 17;
    const OrderPayedByStatusError = 18;
    const OrderBasketItemTypeError = 19;
    const PaymentCashBoxCheckNotFound = 20;
	const OrderSynchronizeBasketItemsModifyError = 21;
	const BeforeUpdatePaymentDeletedError = 22;
	const BeforeUpdateShipmentDeletedError = 23;
	const OrderShippedByStatusError = 24;
	const OrderBasketItemsCurrencyModify = 25;

    const First = 1;
    const Last = 25;

    const OrderIsPayedName = 'ORDER_IS_PAYED';
    const OrderIsShippedName = 'ORDER_IS_SHIPPED';
    const OrderFinalStatusName = 'ORDER_FINAL_STATUS';
    const ShipmentIsShippedName = 'SHIPMENT_IS_SHIPPED';
    const ShipmentBasketItemNotFoundName = 'SHIPMENT_BASKET_ITEM_NOT_FOUND';
    const ShipmentBasketItemQuantityErrorName = 'SHIPMENT_BASKET_ITEM_QUANTITY_ERROR';
    const ShipmentBasketItemsModifyName = 'SHIPMENT_BASKET_ITEMS_MODIFY';
    const OrderShipmentItemsModifyName = 'ORDER_SHIPMENT_ITEMS_MODIFY';
    const ShipmentBasketItemsModifyErrorName = 'SHIPMENT_BASKET_ITEMS_MODIFY_ERROR';
    const OrderShipmentItemsModifyErrorName = 'ORDER_SHIPMENT_ITEMS_MODIFY_ERROR';
    const PaymentIsPayedName = 'PAYMENT_IS_PAYED';
    const OrderShipmentDeletedName = 'ORDER_SHIPMENT_DELETED';
    const OrderShipmentDeletedErrorName = 'ORDER_SHIPMENT_DELETED_ERROR';
    const OrderPaymentDeletedName = 'ORDER_PAYMENT_DELETED';
    const OrderPaymentDeletedErrorName = 'ORDER_PAYMENT_DELETED_ERROR';
    const OrderBasketItemTaxValueErrorName = 'ORDER_BASKET_ITEM_TAX_VALUE_ERROR';
    const OrderSynchronizeBasketItemsModifyName = 'ORDER_SYNCHRONIZE_BASKET_ITEMS_MODIFY';
    const OrderPayedByStatusErrorName = 'ORDER_PAYED_BY_STATUS_ERROR';
    const OrderBasketItemTypeErrorName = 'ORDER_BASKET_ITEM_TYPE_ERROR';
    const PaymentCashBoxCheckNotFoundName = 'PAYMENT_CASH_BOX_CHECK_NOT_FOUND';
    const OrderSynchronizeBasketItemsModifyErrorName = 'ORDER_SYNCHRONIZE_BASKET_ITEMS_MODIFY_ERROR';
    const BeforeUpdatePaymentDeletedErrorName = 'BEFORE_UPDATE_PAYMENT_DELETED_ERROR';
    const BeforeUpdateShipmentDeletedErrorName = 'BEFORE_UPDATE_SHIPMENT_DELETED_ERROR';
    const OrderShippedByStatusErrorName = 'ORDER_SHIPPED_BY_STATUS_ERROR';
    const OrderBasketItemsCurrencyModifyName = 'ORDER_BASKET_ITEMS_CURRENCY_MODIFY';

    private static $ALL_DESCRIPTIONS = array();
    private static $ALL_ERROR_GROUPS = array();

	/**
	 * @param $typeId
	 * @return bool
	 */
	public static function isDefined($typeId)
    {
        if(!is_int($typeId))
        {
            $typeId = (int)$typeId;
        }
        return $typeId >= self::First && $typeId <= self::Last;
    }

	/**
	 * @param string $name
	 * @return int
	 */
	public static function resolveID($name)
    {
        $name = strtoupper(trim(strval($name)));
        if($name == '')
        {
            return self::Undefined;
        }

        switch($name)
        {
            case self::OrderIsPayedName:
                return self::OrderIsPayed;
            case self::OrderIsShippedName:
                return self::OrderIsShipped;
            case self::OrderFinalStatusName:
                return self::OrderFinalStatus;
            case self::ShipmentIsShippedName:
                return self::ShipmentIsShipped;
            case self::ShipmentBasketItemNotFoundName:
                return self::ShipmentBasketItemNotFound;
            case self::ShipmentBasketItemQuantityErrorName:
                return self::ShipmentBasketItemQuantityError;
            case self::ShipmentBasketItemsModifyName:
                return self::ShipmentBasketItemsModify;
            case self::OrderShipmentItemsModifyName:
                return self::OrderShipmentItemsModify;
            case self::ShipmentBasketItemsModifyErrorName:
                return self::ShipmentBasketItemsModifyError;
            case self::OrderShipmentItemsModifyErrorName:
                return self::OrderShipmentItemsModifyError;
            case self::PaymentIsPayedName:
                return self::PaymentIsPayed;
            case self::OrderShipmentDeletedName:
                return self::OrderShipmentDeleted;
            case self::OrderShipmentDeletedErrorName:
                return self::OrderShipmentDeletedError;
            case self::OrderPaymentDeletedName:
                return self::OrderPaymentDeleted;
            case self::OrderPaymentDeletedErrorName:
                return self::OrderPaymentDeletedError;
            case self::OrderBasketItemTaxValueErrorName:
                return self::OrderBasketItemTaxValueError;
            case self::OrderSynchronizeBasketItemsModifyName:
                return self::OrderSynchronizeBasketItemsModify;
            case self::OrderPayedByStatusErrorName:
                return self::OrderPayedByStatusError;
            case self::OrderBasketItemTypeErrorName:
				return self::OrderBasketItemTypeError;
			case self::PaymentCashBoxCheckNotFoundName:
				return self::PaymentCashBoxCheckNotFound;
			case self::OrderSynchronizeBasketItemsModifyErrorName:
				return self::OrderSynchronizeBasketItemsModifyError;
			case self::BeforeUpdatePaymentDeletedErrorName:
				return self::BeforeUpdatePaymentDeletedError;
			case self::BeforeUpdateShipmentDeletedErrorName:
				return self::BeforeUpdateShipmentDeletedError;
			case self::OrderShippedByStatusErrorName:
				return self::OrderShippedByStatusError;
			case self::OrderBasketItemsCurrencyModifyName:
				return self::OrderBasketItemsCurrencyModify;

            default:
                return self::Undefined;
        }
    }

	/**
	 * @param int $typeId
	 * @return string
	 */
	public static function resolveName($typeId)
    {
        if(!is_numeric($typeId))
        {
            return '';
        }

        $typeId = intval($typeId);
        if($typeId <= 0)
        {
            return '';
        }

        switch($typeId)
        {
            case self::OrderIsPayed:
                return self::OrderIsPayedName;
            case self::OrderIsShipped:
                return self::OrderIsShippedName;
            case self::OrderFinalStatus:
                return self::OrderFinalStatusName;
            case self::ShipmentIsShipped:
                return self::ShipmentIsShippedName;
            case self::ShipmentBasketItemNotFound:
                return self::ShipmentBasketItemNotFoundName;
            case self::ShipmentBasketItemQuantityError:
                return self::ShipmentBasketItemQuantityErrorName;
            case self::ShipmentBasketItemsModify:
                return self::ShipmentBasketItemsModifyName;
            case self::OrderShipmentItemsModify:
                return self::OrderShipmentItemsModifyName;
            case self::ShipmentBasketItemsModifyError:
                return self::ShipmentBasketItemsModifyErrorName;
            case self::OrderShipmentItemsModifyError:
                return self::OrderShipmentItemsModifyErrorName;
            case self::PaymentIsPayed:
                return self::PaymentIsPayedName;
            case self::OrderShipmentDeleted:
                return self::OrderShipmentDeletedName;
            case self::OrderShipmentDeletedError:
                return self::OrderShipmentDeletedErrorName;
            case self::OrderPaymentDeleted:
                return self::OrderPaymentDeletedName;
            case self::OrderPaymentDeletedError:
                return self::OrderPaymentDeletedErrorName;
            case self::OrderBasketItemTaxValueError:
                return self::OrderBasketItemTaxValueErrorName;
            case self::OrderSynchronizeBasketItemsModify:
                return self::OrderSynchronizeBasketItemsModifyName;
            case self::OrderPayedByStatusError:
                return self::OrderPayedByStatusErrorName;
			case self::OrderBasketItemTypeError:
				return self::OrderBasketItemTypeErrorName;
			case self::PaymentCashBoxCheckNotFound:
				return self::PaymentCashBoxCheckNotFoundName;
			case self::OrderSynchronizeBasketItemsModifyError:
				return self::OrderSynchronizeBasketItemsModifyErrorName;
			case self::BeforeUpdatePaymentDeletedError:
				return self::BeforeUpdatePaymentDeletedErrorName;
			case self::BeforeUpdateShipmentDeletedError:
				return self::BeforeUpdateShipmentDeletedErrorName;
			case self::OrderShippedByStatusError:
				return self::OrderShippedByStatusErrorName;
			case self::OrderBasketItemsCurrencyModify:
				return self::OrderBasketItemsCurrencyModifyName;

            case self::Undefined:
            default:
                return '';
        }
    }

	/**
	 * @return array
	 */
	public static function getAllDescriptions()
    {
        if(!self::$ALL_DESCRIPTIONS[LANGUAGE_ID])
        {
            IncludeModuleLangFile(__FILE__);
            self::$ALL_DESCRIPTIONS[LANGUAGE_ID] = array(
                self::OrderIsPayed => GetMessage('SALE_COLLISION_TYPE_ORDER_IS_PAYED'),
                self::OrderIsShipped => GetMessage('SALE_COLLISION_TYPE_ORDER_IS_SHIPPED'),
                self::OrderFinalStatus => GetMessage('SALE_COLLISION_TYPE_ORDER_FINAL_STATUS'),
                self::ShipmentIsShipped => GetMessage('SALE_COLLISION_TYPE_SHIPMENT_IS_SHIPPED'),
                self::ShipmentBasketItemNotFound => GetMessage('SALE_COLLISION_TYPE_SHIPMENT_BASKET_ITEM_NOT_FOUND'),
                self::ShipmentBasketItemQuantityError => GetMessage('SALE_COLLISION_TYPE_SHIPMENT_BASKET_ITEM_QUANTITY_ERROR'),
                self::ShipmentBasketItemsModify => GetMessage('SALE_COLLISION_TYPE_SHIPMENT_BASKET_ITEMS_MODIFY'),
                self::OrderShipmentItemsModify => GetMessage('SALE_COLLISION_TYPE_ORDER_SHIPMENT_ITEMS_MODIFY'),
                self::ShipmentBasketItemsModifyError => GetMessage('SALE_COLLISION_TYPE_SHIPMENT_BASKET_ITEMS_MODIFY_ERROR'),
                self::OrderShipmentItemsModifyError => GetMessage('SALE_COLLISION_TYPE_ORDER_SHIPMENT_ITEMS_MODIFY_ERROR'),
                self::PaymentIsPayed => GetMessage('SALE_COLLISION_TYPE_PAYMENT_IS_PAYED'),
                self::OrderShipmentDeleted => GetMessage('SALE_COLLISION_TYPE_ORDER_SHIPMENT_DELETED'),
                self::OrderShipmentDeletedError => GetMessage('SALE_COLLISION_TYPE_ORDER_SHIPMENT_DELETED_ERROR'),
                self::OrderPaymentDeleted => GetMessage('SALE_COLLISION_TYPE_ORDER_PAYMENT_DELETED'),
                self::OrderPaymentDeletedError => GetMessage('SALE_COLLISION_TYPE_ORDER_PAYMENT_DELETED_ERROR'),
                self::OrderBasketItemTaxValueError => GetMessage('SALE_COLLISION_TYPE_ORDER_BASKET_ITEM_TAX_VALUE_ERROR'),
                self::OrderSynchronizeBasketItemsModify => GetMessage('SALE_COLLISION_TYPE_ORDER_SYNCHRONIZE_BASKET_ITEMS_MODIFY'),
                self::OrderPayedByStatusError => GetMessage('SALE_COLLISION_TYPE_ORDER_PAYED_BY_STATUS_ERROR'),
                self::OrderBasketItemTypeError => GetMessage('SALE_COLLISION_TYPE_ORDER_BASKET_ITEM_TYPE_ERROR'),
                self::PaymentCashBoxCheckNotFound => GetMessage('SALE_COLLISION_TYPE_PAYMENT_CASH_BOX_CHECK_NOT_FOUND'),
                self::OrderSynchronizeBasketItemsModifyError => GetMessage('SALE_COLLISION_TYPE_ORDER_SYNCHRONIZE_BASKET_ITEMS_MODIFY_ERROR'),
                self::BeforeUpdatePaymentDeletedError => GetMessage('SALE_COLLISION_TYPE_BEFORE_UPDATE_PAYMENT_DELETED_ERROR'),
                self::BeforeUpdateShipmentDeletedError => GetMessage('SALE_COLLISION_TYPE_BEFORE_UPDATE_SHIPMENT_DELETED_ERROR'),
                self::OrderShippedByStatusError => GetMessage('SALE_COLLISION_TYPE_ORDER_SHIPPED_BY_STATUS_ERROR'),
                self::OrderBasketItemsCurrencyModify => GetMessage('SALE_COLLISION_TYPE_ORDER_BASKET_ITEMS_CURRENCY_MODIFY'),
            );
        }
        return self::$ALL_DESCRIPTIONS[LANGUAGE_ID];
    }

	/**
	 * @param int $typeId
	 * @return string
	 */
	public static function getDescription($typeId)
    {
        $typeId = intval($typeId);
        $all = self::getAllDescriptions();
        return isset($all[$typeId]) ? $all[$typeId] : '';
    }

	/**
	 * @param array $types
	 * @return array
	 */
	public static function getDescriptions($types)
    {
        $result = array();
        if(is_array($types))
        {
            foreach($types as $typeId)
            {
                $typeId = intval($typeId);
                $descr = self::getDescription($typeId);
                if($descr !== '')
                {
                    $result[$typeId] = $descr;
                }
            }
        }
        return $result;
    }

	/**
	 * @return array
	 */
	protected static function getAllErrorGroups()
	{
		if(!self::$ALL_ERROR_GROUPS)
		{
			self::$ALL_ERROR_GROUPS = array(
			self::OrderIsPayed => self::GROUP_E_ERROR,
			self::OrderIsShipped => self::GROUP_E_ERROR,
			self::OrderFinalStatus => self::GROUP_E_ERROR,
			self::ShipmentIsShipped => self::GROUP_E_ERROR,
			self::ShipmentBasketItemNotFound => self::GROUP_E_WARNING,
			self::ShipmentBasketItemQuantityError => self::GROUP_E_WARNING,
			self::ShipmentBasketItemsModify => self::GROUP_E_WARNING,
			self::OrderShipmentItemsModify => self::GROUP_E_WARNING,
			self::ShipmentBasketItemsModifyError => self::GROUP_E_WARNING,
			self::OrderShipmentItemsModifyError => self::GROUP_E_WARNING,
			self::PaymentIsPayed => self::GROUP_E_ERROR,
			self::OrderShipmentDeleted => self::GROUP_E_WARNING,
			self::OrderShipmentDeletedError => self::GROUP_E_WARNING,
			self::OrderPaymentDeleted => self::GROUP_E_WARNING,
			self::OrderPaymentDeletedError => self::GROUP_E_WARNING,
			self::OrderBasketItemTaxValueError => self::GROUP_E_WARNING,
			self::OrderSynchronizeBasketItemsModify => self::GROUP_E_WARNING,
			self::OrderPayedByStatusError => self::GROUP_E_WARNING,
			self::OrderBasketItemTypeError => self::GROUP_E_WARNING,
			self::PaymentCashBoxCheckNotFound => self::GROUP_E_WARNING,
			self::OrderSynchronizeBasketItemsModifyError => self::GROUP_E_ERROR,
			self::BeforeUpdatePaymentDeletedError => self::GROUP_E_ERROR,
			self::BeforeUpdateShipmentDeletedError => self::GROUP_E_ERROR,
			self::OrderShippedByStatusError => self::GROUP_E_WARNING,
			self::OrderBasketItemsCurrencyModify => self::GROUP_E_WARNING,
			);
		}
		return self::$ALL_ERROR_GROUPS;
	}

	/**
	 * @param $typeId
	 * @return mixed|int
	 */
	public static function getErrorGroup($typeId)
	{
		$typeId = intval($typeId);
		$all = self::getAllErrorGroups();
		return isset($all[$typeId]) ? $all[$typeId] : null;
	}
}