<?php
namespace Bitrix\Sale\Exchange;


class EntityType
{
    const UNDEFINED = 0;
    const ORDER = 1;
    const SHIPMENT = 2;
    const PAYMENT_CASH = 3;
    const PAYMENT_CASH_LESS = 4;
    const PAYMENT_CARD_TRANSACTION = 5;
    const PROFILE = 6;
    const USER_PROFILE = 7;

    const FIRST = 1;
    const LAST = 7;

    const ORDER_NAME = 'ORDER';
    const SHIPMENT_NAME = 'SHIPMENT';
    const PAYMENT_CASH_NAME = 'PAYMENT_CASH';
    const PAYMENT_CASH_LESS_NAME = 'PAYMENT_CASH_LESS';
    const PAYMENT_CARD_TRANSACTION_NAME = 'PAYMENT_CARD_TRANSACTION';
    const PROFILE_NAME = 'PROFILE';
	const USER_PROFILE_NAME = 'USER_PROFILE';


    private static $ALL_DESCRIPTIONS = array();

    /**
     * @param $typeID
     * @return bool
     */
    public static function isDefined($typeID)
    {
        if(!is_int($typeID))
        {
            $typeID = (int)$typeID;
        }
        return $typeID >= self::FIRST && $typeID <= self::LAST;
    }

    /**
     * @param $name
     * @return int
     */
    public static function resolveID($name)
    {
        $name = strtoupper(trim(strval($name)));
        if($name == '')
        {
            return self::UNDEFINED;
        }

        switch($name)
        {
            case self::ORDER_NAME:
                return self::ORDER;

            case self::SHIPMENT_NAME:
                return self::SHIPMENT;

            case self::PAYMENT_CASH_NAME:
                return self::PAYMENT_CASH;

            case self::PAYMENT_CASH_LESS_NAME:
                return self::PAYMENT_CASH_LESS;

            case self::PAYMENT_CARD_TRANSACTION_NAME:
                return self::PAYMENT_CARD_TRANSACTION;

            case self::PROFILE_NAME:
                return self::PROFILE;

			case self::USER_PROFILE_NAME:
				return self::USER_PROFILE;

            default:
                return self::UNDEFINED;
        }
    }

    /**
     * @param $typeID
     * @return string
     */
    public static function resolveName($typeID)
    {
        if(!is_numeric($typeID))
        {
            return '';
        }

        $typeID = intval($typeID);
        if($typeID <= 0)
        {
            return '';
        }

        switch($typeID)
        {
            case self::ORDER:
                return self::ORDER_NAME;

            case self::SHIPMENT:
                return self::SHIPMENT_NAME;

            case self::PAYMENT_CASH:
                return self::PAYMENT_CASH_NAME;

            case self::PAYMENT_CASH_LESS:
                return self::PAYMENT_CASH_LESS_NAME;

            case self::PAYMENT_CARD_TRANSACTION:
                return self::PAYMENT_CARD_TRANSACTION_NAME;

            case self::PROFILE:
                return self::PROFILE_NAME;

			case self::USER_PROFILE:
				return self::USER_PROFILE_NAME;

            case self::UNDEFINED:
            default:
                return '';
        }
    }

    /**
     * @return mixed
     */
    public static function getAllDescriptions()
    {
        if(!self::$ALL_DESCRIPTIONS[LANGUAGE_ID])
        {
            IncludeModuleLangFile(__FILE__);
            self::$ALL_DESCRIPTIONS[LANGUAGE_ID] = array(
                self::ORDER => GetMessage('SALE_TYPE_ORDER'),
                self::SHIPMENT => GetMessage('SALE_TYPE_SHIPMENT'),
                self::PAYMENT_CASH => GetMessage('SALE_TYPE_PAYMENT_CASH'),
                self::PAYMENT_CASH_LESS => GetMessage('SALE_TYPE_PAYMENT_CASH_LESS'),
                self::PAYMENT_CARD_TRANSACTION => GetMessage('SALE_TYPE_PAYMENT_CARD_TRANSACTION'),
                self::PROFILE => GetMessage('SALE_TYPE_PROFILE'),
                self::USER_PROFILE => GetMessage('SALE_TYPE_USER_PROFILE')
            );
        }

        return self::$ALL_DESCRIPTIONS[LANGUAGE_ID];
    }

    /**
     * @param $typeID
     * @return string
     */
    public static function getDescription($typeID)
    {
        $typeID = intval($typeID);
        $all = self::getAllDescriptions();
        return isset($all[$typeID]) ? $all[$typeID] : '';
    }

    /**
     * @param $types
     * @return array
     */
    public static function getDescriptions($types)
    {
        $result = array();
        if(is_array($types))
        {
            foreach($types as $typeID)
            {
                $typeID = intval($typeID);
                $descr = self::getDescription($typeID);
                if($descr !== '')
                {
                    $result[$typeID] = $descr;
                }
            }
        }
        return $result;
    }
}