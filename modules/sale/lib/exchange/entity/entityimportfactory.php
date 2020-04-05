<?php
namespace Bitrix\Sale\Exchange\Entity;

use Bitrix\Main;
use Bitrix\Sale\Exchange;

/**
 * Class EntityImportFactory
 * @package Bitrix\Sale\Exchange\Entity
 * @internal
 */
class EntityImportFactory
{
    /**
     * @param $entityTypeID
     * @param null $parentEntityContext
     * @return OrderImport|PaymentCardImport|PaymentCashImport|PaymentCashLessImport|Exchange\ProfileImport|ShipmentImport|UserProfileImport|UserProfileImport
     * @throws Main\ArgumentException
     * @throws Main\NotSupportedException
     */
    public static function create($entityTypeID, $parentEntityContext = null)
    {
        if(!is_int($entityTypeID))
        {
            $entityTypeID = (int)$entityTypeID;
        }

        if(!Exchange\EntityType::IsDefined($entityTypeID))
        {
            throw new Main\ArgumentOutOfRangeException('Is not defined', Exchange\EntityType::FIRST, Exchange\EntityType::LAST);
        }

        if($entityTypeID === Exchange\EntityType::ORDER)
        {
            return new OrderImport(null);
        }
        elseif($entityTypeID === Exchange\EntityType::SHIPMENT)
        {
            return new ShipmentImport($parentEntityContext);
        }
        elseif($entityTypeID === Exchange\EntityType::PAYMENT_CASH)
        {
            return new PaymentCashImport($parentEntityContext);
        }
        elseif($entityTypeID === Exchange\EntityType::PAYMENT_CASH_LESS)
        {
            return new PaymentCashLessImport($parentEntityContext);
        }
        elseif($entityTypeID === Exchange\EntityType::PAYMENT_CARD_TRANSACTION)
        {
            return new PaymentCardImport($parentEntityContext);
        }
        elseif($entityTypeID === Exchange\EntityType::USER_PROFILE)
        {
            return new UserProfileImport();
        }
        else
        {
            throw new Main\NotSupportedException("Entity type: '".Exchange\EntityType::ResolveName($entityTypeID)."' is not supported in current context");
        }
    }
}