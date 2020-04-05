<?php

namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Sale\Exchange\EntityType;

class ConverterFactory
{
	/**
	 * @param $typeId
	 * @return Converter
	 * @throws ArgumentOutOfRangeException
	 * @throws NotSupportedException
	 */
	public static function create($typeId)
	{
		if(!is_int($typeId))
		{
			$typeId = (int)$typeId;
		}

		if(!EntityType::IsDefined($typeId))
		{
			throw new ArgumentOutOfRangeException('documentTypeID', EntityType::FIRST, EntityType::LAST);
		}

		if($typeId === EntityType::ORDER)
		{
			return new ConverterDocumentOrder();
		}
		elseif($typeId === EntityType::SHIPMENT)
		{
			return new ConverterDocumentShipment();
		}
		elseif($typeId === EntityType::PAYMENT_CASH ||
			$typeId === EntityType::PAYMENT_CASH_LESS ||
			$typeId === EntityType::PAYMENT_CARD_TRANSACTION ||
			$typeId === EntityType::INVOICE_PAYMENT_CASH ||
			$typeId === EntityType::INVOICE_PAYMENT_CASH_LESS ||
			$typeId === EntityType::INVOICE_PAYMENT_CARD_TRANSACTION)
		{
			return new ConverterDocumentPayment();
		}
		elseif($typeId == EntityType::PROFILE ||
			$typeId == EntityType::USER_PROFILE ||
			$typeId == EntityType::USER_PROFILE_CONTACT_COMPANY)
		{
			return new ConverterDocumentProfile();
		}
		elseif ($typeId === EntityType::INVOICE)
		{
			return new ConverterDocumentInvoice();
		}
		elseif ($typeId === EntityType::INVOICE_SHIPMENT)
		{
			return new ConverterDocumentShipmentInvoice();
		}
		else
		{
			throw new NotSupportedException("Entity type: '".DocumentType::ResolveName($typeId)."' is not supported in current context");
		}
	}
}