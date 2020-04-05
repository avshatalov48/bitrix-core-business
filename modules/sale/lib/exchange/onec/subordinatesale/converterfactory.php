<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\IConverter;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentPaymentCard;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentPaymentCash;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentPaymentCashLess;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentProfile;
use Bitrix\Sale\Exchange\OneC\DocumentType;

class ConverterFactory
{
	/**
	 * @param $typeId
	 * @return IConverter
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
			throw new ArgumentOutOfRangeException('documentTypeID', DocumentType::FIRST, DocumentType::LAST);
		}

		if($typeId === EntityType::ORDER)
		{
			return new ConverterDocumentOrder();
		}
		elseif($typeId === EntityType::SHIPMENT)
		{
			return new ConverterDocumentShipment();
		}
		elseif($typeId === EntityType::PAYMENT_CASH)
		{
			return new ConverterDocumentPaymentCash();
		}
		elseif($typeId === EntityType::PAYMENT_CASH_LESS)
		{
			return new ConverterDocumentPaymentCashLess();
		}
		elseif($typeId === EntityType::PAYMENT_CARD_TRANSACTION)
		{
			return new ConverterDocumentPaymentCard();
		}
		elseif($typeId == EntityType::PROFILE ||
			$typeId == EntityType::USER_PROFILE)
		{
			return new ConverterDocumentProfile();
		}
		else
		{
			throw new NotSupportedException("Entity type: '".EntityType::ResolveName($typeId)."' is not supported in current context");
		}
	}
}