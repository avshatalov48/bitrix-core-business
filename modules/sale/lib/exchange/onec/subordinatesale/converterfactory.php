<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\IConverter;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentPayment;
use Bitrix\Sale\Exchange\OneC\ConverterDocumentProfile;

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
			$typeId === EntityType::PAYMENT_CARD_TRANSACTION)
		{
			return new ConverterDocumentPayment();
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