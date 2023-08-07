<?php
namespace Bitrix\Sale\Exchange\OneC\CRM;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentInvoice;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentPaymentInvoice;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentProfile;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentShipmentInvoice;
use Bitrix\Sale\Exchange\OneC\DocumentType;

class ConverterFactory
{
	/**
	 * @param $typeId
	 * @return \Bitrix\Sale\Exchange\OneC\Converter
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

		if ($typeId === EntityType::ORDER)
		{
			return new ConverterDocumentInvoice();
		}
		elseif ($typeId === EntityType::SHIPMENT)
		{
			return new ConverterDocumentShipmentInvoice();
		}
		elseif(
			$typeId === EntityType::PAYMENT_CASH ||
			$typeId === EntityType::PAYMENT_CASH_LESS ||
			$typeId === EntityType::PAYMENT_CARD_TRANSACTION)
		{
			return new ConverterDocumentPaymentInvoice();
		}
		elseif($typeId == EntityType::PROFILE ||
			$typeId == EntityType::USER_PROFILE)
		{
			return new ConverterDocumentProfile();
		}
		else
		{
			throw new NotSupportedException("Entity type: '".DocumentType::ResolveName($typeId)."' is not supported in current context");
		}
	}
}