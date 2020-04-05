<?php
namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\DocumentType;
use Bitrix\Sale\Exchange\OneC\PaymentCardDocument;
use Bitrix\Sale\Exchange\OneC\PaymentCashLessDocument;
use Bitrix\Sale\Exchange\OneC\ProfileDocument;
use Bitrix\Sale\Exchange\OneC\UserProfileDocument;

class DocumentFactory
{
	/** Create new document import by specified document type ID.
	 * @static
	 * @param int $documentTypeID Document type ID.
	 * @return DocumentBase
	 * @throws ArgumentOutOfRangeException
	 * @throws NotSupportedException
	 */
	public static function create($documentTypeID)
	{
		if(!is_int($documentTypeID))
		{
			$documentTypeID = (int)$documentTypeID;
		}

		if(!DocumentType::IsDefined($documentTypeID))
		{
			throw new ArgumentOutOfRangeException('documentTypeID', DocumentType::FIRST, DocumentType::LAST);
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
			return new PaymentCardDocument();
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
			throw new NotSupportedException("Document type: '".EntityType::ResolveName($documentTypeID)."' is not supported in current context");
		}
	}
}