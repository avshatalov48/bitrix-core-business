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
	 * @return Exchange\ImportBase
	 * @throws Main\ArgumentOutOfRangeException
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
		elseif($entityTypeID === Exchange\EntityType::INVOICE)
		{
			return new Invoice(null);
		}
		elseif($entityTypeID === Exchange\EntityType::INVOICE_SHIPMENT)
		{
			return new ShipmentInvoice($parentEntityContext);
		}
		elseif($entityTypeID === Exchange\EntityType::INVOICE_PAYMENT_CASH)
		{
			return new PaymentCashInvoice($parentEntityContext);
		}
		elseif($entityTypeID === Exchange\EntityType::INVOICE_PAYMENT_CASH_LESS)
		{
			return new PaymentCashLessInvoice($parentEntityContext);
		}
		elseif($entityTypeID === Exchange\EntityType::INVOICE_PAYMENT_CARD_TRANSACTION)
		{
			return new PaymentCardInvoice($parentEntityContext);
		}
        else
        {
            throw new Main\NotSupportedException("Entity type: '".Exchange\EntityType::ResolveName($entityTypeID)."' is not supported in current context");
        }
    }
}