<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Main;
use Bitrix\Sale\Exchange;


class DocumentImportFactory
{
    /** Create new document import by specified document type ID.
     * @static
     * @param int $documentTypeID Document type ID.
     * @return DocumentBase
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\NotSupportedException
     */
    public static function create($documentTypeID)
    {
        if(!is_int($documentTypeID))
        {
            $documentTypeID = (int)$documentTypeID;
        }

        if(!DocumentType::IsDefined($documentTypeID))
        {
            throw new Main\ArgumentOutOfRangeException('documentTypeID', DocumentType::FIRST, DocumentType::LAST);
        }

        if($documentTypeID === DocumentType::ORDER)
        {
            return new OrderDocument();
        }
        elseif($documentTypeID === DocumentType::SHIPMENT)
        {
            return new ShipmentDocument();
        }
        elseif($documentTypeID === DocumentType::PAYMENT_CASH)
        {
            return new PaymentCashDocument();
        }
        elseif($documentTypeID === DocumentType::PAYMENT_CASH_LESS)
        {
            return new PaymentCashLessDocument();
        }
        elseif($documentTypeID === DocumentType::PAYMENT_CARD_TRANSACTION)
        {
            return new PaymentCardDocument();
        }
        elseif($documentTypeID === DocumentType::PROFILE)
        {
            return new ProfileDocument();
        }
		elseif($documentTypeID === DocumentType::USER_PROFILE)
		{
			return new UserProfileDocument();
		}
        else
        {
            throw new Main\NotSupportedException("Document type: '".Exchange\EntityType::ResolveName($documentTypeID)."' is not supported in current context");
        }
    }
}